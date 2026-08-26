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
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = BetTicket::with('user');

        // Role-based filtering: Admins see everything, Agents/Retailers only see their own
        if ($user->role_id != 1) {
            $query->where('user_id', $user->id);
        }

        // Default or requested date filter (ensures only current/selected date data shows, no old bleeding)
        $selectedDate = $request->input('draw_date', today()->toDateString());
        $query->whereDate('draw_date', $selectedDate);

        // If Admin is viewing index without pagination bloat, paginate nicely
        $tickets = $query->latest('id')->paginate(20)->appends($request->all());

        return view('lottry_pages.reprint.index', [
            'tickets' => $tickets,
            'isAdmin' => ($user->role_id == 1)
        ]);
    }

    /**
     * Search & Filter
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = BetTicket::with('user');

        // Role restriction: Non-admins can only search within their own tickets
        if ($user->role_id != 1) {
            $query->where('user_id', $user->id);
        }

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

        // Handle Date filtering properly: Default to today if blank, else use requested date
        $drawDate = $request->filled('draw_date') ? $request->draw_date : today()->toDateString();
        $query->whereDate('draw_date', $drawDate);

        $tickets = $query
            ->latest('id')
            ->paginate(20)
            ->appends($request->all());

        return view('lottry_pages.reprint.index', [
            'tickets' => $tickets,
            'isAdmin' => ($user->role_id == 1)
        ]);
    }

    /**
     * Reprint Ticket Print
     */
    public function print(BetTicket $ticket)
    {
        $user = Auth::user();

        // Non-admin users can print only their own tickets
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
