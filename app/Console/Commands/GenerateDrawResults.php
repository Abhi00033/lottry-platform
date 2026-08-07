<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use App\Models\SeriesMaster;
use App\Models\Bet;
use App\Models\Result;
use App\Models\UserBalanceTransaction;
use App\Models\User;
use App\Models\BetTicket;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class GenerateDrawResults extends Command implements Isolatable
{
    protected $signature   = 'draw:generate-results';
    protected $description = 'Automate 15-min draw results with catch-up logic and guaranteed profit control';

    const DEFAULT_COMMISSION = 0;

    public function handle()
    {
        $now       = Carbon::now();
        $startTime = config('app.draw_start'); // e.g., '09:00'
        $endTime   = config('app.draw_end');   // e.g., '22:00'

        $todayStart = Carbon::parse($now->format('Y-m-d') . ' ' . $startTime);
        $todayEnd   = Carbon::parse($now->format('Y-m-d') . ' ' . $endTime);

        // Outside operating hours check
        if ($now->lessThan($todayStart)) {
            $this->info("Before start time ($startTime). Skipping.");
            return;
        }

        // Upper limit cap for draw processing
        $latestPossible = $now->greaterThan($todayEnd) ? $todayEnd->copy() : $now->copy();
        $targetMinute   = floor($latestPossible->minute / 15) * 15;
        $latestDrawTime = $latestPossible->second(0)->minute($targetMinute);

        $seriesList = SeriesMaster::all();

        if ($seriesList->isEmpty()) {
            $this->warn("No series found. Exiting.");
            return;
        }

        // -------------------------------------------------------------------------
        // CATCH-UP LOOP: Walk through all 15-min slots from start of day to current slot
        // -------------------------------------------------------------------------
        $currentDrawToProcess = $todayStart->copy();

        while ($currentDrawToProcess->lessThanOrEqualTo($latestDrawTime)) {

            foreach ($seriesList as $mainSeries) {
                for ($i = 0; $i < 10; $i++) {
                    $subSeriesStart = (int) $mainSeries->start + ($i * 100);

                    $exists = Result::where('draw_time', $currentDrawToProcess)
                        ->where('series', $subSeriesStart)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $this->warn("Processing draw for {$subSeriesStart} at {$currentDrawToProcess->format('Y-m-d H:i:s')}");

                    try {
                        $this->processSubSeriesResult(
                            $mainSeries,
                            $subSeriesStart,
                            $currentDrawToProcess->copy()
                        );
                    } catch (\Throwable $e) {
                        $this->error("Error processing {$subSeriesStart} at {$currentDrawToProcess->format('H:i')}: " . $e->getMessage());
                        \Illuminate\Support\Facades\Log::error("DrawResult Error [{$subSeriesStart} @ {$currentDrawToProcess}]: " . $e->getMessage());
                    }
                }
            }

            $currentDrawToProcess->addMinutes(15);
        }

        $this->info("Execution Completed!");
    }

    private function processSubSeriesResult($mainSeries, $subSeriesStart, $drawTime)
    {
        DB::transaction(function () use ($mainSeries, $subSeriesStart, $drawTime) {

            // STEP 1 — FETCH ALL PENDING BETS
            $bets = Bet::where('series_id', $mainSeries->id)
                ->where('draw_time', $drawTime)
                ->where('status', 'pending')
                ->where('series_group', $subSeriesStart)
                ->with('user')
                ->lockForUpdate()
                ->get();

            // ---------------------------------------------------------------------
            // SCENARIO A: NO BETS PLACED ANYWHERE IN THIS SUB-SERIES
            // ---------------------------------------------------------------------
            if ($bets->isEmpty()) {
                // Fetch blocked suffixes relative to drawTime
                $blockedSuffixes = Result::where('series', $subSeriesStart)
                    ->where('draw_time', '<', $drawTime)
                    ->orderByDesc('draw_time')
                    ->take(5)
                    ->pluck('result_number')
                    ->map(fn($n) => $n % 100)
                    ->unique()
                    ->toArray();

                do {
                    $randomSuffix = rand(0, 99);
                } while (in_array($randomSuffix, $blockedSuffixes) && count($blockedSuffixes) < 100);

                $randomNumber = $subSeriesStart + $randomSuffix;

                Result::create([
                    'draw_time'     => $drawTime,
                    'series'        => $subSeriesStart,
                    'result_number' => $randomNumber,
                ]);

                $this->info("No bets for {$subSeriesStart}. Random Result: {$randomNumber}");
                return;
            }

            // ---------------------------------------------------------------------
            // SCENARIO B: BETS EXIST — CALCULATE PAYOUT SCENARIOS
            // ---------------------------------------------------------------------

            // STEP 2 — TOTAL COLLECTION
            $totalCollection = $bets->sum('points');

            // STEP 3 — CALCULATE PAYOUT PER NUMBER (points × 90)
            $payoutScenarios = [];
            for ($n = 0; $n <= 99; $n++) {
                $fullNumber = $subSeriesStart + $n;
                $betsOnNumber = $bets->where('number', (string) $fullNumber);
                $totalBetPoints = $betsOnNumber->sum('points');

                $payoutScenarios[$fullNumber] = [
                    'points' => $totalBetPoints,
                    'payout' => $totalBetPoints * 90,
                ];
            }

            // STEP 4 — FETCH RECENT DRAW SUFFIXES (Relative to target $drawTime)
            $blockedSuffixes = Result::where('series', $subSeriesStart)
                ->where('draw_time', '<', $drawTime)
                ->orderByDesc('draw_time')
                ->take(5)
                ->pluck('result_number')
                ->map(fn($n) => $n % 100)
                ->unique()
                ->toArray();

            // STEP 5 — CATEGORIZE ALL 100 NUMBERS
            $idealWinners      = collect(); // Players win AND House Profit >= 20%
            $acceptableWinners = collect(); // Players win AND House Profit >= 0% (No house loss)
            $zeroBetNumbers    = collect(); // Numbers with 0 bets placed
            $lossCandidates    = collect(); // House loses money (Payout > Collection)

            foreach ($payoutScenarios as $number => $data) {
                $suffix = $number % 100;

                // Filter out recently drawn suffixes
                if (in_array($suffix, $blockedSuffixes)) {
                    continue;
                }

                $houseProfit = $totalCollection - $data['payout'];

                $candidate = [
                    'number'       => $number,
                    'points'       => $data['points'],
                    'payout'       => $data['payout'],
                    'house_profit' => $houseProfit,
                ];

                if ($data['points'] > 0) {
                    if ($houseProfit >= ($totalCollection * 0.20)) {
                        $idealWinners->push($candidate);
                    } elseif ($houseProfit >= 0) {
                        $acceptableWinners->push($candidate);
                    } else {
                        $lossCandidates->push($candidate);
                    }
                } else {
                    $zeroBetNumbers->push($candidate);
                }
            }

            // FALLBACK: If anti-repeat filter blocked all numbers, re-evaluate without filter
            if ($idealWinners->isEmpty() && $acceptableWinners->isEmpty() && $zeroBetNumbers->isEmpty()) {
                foreach ($payoutScenarios as $number => $data) {
                    $houseProfit = $totalCollection - $data['payout'];
                    $candidate = [
                        'number'       => $number,
                        'points'       => $data['points'],
                        'payout'       => $data['payout'],
                        'house_profit' => $houseProfit,
                    ];

                    if ($data['points'] > 0) {
                        if ($houseProfit >= 0) {
                            $acceptableWinners->push($candidate);
                        } else {
                            $lossCandidates->push($candidate);
                        }
                    } else {
                        $zeroBetNumbers->push($candidate);
                    }
                }
            }

            // ---------------------------------------------------------------------
            // STEP 6 — SMART WINNER SELECTION HIERARCHY
            // ---------------------------------------------------------------------
            if ($idealWinners->isNotEmpty()) {
                $winningNumber = $idealWinners->random()['number'];
            } elseif ($acceptableWinners->isNotEmpty()) {
                // Guarantees player wins when whole range is covered (Profit >= 0)
                $winningNumber = $acceptableWinners->random()['number'];
            } elseif ($zeroBetNumbers->isNotEmpty()) {
                $winningNumber = $zeroBetNumbers->random()['number'];
            } else {
                $winningNumber = $lossCandidates->sortBy('payout')->first()['number'];
            }

            // STEP 7 — SAVE RESULT
            Result::create([
                'draw_time'     => $drawTime,
                'series'        => $subSeriesStart,
                'result_number' => $winningNumber,
            ]);

            // STEP 8 — PROCESS WINNERS & LOSERS (ONLY UPDATE TICKET CLAIM AMOUNT, NOT USER BALANCE)
            foreach ($bets as $bet) {
                if ($bet->status !== 'pending') {
                    continue;
                }

                if ((int) $bet->number === (int) $winningNumber) {
                    $user = $bet->user ?? User::find($bet->user_id);
                    if (!$user) {
                        $bet->update(['status' => 'lost']);
                        continue;
                    }

                    $winAmount = $bet->points * 90;

                    $bet->update(['status' => 'won']);

                    // Prize amount is added to BetTicket ONLY.
                    // User wallet balance is untouched until claimed on the Claim Page.
                    if ($bet->ticket_id) {
                        BetTicket::where('id', $bet->ticket_id)
                            ->increment('claim_amount', $winAmount);
                    }
                } else {
                    $bet->update(['status' => 'lost']);
                }
            }
        });
    }

    private function getCommissionRate($user): float
    {
        if ($user && !is_null($user->commision) && (float) $user->commision > 0) {
            return (float) $user->commision;
        }
        return self::DEFAULT_COMMISSION;
    }
}
