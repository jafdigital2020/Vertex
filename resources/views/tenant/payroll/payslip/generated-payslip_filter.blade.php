    @foreach ($payslips as $payslip)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input payroll-checkbox" type="checkbox">
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <a href="#" class="avatar avatar-md" data-bs-toggle="modal"
                    data-bs-target="#view_details"><img
                        src="{{ asset('storage/' . ($payslip->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                        class="img-fluid rounded-circle" alt="img"></a>
                <div class="ms-2">
                    <p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
                            data-bs-target="#view_details">
                            {{ $payslip->user->personalInformation->last_name ?? '' }}
                            {{ $payslip->user->personalInformation->suffix ?? '' }},
                            {{ $payslip->user->personalInformation->first_name ?? '' }}
                            {{ $payslip->user->personalInformation->middle_name ?? '' }}</a>
                    </p>
                    <span
                        class="fs-12">{{ $payslip->user->employmentDetail->department->department_name }}</span>
                </div>
            </div>
        </td>
        <td class="text-center">{{ $payslip->user->employmentDetail->branch->name ?? '' }}</td>
        <td class="text-center">
            @if ($payslip->payroll_period_start && $payslip->payroll_period_end)
                {{ $payslip->payroll_period_start }} - {{ $payslip->payroll_period_end }}
            @else
                N/A
            @endif
        </td>
        <td class="text-center">₱{{ number_format($payslip->total_earnings, 2) }}</td>
        <td class="text-center">₱{{ number_format($payslip->total_deductions, 2) }}</td>
        <td class="text-danger text-center">₱{{ number_format($payslip->net_salary, 2) }}</td>
        <td class="text-center">{{ $payslip->processor_name }}</td>
        <td class="text-center">{{ $payslip->payment_date }}</td>
        <td class="text-center">
            @if ($payslip->status === 'Paid')
                <span
                    class="badge d-inline-flex align-items-center badge-xs badge-success">
                    <i class="ti ti-point-filled me-1"></i>{{ $payslip->status ?? 'N/A' }}
                </span>
            @else
                <span class="badge d-inline-flex align-items-center badge-xs badge-danger">
                    <i class="ti ti-point-filled me-1"></i>{{ $payslip->status ?? 'N/A' }}
                </span>
            @endif
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                <a href="{{ route('generatedPayslips', $payslip->id) }}"
                    class="me-2 edit-payroll-btn" title="View Payslip">
                    <i class="ti ti-eye"></i>
                </a>
                @if(in_array('Update',$permission))
                <a href="#" class="me-2 edit-payroll-btn" data-bs-toggle="modal"
                    data-bs-target="#edit_payroll" title="Edit/Rollback"><i
                        class="ti ti-repeat"></i></a>
                @endif
                @if(in_array('Delete',$permission))
                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                    data-bs-target="#delete_payroll" title="Delete"><i
                        class="ti ti-trash"></i></a>
                @endif
            </div>
        </td>
    </tr>
    @endforeach
