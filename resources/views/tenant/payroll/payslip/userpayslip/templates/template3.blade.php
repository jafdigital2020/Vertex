{{-- Template 3: Modern Minimal Design --}}
<div class="container-fluid py-4 printable-area" style="max-width: 900px;">
    <div class="card border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <!-- Header -->
        <div class="text-center mb-4">
            @if (
                    $payslips->user &&
                    $payslips->user->employmentDetail &&
                    $payslips->user->employmentDetail->branch &&
                    $payslips->user->employmentDetail->branch->branch_logo
                )
                <img src="{{ asset('storage/' . $payslips->user->employmentDetail->branch->branch_logo) }}"
                    alt="Logo" class="mb-3 bg-white p-2 rounded-circle" style="max-height: 80px;">
            @else
                <img src="{{ URL::asset('build/img/Timora-logo.png') }}" alt="Logo" class="mb-3 bg-white p-2 rounded-circle"
                    style="max-height: 80px;">
            @endif
            <h2 class="fw-bold mb-1">{{ $payslips->user->employmentDetail->branch->name ?? 'Company Name' }}</h2>
            <p class="mb-2 opacity-75">{{ $payslips->user->employmentDetail->branch->location ?? '' }}</p>
            <h3 class="fw-bold">PAYSLIP</h3>
            <div class="d-inline-block bg-white text-dark px-4 py-2 rounded-pill">
                <strong>{{ \Carbon\Carbon::parse($payslips->payroll_period_start)->format('M d') }} - 
                {{ \Carbon\Carbon::parse($payslips->payroll_period_end)->format('M d, Y') }}</strong>
            </div>
        </div>
    </div>

    <!-- Employee Info Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="ti ti-user text-primary fs-20"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Employee Name</div>
                        <div class="fw-bold fs-16">{{ $payslips->user->personalInformation->full_name ?? '' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="ti ti-id text-success fs-20"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Employee ID</div>
                        <div class="fw-bold fs-16">#{{ $payslips->user->id ?? '0' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="ti ti-briefcase text-warning fs-20"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Position</div>
                        <div class="fw-bold fs-16">{{ $payslips->user->employmentDetail->designation->designation_name ?? '' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="ti ti-building text-info fs-20"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Department</div>
                        <div class="fw-bold fs-16">{{ $payslips->user->employmentDetail->department->department_name ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Salary Highlight -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg rounded-4 p-4" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="text-white mb-2 fw-bold">Net Salary</h2>
                        <h1 class="text-white mb-0 display-4 fw-bold">₱{{ number_format($payslips->net_salary, 2) }}</h1>
                    </div>
                    <div class="col-md-4 text-md-end text-white">
                        <div class="mb-2">
                            <div class="small opacity-75">Gross Pay</div>
                            <div class="h4 mb-0">₱{{ number_format($payslips->gross_pay, 2) }}</div>
                        </div>
                        <div>
                            <div class="small opacity-75">Total Deductions</div>
                            <div class="h4 mb-0">₱{{ number_format($payslips->total_deductions, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings & Deductions Side by Side -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 rounded-top-4 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-trending-up text-white fs-20 me-2"></i>
                            <h5 class="mb-0 fw-bold text-white">Earnings</h5>
                        </div>
                        <span class="badge bg-white text-dark px-3 py-2 rounded-pill">
                            ₱{{ number_format($payslips->total_earnings, 2) }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @if ($payslips->holiday_pay != 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                <div>
                                    <i class="ti ti-calendar-event text-primary me-2"></i>
                                    <span>Holiday Pay</span>
                                </div>
                                <span class="fw-semibold">₱{{ number_format($payslips->holiday_pay, 2) }}</span>
                            </div>
                        @endif
                        @if ($payslips->overtime_pay != 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                <div>
                                    <i class="ti ti-clock text-primary me-2"></i>
                                    <span>Overtime Pay</span>
                                </div>
                                <span class="fw-semibold">₱{{ number_format($payslips->overtime_pay, 2) }}</span>
                            </div>
                        @endif
                        @if ($payslips->night_differential_pay != 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                <div>
                                    <i class="ti ti-moon text-primary me-2"></i>
                                    <span>Night Differential</span>
                                </div>
                                <span class="fw-semibold">₱{{ number_format($payslips->night_differential_pay, 2) }}</span>
                            </div>
                        @endif
                        
                        {{-- Dynamic Earnings --}}
                        @if (!empty($payslips->earnings))
                            @foreach (json_decode($payslips->earnings, true) as $item)
                                @if (
                                        (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) ||
                                        (isset($item['earning_type_name']) && isset($item['applied_amount']) && $item['applied_amount'] != 0)
                                    )
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                        <div>
                                            <i class="ti ti-coin text-primary me-2"></i>
                                            <span>{{ $item['label'] ?? $item['earning_type_name'] }}</span>
                                        </div>
                                        <span class="fw-semibold">₱{{ number_format($item['amount'] ?? $item['applied_amount'], 2) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                        
                        {{-- Allowances --}}
                        @if (!empty($payslips->allowance))
                            @foreach (is_array($payslips->allowance) ? $payslips->allowance : json_decode($payslips->allowance, true) as $item)
                                @if (
                                        (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) ||
                                        (isset($item['allowance_name']) && isset($item['applied_amount']) && $item['applied_amount'] != 0)
                                    )
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                        <div>
                                            <i class="ti ti-gift text-primary me-2"></i>
                                            <span>{{ $item['label'] ?? $item['allowance_name'] }}</span>
                                        </div>
                                        <span class="fw-semibold">₱{{ number_format($item['amount'] ?? $item['applied_amount'], 2) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 rounded-top-4 py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-trending-down text-white fs-20 me-2"></i>
                            <h5 class="mb-0 fw-bold text-white">Deductions</h5>
                        </div>
                        <span class="badge bg-white text-dark px-3 py-2 rounded-pill">
                            ₱{{ number_format($payslips->total_deductions, 2) }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @if ($payslips->sss_contribution != 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                <div>
                                    <i class="ti ti-shield text-danger me-2"></i>
                                    <span>SSS Contribution</span>
                                </div>
                                <span class="fw-semibold">₱{{ number_format($payslips->sss_contribution, 2) }}</span>
                            </div>
                        @endif
                        @if ($payslips->philhealth_contribution != 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                <div>
                                    <i class="ti ti-heart text-danger me-2"></i>
                                    <span>PhilHealth</span>
                                </div>
                                <span class="fw-semibold">₱{{ number_format($payslips->philhealth_contribution, 2) }}</span>
                            </div>
                        @endif
                        @if ($payslips->pagibig_contribution != 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                <div>
                                    <i class="ti ti-home text-danger me-2"></i>
                                    <span>Pag-IBIG</span>
                                </div>
                                <span class="fw-semibold">₱{{ number_format($payslips->pagibig_contribution, 2) }}</span>
                            </div>
                        @endif
                        @if ($payslips->withholding_tax != 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                <div>
                                    <i class="ti ti-receipt-tax text-danger me-2"></i>
                                    <span>Withholding Tax</span>
                                </div>
                                <span class="fw-semibold">₱{{ number_format($payslips->withholding_tax, 2) }}</span>
                            </div>
                        @endif
                        
                        {{-- Loans --}}
                        @if (!empty($payslips->loan_deductions))
                            @foreach (json_decode($payslips->loan_deductions, true) as $item)
                                @if (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0)
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                        <div>
                                            <i class="ti ti-currency-dollar text-danger me-2"></i>
                                            <span>{{ $item['label'] }}</span>
                                        </div>
                                        <span class="fw-semibold">₱{{ number_format($item['amount'], 2) }}</span>
                                    </div>
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
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                        <div>
                                            <i class="ti ti-minus-vertical text-danger me-2"></i>
                                            <span>{{ $item['label'] ?? $item['deduction_type_name'] }}</span>
                                        </div>
                                        <span class="fw-semibold">₱{{ number_format($item['amount'] ?? $item['applied_amount'], 2) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Tracking Stats -->
    @include('tenant.payroll.payslip.userpayslip.templates.partials.time-tracking')

    <!-- Footer Info -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted small mb-1"><strong>Payment Date:</strong> {{ \Carbon\Carbon::parse($payslips->payment_date)->format('F d, Y') }}</p>
                <p class="text-muted small mb-1"><strong>Status:</strong> <span class="badge bg-{{ $payslips->status == 'Paid' ? 'success' : 'secondary' }}">{{ $payslips->status }}</span></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted small mb-1"><strong>Payslip ID:</strong> #PS{{ $payslips->id }}</p>
                <p class="text-muted small mb-0"><strong>Generated:</strong> {{ now()->format('F d, Y g:i A') }}</p>
            </div>
        </div>
    </div>
</div>
