<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Bet;
use Carbon\Carbon;

class ClaimController extends Controller
{
    public function claim(Request $request): View
    {
        $auth = auth()->user();

        $selectedDate = Carbon::today()->format('Y-m-d');
        /*
        |--------------------------------------------------------------------------
        | GROUP TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        $query = Bet::where('user_id', $auth->id)

            ->whereIn('status', [
                'won',
                'lost',
                'pending'
            ])

            ->whereDate(
                'draw_time',
                $selectedDate
            )

            ->with([
                'transaction',
                'series'
            ]);


        /*
        |--------------------------------------------------------------------------
        | FETCH BETS
        |--------------------------------------------------------------------------
        */

        $bets = $query
            ->latest('draw_time')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GROUP BY TRANSACTION
        |--------------------------------------------------------------------------
        */

        $transactions = $bets
            ->groupBy('transaction_id')
            ->map(function ($items) {

                $firstBet = $items->first();

                /*
                |--------------------------------------------------------------------------
                | STATUS PRIORITY
                |--------------------------------------------------------------------------
                */

                $status = 'lost';

                if ($items->contains('status', 'won')) {

                    $status = 'won';
                } elseif ($items->contains('status', 'pending')) {

                    $status = 'pending';
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL POINTS
                |--------------------------------------------------------------------------
                */

                $totalPoints = $items->sum('points');

                /*
                |--------------------------------------------------------------------------
                | TOTAL WIN
                |--------------------------------------------------------------------------
                */

                $totalWin = $items
                    ->where('status', 'won')
                    ->sum(function ($bet) {

                        return $bet->points * 90;
                    });

                return (object)[

                    'transaction_number' =>
                    $firstBet->transaction->transaction_number ?? '-',

                    'bet_time' =>
                    $firstBet->created_at,

                    'draw_time' =>
                    $firstBet->draw_time,

                    'status' =>
                    $status,

                    'total_points' =>
                    $totalPoints,

                    'total_win' =>
                    $totalWin,

                    'bets' =>
                    $items
                ];
            })

            ->values();

        return view('lottry_pages.claim.index', compact('transactions'));
    }
}
