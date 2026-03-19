@extends('layouts.app')

@section('content')
    <style>
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 1.03rem;
        }

        .dash-table th {
            background: #000;
            color: var(--btn-yellow);
            padding: 0rem .4rem !important;
            font-weight: 700;
            border: 1px solid #b87300;
            text-align: center;
            white-space: nowrap;
        }

        .dash-table td {
            background: #f7f6c5;
            color: var(--text-dark);
            /* padding: .45rem .4rem !important; */
            border: 1px solid #b87300;
            font-weight: 600;
            text-align: center;
        }

        .btn-adv-draw {
            background: var(--btn-yellow);
            color: #000;
            font-weight: 700;
            padding: 0rem .8rem;
            border-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.35);
            box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.4);
            transition: .2s;
        }

        .btn-adv-draw:hover {
            background: var(--btn-yellow-hover);
            transform: translateY(-2px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        /* Radio Filter Styling */
        .filter-radio {
            accent-color: var(--btn-yellow);
            vertical-align: middle;
        }

        /* Base style for the series tab */
        .series-tab {
            user-select: none;
            transition: all 0.2s ease;
            background: #ffb703 !important;
            color: #000 !important;
            border: 1px solid #b87300 !important;
            font-weight: 600;
        }

        /* Style when the internal checkbox is checked */
        .series-tab:has(input:checked) {
            background: #000 !important;
            /* Switch to black background */
            color: #ffb703 !important;
            /* Switch to yellow text */
            border-color: #ffb703 !important;
            box-shadow: inset 0 0 5px rgba(255, 183, 3, 0.5);
        }

        /* Visual feedback for the checkbox itself */
        .series-select {
            cursor: pointer;
            filter: invert(0);
        }

        /* Change checkbox color when checked to match theme */
        .series-tab:has(input:checked) .series-select {
            filter: invert(1) hue-rotate(180deg);
        }

        /* Ensure the custom background stays when checked */
        .series-tab:has(input:checked) {
            /* We keep the dynamic background but use a heavy border to show it is active */
            border: 3px solid #000 !important;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            transform: translateY(-1px);
        }

        /* Optional: Make the text inside even bolder when selected */
        .series-tab:has(input:checked) span {
            text-decoration: underline;
        }

        /* Style for the badge to pop against the colored row */
        .series-result-badge {
            box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* grid second div css Container sizing and alignment */


        .justify-content-between {
            justify-content: space-between !important;
        }

        /* Base button style for the control strip */
        .btn-control {
            border: 1px solid #800000;
            font-weight: 800;
            padding: 3px 12px;
            border-radius: 3px;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
            min-width: 60px;
        }

        /* Green Gradient (High/Low) */
        .btn-green {
            background: linear-gradient(to bottom, #00ff00 0%, #008000 100%);
            color: #fff;
            text-shadow: 1px 1px 1px #000;
        }

        /* Orange/Yellow (LP/FP/Results) */
        .btn-orange {
            background: #ffae00;
            color: #000;
        }

        /* Center Filter Boxes */
        .filter-box {
            border: 1px solid #800000;
            padding: 2px 15px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            background: #fff;
        }

        .grid-radio {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        /* Empty Input field on the right */
        .empty-input-box {
            width: 45px;
            height: 32px;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
        }

        .btn-control:hover {
            filter: brightness(1.1);
        }

        /* Center Filter Cards - Styled for better visibility */
        .filter-card {
            border: 1px solid #800000;
            padding: 4px 20px;
            /* Increased padding for width */
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            background: #fff;
            min-width: 100px;
            /* Ensures they aren't too small */
            justify-content: center;
            transition: background 0.2s;
        }

        .filter-text {
            font-weight: 800;
            font-size: 1.1rem;
            color: #000;
        }

        /* Styling for the Right-side Input Field */
        .lp-input {
            width: 60px;
            height: 34px;
            border: 1px solid #000;
            background: #fff;
            text-align: center;
            font-weight: 700;
            border-radius: 2px;
            outline: none;
        }

        .lp-input:focus {
            border-color: #ffae00;
            box-shadow: 0 0 3px rgba(255, 174, 0, 0.5);
        }

        /* High/Low Green Gradient */
        /* High/Low Green Gradient with increased width */
        .btn-green {
            background: linear-gradient(to bottom, #00ff00 0%, #008000 100%) !important;
            color: #fff !important;
            text-shadow: 1px 1px 1px #000;
            border: 1px solid #800000;
            /* Increased width for a better look */
            min-width: 100px;
            padding: 6px 15px !important;
        }

        /* Ensure the control strip remains balanced */
        .btn-orange {
            background: #ffae00 !important;
            color: #000 !important;
            border: 1px solid #800000;
            font-weight: 800;
            /* Optional: slightly increase these to match the feel */
            min-width: 80px;
        }

        /* Radio button size */
        .grid-radio {
            width: 18px;
            height: 18px;
            accent-color: #007bff;
        }




        /* main grid css style */
        /* Container Fix: Forces 100% width and prevents vertical drifting */
        .bet-grid-parent {
            display: flex;
            width: 100%;
            background: #fff;
            border: 2px solid #000;
            padding: 2px;
            box-sizing: border-box;
        }

        /* Sidebar: Range Labels with High Visibility */
        .sidebar-range {
            width: 220px;
            flex-shrink: 0;
        }

        .btn-nav-group {
            display: flex;
            gap: 1px;
            margin-bottom: 2px;
        }

        .btn-nav-sm {
            border: 2px solid #000;
            font-weight: 900;
            background: var(--btn-yellow);
            color: #000;
            width: 70px;
            height: 34px;
            font-size: 0.8rem;
        }

        .select-all-box {
            border: 2px solid #000;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            height: 34px;
            background: #d68181;
        }

        .series-row-compact {
            display: flex;
            height: 38px;
            /* Fixed height for row alignment */
            border: 1px solid #000;
            margin-bottom: 1px;
        }

        .series-text-lg {
            width: 110px;
            font-weight: 900;
            font-size: 1.1rem;
            /* Big font for range visibility */
            display: flex;
            align-items: center;
            padding-left: 5px;
            background: #fff;
            color: #000;
        }

        .series-amt-tab {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 0.9rem;
            border-left: 1px solid #000;
            color: #000;
        }

        /* HIGH MODE LAYOUT FIX */

        /* LOW mode (default) stays untouched */

        /* When HIGH mode is active */
        .bet-grid-parent.mode-high .sidebar-range {
            width: 300px;
            /* sidebar gets more space */
        }

        .bet-grid-parent.mode-high .series-amt-tab {
            justify-content: space-between;
            /* formula left, amount right */
            padding: 0 8px;
        }

        .bet-grid-parent.mode-high .calc-amt {
            display: inline-block;
        }

        /* LOW mode hides calculated amount */
        .calc-amt {
            display: none;
        }


        /* Grid Layout: Synchronized Row Height */
        .grid-bet-main {
            display: grid;
            grid-template-columns: 50px repeat(10, 1fr);
            flex-grow: 1;
            gap: 1px;
            padding: 0 5px;
        }

        .master-header-box {
            background: #4fa134;
            /* Theme Dark Purple */
            border: 1px solid #000;
            height: 37px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .master-input-field {
            width: 100%;
            height: 100%;
            background: transparent;
            border: none;
            color: #000000;
            text-align: center;
            font-weight: 900;
            font-size: 1rem;
            outline: none;
        }

        /* Betting Cells */
        .bet-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            /* Keeps input at the bottom of the cell */
            height: 38px;
            /* Must match .series-row-compact */
        }

        .digit-label-sm {
            font-size: 0.7rem;
            font-weight: 900;
            color: #000;
            line-height: 1;
        }

        .input-bet-field {
            width: 95%;
            height: 24px;
            border: 1px solid #000;
            text-align: center;
            font-weight: 900;
            font-size: 1rem;
            background: #fff;
        }

        /* Stats Area */
        .stats-sidebar {
            width: 140px;
            flex-shrink: 0;
        }

        .stat-row {
            display: flex;
            gap: 1px;
            height: 38px;
            /* Alignment match */
            margin-bottom: 1px;
        }

        .qty-green {
            background: var(--btn-green);
            border: 1px solid #000;
            width: 50%;
            text-align: center;
            line-height: 38px;
            font-weight: 900;
            color: #fff;
        }

        .pts-yellow {
            background: #fff8e1;
            border: 1px solid #000;
            width: 50%;
            text-align: center;
            line-height: 38px;
            font-weight: 900;
            color: #000;
        }

        .stat-box-width {
            width: 64px;
            /* same as sidebar QTY / POINTS box */
            min-width: 64px;
            text-align: center;
            font-weight: bold;
        }





        /*footer Disclaimer News Ticker */

        .disclaimer-ticker {
            width: 100%;
            overflow: hidden;
            background: #000;
            border-top: 2px solid #ffd700;
            border-bottom: 2px solid #ffd700;
            padding: 6px 0;
        }

        .disclaimer-track {
            display: inline-block;
            white-space: nowrap;
            animation: scroll-left 25s linear infinite;
        }

        .disclaimer-track span {
            color: #ffd700;
            font-size: 13px;
            font-weight: 600;
            padding-left: 100%;
        }

        .disclaimer-ticker:hover .disclaimer-track {
            animation-play-state: paused;
        }

        /* Animation */
        @keyframes scroll-left {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* Disclaimer News Ticker */

        /* Advance draw timer  */
        /* Style for the Time Slot Container */
        #drawTimeContainer {
            max-height: 400px;
            overflow-y: auto;
            padding: 5px;
        }

        /* Style for individual Time Slot Cards */
        .slot-card {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            /* White background */
            color: #000000;
            /* Force BLACK text */
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 8px 12px;
            min-width: 100px;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            font-weight: 700;
            /* Make text bold */
            font-size: 0.9rem;
        }

        .slot-card:hover {
            border-color: #ffb703;
            background-color: #fffdf5;
        }

        /* Style when checkbox is CHECKED */
        .slot-card:has(input:checked) {
            background-color: #212529;
            /* Dark background */
            color: #ffb703 !important;
            /* Yellow text */
            border-color: #ffb703;
        }

        /* Hide the actual checkbox input visually (optional, makes it look like a button) */
        .advance-draw-cb {
            accent-color: #ffb703;
            margin-right: 8px;
        }
    </style>

    <div class="container-fluid p-0 m-0">
        <table class="dash-table">
            <tr>
                <th>Time To Draw</th>
                <th>Dr. Time</th>
                <th>Dr. Date</th>
                <th>Balance Credit</th>
                <th>Last Tr. No.</th>
                <th>Last Tr. PT.</th>
                <th style="background:#000;"></th>
            </tr>
            <tr>
                <td id="timeToDraw">--:--</td>
                <td id="drawTime">--:--</td>
                <td>{{ date('d/m/Y') }}</td>
                <td>{{ number_format($user->balance, 2) }}</td>
                <td>{{ $lastTransaction ? $lastTransaction->transaction_number : 'NA' }}</td>
                <td>
                    @if ($lastTransaction)
                        {{ number_format($lastTransaction->amount, 2) }}
                    @else
                        NA
                    @endif
                </td>
                <td><button class="btn-adv-draw" data-bs-toggle="modal" data-bs-target="#advanceDrawModal"
                        id="btnOpenAdvance">Advance Draw</button></td>
            </tr>
        </table>
    </div>

    <div class="container-fluid px-2 mt-2">
        {{-- result  --}}
        <div class="container-fluid p-0 mb-3">
            <div class="d-flex align-items-center gap-1" style="overflow-x: auto;">
                @php
                    $rowColors = [
                        '#e6ee9c',
                        '#ffab91',
                        '#9ccc65',
                        '#ffcc80',
                        '#c5e1a5',
                        '#fff59d',
                        '#f48fb1',
                        '#ffca28',
                        '#66bb6a',
                        '#ffe082',
                    ];
                @endphp

                @for ($i = 0; $i < 10; $i++)
                    <div class="text-center border border-dark flex-grow-1"
                        style="background: {{ $rowColors[$i] }}; min-width: 85px; height: 38px; border-radius: 2px;">
                        <div id="top-res-val-{{ $i }}"
                            style="font-size: 1.3rem; font-weight: 900; color: #000000; line-height: 38px;">
                            --
                        </div>
                    </div>
                @endfor
            </div>
        </div>
        <!-- ===== SERIES SELECT STRIP (TOP TABS) ===== -->
        <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
            <span class="fw-bold px-3 rounded text-dark"
                style="background: var(--btn-yellow); border:1px solid #000;">Series</span>

            {{-- <div class="d-flex gap-2 bg-dark rounded border border-warning">
                <label class="text-white px-2" style="cursor:pointer;">
                    <input type="radio" name="series_filter" value="all" class="filter-radio"> All
                </label>
                <label class="text-white px-2" style="cursor:pointer;">
                    <input type="radio" name="series_filter" value="odd" class="filter-radio"> Odd
                </label>
                <label class="text-white px-2" style="cursor:pointer;">
                    <input type="radio" name="series_filter" value="even" class="filter-radio"> Even
                </label>
                <label class="text-white px-2" style="cursor:pointer;">
                    <input type="radio" name="series_filter" value="none" class="filter-radio"> None
                </label>
            </div> --}}

            @foreach ($series_master as $s)
                <label class="series-tab d-flex align-items-center gap-2 px-3 rounded" data-series="{{ $s->start }}"
                    style="background:#ffb703; border:1px solid #b87300; cursor:pointer;">
                    <input type="checkbox" class="form-check-input series-select" value="{{ $s->start }}"
                        {{ $loop->first ? 'checked' : '' }}>
                    {{ str_pad($s->start, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($s->end, 4, '0', STR_PAD_LEFT) }}
                </label>
            @endforeach
        </div>

        {{-- grid second layer of feilds --}}
        <div class="container-fluid px-2 mt-2">
            <div
                class="d-flex align-items-center justify-content-between p-1 bg-white border border-dark rounded shadow-sm">

                <div class="d-flex gap-2">
                    <button type="button" class="btn-control btn-green">High</button>
                    <button type="button" class="btn-control btn-green">Low</button>
                </div>

                <div class="d-flex gap-3">
                    <label class="filter-card">
                        <input type="radio" name="grid_filter" value="all" class="grid-radio">
                        <span class="filter-text">All</span>
                    </label>
                    <label class="filter-card">
                        <input type="radio" name="grid_filter" value="even" class="grid-radio">
                        <span class="filter-text">Even</span>
                    </label>
                    <label class="filter-card">
                        <input type="radio" name="grid_filter" value="odd" class="grid-radio">
                        <span class="filter-text">Odd</span>
                    </label>
                </div>

                <div class="d-flex align-items-center gap-1">
                    {{-- <input type="number" class="lp-input" id="lp_val" min="1" max="99" placeholder=""> --}}
                    <input type="number" class="lp-input" id="lp_val" min="1" max="99"
                        oninput="this.value = !!this.value && Math.abs(this.value) >= 1 ? Math.abs(this.value) : null"
                        placeholder="1-99">

                    <button type="button" class="btn-control btn-orange" id="btnLP">LP</button>

                    <label class="btn-control btn-orange d-flex align-items-center gap-1 m-0" style="cursor:pointer;">
                        <input type="checkbox" id="fp_checkbox" style="width:16px;height:16px;"> FP
                    </label>

                    <button type="button" class="btn-control btn-orange" id="btnShowResults">Show Results</button>
                </div>

            </div>
        </div>

        {{-- main grid div section  --}}

        <div class="container-fluid px-2 mt-2">
            <div class="bet-grid-parent">

                <div class="sidebar-range">
                    <div class="btn-nav-group">
                        <button class="btn-nav-sm" id="btnPageUp">PAGE <i class="fa-solid fa-arrow-up"></i></button>
                        <button class="btn-nav-sm" id="btnPageDown">PAGE <i class="fa-solid fa-arrow-down"></i></button>
                        <div class="select-all-box">
                            <input type="checkbox" id="checkSelectAllRows" class="me-1" style="transform: scale(1.2);">
                            ALL
                        </div>
                    </div>

                    <div id="seriesSidebar">
                        @php $colors = ['#e6ee9c','#ffab91','#9ccc65','#ffcc80','#c5e1a5','#fff59d','#f48fb1','#ffca28','#66bb6a','#ffe082']; @endphp
                        @for ($i = 0; $i < 10; $i++)
                            <div class="series-row-compact">
                                <div class="series-text-lg" id="label-row-{{ $i }}">1000-1009</div>
                                <div class="series-amt-tab" style="background: {{ $colors[$i] }}">
                                    <input type="checkbox" class="row-selector me-1" style="transform: scale(1.1);">
                                    <span class="formula-text">(Rs. <span class="display-amt">2</span>)</span>
                                    <span class="calc-amt ms-2 fw-bold"></span>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="px-2 border-end border-start" style="width: 85px; flex-shrink: 0; background: #f8f9fa;">
                    <div class="fw-bold text-center border-bottom mb-2 bg-dark text-white py-1"
                        style="font-size: 0.75rem;">AMT</div>
                    @foreach ([2, 4, 10, 20, 40] as $a)
                        <label class="d-flex align-items-center fw-bold mb-2"
                            style="font-size:0.9rem; cursor:pointer; color: #000;">
                            <input type="radio" name="main_amt" value="{{ $a }}" class="me-1"
                                style="transform: scale(1.2);" {{ $a == 2 ? 'checked' : '' }}>
                            {{ number_format($a, 2) }}
                        </label>
                    @endforeach
                </div>

                <div class="grid-bet-main">
                    <div class="fw-bold text-center align-self-end pb-1" style="font-size: 0.9rem; color: #000;">ALL</div>
                    @for ($d = 0; $d < 10; $d++)
                        <div class="master-header-box">
                            <input type="text" class="master-input-field master-col" data-col="{{ $d }}"
                                maxlength="3" placeholder="{{ $d }}">
                        </div>
                    @endfor

                    @for ($r = 0; $r < 10; $r++)
                        <div class="master-header-box">
                            <input type="text" class="master-input-field master-row" data-row="{{ $r }}"
                                maxlength="3" placeholder="{{ $r }}">
                        </div>

                        @for ($c = 0; $c < 10; $c++)
                            <div class="bet-cell">
                                <span class="digit-label-sm"
                                    id="bet-label-{{ $r }}-{{ $c }}">1000</span>
                                <input type="text" class="input-bet-field" data-row="{{ $r }}"
                                    data-col="{{ $c }}" maxlength="3">
                            </div>
                        @endfor
                    @endfor
                </div>

                <div class="stats-sidebar ps-2 border-start" style="border-width: 2px !important;">
                    <div class="fw-bold mb-1 px-1" style="font-size: 10; color: #000;">
                        <div id="header-qty-pts" class="d-flex justify-content-between">
                            <button class="btn-nav-sm" id="btnQty">QTY</button>
                            <button class="btn-nav-sm" id="btnPoints">POINTS</button>
                        </div>

                        <div id="header-result" class="d-none">
                            <button class="btn-nav-sm"
                                style="width: 100%; background: #000; color: #ffd700; border: 1px solid #b87300; font-size: 0.7rem;">
                                ({{ $lastDrawTime->format('h:i A') }})
                            </button>
                        </div>
                    </div>

                    @php
                        $rowColors = [
                            '#e6ee9c',
                            '#ffab91',
                            '#9ccc65',
                            '#ffcc80',
                            '#c5e1a5',
                            '#fff59d',
                            '#f48fb1',
                            '#ffca28',
                            '#66bb6a',
                            '#ffe082',
                        ];
                    @endphp

                    @for ($s = 0; $s < 10; $s++)
                        <div class="stat-row position-relative">
                            <div class="qty-green stat-normal" id="qty-row-{{ $s }}">0</div>
                            <div class="pts-yellow stat-normal" id="points-row-{{ $s }}">0</div>

                            <div class="result-box stat-result" id="result-row-{{ $s }}"
                                style="display:none; width:100%; background:{{ $rowColors[$s] }}; border:1px solid #000;
        text-align:center; font-weight:900; line-height:38px; color:#d32f2f; font-size: 1.1rem;">
                                --
                            </div>
                        </div>
                    @endfor
                </div>

            </div>
        </div>

        <!-- ===== FOOTER ACTION BAR ===== -->
        <div class="container-fluid px-2">
            <div class="d-flex align-items-center justify-content-between border rounded p-2 bg-light">

                <!-- LEFT -->
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-success btn-sm" id="btnPrint">
                        Print (F6)
                    </button>

                    <button class="btn btn-primary btn-sm fw-bold" id="btnPlaceBet"
                        style="background-color: #0d6efd; border-color: #0d6efd;">
                        <i class="fa-solid fa-check"></i> Place Bet
                    </button>

                    <button class="btn btn-danger btn-sm" id="btnClear">
                        Clear
                    </button>

                    <input type="text" class="form-control form-control-sm" style="width: 220px;"
                        placeholder="F7 Scan Barcode Here" id="barcodeInput">
                </div>

                <!-- CENTER -->
                <div class="fw-bold text-uppercase" id="statusMessage" style="font-size: 0.9rem;">
                </div>

                <!-- RIGHT -->
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="totalQty" class="form-control form-control-sm stat-box-width"
                        value="0" readonly style="background:#fff9c4;">

                    <input type="text" id="totalPoints" class="form-control form-control-sm stat-box-width"
                        value="0" readonly style="background:#fff9c4;">
                </div>
            </div>
        </div>

        <!-- ===== DISCLAIMER TICKER ===== -->
        <div class="disclaimer-ticker">
            <div class="disclaimer-track">
                <span>
                    This lottery system is just for fun. Tickets given here are completely free.
                    There is no financial transaction, gambling or exchange of money.
                    The prizes or points earned through these tickets are for entertainment purposes only,
                    and cannot be used in cash.
                </span>
            </div>
        </div>

    </div>


    {{-- advance draw Modal --}}
    <div class="modal fade" id="advanceDrawModal" tabindex="-1" aria-labelledby="advanceDrawLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-warning">
                    <h5 class="modal-title fw-bold" id="advanceDrawLabel">
                        <i class="fa-solid fa-clock"></i> Select Advance Draws
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="d-flex justify-content-between mb-3">
                        <button class="btn btn-sm btn-outline-dark fw-bold" onclick="selectAllDraws(true)">Select
                            All</button>
                        <button class="btn btn-sm btn-outline-danger fw-bold" onclick="selectAllDraws(false)">Clear
                            All</button>
                    </div>

                    <div id="drawTimeContainer" class="d-flex flex-wrap gap-2 justify-content-start">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success fw-bold" data-bs-dismiss="modal">
                        Confirm Selection (<span id="selectedDrawCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>



    <script>
        // Pass PHP Config to JavaScript
        const DRAW_CONF = {
            start: "{{ config('app.draw_start') }}", // e.g. "08:00"
            end: "{{ config('app.draw_end') }}" // e.g. "22:30"
        };

        function getConfigTime(timeStr) {
            return timeStr.split(':').map(Number);
        }
    </script>
    <script>
        function isWithinDrawTime(now) {
            const start = new Date(now);
            const [sH, sM] = getConfigTime(DRAW_CONF.start);
            start.setHours(sH, sM, 0, 0);

            const end = new Date(now);
            const [eH, eM] = getConfigTime(DRAW_CONF.end);
            end.setHours(eH, eM, 0, 0);

            return now >= start && now <= end;
        }

        function getNextQuarterHour(now) {
            const next = new Date(now);

            let m = now.getMinutes();
            if (m < 15) next.setMinutes(15, 0, 0);
            else if (m < 30) next.setMinutes(30, 0, 0);
            else if (m < 45) next.setMinutes(45, 0, 0);
            else {
                next.setHours(now.getHours() + 1, 0, 0, 0);
            }

            return next;
        }

        function updateTimer() {
            const now = new Date();
            const msgEl = document.getElementById("statusMessage");

            // 1. Check if Time is Over
            if (!isWithinDrawTime(now)) {
                // --- TIME OVER LOGIC ---
                document.getElementById("timeToDraw").innerText = "--";
                document.getElementById("drawTime").innerText = "--";

                // Update the Message Div
                if (msgEl) {
                    msgEl.innerText = "DRAW TIME END";
                    msgEl.className = "fw-bold text-danger text-uppercase"; // Red Color
                }
                return;
            }

            // 2. If Time is Valid (Betting Open)
            if (msgEl) {
                msgEl.innerText = "MARKET OPEN";
                msgEl.className = "fw-bold text-success text-uppercase"; // Green Color
            }

            // --- EXISTING TIMER LOGIC ---
            const drawTime = getNextQuarterHour(now);
            const diff = drawTime - now;
            let s = Math.floor(diff / 1000);

            const sec = s % 60;
            s = (s - sec) / 60;
            const min = s % 60;
            const hr = (s - min) / 60;

            document.getElementById("timeToDraw").innerText =
                `${String(hr).padStart(2,'0')}:${String(min).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;

            let h = drawTime.getHours(),
                ampm = h >= 12 ? "PM" : "AM";
            h = h % 12;
            if (h === 0) h = 12;
            let mm = drawTime.getMinutes().toString().padStart(2, "0");

            document.getElementById("drawTime").innerText = `${h}:${mm} ${ampm}`;
        }

        setInterval(updateTimer, 1000);
        updateTimer();
    </script>


    <script>
        // grid second section script code
        document.addEventListener('DOMContentLoaded', function() {
            const gridRadios = document.querySelectorAll('.grid-radio');

            document.querySelector('input[name="grid_filter"][value="all"]').checked = true;


            // Filter Logic Placeholder
            gridRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    console.log("Grid Logic Active: " + this.value);
                    // In the future, this will trigger Even/Odd logic for the number grid
                });
            });

            // High/Low/LP button handlers
            document.querySelectorAll('.btn-green, .btn-orange').forEach(btn => {
                btn.addEventListener('click', function() {
                    const label = this.innerText;
                    if (label) console.log("Button Clicked: " + label);
                });
            });
        });

        // main grid script code and logic
        document.addEventListener('DOMContentLoaded', function() {


            document.querySelectorAll('.input-bet-field, .master-input-field').forEach(input => {
                input.addEventListener('input', function() {
                    // 1. Remove non-numeric characters immediately
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // 2. Prevent leading zeros (e.g., "05" becomes "5")
                    if (this.value.length > 1 && this.value.startsWith('0')) {
                        this.value = this.value.replace(/^0+/, '');
                    }

                    // 3. Force re-calculation of total points
                    if (typeof updateAllStats === "function") {
                        updateAllStats();
                    }
                });
            });

            /* =====================================================
               STATE
            ===================================================== */
            let activeSeriesRow = 0;
            let currentMode = 'low';
            let currentBaseSeries = null;

            /* =====================================================
               CONFIG
            ===================================================== */
            // REMOVED: const BASE_AMT = 2;  <-- logic changed to read radio button
            const RATE = 90;
            const HIGH_ROW_MULTIPLIERS = [1, 1, 2, 3, 5, 5, 10, 20, 25, 25];

            const gridParent = document.querySelector('.bet-grid-parent');
            const amtColumn = document.querySelector('.px-2.border-end.border-start');
            const allRowsCB = document.getElementById('checkSelectAllRows');

            /* =====================================================
               DEFAULT GRID FILTER
            ===================================================== */
            const defaultGridFilter = document.querySelector('input[name="grid_filter"][value="all"]');
            if (defaultGridFilter) defaultGridFilter.checked = true;

            /* =====================================================
               SERIES FILTER (LEFT TOP)
            ===================================================== */
            const filterRadios = document.querySelectorAll('.filter-radio');
            const seriesCheckboxes = document.querySelectorAll('.series-select');

            // set default base series
            const firstCheckedSeries = document.querySelector('.series-select:checked');
            if (firstCheckedSeries) {
                currentBaseSeries = parseInt(firstCheckedSeries.value, 10);
            }

            function applySeriesFilter(mode) {
                seriesCheckboxes.forEach(cb => {
                    const seriesVal = parseInt(cb.closest('.series-tab').dataset.series, 10);
                    if (mode === 'all') cb.checked = true;
                    else if (mode === 'none') cb.checked = false;
                    else if (mode === 'odd') cb.checked = (seriesVal % 2 !== 0);
                    else if (mode === 'even') cb.checked = (seriesVal % 2 === 0);
                });
            }

            filterRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    applySeriesFilter(this.value);
                });
            });

            seriesCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    filterRadios.forEach(r => r.checked = false);

                    if (cb.checked) {
                        // User checked a series → switch view to it
                        currentBaseSeries = parseInt(cb.value, 10);
                        activeSeriesRow = 0;
                        updateView();
                    } else {
                        // User unchecked a series → auto-switch to first remaining checked series
                        const allChecked = [...document.querySelectorAll('.series-select:checked')];
                        const highestRemaining = allChecked.length > 0 ?
                            allChecked.reduce((max, cb) => parseInt(cb.value) > parseInt(max
                                .value) ? cb : max) :
                            null;

                        if (highestRemaining) {
                            currentBaseSeries = parseInt(highestRemaining.value, 10);
                            activeSeriesRow = 0;
                            updateView();
                        } else {
                            // No series checked at all → auto-check and switch back to first series (1000)
                            const firstSeries = document.querySelector('.series-select');
                            if (firstSeries) {
                                firstSeries.checked = true;
                                currentBaseSeries = parseInt(firstSeries.value, 10);
                                activeSeriesRow = 0;
                                updateView();
                            }
                        }
                    }

                    document.dispatchEvent(new Event('recalculateGridStats'));
                });
            });

            //    UPDATE VIEW (CORE) - FIXED LOGIC HERE
            const lastDrawResults = @json($lastResults);

            function updateView() {
                const baseSeries = currentBaseSeries ?? 1000;

                // 1. Get current selected Amount from Radio Buttons
                let currentSelectedAmt = 2;
                const selectedRadio = document.querySelector('input[name="main_amt"]:checked');
                if (selectedRadio) {
                    currentSelectedAmt = parseInt(selectedRadio.value);
                }

                /* ---------- Sidebar rows, Sidebar Results & Top Result Strip ---------- */
                for (let i = 0; i < 10; i++) {
                    const rowStart = baseSeries + (i * 100);
                    const rowEnd = rowStart + 99;

                    // Determine the winner for this specific 100-number block
                    const winner = lastDrawResults[rowStart];
                    const displayWinner = (winner !== undefined && winner !== null) ? winner : '--';

                    // UPDATE SIDEBAR ROW LABELS (e.g., 1000-1099)
                    const label = document.getElementById(`label-row-${i}`);
                    if (label) label.innerText = `${rowStart}-${rowEnd}`;

                    // UPDATE SIDEBAR RESULT BOXES
                    const resultEl = document.getElementById(`result-row-${i}`);
                    if (resultEl) {
                        resultEl.innerText = displayWinner;
                        // The background color for these is handled by the Blade loop colors
                    }

                    // UPDATE THE 10-COLUMN TOP STRIP (Dynamic Result Row)
                    // const topLabel = document.getElementById(`top-res-label-${i}`);
                    const topVal = document.getElementById(`top-res-val-${i}`);

                    // if (topLabel) topLabel.innerText = `${rowStart}-${rowEnd}`;
                    if (topVal) topVal.innerText = displayWinner;

                    // Apply Logic for Sidebar Amount calculation and Selection Styling
                    const rowEl = label ? label.closest('.series-row-compact') : null;
                    if (rowEl) {
                        const textEl = rowEl.querySelector('.series-text-lg');
                        const amtEl = rowEl.querySelector('.series-amt-tab');
                        const rowCB = rowEl.querySelector('.row-selector');
                        const displayAmt = amtEl.querySelector('.display-amt');
                        const calcAmt = amtEl.querySelector('.calc-amt');

                        // High/Low Mode Logic
                        if (currentMode === 'high') {
                            const mult = HIGH_ROW_MULTIPLIERS[i];
                            displayAmt.innerText = `${currentSelectedAmt} * ${mult}`;
                            calcAmt.innerText = currentSelectedAmt * mult * RATE;
                        } else {
                            displayAmt.innerText = currentSelectedAmt;
                            calcAmt.innerText = '';
                        }

                        // Selection Highlight Logic
                        const rowColor = amtEl.style.background;
                        if (i === activeSeriesRow || (rowCB && rowCB.checked)) {
                            textEl.style.background = rowColor;
                            amtEl.style.outline = '3px solid #000';
                        } else {
                            textEl.style.background = '#fff';
                            amtEl.style.outline = 'none';
                        }
                    }
                }

                /* ---------- Center 10x10 Betting Grid ---------- */
                // This part updates the small numbers (e.g., 1001, 1002) inside the grid cells
                const gridStart = baseSeries + (activeSeriesRow * 100);
                for (let r = 0; r < 10; r++) {
                    for (let c = 0; c < 10; c++) {
                        const betLabel = document.getElementById(`bet-label-${r}-${c}`);
                        if (betLabel) {
                            betLabel.innerText = gridStart + (r * 10) + c;
                        }
                    }
                }
            }

            /* =====================================================
               PAGE NAVIGATION
            ===================================================== */
            document.getElementById('btnPageDown').onclick = () => {
                if (activeSeriesRow < 9) {
                    activeSeriesRow++;
                    updateView();
                }
            };

            document.getElementById('btnPageUp').onclick = () => {
                if (activeSeriesRow > 0) {
                    activeSeriesRow--;
                    updateView();
                }
            };

            /* =====================================================
               ROW CHECKBOXES
            ===================================================== */
            allRowsCB.onchange = function() {
                document.querySelectorAll('.row-selector')
                    .forEach(cb => cb.checked = this.checked);
                updateView();
                document.dispatchEvent(new Event('recalculateGridStats'));
            };

            document.querySelectorAll('.row-selector')
                .forEach(cb => cb.onchange = updateView);

            /* =====================================================
               AMT RADIO (LOW MODE ONLY) - FIXED
            ===================================================== */
            document.querySelectorAll('input[name="main_amt"]').forEach(rad => {
                rad.onchange = function() {
                    // Instead of manually setting text, we call updateView()
                    // This ensures High/Low logic is applied correctly with the new value
                    updateView();
                    // Also trigger stats recalc so the Bottom Total Points updates immediately
                    document.dispatchEvent(new Event('recalculateGridStats'));
                };
            });

            /* =====================================================
               MASTER INPUT (ROW / COL + ALL / EVEN / ODD)
            ===================================================== */
            function handleMasterInput(isRow, index, value) {
                const filterMode = document.querySelector('input[name="grid_filter"]:checked')?.value || 'all';
                const selector = isRow ?
                    `.input-bet-field[data-row="${index}"]` :
                    `.input-bet-field[data-col="${index}"]`;

                document.querySelectorAll(selector).forEach(input => {
                    const r = input.dataset.row;
                    const c = input.dataset.col;
                    const labelEl = document.getElementById(`bet-label-${r}-${c}`);
                    if (!labelEl) return;
                    const lastDigit = parseInt(labelEl.innerText.slice(-1), 10);

                    if (
                        filterMode === 'all' ||
                        (filterMode === 'even' && lastDigit % 2 === 0) ||
                        (filterMode === 'odd' && lastDigit % 2 !== 0)
                    ) {
                        input.value = value;
                    }
                });
                document.dispatchEvent(new Event('recalculateGridStats'));
            }

            function resetGridAndStats() {
                document.querySelectorAll('.input-bet-field').forEach(input => input.value = '');
                document.querySelectorAll('.master-row, .master-col').forEach(input => input.value = '');
                document.querySelectorAll('.row-selector').forEach(cb => cb.checked = false);
                for (let i = 0; i < 10; i++) {
                    document.getElementById(`qty-row-${i}`).innerText = 0;
                    document.getElementById(`points-row-${i}`).innerText = 0;
                }
                document.getElementById('totalQty').value = 0;
                document.getElementById('totalPoints').value = 0;
                document.dispatchEvent(new Event('recalculateGridStats'));
            }

            document.querySelectorAll('.master-row').forEach(inp => {
                inp.oninput = function() {
                    handleMasterInput(true, this.dataset.row, this.value);
                };
            });

            document.querySelectorAll('.master-col').forEach(inp => {
                inp.oninput = function() {
                    handleMasterInput(false, this.dataset.col, this.value);
                };
            });

            /* =====================================================
               HIGH / LOW BUTTONS
            ===================================================== */
            const [btnHigh, btnLow] = document.querySelectorAll('.btn-control.btn-green');

            btnHigh.onclick = () => {
                resetGridAndStats();
                currentMode = 'high';
                gridParent.classList.add('mode-high');
                amtColumn.style.display = 'none';
                allRowsCB.checked = false;
                updateView();
            };

            btnLow.onclick = () => {
                currentMode = 'low';
                gridParent.classList.remove('mode-high');
                amtColumn.style.display = '';
                allRowsCB.checked = false;
                resetGridAndStats();
                updateView();
            };

            /* =====================================================
               LUCKY PICK (LP)
            ===================================================== */
            const lpInput = document.getElementById('lp_val');
            const btnLP = document.getElementById('btnLP');

            // Handle range validation (1-99)
            lpInput.addEventListener('input', function() {
                // Remove non-numbers
                this.value = this.value.replace(/[^0-9]/g, '');

                // Remove leading zeros
                if (this.value.length > 1 && this.value.startsWith('0')) {
                    this.value = this.value.replace(/^0+/, '');
                }

                let value = parseInt(this.value);

                // Enforce 1-99
                if (value > 99) {
                    this.value = 99;
                } else if (value < 1 && this.value !== "") {
                    this.value = 1;
                }
            });

            // Prevent typing decimal points or signs
            lpInput.addEventListener('keydown', function(e) {
                if (['e', 'E', '+', '-', '.'].includes(e.key)) {
                    e.preventDefault();
                }
            });

            function shuffleArray(array) {
                for (let i = array.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [array[i], array[j]] = [array[j], array[i]];
                }
                return array;
            }

            btnLP.addEventListener('click', function() {
                let count = parseInt(lpInput.value);
                if (isNaN(count) || count <= 0) return;
                if (count > 100) count = 100;

                const allInputs = document.querySelectorAll('.input-bet-field');
                let emptyIndices = [];
                let filledIndices = [];

                allInputs.forEach((input, index) => {
                    if (input.value === "") emptyIndices.push(index);
                    else filledIndices.push(index);
                });

                shuffleArray(emptyIndices);
                shuffleArray(filledIndices);
                const candidateIndices = emptyIndices.concat(filledIndices);
                const selectedIndices = candidateIndices.slice(0, count);

                selectedIndices.forEach(index => {
                    const input = allInputs[index];
                    input.value = 1;
                    input.style.backgroundColor = '#fff59d';
                    setTimeout(() => {
                        input.style.backgroundColor = '#fff';
                    }, 500);
                });
                document.dispatchEvent(new Event('recalculateGridStats'));
            });

            /* =====================================================
               FP (FAVOURITE PICK) LOGIC
            ===================================================== */
            const fpCheckbox = document.getElementById('fp_checkbox');

            function clearFPHighlights() {
                document.querySelectorAll('.input-bet-field').forEach(el => {
                    if (el.style.backgroundColor === 'rgb(255, 245, 157)') el.style.backgroundColor =
                        '#fff';
                });
            }


            document.querySelectorAll('.input-bet-field').forEach(input => {
                input.addEventListener('click', function() {
                    if (!fpCheckbox.checked) return;
                    clearFPHighlights();

                    let r = parseInt(this.dataset.row);
                    let c = parseInt(this.dataset.col);
                    let r2 = (r + 5) % 10;
                    let c2 = (c + 5) % 10;

                    const targets = [{
                            row: r,
                            col: c
                        }, {
                            row: r,
                            col: c2
                        },
                        {
                            row: r2,
                            col: c
                        }, {
                            row: r2,
                            col: c2
                        },
                        {
                            row: c,
                            col: r
                        }, {
                            row: c,
                            col: r2
                        },
                        {
                            row: c2,
                            col: r
                        }, {
                            row: c2,
                            col: r2
                        }
                    ];

                    // Collect all 8 target inputs
                    const linkedInputs = [];
                    targets.forEach(target => {
                        const el = document.querySelector(
                            `.input-bet-field[data-row="${target.row}"][data-col="${target.col}"]`
                        );
                        if (el) {
                            el.style.backgroundColor = '#fff59d';
                            el.value = 1;
                            el.dataset.fpGroup = 'active'; // mark as FP group
                            linkedInputs.push(el);
                        }
                    });

                    // Sync all linked inputs together
                    linkedInputs.forEach(el => {
                        // Remove old listener to avoid stacking
                        el.removeEventListener('input', el._fpSyncHandler);

                        el._fpSyncHandler = function() {
                            const newVal = this.value;
                            linkedInputs.forEach(other => {
                                if (other !== this) {
                                    other.value = newVal;
                                }
                            });
                            document.dispatchEvent(new Event('recalculateGridStats'));
                        };

                        el.addEventListener('input', el._fpSyncHandler);
                    });

                    document.dispatchEvent(new Event('recalculateGridStats'));
                });
            });

            fpCheckbox.addEventListener('change', function() {
                if (!this.checked) {
                    // Remove all FP sync listeners and clear highlights
                    document.querySelectorAll('.input-bet-field[data-fp-group="active"]').forEach(el => {
                        if (el._fpSyncHandler) {
                            el.removeEventListener('input', el._fpSyncHandler);
                            delete el._fpSyncHandler;
                        }
                        el.removeAttribute('data-fp-group');
                        el.style.backgroundColor = '#fff';
                    });
                    clearFPHighlights();
                }
            });

            // SHOW / HIDE RESULTS TOGGLE

            const btnShowResults = document.getElementById('btnShowResults');
            const headerQtyPts = document.getElementById('header-qty-pts');
            const headerResult = document.getElementById('header-result');
            let isResultMode = false;

            btnShowResults.addEventListener('click', function() {
                isResultMode = !isResultMode;

                // Toggle Button Text
                this.innerText = isResultMode ? "Hide Results" : "Show Results";
                this.classList.toggle('btn-danger', isResultMode); // Optional: make button red when active

                if (isResultMode) {
                    headerQtyPts.classList.add('d-none');
                    headerResult.classList.remove('d-none');
                } else {
                    headerQtyPts.classList.remove('d-none');
                    headerResult.classList.add('d-none');
                }

                document.querySelectorAll('.stat-row').forEach(row => {
                    const normalStats = row.querySelectorAll('.stat-normal');
                    const resultStat = row.querySelector('.stat-result');

                    if (isResultMode) {
                        normalStats.forEach(el => el.style.setProperty('display', 'none',
                            'important'));
                        if (resultStat) resultStat.style.setProperty('display', 'block',
                            'important');
                    } else {
                        normalStats.forEach(el => el.style.setProperty('display', 'flex',
                            'important'));
                        if (resultStat) resultStat.style.setProperty('display', 'none',
                            'important');
                    }
                });
            });

            /* =====================================================
               GLOBAL GRID TOTAL (100 INPUTS)
            ===================================================== */
            function getGridQty() {
                let qty = 0;
                document.querySelectorAll('.input-bet-field').forEach(input => {
                    qty += parseInt(input.value || 0, 10);
                });
                return qty;
            }

            function getRowRate(rowIndex) {
                const row = document.querySelectorAll('.series-row-compact')[rowIndex];
                if (!row) return 0;
                const displayAmt = row.querySelector('.display-amt')?.innerText || '0';

                // HIGH MODE → "2 * 3"
                if (displayAmt.includes('*')) {
                    const [a, b] = displayAmt.split('*').map(v => parseInt(v.trim(), 10));
                    return a * b;
                }
                // LOW MODE → "2"
                return parseInt(displayAmt, 10) || 0;
            }

            //    UPDATE SIDEBAR ROW STATS & TOTALS

            // function updateAllStats() {
            //     const gridQty = getGridQty();
            //     let totalQty = 0;
            //     let totalPoints = 0;

            //     for (let i = 0; i < 10; i++) {
            //         const rowCB = document.querySelectorAll('.row-selector')[i];

            //         if (rowCB && rowCB.checked) {
            //             const rate = getRowRate(i);
            //             const points = gridQty * rate;
            //             document.getElementById(`qty-row-${i}`).innerText = gridQty;
            //             document.getElementById(`points-row-${i}`).innerText = points;
            //         } else {
            //             document.getElementById(`qty-row-${i}`).innerText = 0;
            //             document.getElementById(`points-row-${i}`).innerText = 0;
            //         }

            //         totalQty += parseInt(document.getElementById(`qty-row-${i}`).innerText || 0, 10);
            //         totalPoints += parseInt(document.getElementById(`points-row-${i}`).innerText || 0, 10);
            //     }

            //     document.getElementById('totalQty').value = totalQty;
            //     document.getElementById('totalPoints').value = totalPoints;
            // }

            function updateAllStats() {
                const gridQty = getGridQty();
                let totalQty = 0;
                let totalPoints = 0;

                // Count how many series tabs are checked
                const checkedSeriesCount = document.querySelectorAll('.series-select:checked').length;
                const seriesMultiplier = checkedSeriesCount > 0 ? checkedSeriesCount : 1;

                for (let i = 0; i < 10; i++) {
                    const rowCB = document.querySelectorAll('.row-selector')[i];

                    if (rowCB && rowCB.checked) {
                        const rate = getRowRate(i);
                        // Multiply points by number of selected series
                        const points = gridQty * rate * seriesMultiplier;
                        const qty = gridQty * seriesMultiplier;
                        document.getElementById(`qty-row-${i}`).innerText = qty;
                        document.getElementById(`points-row-${i}`).innerText = points;
                    } else {
                        document.getElementById(`qty-row-${i}`).innerText = 0;
                        document.getElementById(`points-row-${i}`).innerText = 0;
                    }

                    totalQty += parseInt(document.getElementById(`qty-row-${i}`).innerText || 0, 10);
                    totalPoints += parseInt(document.getElementById(`points-row-${i}`).innerText || 0, 10);
                }

                document.getElementById('totalQty').value = totalQty;
                document.getElementById('totalPoints').value = totalPoints;
            }

            /* =====================================================
               STATS TRIGGERS
            ===================================================== */
            document.querySelectorAll('.input-bet-field').forEach(input => {
                input.addEventListener('input', updateAllStats);
            });

            // Trigger stats update when event is dispatched (from other functions)
            document.addEventListener('recalculateGridStats', function() {
                updateAllStats();
            });

            // INITIAL LOAD
            updateView();
            updateAllStats();
        });


        // below footer
        document.getElementById('btnClear').addEventListener('click', function() {

            // Clear grid inputs
            document.querySelectorAll('.input-bet-field').forEach(inp => {
                inp.value = '';
            });

            // Clear master row & column inputs
            document.querySelectorAll('.master-row, .master-col').forEach(inp => {
                inp.value = '';
            });

            // Uncheck sidebar row selectors
            document.querySelectorAll('.row-selector').forEach(cb => {
                cb.checked = false;
            });

            // Reset select all rows
            const selectAll = document.getElementById('checkSelectAllRows');
            if (selectAll) selectAll.checked = false;

            // Reset totals
            document.getElementById('totalQty').value = 0;
            document.getElementById('totalPoints').value = 0;

            // Reset per-row stats
            document.querySelectorAll('[id^="qty-row-"]').forEach(el => el.innerText = 0);
            document.querySelectorAll('[id^="points-row-"]').forEach(el => el.innerText = 0);

            // Clear barcode field
            const barcode = document.getElementById('barcodeInput');
            if (barcode) barcode.value = '';
        });
    </script>

    {{-- GLOBAL GRID TOTAL (100 INPUTS) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {


            function getGridQty() {
                let qty = 0;

                document.querySelectorAll('.input-bet-field').forEach(input => {
                    qty += parseInt(input.value || 0, 10);
                });

                return qty;
            }

            function getRowRate(rowIndex) {
                const row = document.querySelectorAll('.series-row-compact')[rowIndex];
                if (!row) return 0;

                const displayAmt = row.querySelector('.display-amt')?.innerText || '0';

                // HIGH MODE → "2 * 3"
                if (displayAmt.includes('*')) {
                    const [a, b] = displayAmt.split('*').map(v => parseInt(v.trim(), 10));
                    return a * b;
                }

                // LOW MODE → "2"
                return parseInt(displayAmt, 10) || 0;
            }

            //    UPDATE SIDEBAR ROW STATS

            // function updateRowStats(rowIndex, gridQty) {
            //     const rowCB = document.querySelectorAll('.row-selector')[rowIndex];

            //     if (!rowCB || !rowCB.checked) {
            //         document.getElementById(`qty-row-${rowIndex}`).innerText = 0;
            //         document.getElementById(`points-row-${rowIndex}`).innerText = 0;
            //         return;
            //     }

            //     const rate = getRowRate(rowIndex);
            //     const points = gridQty * rate;

            //     document.getElementById(`qty-row-${rowIndex}`).innerText = gridQty;
            //     document.getElementById(`points-row-${rowIndex}`).innerText = points;
            // }

            function updateRowStats(rowIndex, gridQty) {
                const rowCB = document.querySelectorAll('.row-selector')[rowIndex];

                if (!rowCB || !rowCB.checked) {
                    document.getElementById(`qty-row-${rowIndex}`).innerText = 0;
                    document.getElementById(`points-row-${rowIndex}`).innerText = 0;
                    return;
                }

                // Count checked series
                const checkedSeriesCount = document.querySelectorAll('.series-select:checked').length;
                const seriesMultiplier = checkedSeriesCount > 0 ? checkedSeriesCount : 1;

                const rate = getRowRate(rowIndex);
                const points = gridQty * rate * seriesMultiplier;
                const qty = gridQty * seriesMultiplier;

                document.getElementById(`qty-row-${rowIndex}`).innerText = qty;
                document.getElementById(`points-row-${rowIndex}`).innerText = points;
            }

            //    UPDATE TOTALS

            function updateAllStats() {
                const gridQty = getGridQty();

                let totalQty = 0;
                let totalPoints = 0;

                for (let i = 0; i < 10; i++) {
                    updateRowStats(i, gridQty);

                    totalQty += parseInt(
                        document.getElementById(`qty-row-${i}`).innerText || 0,
                        10
                    );

                    totalPoints += parseInt(
                        document.getElementById(`points-row-${i}`).innerText || 0,
                        10
                    );
                }

                document.getElementById('totalQty').value = totalQty;
                document.getElementById('totalPoints').value = totalPoints;
            }

            //    TRIGGERS (ALL SCENARIOS COVERED)

            // Grid input change (type anytime)
            document.querySelectorAll('.input-bet-field').forEach(input => {
                input.addEventListener('input', updateAllStats);
            });

            // Row checkbox toggle
            document.querySelectorAll('.row-selector').forEach(cb => {
                cb.addEventListener('change', updateAllStats);
            });

            // Amount change (LOW mode)
            document.querySelectorAll('input[name="main_amt"]').forEach(rad => {
                rad.addEventListener('change', updateAllStats);
            });

            // Page navigation (labels change, inputs reused)
            document.getElementById('btnPageUp')?.addEventListener('click', updateAllStats);
            document.getElementById('btnPageDown')?.addEventListener('click', updateAllStats);

            // Initial run
            updateAllStats();

            document.addEventListener('recalculateGridStats', function() {
                updateAllStats();
            });
        });
    </script>

    {{-- F6 and F7 button --}}
    <script>
        document.addEventListener('keydown', function(e) {

            // F6 → Print
            if (e.key === 'F6') {
                e.preventDefault();
                window.print();
            }

            // F7 → Focus barcode
            if (e.key === 'F7') {
                e.preventDefault();
                document.getElementById('barcodeInput')?.focus();
            }
        });
    </script>

    {{-- ADVANCE DRAW LOGIC - FIXED & VISIBLE --}}
    <script>
        let selectedDrawTimes = [];

        // Trigger generation when button is clicked
        document.getElementById('btnOpenAdvance').addEventListener('click', function() {
            // generateDrawSlots();

            const container = document.getElementById('drawTimeContainer');
            // Only generate if empty (first time opening)
            if (container.innerHTML.trim() === '') {
                generateDrawSlots();
            }
        });

        function generateDrawSlots() {
            const container = document.getElementById('drawTimeContainer');
            container.innerHTML = ''; // Clear previous slots
            selectedDrawTimes = [];
            document.getElementById('selectedDrawCount').innerText = '0';

            const now = new Date();

            // Dynamic Start Time (e.g. 08:00)
            const startTime = new Date(now);
            const [sH, sM] = getConfigTime(DRAW_CONF.start);
            startTime.setHours(sH, sM, 0, 0);

            // Dynamic End Time (e.g. 22:30)
            const endTime = new Date(now);
            const [eH, eM] = getConfigTime(DRAW_CONF.end);
            endTime.setHours(eH, eM, 0, 0);

            let slotTime = new Date(startTime);
            let hasSlots = false;

            while (slotTime <= endTime) {
                // Check if time is in the past (Expired)
                // Buffer: 60000ms (1 min) to ensure we don't show a draw that JUST passed
                const isExpired = slotTime <= new Date(now.getTime() + 60000);

                // --- FIX: IF EXPIRED, SKIP THIS SLOT COMPLETELY ---
                if (isExpired) {
                    slotTime.setMinutes(slotTime.getMinutes() + 15);
                    continue; // Jump to next iteration of loop
                }

                // If we are here, the slot is valid (Future)
                hasSlots = true;

                // Format Time String (e.g., "10:30 PM")
                let hours = slotTime.getHours();
                let minutes = slotTime.getMinutes();
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                const strTime = hours + ':' + (minutes < 10 ? '0' + minutes : minutes) + ' ' + ampm;

                // ISO String for Value
                const isoString = toLocalISOString(slotTime);

                // Build HTML Elements
                // Using <label> makes the whole box clickable
                const wrapper = document.createElement('label');
                wrapper.className = 'slot-card'; // This class now has color: black

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input advance-draw-cb';
                checkbox.value = isoString;
                checkbox.id = 'draw_' + isoString;

                // Update count on click
                checkbox.addEventListener('change', updateSelectedDrawCount);

                const span = document.createElement('span');
                span.innerText = strTime;

                wrapper.appendChild(checkbox);
                wrapper.appendChild(span);
                container.appendChild(wrapper);

                // Increment by 15 mins
                slotTime.setMinutes(slotTime.getMinutes() + 15);
            }

            // Optional: Show message if no slots available today
            if (!hasSlots) {
                container.innerHTML =
                    '<div class="text-center w-100 text-danger fw-bold mt-4">No more draws available for today.</div>';
            }
        }

        function updateSelectedDrawCount() {
            const checked = document.querySelectorAll('.advance-draw-cb:checked');
            document.getElementById('selectedDrawCount').innerText = checked.length;
        }

        function selectAllDraws(enable) {
            document.querySelectorAll('.advance-draw-cb:not(:disabled)').forEach(cb => {
                cb.checked = enable;
            });
            updateSelectedDrawCount();
        }

        // Helper: Fix Timezone Offset for ISO String
        function toLocalISOString(date) {
            const offset = date.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(date - offset)).toISOString().slice(0, 19).replace('T', ' ');
            return localISOTime;
        }

        // Helper: Parse "08:00"
        function getConfigTime(timeStr) {
            return timeStr.split(':').map(Number);
        }
    </script>


    {{-- BET PLACEMENT LOGIC - (Frontend Calculated) --}}
    <script>
        document.getElementById('btnPlaceBet').addEventListener('click', function() {
            placeBet();
        });

        function placeBet() {
            // --- EXISTING TIME CHECK (Keep this) ---
            const now = new Date();

            if (!isWithinDrawTime(now)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Draw Time Over',
                    text: `❌ Betting is closed. Allowed time: ${DRAW_CONF.start} to ${DRAW_CONF.end}.`,
                });
                return;
            }

            // --- NEW: COLLECT ADVANCE DRAW TIMES ---
            const advanceDrawTimes = [];
            document.querySelectorAll('.advance-draw-cb:checked').forEach(cb => {
                advanceDrawTimes.push(cb.value);
            });

            // If advance draws selected, use them. If not, array is empty.
            // ----------------------------------------

            // 1. Get Totals
            const grandTotalPoints = parseFloat(document.getElementById('totalPoints').value || 0);

            // 2. Get Ticket Price
            const selectedRadio = document.querySelector('input[name="main_amt"]:checked');
            if (!selectedRadio) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select Amount'
                });
                return;
            }
            const ticketPrice = parseFloat(selectedRadio.value);

            // 3. Validation
            if (grandTotalPoints <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Grid',
                    text: 'No bets entered.'
                });
                return;
            }

            // --- NEW: COST CALCULATION ADJUSTMENT ---
            // If user selects 5 advance draws, the total cost multiplies by 5.
            // We need to show this to the user or validate balance frontend.
            let multiplier = 1;
            if (advanceDrawTimes.length > 0) {
                multiplier = advanceDrawTimes.length;
            }

            const finalTotalCost = grandTotalPoints * multiplier;
            // -----------------------------------------

            const btn = document.getElementById('btnPlaceBet');
            btn.disabled = true;
            btn.innerHTML = 'Processing...';

            // 5. Gather Data (Grid Pattern)
            const gridPattern = {};
            document.querySelectorAll('.input-bet-field').forEach(input => {
                const val = parseInt(input.value || 0);
                if (val > 0) {
                    gridPattern[`${input.dataset.row}-${input.dataset.col}`] = val;
                }
            });

            const bets = [];

            const checkedSeriesTabs = document.querySelectorAll('.series-select:checked');

            checkedSeriesTabs.forEach(seriesCheckbox => {
                const baseSeries = parseInt(seriesCheckbox.value, 10);

                // document.querySelectorAll('.series-row-compact').forEach((rowEl, index) => {
                //     if (rowEl.querySelector('.row-selector').checked) {
                //         const seriesStart = baseSeries + (index * 100);
                //         const seriesNumbers = {};

                //         for (const [key, qty] of Object.entries(gridPattern)) {
                //             const [r, c] = key.split('-').map(Number);
                //             seriesNumbers[seriesStart + (r * 10) + c] = qty;
                //         }

                //         bets.push({
                //             series_start: seriesStart,
                //             row_index: index,
                //             numbers: seriesNumbers
                //         });
                //     }
                // });

                document.querySelectorAll('.series-row-compact').forEach((rowEl, index) => {
                    if (rowEl.querySelector('.row-selector').checked) {
                        const seriesStart = baseSeries + (index * 100);
                        const seriesNumbers = {};

                        for (const [key, qty] of Object.entries(gridPattern)) {
                            const [r, c] = key.split('-').map(Number);
                            seriesNumbers[seriesStart + (r * 10) + c] = qty;
                        }

                        // Get actual unit points - handles HIGH/LOW mode correctly
                        const displayAmt = rowEl.querySelector('.display-amt')?.innerText || String(
                            ticketPrice);
                        let unitPoints = ticketPrice;
                        if (displayAmt.includes('*')) {
                            const [a, b] = displayAmt.split('*').map(v => parseInt(v.trim()));
                            unitPoints = a * b; // e.g. 2 * 20 = 40
                        }

                        bets.push({
                            series_start: seriesStart,
                            row_index: index,
                            unit_points: unitPoints, // ← HIGH/LOW mode points per number
                            numbers: seriesNumbers
                        });
                    }
                });
            });

            // 6. Send to Backend
            fetch("{{ route('bet.place') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        bets: bets,
                        total_points: grandTotalPoints, // Base points per draw
                        ticket_price: ticketPrice,
                        draw_times: advanceDrawTimes // <--- SENDING ARRAY
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Bet Placed!',
                            html: `<b>${data.message}</b><br>Draws: ${Math.max(1, advanceDrawTimes.length)}<br>Total Cost: <b>${data.total_points}</b>`,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) window.location.reload();
                        });
                        document.getElementById('btnClear').click();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: 'Check console.'
                    });
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Place Bet';
                });
        }
    </script>

    {{-- auto refresh if user on idle --}}

    {{-- Smart Auto-Refresh Logic (Optimized for Draw Platforms) --}}
    <script>
        let idleTimer;
        // 15 Minutes in milliseconds (Matching your draw frequency)
        const idleLimit = 15 * 60 * 1000;

        function resetTimer() {
            // 1. Clear the existing countdown
            clearTimeout(idleTimer);

            // 2. Start a new countdown
            idleTimer = setTimeout(function() {

                // 3. DIRTY CHECK: Check if the user is currently entering bets
                const allInputs = document.querySelectorAll('.input-bet-field');
                let hasActiveData = false;

                allInputs.forEach(input => {
                    // Check if input has a value and that value is not just zero
                    if (input.value.trim() !== "" && input.value !== "0") {
                        hasActiveData = true;
                    }
                });

                // 4. PROTECTION LOGIC:
                if (hasActiveData) {
                    console.log("Sync skipped: User has active data in the betting grid.");
                    resetTimer();
                    return;
                }

                // 5. SYNC ACTION: If truly idle, show a professional refresh notice
                Swal.fire({
                    title: 'Syncing Dashboard...',
                    text: 'Updating latest draw results and wallet balance.',
                    icon: 'info',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 2500, // Show for 2.5 seconds
                    didOpen: () => {
                        Swal.showLoading(); // Shows the spinning loader
                    }
                }).then(() => {
                    // 6. FINAL RELOAD: Refresh the page to fetch new results from the server
                    window.location.reload();
                });

            }, idleLimit);
        }

        // --- ACTIVITY LISTENERS ---
        // These events will reset the 15-minute timer
        window.onload = resetTimer;
        window.onmousemove = resetTimer;
        window.onmousedown = resetTimer;
        window.ontouchstart = resetTimer;
        window.onclick = resetTimer;
        window.onkeypress = resetTimer;
        window.addEventListener('scroll', resetTimer, true);

        console.log("✅ Smart-Sync Protection Active: Refreshing only when grid is empty.");
    </script>

@endsection
