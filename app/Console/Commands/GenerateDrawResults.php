<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SeriesMaster;
use App\Models\Bet;
use App\Models\Result;
use App\Models\UserBalanceTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateDrawResults extends Command
{
    protected $signature = 'draw:generate-results';
    protected $description = 'Automate 15-min draw results for every 100-number range';

    public function handle()
    {
        $now = Carbon::now();
        $startTime = config('app.draw_start');
        $endTime = config('app.draw_end');

        if ($now->format('H:i') < $startTime || $now->format('H:i') > $endTime) {
            $this->info("Outside draw hours ($startTime - $endTime). Skipping.");
            return;
        }

        $minutes = $now->minute;
        $targetMinute = floor($minutes / 15) * 15;
        $drawTime = $now->copy()->minute($targetMinute)->second(0);

        $this->info("Generating results for Draw Time: " . $drawTime->format('Y-m-d H:i:s'));

        $seriesList = SeriesMaster::all();

        foreach ($seriesList as $mainSeries) {
            // Loop 10 times for each 100-number row (1000, 1100, 1200...)
            for ($i = 0; $i < 10; $i++) {
                $subSeriesStart = (int)$mainSeries->start + ($i * 100);

                // FIXED: Changed '.' to '->' before exists()
                $exists = Result::where('draw_time', $drawTime)
                    ->where('series', $subSeriesStart)
                    ->exists();

                if ($exists) {
                    $this->line("Result already exists for range starting $subSeriesStart. Skipping.");
                    continue;
                }

                $this->processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime);
            }
        }

        $this->info("Execution Completed!");
    }

    private function processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime)
    {
        DB::transaction(function () use ($mainSeries, $subSeriesStart, $drawTime) {
            $subSeriesEnd = $subSeriesStart + 99;

            // 1. Get ALL bets for this range, even if they aren't "pending" to avoid double processing
            $bets = Bet::where('series_id', $mainSeries->id)
                ->where('draw_time', $drawTime)
                ->where('number', '>=', $subSeriesStart)
                ->where('number', '<=', $subSeriesEnd)
                ->lockForUpdate() // LOCK rows so no other process can touch them during payment
                ->get();

            // 2. Liability Logic (Your existing logic is correct)
            $numberStats = [];
            for ($n = 0; $n <= 99; $n++) {
                $fullNumber = $subSeriesStart + $n;
                $numberStats[$fullNumber] = $bets->where('number', (string)$fullNumber)->sum('points');
            }

            asort($numberStats);
            $minPoints = reset($numberStats);
            $bestNumbers = array_keys(array_filter($numberStats, fn($pts) => $pts == $minPoints));
            $winningNumber = $bestNumbers[array_rand($bestNumbers)];

            // 3. Create Result record
            Result::create([
                'draw_time' => $drawTime,
                'series' => $subSeriesStart,
                'result_number' => $winningNumber,
            ]);

            // 4. Update Statuses & Hierarchical Payment

            $sortedBets = $bets->sortByDesc('points');

            foreach ($sortedBets  as $bet) {
                // SKIP if already processed (Safety First)
                if ($bet->status !== 'pending') continue;

                if ($bet->number == $winningNumber) {
                    // WINNER LOGIC
                    $winAmount = $bet->points * 90;

                    // Update Bet Status
                    $bet->update(['status' => 'won']);

                    // Pay the User (Retailer/Agent/Admin)
                    $user = $bet->user;
                    $user->increment('balance', $winAmount);

                    // Create Ledger Entry for Audit Trail
                    UserBalanceTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'credit',
                        'amount' => $winAmount,
                        'balance_after' => $user->balance,
                        'remarks' => "WIN: Draw " . $drawTime->format('h:i A') . " | No: $winningNumber",
                    ]);
                } else {
                    // LOSER LOGIC
                    $bet->update(['status' => 'lost']);
                }
            }

            $this->info("Processed $subSeriesStart-$subSeriesEnd: Result $winningNumber");
        });
    }
}
