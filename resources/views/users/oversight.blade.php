@extends('layouts.app')

@section('content')
    <style>
        .pagination .page-link {
            background-color: #1a1d20;
            border-color: #373b3e;
            color: #ffc107;
        }

        .pagination .page-item.active .page-link {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #1a1d20;
            color: #6c757d;
        }

        .x-small-text {
            font-size: 0.75rem;
        }
    </style>

    <div class="container py-4 text-white">
        {{-- Header & Filter Bar --}}
        <div class="card bg-dark border-secondary mb-4 shadow-lg">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-xl-2 mb-3 mb-xl-0">
                        <h3 class="text-warning fw-bold mb-0">{{ strtoupper($user->username) }}</h3>
                        <span class="badge bg-secondary">{{ $user->role->name }}</span>
                    </div>
                    <div class="col-xl-10">
                        <form action="{{ route('users.oversight', $user->id) }}" method="GET"
                            class="row g-2 justify-content-xl-end align-items-end text-start">

                            <div class="col-auto">
                                <label class="text-info small mb-1 fw-bold">SEARCH</label>
                                <input type="text" name="search" placeholder="Number/Status..."
                                    class="form-control form-control-sm bg-dark text-white border-secondary"
                                    value="{{ $search }}">
                            </div>

                            <div class="col-auto">
                                <label class="text-info small mb-1 fw-bold">DRAW TIME</label>
                                <select name="draw_time"
                                    class="form-control form-control-sm bg-dark text-white border-secondary">
                                    <option value="">All Draws</option>
                                    @foreach ($availableDrawTimes as $dt)
                                        <option value="{{ $dt->draw_time }}"
                                            {{ $drawTimeFilter == $dt->draw_time ? 'selected' : '' }}>
                                            {{ $dt->draw_time->format('h:i A') }} ({{ $dt->draw_time->format('d M') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <label class="text-info small mb-1 fw-bold">FROM</label>
                                <input type="date" name="start_date"
                                    class="form-control form-control-sm bg-dark text-white border-secondary"
                                    value="{{ $startDate }}">
                            </div>

                            <div class="col-auto">
                                <label class="text-info small mb-1 fw-bold">TO</label>
                                <input type="date" name="end_date"
                                    class="form-control form-control-sm bg-dark text-white border-secondary"
                                    value="{{ $endDate }}">
                            </div>

                            <div class="col-auto">
                                <div class="btn-group shadow-sm">
                                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">FILTER</button>
                                    <a href="{{ route('users.oversight', $user->id) }}"
                                        class="btn btn-secondary btn-sm">RESET</a>
                                    <a href="{{ $backUrl }}" class="btn btn-outline-light btn-sm">
                                        <i class="fa fa-arrow-left"></i> BACK
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 1: Period Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card bg-dark border-info border-2 h-100 shadow-sm">
                    <div class="card-body p-3 text-center">
                        <p class="text-info small mb-1 fw-bold text-uppercase">Tickets Sold</p>
                        <h3 class="mb-0 text-white fw-bold">{{ number_format($totalQty) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-dark border-warning border-2 h-100 shadow-sm">
                    <div class="card-body p-3 text-center">
                        <p class="text-warning small mb-1 fw-bold text-uppercase">Turnover</p>
                        <h3 class="mb-0 text-white fw-bold">₹{{ number_format($totalPlay, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div
                    class="card bg-dark {{ $periodProfit >= 0 ? 'border-success' : 'border-danger' }} border-2 h-100 shadow-sm">
                    <div class="card-body p-3 text-center">
                        <p
                            class="{{ $periodProfit >= 0 ? 'text-success' : 'text-danger' }} small mb-1 fw-bold text-uppercase">
                            Net P/L</p>
                        <h3 class="mb-0 text-white fw-bold">₹{{ number_format($periodProfit, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-dark border-light border-2 h-100 shadow-sm">
                    <div class="card-body p-3 text-center">
                        <p class="text-white-50 small mb-1 fw-bold text-uppercase">Wallet</p>
                        <h3 class="mb-0 text-warning fw-bold">₹{{ number_format($user->balance, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top 10 Numbers Heat Map --}}
        <div class="card bg-dark border-warning mb-4 shadow">
            <div class="card-header border-warning bg-black py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-warning fw-bold text-uppercase"><i class="fa fa-fire me-2"></i>Top 10 Numbers (By
                    Liability)</h6>
                @if ($drawTimeFilter)
                    <span class="badge bg-warning text-dark">Filtered:
                        {{ date('h:i A', strtotime($drawTimeFilter)) }}</span>
                @endif
            </div>
            <div class="card-body p-2">
                <div class="row g-2">
                    @forelse($topNumbers as $top)
                        <div class="col-md-2 col-4">
                            <div class="border border-secondary rounded p-2 text-center bg-black bg-opacity-25 shadow-sm">
                                <div class="text-info fw-bold fs-5">{{ $top->number }}</div>
                                <div class="text-white-50 x-small-text">Amt: ₹{{ number_format($top->total_spent, 0) }}
                                </div>
                                <div class="text-warning fw-bold x-small-text">Qty: {{ $top->total_qty }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-2 italic">No heavy betting detected for this selection.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Main Bet Logs Table --}}
        <div class="card bg-dark border-secondary shadow-lg">
            <div
                class="card-header border-secondary bg-black bg-opacity-50 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-warning fw-bold">Detailed Bet Logs</h5>
                <span class="badge bg-secondary px-3 py-2">{{ $totalBets }} Records Found</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead>
                            <tr class="bg-black">
                                <th class="ps-3 py-3 border-secondary text-info">DRAW TIME</th>
                                <th class="border-secondary text-info">SERIES</th>
                                <th class="text-center border-secondary text-info">NUMBER</th>
                                <th class="border-secondary text-info">QTY</th>
                                <th class="border-secondary text-info">AMOUNT</th>
                                <th class="text-center pe-3 border-secondary text-info">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBets as $bet)
                                <tr class="border-secondary">
                                    <td class="ps-3 text-white">
                                        {{ $bet->draw_time->format('h:i A') }}<br>
                                        <small class="text-white-50">{{ $bet->draw_time->format('d M') }}</small>
                                    </td>
                                    <td class="text-white-50">{{ $bet->series_group }}</td>
                                    <td class="text-center">
                                        <span class="text-info fw-bold fs-5"
                                            style="text-shadow: 0px 0px 8px rgba(0,255,255,0.3);">
                                            {{ $bet->number }}
                                        </span>
                                    </td>
                                    <td class="text-white">{{ $bet->qty }}</td>
                                    <td class="fw-bold text-white">₹{{ number_format($bet->total_amount, 2) }}</td>
                                    <td class="text-center pe-3">
                                        <span
                                            class="badge rounded-pill px-3 py-2 {{ $bet->status == 'won' ? 'bg-success' : ($bet->status == 'lost' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ strtoupper($bet->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-white-50 fs-5 italic">
                                        <i class="fa fa-search me-2"></i> No records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($recentBets->hasPages())
                <div class="card-footer border-secondary bg-black bg-opacity-50 py-3">
                    <div class="d-flex justify-content-center">
                        {!! $recentBets->links() !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
