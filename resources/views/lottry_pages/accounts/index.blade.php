@extends('layouts.app')

@section('content')
    <style>
        .lotto-section-header {
            background: var(--bg-main);
            color: var(--text-light);
            padding: 1.2rem;
            border-radius: 6px;
            margin-bottom: 1.2rem;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: .5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, .25);
        }

        .lotto-card {
            background: var(--bg-card-light);
            backdrop-filter: blur(6px);
            padding: 1.5rem;
            border-radius: 6px;
            color: var(--text-light);
            box-shadow: 0 2px 6px rgba(0, 0, 0, .3);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .lotto-input {
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.25);
            padding: .45rem .7rem;
            font-size: .95rem;
            border-radius: 4px;
            min-width: 160px;
        }

        .lotto-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .lotto-table th {
            background: var(--btn-green);
            color: white;
            padding: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            font-size: .9rem;
            text-align: center;
        }

        .lotto-table td {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            padding: .7rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .print-btn {
            margin: 1rem auto;
            display: block;
        }
    </style>

    <div class="container py-4">

        <div class="lotto-section-header">
            ACCOUNTS REPORT
        </div>

        {{-- FILTER BOX --}}
        <div class="lotto-card text-center">

            <form>
                <label class="fw-bold me-2">Show Report :</label>
                <input type="date" class="lotto-input" value="{{ date('Y-m-d') }}">
                <span class="fw-bold px-2">TO</span>
                <input type="date" class="lotto-input" value="{{ date('Y-m-d') }}">

                <button type="button" class="btn-lotto-yellow btn-boxed ms-2">
                    Show
                </button>
            </form>
        </div>

        {{-- FIRST REPORT --}}
        <div class="lotto-card">

            <h5 class="text-center fw-bold text-warning mb-3">
                First Report - Summary
            </h5>

            <table class="lotto-table">
                <tr>
                    <th>Play Point</th>
                    <th>Commission</th>
                    <th>Win</th>
                    <th>Net</th>
                </tr>
                <tr>
                    <td>0.00</td>
                    <td>0.00</td>
                    <td>0.00</td>
                    <td>0.00</td>
                </tr>
            </table>

            <button class="btn-lotto-green btn-boxed print-btn">
                Print Report
            </button>
        </div>

        {{-- SECOND REPORT --}}
        <div class="lotto-card">

            <h5 class="text-center fw-bold text-warning mb-3">
                Second Report – Commission vs Net
            </h5>

            <table class="lotto-table">
                <tr>
                    <th>Play</th>
                    <th>Win</th>
                    <th>Net</th>
                </tr>
                <tr>
                    <td>0.00</td>
                    <td>0.00</td>
                    <td>0.00</td>
                </tr>
            </table>
        </div>

    </div>
@endsection
