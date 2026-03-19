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
        $auth         = auth()->user();
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $statusFilter = $request->get('status', '');

        $query = Bet::where('user_id', $auth->id)
            ->whereIn('status', ['won', 'lost'])
            ->whereDate('draw_time', $selectedDate)
            ->with(['series', 'transaction']);

        if ($statusFilter === 'won' || $statusFilter === 'lost') {
            $query->where('status', $statusFilter);
        }

        $bets = $query->latest('draw_time')
            ->paginate(20)
            ->appends($request->only(['date', 'status']));

        return view('lottry_pages.claim.index', compact('bets', 'selectedDate', 'statusFilter'));
    }
}
