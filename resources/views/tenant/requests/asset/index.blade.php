<?php $page = 'request-asset'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Asset Requests</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Asset Requests</li>
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
                $totalAssetRequests = 0;
            @endphp

            <div class="row">
                <!-- Left Column - Quick Action Card -->
                <div class="col-xl-3 col-lg-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <h6 class="fw-medium text-gray-5 mb-2">{{ $greeting }}, {{ $name }}</h6>
                                <p class="text-muted mb-0 small">Submit and manage your asset requests</p>
                            </div>

                            <div class="attendance-circle-progress mx-auto mb-3" data-value='65'>
                                <span class="progress-left">
                                    <span class="progress-bar border-success"></span>
                                </span>
                                <span class="progress-right">
                                    <span class="progress-bar border-success"></span>
                                </span>
                                <div class="avatar avatar-xxl avatar-rounded bg-light-success">
                                    <i class="ti ti-building text-success" style="font-size: 48px;"></i>
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#add_asset_request">
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
                                <div class="d-flex justify-content-between align-items-center p-2 bg-success-transparent rounded">
                                    <span class="text-success small fw-medium">Total Requests</span>
                                    <span class="badge badge-success">{{ $totalAssetRequests }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Request History -->
                <div class="col-xl-9 col-lg-8 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>My Asset Requests</h5>
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
                                <table class="table datatable" id="assetRequestsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Asset Type</th>
                                            <th>Asset Name</th>
                                            <th>Quantity</th>
                                            <th>Urgency</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7">
                                                <div class="text-center py-5">
                                                    <div class="avatar avatar-xl bg-light-success mx-auto mb-3">
                                                        <i class="ti ti-building text-success" style="font-size: 32px;"></i>
                                                    </div>
                                                    <h6 class="text-muted">No asset requests yet</h6>
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

    <!-- Add Asset Request Modal -->
    <div class="modal fade" id="add_asset_request" tabindex="-1" aria-labelledby="add_asset_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_asset_request_label">
                        <i class="ti ti-building me-2"></i>New Asset Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assetRequestForm" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asset_type" class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_type" name="asset_type" placeholder="e.g., Laptop, Monitor, Chair" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="asset_name" class="form-label">Asset Name/Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name" placeholder="e.g., Dell XPS 15" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estimated_cost" class="form-label">Estimated Cost (Optional)</label>
                                <input type="number" class="form-control" id="estimated_cost" name="estimated_cost" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="urgency_level" class="form-label">Urgency Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="urgency_level" name="urgency_level" required>
                                    <option value="">Select Urgency</option>
                                    <option value="Low">Low - Can wait 2-4 weeks</option>
                                    <option value="Medium">Medium - Needed within 1-2 weeks</option>
                                    <option value="High">High - Needed within a week</option>
                                    <option value="Critical">Critical - Urgent, needed ASAP</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="2" placeholder="Why do you need this asset?" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="justification" class="form-label">Justification <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="justification" name="justification" rows="3" placeholder="Explain how this asset will help your work or improve productivity..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="attachment" class="form-label">Supporting Documents</label>
                                <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Upload specifications, quotations, or references - Maximum file size: 5MB</small>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>Asset requests are reviewed based on budget availability and business need. Approval time varies by urgency level.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Asset Request Modal -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#assetRequestForm').on('submit', function(e) {
                e.preventDefault();
                // TODO: Implement form submission
                toastr.info('Asset request submission - Coming soon!');
            });
        });
    </script>
@endpush
