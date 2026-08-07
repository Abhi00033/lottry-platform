<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserBalanceTransaction;
use Illuminate\View\View;
use App\Models\BetTicket;
use Carbon\Carbon;

class ClaimController extends Controller
{
    public function index(): View
    {
        $selectedDate = request('date', now()->toDateString());
        $ticketNo     = strtoupper(trim(request('ticket_no', '')));

        $query = BetTicket::with([
            'user',
            'bets',
            'transaction',
            'claimedBy'
        ]);

        if (!empty($ticketNo)) {
            $query->where('ticket_no', $ticketNo);
        } else {
            $query->whereDate('draw_date', $selectedDate)
                ->latest('draw_time')
                ->latest('id');
        }

        $tickets = $query->get()->map(function ($ticket) {
            $drawCompleted = now()->greaterThanOrEqualTo(
                Carbon::parse(
                    $ticket->draw_date->format('Y-m-d') . ' ' .
                        Carbon::parse($ticket->draw_time)->format('H:i:s')
                )
            );

            $hasWon = (float) $ticket->claim_amount > 0;

            if (!$drawCompleted) {
                $displayStatus = 'pending';
            } elseif ($hasWon) {
                $displayStatus = $ticket->claim_status === 'claimed'
                    ? 'claimed'
                    : 'winner';
            } else {
                $displayStatus = 'lost';
            }

            $ticket->display_status = $displayStatus;
            return $ticket;
        });

        $searchedTicket = $tickets->first();

        return view('lottry_pages.claim.index', compact('tickets', 'searchedTicket'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'ticket_no' => 'required|string'
        ]);

        $ticket = BetTicket::with([
            'user',
            'bets',
            'transaction',
            'claimedBy'
        ])
            ->where('ticket_no', strtoupper(trim($request->ticket_no)))
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket / Transaction Number not found.'
            ], 404);
        }

        $drawCompleted = now()->greaterThanOrEqualTo(
            Carbon::parse(
                $ticket->draw_date->format('Y-m-d') . ' ' .
                    Carbon::parse($ticket->draw_time)->format('H:i:s')
            )
        );

        $hasWon = (float) $ticket->claim_amount > 0;

        if (!$drawCompleted) {
            $displayStatus = 'pending';
        } elseif ($hasWon) {
            $displayStatus = $ticket->claim_status === 'claimed'
                ? 'claimed'
                : 'winner';
        } else {
            $displayStatus = 'lost';
        }

        $ticket->display_status = $displayStatus;

        // Filter bets: Show ONLY winning numbers if ticket won/claimed, or pending/all if pending
        $winningBets = $ticket->bets->filter(function ($bet) use ($drawCompleted) {
            if (!$drawCompleted) {
                return true; // Show pending bets
            }
            return in_array($bet->status, ['won', 'claimed']); // Show ONLY winning numbers
        });

        // If no winning bets found (it was a losing ticket), map all bets
        $betsToMap = $winningBets->isNotEmpty() ? $winningBets : $ticket->bets;

        $ticket->formatted_bets = $betsToMap->map(function ($bet) use ($drawCompleted) {
            $betWinAmount = in_array($bet->status, ['won', 'claimed'])
                ? ($bet->points * 90)
                : 0;

            $statusText = !$drawCompleted
                ? 'Pending'
                : (in_array($bet->status, ['won', 'claimed']) ? 'Winner' : 'Lost');

            return [
                'number'     => str_pad($bet->number, 4, '0', STR_PAD_LEFT),
                'qty'        => $bet->qty,
                'points'     => $bet->points,
                'status'     => $statusText,
                'bet_time'   => $bet->created_at ? $bet->created_at->format('h:i:s A') : '-',
                'win_amount' => $betWinAmount,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'ticket'  => $ticket
        ]);
    }

    public function processClaim(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:bet_tickets,id',
        ]);

        DB::beginTransaction();

        try {
            $ticket = BetTicket::with([
                'user',
                'bets',
                'claimedBy'
            ])
                ->lockForUpdate()
                ->findOrFail($request->ticket_id);

            // 1. Draw completed check
            $drawCompleted = now()->greaterThanOrEqualTo(
                Carbon::parse(
                    $ticket->draw_date->format('Y-m-d') . ' ' .
                        Carbon::parse($ticket->draw_time)->format('H:i:s')
                )
            );

            if (!$drawCompleted) {
                DB::rollBack();
                return response()->json([
                    'success'            => false,
                    'is_already_claimed' => false,
                    'is_pending'         => true,
                    'message'            => 'Draw result is not declared yet.'
                ], 200);
            }

            // 2. Already Claimed Check
            if ($ticket->claim_status === 'claimed') {
                DB::rollBack();

                $claimedAtFormatted = $ticket->claimed_at
                    ? Carbon::parse($ticket->claimed_at)->format('d M Y, h:i A')
                    : 'N/A';
                $claimedByUsername = $ticket->claimedBy->username ?? 'Retailer';

                return response()->json([
                    'success'            => true,
                    'is_already_claimed' => true,
                    'message'            => "This ticket was ALREADY CLAIMED on {$claimedAtFormatted}.",
                    'claim_amount'       => number_format($ticket->claim_amount, 2),
                    'claimed_at'         => $claimedAtFormatted,
                    'claimed_by'         => $claimedByUsername,
                ], 200);
            }

            // 3. Losing Ticket Check
            if ((float) $ticket->claim_amount <= 0) {
                DB::rollBack();
                return response()->json([
                    'success'            => false,
                    'is_already_claimed' => false,
                    'is_lost'            => true,
                    'message'            => 'This ticket did not win.'
                ], 200);
            }

            // 4. AUTOMATIC FIRST TIME CLAIM — Credit Balance
            $user = $ticket->user;
            $user->increment('balance', $ticket->claim_amount);
            $user = $user->fresh();

            // Record transaction audit
            UserBalanceTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'credit',
                'amount'        => $ticket->claim_amount,
                'balance_after' => $user->balance,
                'remarks'       => 'Auto-claimed Prize for Ticket #' . $ticket->ticket_no,
            ]);

            // Update ticket claim status
            $ticket->claim_status = 'claimed';
            $ticket->claimed_at   = now();
            $ticket->claimed_by   = auth()->id();
            $ticket->save();

            // Update winning bets status
            $ticket->bets()
                ->where('status', 'won')
                ->update(['status' => 'claimed']);

            DB::commit();

            return response()->json([
                'success'      => true,
                'is_first_win' => true,
                'win_amount'   => number_format($ticket->claim_amount, 2),
                'message'      => 'YOU WIN ₹' . number_format($ticket->claim_amount, 2),
                'new_balance'  => number_format($user->balance, 2),
                'claimed_at'   => $ticket->claimed_at->format('d M Y, h:i A'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Processing Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
