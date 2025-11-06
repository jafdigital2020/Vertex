<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>13th Month Pay Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .header p {
            margin: 3px 0;
            font-size: 9px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 15px;
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .summary-label {
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>13TH MONTH PAY REPORT</h1>
        <p>Generated: {{ $generatedDate }}</p>
        @if (!empty($filters['year']))
            <p>Year: {{ $filters['year'] }}</p>
        @endif
        @if (!empty($filters['dateRange']))
            <p>Date Range: {{ $filters['dateRange'] }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($payrolls as $index => $payroll)
                @php
                    $row = $exporter->formatRow($payroll, $index);
                @endphp
                <tr>
                    <td class="text-center">{{ $row[0] }}</td>
                    <td>{{ $row[1] }}</td>
                    <td>{{ $row[2] }}</td>
                    <td>{{ $row[3] }}</td>
                    <td>{{ $row[4] }}</td>
                    <td>{{ $row[5] }}</td>
                    <td>{{ $row[6] }}</td>
                    <td class="text-center">{{ $row[7] }}</td>
                    <td class="text-right">{{ $row[8] }}</td>
                    <td class="text-right">{{ $row[9] }}</td>
                    <td class="text-right">{{ $row[10] }}</td>
                    <td class="text-center">{{ $row[11] }}</td>
                    <td>{{ $row[12] }}</td>
                    <td class="text-center">
                        @if ($payroll->status === 'Released')
                            <span class="badge badge-success">Released</span>
                        @elseif($payroll->status === 'Approved')
                            <span class="badge badge-warning">Paid</span>
                        @else
                            <span class="badge badge-secondary">{{ $payroll->status }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $row[14] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="8" class="text-right">TOTAL:</td>
                <td class="text-right">{{ number_format($totals['total_basic_pay'], 2) }}</td>
                <td class="text-right">{{ number_format($totals['total_deductions'], 2) }}</td>
                <td class="text-right">{{ number_format($totals['total_thirteenth_month'], 2) }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Employees:</span>
            <span>{{ $totals['total_employees'] }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Basic Pay:</span>
            <span>₱{{ number_format($totals['total_basic_pay'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Deductions:</span>
            <span>₱{{ number_format($totals['total_deductions'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total 13th Month Pay:</span>
            <span>₱{{ number_format($totals['total_thirteenth_month'], 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>&copy; {{ date('Y') }} Timora By JAF Digital Group Inc. All rights reserved.</p>
    </div>
</body>

</html>
