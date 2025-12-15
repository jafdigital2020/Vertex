{{-- Template 1: Current Modern Design --}}
<div class="container-fluid py-4 printable-area" style="max-width: 1000px;">
    <div class="card border-0 shadow-lg rounded-4 p-4 mb-4" style="background: #fff;">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div class="d-flex align-items-center">
                @if (
                        $payslips->user &&
                        $payslips->user->employmentDetail &&
                        $payslips->user->employmentDetail->branch &&
                        $payslips->user->employmentDetail->branch->branch_logo
                    )
                    <img src="{{ asset('storage/' . $payslips->user->employmentDetail->branch->branch_logo) }}" alt="Logo"
                        class="me-3" style="max-height: 70px;">
                @else
                    <img src="{{ URL::asset('build/img/Timora-logo.png') }}" alt="Logo" class="me-3"
                        style="max-height: 70px;" width="80">
                @endif
                <div>
                    <h4 class="mb-0">
                        {{ $payslips->user->employmentDetail->branch->name ?? 'Company Name' }}
                    </h4>
                    <div class="text-muted small"><i class="ti ti-map-pin"></i>
                        {{ $payslips->user->employmentDetail->branch->location ?? '' }}</div>
                    <div class="text-muted small"><i class="ti ti-phone"></i>
                        {{ $payslips->user->employmentDetail->branch->contact_number ?? '' }}</div>
                </div>
            </div>
            <div class="text-end">
                <div class="text-muted small mb-1">Payslip #: <span
                        class="fw-bold text-primary">#PS{{ $payslips->id }}</span></div>
                <div class="text-muted small mb-1">Period:
                    <strong>{{ \Carbon\Carbon::parse($payslips->payroll_period_start)->format('M d, Y') }} -
                        {{ \Carbon\Carbon::parse($payslips->payroll_period_end)->format('M d, Y') }}</strong>
                </div>
                <span
                    class="badge bg-{{ $payslips->status == 'Paid' ? 'success' : 'secondary' }} px-3 py-2 fs-6">{{ $payslips->status }}</span>
            </div>
        </div>

        <!-- Employee & Payroll Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Employee:</td>
                        <td class="fw-semibold">{{ $payslips->user->personalInformation->full_name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Position:</td>
                        <td>{{ $payslips->user->employmentDetail->designation->designation_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Department:</td>
                        <td>{{ $payslips->user->employmentDetail->department->department_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payroll Type:</td>
                        <td>
                            @if ($payslips->payroll_type == 'normal_payroll')
                                Normal Payroll
                            @elseif ($payslips->payroll_type == 'bulk_attendance_payroll')
                                Normal Payroll
                            @else
                                {{ ucfirst($payslips->payroll_type) }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td>{{ $payslips->user->email ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Date:</td>
                        <td>{{ \Carbon\Carbon::parse($payslips->payment_date)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Remarks:</td>
                        <td>{{ $payslips->remarks ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Earnings & Deductions -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 rounded-3 shadow-sm h-100">
                    <div class="card-header bg-light border-bottom-0 rounded-top-3 py-2 px-3">
                        <span class="fw-bold fs-15 text-primary">Earnings</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @include('tenant.payroll.payslip.userpayslip.templates.partials.earnings')
                        <li
                            class="list-group-item d-flex justify-content-between align-items-center bg-light border-top mt-2">
                            <span class="fw-bold">Total Earnings</span>
                            <span class="fw-bold">{{ number_format($payslips->total_earnings, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 rounded-3 shadow-sm h-100">
                    <div class="card-header bg-light border-bottom-0 rounded-top-3 py-2 px-3">
                        <span class="fw-bold fs-15 text-danger">Deductions</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @include('tenant.payroll.payslip.userpayslip.templates.partials.deductions')
                        <li
                            class="list-group-item d-flex justify-content-between align-items-center bg-light border-top mt-2">
                            <span class="fw-bold">Total Deductions</span>
                            <span class="fw-bold">{{ number_format($payslips->total_deductions, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Time Tracking -->
        @include('tenant.payroll.payslip.userpayslip.templates.partials.time-tracking')

        <!-- Summary -->
        <div class="row">
            <div class="col text-end">
                <div class="border-top pt-3">
                    <h6 class="fw-bold mb-1">Basic Pay: <span
                            class="text-dark">₱{{ number_format($payslips->basic_pay, 2) }}</span></h6>
                    <h6 class="fw-bold mb-1">Gross Pay: <span
                            class="text-dark">₱{{ number_format($payslips->gross_pay, 2) }}</span></h6>
                    <h4 class="fw-bold text-success mb-0">Net Salary:
                        <span>₱{{ number_format($payslips->net_salary, 2) }}</span>
                    </h4>
                </div>
            </div>
        </div>
    </div>
</div>