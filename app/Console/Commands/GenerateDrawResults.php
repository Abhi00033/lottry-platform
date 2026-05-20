<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SeriesMaster;
use App\Models\Bet;
use App\Models\Result;
use App\Models\UserBalanceTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateDrawResults extends Command
{
    protected $signature   = 'draw:generate-results';
    protected $description = 'Automate 15-min draw results with profitability control';

    // Only one constant kept — used as fallback ONLY when a user has no commission set in DB.
    // All actual payouts and risk calculations use each agent's own commission rate from DB.
    const DEFAULT_COMMISSION = 5;

    // REMOVED: PAYOUT_MULTIPLIER = 90  → was hardcoded, wrong, replaced by per-agent rate
    // REMOVED: TARGET_HOUSE_PROFIT_PERCENT = 40 → was causing 0/1 repeat bug

    public function handle()
    {
        $now       = Carbon::now();
        $startTime = config('app.draw_start');
        $endTime   = config('app.draw_end');

        if ($now->format('H:i') < $startTime || $now->format('H:i') > $endTime) {
            $this->info("Outside draw hours ($startTime - $endTime). Skipping.");
            return;
        }

        $minutes      = $now->minute;
        $targetMinute = floor($minutes / 15) * 15;
        $drawTime     = $now->copy()->minute($targetMinute)->second(0);

        $this->info("Generating results for Draw Time: " . $drawTime->format('Y-m-d H:i:s'));

        $seriesList = SeriesMaster::all();

        if ($seriesList->isEmpty()) {
            $this->warn("No series found. Exiting.");
            return;
        }

        foreach ($seriesList as $mainSeries) {
            for ($i = 0; $i < 10; $i++) {
                $subSeriesStart = (int) $mainSeries->start + ($i * 100);

                $exists = Result::where('draw_time', $drawTime)
                    ->where('series', $subSeriesStart)
                    ->exists();

                if ($exists) {
                    $this->line("Result already exists for $subSeriesStart. Skipping.");
                    continue;
                }

                try {
                    $this->processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime);
                } catch (\Throwable $e) {
                    $this->error("Error processing $subSeriesStart: " . $e->getMessage());
                    \Illuminate\Support\Facades\Log::error("DrawResult Error [$subSeriesStart]: " . $e->getMessage());
                }
            }
        }

        $this->info("Execution Completed!");
    }

    // -------------------------------------------------------------------------
    // Helper: resolve commission rate for any user/agent
    //
    // Priority:
    //   1. users.commision column value set by admin  → use that
    //   2. Not set / null / zero                      → use DEFAULT_COMMISSION (5%)
    //
    // Called in BOTH Step 2 (risk estimation) and Step 7 (actual payout)
    // so both are always using the exact same rate — no mismatch possible.
    // -------------------------------------------------------------------------
    private function getCommissionRate($user): float
    {
        if (
            $user &&
            !is_null($user->commision) &&
            (float) $user->commision > 0
        ) {
            return (float) $user->commision;
        }

        return self::DEFAULT_COMMISSION;
    }

    private function processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime)
    {
        DB::transaction(function () use ($mainSeries, $subSeriesStart, $drawTime) {

            // $subSeriesEnd = $subSeriesStart + 99;

            // -----------------------------------------------------------------
            // STEP 1 — Fetch all pending bets for this sub-series
            //
            // ->with('user') eager loads each bet's agent so getCommissionRate()
            // in Step 2 and Step 7 does NOT fire extra DB queries per bet.
            // -----------------------------------------------------------------
            $bets = Bet::where('series_id', $mainSeries->id)
                ->where('draw_time', $drawTime)
                ->where('status', 'pending')
                ->where('series_group', $subSeriesStart)
                ->with('user')
                ->lockForUpdate()
                ->get();

            $totalPointsCollected = $bets->sum('points');

            // -----------------------------------------------------------------
            // STEP 2 — Calculate REAL net payout per number
            //
            // For each of the 100 numbers in this sub-series, sum up the exact
            // payout the house would need to pay if that number wins.
            //
            // Uses each agent's own commission from DB (via getCommissionRate).
            //
            // Formula per bet on that number:
            //   netPayout = bet.points × (100 - agentCommission%)
            //
            // Examples with different admin-set commissions:
            //   Agent commission 5%  → bet 100pts → payout = 100 × 95 = 9500
            //   Agent commission 10% → bet 100pts → payout = 100 × 90 = 9000
            //   Agent commission 20% → bet 100pts → payout = 100 × 80 = 8000
            //   Agent no commission  → bet 100pts → payout = 100 × 95 = 9500 (default 5%)
            //
            // Multiple agents can bet on same number — we sum all their payouts.
            // -----------------------------------------------------------------
            $payoutScenarios = [];
            for ($n = 0; $n <= 99; $n++) {
                $fullNumber   = $subSeriesStart + $n;
                $betsOnNumber = $bets->where('number', (string) $fullNumber);

                $realNetPayout = $betsOnNumber->sum(function ($bet) {
                    $commission = $this->getCommissionRate($bet->user);
                    return $bet->points * (100 - $commission);
                });

                $payoutScenarios[$fullNumber] = $realNetPayout;
            }

            // -----------------------------------------------------------------
            // STEP 3 — Last 5 results for this sub-series
            // Used to avoid picking the same number repeatedly across draws.
            // -----------------------------------------------------------------
            $recentNumbers = Result::where('series', $subSeriesStart)
                ->orderBy('draw_time', 'desc')
                ->take(5)
                ->pluck('result_number')
                ->toArray();

            // -----------------------------------------------------------------
            // STEP 4 — Build safe candidate pool
            //
            // SAFE = real net payout for that number ≤ total points collected
            // This guarantees house never pays out more than it took in.
            // The difference (collected - payout) is the house profit.
            // Commission per agent is already baked into $payoutScenarios.
            //
            // Example:
            //   Total collected        = 10,000 pts
            //   Number X net payout    =  7,500 pts → SAFE  (house profit = 2,500)
            //   Number Y net payout    = 11,000 pts → NOT SAFE (house loses 1,000)
            // -----------------------------------------------------------------
            $safeNumbers = collect($payoutScenarios)->filter(
                function ($netPayout) use ($totalPointsCollected) {
                    if ($totalPointsCollected == 0) return true;
                    return $netPayout <= $totalPointsCollected;
                }
            );

            // Remove recently drawn numbers to avoid visible repeat pattern
            $candidates = $safeNumbers->reject(
                function ($payout, $number) use ($recentNumbers) {
                    return in_array($number, $recentNumbers);
                }
            );

            // Fallback 1: all safe numbers happened to be recent → allow from safe pool
            if ($candidates->isEmpty()) {
                $candidates = $safeNumbers;
            }

            // Fallback 2: no safe numbers at all (extreme edge case — e.g. one agent
            // placed massive bets on all 100 numbers) → pick least damaging number
            if ($candidates->isEmpty()) {
                $candidates = collect($payoutScenarios)->sortBy(fn($p) => $p);
            }

            // -----------------------------------------------------------------
            // STEP 5 — Weighted random selection
            //
            // Fixes the core client complaint (0/1 numbers always repeating).
            //
            // OLD broken logic:
            //   sort() → take(15) → random()
            //   = always the 15 numbers with zero/lowest bets
            //   = always low index numbers (00–14) since agents bet on round numbers
            //   = agents see 1001, 1002, 1000 every draw → stop playing
            //
            // NEW fixed logic — weighted random:
            //   weight = ((maxPayout - thisPayout) / maxPayout × 90) + 10
            //
            //   Zero-bet number  → weight 100 → most likely to win
            //   Mid-bet number   → weight ~55 → can win
            //   Max-bet number   → weight 10  → rarely wins but CAN win
            //
            // Result: winning numbers spread naturally across full 00–99 range.
            // House is still protected because only safe numbers are in the pool.
            // -----------------------------------------------------------------
            $maxPayout = $candidates->max() ?: 1;

            $pool = [];
            foreach ($candidates as $number => $netPayout) {
                $weight = (int)(($maxPayout - $netPayout) / $maxPayout * 90) + 10;
                for ($w = 0; $w < $weight; $w++) {
                    $pool[] = $number;
                }
            }

            $winningNumber = $pool[array_rand($pool)];

            // -----------------------------------------------------------------
            // STEP 6 — Persist result
            // -----------------------------------------------------------------
            Result::create([
                'draw_time'     => $drawTime,
                'series'        => $subSeriesStart,
                'result_number' => $winningNumber,
            ]);

            // -----------------------------------------------------------------
            // STEP 7 — Pay winners, mark losers
            //
            // Uses same getCommissionRate() as Step 2 — always in sync.
            //
            // Agent commission 5%  → wins 95× their bet points
            // Agent commission 10% → wins 90× their bet points
            // Agent commission 20% → wins 80× their bet points
            // Agent not set        → wins 95× (DEFAULT_COMMISSION fallback = 5%)
            //
            // Commission in remarks for admin audit trail.
            // -----------------------------------------------------------------
            foreach ($bets as $bet) {
                if ($bet->status !== 'pending') continue;

                if ((int) $bet->number === (int) $winningNumber) {

                    $user = $bet->user ?? User::find($bet->user_id);

                    if (!$user) {
                        $bet->update(['status' => 'lost']);
                        continue;
                    }

                    $commissionRate = $this->getCommissionRate($user);
                    $winAmount      = $bet->points * (100 - $commissionRate);

                    $bet->update(['status' => 'won']);
                    $user->increment('balance', $winAmount);

                    UserBalanceTransaction::create([
                        'user_id'       => $user->id,
                        'type'          => 'credit',
                        'amount'        => $winAmount,
                        'balance_after' => $user->fresh()->balance,
                        'remarks'       => "WIN: Draw " . $drawTime->format('h:i A') .
                            " | No: $winningNumber" .
                            " | Payout: {$winAmount}" .
                            " | Commission: {$commissionRate}%",
                    ]);

                } else {
                    $bet->update(['status' => 'lost']);
                }
            }

            $this->info("Processed $subSeriesStart: Winner $winningNumber (Collected: $totalPointsCollected)");
        });
    }
}
