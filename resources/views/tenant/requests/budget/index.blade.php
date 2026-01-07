<?php $page = 'request-budget'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Budget Requests</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Budget Requests</li>
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
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_budget_request"
                                class="btn btn-primary d-flex align-items-center"><i
                                    class="ti ti-circle-plus me-2"></i>Request Budget</a>
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

            <!-- Budget Request Counts -->
            <div class="row">

                <div class="col-xl-4 col-md-6">
                    <div class="card"
                        style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <!-- LEFT TEXT SECTION -->
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Approved Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalApprovedBudgets }}
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
                                    {{ $totalPendingBudgets }}
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
                                    {{ $totalRejectedBudgets }}
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
            <!-- /Budget Request Counts -->

            <!-- Budget Request list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Budget Requests</h5>
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
                        <table class="table datatable" id="budgetRequestsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Request Date</th>
                                    <th class="text-center">Project Name</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Date Range</th>
                                    <th class="text-center">Justification</th>
                                    <th class="text-center">File Attachment</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Approved By</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="budgetRequestsTableBody">
                                @foreach ($budgetRequests as $budget)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $budget->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $budget->user->personalInformation->last_name }},
                                                            {{ $budget->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $budget->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $budget->created_at ? \Carbon\Carbon::parse($budget->created_at)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ $budget->project_name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $budget->budget_category ?? 'N/A' }}</td>
                                        <td class="text-center">₱{{ number_format($budget->requested_amount, 2) }}</td>
                                        <td class="text-center">
                                            @if ($budget->start_date && $budget->end_date)
                                                {{ \Carbon\Carbon::parse($budget->start_date)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($budget->end_date)->format('M j, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $budget->justification ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if ($budget->attachment)
                                                <a href="{{ asset('storage/' . $budget->attachment) }}"
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
                                                if ($budget->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($budget->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($budget->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($budget->approver_name ?? false)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $budget->approver_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $budget->approver_name }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $budget->approver_dept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td class="text-center">
                                                @if ($budget->status !== 'approved')
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_budget_request"
                                                                data-id="{{ $budget->id }}"
                                                                data-project-name="{{ $budget->project_name }}"
                                                                data-budget-category="{{ $budget->budget_category }}"
                                                                data-requested-amount="{{ $budget->requested_amount }}"
                                                                data-start-date="{{ $budget->start_date }}"
                                                                data-end-date="{{ $budget->end_date }}"
                                                                data-justification="{{ $budget->justification }}"
                                                                data-expected-outcome="{{ $budget->expected_outcome }}"
                                                                data-attachment="{{ $budget->attachment }}"><i
                                                                    class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="#" data-bs-toggle="modal" class="btn-delete"
                                                                data-bs-target="#delete_budget_request"
                                                                data-id="{{ $budget->id }}"
                                                                data-name="{{ $budget->user->personalInformation->full_name ?? 'N/A' }}"><i
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
            <!-- /Budget Request list -->

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    <!-- Add Budget Request Modal -->
    <div class="modal fade" id="add_budget_request" tabindex="-1" aria-labelledby="add_budget_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_budget_request_label">
                        <i class="ti ti-chart-pie me-2"></i>New Budget Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="budgetRequestForm" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="project_name" name="project_name" placeholder="Enter project name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="budget_category" class="form-label">Budget Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="budget_category" name="budget_category" required>
                                    <option value="">Select Category</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Operations">Operations</option>
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Training">Training</option>
                                    <option value="Equipment">Equipment</option>
                                    <option value="Travel">Travel</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="requested_amount" class="form-label">Requested Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="requested_amount" name="requested_amount" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="justification" class="form-label">Justification <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="justification" name="justification" rows="3" placeholder="Explain why this budget is needed..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="expected_outcome" class="form-label">Expected Outcome</label>
                                <textarea class="form-control" id="expected_outcome" name="expected_outcome" rows="2" placeholder="Describe the expected results..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="attachment" class="form-label">Supporting Documents</label>
                                <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                                <small class="text-muted">Upload budget breakdown, proposals, etc. - Maximum file size: 5MB</small>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>Budget requests require approval from your department head and finance team. Processing time may take 5-7 business days.</small>
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
    <!-- /Add Budget Request Modal -->

    <!-- Edit Budget Request Modal -->
    <div class="modal fade" id="edit_budget_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-2"></i>Edit Budget Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBudgetRequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_project_name" name="project_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_budget_category" class="form-label">Budget Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_budget_category" name="budget_category" required>
                                    <option value="">Select Category</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Operations">Operations</option>
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Training">Training</option>
                                    <option value="Equipment">Equipment</option>
                                    <option value="Travel">Travel</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_requested_amount" class="form-label">Requested Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_requested_amount" name="requested_amount" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_justification" class="form-label">Justification <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_justification" name="justification" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_expected_outcome" class="form-label">Expected Outcome</label>
                                <textarea class="form-control" id="edit_expected_outcome" name="expected_outcome" rows="2"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Current Attachment</label>
                                <div id="currentBudgetAttachmentDisplay"></div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_attachment" class="form-label">Update Supporting Documents (optional)</label>
                                <input type="file" class="form-control" id="edit_attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
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
    <!-- /Edit Budget Request Modal -->

    <!-- Delete Budget Request Modal -->
    <div class="modal fade" id="delete_budget_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Budget Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the budget request for <strong id="budgetRequestPlaceholder"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBudgetDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Budget Request Modal -->

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
                url: '{{ route('budget-request-filter') }}',
                type: 'GET',
                data: {
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#budgetRequestsTable').DataTable().destroy();
                        $('#budgetRequestsTableBody').html(response.html);
                        $('#budgetRequestsTable').DataTable();
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

    {{-- Request Budget --}}
    <script>
        $(document).ready(function() {
            $('#budgetRequestForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this)[0];
                var formData = new FormData(form);

                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/budget-requests/employee/request') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Budget request submitted successfully.');
                            $('#add_budget_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to request budget.'));
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

    {{-- Edit Request Budget --}}
    <script>
        $(document).ready(function() {
            $(document).on('click', 'a[data-bs-target="#edit_budget_request"]', function() {
                const id = $(this).data('id');
                $('#editBudgetRequestForm').data('id', id);

                $('#edit_project_name').val($(this).data('project-name'));
                $('#edit_budget_category').val($(this).data('budget-category'));
                $('#edit_requested_amount').val($(this).data('requested-amount'));
                $('#edit_start_date').val($(this).data('start-date'));
                $('#edit_end_date').val($(this).data('end-date'));
                $('#edit_justification').val($(this).data('justification'));
                $('#edit_expected_outcome').val($(this).data('expected-outcome'));

                let attachment = $(this).data('attachment');
                let displayHtml = '';
                if (attachment && attachment !== 'null' && attachment !== '') {
                    let url = `/storage/${attachment}`;
                    displayHtml = `<a href="${url}" target="_blank" class="text-primary">
            <i class="ti ti-file"></i> View Current Attachment
        </a>`;
                }
                $('#currentBudgetAttachmentDisplay').html(displayHtml);

                $('#edit_attachment').val('');
            });

            $('#editBudgetRequestForm').on('submit', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: `/api/budget-requests/employee/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Budget request updated successfully.');
                            $('#edit_budget_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to update budget request.'));
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

    {{-- Delete Request Budget --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let budgetDeleteId = null;
            const confirmBudgetDeleteBtn = document.getElementById('confirmBudgetDeleteBtn');
            const budgetRequestPlaceholder = document.getElementById('budgetRequestPlaceholder');

            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                budgetDeleteId = button.getAttribute('data-id');
                const budgetName = button.getAttribute('data-name');

                if (budgetRequestPlaceholder) {
                    budgetRequestPlaceholder.textContent = budgetName;
                }
            });

            confirmBudgetDeleteBtn?.addEventListener('click', function() {
                if (!budgetDeleteId) return;

                fetch(`/api/budget-requests/employee/delete/${budgetDeleteId}`, {
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
                            toastr.success("Budget request deleted successfully.");
                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_budget_request'));
                            deleteModal.hide();
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting budget request.");
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
