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
    protected $description = 'Automate 15-min draw results with catch-up logic and balanced, fair profit control';

    const DEFAULT_COMMISSION = 0;

    // ======================= TUNABLE SETTINGS =======================
    const PAYOUT_MULT        = 90;     // win = points x 90
    const TARGET_MARGIN      = 0.10;   // house aims to keep ~10% of a slot's collection
    const HIT_RATE           = 0.80;   // chance a bet number wins when an affordable one exists
    const HIT_RATE_BOOST     = 0.90;   // hit rate when the day has a healthy surplus
    const BIG_WIN_CHANCE     = 0.25;   // chance to pick from the bigger-payout half
    const BLOCK_LAST_N       = 3;      // don't repeat the last N result suffixes

    // Carry from previous slots (clamped, never unlimited)
    const CARRY_MAX          = 0.30;   // surplus can add up to 30% of the slot collection
    const DEBT_RECOVERY      = 0.15;   // a past loss reduces the slot budget by up to 15%

    // Per-draw safety
    const DRAW_CAP           = 1.3;    // one sub-series pays at most 130% of its own collection

    // Rare "loss slot" (decided ONCE per slot)
    const LOSS_SLOT_CHANCE   = 0.04;   // ~1 slot in 25
    const MIN_DAY_HEALTH     = 0.05;   // allowed only if the day's house share so far >= 5%
    const LOSS_ALLOWANCE_PCT = 0.10;   // allowance = 10% of the slot collection ...
    const MAX_LOSS_ALLOWANCE = 2000;   // ... but never more than this many points
    // ================================================================

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

            // STEP 1 — FETCH ALL PENDING BETS (unchanged)
            $bets = Bet::where('series_id', $mainSeries->id)
                ->where('draw_time', $drawTime)
                ->where('status', 'pending')
                ->where('series_group', $subSeriesStart)
                ->with('user')
                ->lockForUpdate()
                ->get();

            $blockedSuffixes = $this->getBlockedSuffixes($subSeriesStart, $drawTime);

            // ---------------------------------------------------------------------
            // SCENARIO A: NO BETS PLACED IN THIS SUB-SERIES -> random number
            // ---------------------------------------------------------------------
            if ($bets->isEmpty()) {
                $allowed = array_values(array_diff(range(0, 99), $blockedSuffixes));
                if (empty($allowed)) {
                    $allowed = range(0, 99);
                }

                $randomNumber = $subSeriesStart + $allowed[array_rand($allowed)];

                Result::create([
                    'draw_time'     => $drawTime,
                    'series'        => $subSeriesStart,
                    'result_number' => $randomNumber,
                ]);

                $this->info("No bets for {$subSeriesStart}. Random Result: {$randomNumber}");
                return;
            }

            // ---------------------------------------------------------------------
            // SCENARIO B: BETS EXIST
            // ---------------------------------------------------------------------

            // STEP 2 — POINTS PER NUMBER + COLLECTION OF THIS SUB-SERIES
            $pointsByNumber = [];
            foreach ($bets as $bet) {
                $n = (int) $bet->number;
                $pointsByNumber[$n] = ($pointsByNumber[$n] ?? 0) + $bet->points;
            }
            $totalCollection = array_sum($pointsByNumber);

            // STEP 3 — SLOT BUDGET
            $prev = $this->getPoolStats($drawTime);   // settled bets of PREVIOUS slots today
            $slot = $this->getSlotStats($drawTime);   // this slot, all series (pending+won+lost)

            // Carry from earlier slots today (surplus or debt), clamped
            $prevSurplus = ($prev['collection'] * (1 - self::TARGET_MARGIN)) - $prev['paid'];
            $carry = max(
                -$slot['collection'] * self::DEBT_RECOVERY,
                min($prevSurplus, $slot['collection'] * self::CARRY_MAX)
            );

            // Day health so far (before this slot)
            $dayHealth = $prev['collection'] > 0
                ? ($prev['collection'] - $prev['paid']) / $prev['collection']
                : 1;

            // Rare loss slot: decided once per slot, only when the day is healthy
            $lossSlot  = $this->isLossSlot($drawTime) && $dayHealth >= self::MIN_DAY_HEALTH;
            $allowance = $lossSlot
                ? min(self::MAX_LOSS_ALLOWANCE, $slot['collection'] * self::LOSS_ALLOWANCE_PCT)
                : 0;

            $slotBudget = ($slot['collection'] * (1 - self::TARGET_MARGIN)) + $carry + $allowance;

            // Reserve the unavoidable minimum payouts of the OTHER pending sub-series
            // (those where every number has a bet, so the house cannot pick an unbet number)
            $reserve = $this->getReserve($drawTime, $subSeriesStart);

            $budget = $slotBudget - $slot['paid'] - $reserve;

            // Per-draw cap
            $budget = min($budget, $totalCollection * self::DRAW_CAP);
            $budget = max(0, $budget);

            // STEP 4 — CLASSIFY ALL 100 NUMBERS
            $affordable  = [];   // number => payout
            $zeroBet     = [];   // numbers nobody bet on
            $cheapestBet = null; // last-resort fallback (ignores block list)

            for ($n = 0; $n <= 99; $n++) {
                $full      = $subSeriesStart + $n;
                $points    = $pointsByNumber[$full] ?? 0;
                $isBlocked = in_array($n, $blockedSuffixes, true);

                if ($points <= 0) {
                    if (!$isBlocked) {
                        $zeroBet[] = $full;
                    }
                    continue;
                }

                $payout = $points * self::PAYOUT_MULT;

                if ($cheapestBet === null || $payout < $cheapestBet['payout']) {
                    $cheapestBet = ['number' => $full, 'payout' => $payout];
                }

                if (!$isBlocked && $payout <= $budget) {
                    $affordable[$full] = $payout;
                }
            }

            // STEP 5 — CHOOSE WINNER
            $hitRate = ($carry >= $slot['collection'] * self::CARRY_MAX)
                ? self::HIT_RATE_BOOST
                : self::HIT_RATE;

            $playerWins = !empty($affordable) && $this->chance($hitRate);

            if ($playerWins) {
                $winningNumber = $this->pickWinner($affordable);
                $mode = 'PLAYER WIN';
            } elseif (!empty($zeroBet)) {
                $winningNumber = $zeroBet[array_rand($zeroBet)];
                $mode = 'HOUSE KEEPS (zero-bet number)';
            } elseif (!empty($affordable)) {
                $winningNumber = $this->pickWinner($affordable);
                $mode = 'PLAYER WIN (no zero-bet number left)';
            } else {
                // Every number has bets and none is affordable -> smallest possible payout
                $winningNumber = $cheapestBet['number'];
                $mode = 'FORCED CHEAPEST';
            }

            $this->info(sprintf(
                "Series %d | collect=%s slotBudget=%s paid=%s reserve=%s budget=%s%s | %s -> %d",
                $subSeriesStart,
                $totalCollection,
                round($slotBudget),
                round($slot['paid']),
                round($reserve),
                round($budget),
                $lossSlot ? ' [LOSS SLOT]' : '',
                $mode,
                $winningNumber
            ));

            // STEP 6 — SAVE RESULT (unchanged)
            Result::create([
                'draw_time'     => $drawTime,
                'series'        => $subSeriesStart,
                'result_number' => $winningNumber,
            ]);

            // STEP 7 — PROCESS WINNERS & LOSERS (unchanged: only BetTicket claim_amount, not wallet)
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

                    $winAmount = $bet->points * self::PAYOUT_MULT;

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

    /**
     * Pick a winner from affordable numbers.
     * Mostly random, sometimes from the bigger-payout half so players see real wins.
     */
    private function pickWinner(array $affordable): int
    {
        arsort($affordable); // biggest payout first
        $numbers = array_keys($affordable);

        if (count($numbers) > 1 && $this->chance(self::BIG_WIN_CHANCE)) {
            $top = array_slice($numbers, 0, (int) ceil(count($numbers) / 2));
            return (int) $top[array_rand($top)];
        }

        return (int) $numbers[array_rand($numbers)];
    }

    /**
     * Settled totals of PREVIOUS slots today (all series).
     */
    private function getPoolStats(Carbon $drawTime): array
    {
        $dayStart = $drawTime->copy()->startOfDay();

        $row = Bet::whereIn('status', ['won', 'lost'])
            ->where('draw_time', '>=', $dayStart)
            ->where('draw_time', '<', $drawTime)
            ->selectRaw("COALESCE(SUM(points), 0) as collection,
                         COALESCE(SUM(CASE WHEN status = 'won' THEN points ELSE 0 END), 0) as won_points")
            ->first();

        return [
            'collection' => (float) ($row->collection ?? 0),
            'paid'       => (float) ($row->won_points ?? 0) * self::PAYOUT_MULT,
        ];
    }

    /**
     * Totals for THIS slot across all series:
     * collection = pending + won + lost, paid = wins already decided in this slot.
     */
    private function getSlotStats(Carbon $drawTime): array
    {
        $row = Bet::whereIn('status', ['pending', 'won', 'lost'])
            ->where('draw_time', $drawTime)
            ->selectRaw("COALESCE(SUM(points), 0) as collection,
                         COALESCE(SUM(CASE WHEN status = 'won' THEN points ELSE 0 END), 0) as won_points")
            ->first();

        return [
            'collection' => (float) ($row->collection ?? 0),
            'paid'       => (float) ($row->won_points ?? 0) * self::PAYOUT_MULT,
        ];
    }

    /**
     * Minimum payout that the OTHER still-pending sub-series of this slot will
     * be forced to pay (only sub-series where all 100 numbers have a bet).
     */
    private function getReserve(Carbon $drawTime, int $excludeGroup): float
    {
        $rows = Bet::where('draw_time', $drawTime)
            ->where('status', 'pending')
            ->where('series_group', '!=', $excludeGroup)
            ->selectRaw('series_group, number, SUM(points) as pts')
            ->groupBy('series_group', 'number')
            ->get();

        $groups = [];
        foreach ($rows as $r) {
            $groups[$r->series_group][(int) $r->number] = (float) $r->pts;
        }

        $reserve = 0;
        foreach ($groups as $numbers) {
            if (count($numbers) >= 100) {
                $reserve += min($numbers) * self::PAYOUT_MULT;
            }
        }

        return $reserve;
    }

    /** One decision per slot (same answer for every sub-series, stable across catch-up runs). */
    private function isLossSlot(Carbon $drawTime): bool
    {
        $hash = crc32('lossslot-' . $drawTime->format('Y-m-d H:i'));
        return ($hash % 1000) < (self::LOSS_SLOT_CHANCE * 1000);
    }

    /** Last N result suffixes for this sub-series (anti-repeat). */
    private function getBlockedSuffixes($subSeriesStart, $drawTime): array
    {
        return Result::where('series', $subSeriesStart)
            ->where('draw_time', '<', $drawTime)
            ->orderByDesc('draw_time')
            ->take(self::BLOCK_LAST_N)
            ->pluck('result_number')
            ->map(fn($n) => $n % 100)
            ->unique()
            ->values()
            ->toArray();
    }

    /** true with the given probability (0.0 - 1.0) */
    private function chance(float $probability): bool
    {
        return (mt_rand() / mt_getrandmax()) < $probability;
    }

    private function getCommissionRate($user): float
    {
        if ($user && !is_null($user->commision) && (float) $user->commision > 0) {
            return (float) $user->commision;
        }
        return self::DEFAULT_COMMISSION;
    }
}
