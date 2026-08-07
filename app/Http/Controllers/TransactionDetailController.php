<?php

namespace App\Http\Controllers;

use App\Models\UserBalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Bet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionDetailController extends Controller
{
    public function index(Request $request): View
    {
        $auth = auth()->user();

        // Always show today's transactions
        $today = now()->toDateString();

        $query = Bet::query()
            ->selectRaw("
                draw_time,

                SUM(
                    CASE
                        WHEN status IN ('pending', 'won', 'claimed', 'lost')
                        THEN points
                        ELSE 0
                    END
                ) as sale_points,

                SUM(
                    CASE
                        WHEN status = 'cancelled'
                        THEN points
                        ELSE 0
                    END
                ) as cancel_points,

                SUM(
                    CASE
                        WHEN status IN ('won', 'claimed')
                        THEN points * 90
                        ELSE 0
                    END
                ) as win_points
            ")
            ->whereDate('draw_time', $today);

        // ADMIN (Role 1)
        if ($auth->role_id == 1) {
            // Show all records for today
        }
        // AGENT (Role 2)
        elseif ($auth->role_id == 2) {
            $query->whereHas('user', function ($q) use ($auth) {
                $q->where('parent_id', $auth->id)
                    ->orWhere('id', $auth->id);
            });
        }
        // RETAILER
        else {
            $query->where('user_id', $auth->id);
        }

        $transactions = $query
            ->groupBy('draw_time')
            ->orderByDesc('draw_time')
            ->paginate(20);

        return view(
            'lottry_pages.transaction_details.index',
            compact('transactions')
        );
    }

    public function cancelPage(): View
    {
        $auth = auth()->user();

        // Strict 20 seconds threshold before draw time
        $lockThreshold = now()->addSeconds(20);

        $query = UserBalanceTransaction::query()
            ->select(
                'id',
                'user_id',
                'amount',
                'created_at',
                'type'
            )
            ->where('type', 'debit')
            ->whereHas('bets', function ($q) use ($lockThreshold) {
                $q->where('status', 'pending')
                    ->where('draw_time', '>', $lockThreshold);
            })
            ->with([
                'user:id,parent_id,first_name,last_name,username',
                'user.parent:id,first_name,last_name,username',
                'bets' => function ($q) use ($lockThreshold) {
                    $q->select(
                        'id',
                        'transaction_id',
                        'draw_time',
                        'points',
                        'status'
                    )
                        ->where('status', 'pending')
                        ->where('draw_time', '>', $lockThreshold)
                        ->orderBy('draw_time');
                }
            ]);

        // ADMIN (Role 1)
        if ($auth->role_id == 1) {
            $transactions = $query
                ->latest('id')
                ->paginate(20);
        }
        // AGENT (Role 2)
        elseif ($auth->role_id == 2) {
            $transactions = $query
                ->where(function ($q) use ($auth) {
                    $q->where('user_id', $auth->id)
                        ->orWhereHas('user', function ($sub) use ($auth) {
                            $sub->where('parent_id', $auth->id);
                        });
                })
                ->latest('id')
                ->paginate(20);
        }
        // RETAILER
        else {
            $transactions = $query
                ->where('user_id', $auth->id)
                ->latest('id')
                ->paginate(20);
        }

        return view('lottry_pages.cancel_bets.index', compact('transactions'));
    }

    public function cancelDraw(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required',
            'draw_time'      => 'required',
        ]);

        DB::beginTransaction();

        try {
            $drawTime = Carbon::parse($request->draw_time);

            // LOCK CANCEL FUNCTIONALITY STRICTLY 20 SECONDS BEFORE DRAW TIME
            if (now()->gte($drawTime->copy()->subSeconds(20))) {
                return back()->with(
                    'error',
                    'Draw is locked for cancellations (within 20 seconds of draw time).'
                );
            }

            // FETCH PENDING BETS WITH LOCK FOR UPDATE
            $bets = Bet::where('transaction_id', $request->transaction_id)
                ->where('draw_time', $request->draw_time)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            if ($bets->isEmpty()) {
                return back()->with(
                    'error',
                    'No pending bets found to cancel.'
                );
            }

            // GET THE ACTUAL RETAILER/USER WHO PLACED THE BET
            $betUserId = $bets->first()->user_id;
            $betUser   = User::lockForUpdate()->findOrFail($betUserId);

            // CALCULATE REFUND AMOUNT
            $refund = $bets->sum('points');

            // CANCEL BETS
            Bet::whereIn('id', $bets->pluck('id'))
                ->update([
                    'status' => 'cancelled'
                ]);

            // REFUND USER BALANCE
            $betUser->increment('balance', $refund);
            $freshUser = $betUser->fresh();

            // CREDIT TRANSACTION AUDIT
            UserBalanceTransaction::create([
                'user_id'       => $betUser->id,
                'type'          => 'credit',
                'amount'        => $refund,
                'balance_after' => $freshUser->balance,
                'remarks'       => 'Bet Cancel Refund for Draw ' . $drawTime->format('d M h:i A'),
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Bet cancelled successfully. Refunded ₹' . number_format($refund, 2)
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Cancellation failed: ' . $e->getMessage()
            );
        }
    }
}
