@extends('layouts.app')

@section('content')
    <style>
        .lotto-section-header {
            background: var(--bg-main);
            color: var(--text-light);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.75rem;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: .5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, .25);
        }

        .lotto-card {
            background: var(--bg-card-light);
            backdrop-filter: blur(6px);
            padding: 0.75rem 1.2rem;
            border-radius: 6px;
            color: var(--text-light);
            box-shadow: 0 2px 6px rgba(0, 0, 0, .3);
            margin-bottom: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .lotto-input {
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.25);
            padding: .35rem .6rem;
            font-size: .9rem;
            border-radius: 4px;
            min-width: 150px;
            color: #000;
        }

        .lotto-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }

        .lotto-table th {
            background: #1a5c1a;
            color: #ffffff;
            padding: .5rem .6rem;
            font-weight: 700;
            text-transform: uppercase;
            font-size: .85rem;
            text-align: center;
            border: 1px solid #2d7a2d;
        }

        .lotto-table td {
            background: #1e1e2e;
            padding: 0.6rem .8rem;
            text-align: center;
            border: 1px solid #333355;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .lotto-table td .stat-mini {
            font-size: 0.72rem;
            font-weight: 400;
            color: #aaaaaa;
            margin-top: 2px;
        }

        .lotto-table tr:hover td {
            background: #252540;
        }

        .print-btn {
            margin: 0.5rem auto;
            display: block;
        }

        .report-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #ffd700;
            margin-bottom: 0.3rem;
            text-align: center;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .lotto-card {
                box-shadow: none;
                border: 1px solid #ccc;
            }

            .lotto-table td {
                background: #fff !important;
                color: #000 !important;
            }

            .lotto-table th {
                background: #333 !important;
                color: #fff !important;
            }
        }
    </style>

    <div class="container py-2">

        {{-- ===== Page Header ===== --}}
        <div class="lotto-section-header">
            ACCOUNTS REPORT
        </div>

        {{-- ===== Filter + User Info in one row ===== --}}
        <div class="lotto-card no-print">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">

                {{-- Filter Form --}}
                <form action="{{ route('account.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">

                    <label class="fw-bold text-warning mb-0 small">From:</label>
                    <input type="date" name="date_from" class="lotto-input" value="{{ $dateFrom }}"
                        max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">

                    <label class="fw-bold text-warning mb-0 small">To:</label>
                    <input type="date" name="date_to" class="lotto-input" value="{{ $dateTo }}"
                        max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">

                    <button type="submit" class="btn-lotto-yellow btn-boxed">
                        <i class="fas fa-search me-1"></i> Show
                    </button>

                    @if ($dateFrom !== \Carbon\Carbon::today()->format('Y-m-d') || $dateTo !== \Carbon\Carbon::today()->format('Y-m-d'))
                        <a href="{{ route('account.index') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    @endif
                </form>

                {{-- User Info inline --}}
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <div class="small">
                        <span class="text-white-50">User:</span>
                        <strong class="text-warning ms-1">{{ auth()->user()->username }}</strong>
                    </div>
                    <div class="small">
                        <span class="text-white-50">Commission:</span>
                        <strong class="text-warning ms-1">{{ $commissionRate }}%</strong>
                    </div>
                    <div class="small">
                        <span class="text-white-50">Multiplier:</span>
                        <strong class="text-warning ms-1">{{ $netMultiplier }}x</strong>
                    </div>
                    <div class="small">
                        <span class="text-white-50">Balance:</span>
                        <strong class="text-success ms-1">₹{{ number_format(auth()->user()->balance, 2) }}</strong>
                    </div>
                </div>

            </div>

            {{-- Range + counts --}}
            <div class="mt-1">
                <small class="text-white-50">
                    Showing:
                    <strong class="text-warning">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}</strong>
                    to
                    <strong class="text-warning">{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</strong>
                    &nbsp;|&nbsp; Total: <strong class="text-warning">{{ $totalBets }}</strong>
                    &nbsp;|&nbsp; <span class="text-success fw-bold">Won: {{ $wonBets }}</span>
                    &nbsp;|&nbsp; <span class="text-danger fw-bold">Lost: {{ $lostBets }}</span>
                </small>
            </div>
        </div>

        {{-- ===== Report 1 — Points Summary ===== --}}
        <div class="lotto-card">
            <div class="report-title">Report 1 — Points Summary</div>

            <table class="lotto-table">
                <thead>
                    <tr>
                        <th>Play Points</th>
                        <th>Commission ({{ $commissionRate }}%)</th>
                        <th>Win Points</th>
                        <th>Net (Play - Win)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span style="color:#4fc3f7; font-size:1.2rem;">{{ number_format($totalPlayPoints, 2) }}</span>
                            <div class="stat-mini">Total points bet</div>
                        </td>
                        <td>
                            <span style="color:#ffd54f; font-size:1.2rem;">{{ number_format($totalCommission, 2) }}</span>
                            <div class="stat-mini">Deducted from play</div>
                        </td>
                        <td>
                            <span style="color:#81c784; font-size:1.2rem;">{{ number_format($totalWin, 2) }}</span>
                            <div class="stat-mini">Won × {{ $netMultiplier }}x</div>
                        </td>
                        <td>
                            <span style="color:{{ $netFirst >= 0 ? '#ef5350' : '#81c784' }}; font-size:1.2rem;">
                                {{ number_format($netFirst, 2) }}
                            </span>
                            <div class="stat-mini">{{ $netFirst >= 0 ? 'House profit' : 'User profit' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button class="btn-lotto-green btn-boxed print-btn no-print" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Report
            </button>
        </div>

        {{-- ===== Report 2 — Amount Summary ===== --}}
        <div class="lotto-card">
            <div class="report-title">Report 2 — Amount Summary (₹)</div>

            <table class="lotto-table">
                <thead>
                    <tr>
                        <th>Total Play (₹)</th>
                        <th>Total Win (₹)</th>
                        <th>Net (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span style="color:#4fc3f7; font-size:1.2rem;">₹{{ number_format($totalPlay, 2) }}</span>
                            <div class="stat-mini">Total amount wagered</div>
                        </td>
                        <td>
                            <span style="color:#81c784; font-size:1.2rem;">₹{{ number_format($totalWinAmount, 2) }}</span>
                            <div class="stat-mini">Total amount won</div>
                        </td>
                        <td>
                            <span style="color:{{ $netSecond >= 0 ? '#ef5350' : '#81c784' }}; font-size:1.2rem;">
                                ₹{{ number_format($netSecond, 2) }}
                            </span>
                            <div class="stat-mini">{{ $netSecond >= 0 ? 'House profit' : 'User profit' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
@endsection
