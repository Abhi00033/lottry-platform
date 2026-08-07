<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bet;
use App\Models\BetTicket;
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

    private function getDrawNumber(Carbon $drawTime): int
    {
        $startTime = Carbon::parse($drawTime->format('Y-m-d') . ' ' . config('app.draw_start'));

        return (int) ($startTime->diffInMinutes($drawTime) / 15) + 1;
    }

    private function generateTicketNumber(int $ticketId): string
    {
        return 'LT' . now()->format('Ymd') . str_pad($ticketId, 6, '0', STR_PAD_LEFT);
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

        // Cut-off check
        foreach ($targetDraws as $drawTime) {
            $secondsLeft = now()->diffInSeconds($drawTime, false);

            if ($secondsLeft <= 20) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Betting closed. Last 20 seconds before draw.'
                ], 422);
            }
        }

        $finalTotalCost = $baseTotalPoints * count($targetDraws);

        // 2. Start Transaction with Pessimistic Lock for Double-Spend Protection
        DB::beginTransaction();

        try {
            // Lock the user record for update
            $user = User::where('id', Auth::id())->lockForUpdate()->first();

            // Balance Check inside transaction lock
            if ($user->balance < $finalTotalCost) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient Balance! Required: ' . number_format($finalTotalCost, 2) . ' Available: ' . number_format($user->balance, 2)
                ], 422);
            }

            // A. Deduct User Balance
            $user->balance -= $finalTotalCost;
            $user->save();

            // B. Create Wallet Ledger Record
            $transaction = UserBalanceTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $finalTotalCost,
                'balance_after' => $user->balance,
                'remarks' => 'Bet Placed for ' . count($targetDraws) . ' draws.',
            ]);

            $betsToInsert = [];
            $tickets = [];

            // C. Prepare Tickets & Bets
            foreach ($targetDraws as $drawTime) {
                $ticket = BetTicket::create([
                    'ticket_no'      => '',
                    'user_id'        => $user->id,
                    'transaction_id' => $transaction->id,
                    'draw_date'      => $drawTime->toDateString(),
                    'draw_time'      => $drawTime->format('H:i:s'),
                    'ticket_price'   => $ticketPrice,
                    'total_qty'      => 0,
                    'total_amount'   => 0,
                ]);

                $ticket->ticket_no = $this->generateTicketNumber($ticket->id);
                $ticket->save();

                $tickets[] = $ticket;

                $totalQty = 0;
                $totalAmount = 0;

                foreach ($request->bets as $betData) {
                    $seriesStart = str_pad($betData['series_start'], 4, '0', STR_PAD_LEFT);

                    $seriesId = DB::table('series_master')
                        ->where('start', '<=', $seriesStart)
                        ->where('end', '>=', $seriesStart)
                        ->value('id');

                    if (!$seriesId) continue;

                    foreach ($betData['numbers'] as $numberStr => $qty) {
                        if ($qty > 0) {
                            $unitPoints = isset($betData['unit_points']) ? (float)$betData['unit_points'] : $ticketPrice;
                            $individualPoints = $qty * $unitPoints;

                            $totalQty += $qty;
                            $totalAmount += $individualPoints;

                            $betsToInsert[] = [
                                'user_id'        => $user->id,
                                'transaction_id' => $transaction->id,
                                'ticket_id'      => $ticket->id,
                                'series_id'      => $seriesId,
                                'series_group'   => $seriesStart,
                                'number'         => $numberStr,
                                'qty'            => $qty,
                                'points'         => $individualPoints,
                                'unit_price'     => $ticketPrice,
                                'total_amount'   => $individualPoints,
                                'draw_time'      => $drawTime,
                                'status'         => 'pending',
                                'created_at'     => now(),
                                'updated_at'     => now(),
                            ];
                        }
                    }
                }

                $ticket->update([
                    'total_qty'    => $totalQty,
                    'total_amount' => $totalAmount,
                ]);
            }

            // D. Batch Insert Bets
            foreach (array_chunk($betsToInsert, 500) as $chunk) {
                Bet::insert($chunk);
            }

            $ticketIds = collect($tickets)->pluck('id')->implode(',');

            DB::commit();

            return response()->json([
                'status'       => 'success',
                'message'      => 'Bets Placed Successfully!',
                'print_url'    => route('tickets.print.multiple', ['ids' => $ticketIds]),
                'new_balance'  => number_format($user->balance, 2),
                'total_points' => $finalTotalCost,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
