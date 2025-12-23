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
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Budget Requests</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- /Breadcrumb -->

            @php
                $hour = now()->hour;
                if ($hour < 12) {
                    $greeting = 'Good Morning';
                } elseif ($hour < 18) {
                    $greeting = 'Good Afternoon';
                } else {
                    $greeting = 'Good Evening';
                }

                $user = Auth::guard('web')->user() ?? Auth::guard('global')->user();
                $name = $user?->personalInformation->first_name ?? ($user?->username ?? 'Guest');

                // TODO: Replace with actual data from controller
                $pendingCount = 0;
                $approvedCount = 0;
                $rejectedCount = 0;
                $totalBudgetAmount = 0;
            @endphp

            <div class="row">
                <!-- Left Column - Quick Action Card -->
                <div class="col-xl-3 col-lg-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <h6 class="fw-medium text-gray-5 mb-2">{{ $greeting }}, {{ $name }}</h6>
                                <p class="text-muted mb-0 small">Submit and manage your budget requests</p>
                            </div>

                            <div class="attendance-circle-progress mx-auto mb-3" data-value='65'>
                                <span class="progress-left">
                                    <span class="progress-bar border-info"></span>
                                </span>
                                <span class="progress-right">
                                    <span class="progress-bar border-info"></span>
                                </span>
                                <div class="avatar avatar-xxl avatar-rounded bg-light-info">
                                    <i class="ti ti-chart-pie text-info" style="font-size: 48px;"></i>
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#add_budget_request">
                                    <i class="ti ti-circle-plus me-2"></i>Submit New Request
                                </button>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span class="text-muted small">Pending Requests</span>
                                    <span class="badge badge-warning">{{ $pendingCount }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span class="text-muted small">Approved</span>
                                    <span class="badge badge-success">{{ $approvedCount }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span class="text-muted small">Rejected</span>
                                    <span class="badge badge-danger">{{ $rejectedCount }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-2 bg-info-transparent rounded">
                                    <span class="text-info small fw-medium">Total Requested</span>
                                    <span class="badge badge-info">₱{{ number_format($totalBudgetAmount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Request History -->
                <div class="col-xl-9 col-lg-8 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>My Budget Requests</h5>
                            <div class="d-flex align-items-center gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-filter me-1"></i>All Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                        <li><a class="dropdown-item" href="#" data-filter="all">All Status</a></li>
                                        <li><a class="dropdown-item" href="#" data-filter="pending">Pending</a></li>
                                        <li><a class="dropdown-item" href="#" data-filter="approved">Approved</a></li>
                                        <li><a class="dropdown-item" href="#" data-filter="rejected">Rejected</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="custom-datatable-filter table-responsive">
                                <table class="table datatable" id="budgetRequestsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Category</th>
                                            <th>Amount</th>
                                            <th>Date Range</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6">
                                                <div class="text-center py-5">
                                                    <div class="avatar avatar-xl bg-light-info mx-auto mb-3">
                                                        <i class="ti ti-chart-pie text-info" style="font-size: 32px;"></i>
                                                    </div>
                                                    <h6 class="text-muted">No budget requests yet</h6>
                                                    <p class="text-muted small mb-3">Click "Submit New Request" to get started</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                        <button type="submit" class="btn btn-info">
                            <i class="ti ti-check me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Budget Request Modal -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#budgetRequestForm').on('submit', function(e) {
                e.preventDefault();
                // TODO: Implement form submission
                toastr.info('Budget request submission - Coming soon!');
            });
        });
    </script>
@endpush
