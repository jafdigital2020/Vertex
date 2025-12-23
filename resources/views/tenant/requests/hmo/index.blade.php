<?php $page = 'request-hmo'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">HMO Requests</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">HMO Requests</li>
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
                $activeHMO = 0;
            @endphp

            <div class="row">
                <!-- Left Column - Quick Action Card -->
                <div class="col-xl-3 col-lg-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <h6 class="fw-medium text-gray-5 mb-2">{{ $greeting }}, {{ $name }}</h6>
                                <p class="text-muted mb-0 small">Submit and manage your HMO requests</p>
                            </div>

                            <div class="attendance-circle-progress mx-auto mb-3" data-value='65'>
                                <span class="progress-left">
                                    <span class="progress-bar border-danger"></span>
                                </span>
                                <span class="progress-right">
                                    <span class="progress-bar border-danger"></span>
                                </span>
                                <div class="avatar avatar-xxl avatar-rounded bg-light-danger">
                                    <i class="ti ti-heart-plus text-danger" style="font-size: 48px;"></i>
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#add_hmo_request">
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
                                <div class="d-flex justify-content-between align-items-center p-2 bg-danger-transparent rounded">
                                    <span class="text-danger small fw-medium">Active HMO</span>
                                    <span class="badge badge-danger">{{ $activeHMO }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Request History -->
                <div class="col-xl-9 col-lg-8 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>My HMO Requests</h5>
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
                                <table class="table" id="hmoRequestsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>HMO Plan</th>
                                            <th>Coverage Type</th>
                                            <th>Dependents</th>
                                            <th>Effective Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6">
                                                <div class="text-center py-5">
                                                    <div class="avatar avatar-xl bg-light-danger mx-auto mb-3">
                                                        <i class="ti ti-heart-plus text-danger" style="font-size: 32px;"></i>
                                                    </div>
                                                    <h6 class="text-muted">No HMO requests yet</h6>
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

    <!-- Add HMO Request Modal -->
    <div class="modal fade" id="add_hmo_request" tabindex="-1" aria-labelledby="add_hmo_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_hmo_request_label">
                        <i class="ti ti-heart-plus me-2"></i>New HMO Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="hmoRequestForm" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hmo_plan" class="form-label">HMO Plan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="hmo_plan" name="hmo_plan" placeholder="e.g., Maxicare, Medicard" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="coverage_type" class="form-label">Coverage Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="coverage_type" name="coverage_type" required>
                                    <option value="">Select Coverage Type</option>
                                    <option value="Individual">Individual (Self Only)</option>
                                    <option value="Family">Family (With Dependents)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="number_of_dependents" class="form-label">Number of Dependents</label>
                                <input type="number" class="form-control" id="number_of_dependents" name="number_of_dependents" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="effective_date" class="form-label">Effective Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="effective_date" name="effective_date" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="dependent_details" class="form-label">Dependent Details</label>
                                <textarea class="form-control" id="dependent_details" name="dependent_details" rows="3" placeholder="List dependent names, relationship, and birthdates..."></textarea>
                                <small class="text-muted">e.g., Jane Doe (Spouse, 01/15/1990), John Doe (Son, 05/20/2015)</small>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="reason" class="form-label">Reason for Request <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Explain why you need this HMO coverage..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="attachment" class="form-label">Supporting Documents</label>
                                <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Upload birth certificates, marriage certificate, etc. - Maximum file size: 5MB</small>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>HMO enrollment is subject to company policy and plan availability. Processing may take 7-14 business days.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-check me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add HMO Request Modal -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#hmoRequestForm').on('submit', function(e) {
                e.preventDefault();
                // TODO: Implement form submission
                toastr.info('HMO request submission - Coming soon!');
            });

            // Show/hide dependent details based on coverage type
            $('#coverage_type').on('change', function() {
                if ($(this).val() === 'Family') {
                    $('#number_of_dependents').prop('required', true);
                    $('#dependent_details').prop('required', true);
                } else {
                    $('#number_of_dependents').prop('required', false).val(0);
                    $('#dependent_details').prop('required', false);
                }
            });
        });
    </script>
@endpush
