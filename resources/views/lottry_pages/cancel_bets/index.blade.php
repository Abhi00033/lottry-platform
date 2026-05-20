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
                <div
                    class="card-header
                            bg-dark
                            text-warning
                            d-flex
                            justify-content-between
                            align-items-center">

                    <div>

                        <strong>
                            Transaction:
                        </strong>

                        {{ $txn->transaction_number }}

                    </div>

                    {{-- <div>

                        Total :
                        ₹{{ number_format($txn->amount, 2) }}

                    </div> --}}
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

                                    <th width="180">
                                        Transaction No
                                    </th>

                                    <th width="220">
                                        Bet Time
                                    </th>

                                    <th width="220">
                                        Draw Time
                                    </th>

                                    <th width="120">
                                        Total Bets
                                    </th>

                                    <th width="140">
                                        Total Points
                                    </th>

                                    <th width="140">
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach ($groupedBets as $drawTime => $bets)
                                    @php

                                        $drawObj = \Carbon\Carbon::parse($drawTime);

                                    @endphp

                                    <tr>

                                        {{-- TRANSACTION NUMBER --}}
                                        <td class="fw-bold text-primary">

                                            {{ $txn->transaction_number }}

                                        </td>

                                        {{-- BET TIME --}}
                                        <td>

                                            {{ $txn->created_at->format('d M Y h:i A') }}

                                        </td>

                                        {{-- DRAW TIME --}}
                                        <td class="fw-bold text-danger">

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

                                            <form method="POST" action="{{ route('bets.cancel.draw') }}">

                                                @csrf

                                                <input type="hidden" name="transaction_id" value="{{ $txn->id }}">

                                                <input type="hidden" name="draw_time" value="{{ $drawTime }}">

                                                <button class="btn btn-danger btn-sm fw-bold px-3">

                                                    Cancel

                                                </button>

                                            </form>

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
