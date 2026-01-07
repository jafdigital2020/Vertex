<?php $page = 'request-loan'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Loan Requests</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Loan Requests</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Export', $permission))
                        {{-- <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                                </li>
                            </ul>
                        </div>
                    </div> --}}
                    @endif
                    @if (in_array('Create', $permission))
                        <div class="mb-2 me-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_loan_request"
                                class="btn btn-primary d-flex align-items-center"><i
                                    class="ti ti-circle-plus me-2"></i>Request Loan</a>
                        </div>
                    @endif
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Loan Request Counts -->
            <div class="row">

                <div class="col-xl-4 col-md-6">
                    <div class="card"
                        style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <!-- LEFT TEXT SECTION -->
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Approved Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalApprovedLoans }}
                                </h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>

                            <!-- RIGHT ICON SECTION -->
                            <div class="position-relative d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-primary"
                                    style="
                                        position: absolute;
                                        right: -35%;
                                        top: 90%;
                                        transform: translateY(-55%);
                                        width: 140px;
                                        height: 140px;
                                        background: #fdeff4;
                                        border-radius: 50%;
                                        z-index: 1;
                                        clip-path: inset(0 0 0 0 round 12px);
                                    ">
                                </div>

                                <!-- Icon circle (foreground) -->
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary"
                                    style="
                                        position: relative;
                                        z-index: 2;
                                        width: 45px;
                                        height: 45px;
                                        color: white;

                                        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
                                        right: -10px;
                                        top: 20px;
                                    ">
                                    <i class="ti ti-user-check fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card"
                        style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <!-- LEFT TEXT SECTION -->
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Pending Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalPendingLoans }}
                                </h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>

                            <!-- RIGHT ICON SECTION -->
                            <div class="position-relative d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-mustard"
                                    style="
                                        position: absolute;
                                        right: -35%;
                                        top: 90%;
                                        transform: translateY(-55%);
                                        width: 140px;
                                        height: 140px;
                                        background: #f4eeff;
                                        border-radius: 50%;
                                        z-index: 1;
                                        clip-path: inset(0 0 0 0 round 12px);
                                    ">
                                </div>

                                <!-- Icon circle (foreground) -->
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-mustard"
                                    style="
                                        position: relative;
                                        z-index: 2;
                                        width: 45px;
                                        height: 45px;
                                        color: white;
                                        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
                                        right: -10px;
                                        top: 20px;
                                    ">
                                    <i class="ti ti-user-exclamation fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card"
                        style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <!-- LEFT TEXT SECTION -->
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Rejected Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalRejectedLoans }}
                                </h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>

                            <!-- RIGHT ICON SECTION -->
                            <div class="position-relative d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-raspberry"
                                    style="
                                        position: absolute;
                                        right: -35%;
                                        top: 90%;
                                        transform: translateY(-55%);
                                        width: 140px;
                                        height: 140px;
                                        background: #fff2f2;
                                        border-radius: 50%;
                                        z-index: 1;
                                        clip-path: inset(0 0 0 0 round 12px);
                                    ">
                                </div>

                                <!-- Icon circle (foreground) -->
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-raspberry"
                                    style="
                                        position: relative;
                                        z-index: 2;
                                        width: 45px;
                                        height: 45px;
                                        color: white;
                                        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
                                        right: -10px;
                                        top: 20px;
                                    ">
                                    <i class="ti ti-user-x fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Loan Request Counts -->

            <!-- Loan Request list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Loan Requests</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select" oninput="filter()">
                                <option value="" selected>All Status</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="loanRequestsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Request Date</th>
                                    <th class="text-center">Loan Type</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Repayment Period</th>
                                    <th class="text-center">Purpose</th>
                                    <th class="text-center">File Attachment</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Approved By</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="loanRequestsTableBody">
                                @foreach ($loanRequests as $loan)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $loan->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $loan->user->personalInformation->last_name }},
                                                            {{ $loan->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $loan->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $loan->created_at ? \Carbon\Carbon::parse($loan->created_at)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ $loan->loan_type ?? 'N/A' }}</td>
                                        <td class="text-center">₱{{ number_format($loan->loan_amount, 2) }}</td>
                                        <td class="text-center">{{ $loan->repayment_period }} months</td>
                                        <td class="text-center">{{ $loan->purpose ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if ($loan->attachment)
                                                <a href="{{ asset('storage/' . $loan->attachment) }}"
                                                    class="text-primary" target="_blank">
                                                    <i class="ti ti-file-text"></i> View Attachment
                                                </a>
                                            @else
                                                <span class="text-muted">No Attachment</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $badgeClass = 'badge-info';
                                                if ($loan->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($loan->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($loan->approver_name ?? false)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $loan->approver_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $loan->approver_name }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $loan->approver_dept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td class="text-center">
                                                @if ($loan->status !== 'approved')
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_loan_request"
                                                                data-id="{{ $loan->id }}"
                                                                data-loan-type="{{ $loan->loan_type }}"
                                                                data-loan-amount="{{ $loan->loan_amount }}"
                                                                data-repayment-period="{{ $loan->repayment_period }}"
                                                                data-purpose="{{ $loan->purpose }}"
                                                                data-collateral="{{ $loan->collateral }}"
                                                                data-attachment="{{ $loan->attachment }}"><i
                                                                    class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="#" data-bs-toggle="modal" class="btn-delete"
                                                                data-bs-target="#delete_loan_request"
                                                                data-id="{{ $loan->id }}"
                                                                data-name="{{ $loan->user->personalInformation->full_name ?? 'N/A' }}"><i
                                                                    class="ti ti-trash"></i></a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Loan Request list -->

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    <!-- Add Loan Request Modal -->
    <div class="modal fade" id="add_loan_request" tabindex="-1" aria-labelledby="add_loan_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_loan_request_label">
                        <i class="ti ti-currency-dollar me-2"></i>New Loan Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="loanRequestForm" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="loan_type" class="form-label">Loan Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="loan_type" name="loan_type" required>
                                    <option value="">Select Loan Type</option>
                                    <option value="Emergency Loan">Emergency Loan</option>
                                    <option value="Salary Loan">Salary Loan</option>
                                    <option value="Personal Loan">Personal Loan</option>
                                    <option value="Educational Loan">Educational Loan</option>
                                    <option value="Housing Loan">Housing Loan</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="loan_amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="loan_amount" name="loan_amount" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="repayment_period" class="form-label">Repayment Period (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="repayment_period" name="repayment_period" min="1" placeholder="12" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                                <input type="number" class="form-control" id="interest_rate" name="interest_rate" step="0.01" value="0" readonly>
                                <small class="text-muted">Rate will be determined by HR</small>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose of Loan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Explain the purpose of the loan..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="collateral" class="form-label">Collateral (if any)</label>
                                <textarea class="form-control" id="collateral" name="collateral" rows="2" placeholder="Describe any collateral or guarantor..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="attachment" class="form-label">Supporting Documents</label>
                                <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Upload any supporting documents (ID, proof of income, etc.) - Maximum file size: 5MB</small>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>Loan requests are subject to approval and company loan policy. Processing time may take 3-5 business days.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Loan Request Modal -->

    <!-- Edit Loan Request Modal -->
    <div class="modal fade" id="edit_loan_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-2"></i>Edit Loan Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editLoanRequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_loan_type" class="form-label">Loan Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_loan_type" name="loan_type" required>
                                    <option value="">Select Loan Type</option>
                                    <option value="Emergency Loan">Emergency Loan</option>
                                    <option value="Salary Loan">Salary Loan</option>
                                    <option value="Personal Loan">Personal Loan</option>
                                    <option value="Educational Loan">Educational Loan</option>
                                    <option value="Housing Loan">Housing Loan</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_loan_amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_loan_amount" name="loan_amount" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_repayment_period" class="form-label">Repayment Period (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_repayment_period" name="repayment_period" min="1" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_purpose" class="form-label">Purpose of Loan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_purpose" name="purpose" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_collateral" class="form-label">Collateral (if any)</label>
                                <textarea class="form-control" id="edit_collateral" name="collateral" rows="2"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Current Attachment</label>
                                <div id="currentAttachmentDisplay"></div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_attachment" class="form-label">Update Supporting Documents (optional)</label>
                                <input type="file" class="form-control" id="edit_attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Update Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Edit Loan Request Modal -->

    <!-- Delete Loan Request Modal -->
    <div class="modal fade" id="delete_loan_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Loan Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the loan request for <strong id="loanRequestPlaceholder"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmLoanDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Loan Request Modal -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        if ($('.bookingrange-filtered').length > 0) {
            var start = moment().startOf('year');
            var end = moment().endOf('year');

            function booking_range(start, end) {
                $('.bookingrange-filtered span').html(start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'));
            }

            $('.bookingrange-filtered').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Next Year': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                }
            }, booking_range);

            booking_range(start, end);
        }

        $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
            filter();
        });

        function filter() {
            const dateRange = $('#dateRange_filter').val();
            const status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('loan-request-filter') }}',
                type: 'GET',
                data: {
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#loanRequestsTable').DataTable().destroy();
                        $('#loanRequestsTableBody').html(response.html);
                        $('#loanRequestsTable').DataTable();
                    } else {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    let message = 'An unexpected error occurred.';
                    if (xhr.status === 403) {
                        message = 'You are not authorized to perform this action.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                }
            });
        }
    </script>

    {{-- Request Loan --}}
    <script>
        $(document).ready(function() {
            // Handle form submission
            $('#loanRequestForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this)[0];
                var formData = new FormData(form);

                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/loan-requests/employee/request') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Loan request submitted successfully.');
                            $('#add_loan_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to request loan.'));
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred while processing your request.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });
        });
    </script>

    {{-- Edit Request Loan --}}
    <script>
        $(document).ready(function() {
            // Populate modal when clicking edit
            $(document).on('click', 'a[data-bs-target="#edit_loan_request"]', function() {
                const id = $(this).data('id');
                $('#editLoanRequestForm').data('id', id);

                $('#edit_loan_type').val($(this).data('loan-type'));
                $('#edit_loan_amount').val($(this).data('loan-amount'));
                $('#edit_repayment_period').val($(this).data('repayment-period'));
                $('#edit_purpose').val($(this).data('purpose'));
                $('#edit_collateral').val($(this).data('collateral'));

                // Attachment logic
                let attachment = $(this).data('attachment');
                let displayHtml = '';
                if (attachment && attachment !== 'null' && attachment !== '') {
                    let url = `/storage/${attachment}`;
                    let filename = attachment.split('/').pop();
                    displayHtml = `<a href="${url}" target="_blank" class="text-primary">
            <i class="ti ti-file"></i> View Current Attachment
        </a>`;
                }
                $('#currentAttachmentDisplay').html(displayHtml);

                $('#edit_attachment').val('');
            });

            // Submit update AJAX
            $('#editLoanRequestForm').on('submit', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: `/api/loan-requests/employee/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Loan request updated successfully.');
                            $('#edit_loan_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to update loan request.'));
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred while processing your request.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });
        });
    </script>

    {{-- Delete Request Loan --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let loanDeleteId = null;
            const confirmLoanDeleteBtn = document.getElementById('confirmLoanDeleteBtn');
            const loanRequestPlaceholder = document.getElementById('loanRequestPlaceholder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                loanDeleteId = button.getAttribute('data-id');
                const loanName = button.getAttribute('data-name');

                if (loanRequestPlaceholder) {
                    loanRequestPlaceholder.textContent = loanName;
                }
            });

            // Confirm delete
            confirmLoanDeleteBtn?.addEventListener('click', function() {
                if (!loanDeleteId) return;

                fetch(`/api/loan-requests/employee/delete/${loanDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Loan request deleted successfully.");
                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_loan_request'));
                            deleteModal.hide();
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting loan request.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });
        });
    </script>
@endpush
