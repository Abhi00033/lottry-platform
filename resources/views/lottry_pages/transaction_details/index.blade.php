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
                        <th class="text-warning" style="width: 50px;">Sr No.</th>
                        <th class="text-warning">TXN Number</th>
                        @if (auth()->user()->role_id != 3)
                            <th class="text-warning">User (Retailer)</th>
                            <th class="text-warning">Registered By (Agent)</th>
                        @endif
                        <th class="text-warning">Points Deducted</th>
                        <th class="text-warning">Balance After</th>
                        <th class="text-warning">Date & Time</th>
                        <th class="text-warning">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $index => $txn)
                        <tr>
                            <td class="text-secondary">{{ $transactions->firstItem() + $index }}</td>

                            {{-- TXN Number --}}
                            <td class="fw-bold text-warning">
                                {{ $txn->user ? $txn->transaction_number : '—' }}
                            </td>

                            @if (auth()->user()->role_id != 3)
                                {{-- User (Retailer) column — soft delete safe --}}
                                <td>
                                    @if (!$txn->user)
                                        <span class="text-secondary">Unknown User</span>
                                    @elseif ($txn->user_id == auth()->id())
                                        <span class="badge bg-primary">
                                            Me ({{ $txn->user->username }})
                                        </span>
                                    @else
                                        <span class="text-white">{{ $txn->user->username }}</span>
                                        @if ($txn->user->deleted_at)
                                            <span class="badge bg-danger ms-1" style="font-size:0.65rem;">Deleted</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Registered By (Agent) column — soft delete safe --}}
                                <td>
                                    @if ($txn->user && $txn->user->parent)
                                        <span class="fw-bold text-warning">
                                            {{ $txn->user->parent->username }}
                                        </span>
                                        @if ($txn->user->parent->deleted_at)
                                            <span class="badge bg-danger ms-1" style="font-size:0.65rem;">Deleted</span>
                                        @endif
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                            @endif

                            <td class="fw-bold text-danger">− ₹{{ number_format($txn->amount, 2) }}</td>
                            <td class="text-white">₹{{ number_format($txn->balance_after, 2) }}</td>
                            <td class="text-white-50" style="font-size: 0.85rem;">
                                {{ $txn->created_at->format('d M Y') }}<br>
                                <span class="text-warning">{{ $txn->created_at->format('h:i A') }}</span>
                            </td>
                            <td><small class="text-light">{{ $txn->remarks ?? '—' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role_id != 3 ? 8 : 5 }}" class="text-center py-5">
                                <div class="text-secondary">
                                    <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                                    No transactions found for
                                    <strong
                                        class="text-warning">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
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
