@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3 px-2">

        {{-- ===== Header + Filter (left-aligned, not full right) ===== --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            {{-- Title --}}
            <h3 class="fw-bold text-warning m-0">
                <i class="fas fa-trophy me-2"></i>Daily Draw Results
            </h3>

            {{-- Filter: sits center-right, not pushed to extreme right --}}
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('results.index') }}" method="GET" id="filterForm"
                    class="d-flex align-items-center gap-2">

                    <span class="fw-bold text-white" style="font-size:0.9rem; white-space:nowrap;">
                        <i class="fas fa-calendar-alt me-1 text-warning"></i> Date:
                    </span>

                    <input type="date" name="date"
                        class="form-control form-control-sm bg-dark text-white border-secondary"
                        style="max-width: 165px; color-scheme: dark;" value="{{ $selectedDate }}"
                        max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                        onchange="document.getElementById('filterForm').submit()">

                    @if ($selectedDate !== \Carbon\Carbon::today()->format('Y-m-d'))
                        <a href="{{ route('results.index', ['reset' => 1]) }}" class="btn btn-outline-danger btn-sm"
                            style="white-space:nowrap;">
                            <i class="fas fa-sync-alt me-1"></i> Today
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- ===== Day / Date Banner ===== --}}
        <div class="text-center fw-bold mb-3 py-2"
            style="background: linear-gradient(90deg, #007700, #00bb00, #007700);
                    color: #ffffff;
                    font-size: 1.1rem;
                    border-radius: 4px;
                    border: 2px solid #00cc00;
                    letter-spacing: 1px;">
            {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }} &mdash; Results
        </div>

        {{-- ===== Color Config ===== --}}
        @php
            // Light pastel colors — black font on all
            $colors = [
                0 => '#e8f5a3', // light yellow-green
                1 => '#ffb3a7', // light red/salmon
                2 => '#a8d8a8', // light green
                3 => '#ffd4b0', // light orange/peach
                4 => '#c8edc8', // very light green
                5 => '#fff3a0', // light yellow
                6 => '#f4b8d0', // light pink
                7 => '#ffd966', // light gold
                8 => '#90d090', // medium light green
                9 => '#f0e68c', // light khaki/yellow
            ];
        @endphp

        {{-- ===== Results Per Time Slot ===== --}}
        @forelse($results as $time => $drawResults)
            {{-- Time Slot Header --}}
            <div class="text-center fw-bold mb-0 mt-3"
                style="background: linear-gradient(90deg, #8b0000, #cc0000, #8b0000);
                        color: #ffd700;
                        font-size: 1.2rem;
                        padding: 9px 12px;
                        border: 2px solid #cc0000;
                        border-bottom: none;
                        border-radius: 6px 6px 0 0;
                        letter-spacing: 2px;">
                🕐 {{ $time }}
            </div>

            {{-- Results Grid --}}
            <div
                style="display: grid;
                        grid-template-columns: repeat(10, 1fr);
                        border: 2px solid #cc0000;
                        border-top: none;
                        border-radius: 0 0 6px 6px;
                        overflow: hidden;
                        margin-bottom: 0;">
                @foreach ($seriesList as $seriesIndex => $series)
                    @for ($i = 0; $i < 10; $i++)
                        @php
                            $subStart = (int) $series->start + $i * 100;
                            $match = $drawResults->where('series', $subStart)->first();
                            $bg = $colors[$i % 10];
                        @endphp
                        <div
                            style="background-color: {{ $bg }};
                                    color: #000000;
                                    font-size: 1.4rem;
                                    font-weight: 800;
                                    text-align: center;
                                    padding: 10px 4px;
                                    border: 1px solid rgba(0,0,0,0.12);
                                    font-family: 'Courier New', monospace;
                                    letter-spacing: 1px;">
                            @if ($match)
                                {{ $match->result_number }}
                            @else
                                <span style="color: #000; opacity: 0.3;">--</span>
                            @endif
                        </div>
                    @endfor
                @endforeach
            </div>

        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No results generated for
                <strong class="text-warning">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong> yet.
            </div>
        @endforelse

    </div>

    <style>
        .results-grid>div:hover {
            filter: brightness(0.95);
            cursor: default;
        }
    </style>
@endsection
