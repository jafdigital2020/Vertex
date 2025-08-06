<?php $page = 'alphalist-reports'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Alphalist Reports</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Alphalist Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <div>
                            @if(in_array('Export', $permission))
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1" onclick="exportToExcel()">
                                            <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Card and Selection --}}
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">Generate Alphalist Report</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="alphalistForm" action="{{ route('alphalist-report') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="branch_filter" class="form-label">Branch <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="branch_filter" name="branch_filter" required>
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ request('branch_filter') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="">Select Year</option>
                                        @for ($i = date('Y'); $i >= 2020; $i--)
                                            <option value="{{ $i }}"
                                                {{ request('year') == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                           @if(in_array('Read', $permission) || in_array('Create', $permission))
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-file-report me-1"></i>Process Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if ($selectedBranch)
                <!-- Header Section -->
                <div class="text-start mb-4" id="reportHeader">
                    <h3 class="fw-bold">BIR FORM</h3>
                    <h5>AS OF DECEMBER {{ request('year') ?? date('Y') }}</h5>
                    <div class="mt-3">
                        <p><strong>TIN:</strong> {{ $selectedBranch->branch_tin ?? '' }}</p>
                        <p><strong>BRANCH NAME:</strong> {{ $selectedBranch->name ?? '' }}</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="alphalistTable">
                        <thead>
                            <tr>
                                <th rowspan="3" class="text-center align-middle border border-dark">SEQ NO</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">TAXPAYER IDENTIFICATION
                                    NUMBER</th>
                                <th colspan="3" class="text-center border border-dark">NAME OF EMPLOYEES</th>
                                <th colspan="2" class="text-center border border-dark">Inclusive Date of Employment</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">GROSS COMPENSATION INCOME
                                </th>
                                <th rowspan="3" class="text-center align-middle border border-dark">13th MONTH PAY & OTHER
                                    BENEFITS (≤
                                    90K)</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">DE MINIMIS BENEFITS</th>
                                <th colspan="2" class="text-center border border-dark">NON-TAXABLE COMPENSATION</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">TOTAL NON-TAXABLE/EXEMPT
                                    COMPENSATION
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
                                <th rowspan="2" class="text-center align-middle border border-dark">
                                    SSS/GSIS/PHIC/PAG-IBIG/Union Dues
                                </th>
                                <th rowspan="2" class="text-center align-middle border border-dark">Other Non-Taxable
                                    Benefits
                                </th>
                                <th rowspan="2" class="text-center align-middle border border-dark">Basic Salary (Taxable
                                    Portion)</th>
                                <th rowspan="2" class="text-center align-middle border border-dark">Other Taxable Benefits
                                </th>
                                <th rowspan="2" class="text-center align-middle border border-dark">TOTAL TAXABLE
                                    COMPENSATION
                                    INCOME
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
                                        $group['sss_contribution'] +
                                        $group['philhealth_contribution'] +
                                        $group['pagibig_contribution'];
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
                                    $totals['deminimis_total'] += $group['deminimis_breakdown']->sum(
                                        'total_applied_amount',
                                    );
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
                                    <td class="text-end">
                                        {{ number_format($group['deminimis_breakdown']->sum('total_applied_amount'), 2) }}
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
                                <td class="text-end border border-dark">
                                    {{ number_format($totals['thirteenth_month_pay'], 2) }}
                                </td>
                                <td class="text-end border border-dark">{{ number_format($totals['deminimis_total'], 2) }}
                                </td>
                                <td class="text-end border border-dark">{{ number_format($totals['contributions_total'], 2) }}
                                </td>
                                <td class="text-end border border-dark">
                                    {{ number_format($totals['other_non_taxable_total'], 2) }}
                                </td>
                                <td class="text-end border border-dark">{{ number_format($totals['total_non_taxable'], 2) }}
                                </td>
                                <td class="text-end border border-dark">{{ number_format($totals['basic_pay_total'], 2) }}
                                </td>
                                <td class="text-end border border-dark">{{ number_format($totals['other_taxable_total'], 2) }}
                                </td>
                                <td class="text-end border border-dark">
                                    {{ number_format($totals['total_taxable_compensation'], 2) }}</td>
                                <td class="text-end border border-dark">
                                    {{ number_format($totals['withholding_tax_total'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center my-5">
                    <i class="ti ti-info-circle fs-2 mb-2"></i>
                    <div class="fw-bold">Please use the filters above to generate report.</div>
                </div>
            @endif
        </div>
        @include('layout.partials.footer-company')

    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script type="application/json" id="reportData">
                {
                    "branch": @json($selectedBranch ?? null),
                    "year": "{{ request('year') ?? date('Y') }}",
                    "payrolls": @json($payrollsGrouped ?? []),
                    "totals": @json($totals ?? [])
                }
            </script>

    <script>
        function exportToExcel() {
            const reportData = JSON.parse(document.getElementById('reportData').textContent);

            // Create workbook
            const wb = XLSX.utils.book_new();

            // Create worksheet data array
            const wsData = [];

            // Add header info
            wsData.push(['BIR FORM']);
            wsData.push([`AS OF DECEMBER ${reportData.year}`]);
            wsData.push([]);
            wsData.push([`TIN: ${reportData.branch?.branch_tin || ''}`]);
            wsData.push([`BRANCH NAME: ${reportData.branch?.name || ''}`]);
            wsData.push([]);

            // Create complex header structure to match the table
            // First header row
            wsData.push([
                'SEQ NO', 'TAXPAYER IDENTIFICATION NUMBER', 'NAME OF EMPLOYEES', '', '',
                'Inclusive Date of Employment', '', 'GROSS COMPENSATION INCOME',
                '13th MONTH PAY & OTHER BENEFITS (≤ 90K)', 'DE MINIMIS BENEFITS',
                'NON-TAXABLE COMPENSATION', '', 'TOTAL NON-TAXABLE/EXEMPT COMPENSATION INCOME',
                'TAXABLE COMPENSATION', '', '', 'TAX WITHHELD'
            ]);

            // Second header row
            wsData.push([
                '', '', '(Last Name)', '(First Name)', '(Middle Name)', 'From', 'To', '', '', '',
                'SSS/GSIS/PHIC/PAG-IBIG/Union Dues', 'Other Non-Taxable Benefits', '',
                'Basic Salary (Taxable Portion)', 'Other Taxable Benefits', 'TOTAL TAXABLE COMPENSATION INCOME', ''
            ]);

            // Add data rows
            let seqNo = 1;
            Object.values(reportData.payrolls).forEach(group => {
                const totalContributions = group.sss_contribution + group.philhealth_contribution + group.pagibig_contribution;
                const otherNonTaxableBenefits = group.earnings_breakdown?.filter(e => e.is_taxable === 0).reduce((sum, e) => sum + e.total_applied_amount, 0) || 0;
                const deminimisTotal = group.deminimis_breakdown?.reduce((sum, d) => sum + d.total_applied_amount, 0) || 0;
                const totalNonTaxable = totalContributions + otherNonTaxableBenefits + deminimisTotal;
                const otherTaxableBenefits = (group.earnings_breakdown?.filter(e => e.is_taxable === 1).reduce((sum, e) => sum + e.total_applied_amount, 0) || 0) - group.basic_pay;
                const totalTaxableCompensation = group.basic_pay + otherTaxableBenefits;

                // Format dates
                const fromDate = group.pay_period_start ? new Date(group.pay_period_start).toLocaleDateString() : '';
                const toDate = group.pay_period_end ? new Date(group.pay_period_end).toLocaleDateString() : '';

                wsData.push([
                    seqNo++,
                    group.user?.government_detail?.tin_number || '',
                    group.user?.personal_information?.last_name || '',
                    group.user?.personal_information?.first_name || '',
                    group.user?.personal_information?.middle_name || '',
                    fromDate,
                    toDate,
                    parseFloat(group.total_earnings || 0),
                    parseFloat(group.thirteenth_month_pay || 0),
                    deminimisTotal,
                    totalContributions,
                    otherNonTaxableBenefits,
                    totalNonTaxable,
                    parseFloat(group.basic_pay || 0),
                    otherTaxableBenefits,
                    totalTaxableCompensation,
                    parseFloat(group.withholding_tax || 0)
                ]);
            });

            // Add totals row
            wsData.push([
                'TOTAL', '', '', '', '', '', '',
                parseFloat(reportData.totals?.total_earnings || 0),
                parseFloat(reportData.totals?.thirteenth_month_pay || 0),
                parseFloat(reportData.totals?.deminimis_total || 0),
                parseFloat(reportData.totals?.contributions_total || 0),
                parseFloat(reportData.totals?.other_non_taxable_total || 0),
                parseFloat(reportData.totals?.total_non_taxable || 0),
                parseFloat(reportData.totals?.basic_pay_total || 0),
                parseFloat(reportData.totals?.other_taxable_total || 0),
                parseFloat(reportData.totals?.total_taxable_compensation || 0),
                parseFloat(reportData.totals?.withholding_tax_total || 0)
            ]);

            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(wsData);

            // Merge cells for header structure (similar to table design)
            const merges = [
                // SEQ NO - rows 6-7
                {s: {c: 0, r: 6}, e: {c: 0, r: 7}},
                // TAXPAYER ID - rows 6-7
                {s: {c: 1, r: 6}, e: {c: 1, r: 7}},
                // NAME OF EMPLOYEES - cols 2-4
                {s: {c: 2, r: 6}, e: {c: 4, r: 6}},
                // Inclusive Date - cols 5-6
                {s: {c: 5, r: 6}, e: {c: 6, r: 6}},
                // GROSS COMPENSATION - rows 6-7
                {s: {c: 7, r: 6}, e: {c: 7, r: 7}},
                // 13th MONTH - rows 6-7
                {s: {c: 8, r: 6}, e: {c: 8, r: 7}},
                // DE MINIMIS - rows 6-7
                {s: {c: 9, r: 6}, e: {c: 9, r: 7}},
                // NON-TAXABLE COMPENSATION - cols 10-11
                {s: {c: 10, r: 6}, e: {c: 11, r: 6}},
                // TOTAL NON-TAXABLE - rows 6-7
                {s: {c: 12, r: 6}, e: {c: 12, r: 7}},
                // TAXABLE COMPENSATION - cols 13-15
                {s: {c: 13, r: 6}, e: {c: 15, r: 6}},
                // TAX WITHHELD - rows 6-7
                {s: {c: 16, r: 6}, e: {c: 16, r: 7}}
            ];

            ws['!merges'] = merges;

            // Set column widths
            ws['!cols'] = [
                {width: 8}, {width: 20}, {width: 15}, {width: 15}, {width: 15},
                {width: 12}, {width: 12}, {width: 18}, {width: 20}, {width: 15},
                {width: 25}, {width: 20}, {width: 25}, {width: 20}, {width: 18},
                {width: 25}, {width: 15}
            ];

            // Style the cells (make headers bold, center align, add borders)
            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let R = range.s.r; R <= range.e.r; ++R) {
                for (let C = range.s.c; C <= range.e.c; ++C) {
                    const cell_address = {c: C, r: R};
                    const cell_ref = XLSX.utils.encode_cell(cell_address);

                    if (!ws[cell_ref]) continue;

                    // Style header rows and totals
                    if (R <= 7 || R === wsData.length - 1) {
                        ws[cell_ref].s = {
                            font: {bold: true},
                            alignment: {horizontal: 'center', vertical: 'center'},
                            border: {
                                top: {style: 'thin'},
                                bottom: {style: 'thin'},
                                left: {style: 'thin'},
                                right: {style: 'thin'}
                            }
                        };
                    }

                    // Right align numeric columns
                    if (C >= 7 && R > 7 && R < wsData.length - 1) {
                        ws[cell_ref].s = {
                            alignment: {horizontal: 'right'},
                            numFmt: '#,##0.00'
                        };
                    }
                }
            }

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Alphalist Report');

            // Save file
            const filename = `Alphalist_Report_${reportData.year}_${reportData.branch?.name || 'All'}.xlsx`;
            XLSX.writeFile(wb, filename);
        }
    </script>
@endpush
