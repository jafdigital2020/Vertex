<?php $page = 'ob-employee'; ?>
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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Official Business
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if(in_array('Export',$permission))
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
                    @if(in_array('Create',$permission))
                    <div class="mb-2 me-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_employee_ob"
                            class="btn btn-primary d-flex align-items-center"><i
                                class="ti ti-circle-plus me-2"></i>Request</a>
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

            <!-- Overtime Counts -->
            <div class="row">

                <div class="col-xl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Approved Request</p>
                                    <h4 id="totalApprovedOB">{{ $totalApprovedOB }}</h4>
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
                <div class="col-xl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Pending Request</p>
                                    <h4 id="totalPendingOB">{{ $totalPendingOB }}</h4>
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
                <div class="col-xl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Rejected</p>
                                    <h4 id="totalRejectedOB">{{ $totalRejectedOB }}</h4>
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
            </div>
            <!-- /Overtime Counts -->

            <!-- Performance Indicator list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Official Business</h5>
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
                        <table class="table datatable" id="obemployeeTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                   <th class="text-center">Date </th>
                                   <th class="text-center">Start & End Time</th>
                                   <th class="text-center">OB Hours</th>
                                   <th class="text-center">Purpose</th>
                                   <th class="text-center">File Attachment</th>
                                   <th class="text-center">Status</th>
                                   <th class="text-center">Approved By</th>
                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                   <th class="text-center">Action</th>
                                   @endif
                                </tr>
                            </thead>
                            <tbody id="obemployeeTableBody">
                                @foreach ($obEntries as $ob)
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
                                      <td class="text-center">
                                            {{ $ob->ob_date ? \Carbon\Carbon::parse($ob->ob_date)->format('F j, Y') : 'N/A' }}
                                        </td>
                                      <td class="text-center">
                                            @if ($ob->date_ob_in && $ob->date_ob_out)
                                                {{ \Carbon\Carbon::parse($ob->date_ob_in)->format('h:i A') }} -
                                                {{ \Carbon\Carbon::parse($ob->date_ob_out)->format('h:i A') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                      <td class="text-center">{{ $ob->ob_minutes_formatted }}</td>
                                      <td class="text-center">{{ $ob->purpose ?? 'N/A' }}</td>
                                      <td class="text-center">
                                            @if ($ob->file_attachment)
                                                <a href="{{ asset('storage/' . $ob->file_attachment) }}"
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
                                                if ($ob->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($ob->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($ob->status) }}
                                            </span>
                                        </td>
                                      <td class="text-center">
                                            @if ($ob->lastApproverName)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $ob->latestApproval->obApprover->personalInformation->profile_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $ob->lastApproverName }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $ob->lastApproverDept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                      <td class="text-center">
                                            @if ($ob->status !== 'approved')
                                                <div class="action-icon d-inline-flex">
                                                    @if(in_array('Update',$permission))
                                                    <a href="#" class="me-2" data-bs-toggle="modal"
                                                        data-bs-target="#edit_employee_ob" data-id="{{ $ob->id }}"
                                                        data-ob-date="{{ $ob->ob_date }}"
                                                        data-ob-in="{{ $ob->date_ob_in }}"
                                                        data-ob-out="{{ $ob->date_ob_out }}"
                                                        data-ob-break="{{ $ob->ob_break_minutes }}"
                                                        data-total-ob="{{ $ob->total_ob_minutes }}"
                                                        data-purpose="{{ $ob->purpose }}"
                                                        data-file-attachment="{{ $ob->file_attachment }}"><i
                                                            class="ti ti-edit"></i></a>
                                                        @endif
                                                    @if(in_array('Delete',$permission))
                                                    <a href="#" data-bs-toggle="modal" class="btn-delete"
                                                        data-bs-target="#delete_employee_ob"
                                                        data-id="{{ $ob->id }}"
                                                        data-name="{{ $ob->user->personalInformation->full_name ?? 'N/A' }}"><i
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
            <!-- /Performance Indicator list -->

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

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
            url: '{{ route('ob-employee-filter') }}',
            type: 'GET',
            data: {
                dateRange,
                status,
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#obemployeeTable').DataTable().destroy();
                    $('#obemployeeTableBody').html(response.html);
                    $('#obemployeeTable').DataTable();
                    $('#totalApprovedOB').text(response.totalApprovedOB);
                    $('#totalPendingOB').text(response.totalPendingOB);
                    $('#totalRejectedOB').text(response.totalRejectedOB);
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

    {{-- Request OB --}}
    <script>
        $(document).ready(function() {
            //  Start time and end time computation
            function formatMinutes(mins) {
                if (isNaN(mins) || mins <= 0) return '';
                var hr = Math.floor(mins / 60);
                var min = mins % 60;
                var text = '';
                if (hr > 0) text += hr + 'hr' + (hr > 1 ? 's ' : ' ');
                if (min > 0) text += min + 'min' + (min > 1 ? 's' : '');
                return text.trim();
            }

            function computeOvertimeMinutes() {
                var start = $('#employeeOBDateOBIn').val();
                var end = $('#employeeOBDateOBOut').val();
                var breakMins = parseInt($('#obBreakMinutes').val()) || 0;

                if (start && end) {
                    var startTime = new Date(start);
                    var endTime = new Date(end);

                    if (endTime > startTime) {
                        var diffMs = endTime - startTime;
                        var diffMins = Math.floor(diffMs / 1000 / 60);

                        var totalOBMins = diffMins - breakMins;
                        if (totalOBMins < 0) totalOBMins = 0;

                        $('#employeeTotalOBMinutes').val(formatMinutes(totalOBMins));
                        $('#employeeTotalOBMinutesHidden').val(totalOBMins);
                    } else {
                        $('#employeeTotalOBMinutes').val('');
                        $('#employeeTotalOBMinutesHidden').val('');
                    }
                } else {
                    $('#employeeTotalOBMinutes').val('');
                    $('#employeeTotalOBMinutesHidden').val('');
                }
            }

            $('#employeeOBDateOBIn, #employeeOBDateOBOut, #obBreakMinutes').on('change input', computeOvertimeMinutes);

            // Handle form submission
            $('#employeeOBForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this)[0];
                var formData = new FormData(form);

                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/official-business/employee/request') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('OB request submitted successfully.');
                            $('#add_employee_ob').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to request OB.'));
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

    {{-- Edit Request OB --}}
    <script>
        $(document).ready(function() {
            // Populate modal when clicking edit
            $(document).on('click', 'a[data-bs-target="#edit_employee_ob"]', function() {
                const id = $(this).data('id');
                $('#editOBForm').data('id', id); // store id on the form

                // Fix for date input
                let obDate = $(this).data('ob-date');
                if (obDate) {
                    obDate = obDate.toString().substring(0, 10);
                    $('#editEmployeeOBDate').val(obDate);
                } else {
                    $('#editEmployeeOBDate').val('');
                }
                $('#editEmployeeOBDateOBIn').val($(this).data('ob-in'));
                $('#editEmployeeOBDateOBOut').val($(this).data('ob-out'));

                // Set break minutes
                $('#editEmployeeOBBreakMinutes').val($(this).data('ob-break'));

                $('#editEmployeeOBPurpose').val($(this).data('purpose'));

                // Calculate & set readable total ob mins
                let mins = parseInt($(this).data('total-ob')) || 0;
                $('#editEmployeeTotalOBMinutes').val(formatMinutes(mins));
                $('#editEmployeeTotalOBMinutesHidden').val(mins);

                // Attachment logic
                let attachment = $(this).data('file-attachment');
                let displayHtml = '';
                if (attachment && attachment !== 'null' && attachment !== '') {
                    let url = `/storage/${attachment}`;
                    let filename = attachment.split('/').pop();
                    displayHtml = `<a href="${url}" target="_blank" class="text-primary">
            <i class="ti ti-file"></i> View Current Attachment
        </a>`;
                }
                $('#currentOBAttachmentFile').html(displayHtml);

                $('#editEmployeeOBFileAttachment').val('');
            });

            // Recompute minutes when user changes start/end/break
            function formatMinutes(mins) {
                if (isNaN(mins) || mins <= 0) return '';
                var hr = Math.floor(mins / 60);
                var min = mins % 60;
                var text = '';
                if (hr > 0) text += hr + 'hr' + (hr > 1 ? 's ' : ' ');
                if (min > 0) text += min + 'min' + (min > 1 ? 's' : '');
                return text.trim();
            }

            function computeOBMinutesEdit() {
                var start = $('#editEmployeeOBDateOBIn').val();
                var end = $('#editEmployeeOBDateOBOut').val();
                var breakMins = parseInt($('#editEmployeeOBBreakMinutes').val()) || 0;
                if (start && end) {
                    var startTime = new Date(start);
                    var endTime = new Date(end);
                    if (endTime > startTime) {
                        var diffMs = endTime - startTime;
                        var diffMins = Math.floor(diffMs / 1000 / 60);
                        var totalOBMins = diffMins - breakMins;
                        if (totalOBMins < 0) totalOBMins = 0;
                        $('#editEmployeeTotalOBMinutes').val(formatMinutes(totalOBMins));
                        $('#editEmployeeTotalOBMinutesHidden').val(totalOBMins);
                    } else {
                        $('#editEmployeeTotalOBMinutes').val('');
                        $('#editEmployeeTotalOBMinutesHidden').val('');
                    }
                } else {
                    $('#editEmployeeTotalOBMinutes').val('');
                    $('#editEmployeeTotalOBMinutesHidden').val('');
                }
            }
            $('#editEmployeeOBDateOBIn, #editEmployeeOBDateOBOut, #editEmployeeOBBreakMinutes').on('change input', computeOBMinutesEdit);

            // Submit update AJAX
            $('#editOBForm').on('submit', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');
                formData.set('total_ob_minutes', $('#editEmployeeTotalOBMinutesHidden').val());

                $.ajax({
                    type: 'POST',
                    url: `/api/official-business/employee/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Official business updated successfully.');
                            $('#edit_employee_ob').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to update official business.'));
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

    {{-- Delete Request OB --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let obDeleteId = null;
            const confirmOBEmployeeBtn = document.getElementById('confirmOBEmployeeBtn');
            const userOBPlaceholder = document.getElementById('userOBPlaceholder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                obDeleteId = button.getAttribute('data-id');
                const obName = button.getAttribute('data-name');

                if (userOBPlaceholder) {
                    userOBPlaceholder.textContent = obName;
                }
            });

            // Confirm delete
            confirmOBEmployeeBtn?.addEventListener('click', function() {
                if (!obDeleteId) return;

                fetch(`/api/official-business/employee/delete/${obDeleteId}`, {
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
                            toastr.success("Official business deleted successfully.");
                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_employee_ob'));
                            deleteModal.hide();
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting official business.");
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
