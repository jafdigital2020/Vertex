<?php $page = 'ob-admin'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Official Business</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i> Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Official Business</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export / Download
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
                                <li>
                                    <a href="#" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Download
                                        Template</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#uploadOvertimeCSVModal"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-upload me-2"></i>Upload
                            Official Business</a>
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Overtime Counts -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Pending Count</p>
                                    <h4>{{ $pendingCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-pink-transparent border border-pink d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-edit text-pink fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Approved Request</p>
                                    <h4>{{ $approvedCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-transparent-purple border border-purple d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-exclamation text-purple fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Rejected</p>
                                    <h4>{{ $rejectedCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-skyblue-transparent border border-skyblue d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-exclamation text-skyblue fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">OB Requests</p>
                                    <h4>{{ $totalOBRequests }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-transparent-primary border border-primary d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-check text-primary fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Official Business Counts -->

            <!-- Performance Indicator list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Official Business</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2" style="max-width:200px;">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" oninput="filter()">
                                <option value="" selected>All Branches</option>

                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                oninput="filter()">
                                <option value="" selected>All Departments</option>

                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                oninput="filter()">
                                <option value="" selected>All Designations</option>

                            </select>
                        </div>

                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                oninput="filter()">
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
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Date </th>
                                    <th>Start & End Time</th>
                                    <th>OB Hours</th>
                                    <th>Purpose</th>
                                    <th>File Attachment</th>
                                    <th>Status</th>
                                    <th>Next Approver</th>
                                    <th>Last Approved By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="obAdminTableBody">

                                @foreach ($obEntries as $ob)
                                    @php
                                        $status = strtolower($ob->status);
                                        $colors = [
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'pending' => 'info',
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $ob->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $ob->user->personalInformation->last_name }},
                                                            {{ $ob->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $ob->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $ob->ob_date ? \Carbon\Carbon::parse($ob->ob_date)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $ob->date_ob_in ? \Carbon\Carbon::parse($ob->date_ob_in)->format('g:i A') : 'N/A' }} -
                                            {{ $ob->date_ob_out ? \Carbon\Carbon::parse($ob->date_ob_out)->format('g:i A') : 'N/A' }}
                                        </td>
                                        <td>{{ $ob->ob_minutes_formatted }}</td>
                                        <td>{{ $ob->purpose ?? 'N/A' }}</td>
                                        <td>
                                            @if ($ob->file_attachment)
                                                <a href="{{ asset('storage/' . $ob->file_attachment) }}"
                                                    class="text-primary" target="_blank">
                                                    <i class="ti ti-file-text"></i> View Attachment
                                                </a>
                                            @else
                                                <span class="text-muted">No Attachment</span>
                                            @endif
                                        </td>
                                        <td>
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
                                                            class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'approved' ? 'active' : '' }}"
                                                            data-action="approved" data-official-id="{{ $ob->id }}"
                                                            data-bs-toggle="modal" data-bs-target="#approvalModal">
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
                                                            class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'rejected' ? 'active' : '' }}"
                                                            data-action="rejected" data-official-id="{{ $ob->id }}"
                                                            data-bs-toggle="modal" data-bs-target="#approvalModal">
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
                                                            class="dropdown-item d-flex align-items-center js-approve {{ $status === 'pending' ? 'active' : '' }}"
                                                            data-action="CHANGES_REQUESTED"
                                                            data-official-id="{{ $ob->id }}">
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
                                        <td>
                                            @if (count($ob->next_approvers))
                                                {{ implode(', ', $ob->next_approvers) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex flex-column">
                                                {{-- 1) Approver name --}}
                                                <span class="fw-semibold">
                                                    {{ $ob->last_approver ?? '—' }}
                                                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="right"
                                                        data-bs-title="{{ $ob->latestApproval->comment ?? 'No comment' }}">
                                                        <i class="ti ti-info-circle text-info"></i></a>
                                                </span>
                                                {{-- Approval date/time --}}
                                                @if ($ob->latestApproval)
                                                    <small class="text-muted mt-1">
                                                        {{ \Carbon\Carbon::parse($ob->latestApproval->acted_at)->format('d M Y, h:i A') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_admin_overtime"
                                                    data-id="{{ $ob->id }}"><i class="ti ti-edit"></i></a>
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_admin_overtime" data-id="{{ $ob->id }}"
                                                    data-user-name="{{ $ob->user->personalInformation->first_name }} {{ $ob->user->personalInformation->last_name }}"><i
                                                        class="ti ti-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /OB list -->
        </div>

        <!-- Approval Comment Modal -->
        <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="approvalForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="approvalModalLabel">Add Approval Comment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="modalOBId">
                            <input type="hidden" id="modalAction">
                            <div class="mb-3">
                                <label for="modalComment" class="form-label">Comment</label>
                                <textarea id="modalComment" class="form-control" rows="3"></textarea>
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

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Approvers Steps --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));

            // 1) Open modal for both Approve & Reject buttons
            document.addEventListener('click', function(event) {
                if (event.target.closest('.js-approve-btn')) {
                    const btn = event.target.closest('.js-approve-btn');
                    document.getElementById('modalOBId').value = btn.dataset.officialId;
                    document.getElementById('modalAction').value = btn.dataset.action;
                    document.getElementById('modalComment').value = '';
                    document.getElementById('approvalModalLabel').textContent =
                        btn.dataset.action === 'approved' ? 'Approve with comment' :
                        btn.dataset.action === 'rejected' ? 'Reject with comment' :
                        'Request Changes with comment';
                    // Log officialId to console
                    console.log('OB ID Passed:', btn.dataset.officialId);
                }
            });


            // 2) Submit the modal form for both Approve & Reject
            document.getElementById('approvalForm').addEventListener('submit', async e => {
                e.preventDefault();

                const obId = document.getElementById('modalOBId').value;
                const action = document.getElementById('modalAction').value;
                const comment = document.getElementById('modalComment').value.trim();
                const url = action === 'rejected' ?
                    `/api/official-business/admin/${obId}/reject` :
                    `/api/official-business/admin/${obId}/approve`;

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

                    // show only the message text on error
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Failed to update status.');
                    }

                    const json = await res.json();
                    toastr.success(json.message);

                    modal.hide();

                } catch (err) {
                    console.error(err);
                    toastr.error(err.message);
                }
            });
        });
    </script>

    <script>
        @if (session('toastr_success'))
            toastr.success("{!! session('toastr_success') !!}");
        @endif

        @if (session('toastr_error'))
            toastr.error("{!! session('toastr_error') !!}");
        @endif

        @if (session('toastr_details') && is_array(session('toastr_details')))
            let details = `{!! implode('<br>', session('toastr_details')) !!}`;
            toastr.info(details);
        @endif
    </script>
@endpush
