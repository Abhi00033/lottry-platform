<?php

namespace App\Http\Controllers;

use App\Models\BetTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReprintController extends Controller
{
    /**
     * Reprint Home
     */
    public function index()
    {
        $user = Auth::user();

        // Admin
        if ($user->role_id == 1) {

            $tickets = BetTicket::with('user')
                ->whereDate('draw_date', today())
                ->latest('id')
                ->paginate(20);

            return view('lottry_pages.reprint.index', [
                'tickets' => $tickets,
                'isAdmin' => true
            ]);
        }

        // Retailer
        $tickets = BetTicket::with('user')
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('lottry_pages.reprint.index', [
            'tickets' => $tickets,
            'isAdmin' => false
        ]);
    }

    /**
     * Admin Search
     */
    public function search(Request $request)
    {
        $user = Auth::user();

        abort_if($user->role_id != 1, 403);

        $query = BetTicket::with('user');

        if ($request->filled('ticket_no')) {
            $query->where('ticket_no', 'like', '%' . $request->ticket_no . '%');
        }

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', $request->transaction_id);
        }

        if ($request->filled('mobile')) {

            $query->whereHas('user', function ($q) use ($request) {
                $q->where('mobile', 'like', '%' . $request->mobile . '%');
            });
        }

        if ($request->filled('username')) {

            $query->whereHas('user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->username . '%');
            });
        }

        if ($request->filled('draw_date')) {
            $query->whereDate('draw_date', $request->draw_date);
        }

        if (!$request->filled('draw_date')) {
            $query->whereDate('draw_date', today());
        }

        $tickets = $query
            ->latest('id')
            ->paginate(20)
            ->appends($request->all());

        return view('lottry_pages.reprint.index', [
            'tickets' => $tickets,
            'isAdmin' => true
        ]);
    }

    /**
     * Reprint Ticket
     */
    public function print(BetTicket $ticket)
    {
        $user = Auth::user();

        // Retailer can print only his own tickets
        if ($user->role_id != 1) {

            abort_if(
                $ticket->user_id != $user->id,
                403,
                'Unauthorized'
            );
        }

        // Reuse existing print controller
        return app(TicketController::class)->print(
            request(),
            $ticket
        );
    }
}
