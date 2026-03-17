<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\SeriesMaster;
use App\Models\UserBalanceTransaction; // 1. Import the model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $series_master = SeriesMaster::all();

        // 2. Fetch the very last transaction for this specific user
        $lastTransaction = UserBalanceTransaction::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $now = Carbon::now();
        $lastMinute = floor($now->minute / 15) * 15;
        $lastDrawTime = $now->copy()->minute($lastMinute)->second(0);

        $lastResults = Result::where('draw_time', $lastDrawTime)
            ->pluck('result_number', 'series')
            ->toArray();

        // 3. Pass $lastTransaction to the view
        return view('dashboard', compact('user', 'series_master', 'lastResults', 'lastDrawTime', 'lastTransaction'));
    }
}
