<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>RWINLOT Ticket</title>

    <style>
        @page {
            margin: 2mm;
            size: auto;
        }

        html,
        body {

            margin: 0;
            padding: 0;

            font-family: "Courier New", monospace;

            font-size: 10px;

            background: #fff;

            color: #000;

        }

        .ticket {

            width: 58mm;

            margin: 0;

            padding: 2mm;

            box-sizing: border-box;

            page-break-after: always;

        }

        .title {

            text-align: center;

            font-size: 18px;

            font-weight: bold;

            letter-spacing: 1px;

        }

        .subtitle {

            text-align: center;

            font-size: 10px;

            margin-bottom: 2px;

        }

        .line {

            border-top: 1px dashed #000;

            margin: 3px 0;

        }

        table {

            width: 100%;

            border-collapse: collapse;

        }

        td {

            padding: 1px 0;

            vertical-align: top;

            font-size: 10px;

        }

        .left {

            text-align: left;

        }

        .right {

            text-align: right;

        }

        .center {

            text-align: center;

        }

        .numbers {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 2px 0;
        }

        .numbers th {
            font-size: 10px;
            font-weight: bold;
            padding: 2px 0 4px;
            text-align: left;
        }

        .numbers td {
            font-size: 10px;
            padding: 1px 0;
            white-space: nowrap;
            vertical-align: top;
        }

        /* 1st NUM */
        .numbers th:nth-child(1),
        .numbers td:nth-child(1) {
            width: 18%;
            text-align: left;
        }

        /* 1st QTY */
        .numbers th:nth-child(2),
        .numbers td:nth-child(2) {
            width: 8%;
            text-align: right;
            padding-right: 10px;
            /* gap before 2nd NUM */
        }

        /* 2nd NUM */
        .numbers th:nth-child(3),
        .numbers td:nth-child(3) {
            width: 18%;
            text-align: left;
        }

        /* 2nd QTY */
        .numbers th:nth-child(4),
        .numbers td:nth-child(4) {
            width: 8%;
            text-align: right;
            padding-right: 10px;
            /* gap before 3rd NUM */
        }

        /* 3rd NUM */
        .numbers th:nth-child(5),
        .numbers td:nth-child(5) {
            width: 18%;
            text-align: left;
        }

        /* 3rd QTY */
        .numbers th:nth-child(6),
        .numbers td:nth-child(6) {
            width: 8%;
            text-align: right;
        }

        .footer {

            text-align: center;

            margin-top: 5px;

            font-size: 10px;

        }

        @media print {

            .ticket {

                width: 58mm;

            }

        }
    </style>

</head>

<body>

    @foreach ($tickets as $ticket)
        <div class="ticket">

            <div class="title">

                RWINLOT

            </div>

            <div class="subtitle">

                Game For Adults Only

            </div>

            <div class="line"></div>

            <table>

                <tr>

                    <td><strong>Ticket</strong></td>

                    <td class="right">{{ $ticket->ticket_no }}</td>

                </tr>

                <tr>

                    <td>Date</td>

                    <td class="right">

                        {{ $ticket->draw_date->format('d-m-Y') }}

                    </td>

                </tr>

                <tr>

                    <td>Draw Time</td>

                    <td class="right">

                        {{ \Carbon\Carbon::parse($ticket->draw_time)->format('h:i A') }}

                    </td>

                </tr>

                <tr>

                    <td>Bet Time</td>

                    <td class="right">

                        {{ $ticket->created_at->format('d-m-Y h:i A') }}

                    </td>

                </tr>

                <tr>

                    <td>Retailer Id</td>

                    <td class="right">

                        {{ strtoupper($ticket->user->unique_id) }}

                    </td>

                </tr>

            </table>

            <div class="line"></div>
            @php
                $chunks = $ticket->bets
                    ->sortBy('id')
                    ->values()
                    ->chunk(3)
                    ->map(function ($chunk) {
                        return $chunk->values();
                    });
            @endphp

            <table class="numbers">

                <thead>
                    <tr>
                        <th>NUM</th>
                        <th>QTY</th>

                        <th>NUM</th>
                        <th>QTY</th>

                        <th>NUM</th>
                        <th>QTY</th>
                    </tr>

                    <tr>
                        <td colspan="6">
                            <div class="line"></div>
                        </td>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($chunks as $row)
                        <tr>

                            @for ($i = 0; $i < 3; $i++)
                                @if (isset($row[$i]))
                                    <td>{{ str_pad($row[$i]->number, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ str_pad($row[$i]->qty, 3, ' ', STR_PAD_LEFT) }}</td>
                                @else
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                @endif
                            @endfor

                        </tr>
                    @endforeach

                </tbody>

            </table>

            <div class="line"></div>

            <table>

                <tr>

                    <td>Ticket Price</td>

                    <td class="right">

                        ₹ {{ number_format($ticket->ticket_price, 2) }}

                    </td>

                </tr>

                <tr>

                    <td>Total Qty</td>

                    <td class="right">

                        {{ $ticket->total_qty }}

                    </td>

                </tr>

                <tr>

                    <td>Total Amount</td>

                    <td class="right">

                        ₹ {{ number_format($ticket->total_amount, 2) }}

                    </td>

                </tr>

            </table>

            <div class="line"></div>

            <div class="center">

                <strong>{{ $ticket->ticket_no }}</strong>

            </div>

            <div class="line"></div>

            <div class="footer">

                <strong>THANK YOU</strong>

                <br>

                Best Of Luck!

                <br><br>

                <strong>RWINLOT</strong>

            </div>

        </div>
    @endforeach

    <script>
        window.onload = function() {

            window.print();

        };
    </script>

</body>

</html>
