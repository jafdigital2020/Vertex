 @foreach ($payrolls as $payroll)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input payroll-checkbox" type="checkbox"
                    value="{{ $payroll->id }}">
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <a href="#" class="avatar avatar-md" data-bs-toggle="modal"
                    data-bs-target="#view_details"><img
                        src="{{ asset('storage/' . ($payroll->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                        class="img-fluid rounded-circle" alt="img"></a>
                <div class="ms-2">
                    <p class="text-dark mb-0"><a href="#"
                            data-bs-toggle="modal" data-bs-target="#view_details">
                            {{ $payroll->user->personalInformation->last_name ?? '' }}
                            {{ $payroll->user->personalInformation->suffix ?? '' }},
                            {{ $payroll->user->personalInformation->first_name ?? '' }}
                            {{ $payroll->user->personalInformation->middle_name ?? '' }}</a>
                    </p>
                    <span class="fs-12"></span>
                </div>
            </div>
        </td>
        <td>{{ $payroll->user->employmentDetail->branch->name ?? '' }}</td>
        <td>₱{{ number_format($payroll->total_deductions, 2) }}</td>
        <td>₱{{ number_format($payroll->total_earnings, 2) }}</td>
        <td class="text-danger">₱{{ number_format($payroll->net_salary, 2) }}</td>
        <td>
                @if (in_array('Update', $permission) || in_array('Delete', $permission) )
            <div class="action-icon d-inline-flex">
                @if(in_array('Update', $permission))
                <a href="#" class="me-2 edit-payroll-btn"
                    data-bs-toggle="modal" data-bs-target="#edit_payroll"
                    data-id="{{ $payroll->id }}"
                    data-payroll-type="{{ $payroll->payroll_type }}"
                    data-payroll-period="{{ $payroll->payroll_period }}"
                    data-payroll-period-start="{{ $payroll->payroll_period_start }}"
                    data-payroll-period-end="{{ $payroll->payroll_period_end }}"
                    data-total-worked-minutes="{{ $payroll->total_worked_minutes }}"
                    data-total-late-minutes="{{ $payroll->total_late_minutes }}"
                    data-total-undertime-minutes="{{ $payroll->total_undertime_minutes }}"
                    data-total-overtime-minutes="{{ $payroll->total_overtime_minutes }}"
                    data-total-night-differential-minutes="{{ $payroll->total_night_differential_minutes }}"
                    data-total-overtime-night-diff-minutes="{{ $payroll->total_overtime_night_diff_minutes }}"
                    data-total-worked-days="{{ $payroll->total_worked_days }}"
                    data-total-absent-days="{{ $payroll->total_absent_days }}"
                    data-holiday-pay="{{ $payroll->holiday_pay }}"
                    data-leave-pay="{{ $payroll->leave_pay }}"
                    data-overtime-pay="{{ $payroll->overtime_pay }}"
                    data-night-differential-pay="{{ $payroll->night_differential_pay }}"
                    data-overtime-night-diff-pay="{{ $payroll->overtime_night_diff_pay }}"
                    data-late-deduction="{{ $payroll->late_deduction }}"
                    data-overtime-restday-pay="{{ $payroll->overtime_restday_pay }}"
                    data-undertime-deduction="{{ $payroll->undertime_deduction }}"
                    data-absent-deduction="{{ $payroll->absent_deduction }}"
                    data-earnings="{{ $payroll->earnings }}"
                    data-total-earnings="{{ $payroll->total_earnings }}"
                    data-allowance="{{ $payroll->allowance }}"
                    data-taxable-income="{{ $payroll->taxable_income }}"
                    data-deminimis="{{ $payroll->deminimis }}"
                    data-sss-contribution="{{ $payroll->sss_contribution }}"
                    data-philhealth-contribution="{{ $payroll->philhealth_contribution }}"
                    data-pagibig-contribution="{{ $payroll->pagibig_contribution }}"
                    data-withholding-tax="{{ $payroll->withholding_tax }}"
                    data-loan-deductions="{{ htmlspecialchars(json_encode($payroll->loan_deductions), ENT_QUOTES, 'UTF-8') }}"
                    data-deductions="{{ $payroll->deductions }}"
                    data-total-deductions="{{ $payroll->total_deductions }}"
                    data-basic-pay="{{ $payroll->basic_pay }}"
                    data-gross-pay="{{ $payroll->gross_pay }}"
                    data-net-salary="{{ $payroll->net_salary }}"
                    data-payment-date="{{ $payroll->payment_date }}"
                    data-status="{{ $payroll->status }}"
                    data-remarks="{{ $payroll->remarks }}"
                    data-processed-by="{{ $payroll->processor_name }}"
                    data-work-formatted="{{ $payroll->total_worked_minutes_formatted }}"
                    title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if(in_array('Delete', $permission))
                <a href="javascript:void(0);" class="btn-delete"
                    data-bs-toggle="modal" data-bs-target="#delete_payroll"
                    data-id="{{ $payroll->id }}"
                    data-name="{{ $payroll->user->personalInformation->full_name }}"
                    title="Delete"><i class="ti ti-trash"></i></a>
                @endif
            </div>
            @endif
        </td>
    </tr>
@endforeach