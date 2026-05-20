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

        // ── GET BETS ─────────────────────────────────────

        $bets = Bet::where('user_id', $auth->id)
            ->whereIn('status', ['won', 'lost', 'pending'])
            ->whereBetween('draw_time', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->get();



        // ── BASIC VALUES ────────────────────────────────

        $commissionRate = $auth->commision ?? 0;



        // ── REPORT 1 : POINT SUMMARY ───────────────────

        // Total Play Points
        $totalPlayPoints = $bets->sum('points');

        // Commission Amount
        $totalCommission = ($commissionRate / 100) * $totalPlayPoints;

        // Only Won Points
        $wonPoints = $bets->where('status', 'won')->sum('points');

        // Win After Commission Deduction
        $totalWin = $wonPoints * ((100 - $commissionRate) / 100);

        // REPORT 1 NET
        $netFirst = $totalPlayPoints - $totalCommission - $totalWin;



        // ── REPORT 2 : AMOUNT SUMMARY ──────────────────

        // Total Play Amount
        $totalPlay = $bets->sum('total_amount');

        // Original Win Amount WITHOUT commission deduction
        $totalWinAmount = $wonPoints;

        // REPORT 2 NET
        $netSecond = $totalPlay - $totalWinAmount;



        // ── REPORT ARRAYS ──────────────────────────────

        $report1 = [
            'play_point' => $totalPlayPoints,
            'commission' => $totalCommission,
            'win_point'  => $totalWin,
            'net'        => $netFirst,
        ];

        $report2 = [
            'play_point' => $totalPlay,
            'win_point'  => $totalWinAmount,
            'net'        => $netSecond,
        ];



        // ── POSITIVE / NEGATIVE REPORTS ────────────────

        $positiveReports = [];
        $negativeReports = [];

        // REPORT 1
        if ($netFirst >= 0) {
            $positiveReports['report1'] = $report1;
        } else {
            $negativeReports['report1'] = $report1;
        }

        // REPORT 2
        if ($netSecond >= 0) {
            $positiveReports['report2'] = $report2;
        } else {
            $negativeReports['report2'] = $report2;
        }



        // ── COUNTS ─────────────────────────────────────

        $totalBets = $bets->count();

        $wonBets = $bets->where('status', 'won')->count();

        $lostBets = $bets->where('status', 'lost')->count();

        $pendingBets = $bets->where('status', 'pending')->count();

        $pendingPoints = $bets->where('status', 'pending')->sum('points');



        // ── RETURN VIEW ────────────────────────────────

        return view('lottry_pages.accounts.index', compact(

            'dateFrom',
            'dateTo',

            'commissionRate',

            'totalPlayPoints',
            'totalCommission',
            'totalWin',
            'netFirst',

            'totalPlay',
            'totalWinAmount',
            'netSecond',

            'positiveReports',
            'negativeReports',

            'totalBets',
            'wonBets',
            'lostBets',
            'pendingBets',
            'pendingPoints'
        ));
    }

    // public function accounts(Request $request): View
    // {
    //     $auth      = auth()->user();
    //     $dateFrom  = $request->get('date_from', Carbon::today()->format('Y-m-d'));
    //     $dateTo    = $request->get('date_to',   Carbon::today()->format('Y-m-d'));

    //     // 1. UPDATED: Added 'pending' to the status list
    //     $bets = Bet::where('user_id', $auth->id)
    //         ->whereIn('status', ['won', 'lost', 'pending'])
    //         ->whereBetween('draw_time', [
    //             Carbon::parse($dateFrom)->startOfDay(),
    //             Carbon::parse($dateTo)->endOfDay(),
    //         ])
    //         ->get();

    //     // ── Calculations ──────────────────────────────
    //     $totalPlayPoints  = $bets->sum('points');
    //     $commissionRate   = $auth->commision ?? 0;
    //     $totalCommission  = ($commissionRate / 100) * $totalPlayPoints;
    //     $netMultiplier    = 100 - $commissionRate;

    //     // 2. IMPORTANT: Only sum 'won' bets for the Win Points
    //     $totalWin         = $bets->where('status', 'won')->sum('points') * $netMultiplier;
    //     $netFirst         = $totalPlayPoints - $totalWin;

    //     $totalPlay        = $bets->sum('total_amount');
    //     $totalWinAmount   = $totalWin; // Match the calculated win
    //     $netSecond        = $totalPlay - $totalWinAmount;

    //     // ── Counts ────────────────────────────────────
    //     $totalBets        = $bets->count();
    //     $wonBets          = $bets->where('status', 'won')->count();
    //     $lostBets         = $bets->where('status', 'lost')->count();
    //     $pendingBets      = $bets->where('status', 'pending')->count(); // New Count
    //     $pendingPoints    = $bets->where('status', 'pending')->sum('points'); // New Sum

    //     return view('lottry_pages.accounts.index', compact(
    //         'dateFrom',
    //         'dateTo',
    //         'totalPlayPoints',
    //         'totalCommission',
    //         'commissionRate',
    //         'totalWin',
    //         'netFirst',
    //         'totalPlay',
    //         'totalWinAmount',
    //         'netSecond',
    //         'totalBets',
    //         'wonBets',
    //         'lostBets',
    //         'netMultiplier',
    //         'pendingBets',
    //         'pendingPoints'
    //     ));
    // }
}
