<?php $page = 'request-coe'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Certificate of Employment Requests</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">COE Requests</li>
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
                $totalCOE = 0;
            @endphp

            <div class="row">
                <!-- Left Column - Quick Action Card -->
                <div class="col-xl-3 col-lg-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <h6 class="fw-medium text-gray-5 mb-2">{{ $greeting }}, {{ $name }}</h6>
                                <p class="text-muted mb-0 small">Submit and manage your COE requests</p>
                            </div>

                            <div class="attendance-circle-progress mx-auto mb-3" data-value='65'>
                                <span class="progress-left">
                                    <span class="progress-bar border-warning"></span>
                                </span>
                                <span class="progress-right">
                                    <span class="progress-bar border-warning"></span>
                                </span>
                                <div class="avatar avatar-xxl avatar-rounded bg-light-warning">
                                    <i class="ti ti-certificate text-warning" style="font-size: 48px;"></i>
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#add_coe_request">
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
                                <div class="d-flex justify-content-between align-items-center p-2 bg-warning-transparent rounded">
                                    <span class="text-warning small fw-medium">Total Requests</span>
                                    <span class="badge badge-warning">{{ $totalCOE }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Request History -->
                <div class="col-xl-9 col-lg-8 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>My COE Requests</h5>
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
                                <table class="table" id="coeRequestsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Purpose</th>
                                            <th>Recipient</th>
                                            <th>Needed By</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6">
                                                <div class="text-center py-5">
                                                    <div class="avatar avatar-xl bg-light-warning mx-auto mb-3">
                                                        <i class="ti ti-certificate text-warning" style="font-size: 32px;"></i>
                                                    </div>
                                                    <h6 class="text-muted">No COE requests yet</h6>
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

    <!-- Add COE Request Modal -->
    <div class="modal fade" id="add_coe_request" tabindex="-1" aria-labelledby="add_coe_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_coe_request_label">
                        <i class="ti ti-certificate me-2"></i>New Certificate of Employment Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="coeRequestForm" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Why do you need a Certificate of Employment?" required></textarea>
                                <small class="text-muted">e.g., Visa Application, Bank Loan, Employment Verification, etc.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="recipient_name" class="form-label">Recipient Name</label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" placeholder="Who will receive this?">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="recipient_company" class="form-label">Recipient Company/Institution</label>
                                <input type="text" class="form-control" id="recipient_company" name="recipient_company" placeholder="Company or institution name">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="address_to" class="form-label">Address To</label>
                                <textarea class="form-control" id="address_to" name="address_to" rows="2" placeholder="To Whom It May Concern or specific addressee..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="request_date" class="form-label">Request Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="request_date" name="request_date" value="{{ date('Y-m-d') }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="needed_by_date" class="form-label">Needed By Date</label>
                                <input type="date" class="form-control" id="needed_by_date" name="needed_by_date" min="{{ date('Y-m-d') }}">
                                <small class="text-muted">When do you need this certificate?</small>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>COE requests are typically processed within 2-3 business days. Please allow sufficient time for approval and processing.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ti ti-check me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add COE Request Modal -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#coeRequestForm').on('submit', function(e) {
                e.preventDefault();
                // TODO: Implement form submission
                toastr.info('COE request submission - Coming soon!');
            });
        });
    </script>
@endpush
