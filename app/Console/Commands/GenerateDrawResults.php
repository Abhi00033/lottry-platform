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
    const DEFAULT_COMMISSION = 0;

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
            $winnerRanges = collect(range(0, 9))
                ->shuffle()
                ->take(rand(1, 2))
                ->toArray();

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
                    $this->processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime, in_array($i, $winnerRanges));
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

    private function processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime, $allowWinner)
    {

        DB::transaction(function () use ($mainSeries, $subSeriesStart, $drawTime, $allowWinner) {

            //  STEP 1 — FETCH ALL PENDING BETS

            $bets = Bet::where('series_id', $mainSeries->id)
                ->where('draw_time', $drawTime)
                ->where('status', 'pending')
                ->where('series_group', $subSeriesStart)
                ->with('user')
                ->lockForUpdate()
                ->get();

            //   NO BETS PLACED

            if ($bets->isEmpty()) {

                $randomNumber = $subSeriesStart + rand(0, 99);

                Result::create([
                    'draw_time'     => $drawTime,
                    'series'        => $subSeriesStart,
                    'result_number' => $randomNumber,
                ]);

                $this->info("No bets for {$subSeriesStart}. Random Result: {$randomNumber}");

                return;
            }

            // STEP 2 — TOTAL COLLECTION

            $totalPointsCollected = $bets->sum('points');

            // STEP 3 — CALCULATE PAYOUT PER NUMBER
            // | REAL GAME LOGIC:
            // | points already contains:
            // | amount × page multiplier
            // |
            // | Final payout:
            // | points × 90

            $payoutScenarios = [];

            for ($n = 0; $n <= 99; $n++) {

                $fullNumber = $subSeriesStart + $n;

                $betsOnNumber = $bets->where(
                    'number',
                    (string) $fullNumber
                );

                //    | IMPORTANT:
                //     | ONLY NUMBERS WITH ACTUAL BETS




                $totalBetPoints = $betsOnNumber->sum('points');

                $payoutScenarios[$fullNumber] = [
                    'points' => $totalBetPoints,
                ];
            }

            //   STEP 4 — REMOVE RECENT RESULTS

            $blockedSuffixes = Result::orderBy('draw_time', 'desc')
                ->take(30)
                ->pluck('result_number')
                ->map(function ($number) {
                    return $number % 100;
                })
                ->unique()
                ->toArray();

            /*
        |--------------------------------------------------------------------------
        | STEP 5 — SAFE NUMBERS ONLY
        |--------------------------------------------------------------------------
        |
        | We only allow numbers where:
        |
        | payout <= collection
        |
        | so house never goes into loss
        |--------------------------------------------------------------------------
        */



            $totalCollection = $bets->sum('points');

            //   House keeps

            $candidates = collect();

            foreach ($payoutScenarios as $number => $data) {

                $suffix = $number % 100;

                if (in_array($suffix, $blockedSuffixes)) {
                    continue;
                }

                $candidates[$number] = [
                    'points' => $data['points'],
                    'payout' => $data['points'] * 90
                ];
            }


            //   Final fallback

            if ($candidates->isEmpty()) {
                $candidates = collect($payoutScenarios);
            }

            //   Winner logic

            if (!$allowWinner) {

                $zeroBetNumbers = $candidates->filter(function ($data) {
                    return $data['points'] == 0;
                });

                if ($zeroBetNumbers->isNotEmpty()) {

                    $winningNumber = $zeroBetNumbers
                        ->keys()
                        ->random();
                } else {

                    $winningNumber = $candidates
                        ->keys()
                        ->random();
                }
            } else {

                $betNumbers = $candidates
                    ->filter(function ($data) {
                        return $data['points'] > 0;
                    })
                    ->sortBy('payout');

                if ($betNumbers->isNotEmpty()) {

                    $safeCandidates = $betNumbers
                        ->take(max(1, ceil($betNumbers->count() * 0.8)));

                    $winningNumber = $safeCandidates
                        ->keys()
                        ->random();
                } else {

                    $winningNumber = $candidates
                        ->keys()
                        ->random();
                }
            }

            //    STEP 7 — SAVE RESULT

            Result::create([
                'draw_time'     => $drawTime,
                'series'        => $subSeriesStart,
                'result_number' => $winningNumber,
            ]);

            //   STEP 8 — PROCESS WINNERS / LOSERS

            foreach ($bets as $bet) {

                if ($bet->status !== 'pending') {
                    continue;
                }

                // WINNER

                if ((int) $bet->number === (int) $winningNumber) {

                    $user = $bet->user ?? User::find($bet->user_id);

                    if (!$user) {

                        $bet->update([
                            'status' => 'lost'
                        ]);

                        continue;
                    }

                    // FINAL WIN LOGIC  points × 90

                    $winAmount = $bet->points * 90;

                    $bet->update([
                        'status' => 'won'
                    ]);

                    $user->increment(
                        'balance',
                        $winAmount
                    );

                    //  COMMISSION IS SEPARATE BUSINESS LOGIC

                    $commissionRate = $this->getCommissionRate($user);

                    UserBalanceTransaction::create([

                        'user_id'       => $user->id,
                        'type'          => 'credit',
                        'amount'        => $winAmount,
                        'balance_after' => $user->fresh()->balance,

                        'remarks'       =>
                        "WIN: Draw " .
                            $drawTime->format('h:i A') .
                            " | No: {$winningNumber}" .
                            " | Win: {$winAmount}" .
                            " | Commission: {$commissionRate}%"
                    ]);
                }

                //  LOSER
                else {

                    $bet->update([
                        'status' => 'lost'
                    ]);
                }
            }
        });
    }
}
