<?php

namespace App\Http\Controllers;

use App\Models\UserBalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class TransactionDetailController extends Controller
{
    public function index(Request $request): View
    {
        $auth = auth()->user();

        // Default to today if no date selected
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));


        $query = UserBalanceTransaction::where('type', 'debit')
            ->whereHas('bets')
            ->with(['user' => function ($q) {
                $q->withTrashed(); // ← fetch soft deleted users too
            }, 'user.parent' => function ($q) {
                $q->withTrashed(); // ← fetch soft deleted parents too
            }])
            ->whereDate('created_at', $selectedDate);

        if ($auth->role_id == 1) {
            // Admin sees every debit transaction
            $transactions = $query->latest()->paginate(20)->appends($request->only('date'));
        } elseif ($auth->role_id == 2) {
            // Agent sees own + their retailers' transactions
            $transactions = $query->where(function ($q) use ($auth) {
                $q->where('user_id', $auth->id)
                    ->orWhereHas('user', function ($sub) use ($auth) {
                        $sub->where('parent_id', $auth->id);
                    });
            })->latest()->paginate(20)->appends($request->only('date'));
        } else {
            // Retailer sees only their own
            $transactions = $query->where('user_id', $auth->id)
                ->latest()
                ->paginate(20)
                ->appends($request->only('date'));
        }

        return view('lottry_pages.transaction_details.index', compact('transactions', 'selectedDate'));
    }
}
