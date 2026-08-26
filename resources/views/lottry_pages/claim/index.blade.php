@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="fw-bold text-warning m-0">
                <i class="fas fa-trophy me-2"></i> Claim / Results
            </h4>
            <div class="badge bg-dark border border-warning px-3 py-2">
                Balance:
                <span class="text-warning fw-bold" id="userWalletBalance">
                    ₹{{ number_format(auth()->user()->balance, 2) }}
                </span>
            </div>
        </div>

        {{-- Ticket / Txn Search --}}
        <div class="card bg-dark border-secondary shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-9">
                        <label class="form-label text-warning fw-bold">
                            <i class="fas fa-barcode me-2"></i> Scan Barcode / Enter Ticket Number
                        </label>
                        <input type="text" id="ticketSearch"
                            class="form-control form-control-lg bg-dark text-white border-warning"
                            value="{{ request('ticket_no') }}" placeholder="Scan barcode or enter ticket number..."
                            autocomplete="off">
                    </div>
                    <div class="col-lg-3 d-grid gap-2">
                        <button type="button" id="searchTicketBtn" class="btn btn-warning btn-lg fw-bold">
                            <i class="fas fa-search me-2"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table (No Action / View Column) --}}
        <div class="card bg-dark border-secondary shadow-sm">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle text-center mb-0">
                    <thead class="border-secondary">
                        <tr>
                            <th class="text-warning">Txn / Ticket No (Click to Open)</th>
                            <th class="text-warning">Draw Time</th>
                            <th class="text-warning">Prize Amount</th>
                            <th class="text-warning">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr class="ticket-row cursor-pointer" data-ticket-no="{{ $ticket->ticket_no }}">
                                {{-- Ticket Number Trigger --}}
                                <td>
                                    <span class="badge bg-outline-warning border border-warning text-warning p-2">
                                        <i class="fas fa-ticket-alt me-1"></i> {{ $ticket->ticket_no }}
                                    </span>
                                </td>

                                {{-- Draw Time --}}
                                <td class="text-white-50">
                                    {{ $ticket->draw_date->format('d M Y') }}
                                    <br>
                                    <span class="text-warning">
                                        {{ \Carbon\Carbon::parse($ticket->draw_time)->format('h:i A') }}
                                    </span>
                                </td>

                                {{-- Prize Amount --}}
                                <td>
                                    @if ($ticket->display_status == 'pending')
                                        <span class="text-warning fw-bold">Pending Draw</span>
                                    @elseif($ticket->claim_amount > 0)
                                        <span
                                            class="text-success fw-bold">₹{{ number_format($ticket->claim_amount, 2) }}</span>
                                    @else
                                        <span class="text-secondary">₹0.00</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td>
                                    @switch($ticket->display_status)
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @break

                                        @case('winner')
                                            <span class="badge bg-success">
                                                Winner
                                                <small class="d-block text-warning">Unclaimed</small>
                                            </span>
                                        @break

                                        @case('claimed')
                                            <span class="badge bg-primary">
                                                Winner
                                                <small class="d-block">Claimed</small>
                                            </span>
                                        @break

                                        @default
                                            <span class="badge bg-danger">Lost</span>
                                    @endswitch
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-5">
                                        <div class="text-secondary">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i> No tickets found
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Dynamic Modal with Static Backdrop & Keyboard Disabled --}}
            <div class="modal fade" id="searchTicketModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content bg-dark border-secondary text-white">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title text-warning" id="modalTxnTitle">
                                <i class="fas fa-receipt me-2"></i> Ticket Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="searchTicketBody">
                            {{-- Filled dynamically via JS --}}
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endsection

    @push('scripts')
        <script>
            $('.ticket-row').css('cursor', 'pointer');

            // Automatically reload page whenever the modal is closed via the Close option
            $('#searchTicketModal').on('hidden.bs.modal', function() {
                location.reload();
            });

            // Auto-Claim and Modal Open Handler
            function openAndAutoClaimTicket(ticketNo) {
                if (!ticketNo) return;

                $('#searchTicketBtn').prop('disabled', true);

                // Fetch Ticket Details
                $.ajax({
                    url: "{{ route('claim.search') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ticket_no: ticketNo
                    },
                    success: function(response) {
                        $('#searchTicketBtn').prop('disabled', false);

                        if (!response.success) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Ticket Search',
                                text: response.message,
                                background: '#1a1a1a',
                                color: '#fff'
                            });
                            return;
                        }

                        let ticket = response.ticket;

                        // AUTOMATICALLY TRIGGER CLAIM API
                        $.post("{{ route('claim.process') }}", {
                            _token: "{{ csrf_token() }}",
                            ticket_id: ticket.id
                        }, function(claimRes) {

                            let betsHtml = '';
                            ticket.formatted_bets.forEach(function(bet) {
                                betsHtml += `
                            <tr>
                                <td class="text-center text-white-50">${bet.bet_time}</td>
                                <td class="text-center fw-bold text-warning">${bet.number}</td>
                                <td class="text-center">${bet.qty}</td>
                                <td class="text-center">${bet.points}</td>
                                <td class="text-center text-success fw-bold">₹${parseFloat(bet.win_amount).toFixed(2)}</td>
                                <td class="text-center">
                                    <span class="badge ${bet.status === 'Pending' ? 'bg-warning text-dark' : (bet.status === 'Winner' ? 'bg-success' : 'bg-secondary')}">
                                        ${bet.status}
                                    </span>
                                </td>
                            </tr>
                        `;
                            });

                            let claimAlertHtml = '';
                            if (claimRes.is_first_win) {
                                // First-time win auto claimed
                                claimAlertHtml = `
                            <div class="alert alert-success bg-success text-white border-0 text-center my-3">
                                <h3 class="fw-bold mb-1">🎉 YOU WIN ₹${claimRes.win_amount}! 🎉</h3>
                                <p class="m-0">Amount automatically added to your wallet balance.</p>
                            </div>
                        `;
                                if (claimRes.new_balance) {
                                    $('#userWalletBalance').text('₹' + claimRes.new_balance);
                                }
                            } else if (claimRes.is_already_claimed) {
                                // Already claimed second time
                                claimAlertHtml = `
                            <div class="alert alert-warning bg-dark border-warning text-warning text-center my-3">
                                <i class="fas fa-exclamation-triangle me-2"></i> ${claimRes.message}
                            </div>
                        `;
                            } else if (claimRes.is_pending) {
                                claimAlertHtml = `
                            <div class="alert alert-info bg-dark border-info text-info text-center my-3">
                                <i class="fas fa-clock me-2"></i> Draw result is pending.
                            </div>
                        `;
                            }

                            let modalHtml = `
                        ${claimAlertHtml}
                        <table class="table table-dark table-borderless mb-0">
                            <tr>
                                <th width="35%">Txn / Ticket No</th>
                                <td class="fw-bold text-warning">${ticket.ticket_no}</td>
                            </tr>
                            <tr>
                                <th>Retailer</th>
                                <td>${ticket.user?.unique_id ?? '-'}</td>
                            </tr>
                            <tr>
                                <th>Draw Date & Time</th>
                                <td>${ticket.draw_date} ${ticket.draw_time}</td>
                            </tr>
                            <tr>
                                <th>Winning Amount</th>
                                <td class="text-success fw-bold">₹${parseFloat(ticket.claim_amount).toFixed(2)}</td>
                            </tr>
                        </table>

                        <hr class="border-secondary">
                        <h6 class="text-warning mb-3"><i class="fas fa-trophy me-2"></i> Winning Numbers Details</h6>

                        <div class="table-responsive">
                            <table class="table table-dark table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-warning text-center">Bet Time</th>
                                        <th class="text-warning text-center">Number</th>
                                        <th class="text-warning text-center">Qty</th>
                                        <th class="text-warning text-center">Points</th>
                                        <th class="text-warning text-center">Win Amount</th>
                                        <th class="text-warning text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${betsHtml.length > 0 ? betsHtml : '<tr><td colspan="6" class="text-center text-muted">No winning numbers for this ticket.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    `;

                            $('#searchTicketBody').html(modalHtml);

                            // Initialize Bootstrap Modal with static backdrop options
                            let modalElement = document.getElementById('searchTicketModal');
                            let modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(
                                modalElement, {
                                    backdrop: 'static',
                                    keyboard: false
                                });
                            modal.show();
                        });
                    }
                });
            }

            document.addEventListener("DOMContentLoaded", function() {
                @if (isset($autoOpenTicketNo) && !empty($autoOpenTicketNo))
                    // 1. Clean the URL immediately so refreshing won't re-trigger the modal loop
                    if (window.history.replaceState) {
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location
                            .pathname;
                        window.history.replaceState({
                            path: cleanUrl
                        }, '', cleanUrl);
                    }

                    // 2. Automatically trigger your modal popup and claim process
                    let scannedTicketNo = "{{ $autoOpenTicketNo }}";
                    if (typeof openAndAutoClaimTicket === 'function') {
                        openAndAutoClaimTicket(scannedTicketNo);
                    }
                @endif
            });

            // Click row or Ticket number to open modal
            $(document).on('click', '.ticket-row', function() {
                let ticketNo = $(this).data('ticket-no');
                openAndAutoClaimTicket(ticketNo);
            });

            // Search button handler
            $('#searchTicketBtn').on('click', function() {
                let ticketNo = $('#ticketSearch').val().trim();
                openAndAutoClaimTicket(ticketNo);
            });

            $('#ticketSearch').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    openAndAutoClaimTicket($('#ticketSearch').val().trim());
                }
            });
        </script>
    @endpush
