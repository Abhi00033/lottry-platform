@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- ===== Header ===== --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <h4 class="fw-bold text-warning m-0">
                <i class="fas fa-trophy me-2"></i>
                Claim / Results
            </h4>

            <div class="badge bg-dark border border-warning px-3 py-2">

                Balance:
                <span class="text-warning fw-bold">

                    ₹{{ number_format(auth()->user()->balance, 2) }}

                </span>

            </div>

        </div>

        {{-- ===== Table ===== --}}
        <div class="card bg-dark border-secondary shadow-sm">

            <div class="table-responsive">

                <table class="table table-dark table-hover align-middle text-center mb-0">

                    <thead class="border-secondary">

                        <tr>

                            <th class="text-warning">
                                Transaction
                            </th>

                            <th class="text-warning">
                                Draw Time
                            </th>

                            <th class="text-warning">
                                Points
                            </th>

                            <th class="text-warning">
                                Win
                            </th>

                            <th class="text-warning">
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($transactions as $transaction)
                            <tr>

                                {{-- Transaction --}}
                                <td class="fw-bold text-warning">

                                    {{ $transaction->transaction_number }}

                                </td>

                                {{-- Draw --}}
                                <td class="text-white-50 small">

                                    {{ \Carbon\Carbon::parse($transaction->draw_time)->format('d M Y') }}

                                    <br>

                                    <span class="text-warning">

                                        {{ \Carbon\Carbon::parse($transaction->draw_time)->format('h:i A') }}

                                    </span>

                                </td>

                                {{-- Points --}}
                                <td class="fw-bold text-info">

                                    {{ $transaction->total_points }}

                                </td>

                                {{-- Win --}}
                                <td>

                                    @if ($transaction->total_win > 0)
                                        <span class="text-success fw-bold">

                                            ₹{{ number_format($transaction->total_win, 2) }}

                                        </span>
                                    @else
                                        <span class="text-secondary">

                                            —

                                        </span>
                                    @endif

                                </td>

                                {{-- Status --}}
                                <td>

                                    <button type="button"
                                        class="btn btn-sm fw-bold px-3
                                    {{ $transaction->status == 'won'
                                        ? 'btn-success'
                                        : ($transaction->status == 'pending'
                                            ? 'btn-warning text-dark'
                                            : 'btn-danger') }}"
                                        data-bs-toggle="modal" data-bs-target="#transactionModal{{ $loop->index }}">

                                        {{ strtoupper($transaction->status) }}

                                    </button>

                                </td>

                            </tr>

                            {{-- ===== Modal ===== --}}
                            <div class="modal fade" id="transactionModal{{ $loop->index }}" tabindex="-1">

                                <div class="modal-dialog modal-dialog-centered">

                                    <div class="modal-content bg-dark border-secondary rounded-4 text-white">

                                        {{-- Header --}}
                                        <div class="modal-header border-secondary">

                                            <h5 class="modal-title text-warning fw-bold">

                                                Transaction Details

                                            </h5>

                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>

                                        </div>

                                        {{-- Body --}}
                                        <div class="modal-body px-4 py-4">

                                            <div class="d-flex flex-column gap-3">

                                                {{-- Transaction --}}
                                                <div class="d-flex justify-content-between">

                                                    <span class="text-white-50">

                                                        Transaction

                                                    </span>

                                                    <span class="fw-bold text-warning">

                                                        {{ $transaction->transaction_number }}

                                                    </span>

                                                </div>

                                                {{-- Draw Time --}}
                                                <div class="d-flex justify-content-between">

                                                    <span class="text-white-50">

                                                        Draw Time

                                                    </span>

                                                    <span class="fw-bold text-info">

                                                        {{ \Carbon\Carbon::parse($transaction->draw_time)->format('d M Y h:i A') }}

                                                    </span>

                                                </div>

                                                {{-- Points --}}
                                                <div class="d-flex justify-content-between">

                                                    <span class="text-white-50">

                                                        Total Points

                                                    </span>

                                                    <span class="fw-bold text-primary">

                                                        {{ $transaction->total_points }}

                                                    </span>

                                                </div>

                                                {{-- Winning Details --}}
                                                @if ($transaction->status == 'won')
                                                    @php
                                                        $wonBets = $transaction->bets->where('status', 'won');
                                                    @endphp

                                                    {{-- Winning Number --}}
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-white-50">
                                                            Winning Numbers
                                                        </span>

                                                        <span class="fw-bold text-warning">
                                                            {{ $wonBets->pluck('number')->implode(', ') }}
                                                        </span>
                                                    </div>

                                                    {{-- Win Amount --}}
                                                    <div class="d-flex justify-content-between">

                                                        <span class="text-white-50">

                                                            Win Amount

                                                        </span>

                                                        <span class="fw-bold text-success fs-4">

                                                            ₹{{ number_format($transaction->total_win, 2) }}

                                                        </span>

                                                    </div>
                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @empty

                            <tr>

                                <td colspan="5" class="py-5">

                                    <div class="text-secondary">

                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>

                                        No results found

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>
@endsection
