{{-- Template 2: Wecall Classic Design --}}
<div class="container-fluid py-4 printable-area" style="max-width: 900px;">
    <div class="card border shadow-sm p-0 mb-4" style="background: #fff;">
        <!-- Header with Logo and Company Info -->
        <div class="border-bottom p-3">
            <div class="text-center mb-3">
                @if (
                        $payslips->user &&
                        $payslips->user->employmentDetail &&
                        $payslips->user->employmentDetail->branch &&
                        $payslips->user->employmentDetail->branch->branch_logo
                    )
                    <img src="{{ asset('storage/' . $payslips->user->employmentDetail->branch->branch_logo) }}" alt="Logo"
                        class="mb-2" style="max-height: 60px;">
                @else
                    <img src="{{ URL::asset('build/img/Timora-logo.png') }}" alt="Logo" class="mb-2"
                        style="max-height: 60px;">
                @endif
                <h5 class="mb-0 fw-bold">{{ $payslips->user->employmentDetail->branch->name ?? 'Company Name' }}</h5>
                <div class="small text-muted">{{ $payslips->user->employmentDetail->branch->location ?? '' }}</div>
            </div>
            <h4 class="text-center fw-bold mb-0">PAYSLIP</h4>
        </div>

        <!-- Employee Info Header -->
        <div class="p-3">
            <table class="table table-sm mb-3" style="border: none;">
                <tr>
                    <td style="width: 50%; border: none; padding: 2px 8px;">
                        <div class="d-flex">
                            <span style="min-width: 180px;">Employee Name:</span>
                            <span>{{ $payslips->user->personalInformation->full_name ?? '' }}</span>
                        </div>
                    </td>
                    <td style="width: 50%; border: none; padding: 2px 8px;" class="text-end">
                        <span>{{ number_format($payslips->basic_pay, 0) }} Basic Monthly</span>
                        <span class="fw-bold ms-3">-</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 8px;">
                        <div class="d-flex">
                            <span style="min-width: 180px;">Employee ID:</span>
                            <span>{{ $payslips->user->employmentDetail->employee_id ?? '0' }}</span>
                        </div>
                    </td>
                    <td style="border: none; padding: 2px 8px;" class="text-end">
                        <span>Daily Rate:</span>
                        <span class="fw-bold ms-3">-</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 8px;">
                        <div class="d-flex">
                            <span style="min-width: 180px;">Pay Period Begin Date:</span>
                            <span>{{ \Carbon\Carbon::parse($payslips->pay_period_start)->format('F j, Y') }}</span>
                        </div>
                    </td>
                    <td style="border: none; padding: 2px 8px;" class="text-end">
                        <span>Hourly Rate:</span>
                        <span class="fw-bold ms-3">-</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 8px;">
                        <div class="d-flex">
                            <span style="min-width: 180px;">Pay Period End Date:</span>
                            <span>{{ \Carbon\Carbon::parse($payslips->pay_period_end)->format('F j, Y') }}</span>
                        </div>
                    </td>
                    <td style="border: none; padding: 2px 8px;" class="text-end">
                        <span>Total Work Hours:</span>
                        <span class="fw-bold ms-3">-</span>
                    </td>
                </tr>
            </table>

            <!-- Earnings and Deductions Table -->
            <table class="table table-bordered table-sm mb-3" style="border: 1px solid #dee2e6; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <th style="width: 50%; border-right: 1px solid #dee2e6;">
                            <div class="row g-0">
                                <div class="col-5 px-2">Earnings</div>
                                <div class="col-2 px-1 text-center" style="border-left: 1px solid #dee2e6;">Rate</div>
                                <div class="col-2 px-1 text-center" style="border-left: 1px solid #dee2e6;">Hours</div>
                                <div class="col-3 px-1 text-end" style="border-left: 1px solid #dee2e6;">Amount</div>
                            </div>
                        </th>
                        <th style="width: 50%;">Deductions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- REGULAR Section -->
                    <tr>
                        <td style="border-right: 2px solid #000; padding: 0;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="4" class="fw-bold px-2 py-1" style="border: none;"><i>REGULAR</i></td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="width: 41.67%; border: none;">REGULAR HOUR</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">1.00</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">-</td>
                                    <td class="text-center px-1" style="width: 25%; border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">REGULAR OT</td>
                                    <td class="text-center" style="border: none;">1.25</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">ND RATE</td>
                                    <td class="text-center" style="border: none;">1.10</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">ND OT RATE</td>
                                    <td class="text-center" style="border: none;">1.38</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0; vertical-align: top;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="2" class="fw-bold px-2 py-1" style="border: none;"><i>MANDATORY
                                            CONTRIBUTION</i></td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="width: 50%; border: none;">SSS</td>
                                    <td class="text-center px-2" style="width: 50%; border: none;">
                                        @if($payslips->sss_contribution != 0)
                                            {{ number_format($payslips->sss_contribution, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">Philhealth</td>
                                    <td class="text-center px-2" style="border: none;">
                                        @if($payslips->philhealth_contribution != 0)
                                            {{ number_format($payslips->philhealth_contribution, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">Pagibig</td>
                                    <td class="text-center px-2" style="border: none;">
                                        @if($payslips->pagibig_contribution != 0)
                                            {{ number_format($payslips->pagibig_contribution, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- DUTY ON REST DAY Section -->
                    <tr>
                        <td style="border-right: 1px solid #dee2e6; padding: 0;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="4" class="fw-bold px-2 py-1" style="border: none;"><i>DUTY ON REST
                                            DAY</i></td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="width: 41.67%; border: none;">SPEC HOL/ RD</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">1.30</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">-</td>
                                    <td class="text-center px-1" style="width: 25%; border: none;">
                                        @if($payslips->restday_pay != 0)
                                            {{ number_format($payslips->restday_pay, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">SPEC HOL/ RD OT</td>
                                    <td class="text-center" style="border: none;">1.69</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">
                                        @if($payslips->overtime_restday_pay != 0)
                                            {{ number_format($payslips->overtime_restday_pay, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">SPEC HOL/RD ND</td>
                                    <td class="text-center" style="border: none;">1.43</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">SPEC HOL/RD ND OT</td>
                                    <td class="text-center" style="border: none;">2.42</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0; vertical-align: top;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="2" class="fw-bold px-2 py-1" style="border: none;"><i>LOANS</i></td>
                                </tr>
                                @php
                                    $hasLoans = false;
                                    $loanItems = [];

                                    if (!empty($payslips->loan_deductions)) {
                                        $loanDeductions = json_decode($payslips->loan_deductions, true);
                                        if (is_array($loanDeductions)) {
                                            foreach ($loanDeductions as $item) {
                                                if (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) {
                                                    $hasLoans = true;
                                                    $loanItems[] = $item;
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                @if($hasLoans)
                                    @foreach($loanItems as $loan)
                                        <tr>
                                            <td class="px-2" style="width: 50%; border: none;">{{ $loan['label'] }}</td>
                                            <td class="text-end px-2" style="width: 50%; border: none;">
                                                {{ number_format($loan['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="px-2" style="width: 50%; border: none;">SSS</td>
                                        <td class="text-center px-2" style="width: 50%; border: none;">-</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2" style="border: none;">Pagibig</td>
                                        <td class="text-center px-2" style="border: none;">-</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2" style="border: none;">Cash Advance</td>
                                        <td class="text-center px-2" style="border: none;">-</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <!-- SPECIAL HOLIDAY Section -->
                    <tr>
                        <td style="border-right: 1px solid #dee2e6; padding: 0;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="4" class="fw-bold px-2 py-1" style="border: none;"><i>SPECIAL HOLIDAY/
                                            DUTY</i></td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="width: 41.67%; border: none;">SPEC HOL/ RD</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">1.30</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">-</td>
                                    <td class="text-center px-1" style="width: 25%; border: none;">
                                        @if($payslips->holiday_pay != 0)
                                            {{ number_format($payslips->holiday_pay, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0; vertical-align: top;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="2" class="fw-bold px-2 py-1" style="border: none;"><i>OTHERS</i></td>
                                </tr>
                                @php
                                    $hasOtherDeductions = false;
                                    $otherDeductionItems = [];

                                    if (!empty($payslips->deductions)) {
                                        $deductions = json_decode($payslips->deductions, true);
                                        if (is_array($deductions)) {
                                            foreach ($deductions as $item) {
                                                if (
                                                    (isset($item['label']) && isset($item['amount']) && $item['amount'] != 0) ||
                                                    (isset($item['deduction_type_name']) && isset($item['applied_amount']) && $item['applied_amount'] != 0)
                                                ) {
                                                    $hasOtherDeductions = true;
                                                    $otherDeductionItems[] = $item;
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                @if(!empty($otherDeductionItems))
                                    @foreach($otherDeductionItems as $deduction)
                                        <tr>
                                            <td class="px-2" style="width: 50%; border: none;">
                                                {{ $deduction['label'] ?? $deduction['deduction_type_name'] }}</td>
                                            <td class="text-center px-2" style="width: 50%; border: none;">
                                                @if($deduction['label'] ?? $deduction['deduction_type_name'] == 'Pantry')
                                                    -
                                                @else
                                                    {{ number_format($deduction['amount'] ?? $deduction['applied_amount'], 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="px-2" style="width: 50%; border: none;">Pantry</td>
                                        <td class="text-center px-2" style="width: 50%; border: none;">-</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="px-2" style="border: none;">Salary Adjustment (-)</td>
                                    <td class="text-center px-2" style="border: none;">
                                        @if($payslips->late_deduction != 0)
                                            {{ number_format($payslips->late_deduction, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">Withholding Tax</td>
                                    <td class="text-center px-2" style="border: none;">
                                        @if($payslips->withholding_tax != 0)
                                            {{ number_format($payslips->withholding_tax, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- REGULAR OR LEGAL HOLIDAY Section -->
                    <tr>
                        <td style="border-right: 1px solid #dee2e6; padding: 0;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="4" class="fw-bold px-2 py-1" style="border: none;"><i>REGULAR OR LEGAL
                                            HOLIDAY</i></td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="width: 41.67%; border: none;">LEG HOL<span
                                            style="border: 1px solid #000; display: inline-block; width: 20px; height: 15px; margin-left: 5px;"></span>
                                    </td>
                                    <td class="text-center" style="width: 16.67%; border: none;">2.00</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">-</td>
                                    <td class="text-center px-1" style="width: 25%; border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">LEG HOL OT</td>
                                    <td class="text-center" style="border: none;">2.50</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">LEG HOL ND</td>
                                    <td class="text-center" style="border: none;">2.75</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">LEG HOL/REST ND OT</td>
                                    <td class="text-center" style="border: none;">3.44</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0;"></td>
                    </tr>

                    <!-- SPECIAL HOLIDAY FALLING Section -->
                    <tr>
                        <td style="border-right: 1px solid #dee2e6; padding: 0;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="4" class="fw-bold px-2 py-1" style="border: none;"><i>SPECIAL HOLIDAY
                                            FALLING ON A REST DAY</i></td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="width: 41.67%; border: none;">SPEC HOL ON RD</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">1.69</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">-</td>
                                    <td class="text-center px-1" style="width: 25%; border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">SPEC HOL ON RD OT</td>
                                    <td class="text-center" style="border: none;">2.20</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">SPEC HOL ON RD ND</td>
                                    <td class="text-center" style="border: none;">2.42</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">SPEC HOL ON RD ND OT</td>
                                    <td class="text-center" style="border: none;">3.14</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0;"></td>
                    </tr>

                    <!-- REGULAR HOLIDAY FALLING Section -->
                    <tr>
                        <td style="border-right: 1px solid #dee2e6; padding: 0;">
                            <table class="w-100 mb-0" style="border: none;">
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="4" class="fw-bold px-2 py-1" style="border: none;"><i>REGULAR HOLIDAY
                                            FALLING ON A REST DAY</i></td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="width: 41.67%; border: none;">LEG HOL ON RD</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">2.60</td>
                                    <td class="text-center" style="width: 16.67%; border: none;">-</td>
                                    <td class="text-center px-1" style="width: 25%; border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">LEG HOL ON RD OT</td>
                                    <td class="text-center" style="border: none;">3.38</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">LEG HOL ON RD ND</td>
                                    <td class="text-center" style="border: none;">3.72</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                                <tr>
                                    <td class="px-2" style="border: none;">LEG HOL ON RD ND OT</td>
                                    <td class="text-center" style="border: none;">3.72</td>
                                    <td class="text-center" style="border: none;">-</td>
                                    <td class="text-center px-1" style="border: none;">-</td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 0;"></td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary Section -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <table class="table table-bordered table-sm mb-0"
                        style="border: 1px solid #dee2e6; font-size: 0.85rem;">
                        <tbody>
                            <tr>
                                <td class="fw-bold" style="width: 60%;">Basic Salary</td>
                                <td class="text-end fw-bold" style="width: 40%;">
                                    @php
                                        $basicSalaryValue = $payslips->basic_pay ?? 0;
                                    @endphp
                                    @if($basicSalaryValue != 0)
                                        {{ number_format($basicSalaryValue, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @php
                                $bonusFields = [
                                    'Employee of the Month Bonus',
                                    'Complete Attendance Bonus',
                                    'Performance Bonus',
                                    'Hit Target SLA Bonus',
                                    'TL/QA Bonus',
                                    'Service Incentive Leave (SIL)',
                                    'Salary Adjustment (+)'
                                ];
                            @endphp
                            @foreach($bonusFields as $bonusField)
                                <tr>
                                    <td>{{ $bonusField }}</td>
                                    <td class="text-center">-</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="fw-bold">Total Gross Salary</td>
                                <td class="text-end fw-bold">
                                    @if($payslips->total_earnings != 0)
                                        {{ number_format($payslips->total_earnings, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total Net Salary</td>
                                <td class="text-end fw-bold">
                                    @if($payslips->net_salary != 0)
                                        {{ number_format($payslips->net_salary, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            @php
                                $employerShares = [
                                    'SSS' => $payslips->sss_contribution_employer ?? 0,
                                    'Philhelath' => $payslips->philhealth_contribution_employer ?? 0,
                                    'Pag-ibig' => $payslips->pagibig_contribution_employer ?? 0,
                                ];
                            @endphp

                            @foreach($employerShares as $shareName => $shareValue)
                                <tr>
                                    <td>Employer Share - {{ $shareName }}</td>
                                    <td class="text-center">
                                        @if($shareValue != 0)
                                            {{ number_format($shareValue, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered table-sm mb-0"
                        style="border: 1px solid #000; font-size: 0.85rem;">
                        <tbody>
                            <tr>
                                <td class="fw-bold" style="width: 60%;">Total Deductions</td>
                                <td class="text-end fw-bold" style="width: 40%;">
                                    @if($payslips->total_deductions != 0)
                                        {{ number_format($payslips->total_deductions, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            @php
                                $cashAdvanceAmount = 0;
                                if (!empty($payslips->loan_deductions)) {
                                    $loanDeductions = json_decode($payslips->loan_deductions, true);
                                    if (is_array($loanDeductions)) {
                                        foreach ($loanDeductions as $loan) {
                                            if (isset($loan['label']) && strtolower($loan['label']) == 'cash advance' && isset($loan['amount'])) {
                                                $cashAdvanceAmount = $loan['amount'];
                                                break;
                                            }
                                        }
                                    }
                                }
                            @endphp

                            <tr>
                                <td>Cash Advance</td>
                                <td class="text-center">
                                    @if($cashAdvanceAmount != 0)
                                        {{ number_format($cashAdvanceAmount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Total Receivable</td>
                                <td class="text-end fw-bold">
                                    @php
                                        $totalReceivable = $payslips->net_salary - $cashAdvanceAmount;
                                    @endphp
                                    @if($totalReceivable != 0)
                                        {{ number_format($totalReceivable, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="border-top mt-4 pt-3">
                <div class="row">
                    <div class="col-12">
                        <p class="small mb-2 fw-semibold">Employee Signature:</p>
                        <div style="height: 60px; border-bottom: 1px solid #000; position: relative;">
                            <span
                                style="position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%);">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>