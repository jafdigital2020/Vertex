<?php $page = 'overtime-employee'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Overtime</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Overtime</li>
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
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_employee_overtime"
                                class="btn btn-primary d-flex align-items-center"><i
                                    class="ti ti-circle-plus me-2"></i>Manual
                                Overtime</a>
                        </div>
                        <div class="mb-2 me-2">
                            <a href="#" id="overtimeClockIn" class="btn btn-secondary d-flex align-items-center"><i
                                    class="ti ti-clock me-2"></i>Clock-In
                                Overtime</a>
                        </div>
                        <div class="mb-2 me-2">
                            <a href="#" id="overtimeClockOut" class="btn btn-secondary d-flex align-items-center"><i
                                    class="ti ti-clock me-2"></i>Clock-Out
                                Overtime</a>
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
                                    <h4 id="approvedRequests">{{ $approvedRequests }}</h4>
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
                                    <h4 id="pendingRequests">{{ $pendingRequests }}</h4>
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
                                    <h4 id="rejectedRequests">{{ $rejectedRequests }}</h4>
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
                    <h5>Employee Overtime</h5>
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
                        <table class="table datatable" id="overtimeEmployeeTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Date </th>
                                    <th class="text-center">Start & End Time</th>
                                    <th class="text-center">Overtime Hours</th>
                                    <th class="text-center">File Attachment</th>
                                    <th class="text-center">Offset Date</th>
                                    <th class="text-center">Approved By</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="overtimeEmployeeTableBody">
                                @foreach ($overtimes as $ot)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $ot->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $ot->user->personalInformation->last_name }},
                                                            {{ $ot->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $ot->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $ot->overtime_date ? $ot->overtime_date->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ot->date_ot_in ? $ot->date_ot_in->format('g:i A') : 'N/A' }} -
                                            {{ $ot->date_ot_out ? $ot->date_ot_out->format('g:i A') : 'N/A' }}
                                        </td>

                                        <td class="text-center">{{ $ot->total_ot_minutes_formatted }}</td>
                                        <td class="text-center">
                                            @if ($ot->file_attachment)
                                                <a href="{{ asset('storage/' . $ot->file_attachment) }}"
                                                    class="text-primary" target="_blank">
                                                    <i class="ti ti-file-text"></i> View Attachment
                                                </a>
                                            @else
                                                <span class="text-muted">No Attachment</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $ot->offset_date ? \Carbon\Carbon::parse($ot->offset_date)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            @if ($ot->lastApproverName)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $ot->latestApproval->otApprover->personalInformation->profile_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $ot->lastApproverName }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $ot->lastApproverDept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $badgeClass = 'badge-info';
                                                if ($ot->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($ot->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($ot->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($ot->status !== 'approved')
                                                <div class="action-icon d-inline-flex">
                                                    @if (in_array('Update', $permission))
                                                        <a href="#" class="me-2" data-bs-toggle="modal"
                                                            data-bs-target="#edit_employee_overtime"
                                                            data-id="{{ $ot->id }}"
                                                            data-overtime-date="{{ $ot->overtime_date }}"
                                                            data-ot-in="{{ $ot->date_ot_in }}"
                                                            data-ot-out="{{ $ot->date_ot_out }}"
                                                            data-total-ot="{{ $ot->total_ot_minutes }}"
                                                            data-file-attachment="{{ $ot->file_attachment }}"
                                                            data-reason="{{ $ot->reason }}"
                                                            data-offset-date="{{ $ot->offset_date }}"
                                                            data-status="{{ $ot->status }}"><i
                                                                class="ti ti-edit"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" data-bs-toggle="modal"
                                                            data-bs-target="#delete_employee_overtime"
                                                            data-id="{{ $ot->id }}"><i class="ti ti-trash"></i></a>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Performance Indicator list -->

        </div>

        {{-- Clock In Modal Popup --}}
        <div class="modal fade" id="clockInOvertimeModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="clockInOvertimeForm" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Clock-In Overtime</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">File Attachment</label>
                                <input type="file" class="form-control" name="file_attachment"
                                    id="clockInFileAttachment">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason <span class="text-danger">*</label>
                                <textarea class="form-control" name="reason" id="clockInReason" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Offset Date (optional)</label>
                                <input type="date" class="form-control" name="offset_date" id="clockInOffsetDate">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Clock-In</button>
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
    <script>
        if ($('.bookingrange-filtered').length > 0) {
            var start = moment().subtract(29, 'days');
            var end = moment();

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
                url: '{{ route('overtime-employee-filter') }}',
                type: 'GET',
                data: {
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#overtimeEmployeeTable').DataTable().destroy();
                        $('#overtimeEmployeeTableBody').html(response.html);
                        $('#overtimeEmployeeTable').DataTable();
                        $('#pendingRequests').text(response.pendingRequests);
                        $('#approvedRequests').text(response.approvedRequests);
                        $('#rejectedRequests').text(response.rejectedRequests);
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

    {{-- Manual Overtime (Create/Store) AJAX Form Submission --}}
    <script>
        $(document).ready(function() {
            // Clear error messages when modal is closed
            $('#add_employee_overtime').on('hidden.bs.modal', function() {
                $('#employeeOvertimeManualForm')[0].reset();
                $('.invalid-feedback').remove();
                $('.form-control, .form-select').removeClass('is-invalid');
                $('#employeeOvertimeTotalOtMinutes').val('');
                $('#employeeOvertimeTotalOtMinutesHidden').val('');
            });

            // Clear individual field errors on input
            $('#employeeOvertimeManualForm input, #employeeOvertimeManualForm select').on('input change',
                function() {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                });

            // Start time and end time computation
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
                var start = $('#employeeOvertimeDateOtIn').val();
                var end = $('#employeeOvertimeDateOtOut').val();

                if (start && end) {
                    var startTime = new Date(start);
                    var endTime = new Date(end);

                    if (endTime > startTime) {
                        var diffMs = endTime - startTime;
                        var diffMins = Math.floor(diffMs / 1000 / 60);

                        $('#employeeOvertimeTotalOtMinutes').val(formatMinutes(diffMins));
                        $('#employeeOvertimeTotalOtMinutesHidden').val(diffMins);

                        // Clear any end time errors if valid
                        $('#employeeOvertimeDateOtOut').removeClass('is-invalid');
                        $('#employeeOvertimeDateOtOut').siblings('.invalid-feedback').remove();
                    } else {
                        $('#employeeOvertimeTotalOtMinutes').val('');
                        $('#employeeOvertimeTotalOtMinutesHidden').val('');

                        // Show error for invalid end time
                        $('#employeeOvertimeDateOtOut').addClass('is-invalid');
                        $('#employeeOvertimeDateOtOut').siblings('.invalid-feedback').remove();
                        $('#employeeOvertimeDateOtOut').after(
                            '<div class="invalid-feedback d-block">End time must be after start time</div>');
                    }
                } else {
                    $('#employeeOvertimeTotalOtMinutes').val('');
                    $('#employeeOvertimeTotalOtMinutesHidden').val('');
                }
            }

            $('#employeeOvertimeDateOtIn, #employeeOvertimeDateOtOut').on('change input', computeOvertimeMinutes);

            // Handle form submission with better error handling
            $('#employeeOvertimeManualForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.invalid-feedback').remove();
                $('.form-control, .form-select').removeClass('is-invalid');

                // Client-side validation
                let hasError = false;
                const overtimeDate = $('#employeeOvertimeDate').val();
                const startTime = $('#employeeOvertimeDateOtIn').val();
                const endTime = $('#employeeOvertimeDateOtOut').val();
                const totalMinutes = $('#employeeOvertimeTotalOtMinutesHidden').val();

                if (!overtimeDate) {
                    $('#employeeOvertimeDate').addClass('is-invalid');
                    $('#employeeOvertimeDate').after(
                        '<div class="invalid-feedback d-block">Please select an overtime date</div>');
                    hasError = true;
                }

                if (!startTime) {
                    $('#employeeOvertimeDateOtIn').addClass('is-invalid');
                    $('#employeeOvertimeDateOtIn').after(
                        '<div class="invalid-feedback d-block">Please select a start time</div>');
                    hasError = true;
                }

                if (!endTime) {
                    $('#employeeOvertimeDateOtOut').addClass('is-invalid');
                    $('#employeeOvertimeDateOtOut').after(
                        '<div class="invalid-feedback d-block">Please select an end time</div>');
                    hasError = true;
                }

                if (startTime && endTime && new Date(endTime) <= new Date(startTime)) {
                    $('#employeeOvertimeDateOtOut').addClass('is-invalid');
                    $('#employeeOvertimeDateOtOut').after(
                        '<div class="invalid-feedback d-block">End time must be after start time</div>');
                    hasError = true;
                }

                if (!totalMinutes || totalMinutes <= 0) {
                    toastr.error('Overtime duration must be greater than 0 minutes');
                    hasError = true;
                }

                if (hasError) {
                    return false;
                }

                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                // Disable submit button to prevent double submission
                const $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/overtime-employee/create/manual') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Overtime request submitted successfully!');
                            $('#add_employee_overtime').modal('hide');
                            filter();
                        } else {
                            toastr.error(response.message ||
                                'Unable to submit overtime request');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validation errors from server
                            const errors = xhr.responseJSON.errors;

                            // Map backend field names to frontend field IDs
                            const fieldMapping = {
                                'overtime_date': '#employeeOvertimeDate',
                                'date_ot_in': '#employeeOvertimeDateOtIn',
                                'date_ot_out': '#employeeOvertimeDateOtOut',
                                'total_ot_minutes': '#employeeOvertimeTotalOtMinutesHidden',
                                'file_attachment': '#employeeOvertimeFileAttachment',
                                'offset_date': '#employeeOvertimeOffsetDate'
                            };

                            // Display each validation error below its field
                            $.each(errors, function(field, messages) {
                                const $field = $(fieldMapping[field]);
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    $field.after(
                                        `<div class="invalid-feedback d-block">${messages[0]}</div>`
                                    );
                                }
                            });

                            toastr.error('Please correct the errors in the form');
                        } else {
                            let msg = 'An error occurred while processing your request';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.status === 403) {
                                msg = 'You do not have permission to perform this action';
                            } else if (xhr.status === 500) {
                                msg = 'Server error. Please try again later';
                            }
                            toastr.error(msg);
                        }
                    },
                    complete: function() {
                        // Re-enable submit button
                        $submitBtn.prop('disabled', false).html('Submit');
                    }
                });
            });
        });
    </script>

    {{-- Manual Overtime (Edit) AJAX Form Submission --}}
    <script>
        $(document).ready(function() {
            // Clear errors when modal is closed
            $('#edit_employee_overtime').on('hidden.bs.modal', function() {
                $('.invalid-feedback').remove();
                $('.form-control, .form-select').removeClass('is-invalid');
                // optionally reset the form fields
                $('#editEmployeeOvertimeManualForm')[0].reset();
                $('#editEmployeeOvertimeTotalOtMinutes').val('');
                $('#editEmployeeOvertimeTotalOtMinutesHidden').val('');
                $('#currentOvertimeAttachment').html('');
            });

            // Clear individual field errors on input (include textarea)
            $('#editEmployeeOvertimeManualForm input, #editEmployeeOvertimeManualForm select, #editEmployeeOvertimeManualForm textarea')
                .on('input change', function() {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                });

            // Populate modal when clicking edit
            $(document).on('click', 'a[data-bs-target="#edit_employee_overtime"]', function() {
                // Clear previous errors
                $('.invalid-feedback').remove();
                $('.form-control, .form-select').removeClass('is-invalid');

                const id = $(this).data('id');
                $('#editEmployeeOvertimeManualForm').data('id', id);

                // Fix for date input
                let overtimeDate = $(this).data('overtime-date');
                if (overtimeDate) {
                    overtimeDate = overtimeDate.toString().substring(0, 10);
                    $('#editEmployeeOvertimeDate').val(overtimeDate);
                } else {
                    $('#editEmployeeOvertimeDate').val('');
                }

                // Fix for offset_date
                let offsetDate = $(this).data('offset-date');
                if (offsetDate) {
                    offsetDate = offsetDate.toString().substring(0, 10);
                    $('#editEmployeeOvertimeOffsetDate').val(offsetDate);
                } else {
                    $('#editEmployeeOvertimeOffsetDate').val('');
                }

                $('#editEmployeeOvertimeDateOtIn').val($(this).data('ot-in'));
                $('#editEmployeeOvertimeDateOtOut').val($(this).data('ot-out'));

                // Populate reason field
                let reason = $(this).data('reason');
                $('#editEmployeeOvertimeReason').val(reason || '');

                // Calculate & set readable total ot mins
                let mins = parseInt($(this).data('total-ot')) || 0;
                $('#editEmployeeOvertimeTotalOtMinutes').val(formatMinutes(mins));
                $('#editEmployeeOvertimeTotalOtMinutesHidden').val(mins);

                // Attachment logic
                let attachment = $(this).data('file-attachment');
                let displayHtml = '';
                if (attachment && attachment !== 'null' && attachment !== '') {
                    let url = `/storage/${attachment}`;
                    displayHtml = `<a href="${url}" target="_blank" class="text-primary">
                    <i class="ti ti-file"></i> View Current Attachment
                </a>`;
                }
                $('#currentOvertimeAttachment').html(displayHtml);
                $('#employeeOvertimeFileAttachment').val('');
            });

            // Recompute minutes when user changes start/end
            function formatMinutes(mins) {
                if (isNaN(mins) || mins <= 0) return '';
                var hr = Math.floor(mins / 60);
                var min = mins % 60;
                var text = '';
                if (hr > 0) text += hr + 'hr' + (hr > 1 ? 's ' : ' ');
                if (min > 0) text += min + 'min' + (min > 1 ? 's' : '');
                return text.trim();
            }

            function computeOvertimeMinutesEdit() {
                var start = $('#editEmployeeOvertimeDateOtIn').val();
                var end = $('#editEmployeeOvertimeDateOtOut').val();

                if (start && end) {
                    var startTime = new Date(start);
                    var endTime = new Date(end);

                    if (endTime > startTime) {
                        var diffMs = endTime - startTime;
                        var diffMins = Math.floor(diffMs / 1000 / 60);
                        $('#editEmployeeOvertimeTotalOtMinutes').val(formatMinutes(diffMins));
                        $('#editEmployeeOvertimeTotalOtMinutesHidden').val(diffMins);

                        // Clear errors
                        $('#editEmployeeOvertimeDateOtOut').removeClass('is-invalid');
                        $('#editEmployeeOvertimeDateOtOut').siblings('.invalid-feedback').remove();
                    } else {
                        $('#editEmployeeOvertimeTotalOtMinutes').val('');
                        $('#editEmployeeOvertimeTotalOtMinutesHidden').val('');

                        // Show error
                        $('#editEmployeeOvertimeDateOtOut').addClass('is-invalid');
                        $('#editEmployeeOvertimeDateOtOut').siblings('.invalid-feedback').remove();
                        $('#editEmployeeOvertimeDateOtOut').after(
                            '<div class="invalid-feedback d-block">End time must be after start time</div>');
                    }
                } else {
                    $('#editEmployeeOvertimeTotalOtMinutes').val('');
                    $('#editEmployeeOvertimeTotalOtMinutesHidden').val('');
                }
            }

            $('#editEmployeeOvertimeDateOtIn, #editEmployeeOvertimeDateOtOut').on('change input',
                computeOvertimeMinutesEdit);

            // Submit update AJAX
            $('#editEmployeeOvertimeManualForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.invalid-feedback').remove();
                $('.form-control, .form-select').removeClass('is-invalid');

                // Client-side validation
                let hasError = false;
                const overtimeDate = $('#editEmployeeOvertimeDate').val();
                const startTime = $('#editEmployeeOvertimeDateOtIn').val();
                const endTime = $('#editEmployeeOvertimeDateOtOut').val();
                const totalMinutes = $('#editEmployeeOvertimeTotalOtMinutesHidden').val();
                const reason = $('#editEmployeeOvertimeReason').val();

                if (!overtimeDate) {
                    $('#editEmployeeOvertimeDate').addClass('is-invalid');
                    $('#editEmployeeOvertimeDate').after(
                        '<div class="invalid-feedback d-block">Please select an overtime date</div>');
                    hasError = true;
                }

                if (!startTime) {
                    $('#editEmployeeOvertimeDateOtIn').addClass('is-invalid');
                    $('#editEmployeeOvertimeDateOtIn').after(
                        '<div class="invalid-feedback d-block">Please select a start time</div>');
                    hasError = true;
                }

                if (!endTime) {
                    $('#editEmployeeOvertimeDateOtOut').addClass('is-invalid');
                    $('#editEmployeeOvertimeDateOtOut').after(
                        '<div class="invalid-feedback d-block">Please select an end time</div>');
                    hasError = true;
                }

                if (startTime && endTime && new Date(endTime) <= new Date(startTime)) {
                    $('#editEmployeeOvertimeDateOtOut').addClass('is-invalid');
                    $('#editEmployeeOvertimeDateOtOut').after(
                        '<div class="invalid-feedback d-block">End time must be after start time</div>');
                    hasError = true;
                }

                if (!totalMinutes || totalMinutes <= 0) {
                    toastr.error('Overtime duration must be greater than 0 minutes');
                    hasError = true;
                }

                if (!reason || reason.trim() === '') {
                    $('#editEmployeeOvertimeReason').addClass('is-invalid');
                    $('#editEmployeeOvertimeReason').after(
                        '<div class="invalid-feedback d-block">Please provide a reason</div>');
                    hasError = true;
                }

                if (hasError) {
                    return false;
                }

                const id = $(this).data('id');
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');
                formData.set('total_ot_minutes', $('#editEmployeeOvertimeTotalOtMinutesHidden').val());

                // Disable submit button
                const $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

                $.ajax({
                    type: 'POST',
                    url: `/api/overtime-employee/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Overtime updated successfully!');
                            $('#edit_employee_overtime').modal('hide');
                            filter();
                        } else {
                            toastr.error(response.message || 'Unable to update overtime');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;

                            const fieldMapping = {
                                'overtime_date': '#editEmployeeOvertimeDate',
                                'date_ot_in': '#editEmployeeOvertimeDateOtIn',
                                'date_ot_out': '#editEmployeeOvertimeDateOtOut',
                                'total_ot_minutes': '#editEmployeeOvertimeTotalOtMinutesHidden',
                                'file_attachment': '#employeeOvertimeFileAttachment',
                                'offset_date': '#editEmployeeOvertimeOffsetDate',
                                'reason': '#editEmployeeOvertimeReason'
                            };

                            $.each(errors, function(field, messages) {
                                const $field = $(fieldMapping[field]);
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    $field.after(
                                        `<div class="invalid-feedback d-block">${messages[0]}</div>`
                                        );
                                }
                            });

                            toastr.error('Please correct the errors in the form');
                        } else {
                            let msg = 'An error occurred while processing your request';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.status === 403) {
                                msg = 'You do not have permission to perform this action';
                            } else if (xhr.status === 500) {
                                msg = 'Server error. Please try again later';
                            }
                            toastr.error(msg);
                        }
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html('Update');
                    }
                });
            });
        });
    </script>

    {{-- Manual Overtime (Delete) AJAX --}}
    <script>
        let overtimeDeleteId = null;

        $(document).ready(function() {
            // Store the ID when clicking delete
            $(document).on('click', 'a[data-bs-target="#delete_employee_overtime"]', function() {
                overtimeDeleteId = $(this).data('id');
            });

            // Handle delete confirmation
            $('#employeeOvertimeDeleteBtn').on('click', function() {
                if (overtimeDeleteId) {
                    $.ajax({
                        type: 'DELETE',
                        url: `/api/overtime-employee/delete/${overtimeDeleteId}/`,
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success('Overtime deleted successfully.');
                                $('#delete_employee_overtime').modal('hide');
                                filter();
                            } else {
                                toastr.error('Error: ' + (response.message ||
                                    'Unable to delete overtime.'));
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
                }
            });
        });
    </script>

    {{-- Clock-In Button Overtime --}}
    <script>
        $(document).ready(function() {
            // Open modal on Clock-In button
            $('#overtimeClockIn').on('click', function(e) {
                e.preventDefault();
                $('#clockInFileAttachment').val('');
                $('#clockInOffsetDate').val('');
                $('#clockInOvertimeModal').modal('show');
            });
            $('#employeeOvertimeDateOtOut').on('change', function() {
                let start = new Date($('#employeeOvertimeDateOtIn').val());
                let end = new Date($(this).val());

                if (start && end && end < start) {
                    toastr.error('End time cannot be earlier than start time.');
                    $(this).val('');
                }
            });
            // Handle form submit
            $('#clockInOvertimeForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '/api/overtime-employee/clock-in',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('You have clocked in for overtime!');
                            $('#clockInOvertimeModal').modal('hide');
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            toastr.error(response.message ||
                                'Unable to clock in for overtime.');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred while clocking in.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });
        });
    </script>

    {{-- Clock-Out Button Overtime --}}
    <script>
        $('#overtimeClockOut').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: '/api/overtime-employee/clock-out',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('You have clocked out from overtime!');
                        $('#overtimeClockOut').addClass('disabled').attr('disabled', true);
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message || 'Unable to clock out for overtime.');
                    }
                },
                error: function(xhr) {
                    let msg = 'An error occurred while clocking out.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                }
            });
        });
    </script>
@endpush
