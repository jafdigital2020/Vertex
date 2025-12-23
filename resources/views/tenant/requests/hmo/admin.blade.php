<?php $page = 'hmo-requests'; ?>
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
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Export', $permission))
                        <div class="me-2 mb-2">
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

            <!-- HMO Requests Info -->
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #12515D 0%, #2A9D8F 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Approved HMO</p>
                                <h2 id="approvedHMOCount" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($approvedHMOCount ?? 0, 2, '0', STR_PAD_LEFT) }}
                                </h2>
                                <small class="text-white-75">This Month</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-heart-plus" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-heart-plus" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #b53654 0%, #f2848c 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Rejected HMO</p>
                                <h2 id="rejectedHMOCount" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($rejectedHMOCount ?? 0, 2, '0', STR_PAD_LEFT) }}
                                </h2>
                                <small class="text-white-75">This Month</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-x" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-x" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #ed7464 0%, #f9c6b8 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Pending Requests</p>
                                <h2 id="pendingHMOCount" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($pendingHMOCount ?? 0, 2, '0', STR_PAD_LEFT) }}
                                </h2>
                                <small class="text-white-75">This Month</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-clock" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-clock" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /HMO Requests Info -->

            <!-- HMO Requests list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">HMO Request List</h5>

                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <!-- Bulk Actions Dropdown -->
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-teal dropdown-toggle text-white" type="button" id="bulkActionsDropdownHMO"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Bulk Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdownHMO">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                        id="bulkApproveHMO">
                                        <i class="ti ti-check me-2 text-success"></i>
                                        <span>Approve</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                        id="bulkRejectHMO">
                                        <i class="ti ti-x me-2 text-danger"></i>
                                        <span>Reject</span>
                                    </a>
                                </li>
                                @if (in_array('Delete', $permission))
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                            id="bulkDeleteHMO">
                                            <i class="ti ti-trash me-2 text-danger"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRangeHMO_filter">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="coverage_filter" id="coverage_filter" class="select2 form-select"
                                oninput="filterHMO()">
                                <option value="" selected>All Coverage Types</option>
                                <option value="individual">Individual</option>
                                <option value="family">Family</option>
                                <option value="spouse">Spouse</option>
                                <option value="dependent">Dependent</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="statusHMO_filter" class="select2 form-select"
                                oninput="filterHMO()">
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
                        <table class="table datatable" id="adminHMOTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all-hmo">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">HMO Plan</th>
                                    <th class="text-center">Coverage Type</th>
                                    <th class="text-center">Dependents</th>
                                    <th class="text-center">Effective Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="adminHMOTableBody">
                                @if (in_array('Read', $permission))
                                    @foreach ($hmoRequests ?? [] as $hr)
                                        @php
                                            $status = strtolower($hr->status);
                                            $colors = [
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'pending' => 'primary',
                                            ];
                                        @endphp

                                        <tr data-hmo-id="{{ $hr->id }}">
                                            <td>
                                                <div class="form-check form-check-md">
                                                    <input class="form-check-input" type="checkbox"
                                                        value="{{ $hr->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center file-name-icon">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ URL::asset('build/img/users/user-32.jpg') }}"
                                                            class="img-fluid" alt="img">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium"><a
                                                                href="javascript:void(0);">{{ $hr->user->personalInformation->last_name ?? '' }},
                                                                {{ $hr->user->personalInformation->first_name ?? '' }}</a>
                                                        </h6>
                                                        <span
                                                            class="fs-12 fw-normal ">{{ $hr->user->employmentDetail->department->department_name ?? 'No Department' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <p class="fs-14 fw-medium mb-0">
                                                    {{ $hr->hmo_plan ?? 'N/A' }}</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    {{ ucfirst($hr->coverage_type ?? 'Individual') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                                                        {{ $hr->dependents_count ?? 0 }}</p>
                                                    @if ($hr->dependents_count > 0)
                                                        <a href="#" class="ms-2" data-bs-toggle="tooltip"
                                                            data-bs-placement="right" title="{{ $hr->dependents_names ?? 'View dependents list' }}">
                                                            <i class="ti ti-info-circle text-info"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($hr->effective_date)->format('d M Y') }}
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown" style="position: static; overflow: visible;">
                                                    <a href="#"
                                                        class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                                                        data-bs-toggle="dropdown">
                                                        <span
                                                            class="rounded-circle bg-transparent-{{ $colors[$status] }} d-flex justify-content-center align-items-center me-2">
                                                            <i class="ti ti-point-filled text-{{ $colors[$status] }}"></i>
                                                        </span>
                                                        {{ ucfirst($status) }}
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end p-3">
                                                        <li>
                                                            <a href="#"
                                                                class="dropdown-item d-flex align-items-center js-approve-btn-hmo {{ $status === 'approved' ? 'active' : '' }}"
                                                                data-action="APPROVED" data-hmo-id="{{ $hr->id }}"
                                                                data-bs-toggle="modal" data-bs-target="#approvalModalHMO">
                                                                <span
                                                                    class="rounded-circle bg-transparent-{{ $colors['approved'] }} d-flex justify-content-center align-items-center me-2">
                                                                    <i
                                                                        class="ti ti-point-filled text-{{ $colors['approved'] }}"></i>
                                                                </span>
                                                                Approved
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#"
                                                                class="dropdown-item d-flex align-items-center js-approve-btn-hmo {{ $status === 'rejected' ? 'active' : '' }}"
                                                                data-action="REJECTED" data-hmo-id="{{ $hr->id }}"
                                                                data-bs-toggle="modal" data-bs-target="#approvalModalHMO">
                                                                <span
                                                                    class="rounded-circle bg-transparent-{{ $colors['rejected'] }} d-flex justify-content-center align-items-center me-2">
                                                                    <i
                                                                        class="ti ti-point-filled text-{{ $colors['rejected'] }}"></i>
                                                                </span>
                                                                Rejected
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#"
                                                                class="dropdown-item d-flex align-items-center {{ $status === 'pending' ? 'active' : '' }}"
                                                                data-action="CHANGES_REQUESTED"
                                                                data-hmo-id="{{ $hr->id }}">
                                                                <span
                                                                    class="rounded-circle bg-transparent-{{ $colors['pending'] }} d-flex justify-content-center align-items-center me-2">
                                                                    <i
                                                                        class="ti ti-point-filled text-{{ $colors['pending'] }}"></i>
                                                                </span>
                                                                Pending
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-icon d-inline-flex">
                                                    @if (in_array('Update', $permission))
                                                        <a href="#" class="me-2" data-bs-toggle="modal"
                                                            data-bs-target="#hmo_view" data-id="{{ $hr->id }}"><i
                                                                class="ti ti-eye"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="btn-delete-hmo" data-bs-toggle="modal"
                                                            data-bs-target="#hmo_delete"
                                                            data-id="{{ $hr->id }}"
                                                            data-name="{{ $hr->user->personalInformation->first_name ?? '' }} {{ $hr->user->personalInformation->last_name ?? '' }}"><i
                                                                class="ti ti-trash"></i></a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /HMO Requests list -->

            <!-- Approval Comment Modal -->
            <div class="modal fade" id="approvalModalHMO" tabindex="-1" aria-labelledby="approvalModalHMOLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="approvalFormHMO">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="approvalModalHMOLabel">Add Approval Comment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="modalHMOId">
                                <input type="hidden" id="modalActionHMO">
                                <div class="mb-3">
                                    <label for="modalCommentHMO" class="form-label">Comment</label>
                                    <textarea id="modalCommentHMO" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="hmo_delete" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Delete HMO Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the HMO request for <strong id="userHMOPlaceHolder"></strong>?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="hmoRequestConfirmBtn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->
@endsection

@push('scripts')
    <!-- Date Range Picker JS -->
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

        $('#dateRangeHMO_filter').on('apply.daterangepicker', function(ev, picker) {
            filterHMO();
        });

        function filterHMO() {
            const dateRange = $('#dateRangeHMO_filter').val();
            const status = $('#statusHMO_filter').val();
            const coverage = $('#coverage_filter').val();
            // TODO: Implement AJAX filter for HMO requests
            console.log('Filter:', { dateRange, status, coverage });
        }
    </script>

    <!-- Approve/Reject HMO Request -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = new bootstrap.Modal(document.getElementById('approvalModalHMO'));

            document.addEventListener('click', function(event) {
                if (event.target.closest('.js-approve-btn-hmo')) {
                    const btn = event.target.closest('.js-approve-btn-hmo');
                    document.getElementById('modalHMOId').value = btn.dataset.hmoId;
                    document.getElementById('modalActionHMO').value = btn.dataset.action;
                    document.getElementById('modalCommentHMO').value = '';
                    document.getElementById('approvalModalHMOLabel').textContent =
                        btn.dataset.action === 'APPROVED' ? 'Approve with comment' :
                        btn.dataset.action === 'REJECTED' ? 'Reject with comment' :
                        'Request Changes with comment';
                }
            });

            document.getElementById('approvalFormHMO').addEventListener('submit', async e => {
                e.preventDefault();

                const hmoId = document.getElementById('modalHMOId').value;
                const action = document.getElementById('modalActionHMO').value;
                const comment = document.getElementById('modalCommentHMO').value.trim();
                // TODO: Update with actual API endpoint
                const url = action === 'REJECTED' ?
                    `/api/hmo/hmo-request/${hmoId}/reject` :
                    `/api/hmo/hmo-request/${hmoId}/approve`;

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            action,
                            comment
                        }),
                    });

                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Failed to update status.');
                    }

                    const json = await res.json();
                    toastr.success(json.message);

                    modal.hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } catch (err) {
                    console.error(err);
                    toastr.error(err.message);
                }
            });
        });
    </script>

    <!-- Delete HMO Request -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let deleteId = null;
            const hmoRequestConfirmBtn = document.getElementById('hmoRequestConfirmBtn');
            const userHMOPlaceHolder = document.getElementById('userHMOPlaceHolder');

            $(document).on('click', '.btn-delete-hmo', function() {
                deleteId = $(this).data('id');
                const userName = $(this).data('name');

                if (userHMOPlaceHolder) {
                    userHMOPlaceHolder.textContent = userName;
                }
            });

            hmoRequestConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                // TODO: Update with actual API endpoint
                fetch(`/api/hmo/hmo-request/${deleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("HMO request deleted successfully.");
                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('hmo_delete'));
                            deleteModal.hide();
                            setTimeout(() => window.location.reload(), 1000);
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
