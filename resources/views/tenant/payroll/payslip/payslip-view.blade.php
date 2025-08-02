<?php $page = 'payslip'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payslip</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payslip</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="mb-2">
                        <a href="#" class="btn btn-dark d-flex align-items-center"><i
                                class="ti ti-download me-2"></i>Download</a>
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Payslip -->
            <div class="container-fluid py-4 printable-area" style="max-width: 1000px;">
                <div class="card border-0 shadow-lg rounded-4 p-4 mb-4" style="background: #fff;">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div class="d-flex align-items-center">
                            @if (
                                $payslips->user &&
                                    $payslips->user->employmentDetail &&
                                    $payslips->user->employmentDetail->branch &&
                                    $payslips->user->employmentDetail->branch->branch_logo)
                                <img src="{{ asset('storage/' . $payslips->user->employmentDetail->branch->branch_logo) }}"
                                    alt="Logo" class="me-3" style="max-height: 70px;">
                            @else
                                <img src="{{ URL::asset('build/img/Timora-logo.png') }}" alt="Logo" class="me-3"
                                    style="max-height: 70px;" width="80">
                            @endif
                            <div>
                                <h4 class="mb-0">
                                    {{ $payslips->user->employmentDetail->branch->name ?? 'Company Name' }}</h4>
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
                                            Head Office Payroll
                                        @elseif ($payslips->payroll_type == 'bulk_attendance_payroll')
                                            Security Guards Payroll
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

                                    {{-- De Minimis --}}
                                    @if (!empty($payslips->deminimis))
                                        @foreach (json_decode($payslips->deminimis, true) as $item)
                                            @if (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0)
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    {{ $item['label'] }} (De Minimis)
                                                    <span>{{ number_format($item['amount'], 2) }}</span>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
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
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center">
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

                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center bg-light border-top mt-2">
                                        <span class="fw-bold">Total Deductions</span>
                                        <span class="fw-bold">{{ number_format($payslips->total_deductions, 2) }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

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

            <!-- /Payslip -->


        </div>

        @include('layout.partials.footer-company')

    </div>
@endsection
