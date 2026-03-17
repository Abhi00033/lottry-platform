<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LotteryController extends Controller
{
    public function accounts()
    {
        return view('lottry_pages.accounts.index');
    }
    // public function transactions()
    // {
    //     return view('lottery.pages.transactions');
    // }
    // public function reprint()
    // {
    //     return view('lottery.pages.reprint');
    // }
    // public function cancel()
    // {
    //     return view('lottery.pages.cancel');
    // }
    // public function results()
    // {
    //     return view('lottery.pages.results');
    // }
    // public function claim()
    // {
    //     return view('lottery.pages.claim');
    // }
}
