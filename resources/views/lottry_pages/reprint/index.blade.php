@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <h4 class="fw-bold text-warning m-0">
                <i class="fas fa-print me-2"></i>
                Ticket Reprint
            </h4>

            <div class="badge bg-dark border border-warning px-3 py-2">
                Balance :
                <span class="text-warning fw-bold">
                    ₹{{ number_format(auth()->user()->balance, 2) }}
                </span>
            </div>

        </div>

        {{-- Search --}}
        @if ($isAdmin)
            <div class="card bg-dark border-secondary shadow-sm mb-3">

                <div class="card-body">

                    <form action="{{ route('reprint.search') }}" method="POST">

                        @csrf

                        <div class="row g-3 align-items-end">

                            <div class="col-lg-2">

                                <label class="form-label text-warning fw-bold">
                                    Ticket No
                                </label>

                                <input type="text" class="form-control bg-dark text-white border-warning"
                                    name="ticket_no" value="{{ request('ticket_no') }}">

                            </div>

                            <div class="col-lg-2">

                                <label class="form-label text-warning fw-bold">
                                    Transaction
                                </label>

                                <input type="text" class="form-control bg-dark text-white border-warning"
                                    name="transaction_id" value="{{ request('transaction_id') }}">

                            </div>

                            <div class="col-lg-2">

                                <label class="form-label text-warning fw-bold">
                                    Username
                                </label>

                                <input type="text" class="form-control bg-dark text-white border-warning" name="username"
                                    value="{{ request('username') }}">

                            </div>

                            <div class="col-lg-2">

                                <label class="form-label text-warning fw-bold">
                                    Mobile
                                </label>

                                <input type="text" class="form-control bg-dark text-white border-warning" name="mobile"
                                    value="{{ request('mobile') }}">

                            </div>

                            <div class="col-lg-2">

                                <label class="form-label text-warning fw-bold">
                                    Draw Date
                                </label>

                                <input type="date" class="form-control bg-dark text-white border-warning"
                                    name="draw_date" value="{{ request('draw_date', now()->format('Y-m-d')) }}">

                            </div>

                            <div class="col-lg-2 d-grid">

                                <button class="btn btn-warning fw-bold">

                                    <i class="fas fa-search me-1"></i>

                                    Search

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>
        @else
            <div class="alert alert-info border-0 shadow-sm">

                <i class="fas fa-info-circle me-2"></i>

                Showing your latest <strong>5</strong> tickets.

            </div>
        @endif


        {{-- Table --}}
        <div class="card bg-dark border-secondary shadow-sm">

            <div class="table-responsive">

                <table class="table table-dark table-hover align-middle text-center mb-0">

                    <thead class="border-secondary">

                        <tr>

                            <th class="text-warning">Ticket</th>

                            <th class="text-warning">Retailer</th>

                            <th class="text-warning">Draw</th>

                            <th class="text-warning">Qty</th>

                            <th class="text-warning">Amount</th>

                            <th class="text-warning">Bet Time</th>

                            <th class="text-warning">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($tickets as $ticket)
                            <tr>

                                <td>

                                    <div class="fw-bold text-info">

                                        {{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}

                                    </div>

                                </td>

                                <td>

                                    {{ strtoupper(optional($ticket->user)->unique_id) }}

                                </td>

                                <td>

                                    {{ $ticket->draw_date->format('d M Y') }}

                                    <br>

                                    <small class="text-warning">

                                        {{ \Carbon\Carbon::parse($ticket->draw_time)->format('h:i A') }}

                                    </small>

                                </td>

                                <td>

                                    {{ $ticket->total_qty }}

                                </td>

                                <td class="fw-bold text-success">

                                    ₹{{ number_format($ticket->total_amount, 2) }}

                                </td>

                                <td>

                                    {{ $ticket->created_at->format('d M Y') }}

                                    <br>

                                    <small class="text-info">

                                        {{ $ticket->created_at->format('h:i A') }}

                                    </small>

                                </td>

                                <td>

                                    <button type="button" onclick="silentReprint('{{ route('reprint.print', $ticket) }}')"
                                        class="btn btn-outline-warning btn-sm">

                                        <i class="fas fa-print me-1"></i>

                                        Reprint

                                    </button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="py-5">

                                    <div class="text-secondary">

                                        <i class="fas fa-print fa-2x mb-2 d-block"></i>

                                        No tickets available.

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($isAdmin)
                <div class="card-footer bg-dark border-secondary">

                    {{ $tickets->links() }}

                </div>
            @endif

        </div>

    </div>

    {{-- Direct Background Reprint Script --}}
    <script>
        function silentReprint(printUrl) {
            let printFrame = document.getElementById('silentPrintIframe');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'silentPrintIframe';
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
            }

            printFrame.src = printUrl;

            printFrame.onload = function() {
                try {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                } catch (e) {
                    console.warn('Printer prompt trigger failed:', e);
                }
            };
        }
    </script>
@endsection
