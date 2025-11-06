{{-- filepath: resources/views/tenant/payroll/exports/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
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

        .filters {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            font-size: 8px;
        }

        .filters .filter-item {
            display: inline-block;
            margin-right: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 7px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
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
            font-size: 8px;
        }

        .summary-label {
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7px;
            color: #666;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
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
        <h1>PAYROLL REPORT</h1>
        <p>Generated: {{ $generatedDate }}</p>
        @if (!empty($filters['dateRange']))
            <p>Date Range: {{ $filters['dateRange'] }}</p>
        @endif
    </div>

    @if($filters['branch'] || $filters['department'] || $filters['designation'] || $filters['dateRange'])
    <div class="filters">
        <strong style="font-size: 9px;">Applied Filters:</strong>
        @if($filters['dateRange'])
            <span class="filter-item"><strong>Date Range:</strong> {{ $filters['dateRange'] }}</span>
        @endif
        @if($filters['branch'])
            <span class="filter-item"><strong>Branch:</strong> {{ $filters['branch'] }}</span>
        @endif
        @if($filters['department'])
            <span class="filter-item"><strong>Department:</strong> {{ $filters['department'] }}</span>
        @endif
        @if($filters['designation'])
            <span class="filter-item"><strong>Designation:</strong> {{ $filters['designation'] }}</span>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 2%;">No.</th>
                <th style="width: 5%;">Employee ID</th>
                <th style="width: 10%;">Employee Name</th>
                <th style="width: 6%;">Branch</th>
                <th style="width: 6%;">Department</th>
                <th style="width: 6%;">Designation</th>
                <th style="width: 5%;">Type</th>
                <th style="width: 8%;">Period</th>
                <th style="width: 5%;">Total Hours</th>
                <th style="width: 4%;">Late</th>
                <th style="width: 4%;">Undertime</th>
                <th style="width: 6%;">Basic Pay</th>
                <th style="width: 6%;">Gross Pay</th>
                <th style="width: 6%;">Earnings</th>
                <th style="width: 6%;">Deductions</th>
                <th style="width: 6%;">Net Pay</th>
                <th style="width: 5%;">Payment Date</th>
                <th style="width: 4%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $index => $payroll)
            @php
                $row = $exporter->formatRow($payroll, $index);
            @endphp
            <tr>
                <td class="text-center">{{ $row[0] }}</td>
                <td class="text-center">{{ $row[1] }}</td>
                <td>{{ $row[2] }}</td>
                <td>{{ $row[3] }}</td>
                <td>{{ $row[4] }}</td>
                <td>{{ $row[5] }}</td>
                <td class="text-center">{{ $row[6] }}</td>
                <td class="text-center">
                    @if($payroll->payroll_period_start && $payroll->payroll_period_end)
                        {{ \Carbon\Carbon::parse($payroll->payroll_period_start)->format('m/d') }} -
                        {{ \Carbon\Carbon::parse($payroll->payroll_period_end)->format('m/d/Y') }}
                    @else
                        {{ $row[7] }}
                    @endif
                </td>
                <td class="text-center">{{ $row[11] }}</td>
                <td class="text-center">{{ $row[12] }}</td>
                <td class="text-center">{{ $row[13] }}</td>
                <td class="text-right">{{ $row[14] }}</td>
                <td class="text-right">{{ $row[15] }}</td>
                <td class="text-right">{{ $row[16] }}</td>
                <td class="text-right">{{ $row[17] }}</td>
                <td class="text-right">{{ $row[18] }}</td>
                <td class="text-center">{{ $row[19] }}</td>
                <td class="text-center">
                    @if ($payroll->status === 'Released')
                        <span class="badge badge-success">Released</span>
                    @elseif($payroll->status === 'Approved')
                        <span class="badge badge-warning">Approved</span>
                    @else
                        <span class="badge badge-secondary">{{ $payroll->status }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="8" class="text-right">TOTAL:</td>
                <td class="text-center">{{ $summaryTotals['total_worked_hours_formatted'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $summaryTotals['total_late_hours_formatted'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $summaryTotals['total_undertime_hours_formatted'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($summaryTotals['total_basic_pay'], 2) }}</td>
                <td class="text-right">{{ number_format($summaryTotals['total_gross_pay'], 2) }}</td>
                <td class="text-right">{{ number_format($summaryTotals['total_earnings'], 2) }}</td>
                <td class="text-right">{{ number_format($summaryTotals['total_deductions'], 2) }}</td>
                <td class="text-right">{{ number_format($summaryTotals['total_net_pay'], 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Employees:</span>
            <span>{{ $summaryTotals['total_employees'] }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Basic Pay:</span>
            <span>₱{{ number_format($summaryTotals['total_basic_pay'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Gross Pay:</span>
            <span>₱{{ number_format($summaryTotals['total_gross_pay'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Earnings:</span>
            <span>₱{{ number_format($summaryTotals['total_earnings'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Deductions:</span>
            <span>₱{{ number_format($summaryTotals['total_deductions'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Net Pay:</span>
            <span>₱{{ number_format($summaryTotals['total_net_pay'], 2) }}</span>
        </div>
        <div class="summary-row" style="margin-top: 8px; border-top: 1px solid #ddd; padding-top: 8px;">
            <span class="summary-label">Total Work Hours:</span>
            <span>{{ $summaryTotals['total_worked_hours_formatted'] ?? 'N/A' }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Late Hours:</span>
            <span>{{ $summaryTotals['total_late_hours_formatted'] ?? 'N/A' }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Undertime:</span>
            <span>{{ $summaryTotals['total_undertime_hours_formatted'] ?? 'N/A' }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Overtime:</span>
            <span>{{ $summaryTotals['total_overtime_hours_formatted'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>&copy; {{ date('Y') }} Timora By JAF Digital Group Inc. All rights reserved.</p>
    </div>
</body>
</html>
