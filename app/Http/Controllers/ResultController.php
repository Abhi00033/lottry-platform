<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\SeriesMaster;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('reset')) {
            return redirect()->route('results.index');
        }
        // 1. Get the date from request or default to today
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));

        // 2. Fetch series to build the horizontal headers (1000-1099, 1100-1199, etc.)
        $seriesList = SeriesMaster::orderBy('start', 'asc')->get();

        // 3. Fetch results for the selected date, grouped by time
        $results = Result::whereDate('draw_time', $selectedDate)
            ->orderBy('draw_time', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->draw_time->format('h:i A');
            });

        return view('lottry_pages.results.index', compact('results', 'seriesList', 'selectedDate'));
    }
}
