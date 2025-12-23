<?php $page = 'loan-requests'; ?>
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
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Loan Requests</li>
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

            <!-- Loan Requests Info -->
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #12515D 0%, #2A9D8F 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Approved Loans</p>
                                <h2 id="approvedLoansCount" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($approvedLoansCount ?? 0, 2, '0', STR_PAD_LEFT) }}
                                </h2>
                                <small class="text-white-75">This Month</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-currency-dollar" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-currency-dollar" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
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
                                <p class="fs-12 fw-medium mb-1 text-white-75">Rejected Loans</p>
                                <h2 id="rejectedLoansCount" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($rejectedLoansCount ?? 0, 2, '0', STR_PAD_LEFT) }}
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
                                <h2 id="pendingLoansCount" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($pendingLoansCount ?? 0, 2, '0', STR_PAD_LEFT) }}
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
            <!-- /Loan Requests Info -->

            <!-- Loan Requests list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Loan Request List</h5>

                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <!-- Bulk Actions Dropdown -->
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-teal dropdown-toggle text-white" type="button" id="bulkActionsDropdownLoan"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Bulk Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdownLoan">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                        id="bulkApproveLoan">
                                        <i class="ti ti-check me-2 text-success"></i>
                                        <span>Approve</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                        id="bulkRejectLoan">
                                        <i class="ti ti-x me-2 text-danger"></i>
                                        <span>Reject</span>
                                    </a>
                                </li>
                                @if (in_array('Delete', $permission))
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                            id="bulkDeleteLoan">
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
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRangeLoan_filter">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="loantype_filter" id="loantype_filter" class="select2 form-select"
                                oninput="filterLoan()">
                                <option value="" selected>All Loan Types</option>
                                @foreach ($loanTypes ?? [] as $loantype)
                                    <option value="{{ $loantype->id }}">{{ $loantype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="statusLoan_filter" class="select2 form-select"
                                oninput="filterLoan()">
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
                        <table class="table datatable" id="adminLoanTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all-loan">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">Loan Type</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Repayment Period</th>
                                    <th class="text-center">Request Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="adminLoanTableBody">
                                @if (in_array('Read', $permission))
                                    @foreach ($loanRequests ?? [] as $lr)
                                        @php
                                            $status = strtolower($lr->status);
                                            $colors = [
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'pending' => 'primary',
                                            ];
                                        @endphp

                                        <tr data-loan-id="{{ $lr->id }}">
                                            <td>
                                                <div class="form-check form-check-md">
                                                    <input class="form-check-input" type="checkbox"
                                                        value="{{ $lr->id }}">
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
                                                                href="javascript:void(0);">{{ $lr->user->personalInformation->last_name ?? '' }},
                                                                {{ $lr->user->personalInformation->first_name ?? '' }}</a>
                                                        </h6>
                                                        <span
                                                            class="fs-12 fw-normal ">{{ $lr->user->employmentDetail->department->department_name ?? 'No Department' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                                                        {{ $lr->loan_type ?? 'N/A' }}</p>
                                                    <a href="#" class="ms-2" data-bs-toggle="tooltip"
                                                        data-bs-placement="right" title="{{ $lr->purpose ?? 'No purpose provided' }}">
                                                        <i class="ti ti-info-circle text-info"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($lr->amount ?? 0, 2) }}
                                            </td>
                                            <td class="text-center">
                                                {{ $lr->repayment_period ?? 'N/A' }} months
                                            </td>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($lr->created_at)->format('d M Y') }}
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
                                                                class="dropdown-item d-flex align-items-center js-approve-btn-loan {{ $status === 'approved' ? 'active' : '' }}"
                                                                data-action="APPROVED" data-loan-id="{{ $lr->id }}"
                                                                data-bs-toggle="modal" data-bs-target="#approvalModalLoan">
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
                                                                class="dropdown-item d-flex align-items-center js-approve-btn-loan {{ $status === 'rejected' ? 'active' : '' }}"
                                                                data-action="REJECTED" data-loan-id="{{ $lr->id }}"
                                                                data-bs-toggle="modal" data-bs-target="#approvalModalLoan">
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
                                                                data-loan-id="{{ $lr->id }}">
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
                                                            data-bs-target="#loan_view" data-id="{{ $lr->id }}"><i
                                                                class="ti ti-eye"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="btn-delete-loan" data-bs-toggle="modal"
                                                            data-bs-target="#loan_delete"
                                                            data-id="{{ $lr->id }}"
                                                            data-name="{{ $lr->user->personalInformation->first_name ?? '' }} {{ $lr->user->personalInformation->last_name ?? '' }}"><i
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
            <!-- /Loan Requests list -->

            <!-- Approval Comment Modal -->
            <div class="modal fade" id="approvalModalLoan" tabindex="-1" aria-labelledby="approvalModalLoanLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="approvalFormLoan">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="approvalModalLoanLabel">Add Approval Comment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="modalLoanId">
                                <input type="hidden" id="modalActionLoan">
                                <div class="mb-3">
                                    <label for="modalCommentLoan" class="form-label">Comment</label>
                                    <textarea id="modalCommentLoan" class="form-control" rows="3"></textarea>
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
            <div class="modal fade" id="loan_delete" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Delete Loan Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the loan request for <strong id="userLoanPlaceHolder"></strong>?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="loanRequestConfirmBtn">Delete</button>
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

        $('#dateRangeLoan_filter').on('apply.daterangepicker', function(ev, picker) {
            filterLoan();
        });

        function filterLoan() {
            const dateRange = $('#dateRangeLoan_filter').val();
            const status = $('#statusLoan_filter').val();
            const loantype = $('#loantype_filter').val();
            // TODO: Implement AJAX filter for loan requests
            console.log('Filter:', { dateRange, status, loantype });
        }
    </script>

    <!-- Approve/Reject Loan Request -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = new bootstrap.Modal(document.getElementById('approvalModalLoan'));

            document.addEventListener('click', function(event) {
                if (event.target.closest('.js-approve-btn-loan')) {
                    const btn = event.target.closest('.js-approve-btn-loan');
                    document.getElementById('modalLoanId').value = btn.dataset.loanId;
                    document.getElementById('modalActionLoan').value = btn.dataset.action;
                    document.getElementById('modalCommentLoan').value = '';
                    document.getElementById('approvalModalLoanLabel').textContent =
                        btn.dataset.action === 'APPROVED' ? 'Approve with comment' :
                        btn.dataset.action === 'REJECTED' ? 'Reject with comment' :
                        'Request Changes with comment';
                }
            });

            document.getElementById('approvalFormLoan').addEventListener('submit', async e => {
                e.preventDefault();

                const loanId = document.getElementById('modalLoanId').value;
                const action = document.getElementById('modalActionLoan').value;
                const comment = document.getElementById('modalCommentLoan').value.trim();
                // TODO: Update with actual API endpoint
                const url = action === 'REJECTED' ?
                    `/api/loan/loan-request/${loanId}/reject` :
                    `/api/loan/loan-request/${loanId}/approve`;

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

    <!-- Delete Loan Request -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let deleteId = null;
            const loanRequestConfirmBtn = document.getElementById('loanRequestConfirmBtn');
            const userLoanPlaceHolder = document.getElementById('userLoanPlaceHolder');

            $(document).on('click', '.btn-delete-loan', function() {
                deleteId = $(this).data('id');
                const userName = $(this).data('name');

                if (userLoanPlaceHolder) {
                    userLoanPlaceHolder.textContent = userName;
                }
            });

            loanRequestConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                // TODO: Update with actual API endpoint
                fetch(`/api/loan/loan-request/${deleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Loan request deleted successfully.");
                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('loan_delete'));
                            deleteModal.hide();
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting loan request.");
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
