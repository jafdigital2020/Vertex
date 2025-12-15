{{-- Time Tracking Partial --}}
@php
    if (!function_exists('formatMinutes')) {
        function formatMinutes($minutes)
        {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            if ($hours > 0) {
                return "{$hours} hr" .
                    ($hours > 1 ? 's' : '') .
                    " {$mins} min" .
                    ($mins != 1 ? 's' : '');
            }
            return "{$mins} min" . ($mins != 1 ? 's' : '');
        }
    }
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #fff;">
    <div class="card-header bg-light border-bottom-0 rounded-top-3 py-2 px-3">
        <span class="fw-bold fs-15 text-primary">Time Tracking</span>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @php
                $timeMetrics = [
                    ['label' => 'Days Worked', 'value' => $payslips->total_worked_days],
                    ['label' => 'Absent Days', 'value' => $payslips->total_absent_days],
                    [
                        'label' => 'Worked Time',
                        'value' => formatMinutes($payslips->total_worked_minutes),
                    ],
                    [
                        'label' => 'Late Time',
                        'value' => formatMinutes($payslips->total_late_minutes),
                    ],
                    [
                        'label' => 'Undertime',
                        'value' => formatMinutes($payslips->total_undertime_minutes),
                    ],
                    [
                        'label' => 'Overtime',
                        'value' => formatMinutes($payslips->total_overtime_minutes),
                    ],
                    [
                        'label' => 'Night Diff',
                        'value' => formatMinutes($payslips->total_night_differential_minutes),
                    ],
                    [
                        'label' => 'OT Night Diff',
                        'value' => formatMinutes($payslips->total_overtime_night_diff_minutes),
                    ],
                ];
            @endphp
            @foreach ($timeMetrics as $metric)
                <div class="col-6 col-md-3">
                    <div
                        class="bg-light border-0 rounded-3 py-3 px-2 h-100 d-flex flex-column align-items-center justify-content-center">
                        <div class="text-muted small mb-1">{{ $metric['label'] }}</div>
                        <div class="fw-semibold fs-13 text-dark">{{ $metric['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>