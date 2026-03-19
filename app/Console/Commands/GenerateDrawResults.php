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
    protected $signature = 'draw:generate-results';
    protected $description = 'Automate 15-min draw results for every 100-number range';

    // Default commission if user has null or 0 commission set
    const DEFAULT_COMMISSION = 5;

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
                    // Log error but NEVER crash the whole command
                    // Continue to next sub-series
                    $this->error("Error processing $subSeriesStart: " . $e->getMessage());
                    \Illuminate\Support\Facades\Log::error("DrawResult Error [$subSeriesStart]: " . $e->getMessage());
                }
            }
        }

        $this->info("Execution Completed!");
    }

    private function processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime)
    {
        DB::transaction(function () use ($mainSeries, $subSeriesStart, $drawTime) {
            $subSeriesEnd = $subSeriesStart + 99;

            // 1. Get all bets for this range
            $bets = Bet::where('series_id', $mainSeries->id)
                ->where('draw_time', $drawTime)
                ->where('number', '>=', $subSeriesStart)
                ->where('number', '<=', $subSeriesEnd)
                ->with('user') // eager load user to avoid N+1 and null issues
                ->lockForUpdate()
                ->get();

            // 2. Build number stats — total points bet on each number
            $numberStats = [];
            for ($n = 0; $n <= 99; $n++) {
                $fullNumber               = $subSeriesStart + $n;
                $numberStats[$fullNumber] = $bets->where('number', (string) $fullNumber)->sum('points');
            }

            // 3. WIN RATIO LOGIC — 60% user wins, 40% house wins
            $rand = mt_rand(1, 100);

            if ($rand <= 60) {
                // 60% — weighted random from numbers that HAVE bets
                $bettedNumbers = array_filter($numberStats, fn($pts) => $pts > 0);

                if (!empty($bettedNumbers)) {
                    $pool = [];
                    foreach ($bettedNumbers as $number => $points) {
                        $weight = max(1, (int) ceil($points));
                        for ($w = 0; $w < $weight; $w++) {
                            $pool[] = $number;
                        }
                    }
                    $winningNumber = $pool[array_rand($pool)];
                } else {
                    // No bets placed — pick fully random
                    $winningNumber = $subSeriesStart + mt_rand(0, 99);
                }
            } else {
                // 40% — house wins, pick number with LEAST bets
                asort($numberStats);
                $minPoints   = reset($numberStats);
                $bestNumbers = array_keys(array_filter($numberStats, fn($pts) => $pts == $minPoints));
                $winningNumber = $bestNumbers[array_rand($bestNumbers)];
            }

            // 4. Create Result record
            Result::create([
                'draw_time'     => $drawTime,
                'series'        => $subSeriesStart,
                'result_number' => $winningNumber,
            ]);

            // 5. Process bets — pay winners, mark losers
            $sortedBets = $bets->sortByDesc('points');

            foreach ($sortedBets as $bet) {
                // Safety: skip already processed bets
                if ($bet->status !== 'pending') continue;

                if ((int) $bet->number === (int) $winningNumber) {

                    // ── User safety: fetch fresh if relationship is null ──
                    $user = $bet->user ?? User::find($bet->user_id);

                    if (!$user) {
                        // User completely missing — mark as lost and move on, never crash
                        $this->warn("User not found for bet ID {$bet->id}. Marking as lost.");
                        $bet->update(['status' => 'lost']);
                        continue;
                    }

                    // ── Commission: default 5% if null or 0 ──
                    // null  → 5% default
                    // 0     → 5% default
                    // 10    → 10%
                    // 30    → 30%
                    $rawCommission  = $user->commision;
                    $commissionRate = (!is_null($rawCommission) && $rawCommission > 0)
                        ? (float) $rawCommission
                        : self::DEFAULT_COMMISSION;

                    $netMultiplier = 100 - $commissionRate;
                    $winAmount     = $bet->points * $netMultiplier;

                    $bet->update(['status' => 'won']);
                    $user->increment('balance', $winAmount);

                    UserBalanceTransaction::create([
                        'user_id'       => $user->id,
                        'type'          => 'credit',
                        'amount'        => $winAmount,
                        'balance_after' => $user->fresh()->balance,
                        'remarks'       => "WIN: Draw " . $drawTime->format('h:i A') .
                            " | No: $winningNumber" .
                            " | {$bet->points} x {$netMultiplier} = Rs.{$winAmount}" .
                            " | Commission: {$commissionRate}%",
                    ]);
                } else {
                    $bet->update(['status' => 'lost']);
                }
            }

            $this->info("Processed $subSeriesStart-$subSeriesEnd: Result $winningNumber");
        });
    }
}
