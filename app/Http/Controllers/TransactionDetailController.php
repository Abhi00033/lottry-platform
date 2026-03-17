<?php

namespace App\Http\Controllers;

use App\Models\UserBalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionDetailController extends Controller
{
    /**
     * Display transactions based on hierarchy:
     * Admin: Sees All
     * Agent: Sees Self + Own Retailers
     * Retailer: Sees Self Only
     */
    public function index(Request $request): View
    {
        $auth = auth()->user();

        // Start with 'debit' type (bet placements) and eager load relationships
        $query = UserBalanceTransaction::where('type', 'debit')->with('user.parent');

        if ($auth->role_id == 1) {
            // Admin sees every transaction in the system
            $transactions = $query->latest()->paginate(20);
        } elseif ($auth->role_id == 2) {
            // Agent sees their own transactions OR those of their retailers
            $transactions = $query->where(function ($q) use ($auth) {
                $q->where('user_id', $auth->id)
                    ->orWhereHas('user', function ($sub) use ($auth) {
                        $sub->where('parent_id', $auth->id); // Retailers registered by this agent
                    });
            })->latest()->paginate(20);
        } else {
            // Retailer sees only their own bet transactions
            $transactions = $query->where('user_id', $auth->id)->latest()->paginate(20);
        }

        return view('lottry_pages.transaction_details.index', compact('transactions'));
    }
}
