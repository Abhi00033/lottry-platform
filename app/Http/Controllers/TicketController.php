<?php

namespace App\Http\Controllers;

use App\Models\BetTicket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
   
    public function print(Request $request, BetTicket $ticket = null)
    {
        // Multiple Tickets
        if ($request->filled('ids')) {

            $ids = collect(explode(',', $request->ids))
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            $tickets = BetTicket::with([
                'bets',
                'user',
                'transaction'
            ])
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->get();

            return view('tickets.print', compact('tickets'));
        }

        // Single Ticket

        abort_if(!$ticket, 404);

        $ticket->load([
            'bets',
            'user',
            'transaction'
        ]);

        $tickets = collect([$ticket]);

        return view('tickets.print', compact('tickets'));
    }
}
