<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #000;
            background: #fff;
            padding: 18pt;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 16pt;
            padding-bottom: 10pt;
            border-bottom: 1pt solid #000;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            color: #000;
        }

        .header p {
            font-size: 9pt;
            color: #333;
            margin-top: 4pt;
        }

        .company-banner {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 5pt 0;
            font-size: 9.5pt;
            font-weight: bold;
            margin-bottom: 14pt;
        }

        .report-meta {
            display: table;
            width: 100%;
            table-layout: fixed;
            font-size: 8.5pt;
            margin-bottom: 14pt;
            padding: 7pt;
            border: 1pt solid #ccc;
            background: #f9f9f9;
        }

        .report-meta-cell {
            display: table-cell;
            vertical-align: top;
            line-height: 1.4;
        }

        .report-meta strong {
            font-weight: bold;
        }

        .filters {
            background: #f0f0f0;
            padding: 7pt;
            margin-bottom: 16pt;
            border: 1pt solid #ccc;
            font-size: 8.5pt;
        }

        .filters strong {
            display: block;
            margin-bottom: 4pt;
            font-weight: bold;
        }

        .filter-tag {
            display: inline-block;
            background: #e0e0e0;
            padding: 2pt 7pt;
            margin-right: 6pt;
            font-weight: bold;
            border-radius: 10pt;
            font-size: 8pt;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 5pt 0;
            text-align: center;
            margin: 20pt 0 12pt 0;
            border-radius: 3pt;
        }

        /* Main Data Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
            font-size: 6.5pt;
        }

        table.data-table th {
            background-color: #000 !important;
            color: #fff !important;
            padding: 4pt 3pt;
            text-align: center;
            font-weight: bold;
            border: 1pt solid #000;
            text-transform: uppercase;
            font-size: 6.5pt;
        }

        table.data-table td {
            padding: 3.5pt 3pt;
            border: 1pt solid #ccc;
            vertical-align: top;
            text-align: left;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .earn { color: #006400; font-weight: bold; }
        .ded { color: #8b0000; font-weight: bold; }
        .net { color: #b8860b; font-weight: bold; }

        /* Summary Cards */
        .summary-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 12pt;
            margin-bottom: 20pt;
        }

        .summary-card {
            width: 33%;
            padding: 0;
            vertical-align: top;
        }

        .card-box {
            border: 1.5pt solid #000;
            padding: 6pt;
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            line-height: 1.25;
        }

        .card-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 4pt;
            padding-bottom: 2pt;
            border-bottom: 1pt solid #000;
            font-size: 8pt;
        }

        .card-line {
            display: table;
            width: 100%;
            margin-bottom: 2pt;
        }

        .card-label {
            display: table-cell;
            text-align: left;
            padding-right: 4pt;
            width: 60%;
        }

        .card-value {
            display: table-cell;
            text-align: right;
            width: 40%;
        }

        .card-total-line {
            margin-top: 4pt;
            padding-top: 3pt;
            border-top: 1pt solid #000;
        }

        .card-total-label {
            font-weight: bold;
        }

        /* Final Accounting */
        .final-section {
            background: #f0f0f0;
            padding: 12pt;
            margin-bottom: 20pt;
            border: 1pt solid #ccc;
        }

        .final-section h3 {
            font-weight: bold;
            margin-bottom: 10pt;
            font-size: 10pt;
            text-align: center;
        }

        .final-totals-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 15pt;
        }

        .final-card {
            width: 50%;
            padding: 10pt;
            border: 1.5pt solid #000;
            background: #fff;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            vertical-align: top;
            font-family: Arial, sans-serif;
        }

        /* Metrics */
        .metrics {
            background: #f9f9f9;
            padding: 12pt;
            margin-bottom: 18pt;
            border: 1pt solid #ccc;
        }

        .metrics-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .metrics-col {
            display: table-cell;
            padding-right: 15pt;
            vertical-align: top;
        }

        .metrics-col h4 {
            font-weight: bold;
            margin-bottom: 8pt;
            font-size: 9pt;
            text-decoration: underline;
        }

        .metric-item {
            display: table;
            width: 100%;
            margin-bottom: 5pt;
            font-size: 8pt;
        }

        .metric-label {
            display: table-cell;
            width: 70%;
        }

        .metric-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: bold;
        }

        .status {
            font-weight: bold;
            margin-left: 4pt;
            font-size: 8pt;
        }
        .good { color: #006400; }
        .warn { color: #b8860b; }

        /* Category Section */
        .category-section {
            background: #f9f9f9;
            padding: 12pt;
            margin-bottom: 18pt;
            border: 1pt solid #ccc;
        }

        .category-section h3 {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10pt;
            font-size: 10pt;
        }

        .category-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .category-item {
            display: table-cell;
            background: #fff;
            padding: 8pt;
            border: 1pt solid #ccc;
            vertical-align: top;
        }

        .category-item h4 {
            font-weight: bold;
            margin-bottom: 6pt;
            font-size: 8.5pt;
            text-align: center;
            text-decoration: underline;
        }

        .category-row {
            display: table;
            width: 100%;
            margin-bottom: 2pt;
            font-size: 8pt;
        }

        .cat-label {
            display: table-cell;
            width: 60%;
        }

        .cat-value {
            display: table-cell;
            width: 40%;
            text-align: right;
        }

        /* Employee Detail - ENHANCED */
        .employee-detail {
            background: #fff;
            padding: 10pt;
            margin-bottom: 16pt;
            border: 1pt solid #ccc;
        }

        .employee-detail h3 {
            font-weight: bold;
            margin-bottom: 8pt;
            font-size: 9.5pt;
            color: #000;
            border-bottom: 1pt solid #000;
            padding-bottom: 3pt;
        }

        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 10pt;
            font-family: Arial, sans-serif;
        }

        table.detail-table th,
        table.detail-table td {
            padding: 3pt 4pt;
            border: 1pt solid #ccc;
            vertical-align: top;
            text-align: left;
        }

        table.detail-table th {
            background: #eee;
            font-weight: bold;
        }

        table.detail-table td.text-right {
            text-align: right;
        }

        table.detail-table td.text-center {
            text-align: center;
        }

        .tax-badge {
            padding: 1pt 4pt;
            background: #000;
            color: #fff;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 4pt;
        }

        .section-header-row td {
            background: #f0f0f0 !important;
            font-weight: bold;
            padding: 4pt;
            text-align: left;
        }

        .section-divider td {
            border-top: 1pt solid #ccc !important;
        }

        .footer {
            margin-top: 24pt;
            text-align: center;
            font-size: 7.5pt;
            color: #555;
            padding-top: 10pt;
            border-top: 1pt solid #ccc;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payroll Report & Financial Analysis</h1>
        <p>Employee Compensation Report</p>
    </div>

    <div class="company-banner">
        Timora Payroll Management System | Professional HR & Accounting Report
    </div>

    <div class="report-meta">
        <div class="report-meta-cell">
            <strong>Generated:</strong> {{ $generatedDate }}<br>
            <strong>Total Employees:</strong> {{ $summaryTotals['total_employees'] ?? 0 }}
        </div>
        <div class="report-meta-cell">
            <strong>Report Type:</strong> Detailed Breakdown<br>
            <strong>Status:</strong> {{ $payrolls->count() > 0 ? 'Complete' : 'No Data' }}
        </div>
    </div>

    @if($filters['branch'] || $filters['department'] || $filters['designation'] || $filters['dateRange'])
    <div class="filters">
        <strong>APPLIED FILTERS</strong>
        @if($filters['dateRange'])
            <span class="filter-tag">Period: {{ $filters['dateRange'] }}</span>
        @endif
        @if($filters['branch'])
            <span class="filter-tag">Branch: {{ $filters['branch'] }}</span>
        @endif
        @if($filters['department'])
            <span class="filter-tag">Department: {{ $filters['department'] }}</span>
        @endif
        @if($filters['designation'])
            <span class="filter-tag">Position: {{ $filters['designation'] }}</span>
        @endif
    </div>
    @endif

    <div class="section-title">EMPLOYEE INFORMATION & COMPENSATION SUMMARY</div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Branch</th>
                <th>Basic Pay</th>
                <th>Overtime Pay</th>
                <th>Allowances</th>
                <th>Gross Pay</th>
                <th>SSS</th>
                <th>PhilHealth</th>
                <th>Pag-IBIG</th>
                <th>W/Tax</th>
                <th>Total Deductions</th>
                <th>Net Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $index => $payroll)
                @php $row = $exporter->formatRow($payroll, $index); @endphp
                <tr>
                    <td>{{ $row[1] }}</td>
                    <td>{{ $row[2] }}</td>
                    <td>{{ $row[4] }}</td>
                    <td>{{ $row[5] }}</td>
                    <td>{{ $row[3] }}</td>
                    <td class="text-right">PHP {{ $row[15] }}</td>
                    <td class="text-right">PHP {{ $row[17] }}</td>
                    <td class="text-right">PHP {{ $row[22] }}</td>
                    <td class="text-right earn">PHP {{ $row[24] }}</td>
                    <td class="text-right">PHP {{ $row[29] }}</td>
                    <td class="text-right">PHP {{ $row[30] }}</td>
                    <td class="text-right">PHP {{ $row[31] }}</td>
                    <td class="text-right">PHP {{ $row[32] }}</td>
                    <td class="text-right ded">PHP {{ $row[35] }}</td>
                    <td class="text-right net">PHP {{ $row[37] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <div class="section-title">PAYROLL SUMMARY & ANALYTICS</div>

    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="card-box">
                    <div class="card-title">TOTAL EARNINGS</div>
                    <div class="card-line">
                        <div class="card-label">Basic Pay</div>
                        <div class="card-value">PHP {{ number_format($summaryTotals['total_basic_pay'], 2) }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">Overtime Pay</div>
                        <div class="card-value">PHP {{ number_format($summaryTotals['total_overtime_pay'], 2) }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">Allowances</div>
                        <div class="card-value">PHP {{ number_format($summaryTotals['total_allowances'], 2) }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">&nbsp;</div>
                        <div class="card-value">&nbsp;</div>
                    </div>
                    <div class="card-total-line">
                        <div class="card-line">
                            <div class="card-label card-total-label">GROSS PAY</div>
                            <div class="card-value card-total-label">PHP {{ number_format($summaryTotals['total_gross_pay'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </td>
            <td class="summary-card">
                <div class="card-box">
                    <div class="card-title">TOTAL DEDUCTIONS</div>
                    <div class="card-line">
                        <div class="card-label">SSS</div>
                        <div class="card-value">PHP {{ number_format($summaryTotals['total_sss_contribution'], 2) }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">PhilHealth</div>
                        <div class="card-value">PHP {{ number_format($summaryTotals['total_philhealth_contribution'], 2) }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">Pag-IBIG</div>
                        <div class="card-value">PHP {{ number_format($summaryTotals['total_pagibig_contribution'], 2) }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">Withholding Tax</div>
                        <div class="card-value">PHP {{ number_format($summaryTotals['total_withholding_tax'], 2) }}</div>
                    </div>
                    <div class="card-total-line">
                        <div class="card-line">
                            <div class="card-label card-total-label">TOTAL</div>
                            <div class="card-value card-total-label">PHP {{ number_format($summaryTotals['total_deductions'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </td>
            <td class="summary-card">
                <div class="card-box">
                    <div class="card-title">FINAL TOTALS</div>
                    <div class="card-line">
                        <div class="card-label">Employees</div>
                        <div class="card-value">{{ $summaryTotals['total_employees'] }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">Total Hours</div>
                        <div class="card-value">{{ $summaryTotals['total_worked_hours_formatted'] ?? 'N/A' }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">Avg per Employee</div>
                        <div class="card-value">PHP {{ $summaryTotals['total_employees'] > 0 ? number_format($summaryTotals['total_net_pay'] / $summaryTotals['total_employees'], 2) : '0.00' }}</div>
                    </div>
                    <div class="card-line">
                        <div class="card-label">&nbsp;</div>
                        <div class="card-value">&nbsp;</div>
                    </div>
                    <div class="card-total-line">
                        <div class="card-line">
                            <div class="card-label card-total-label">NET PAY</div>
                            <div class="card-value card-total-label">PHP {{ number_format($summaryTotals['total_net_pay'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="final-section">
        <h3>FINAL ACCOUNTING TOTALS</h3>
        <table class="final-totals-table">
            <tr>
                <td class="final-card">
                    <strong>Taxable Income</strong><br>
                    PHP {{ number_format($summaryTotals['total_taxable_income'], 2) }}
                </td>
                <td class="final-card">
                    <strong>Net Payroll</strong><br>
                    PHP {{ number_format($summaryTotals['total_net_pay'], 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div style="page-break-before: always;"></div>

    <div class="section-title">HR PERFORMANCE METRICS</div>

    <div class="metrics">
        <div class="metrics-grid">
            <div class="metrics-col">
                <h4>PERFORMANCE METRICS</h4>
                <div class="metric-item">
                    <div class="metric-label">Average Salary per Employee</div>
                    <div class="metric-value">PHP {{ number_format($insights['average_salary_per_employee'], 0) }}</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Overtime Rate</div>
                    <div class="metric-value">
                        {{ number_format($insights['overtime_percentage'], 1) }}%
                        @if($insights['overtime_percentage'] < 15)
                            <span class="status good">Optimal</span>
                        @else
                            <span class="status warn">High</span>
                        @endif
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Take-home Rate</div>
                    <div class="metric-value">
                        {{ number_format($insights['take_home_rate'], 1) }}%
                        @if($insights['take_home_rate'] >= 65)
                            <span class="status good">Good</span>
                        @else
                            <span class="status warn">Low</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="metrics-col">
                <h4>COST ANALYSIS</h4>
                <div class="metric-item">
                    <div class="metric-label">Total Labor Cost</div>
                    <div class="metric-value">PHP {{ number_format($insights['total_labor_cost'], 0) }}</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Government Contributions</div>
                    <div class="metric-value">PHP {{ number_format($insights['government_contributions_total'], 0) }} ({{ number_format($insights['government_contribution_rate'], 1) }}%)</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Average Hours per Employee</div>
                    <div class="metric-value">{{ number_format($insights['average_hours_per_employee'], 1) }} hrs</div>
                </div>
            </div>
        </div>
    </div>

    <div style="page-break-before: always;"></div>

    <div class="section-title">COMPREHENSIVE PAYROLL SUMMARY BY CATEGORY</div>

    @php
        $totalAllowancesByType = [];
        $totalLoansByType = [];
        $totalDemininisByType = [];
        $totalEarningsByType = [];

        foreach($payrolls as $p) {
            $allowances = is_string($p->allowance) ? json_decode($p->allowance, true) : ($p->allowance ?? []);
            foreach($allowances as $a) {
                $type = $a['type'] ?? 'Unknown';
                $totalAllowancesByType[$type] = ($totalAllowancesByType[$type] ?? 0) + ($a['amount'] ?? 0);
            }

            $loans = is_string($p->loan_deductions) ? json_decode($p->loan_deductions, true) : ($p->loan_deductions ?? []);
            foreach($loans as $l) {
                $type = $l['deduction_type'] ?? $l['type'] ?? 'Unknown';
                $totalLoansByType[$type] = ($totalLoansByType[$type] ?? 0) + ($l['amount'] ?? 0);
            }

            $deminimis = is_string($p->deminimis) ? json_decode($p->deminimis, true) : ($p->deminimis ?? []);
            foreach($deminimis as $d) {
                $type = $d['benefit_name'] ?? 'Unknown';
                $totalDemininisByType[$type] = ($totalDemininisByType[$type] ?? 0) + ($d['amount'] ?? 0);
            }

            $earnings = is_string($p->earnings) ? json_decode($p->earnings, true) : ($p->earnings ?? []);
            foreach($earnings as $e) {
                $type = $e['earning_type_name'] ?? 'Unknown';
                $totalEarningsByType[$type] = ($totalEarningsByType[$type] ?? 0) + ($e['applied_amount'] ?? 0);
            }
        }
    @endphp

    @if(!empty($totalEarningsByType) || !empty($totalAllowancesByType) || !empty($totalDemininisByType) || !empty($totalLoansByType))
    <div class="category-section">
        <h3>COMPREHENSIVE PAYROLL SUMMARY BY CATEGORY</h3>
        <table class="category-grid">
            @if(!empty($totalEarningsByType))
            <td class="category-item">
                <h4>Total Additional Earnings</h4>
                @foreach($totalEarningsByType as $type => $amount)
                    <div class="category-row">
                        <div class="cat-label">{{ $type }}</div>
                        <div class="cat-value">PHP {{ number_format($amount, 2) }}</div>
                    </div>
                @endforeach
            </td>
            @endif

            @if(!empty($totalAllowancesByType))
            <td class="category-item">
                <h4>Total Allowances</h4>
                @foreach($totalAllowancesByType as $type => $amount)
                    <div class="category-row">
                        <div class="cat-label">{{ $type }}</div>
                        <div class="cat-value">PHP {{ number_format($amount, 2) }}</div>
                    </div>
                @endforeach
            </td>
            @endif

            @if(!empty($totalDemininisByType))
            <td class="category-item">
                <h4>Total De Minimis</h4>
                @foreach($totalDemininisByType as $type => $amount)
                    <div class="category-row">
                        <div class="cat-label">{{ $type }}</div>
                        <div class="cat-value">PHP {{ number_format($amount, 2) }}</div>
                    </div>
                @endforeach
            </td>
            @endif

            @if(!empty($totalLoansByType))
            <td class="category-item">
                <h4>Total Loan Deductions</h4>
                @foreach($totalLoansByType as $type => $amount)
                    <div class="category-row">
                        <div class="cat-label">{{ $type }}</div>
                        <div class="cat-value">PHP {{ number_format($amount, 2) }}</div>
                    </div>
                @endforeach
            </td>
            @endif
        </table>
    </div>
    @endif

    <!-- === ENHANCED EMPLOYEE DETAIL SECTION === -->
    @foreach($payrolls as $payroll)
        @php
            $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : ($payroll->earnings ?? []);
            $deminimis = is_string($payroll->deminimis) ? json_decode($payroll->deminimis, true) : ($payroll->deminimis ?? []);
            $allowances = is_string($payroll->allowance) ? json_decode($payroll->allowance, true) : ($payroll->allowance ?? []);
            $loanDeductions = is_string($payroll->loan_deductions) ? json_decode($payroll->loan_deductions, true) : ($payroll->loan_deductions ?? []);
            $hasData = !empty($earnings) || !empty($deminimis) || !empty($allowances) || !empty($loanDeductions);
        @endphp

        @if($hasData)
        <div class="employee-detail">
            <h3>
                {{ $payroll->user->personalInformation->last_name ?? '' }},
                {{ $payroll->user->personalInformation->first_name ?? '' }}
                ({{ $payroll->user->employmentDetail->employee_id ?? 'N/A' }})
            </h3>

            <table class="detail-table">
                <tbody>
                    <!-- Additional Earnings -->
                    @if(!empty($earnings))
                    <tr class="section-header-row">
                        <td colspan="7">Additional Earnings</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Default</th>
                        <th>Override</th>
                        <th>Applied</th>
                        <th>Tax</th>
                        <th>Frequency</th>
                    </tr>
                    @foreach($earnings as $e)
                    <tr>
                        <td>{{ $e['earning_type_name'] ?? '—' }}</td>
                        <td>{{ ucfirst($e['calculation_method'] ?? '—') }}</td>
                        <td class="text-right">PHP {{ number_format($e['default_amount'] ?? 0, 2) }}</td>
                        <td class="text-right">PHP {{ number_format($e['user_amount_override'] ?? 0, 2) }}</td>
                        <td class="text-right earn">PHP {{ number_format($e['applied_amount'] ?? 0, 2) }}</td>
                        <td class="text-center">
                            <span class="tax-badge">{{ ($e['is_taxable'] ?? 1) ? 'TAX' : 'NON' }}</span>
                        </td>
                        <td>{{ ucfirst($e['frequency'] ?? '—') }}</td>
                    </tr>
                    @endforeach
                    @endif

                    <!-- De Minimis -->
                    @if(!empty($deminimis))
                    <tr class="section-divider"><td colspan="7"></td></tr>
                    <tr class="section-header-row">
                        <td colspan="7">De Minimis Benefits</td>
                    </tr>
                    <tr>
                        <th>Benefit</th>
                        <th class="text-right">Amount</th>
                        <th colspan="5">Description</th>
                    </tr>
                    @foreach($deminimis as $d)
                    <tr>
                        <td>{{ $d['benefit_name'] ?? '—' }}</td>
                        <td class="text-right net">PHP {{ number_format($d['amount'] ?? 0, 2) }}</td>
                        <td colspan="5">{{ $d['description'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                    @endif

                    <!-- Allowances -->
                    @if(!empty($allowances))
                    <tr class="section-divider"><td colspan="7"></td></tr>
                    <tr class="section-header-row">
                        <td colspan="7">Allowances</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <th class="text-right">Amount</th>
                        <th colspan="5"></th>
                    </tr>
                    @foreach($allowances as $a)
                    <tr>
                        <td>{{ $a['type'] ?? '—' }}</td>
                        <td class="text-right earn">PHP {{ number_format($a['amount'] ?? 0, 2) }}</td>
                        <td colspan="5"></td>
                    </tr>
                    @endforeach
                    @endif

                    <!-- Loan Deductions -->
                    @if(!empty($loanDeductions))
                    <tr class="section-divider"><td colspan="7"></td></tr>
                    <tr class="section-header-row">
                        <td colspan="7">Loan Deductions</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <th class="text-right">Amount</th>
                        <th colspan="5"></th>
                    </tr>
                    @foreach($loanDeductions as $l)
                    <tr>
                        <td>{{ $l['deduction_type'] ?? $l['type'] ?? '—' }}</td>
                        <td class="text-right ded">PHP {{ number_format($l['amount'] ?? 0, 2) }}</td>
                        <td colspan="5"></td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        @endif
    @endforeach

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>&copy; {{ date('Y') }} Timora By JAF Digital Group Inc. All rights reserved.</p>
    </div>
</body>
</html>