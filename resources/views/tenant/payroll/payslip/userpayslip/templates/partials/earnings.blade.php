{{-- Earnings Partial --}}
@if ($payslips->holiday_pay != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        Holiday Pay <span>{{ number_format($payslips->holiday_pay, 2) }}</span></li>
@endif
@if ($payslips->leave_pay != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">Leave
        Pay <span>{{ number_format($payslips->leave_pay, 2) }}</span></li>
@endif
@if ($payslips->restday_pay != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">Rest
        Day Pay <span>{{ number_format($payslips->restday_pay, 2) }}</span></li>
@endif
@if ($payslips->overtime_pay != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        Overtime Pay <span>{{ number_format($payslips->overtime_pay, 2) }}</span></li>
@endif
@if ($payslips->night_differential_pay != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">Night
        Diff Pay <span>{{ number_format($payslips->night_differential_pay, 2) }}</span>
    </li>
@endif
@if ($payslips->overtime_night_diff_pay != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">OT
        Night Diff Pay
        <span>{{ number_format($payslips->overtime_night_diff_pay, 2) }}</span>
    </li>
@endif
@if ($payslips->overtime_restday_pay != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">OT
        Rest Day Pay
        <span>{{ number_format($payslips->overtime_restday_pay, 2) }}</span>
    </li>
@endif
{{-- Dynamic Earnings --}}
@if (!empty($payslips->earnings))
    @foreach (json_decode($payslips->earnings, true) as $item)
        @if (
                (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) ||
                (isset($item['earning_type_name']) && isset($item['applied_amount']) && $item['applied_amount'] != 0)
            )
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $item['label'] ?? $item['earning_type_name'] }}
                <span>{{ number_format($item['amount'] ?? $item['applied_amount'], 2) }}</span>
            </li>
        @endif
    @endforeach
@endif

{{-- Dynamic Allowance --}}
@if (!empty($payslips->allowance))
    @foreach (is_array($payslips->allowance) ? $payslips->allowance : json_decode($payslips->allowance, true) as $item)
        @if (
                (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) ||
                (isset($item['allowance_name']) && isset($item['applied_amount']) && $item['applied_amount'] != 0)
            )
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $item['label'] ?? $item['allowance_name'] }}
                <span>{{ number_format($item['amount'] ?? $item['applied_amount'], 2) }}</span>
            </li>
        @endif
    @endforeach
@endif

{{-- De Minimis --}}
@if (!empty($payslips->deminimis))
    @foreach (is_array($payslips->deminimis) ? $payslips->deminimis : json_decode($payslips->deminimis, true) as $item)
        @if (
                (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) ||
                (isset($item['deminimis_type_name']) && isset($item['applied_amount']) && $item['applied_amount'] != 0)
            )
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $item['label'] ?? $item['deminimis_type_name'] }} (De Minimis)
                <span>{{ number_format($item['amount'] ?? $item['applied_amount'], 2) }}</span>
            </li>
        @endif
    @endforeach
@endif