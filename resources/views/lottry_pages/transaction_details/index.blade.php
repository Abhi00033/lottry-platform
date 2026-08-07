@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="fw-bold text-warning m-0">
                <i class="fas fa-list-alt me-2"></i> Betting Transactions (Today)
            </h3>
            <div class="badge bg-dark border border-warning px-3 py-2" style="font-size: 1rem;">
                My Balance: <span class="text-warning fw-bold">₹{{ number_format(auth()->user()->balance, 2) }}</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="card bg-dark border-secondary shadow-sm">
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
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No transaction records found for today.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($transactions->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>
@endsection
