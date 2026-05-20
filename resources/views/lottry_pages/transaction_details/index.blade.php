@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- ===== Header ===== --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-warning m-0">Betting Transactions</h3>
            <span class="badge bg-dark border border-warning p-2" style="font-size: 1rem;">
                My Balance: <span class="text-warning">₹{{ number_format(auth()->user()->balance, 2) }}</span>
            </span>
        </div>

        {{-- ===== Filter Bar ===== --}}
        <div class="card bg-dark border-secondary mb-3 px-3 py-2">
            <div class="d-flex align-items-center flex-wrap gap-3">

                <span class="fw-bold text-white" style="white-space: nowrap; font-size: 0.95rem;">
                    <i class="fas fa-calendar-alt me-1 text-warning"></i> Filter by Date:
                </span>

                <form action="{{ route('transactions.index') }}" method="GET" id="dateFilterForm"
                    class="d-flex align-items-center gap-2 m-0">

                    <input type="date" name="date"
                        class="form-control form-control-sm bg-dark border-secondary text-white"
                        style="max-width: 175px; color-scheme: dark;" value="{{ $selectedDate }}"
                        max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                        onchange="document.getElementById('dateFilterForm').submit()">

                    @if ($selectedDate !== \Carbon\Carbon::today()->format('Y-m-d'))
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-danger"
                            style="white-space: nowrap;">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    @endif
                </form>

                <span style="font-size: 0.88rem; color: #aaa;">
                    Showing: <strong
                        class="text-warning">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
                </span>

            </div>
        </div>

        {{-- ===== Table ===== --}}
        <div class="table-responsive">
            <table class="table table-dark table-bordered table-hover align-middle text-center mb-0">
                <thead style="background-color: #2a2a2a;">
                    <tr>
                        <th class="text-warning">Sr No.</th>
                        <th class="text-warning">Draw Time</th>
                        <th class="text-warning">Sale Points</th>
                        <th class="text-warning">Cancel Points</th>
                        <th class="text-warning">Win Points</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($transactions as $index => $txn)
                        <tr>

                            <td>
                                {{ $transactions->firstItem() + $index }}
                            </td>

                            <td class="fw-bold text-info">
                                {{ \Carbon\Carbon::parse($txn->draw_time)->format('d M Y h:i A') }}
                            </td>

                            <td class="fw-bold text-success">
                                ₹{{ number_format($txn->sale_points, 2) }}
                            </td>

                            <td class="fw-bold text-danger">
                                ₹{{ number_format($txn->cancel_points, 2) }}
                            </td>

                            <td class="fw-bold text-warning">
                                ₹{{ number_format($txn->win_points, 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center py-5">

                                <div class="text-secondary">

                                    No records found for

                                    <strong class="text-warning">
                                        {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                                    </strong>

                                </div>

                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- ===== Pagination ===== --}}
        @if ($transactions->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>
@endsection
