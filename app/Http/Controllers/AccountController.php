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
        $auth      = auth()->user();
        $dateFrom  = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo    = $request->get('date_to',   Carbon::today()->format('Y-m-d'));

        // All bets in date range (won + lost only)
        $bets = Bet::where('user_id', $auth->id)
            ->whereIn('status', ['won', 'lost'])
            ->whereBetween('draw_time', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->get();

        // ── First Report ──────────────────────────────
        $totalPlayPoints  = $bets->sum('points');
        $commissionRate   = $auth->commision ?? 0;
        $totalCommission  = ($commissionRate / 100) * $totalPlayPoints;

        // Win amount = won bets × net multiplier
        $netMultiplier    = 100 - $commissionRate;
        $totalWin         = $bets->where('status', 'won')->sum('points') * $netMultiplier;

        // Net = what user played minus what they won (house perspective)
        $netFirst         = $totalPlayPoints - $totalWin;

        // ── Second Report ─────────────────────────────
        $totalPlay        = $bets->sum('total_amount');
        $totalWinAmount   = $bets->where('status', 'won')->sum('points') * $netMultiplier;
        $netSecond        = $totalPlay - $totalWinAmount;

        // ── Counts ────────────────────────────────────
        $totalBets        = $bets->count();
        $wonBets          = $bets->where('status', 'won')->count();
        $lostBets         = $bets->where('status', 'lost')->count();

        return view('lottry_pages.accounts.index', compact(
            'dateFrom',
            'dateTo',
            'totalPlayPoints',
            'totalCommission',
            'commissionRate',
            'totalWin',
            'netFirst',
            'totalPlay',
            'totalWinAmount',
            'netSecond',
            'totalBets',
            'wonBets',
            'lostBets',
            'netMultiplier'
        ));
    }
}
