<?php $page = 'request-hmo'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">HMO Requests</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Employee</li>
                            <li class="breadcrumb-item active" aria-current="page">HMO Requests</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Create', $permission))
                        <div class="mb-2 me-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_hmo_request"
                                class="btn btn-primary d-flex align-items-center"><i
                                    class="ti ti-circle-plus me-2"></i>Request HMO</a>
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

            <!-- HMO Request Counts -->
            <div class="row">
                <div class="col-xl-4 col-md-6">
                    <div class="card" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Approved Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">{{ $totalApprovedHMO }}</h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>
                            <div class="position-relative d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-primary" style="position: absolute; right: -35%; top: 90%; transform: translateY(-55%); width: 140px; height: 140px; background: #fdeff4; border-radius: 50%; z-index: 1; clip-path: inset(0 0 0 0 round 12px);"></div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary" style="position: relative; z-index: 2; width: 45px; height: 45px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.06); right: -10px; top: 20px;">
                                    <i class="ti ti-user-check fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Pending Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">{{ $totalPendingHMO }}</h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>
                            <div class="position-relative d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-mustard" style="position: absolute; right: -35%; top: 90%; transform: translateY(-55%); width: 140px; height: 140px; background: #f4eeff; border-radius: 50%; z-index: 1; clip-path: inset(0 0 0 0 round 12px);"></div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-mustard" style="position: relative; z-index: 2; width: 45px; height: 45px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.06); right: -10px; top: 20px;">
                                    <i class="ti ti-user-exclamation fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: none; overflow: hidden;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <h6 class="fw-medium text-gray-5 mb-1" style="font-size: 14px;">Rejected Request</h6>
                                <h3 class="mb-1 fw-bold mt-4" style="line-height: 1; font-size: 20px; color: #212529;">{{ $totalRejectedHMO }}</h3>
                                <p class="fw-medium text-muted mb-0" style="font-size: 12px;">Requests</p>
                            </div>
                            <div class="position-relative d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; overflow: visible;">
                                <div class="bg-light-raspberry" style="position: absolute; right: -35%; top: 90%; transform: translateY(-55%); width: 140px; height: 140px; background: #fff2f2; border-radius: 50%; z-index: 1; clip-path: inset(0 0 0 0 round 12px);"></div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-raspberry" style="position: relative; z-index: 2; width: 45px; height: 45px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.06); right: -10px; top: 20px;">
                                    <i class="ti ti-user-x fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /HMO Request Counts -->

            <!-- HMO Request list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">HMO Requests</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                <span class="input-icon-addon"><i class="ti ti-chevron-down"></i></span>
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
                        <table class="table datatable" id="hmoRequestsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Request Date</th>
                                    <th class="text-center">HMO Type</th>
                                    <th class="text-center">Coverage Type</th>
                                    <th class="text-center">Dependents</th>
                                    <th class="text-center">Purpose</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Approved By</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="hmoRequestsTableBody">
                                @foreach ($hmoRequests as $hmo)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $hmo->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="#">{{ $hmo->user->personalInformation->last_name }}, {{ $hmo->user->personalInformation->first_name }}</a></h6>
                                                    <span class="fs-12 fw-normal ">{{ $hmo->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $hmo->created_at ? \Carbon\Carbon::parse($hmo->created_at)->format('F j, Y') : 'N/A' }}</td>
                                        <td class="text-center">{{ $hmo->hmo_type ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $hmo->coverage_type ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $hmo->dependents ?? '0' }}</td>
                                        <td class="text-center">{{ Str::limit($hmo->purpose ?? 'N/A', 30) }}</td>
                                        <td class="text-center">
                                            @php
                                                $badgeClass = 'badge-info';
                                                if ($hmo->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($hmo->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($hmo->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($hmo->approver_name ?? false)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $hmo->approver_picture) }}" class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">{{ $hmo->approver_name }}</h6>
                                                        <span class="fs-12 fw-normal">{{ $hmo->approver_dept }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td class="text-center">
                                                @if ($hmo->status !== 'approved')
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal" data-bs-target="#edit_hmo_request"
                                                                data-id="{{ $hmo->id }}" data-hmo-type="{{ $hmo->hmo_type }}"
                                                                data-coverage-type="{{ $hmo->coverage_type }}" data-dependents="{{ $hmo->dependents }}"
                                                                data-purpose="{{ $hmo->purpose }}" data-medical-history="{{ $hmo->medical_history }}"><i class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="#" data-bs-toggle="modal" class="btn-delete" data-bs-target="#delete_hmo_request"
                                                                data-id="{{ $hmo->id }}" data-name="{{ $hmo->user->personalInformation->full_name ?? 'N/A' }}"><i class="ti ti-trash"></i></a>
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
            <!-- /HMO Request list -->

        </div>
        @include('layout.partials.footer-company')
    </div>

    <!-- Add HMO Request Modal -->
    <div class="modal fade" id="add_hmo_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-healthrecognition me-2"></i>New HMO Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="hmoRequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hmo_type" class="form-label">HMO Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="hmo_type" name="hmo_type" required>
                                    <option value="">Select HMO Type</option>
                                    <option value="Standard">Standard</option>
                                    <option value="Premium">Premium</option>
                                    <option value="Platinum">Platinum</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="coverage_type" class="form-label">Coverage Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="coverage_type" name="coverage_type" required>
                                    <option value="">Select Coverage</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Family">Family</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="dependents" class="form-label">Number of Dependents</label>
                                <input type="number" class="form-control" id="dependents" name="dependents" min="0" value="0">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Explain the purpose of this request..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="medical_history" class="form-label">Medical History (Optional)</label>
                                <textarea class="form-control" id="medical_history" name="medical_history" rows="2" placeholder="Any pre-existing conditions..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>HMO requests require approval. Processing time may take 5-7 business days.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit HMO Request Modal -->
    <div class="modal fade" id="edit_hmo_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-edit me-2"></i>Edit HMO Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editHMORequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_hmo_type" class="form-label">HMO Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_hmo_type" name="hmo_type" required>
                                    <option value="">Select HMO Type</option>
                                    <option value="Standard">Standard</option>
                                    <option value="Premium">Premium</option>
                                    <option value="Platinum">Platinum</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_coverage_type" class="form-label">Coverage Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_coverage_type" name="coverage_type" required>
                                    <option value="">Select Coverage</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Family">Family</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_dependents" class="form-label">Number of Dependents</label>
                                <input type="number" class="form-control" id="edit_dependents" name="dependents" min="0">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_purpose" name="purpose" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_medical_history" class="form-label">Medical History</label>
                                <textarea class="form-control" id="edit_medical_history" name="medical_history" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete HMO Request Modal -->
    <div class="modal fade" id="delete_hmo_request" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete HMO Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the HMO request for <strong id="hmoRequestPlaceholder"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmHMODeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

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
                url: '{{ route('hmo-request-filter') }}',
                type: 'GET',
                data: { dateRange, status },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#hmoRequestsTable').DataTable().destroy();
                        $('#hmoRequestsTableBody').html(response.html);
                        $('#hmoRequestsTable').DataTable();
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
            $('#hmoRequestForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData($(this)[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/hmo-requests/employee/request') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('HMO request submitted successfully.');
                            $('#add_hmo_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message || 'Unable to request HMO.'));
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred while processing your request.');
                    }
                });
            });

            $(document).on('click', 'a[data-bs-target="#edit_hmo_request"]', function() {
                const id = $(this).data('id');
                $('#editHMORequestForm').data('id', id);

                $('#edit_hmo_type').val($(this).data('hmo-type'));
                $('#edit_coverage_type').val($(this).data('coverage-type'));
                $('#edit_dependents').val($(this).data('dependents'));
                $('#edit_purpose').val($(this).data('purpose'));
                $('#edit_medical_history').val($(this).data('medical-history'));
            });

            $('#editHMORequestForm').on('submit', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                var formData = new FormData($(this)[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: `/api/hmo-requests/employee/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('HMO request updated successfully.');
                            $('#edit_hmo_request').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message || 'Unable to update HMO request.'));
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred while processing your request.');
                    }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            let hmoDeleteId = null;
            const confirmHMODeleteBtn = document.getElementById('confirmHMODeleteBtn');
            const hmoRequestPlaceholder = document.getElementById('hmoRequestPlaceholder');

            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;
                hmoDeleteId = button.getAttribute('data-id');
                const hmoName = button.getAttribute('data-name');
                if (hmoRequestPlaceholder) {
                    hmoRequestPlaceholder.textContent = hmoName;
                }
            });

            confirmHMODeleteBtn?.addEventListener('click', function() {
                if (!hmoDeleteId) return;

                fetch(`/api/hmo-requests/employee/delete/${hmoDeleteId}`, {
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
                        toastr.success("HMO request deleted successfully.");
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('delete_hmo_request'));
                        deleteModal.hide();
                        filter();
                    } else {
                        return response.json().then(data => {
                            toastr.error(data.message || "Error deleting HMO request.");
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
