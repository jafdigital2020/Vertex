{{-- Deductions Partial --}}
@if ($payslips->sss_contribution != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">SSS
        Contribution <span>{{ number_format($payslips->sss_contribution, 2) }}</span>
    </li>
@endif
@if ($payslips->philhealth_contribution != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        PhilHealth Contribution
        <span>{{ number_format($payslips->philhealth_contribution, 2) }}</span>
    </li>
@endif
@if ($payslips->pagibig_contribution != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        Pag-IBIG Contribution
        <span>{{ number_format($payslips->pagibig_contribution, 2) }}</span>
    </li>
@endif
@if ($payslips->withholding_tax != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        Withholding Tax <span>{{ number_format($payslips->withholding_tax, 2) }}</span>
    </li>
@endif
@if ($payslips->late_deduction != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">Late
        Deduction <span>{{ number_format($payslips->late_deduction, 2) }}</span></li>
@endif
@if ($payslips->undertime_deduction != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        Undertime Deduction
        <span>{{ number_format($payslips->undertime_deduction, 2) }}</span>
    </li>
@endif
@if ($payslips->absent_deduction != 0)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        Absent
        Deduction <span>{{ number_format($payslips->absent_deduction, 2) }}</span></li>
@endif
{{-- Loans --}}
@if (!empty($payslips->loan_deductions))
    @foreach (json_decode($payslips->loan_deductions, true) as $item)
        @if (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $item['label'] }} (Loan)
                <span>{{ number_format($item['amount'], 2) }}</span>
            </li>
        @endif
    @endforeach
@endif

{{-- Other Deductions --}}
@if (!empty($payslips->deductions))
    @foreach (json_decode($payslips->deductions, true) as $item)
        @if (
                (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) ||
                (isset($item['deduction_type_name']) && isset($item['applied_amount']) && $item['applied_amount'] != 0)
            )
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $item['label'] ?? $item['deduction_type_name'] }}
                <span>{{ number_format($item['amount'] ?? $item['applied_amount'], 2) }}</span>
            </li>
        @endif
    @endforeach
@endif