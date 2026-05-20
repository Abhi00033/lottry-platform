@extends('layouts.app')

@section('content')

    <div class="container py-3">

        <div class="d-flex
                justify-content-between
                align-items-center
                mb-3">

            <h3 class="fw-bold text-danger m-0">

                Cancel Bets

            </h3>

        </div>

        @forelse($transactions as $txn)
            @php
                $groupedBets = $txn->bets->groupBy(function ($bet) {
                    return \Carbon\Carbon::parse($bet->draw_time)->format('Y-m-d H:i:s');
                });
            @endphp

            <div class="card mb-4 border-dark">

                <div
                    class="card-header
                        bg-dark
                        text-warning
                        d-flex
                        justify-content-between">

                    <div>

                        <strong>
                            TXN:
                        </strong>

                        {{ $txn->transaction_number }}

                    </div>

                    <div>

                        ₹{{ number_format($txn->amount, 2) }}

                    </div>

                </div>

                <div class="card-body">

                    {{-- DRAW TABS --}}
                    <ul class="nav nav-tabs mb-3">

                        @foreach ($groupedBets as $drawTime => $bets)
                            @php
                                $tabId = 'tab_' . md5($txn->id . $drawTime);
                                $drawObj = \Carbon\Carbon::parse($drawTime);
                            @endphp

                            <li class="nav-item">

                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                                    data-bs-target="#{{ $tabId }}" type="button">

                                    {{ $drawObj->format('h:i A') }}

                                </button>

                            </li>
                        @endforeach

                    </ul>

                    {{-- TAB CONTENT --}}
                    <div class="tab-content">

                        @foreach ($groupedBets as $drawTime => $bets)
                            @php

                                $tabId = 'tab_' . md5($txn->id . $drawTime);

                                $drawObj = \Carbon\Carbon::parse($drawTime);

                            @endphp

                            <div class="tab-pane fade
                            {{ $loop->first ? 'show active' : '' }}"
                                id="{{ $tabId }}">

                                <div
                                    class="d-flex
                                        justify-content-between
                                        align-items-center
                                        mb-3">

                                    <div>

                                        <strong>
                                            Draw:
                                        </strong>

                                        {{ $drawObj->format('d M Y h:i A') }}

                                    </div>

                                    <form method="POST" action="{{ route('bets.cancel.draw') }}">

                                        @csrf

                                        <input type="hidden" name="transaction_id" value="{{ $txn->id }}">

                                        <input type="hidden" name="draw_time" value="{{ $drawTime }}">

                                        <button class="btn btn-danger btn-sm">

                                            Cancel Draw

                                        </button>

                                    </form>

                                </div>

                                <table class="table table-bordered">

                                    <thead class="table-dark">

                                        <tr>

                                            <th>Number</th>
                                            <th>Qty</th>
                                            <th>Points</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        @foreach ($bets as $bet)
                                            <tr>

                                                <td>
                                                    {{ $bet->number }}
                                                </td>

                                                <td>
                                                    {{ $bet->qty }}
                                                </td>

                                                <td>
                                                    ₹{{ number_format($bet->points, 2) }}
                                                </td>

                                            </tr>
                                        @endforeach

                                    </tbody>

                                </table>

                            </div>
                        @endforeach

                    </div>

                </div>

            </div>

        @empty

            <div class="alert alert-secondary">

                No cancellable bets found.

            </div>
        @endforelse

    </div>

@endsection
