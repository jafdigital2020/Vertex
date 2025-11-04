<?php $page = 'payslip'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">13th Month Pay Slip</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Payroll</li>
                            <li class="breadcrumb-item active" aria-current="page">13th Month Pay Slip</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="mb-2">
                        <a href="#" id="downloadBtn" class="btn btn-dark d-flex align-items-center">
                            <i class="ti ti-download me-2"></i>Download
                        </a>
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
                            <h5 class="mb-2 text-success"><i class="ti ti-calendar-dollar me-1"></i>13TH MONTH PAY</h5>
                            <div class="text-muted small mb-1">Payslip #: <span
                                    class="fw-bold text-primary">#13MP{{ $payslips->id }}</span></div>
                            <div class="text-muted small mb-1">Coverage Period:
                                <strong>{{ \Carbon\Carbon::create($payslips->from_year ?? $payslips->year, $payslips->from_month)->format('F Y') }}
                                    -
                                    {{ \Carbon\Carbon::create($payslips->to_year ?? $payslips->year, $payslips->to_month)->format('F Y') }}</strong>
                            </div>
                            <div class="text-muted small mb-1">Payment Date:
                                <strong>{{ \Carbon\Carbon::parse($payslips->payment_date)->format('M d, Y') }}</strong>
                            </div>
                            <span
                                class="badge bg-{{ $payslips->status == 'Released' ? 'success' : 'secondary' }} px-3 py-2 fs-8">{{ $payslips->status }}</span>
                        </div>
                    </div>

                    <!-- Employee & Payroll Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="40%">Employee:</td>
                                    <td class="fw-semibold">{{ $payslips->user->personalInformation->full_name ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Employee ID:</td>
                                    <td>{{ $payslips->user->employmentDetail->employee_id ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Position:</td>
                                    <td>{{ $payslips->user->employmentDetail->designation->designation_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Department:</td>
                                    <td>{{ $payslips->user->employmentDetail->department->department_name ?? '' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="40%">Email:</td>
                                    <td>{{ $payslips->user->email ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Year:</td>
                                    <td class="fw-semibold">{{ $payslips->year }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Processed By:</td>
                                    <td>{{ $payslips->processor_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Remarks:</td>
                                    <td>{{ $payslips->remarks ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Monthly Breakdown Table -->
                    <div class="card border-0 rounded-3 shadow-sm mb-4">
                        <div class="card-header bg-light border-bottom-0 rounded-top-3 py-3 px-4">
                            <h5 class="mb-0 fw-bold"><i class="ti ti-calendar-stats me-2"></i>Monthly Breakdown</h5>
                            <small>Detailed computation for each month in the coverage period</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Month</th>
                                            <th class="border-0 text-center">Payroll Count</th>
                                            <th class="border-0 text-end">Basic Pay</th>
                                            <th class="border-0 text-end">13th Month</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $monthlyBreakdown = is_array($payslips->monthly_breakdown)
                                                ? $payslips->monthly_breakdown
                                                : json_decode($payslips->monthly_breakdown, true) ?? [];

                                            // Default year to payslip year if available (handles year boundary properly)
                                            $defaultYear = $payslips->year ?? date('Y');

                                            // Helper to extract numeric year and month from various possible keys
                                            $extractYearMonth = function ($item) use ($defaultYear) {
                                                try {
                                                    // Year extraction
                                                    if (!empty($item['year'])) {
                                                        $y = (int) $item['year'];
                                                    } elseif (!empty($item['period_year'])) {
                                                        $y = (int) $item['period_year'];
                                                    } elseif (!empty($item['period_start'])) {
                                                        $y = \Carbon\Carbon::parse($item['period_start'])->year;
                                                    } else {
                                                        $y = $defaultYear;
                                                    }

                                                    // Month extraction
                                                    if (!empty($item['month'])) {
                                                        $m = (int) $item['month'];
                                                    } elseif (!empty($item['period_month'])) {
                                                        $m = (int) $item['period_month'];
                                                    } elseif (!empty($item['period_start'])) {
                                                        $m = \Carbon\Carbon::parse($item['period_start'])->month;
                                                    } elseif (!empty($item['month_name'])) {
                                                        // month_name like "September"
                                                        $m = \Carbon\Carbon::createFromFormat('F', $item['month_name'])->month;
                                                    } else {
                                                        $m = 0;
                                                    }

                                                    return [$y, $m];
                                                } catch (\Exception $e) {
                                                    return [$defaultYear, 0];
                                                }
                                            };

                                            // Sort by year then month to ensure chronological order across year boundaries
                                            usort($monthlyBreakdown, function ($a, $b) use ($extractYearMonth) {
                                                [$ay, $am] = $extractYearMonth($a);
                                                [$by, $bm] = $extractYearMonth($b);

                                                if ($ay === $by) {
                                                    return $am <=> $bm;
                                                }
                                                return $ay <=> $by;
                                            });
                                        @endphp

                                        @forelse ($monthlyBreakdown as $month)
                                            <tr>
                                                <td class="fw-semibold">{{ $month['month_name'] ?? '' }}</td>

                                                <td class="text-center">
                                                    <span>{{ $month['payroll_count'] ?? 0 }}</span>
                                                </td>
                                                <td class="text-end">₱{{ number_format($month['basic_pay'] ?? 0, 2) }}</td>

                                                <td class="text-end fw-bold text-success">
                                                    ₱{{ number_format($month['thirteenth_month_contribution'] ?? 0, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    <i class="ti ti-info-circle me-2"></i>No monthly breakdown available
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        @php
                            $totalPayroll = 0;
                            if (is_array($monthlyBreakdown)) {
                                foreach ($monthlyBreakdown as $m) {
                                    $totalPayroll += isset($m['payroll_count']) ? (int) $m['payroll_count'] : 0;
                                }
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="card border-0 rounded-3 shadow-sm bg-warning bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Payroll</h6>
                                    <h3 class="text-warning mb-0">{{ $totalPayroll }}</h3>
                                    <small class="text-muted">Total payroll entries in coverage period</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 rounded-3 shadow-sm bg-primary bg-opacity-10">
                                <div class="card-body text-center text-white">
                                    <h6 class="mb-2 text-white">Total Basic Pay</h6>
                                    <h3 class="mb-0 text-white">₱{{ number_format($payslips->total_basic_pay, 2) }}</h3>
                                    <small class="text-white-50">Sum of all basic pay</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 rounded-3 shadow-sm bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">13th Month Pay</h6>
                                    <h3 class="text-success mb-0 fw-bold">
                                        ₱{{ number_format($payslips->total_thirteenth_month, 2) }}</h3>
                                    <small class="text-muted">Final amount</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Signatures -->
                    <div class="row mt-5 pt-4 border-top">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="border-bottom border-dark d-inline-block px-5 pb-1 mb-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                                <p class="mb-0 fw-semibold">Employee Signature</p>
                                <p class="text-muted small mb-0">
                                    {{ $payslips->user->personalInformation->full_name ?? '' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="border-bottom border-dark d-inline-block px-5 pb-1 mb-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                                <p class="mb-0 fw-semibold">Authorized Signatory</p>
                                <p class="text-muted small mb-0">HR / Finance </p>
                            </div>
                        </div>
                    </div>

                    <!-- Disclaimer -->
                    <div class="alert alert-light border mt-4 mb-0">
                        <p class="mb-0 text-muted small">
                            <i class="ti ti-info-circle me-1"></i>
                            <strong>Note:</strong> This is a computer-generated 13th month pay slip.
                            The 13th month pay is mandatory for all rank-and-file employees in the Philippines
                            as mandated by Presidential Decree No. 851. This benefit must be paid on or before December 24
                            of every year.
                        </p>
                    </div>
                </div>
            </div>

            <!-- /Payslip -->


        </div>

        @include('layout.partials.footer-company')

    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.getElementById('downloadBtn').addEventListener('click', function() {
            var content = document.querySelector('.printable-area');

            html2canvas(content, {
                useCORS: true,
                scale: 1.5,
                logging: false,
                allowTaint: true,
                backgroundColor: '#ffffff'
            }).then(function(canvas) {
                try {
                    var imgData = canvas.toDataURL('image/jpeg', 0.85); // 85% quality
                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF({
                        orientation: 'portrait',
                        unit: 'pt',
                        format: 'a4',
                        compress: true
                    });

                    //  fit A4 page
                    var pageWidth = doc.internal.pageSize.getWidth();
                    var pageHeight = doc.internal.pageSize.getHeight();
                    var imgWidth = canvas.width;
                    var imgHeight = canvas.height;
                    var ratio = Math.min(pageWidth / imgWidth, pageHeight / imgHeight);

                    var imgX = (pageWidth - imgWidth * ratio) / 2;
                    var imgY = 20;

                    doc.addImage(imgData, 'JPEG', imgX, imgY, imgWidth * ratio, imgHeight * ratio,
                        undefined, 'FAST'); // Use JPEG and FAST compression

                    doc.save(
                        '13th-month-payslip-{{ $payslips->id }}-{{ str_replace(' ', '-', $payslips->user->personalInformation->full_name ?? 'employee') }}.pdf'
                    );
                } catch (error) {
                    console.error('Error capturing the printable area:', error);
                }
            }).catch(function(error) {
                console.error('html2canvas failed:', error);
            });
        });
    </script>
@endpush
