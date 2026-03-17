<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bet;
use App\Models\UserBalanceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BetController extends Controller
{
    // Helper to get the next draw time (15 min intervals)
    private function getNextDrawTime()
    {
        $now = Carbon::now();
        $minutes = $now->minute;
        $nextDraw = $now->copy();

        if ($minutes < 15) {
            $nextDraw->minute(15)->second(0);
        } elseif ($minutes < 30) {
            $nextDraw->minute(30)->second(0);
        } elseif ($minutes < 45) {
            $nextDraw->minute(45)->second(0);
        } else {
            $nextDraw->addHour()->minute(0)->second(0);
        }

        return $nextDraw;
    }

    public function placeBet(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'bets' => 'required|array',
            'total_points' => 'required|numeric|min:1',
            'ticket_price' => 'required|numeric',
            'draw_times' => 'array|nullable',
        ]);

        $user = Auth::user();
        $baseTotalPoints = $request->input('total_points');
        $ticketPrice = $request->input('ticket_price');
        $selectedDrawTimes = $request->input('draw_times');

        // Determine Target Draw Times
        $targetDraws = [];
        if (!empty($selectedDrawTimes)) {
            foreach ($selectedDrawTimes as $timeStr) {
                $targetDraws[] = Carbon::parse($timeStr);
            }
        } else {
            $targetDraws[] = $this->getNextDrawTime();
        }

        $finalTotalCost = $baseTotalPoints * count($targetDraws);

        // 2. Balance Check
        if ($user->balance < $finalTotalCost) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient Balance! Required: ' . number_format($finalTotalCost, 2) . ' Available: ' . number_format($user->balance, 2)
            ], 422);
        }

        // 3. Start Database Transaction
        DB::beginTransaction();

        try {
            // A. Deduct User Balance
            $user->balance -= $finalTotalCost;
            $user->save();

            // B. Create the Wallet Transaction Record FIRST (to get the ID)
            $transaction = UserBalanceTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $finalTotalCost,
                'balance_after' => $user->balance,
                'remarks' => 'Bet Placed for ' . count($targetDraws) . ' draws.',
            ]);

            $betsToInsert = [];

            // C. Prepare Bet Data with transaction_id and pricing details
            foreach ($targetDraws as $drawTime) {
                foreach ($request->bets as $betData) {
                    $seriesStart = str_pad($betData['series_start'], 4, '0', STR_PAD_LEFT);

                    $seriesId = DB::table('series_master')
                        ->where('start', '<=', $seriesStart)
                        ->where('end', '>=', $seriesStart)
                        ->value('id');

                    if (!$seriesId) continue;

                    foreach ($betData['numbers'] as $numberStr => $qty) {
                        if ($qty > 0) {
                            // $individualPoints = $qty * $ticketPrice;
                            $unitPoints = isset($betData['unit_points']) ? (float)$betData['unit_points'] : $ticketPrice;
                            $individualPoints = $qty * $unitPoints;

                            $betsToInsert[] = [
                                'user_id'        => $user->id,
                                'transaction_id' => $transaction->id, // <--- Added: Link to ledger
                                'series_id'      => $seriesId,
                                'series_group'   => $seriesStart,
                                'number'         => $numberStr,
                                'qty'            => $qty,
                                'points'         => $individualPoints,
                                'unit_price'     => $ticketPrice,      // <--- Added: Current price tracking
                                'total_amount'   => $individualPoints, // <--- Added: Line item cost
                                'draw_time'      => $drawTime,
                                'status'         => 'pending',
                                'created_at'     => now(),
                                'updated_at'     => now(),
                            ];
                        }
                    }
                }
            }

            // D. Batch Insert Bets
            foreach (array_chunk($betsToInsert, 500) as $chunk) {
                Bet::insert($chunk);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bets Placed Successfully!',
                'new_balance' => number_format($user->balance, 2),
                'total_points' => $finalTotalCost
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
