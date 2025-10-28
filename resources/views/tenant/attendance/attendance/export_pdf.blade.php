<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Attendance Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7px;
            line-height: 1.1;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }
        .header h2 { font-size: 12px; margin-bottom: 3px; }
        .header p { font-size: 8px; color: #666; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px 2px;
            text-align: left;
            font-size: 6px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        td { vertical-align: middle; }

        .badge {
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6px;
            display: inline-block;
            text-align: center;
        }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }

        .summary {
            margin-top: 10px;
            background-color: #f9f9f9;
            padding: 5px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
        .summary h3 { font-size: 9px; margin-bottom: 3px; }
        .summary-item {
            margin: 2px 0;
            font-size: 7px;
            display: flex;
            justify-content: space-between;
        }
        .summary-item strong { min-width: 120px; }

        @page {
            margin: 10mm 8mm;
            size: A4 landscape;
        }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Attendance Report</h2>
        <p>Generated: {{ $exportDate }} | Records: {{ $totalRecords ?? $attendances->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 6%;">ID</th>
                <th style="width: 12%;">Employee</th>
                <th style="width: 8%;">Date</th>
                <th style="width: 8%;">Shift</th>
                <th style="width: 6%;">Status</th>
                <th style="width: 6%;">In</th>
                <th style="width: 6%;">Out</th>
                <th style="width: 5%;">Late</th>
                <th style="width: 7%;">Work</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $index => $att)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $att->user->employmentDetail->employee_id ?? '' }}</td>
                <td>
                    {{ $att->user->personalInformation->last_name ?? '' }},
                    {{ $att->user->personalInformation->first_name ?? '' }}
                </td>
                <td>{{ $att->attendance_date ? $att->attendance_date->format('M d, Y') : '' }}</td>
                <td>{{ $att->shift->name ?? '-' }}</td>
                <td style="text-align: center;">
                    @php
                        $badgeClass = $att->status === 'present' ? 'badge-success' :
                                    ($att->status === 'late' ? 'badge-warning' : 'badge-danger');
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($att->status) }}</span>
                </td>
                <td style="text-align: center;">{{ $att->time_only ?? '-' }}</td>
                <td style="text-align: center;">{{ $att->time_out_only ?? '-' }}</td>
                <td style="text-align: center;">{{ $att->total_late_minutes ?? 0 }}m</td>
                <td style="text-align: center;">{{ $att->total_work_minutes_formatted ?? '0' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary Totals</h3>
        <div class="summary-item">
            <strong>Total Records:</strong>
            <span>{{ $summaryTotals['total_records'] }}</span>
        </div>
        <div class="summary-item">
            <strong>Present:</strong>
            <span>{{ $summaryTotals['total_present'] }}</span>
        </div>
        <div class="summary-item">
            <strong>Late:</strong>
            <span>{{ $summaryTotals['total_late'] }}</span>
        </div>
        <div class="summary-item">
            <strong>Absent:</strong>
            <span>{{ $summaryTotals['total_absent'] }}</span>
        </div>
        <div class="summary-item">
            <strong>Total Work Hours:</strong>
            <span>{{ $summaryTotals['total_work_hours_formatted'] }}</span>
        </div>
        <div class="summary-item">
            <strong>Total Late:</strong>
            <span>{{ $summaryTotals['total_late_hours_formatted'] }}</span>
        </div>
        <div class="summary-item">
            <strong>Total Undertime:</strong>
            <span>{{ $summaryTotals['total_undertime_hours_formatted'] }}</span>
        </div>
    </div>
</body>
</html>
