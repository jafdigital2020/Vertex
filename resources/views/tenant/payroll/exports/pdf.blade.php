{{-- filepath: resources/views/tenant/payroll/exports/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 9px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .export-info {
            font-size: 9px;
            color: #666;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .filters h4 {
            margin: 0 0 10px 0;
            font-size: 11px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 9px;
        }
        .summary {
            background-color: #e7f3ff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary h4 {
            margin: 0 0 10px 0;
            font-size: 11px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 10px;
            font-weight: bold;
        }
        /* ✅ NEW: Time summary grid */
        .time-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 7px;
        }
        th {
            background-color: #366092;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .currency {
            text-align: right;
        }
        .center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .time-column {
            text-align: center;
            font-size: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Timora - Payroll Management System</div>
        <div class="report-title">Payroll Report</div>
        <div class="export-info">
            Generated on {{ $exportDate }} at {{ $exportTime }}
        </div>
    </div>

    @if($filters['branch'] || $filters['department'] || $filters['designation'] || $filters['dateRange'])
    <div class="filters">
        <h4>Applied Filters:</h4>
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

    <div class="summary">
        <h4>Summary</h4>
        <!-- Financial Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Employees</div>
                <div class="summary-value">{{ number_format($summaryTotals['total_employees']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Earnings</div>
                <div class="summary-value">₱{{ number_format($summaryTotals['total_earnings'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Deductions</div>
                <div class="summary-value">₱{{ number_format($summaryTotals['total_deductions'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Net Pay</div>
                <div class="summary-value">₱{{ number_format($summaryTotals['total_net_pay'], 2) }}</div>
            </div>
        </div>

        <!-- ✅ NEW: Time Summary -->
        <div class="time-summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Work Hours</div>
                <div class="summary-value">{{ $summaryTotals['total_worked_hours_formatted'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Late Hours</div>
                <div class="summary-value">{{ $summaryTotals['total_late_hours_formatted'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Undertime</div>
                <div class="summary-value">{{ $summaryTotals['total_undertime_hours_formatted'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Overtime</div>
                <div class="summary-value">{{ $summaryTotals['total_overtime_hours_formatted'] }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 2%;">No.</th>
                <th style="width: 6%;">Employee ID</th>
                <th style="width: 12%;">Employee Name</th>
                <th style="width: 8%;">Branch</th>
                <th style="width: 8%;">Department</th>
                <th style="width: 8%;">Designation</th>
                <th style="width: 6%;">Type</th>
                <th style="width: 8%;">Period</th>
                <th style="width: 6%;">Total Hours</th>
                <th style="width: 5%;">Late</th>
                <th style="width: 5%;">Undertime</th>
                <th style="width: 6%;">Basic Pay</th>
                <th style="width: 6%;">Earnings</th>
                <th style="width: 6%;">Deductions</th>
                <th style="width: 6%;">Net Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $index => $payroll)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ $payroll->user->employee_id ?? 'N/A' }}</td>
                <td>
                    {{ ($payroll->user->personalInformation->last_name ?? '') }}
                    {{ $payroll->user->personalInformation->suffix ? ' ' . $payroll->user->personalInformation->suffix : '' }},
                    {{ ($payroll->user->personalInformation->first_name ?? '') }}
                    {{ ($payroll->user->personalInformation->middle_name ?? '') }}
                </td>
                <td>{{ $payroll->user->employmentDetail->branch->name ?? 'N/A' }}</td>
                <td>{{ $payroll->user->employmentDetail->department->department_name ?? 'N/A' }}</td>
                <td>{{ $payroll->user->employmentDetail->designation->designation_name ?? 'N/A' }}</td>
                <td class="center">{{ ucfirst(str_replace('_', ' ', $payroll->payroll_type ?? 'N/A')) }}</td>
                <td class="center">
                    @if($payroll->payroll_period_start && $payroll->payroll_period_end)
                        {{ \Carbon\Carbon::parse($payroll->payroll_period_start)->format('m/d') }} -
                        {{ \Carbon\Carbon::parse($payroll->payroll_period_end)->format('m/d/Y') }}
                    @else
                        N/A
                    @endif
                </td>
                <!-- ✅ NEW: Time columns with formatted display -->
                <td class="time-column">
                    @php
                        $totalMinutes = $payroll->total_worked_minutes ?? 0;
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                    @endphp
                    {{ $hours }}h {{ $minutes }}m
                </td>
                <td class="time-column">
                    @php
                        $lateMinutes = $payroll->total_late_minutes ?? 0;
                        $lateHours = floor($lateMinutes / 60);
                        $lateMins = $lateMinutes % 60;
                    @endphp
                    {{ $lateHours }}h {{ $lateMins }}m
                </td>
                <td class="time-column">
                    @php
                        $undertimeMinutes = $payroll->total_undertime_minutes ?? 0;
                        $undertimeHours = floor($undertimeMinutes / 60);
                        $undertimeMins = $undertimeMinutes % 60;
                    @endphp
                    {{ $undertimeHours }}h {{ $undertimeMins }}m
                </td>
                <td class="currency">₱{{ number_format($payroll->basic_pay ?? 0, 2) }}</td>
                <td class="currency">₱{{ number_format($payroll->total_earnings ?? 0, 2) }}</td>
                <td class="currency">₱{{ number_format($payroll->total_deductions ?? 0, 2) }}</td>
                <td class="currency">₱{{ number_format($payroll->net_salary ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report. No signature required.</p>
        <p>© {{ date('Y') }} Timora - Payroll Management System</p>
    </div>
</body>
</html>
