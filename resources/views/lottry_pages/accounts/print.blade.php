<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>Account Report</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            background: #fff;
            line-height: 1.3;
        }

        .container {
            width: 640px;
            max-width: 100%;
            margin: 0 auto;
        }

        h1 {
            margin: 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        h2 {
            margin: 8px 0 18px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        h3 {
            margin: 18px 0 8px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            page-break-after: avoid;
        }

        .info {
            margin-bottom: 18px;
            line-height: 22px;
            font-size: 13px;
        }

        table {
            width: 98%;
            margin: 0 auto 18px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        th {
            background: #333;
            color: #fff;
            border: 1px solid #000;
            padding: 8px;
            font-size: 13px;
            text-align: center;
        }

        td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 13px;
            text-align: center;
        }

        .amount {
            width: 170px;
            text-align: right;
            padding-right: 10px;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
        }

        @media screen {

            body {
                background: #f3f3f3;
            }

            .container {
                background: #fff;
                padding: 25px;
                margin: 30px auto;
                box-shadow: 0 0 8px rgba(0, 0, 0, .15);
            }

        }

        @media print {

            body {
                background: #fff;
            }

            .container {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            button {
                display: none;
            }

        }
    </style>
</head>

<body>

    <div class="container">

        <h1>RWINLOT</h1>

        <h2>ACCOUNT SUMMARY REPORT</h2>

        <div class="info">

            <strong>Retailer :</strong>
            {{ strtoupper($auth->username) }}

            <br>

            <strong>From :</strong>
            {{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }}

            <br>

            <strong>To :</strong>
            {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}

            <br>

            <strong>Printed :</strong>

            {{ now()->format('d-m-Y h:i A') }}

        </div>

        <h3>REPORT - 1</h3>

        <table>

            <tr>

                <th>Description</th>

                <th>Amount</th>

            </tr>

            <tr>

                <td>Play Point</td>

                <td class="amount">{{ number_format($report1['play_point'], 2) }}</td>

            </tr>

            <tr>

                <td>Commission</td>

                <td class="amount">{{ number_format($report1['commission'], 2) }}</td>

            </tr>

            <tr>

                <td>Win Point</td>

                <td class="amount">{{ number_format($report1['win_point'], 2) }}</td>

            </tr>

            <tr>

                <td><strong>Net</strong></td>

                <td class="amount">

                    <strong>

                        {{ $report1['net'] < 0 ? '-' : '' }}

                        {{ number_format(abs($report1['net']), 2) }}

                    </strong>

                </td>

            </tr>

        </table>

        <h3>REPORT - 2</h3>

        <table>

            <tr>

                <th>Description</th>

                <th>Amount</th>

            </tr>

            <tr>

                <td>Play Amount</td>

                <td class="amount">

                    {{ number_format($report2['play_point'], 2) }}

                </td>

            </tr>

            <tr>

                <td>Win Amount</td>

                <td class="amount">

                    {{ number_format($report2['win_point'], 2) }}

                </td>

            </tr>

            <tr>

                <td><strong>Net</strong></td>

                <td class="amount">

                    <strong>

                        {{ $report2['net'] < 0 ? '-' : '' }}

                        {{ number_format(abs($report2['net']), 2) }}

                    </strong>

                </td>

            </tr>

        </table>

        <div class="footer">

            <strong>Generated By RWINLOT</strong>

        </div>

    </div>

    <script>
        window.onload = function() {

            window.print();

        };
    </script>

</body>

</html>
