<?php

namespace App\Http\Controllers;

use App\Models\UserBalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Bet;
use Illuminate\Support\Facades\DB;

class TransactionDetailController extends Controller
{
    // public function index(Request $request): View
    // {
    //     $auth = auth()->user();

    //     // Default to today if no date selected
    //     $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));


    //     $query = UserBalanceTransaction::where('type', 'debit')
    //         ->whereHas('bets')
    //         ->with([
    //             'user' => function ($q) {
    //                 $q->withTrashed();
    //             },
    //             'user.parent' => function ($q) {
    //                 $q->withTrashed();
    //             },
    //             'bets'
    //         ])
    //         ->whereDate('created_at', $selectedDate);

    //     if ($auth->role_id == 1) {
    //         // Admin sees every debit transaction
    //         $transactions = $query->latest()->paginate(20)->appends($request->only('date'));
    //     } elseif ($auth->role_id == 2) {
    //         // Agent sees own + their retailers' transactions
    //         $transactions = $query->where(function ($q) use ($auth) {
    //             $q->where('user_id', $auth->id)
    //                 ->orWhereHas('user', function ($sub) use ($auth) {
    //                     $sub->where('parent_id', $auth->id);
    //                 });
    //         })->latest()->paginate(20)->appends($request->only('date'));
    //     } else {
    //         // Retailer sees only their own
    //         $transactions = $query->where('user_id', $auth->id)
    //             ->latest()
    //             ->paginate(20)
    //             ->appends($request->only('date'));
    //     }

    //     return view('lottry_pages.transaction_details.index', compact('transactions', 'selectedDate'));
    // }


    public function index(Request $request): View
    {
        $auth = auth()->user();

        $selectedDate = $request->get(
            'date',
            now()->format('Y-m-d')
        );

        $query = Bet::query()
            ->selectRaw("
            draw_time,

            SUM(
                CASE
                    WHEN status IN ('pending','won','lost')
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
                    WHEN status = 'won'
                    THEN points * 90
                    ELSE 0
                END
            ) as win_points
        ")

            ->whereDate('draw_time', $selectedDate);

        // ADMIN
        if ($auth->role_id == 1) {

            // no filter
        }

        // AGENT
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
            ->orderBy('draw_time', 'desc')
            ->paginate(20)
            ->appends($request->only('date'));

        return view('lottry_pages.transaction_details.index', compact('transactions', 'selectedDate'));
    }


    public function cancelPage(): View
    {
        $auth = auth()->user();

        $query = UserBalanceTransaction::query()

            ->select(
                'id',
                'user_id',
                'amount',
                'created_at',
                'type'
            )

            ->where('type', 'debit')

            ->whereHas('bets', function ($q) {

                $q->where('status', 'pending')
                    ->where('draw_time', '>', now()->addMinutes(2));
            })

            ->with([

                'user:id,parent_id,first_name,last_name,username',

                'user.parent:id,first_name,last_name,username',

                'bets' => function ($q) {

                    $q->select(
                        'id',
                        'transaction_id',
                        'draw_time',
                        'points',
                        'status'
                    )

                        ->where('status', 'pending')

                        ->where('draw_time', '>', now()->addMinutes(2))

                        ->orderBy('draw_time');
                }

            ]);

        // ADMIN
        if ($auth->role_id == 1) {

            $transactions = $query
                ->latest('id')
                ->paginate(20);
        }

        // AGENT
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
            'draw_time' => 'required',
        ]);

        DB::beginTransaction();

        try {

            $user = auth()->user();

            $drawTime = Carbon::parse($request->draw_time);

            // LOCK BEFORE 2 MIN
            if (now()->gte($drawTime->copy()->subMinutes(2))) {

                return back()->with(
                    'error',
                    'Draw already locked.'
                );
            }

            // FETCH PENDING BETS
            $bets = Bet::where('transaction_id', $request->transaction_id)

                ->where('draw_time', $request->draw_time)

                ->where('status', 'pending')

                ->get();

            if ($bets->isEmpty()) {

                return back()->with(
                    'error',
                    'No pending bets found.'
                );
            }

            // REFUND
            $refund = $bets->sum('points');

            // CANCEL BETS
            Bet::whereIn('id', $bets->pluck('id'))

                ->update([
                    'status' => 'cancelled'
                ]);

            // REFUND USER BALANCE
            $user->increment('balance', $refund);

            // CREDIT ENTRY
            UserBalanceTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'credit',
                'amount'        => $refund,
                'balance_after' => $user->fresh()->balance,
                'remarks'       => 'Bet Cancel Refund',
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Bet cancelled successfully. Refund ₹' . number_format($refund, 2)
            );
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    // public function cancelPage(): View
    // {
    //     $auth = auth()->user();

    //     $query = UserBalanceTransaction::where('type', 'debit')
    //         ->whereHas('bets', function ($q) {

    //             $q->where('status', 'pending')
    //                 ->where('draw_time', '>', now()->addMinutes(2));
    //         })
    //         ->with([
    //             'user' => function ($q) {
    //                 $q->withTrashed();
    //             },
    //             'user.parent' => function ($q) {
    //                 $q->withTrashed();
    //             },
    //             'bets' => function ($q) {

    //                 $q->where('status', 'pending')
    //                     ->where('draw_time', '>', now()->addMinutes(2))
    //                     ->orderBy('draw_time');
    //             }
    //         ]);

    //     // ADMIN
    //     if ($auth->role_id == 1) {

    //         $transactions = $query
    //             ->latest()
    //             ->paginate(20);
    //     }

    //     // AGENT
    //     elseif ($auth->role_id == 2) {

    //         $transactions = $query
    //             ->where(function ($q) use ($auth) {

    //                 $q->where('user_id', $auth->id)
    //                     ->orWhereHas('user', function ($sub) use ($auth) {

    //                         $sub->where('parent_id', $auth->id);
    //                     });
    //             })
    //             ->latest()
    //             ->paginate(20);
    //     }

    //     // RETAILER
    //     else {

    //         $transactions = $query
    //             ->where('user_id', $auth->id)
    //             ->latest()
    //             ->paginate(20);
    //     }

    //     return view('lottry_pages.cancel_bets.index', compact('transactions'));
    // }

    // public function cancelDraw(Request $request)
    // {
    //     $request->validate([
    //         'transaction_id' => 'required',
    //         'draw_time' => 'required',
    //     ]);

    //     DB::beginTransaction();

    //     try {

    //         $user = auth()->user();

    //         $drawTime = Carbon::parse($request->draw_time);

    //         // stop cancel before 2 mins
    //         if (now()->gte($drawTime->copy()->subMinutes(2))) {

    //             return back()->with(
    //                 'error',
    //                 'Draw already locked.'
    //             );
    //         }

    //         // fetch bets
    //         $bets = Bet::where('transaction_id', $request->transaction_id)
    //             ->where('draw_time', $request->draw_time)
    //             ->where('status', 'pending')
    //             ->get();

    //         if ($bets->isEmpty()) {

    //             return back()->with(
    //                 'error',
    //                 'No pending bets found.'
    //             );
    //         }

    //         // refund amount
    //         $refund = $bets->sum('points');

    //         // cancel bets
    //         Bet::whereIn('id', $bets->pluck('id'))
    //             ->update([
    //                 'status' => 'cancelled'
    //             ]);

    //         // refund balance
    //         $user->balance += $refund;
    //         $user->save();

    //         // create credit transaction
    //         UserBalanceTransaction::create([
    //             'user_id' => $user->id,
    //             'type' => 'credit',
    //             'amount' => $refund,
    //             'balance_after' => $user->balance,
    //             'remarks' => 'Bet Cancel Refund',
    //         ]);

    //         DB::commit();

    //         return back()->with(
    //             'success',
    //             'Bet cancelled successfully. Refund ₹' . number_format($refund, 2)
    //         );
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return back()->with(
    //             'error',
    //             $e->getMessage()
    //         );
    //     }
    // }
}
