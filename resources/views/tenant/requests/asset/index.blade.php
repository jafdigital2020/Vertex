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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Asset Requests</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Export', $permission))
                        {{-- Export dropdown --}}
                    @endif
                    @if (in_array('Create', $permission))
                        <div class="mb-2 me-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_asset_request"
                                class="btn btn-primary d-flex align-items-center"><i
                                    class="ti ti-circle-plus me-2"></i>Request Asset</a>
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

            <!-- Asset Request Counts -->
            <div class="row">

                <div class="col-xl-4 col-md-6">
                    <div class="card"
                        style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Approved Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalApprovedAssets }}
                                </h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>

                            <div class="position-relative d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-primary"
                                    style="position: absolute; right: -35%; top: 90%; transform: translateY(-55%); width: 140px; height: 140px; background: #fdeff4; border-radius: 50%; z-index: 1; clip-path: inset(0 0 0 0 round 12px);">
                                </div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary"
                                    style="position: relative; z-index: 2; width: 45px; height: 45px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.06); right: -10px; top: 20px;">
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
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Pending Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalPendingAssets }}
                                </h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>

                            <div class="position-relative d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-mustard"
                                    style="position: absolute; right: -35%; top: 90%; transform: translateY(-55%); width: 140px; height: 140px; background: #f4eeff; border-radius: 50%; z-index: 1; clip-path: inset(0 0 0 0 round 12px);">
                                </div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-mustard"
                                    style="position: relative; z-index: 2; width: 45px; height: 45px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.06); right: -10px; top: 20px;">
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
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Rejected Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">
                                    {{ $totalRejectedAssets }}
                                </h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>

                            <div class="position-relative d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-raspberry"
                                    style="position: absolute; right: -35%; top: 90%; transform: translateY(-55%); width: 140px; height: 140px; background: #fff2f2; border-radius: 50%; z-index: 1; clip-path: inset(0 0 0 0 round 12px);">
                                </div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-raspberry"
                                    style="position: relative; z-index: 2; width: 45px; height: 45px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.06); right: -10px; top: 20px;">
                                    <i class="ti ti-user-x fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Asset Request Counts -->

            <!-- Asset Request list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Asset Requests</h5>
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
                        <table class="table datatable" id="assetRequestsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Request Date</th>
                                    <th class="text-center">Asset Type</th>
                                    <th class="text-center">Asset Name</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Purpose</th>
                                    <th class="text-center">File Attachment</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Approved By</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="assetRequestsTableBody">
                                @foreach ($assetRequests as $asset)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $asset->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $asset->user->personalInformation->last_name }},
                                                            {{ $asset->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $asset->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $asset->created_at ? \Carbon\Carbon::parse($asset->created_at)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ $asset->asset_type ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $asset->asset_name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $asset->quantity ?? 'N/A' }}</td>
                                        <td class="text-center">{{ Str::limit($asset->purpose ?? 'N/A', 30) }}</td>
                                        <td class="text-center">
                                            @if ($asset->attachment)
                                                <a href="{{ asset('storage/' . $asset->attachment) }}"
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
                                                if ($asset->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($asset->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($asset->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($asset->approver_name ?? false)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $asset->approver_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $asset->approver_name }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $asset->approver_dept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td class="text-center">
                                                @if ($asset->status !== 'approved')
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_asset_request"
                                                                data-id="{{ $asset->id }}"
                                                                data-asset-type="{{ $asset->asset_type }}"
                                                                data-asset-name="{{ $asset->asset_name }}"
                                                                data-quantity="{{ $asset->quantity }}"
                                                                data-purpose="{{ $asset->purpose }}"
                                                                data-specifications="{{ $asset->specifications }}"
                                                                data-attachment="{{ $asset->attachment }}"><i
                                                                    class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="#" data-bs-toggle="modal" class="btn-delete"
                                                                data-bs-target="#delete_asset_request"
                                                                data-id="{{ $asset->id }}"
                                                                data-name="{{ $asset->user->personalInformation->full_name ?? 'N/A' }}"><i
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
            <!-- /Asset Request list -->

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    <!-- Add Asset Request Modal -->
    <div class="modal fade" id="add_asset_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-package me-2"></i>New Asset Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assetRequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asset_type" class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="asset_type" name="asset_type" required>
                                    <option value="">Select Asset Type</option>
                                    <option value="Computer">Computer</option>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Monitor">Monitor</option>
                                    <option value="Keyboard">Keyboard</option>
                                    <option value="Mouse">Mouse</option>
                                    <option value="Printer">Printer</option>
                                    <option value="Phone">Phone</option>
                                    <option value="Furniture">Furniture</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="asset_name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name" placeholder="Enter asset name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Explain why you need this asset..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="specifications" class="form-label">Specifications (Optional)</label>
                                <textarea class="form-control" id="specifications" name="specifications" rows="2" placeholder="Any specific requirements or specifications..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="attachment" class="form-label">Supporting Documents</label>
                                <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Upload any supporting documents - Maximum file size: 5MB</small>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>Asset requests require approval. Processing time may vary based on availability.</small>
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
    <!-- /Add Asset Request Modal -->

    <!-- Edit Asset Request Modal -->
    <div class="modal fade" id="edit_asset_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-edit me-2"></i>Edit Asset Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAssetRequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_asset_type" class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_asset_type" name="asset_type" required>
                                    <option value="">Select Asset Type</option>
                                    <option value="Computer">Computer</option>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Monitor">Monitor</option>
                                    <option value="Keyboard">Keyboard</option>
                                    <option value="Mouse">Mouse</option>
                                    <option value="Printer">Printer</option>
                                    <option value="Phone">Phone</option>
                                    <option value="Furniture">Furniture</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_asset_name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_asset_name" name="asset_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_purpose" name="purpose" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_specifications" class="form-label">Specifications</label>
                                <textarea class="form-control" id="edit_specifications" name="specifications" rows="2"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Current Attachment</label>
                                <div id="currentAssetAttachmentDisplay"></div>
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
    <!-- /Edit Asset Request Modal -->

    <!-- Delete Asset Request Modal -->
    <div class="modal fade" id="delete_asset_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Asset Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the asset request for <strong id="assetRequestPlaceholder"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmAssetDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Asset Request Modal -->

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
                url: '{{ route('asset-request-filter') }}',
                type: 'GET',
                data: { dateRange, status },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#assetRequestsTable').DataTable().destroy();
                        $('#assetRequestsTableBody').html(response.html);
                        $('#assetRequestsTable').DataTable();
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

    <script>
        $(document).ready(function() {
            $('#assetRequestForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData($(this)[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/asset-requests/employee/request') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Asset request submitted successfully.');
                            $('#add_asset_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message || 'Unable to request asset.'));
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

            $(document).on('click', 'a[data-bs-target="#edit_asset_request"]', function() {
                const id = $(this).data('id');
                $('#editAssetRequestForm').data('id', id);

                $('#edit_asset_type').val($(this).data('asset-type'));
                $('#edit_asset_name').val($(this).data('asset-name'));
                $('#edit_quantity').val($(this).data('quantity'));
                $('#edit_purpose').val($(this).data('purpose'));
                $('#edit_specifications').val($(this).data('specifications'));

                let attachment = $(this).data('attachment');
                let displayHtml = '';
                if (attachment && attachment !== 'null' && attachment !== '') {
                    displayHtml = `<a href="/storage/${attachment}" target="_blank" class="text-primary"><i class="ti ti-file"></i> View Current Attachment</a>`;
                }
                $('#currentAssetAttachmentDisplay').html(displayHtml);
                $('#edit_attachment').val('');
            });

            $('#editAssetRequestForm').on('submit', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                var formData = new FormData($(this)[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: `/api/asset-requests/employee/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Asset request updated successfully.');
                            $('#edit_asset_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message || 'Unable to update asset request.'));
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

        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            let assetDeleteId = null;
            const confirmAssetDeleteBtn = document.getElementById('confirmAssetDeleteBtn');
            const assetRequestPlaceholder = document.getElementById('assetRequestPlaceholder');

            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;
                assetDeleteId = button.getAttribute('data-id');
                const assetName = button.getAttribute('data-name');
                if (assetRequestPlaceholder) {
                    assetRequestPlaceholder.textContent = assetName;
                }
            });

            confirmAssetDeleteBtn?.addEventListener('click', function() {
                if (!assetDeleteId) return;

                fetch(`/api/asset-requests/employee/delete/${assetDeleteId}`, {
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
                        toastr.success("Asset request deleted successfully.");
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('delete_asset_request'));
                        deleteModal.hide();
                        filter();
                    } else {
                        return response.json().then(data => {
                            toastr.error(data.message || "Error deleting asset request.");
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
