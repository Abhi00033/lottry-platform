@extends('layouts.app')

@section('content')

    <div class="container py-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-danger m-0">
                Cancel Bets
            </h3>
        </div>

        @forelse($transactions as $txn)
            @php
                $groupedBets = $txn->bets->groupBy(function ($bet) {
                    return \Carbon\Carbon::parse($bet->draw_time)->format('Y-m-d H:i:s');
                });
            @endphp

            <div class="card mb-4 border-dark shadow-sm">

                {{-- CARD HEADER --}}
                <div class="card-header bg-dark text-warning d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Ticket / Transaction No: </strong>
                        <span class="text-white fw-bold">
                            {{ $txn->bets->first()->ticket->ticket_no ?? 'LT' . str_pad($txn->id, 8, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    <div>
                        <span class="badge bg-warning text-dark">
                            {{ $groupedBets->count() }} Draws
                        </span>
                    </div>
                </div>

                {{-- CARD BODY --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th width="200">Ticket / Txn No</th>
                                    <th width="200">Bet Time</th>
                                    <th width="200">Draw Time</th>
                                    <th width="120">Total Bets</th>
                                    <th width="140">Total Points</th>
                                    <th width="140">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($groupedBets as $drawTime => $bets)
                                    @php
                                        $drawObj = \Carbon\Carbon::parse($drawTime);
                                        $secondsUntilDraw = now()->diffInSeconds($drawObj, false);
                                        $isLocked = $secondsUntilDraw <= 20;
                                        $ticketNo =
                                            $bets->first()->ticket->ticket_no ??
                                            'LT' . str_pad($txn->id, 8, '0', STR_PAD_LEFT);
                                    @endphp

                                    <tr>
                                        {{-- TICKET / TRANSACTION NUMBER --}}

                                        <td class="fw-bold text-primary text-center">
                                            {{ str_pad($txn->id, 6, '0', STR_PAD_LEFT) }}
                                        </td>

                                        {{-- BET TIME --}}
                                        <td class="text-center">
                                            {{ $txn->created_at->format('d M Y h:i A') }}
                                        </td>

                                        {{-- DRAW TIME --}}
                                        <td class="fw-bold text-danger text-center">
                                            {{ $drawObj->format('d M Y h:i A') }}
                                        </td>

                                        {{-- TOTAL BETS --}}
                                        <td class="text-center fw-bold">
                                            {{ $bets->count() }}
                                        </td>

                                        {{-- TOTAL POINTS --}}
                                        <td class="text-center fw-bold text-success">
                                            ₹{{ number_format($bets->sum('points'), 2) }}
                                        </td>

                                        {{-- ACTION --}}
                                        <td class="text-center">
                                            @if ($isLocked)
                                                <button class="btn btn-secondary btn-sm fw-bold px-3" disabled
                                                    title="Locked (within 20 seconds of draw)">
                                                    Locked
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('bets.cancel.draw') }}">
                                                    @csrf
                                                    <input type="hidden" name="transaction_id" value="{{ $txn->id }}">
                                                    <input type="hidden" name="draw_time" value="{{ $drawTime }}">

                                                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        @empty
            <div class="alert alert-secondary">
                No cancellable bets found.
            </div>
        @endforelse

        {{-- PAGINATION --}}
        @if ($transactions->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[action*="cancel"]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Cancel Bet?',
                        text: 'Are you sure you want to cancel this draw?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Cancel',
                        cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

@endsection
