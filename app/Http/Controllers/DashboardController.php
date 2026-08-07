<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\SeriesMaster;
use App\Models\UserBalanceTransaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $series_master = SeriesMaster::all();

        // 1. Fetch user's latest debit transaction today
        $lastTransaction = UserBalanceTransaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->whereDate('created_at', today())
            ->latest('id')
            ->first();

        // 2. Calculate the exact previous 15-minute draw timestamp
        $now = Carbon::now();
        $lastMinute = floor($now->minute / 15) * 15;
        $lastDrawTime = $now->copy()->minute($lastMinute)->second(0);

        // 3. Get results for that draw time mapped by series
        $lastResults = Result::where('draw_time', $lastDrawTime)
            ->pluck('result_number', 'series')
            ->toArray();

        return view('dashboard', compact('user', 'series_master', 'lastResults', 'lastDrawTime', 'lastTransaction'));
    }

    public function checkLatestResult()
    {
        $now = Carbon::now();

        $start = Carbon::createFromTimeString(config('app.draw_start'));
        $end   = Carbon::createFromTimeString(config('app.draw_end'));

        $start->setDate($now->year, $now->month, $now->day);
        $end->setDate($now->year, $now->month, $now->day);

        // Outside operating hours check
        if ($now->lt($start) || $now->gt($end)) {
            return response()->json([
                'success' => false
            ]);
        }

        $latestResult = Result::latest('id')->first();

        return response()->json([
            'success'          => true,
            'latest_result_id' => $latestResult?->id ?? 0
        ]);
    }
}
