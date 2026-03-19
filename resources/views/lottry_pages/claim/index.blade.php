@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- ===== Header ===== --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="fw-bold text-warning m-0">
                <i class="fas fa-trophy me-2"></i> Claim / Results
            </h3>
            <span class="badge bg-dark border border-warning p-2" style="font-size: 1rem;">
                My Balance: <span class="text-warning">₹{{ number_format(auth()->user()->balance, 2) }}</span>
            </span>
        </div>

        {{-- ===== Filter Bar ===== --}}
        <div class="card bg-dark border-secondary mb-3 px-3 py-2">
            <form action="{{ route('claim.index') }}" method="GET" id="claimFilterForm">
                <div class="row g-2 align-items-end">

                    {{-- Date Filter --}}
                    <div class="col-md-4 col-sm-12">
                        <label class="text-white-50 small mb-1">
                            <i class="fas fa-calendar-alt me-1 text-warning"></i> Draw Date
                        </label>
                        <input type="date" name="date"
                            class="form-control form-control-sm bg-dark text-white border-secondary"
                            style="color-scheme: dark;" value="{{ $selectedDate }}"
                            max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                            onchange="document.getElementById('claimFilterForm').submit()">
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="text-white-50 small mb-1">
                            <i class="fas fa-filter me-1 text-warning"></i> Status
                        </label>
                        <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary"
                            onchange="document.getElementById('claimFilterForm').submit()">
                            <option value="" {{ $statusFilter === '' ? 'selected' : '' }}>All Results</option>
                            <option value="won" {{ $statusFilter === 'won' ? 'selected' : '' }}>Won Only</option>
                            <option value="lost" {{ $statusFilter === 'lost' ? 'selected' : '' }}>Lost Only</option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-5 col-sm-6 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        @if ($selectedDate !== \Carbon\Carbon::today()->format('Y-m-d') || $statusFilter !== '')
                            <a href="{{ route('claim.index') }}" class="btn btn-outline-danger btn-sm px-3">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        @endif
                    </div>

                </div>
            </form>

            {{-- Showing date info --}}
            <div class="mt-2 d-flex align-items-center gap-3">
                <small class="text-white-50">
                    Showing:
                    <strong class="text-warning">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
                </small>
                @if ($statusFilter)
                    <span class="badge {{ $statusFilter === 'won' ? 'bg-success' : 'bg-danger' }}">
                        {{ ucfirst($statusFilter) }} Only
                    </span>
                @endif
                <small class="text-white-50">
                    Total: <strong class="text-warning">{{ $bets->total() }}</strong> records
                </small>
            </div>
        </div>

        {{-- ===== Summary Cards ===== --}}
        @php
            $wonCount = $bets->getCollection()->where('status', 'won')->count();
            $lostCount = $bets->getCollection()->where('status', 'lost')->count();
            $totalWon = $bets->getCollection()->where('status', 'won')->sum('total_amount');
        @endphp

        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="card bg-dark border-success text-center py-2">
                    <div class="text-success fw-bold" style="font-size:1.3rem;">{{ $wonCount }}</div>
                    <small class="text-white-50">Won (this page)</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card bg-dark border-danger text-center py-2">
                    <div class="text-danger fw-bold" style="font-size:1.3rem;">{{ $lostCount }}</div>
                    <small class="text-white-50">Lost (this page)</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card bg-dark border-warning text-center py-2">
                    <div class="text-warning fw-bold" style="font-size:1.3rem;">₹{{ number_format($totalWon, 2) }}</div>
                    <small class="text-white-50">Won Amount (this page)</small>
                </div>
            </div>
        </div>

        {{-- ===== Table ===== --}}
        <div class="table-responsive">
            <table class="table table-dark table-bordered table-hover align-middle text-center mb-0">
                <thead style="background-color: #1e1e2e;">
                    <tr>
                        <th class="text-warning">Sr. No.</th>
                        <th class="text-warning">Transaction No.</th>
                        <th class="text-warning">Bet Time</th>
                        <th class="text-warning">Draw Time</th>
                        <th class="text-warning">Number</th>
                        <th class="text-warning">Points</th>
                        <th class="text-success">Win Amount</th>
                        <th class="text-warning">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bets as $bet)
                        <tr>
                            <td class="text-secondary">
                                {{ ($bets->currentPage() - 1) * $bets->perPage() + $loop->iteration }}
                            </td>
                            <td class="fw-bold text-warning" style="font-size:0.85rem;">
                                {{ $bet->transaction->transaction_number ?? '—' }}
                            </td>
                            <td class="text-white-50" style="font-size:0.85rem;">
                                {{ $bet->created_at->format('d M Y') }}<br>
                                <span class="text-warning">{{ $bet->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="text-white-50" style="font-size:0.85rem;">
                                {{ \Carbon\Carbon::parse($bet->draw_time)->format('d M Y') }}<br>
                                <span
                                    class="text-warning">{{ \Carbon\Carbon::parse($bet->draw_time)->format('h:i A') }}</span>
                            </td>
                            <td class="fw-bold text-white">
                                {{ $bet->number }}
                            </td>
                            <td class="text-info fw-bold">
                                {{ $bet->points }}
                            </td>
                            <td class="fw-bold">
                                @if ($bet->status === 'won')
                                    @php
                                        $commissionRate = auth()->user()->commision ?? 0;
                                        $netMultiplier = 100 - $commissionRate;
                                        $winAmt = $bet->points * $netMultiplier;
                                    @endphp
                                    <span class="text-success">₹{{ number_format($winAmt, 2) }}</span>
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($bet->status === 'won')
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check me-1"></i> WON
                                    </span>
                                @else
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="fas fa-times me-1"></i> LOST
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                                <span class="text-secondary">
                                    No results found for
                                    <strong class="text-warning">
                                        {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                                    </strong>
                                    @if ($statusFilter)
                                        with status <strong class="text-warning">{{ ucfirst($statusFilter) }}</strong>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== Pagination ===== --}}
        @if ($bets->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $bets->links() }}
            </div>
        @endif

    </div>
@endsection
