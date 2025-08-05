<!-- Header Section -->
<div class="text-center mb-4">
    <h3 class="fw-bold">BIR FORM 1604CF</h3>
    <h5>AS OF DECEMBER {{ $year ?? date('Y') }}</h5>
    <div class="mt-3">
        <p><strong>TIN:</strong> {{ $companyTin ?? '' }}</p>
        <p><strong>BRANCH NAME:</strong> {{ $branchName ?? '' }}</p>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th rowspan="3" class="text-center align-middle border border-dark">SEQ NO</th>
            <th rowspan="3" class="text-center align-middle border border-dark">TAXPAYER IDENTIFICATION NUMBER</th>
            <th colspan="3" class="text-center border border-dark">NAME OF EMPLOYEES</th>
            <th colspan="2" class="text-center border border-dark">Inclusive Date of Employment</th>
            <th rowspan="3" class="text-center align-middle border border-dark">GROSS COMPENSATION INCOME</th>
            <th rowspan="3" class="text-center align-middle border border-dark">13th MONTH PAY & OTHER BENEFITS (≤
                90K)</th>
            <th rowspan="3" class="text-center align-middle border border-dark">DE MINIMIS BENEFITS</th>
            <th colspan="2" class="text-center border border-dark">NON-TAXABLE COMPENSATION</th>
            <th rowspan="3" class="text-center align-middle border border-dark">TOTAL NON-TAXABLE/EXEMPT COMPENSATION
                INCOME</th>
            <th colspan="3" class="text-center border border-dark">TAXABLE COMPENSATION</th>
            <th rowspan="3" class="text-center align-middle border border-dark">TAX WITHHELD</th>
        </tr>
        <tr>
            <th rowspan="2" class="text-center align-middle border border-dark">(Last Name)</th>
            <th rowspan="2" class="text-center align-middle border border-dark">(First Name)</th>
            <th rowspan="2" class="text-center align-middle border border-dark">(Middle Name)</th>
            <th rowspan="2" class="text-center align-middle border border-dark">From</th>
            <th rowspan="2" class="text-center align-middle border border-dark">To</th>
            <th rowspan="2" class="text-center align-middle border border-dark">SSS/GSIS/PHIC/PAG-IBIG/Union Dues
            </th>
            <th rowspan="2" class="text-center align-middle border border-dark">Other Non-Taxable Benefits</th>
            <th rowspan="2" class="text-center align-middle border border-dark">Basic Salary (Taxable Portion)</th>
            <th rowspan="2" class="text-center align-middle border border-dark">Other Taxable Benefits</th>
            <th rowspan="2" class="text-center align-middle border border-dark">TOTAL TAXABLE COMPENSATION INCOME
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $totals = [
                'total_earnings' => 0,
                'thirteenth_month_pay' => 0,
                'deminimis_total' => 0,
                'contributions_total' => 0,
                'other_non_taxable_total' => 0,
                'total_non_taxable' => 0,
                'basic_pay_total' => 0,
                'other_taxable_total' => 0,
                'total_taxable_compensation' => 0,
                'withholding_tax_total' => 0,
            ];
        @endphp
        @forelse ($payrollsGrouped as $userId => $group)
            @php
                $totalContributions =
                    $group['sss_contribution'] + $group['philhealth_contribution'] + $group['pagibig_contribution'];
                $otherNonTaxableBenefits = $group['earnings_breakdown']
                    ->where('is_taxable', 0)
                    ->sum('total_applied_amount');
                $totalNonTaxable =
                    $totalContributions +
                    $otherNonTaxableBenefits +
                    $group['deminimis_breakdown']->sum('total_applied_amount');
                $otherTaxableBenefits =
                    $group['earnings_breakdown']->where('is_taxable', 1)->sum('total_applied_amount') -
                    $group['basic_pay'];
                $totalTaxableCompensation = $group['basic_pay'] + $otherTaxableBenefits;

                // Accumulate totals
                $totals['total_earnings'] += $group['total_earnings'];
                $totals['thirteenth_month_pay'] += $group['thirteenth_month_pay'];
                $totals['deminimis_total'] += $group['deminimis_breakdown']->sum('total_applied_amount');
                $totals['contributions_total'] += $totalContributions;
                $totals['other_non_taxable_total'] += $otherNonTaxableBenefits;
                $totals['total_non_taxable'] += $totalNonTaxable;
                $totals['basic_pay_total'] += $group['basic_pay'];
                $totals['other_taxable_total'] += $otherTaxableBenefits;
                $totals['total_taxable_compensation'] += $totalTaxableCompensation;
                $totals['withholding_tax_total'] += $group['withholding_tax'];
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $group['user']->governmentDetail->tin_number ?? '' }}</td>
                <td>{{ $group['user']->personalInformation->last_name ?? '' }}</td>
                <td>{{ $group['user']->personalInformation->first_name ?? '' }}</td>
                <td>{{ $group['user']->personalInformation->middle_name ?? '' }}</td>
                <td>{{ $group['pay_period_start'] ? \Carbon\Carbon::parse($group['pay_period_start'])->format('m/d/Y') : '' }}
                </td>
                <td>{{ $group['pay_period_end'] ? \Carbon\Carbon::parse($group['pay_period_end'])->format('m/d/Y') : '' }}
                </td>
                <td class="text-end">{{ number_format($group['total_earnings'], 2) }}</td>
                <td class="text-end">{{ number_format($group['thirteenth_month_pay'], 2) }}</td>
                <td class="text-end">{{ number_format($group['deminimis_breakdown']->sum('total_applied_amount'), 2) }}
                </td>
                <td class="text-end">{{ number_format($totalContributions, 2) }}</td>
                <td class="text-end">{{ number_format($otherNonTaxableBenefits, 2) }}</td>
                <td class="text-end">{{ number_format($totalNonTaxable, 2) }}</td>
                <td class="text-end">{{ number_format($group['basic_pay'], 2) }}</td>
                <td class="text-end">{{ number_format($otherTaxableBenefits, 2) }}</td>
                <td class="text-end">{{ number_format($totalTaxableCompensation, 2) }}</td>
                <td class="text-end">{{ number_format($group['withholding_tax'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="17" class="text-center">No data available</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="fw-bold bg-light">
            <td colspan="7" class="text-center border border-dark">TOTAL</td>
            <td class="text-end border border-dark">{{ number_format($totals['total_earnings'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['thirteenth_month_pay'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['deminimis_total'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['contributions_total'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['other_non_taxable_total'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['total_non_taxable'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['basic_pay_total'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['other_taxable_total'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['total_taxable_compensation'], 2) }}</td>
            <td class="text-end border border-dark">{{ number_format($totals['withholding_tax_total'], 2) }}</td>
        </tr>
    </tfoot>
</table>
