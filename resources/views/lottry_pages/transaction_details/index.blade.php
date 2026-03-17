@extends('layouts.app')

@section('content')
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-warning m-0">Betting Transactions</h3>
            <span class="badge bg-dark border border-warning p-2" style="font-size: 1rem;">
                My Balance: <span class="text-warning">₹{{ number_format(auth()->user()->balance, 2) }}</span>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>TXN Number</th>
                        @if (auth()->user()->role_id != 3)
                            <th>User (Retailer)</th>
                            <th>Registered By (Agent)</th>
                        @endif
                        <th>Points Deducted</th>
                        <th>Balance After</th>
                        <th>Date & Time</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $txn)
                        <tr>
                            <td class="fw-bold text-warning">
                                {{-- Uses the Accessor: USER-RANDOM-ID --}}
                                {{ $txn->transaction_number }}
                            </td>

                            @if (auth()->user()->role_id != 3)
                                <td>
                                    {{-- Identify if the row is the logged-in user's own transaction --}}
                                    @if ($txn->user_id == auth()->id())
                                        <span class="badge bg-primary">Me ({{ $txn->user->username }})</span>
                                    @else
                                        {{ $txn->user->username }}
                                    @endif
                                </td>
                                <td>
                                    @if ($txn->user->parent)
                                        <span class="text-warning fw-bold">{{ $txn->user->parent->username }}</span>
                                    @else
                                        <span class="text-white-50">System</span>
                                    @endif
                                </td>
                            @endif

                            <td class="text-danger fw-bold">- ₹{{ number_format($txn->amount, 2) }}</td>
                            <td>₹{{ number_format($txn->balance_after, 2) }}</td>
                            <td class="small">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                            {{-- Brighter text for remarks on dark background --}}
                            <td><small class="text-light">{{ $txn->remarks }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role_id != 3 ? 6 : 4 }}" class="text-center py-4">
                                No betting transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
