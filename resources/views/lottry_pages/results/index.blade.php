@extends('layouts.app')

@section('content')
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-warning m-0">Daily Draw Results</h3>

            <div class="d-flex align-items-center gap-2">
                {{-- Date Filter Form --}}
                <form action="{{ route('results.index') }}" method="GET" id="filterForm" class="d-flex gap-2">
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate }}"
                        onchange="document.getElementById('filterForm').submit()">

                    {{-- Reset Button --}}
                    @if ($selectedDate !== \Carbon\Carbon::today()->format('Y-m-d'))
                        <a href="{{ route('results.index', ['reset' => 1]) }}" class="btn btn-outline-danger">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th style="width: 150px; background-color: #1a1a2e; color: #ffd700;">Draw Time</th>
                        @foreach ($seriesList as $series)
                            @for ($i = 0; $i < 10; $i++)
                                @php
                                    $subStart = (int) $series->start + $i * 100;
                                    $colors = [
                                        0 => '#e6ee9c',
                                        1 => '#ffab91',
                                        2 => '#9ccc65',
                                        3 => '#ffcc80',
                                        4 => '#c5e1a5',
                                        5 => '#fff59d',
                                        6 => '#f48fb1',
                                        7 => '#ffca28',
                                        8 => '#66bb6a',
                                        9 => '#ffe082',
                                    ];
                                @endphp
                                <th style="background-color: {{ $colors[$i % 10] }}; color: #000000;">
                                    {{ $subStart }}
                                </th>
                            @endfor
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $time => $drawResults)
                        <tr>
                            <td class="fw-bold text-warning">{{ $time }}</td>
                            @foreach ($seriesList as $series)
                                @for ($i = 0; $i < 10; $i++)
                                    @php
                                        $colors = [
                                            0 => '#e6ee9c',
                                            1 => '#ffab91',
                                            2 => '#9ccc65',
                                            3 => '#ffcc80',
                                            4 => '#c5e1a5',
                                            5 => '#fff59d',
                                            6 => '#f48fb1',
                                            7 => '#ffca28',
                                            8 => '#66bb6a',
                                            9 => '#ffe082',
                                        ];
                                        $subStart = (int) $series->start + $i * 100;
                                        $match = $drawResults->where('series', $subStart)->first();
                                        $bgColor = $colors[$i % 10];
                                    @endphp
                                    <td style="background-color: {{ $bgColor }}; padding: 4px;">
                                        @if ($match)
                                            <span class="fs-6 fw-bold" style="color: #cc0000;">
                                                {{ $match->result_number }}
                                            </span>
                                        @else
                                            <span style="color: #000;">--</span>
                                        @endif
                                    </td>
                                @endfor
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $seriesList->count() * 10 + 1 }}" class="py-5 text-center text-muted">
                                No results generated for this date yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
