@extends('layouts.app')

@section('content')
    <style>
        .filter-card {
            background: rgba(20, 20, 30, 0.92);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .account-wrapper {
            background: rgba(20, 20, 30, 0.92);
            border-radius: 8px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .main-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #ffd700;
            text-decoration: underline;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .section-title {
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            margin-top: 20px;
            margin-bottom: 12px;
        }

        .report-title {
            text-align: center;
            color: #ffd700;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            max-width: 520px;
            margin: auto;
            margin-bottom: 28px;
            border-collapse: collapse;
            background: #1e1e2f;
        }

        .summary-table th {
            background: #146c43;
            color: #fff;
            border: 1px solid #2e8b57;
            padding: 7px;
            font-size: 13px;
            text-align: center;
        }

        .summary-table td {
            border: 1px solid #444;
            padding: 7px;
            color: #fff;
            font-size: 13px;
            text-align: center;
            background: #25253a;
        }

        .summary-table tr:hover td {
            background: #2f2f48;
        }

        .positive-net {
            color: #4caf50 !important;
            font-weight: 700;
        }

        .negative-net {
            color: #ff5252 !important;
            font-weight: 700;
        }

        .custom-input {
            height: 40px;
            border-radius: 5px;
            border: 1px solid #ccc;
            padding: 6px 10px;
            font-size: 14px;
        }

        .compact-btn {
            height: 40px;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 600;

            display: flex;
            align-items: center;
            justify-content: center;

            line-height: 1;
        }

        .top-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .mini-stat {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.4;
        }

        .mini-stat strong {
            color: #ffd700;
        }

        .form-label {
            color: #fff;
            font-size: 13px;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .filter-row {
            align-items: end;
        }

        .section-box {
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .positive-section {
            border: 1px solid rgba(76, 175, 80, 0.25);
            background: rgba(76, 175, 80, 0.04);
        }

        .negative-section {
            border: 1px solid rgba(255, 82, 82, 0.25);
            background: rgba(255, 82, 82, 0.04);
        }

        .section-heading {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .positive-heading {
            color: #4caf50;
        }

        .negative-heading {
            color: #ff5252;
        }

        .report-card {
            background: rgba(255, 255, 255, 0.03);
            padding: 15px;
            border-radius: 10px;
            height: 100%;
        }

        .summary-table {
            max-width: 100% !important;
            margin-bottom: 0 !important;
        }

        .report-title {
            font-size: 20px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {

            .summary-table {
                max-width: 100%;
            }

            .main-title {
                font-size: 18px;
            }

            .section-title {
                font-size: 14px;
            }

            .report-title {
                font-size: 14px;
            }

            .compact-btn {
                width: 100%;
                margin-top: 5px;
            }

            .top-stats {
                flex-direction: column;
            }
        }

        @media print {

            body {
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .filter-card {
                display: none !important;
            }

            .account-wrapper {
                background: #fff !important;
                box-shadow: none;
                border: none;
                padding: 0;
            }

            .summary-table {
                background: #fff !important;
            }

            .summary-table th {
                background: #333 !important;
                color: #fff !important;
            }

            .summary-table td {
                background: #fff !important;
                color: #000 !important;
                border: 1px solid #000 !important;
            }

            .main-title,
            .section-title,
            .report-title {
                color: #000 !important;
            }
        }
    </style>

    <div class="container py-3">

        {{-- FILTER SECTION --}}
        <div class="filter-card no-print">

            <form action="{{ route('account.index') }}" method="GET">

                <div class="row g-2 filter-row">

                    <div class="col-md-3">
                        <label class="form-label">From Date</label>

                        <input type="date" name="date_from" class="form-control custom-input" value="{{ $dateFrom }}"
                            max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">To Date</label>

                        <input type="date" name="date_to" class="form-control custom-input" value="{{ $dateTo }}"
                            max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-6 d-flex gap-2">

                        <button type="submit" class="btn btn-primary compact-btn">
                            Show Report
                        </button>

                        <a href="{{ route('account.index') }}" class="btn btn-danger compact-btn">
                            Reset
                        </a>

                        <button type="button" onclick="window.print()" class="btn btn-success compact-btn">
                            Print
                        </button>

                    </div>

                </div>

            </form>

        </div>

        {{-- MAIN REPORT --}}
        <div class="account-wrapper">

            <h4 class="main-title">
                Account Summary
            </h4>

            <div class="row g-4 mt-1">

                {{-- REPORT 1 --}}
                <div class="col-lg-6">

                    <div class="report-card">

                        <div class="report-title">
                            Report – 1
                        </div>

                        <table class="summary-table">

                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>

                            <tr>
                                <td>Play Point</td>
                                <td>
                                    {{ number_format($report1['play_point'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td>Commission</td>
                                <td>
                                    {{ number_format($report1['commission'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td>Win Point</td>
                                <td>
                                    {{ number_format($report1['win_point'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Net</strong>
                                </td>

                                <td class="{{ $report1['net'] >= 0 ? 'positive-net' : 'negative-net' }}">

                                    {{ $report1['net'] < 0 ? '-' : '' }}
                                    {{ number_format(abs($report1['net']), 2) }}

                                </td>
                            </tr>

                        </table>

                    </div>

                </div>

                {{-- REPORT 2 --}}
                <div class="col-lg-6">

                    <div class="report-card">

                        <div class="report-title">
                            Report – 2
                        </div>

                        <table class="summary-table">

                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>

                            <tr>
                                <td>Play Point</td>
                                <td>
                                    {{ number_format($report2['play_point'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td>Win Point</td>
                                <td>
                                    {{ number_format($report2['win_point'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Net</strong>
                                </td>

                                <td class="{{ $report2['net'] >= 0 ? 'positive-net' : 'negative-net' }}">

                                    {{ $report2['net'] < 0 ? '-' : '' }}
                                    {{ number_format(abs($report2['net']), 2) }}

                                </td>
                            </tr>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
