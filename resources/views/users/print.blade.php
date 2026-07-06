<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>User Report</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
        }

        h2 {
            margin: 0;
            text-align: center;
        }

        .sub-title {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            border: 1px solid #000;
            padding: 7px;
            font-size: 12px;
        }

        table.report th {
            background: #efefef;
            text-align: center;
        }

        table.report td {
            text-align: center;
        }

        table.report td.name {
            text-align: left;
        }

        tfoot td {
            font-weight: bold;
            background: #f7f7f7;
        }

        .text-success {
            color: green;
        }

        .text-danger {
            color: red;
        }

        @media print {

            .no-print {
                display: none;
            }

        }
    </style>
</head>

<body>

    <div class="no-print" style="text-align:right;margin-bottom:15px;">
        <button onclick="window.print()">Print</button>
    </div>

    <h2>User Report</h2>

    <div class="sub-title">
        From :
        <strong>{{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }}</strong>

        &nbsp;&nbsp;&nbsp;

        To :
        <strong>{{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</strong>

        <br><br>

        Generated :
        {{ now()->format('d-m-Y h:i A') }}
    </div>

    <table class="report">

        <thead>

            <tr>

                <th width="5%">#</th>

                <th width="25%">User</th>

                <th width="15%">Role</th>

                <th width="15%">Balance</th>

                <th width="15%">Total Play</th>

                <th width="15%">Total Win</th>

                <th width="15%">House P/L</th>

            </tr>

        </thead>

        <tbody>


            @forelse($users as $user)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td class="name">

                        <strong>
                            {{ $user->first_name }}
                            {{ $user->last_name }}
                        </strong>

                        <br>

                        <small>{{ $user->username }}</small>

                    </td>

                    <td>

                        {{ $user->role->name ?? '-' }}

                    </td>

                    <td>

                        ₹{{ number_format($user->report_balance, 2) }}

                    </td>

                    <td>

                        ₹{{ number_format($user->report_total_play, 2) }}

                    </td>

                    <td>

                        ₹{{ number_format($user->report_total_win, 2) }}

                    </td>

                    <td class="{{ $user->report_house_profit >= 0 ? 'text-success' : 'text-danger' }}">

                        ₹{{ number_format($user->report_house_profit, 2) }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7">

                        No Data Found

                    </td>

                </tr>
            @endforelse

        </tbody>

        <tfoot>

            <tr>

                <td colspan="3">

                    GRAND TOTAL

                </td>

                <td>

                    ₹{{ number_format($grandTotalBalance, 2) }}

                </td>

                <td>

                    ₹{{ number_format($grandTotalPlay, 2) }}

                </td>

                <td>

                    ₹{{ number_format($grandTotalWin, 2) }}

                </td>

                <td>

                    ₹{{ number_format($grandTotalProfit, 2) }}

                </td>

            </tr>

        </tfoot>

    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>

</body>

</html>
