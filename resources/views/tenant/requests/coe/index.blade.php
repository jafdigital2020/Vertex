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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">COE Requests</li>
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
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_coe_request"
                                class="btn btn-primary d-flex align-items-center"><i
                                    class="ti ti-circle-plus me-2"></i>Complete Request</a>
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

            <!-- COE Request Counts -->
            <div class="row">

                <div class="col-xl-4 col-md-6">
                    <div class="card"
                        style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <!-- LEFT TEXT SECTION -->
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Approved Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalApprovedCOE }}
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
                                    {{ $totalPendingCOE }}
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
                                    {{ $totalRejectedCOE }}
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
            <!-- /COE Request Counts -->

            <!-- COE Request list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">COE Requests</h5>
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
                        <table class="table datatable" id="coeRequestsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Request Date</th>
                                    <th class="text-center">Purpose</th>
                                    <th class="text-center">Recipient</th>
                                    <th class="text-center">Needed By</th>
                                    <th class="text-center">Address To</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Approved By</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="coeRequestsTableBody">
                                @foreach ($coeRequests as $coe)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $coe->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $coe->user->personalInformation->last_name }},
                                                            {{ $coe->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $coe->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $coe->created_at ? \Carbon\Carbon::parse($coe->created_at)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ $coe->purpose ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $coe->recipient_name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            {{ $coe->needed_by_date ? \Carbon\Carbon::parse($coe->needed_by_date)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ Str::limit($coe->address_to ?? 'N/A', 30) }}</td>
                                        <td class="text-center">
                                            @php
                                                $badgeClass = 'badge-info';
                                                if ($coe->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($coe->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($coe->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($coe->approver_name ?? false)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $coe->approver_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $coe->approver_name }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $coe->approver_dept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td class="text-center">
                                                @if ($coe->status !== 'approved')
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_coe_request"
                                                                data-id="{{ $coe->id }}"
                                                                data-purpose="{{ $coe->purpose }}"
                                                                data-recipient-name="{{ $coe->recipient_name }}"
                                                                data-recipient-company="{{ $coe->recipient_company }}"
                                                                data-address-to="{{ $coe->address_to }}"
                                                                data-needed-by-date="{{ $coe->needed_by_date }}"><i
                                                                    class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="#" data-bs-toggle="modal" class="btn-delete"
                                                                data-bs-target="#delete_coe_request"
                                                                data-id="{{ $coe->id }}"
                                                                data-name="{{ $coe->user->personalInformation->full_name ?? 'N/A' }}"><i
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
            <!-- /COE Request list -->

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
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add COE Request Modal -->

    <!-- Edit COE Request Modal -->
    <div class="modal fade" id="edit_coe_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-2"></i>Edit COE Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCOERequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_purpose" name="purpose" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_recipient_name" class="form-label">Recipient Name</label>
                                <input type="text" class="form-control" id="edit_recipient_name" name="recipient_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_recipient_company" class="form-label">Recipient Company/Institution</label>
                                <input type="text" class="form-control" id="edit_recipient_company" name="recipient_company">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_address_to" class="form-label">Address To</label>
                                <textarea class="form-control" id="edit_address_to" name="address_to" rows="2"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_needed_by_date" class="form-label">Needed By Date</label>
                                <input type="date" class="form-control" id="edit_needed_by_date" name="needed_by_date">
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
    <!-- /Edit COE Request Modal -->

    <!-- Delete COE Request Modal -->
    <div class="modal fade" id="delete_coe_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete COE Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the COE request for <strong id="coeRequestPlaceholder"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmCOEDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete COE Request Modal -->

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
                url: '{{ route('coe-request-filter') }}',
                type: 'GET',
                data: {
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#coeRequestsTable').DataTable().destroy();
                        $('#coeRequestsTableBody').html(response.html);
                        $('#coeRequestsTable').DataTable();
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

    {{-- Request COE --}}
    <script>
        $(document).ready(function() {
            // Handle form submission
            $('#coeRequestForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this)[0];
                var formData = new FormData(form);

                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/coe-requests/employee/request') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('COE request submitted successfully.');
                            $('#add_coe_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to request COE.'));
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

    {{-- Edit Request COE --}}
    <script>
        $(document).ready(function() {
            // Populate modal when clicking edit
            $(document).on('click', 'a[data-bs-target="#edit_coe_request"]', function() {
                const id = $(this).data('id');
                $('#editCOERequestForm').data('id', id);

                $('#edit_purpose').val($(this).data('purpose'));
                $('#edit_recipient_name').val($(this).data('recipient-name'));
                $('#edit_recipient_company').val($(this).data('recipient-company'));
                $('#edit_address_to').val($(this).data('address-to'));
                $('#edit_needed_by_date').val($(this).data('needed-by-date'));
            });

            // Submit update AJAX
            $('#editCOERequestForm').on('submit', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: `/api/coe-requests/employee/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('COE request updated successfully.');
                            $('#edit_coe_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to update COE request.'));
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

    {{-- Delete Request COE --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let coeDeleteId = null;
            const confirmCOEDeleteBtn = document.getElementById('confirmCOEDeleteBtn');
            const coeRequestPlaceholder = document.getElementById('coeRequestPlaceholder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                coeDeleteId = button.getAttribute('data-id');
                const coeName = button.getAttribute('data-name');

                if (coeRequestPlaceholder) {
                    coeRequestPlaceholder.textContent = coeName;
                }
            });

            // Confirm delete
            confirmCOEDeleteBtn?.addEventListener('click', function() {
                if (!coeDeleteId) return;

                fetch(`/api/coe-requests/employee/delete/${coeDeleteId}`, {
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
                            toastr.success("COE request deleted successfully.");
                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_coe_request'));
                            deleteModal.hide();
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting COE request.");
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
