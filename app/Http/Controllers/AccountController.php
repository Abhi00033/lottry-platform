<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Bet;
use App\Models\UserBalanceTransaction;
use Carbon\Carbon;

class AccountController extends Controller
{

    public function accounts(Request $request): View
    {
        $auth = auth()->user();

        $dateFrom = $request->get(
            'date_from',
            Carbon::today()->format('Y-m-d')
        );

        $dateTo = $request->get(
            'date_to',
            Carbon::today()->format('Y-m-d')
        );

        // FETCH BETS

        $bets = Bet::where('user_id', $auth->id)

            ->whereIn('status', [
                'won',
                'lost',
                'pending'
            ])

            ->whereBetween('draw_time', [

                Carbon::parse($dateFrom)->startOfDay(),

                Carbon::parse($dateTo)->endOfDay(),

            ])

            ->get();

        //  DEFAULT COMMISSION

        $commissionRate = $auth->commision ?? 5;

        //  REPORT 1 — POINT REPORT

        // Total played points
        $playPoints = $bets->sum('points');

        // Business commission info only
        $commission = ($playPoints * $commissionRate) / 100;

        // Total winning payout
        $winPoints = $bets

            ->where('status', 'won')

            ->sum(function ($bet) {

                return $bet->points * 90;
            });

        // Base net
        $baseNet = $playPoints - $winPoints;

        // Always deduct commission from the final result
        $netReport1 = $baseNet - $commission;
        // | REPORT 2 — AMOUNT REPORT

        // Total amount played
        $playAmount = $bets->sum('total_amount');

        // Total winning amount
        $winAmount = $bets

            ->where('status', 'won')

            ->sum(function ($bet) {

                return $bet->points * 90;
            });

        // Final net
        $netReport2 = $playAmount - $winAmount;

        // REPORT ARRAYS

        $report1 = [

            'play_point' => $playPoints,

            'commission' => $commission,

            'win_point'  => $winPoints,

            'net'        => $netReport1,
        ];

        $report2 = [

            'play_point' => $playAmount,

            'win_point'  => $winAmount,

            'net'        => $netReport2,
        ];

        //  RETURN VIEW

        return view(
            'lottry_pages.accounts.index',
            compact(

                'dateFrom',
                'dateTo',

                'report1',
                'report2'
            )
        );
    }

    public function print(Request $request): View
    {
        $auth = auth()->user();

        $dateFrom = $request->get(
            'date_from',
            Carbon::today()->format('Y-m-d')
        );

        $dateTo = $request->get(
            'date_to',
            Carbon::today()->format('Y-m-d')
        );

        $bets = Bet::where('user_id', $auth->id)

            ->whereIn('status', [
                'won',
                'lost',
                'pending'
            ])

            ->whereBetween('draw_time', [

                Carbon::parse($dateFrom)->startOfDay(),

                Carbon::parse($dateTo)->endOfDay(),

            ])

            ->get();

        $commissionRate = $auth->commision ?? 5;

        $playPoints = $bets->sum('points');

        $commission = ($playPoints * $commissionRate) / 100;

        $winPoints = $bets
            ->where('status', 'won')
            ->sum(function ($bet) {
                return $bet->points * 90;
            });

        $baseNet = $playPoints - $winPoints;

        $netReport1 = $baseNet - $commission;

        $playAmount = $bets->sum('total_amount');

        $winAmount = $bets
            ->where('status', 'won')
            ->sum(function ($bet) {
                return $bet->points * 90;
            });

        $netReport2 = $playAmount - $winAmount;

        $report1 = [

            'play_point' => $playPoints,

            'commission' => $commission,

            'win_point'  => $winPoints,

            'net'        => $netReport1,
        ];

        $report2 = [

            'play_point' => $playAmount,

            'win_point'  => $winAmount,

            'net'        => $netReport2,
        ];

        return view('lottry_pages.accounts.print', compact('auth', 'dateFrom', 'dateTo', 'report1', 'report2'));
    }
}
