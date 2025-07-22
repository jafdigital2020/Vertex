<?php $page = 'bulk-attendance-admin'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Attendance Admin</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                             <li class="breadcrumb-item">
                                 Attendance
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Admin</li>
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
                                        <a href="{{ route('downloadAttendanceTemplate') }}"
                                            class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Download
                                            Template </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('downloadAttendanceBulkImportTemplate') }}"
                                            class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Download
                                            Bulk Import Template </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    @if (in_array('Create', $permission))
                        <div class="mb-2 d-flex gap-2">
                            <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#attendance_upload_modal">
                                <i class="ti ti-file-upload me-2"></i> Import Default Attendance
                            </a>
                            <a href="#" class="btn btn-secondary d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#bulk_attendance_upload_modal">
                                <i class="ti ti-file-upload me-2"></i> Import Bulk Attendance
                            </a>
                        </div>
                    @endif
                    <div class="ms-2 head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card border-0">
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-5">
                            <div class="mb-3 mb-md-0">
                                <h4 class="mb-1">Attendance Details Today</h4>
                                {{-- <p>Data from the 800+ total no of employees</p> --}}
                            </div>
                        </div>
                    </div>
                    <div class="border rounded">
                        <div class="row gx-0">
                            <div class="col-md col-sm-4 border-end">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">
                                        <i class="ti ti-check text-success me-1"></i> Present
                                    </span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $totalPresent }}</h5>
                                        <span class="badge bg-success-subtle text-success d-inline-flex align-items-center">
                                            <i class="ti ti-users me-1"></i> Employees
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md col-sm-4 border-end">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">
                                        <i class="ti ti-clock-edit text-warning me-1"></i> Late Login
                                    </span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $totalLate }}</h5>
                                        <span class="badge bg-warning-subtle text-warning d-inline-flex align-items-center">
                                            <i class="ti ti-clock me-1"></i> Late
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md col-sm-4">
                                <div class="p-3">
                                    <span class="fw-medium mb-1 d-block">
                                        <i class="ti ti-user-off text-danger me-1"></i> Absent
                                    </span>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ $totalAbsent }}</h5>
                                        <span class="badge bg-danger-subtle text-danger d-inline-flex align-items-center">
                                            <i class="ti ti-x me-1"></i> Absent
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="payroll-btns mb-3">
                <a href="{{ route('attendance-admin') }}" class="btn btn-white  border me-2">Head Office Attendance</a>
                <a href="{{ route('bulkAdminAttendanceIndex') }}" class="btn btn-white active border me-2">Security Guard Attendance</a>
                <a href="{{ route('adminRequestAttendance') }}" class="btn btn-white border me-2">Request
                    Attendance</a>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Admin Bulk Attendance</h5>
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
                        <div class="col-2 form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select ">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select">
                                <option value="" selected>All Status</option>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <button class="btn btn-primary" onclick="filter()"><i
                                    class="fas fa-filter me-2"></i>Filter</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th>Date From - To</th>
                                    <th>Working Days</th>
                                    <th>Regular Hours</th>
                                    <th>Regular OT Hours</th>
                                    <th>Regular ND Hours</th>
                                    <th>Regular ND + OT Hours</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="adminBulkAttTableBody">
                                @foreach ($bulkAttendances as $userAtt)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ URL::asset('build/img/users/user-49.jpg') }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $userAtt->user->personalInformation->last_name }},
                                                            {{ $userAtt->user->personalInformation->first_name }}
                                                            {{ $userAtt->user->personalInformation->middle_name }}.</a>
                                                    </h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $userAtt->user->employmentDetail->department->department_name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($userAtt->date_from && $userAtt->date_to)
                                                {{ \Carbon\Carbon::parse($userAtt->date_from)->format('F d, Y') }} -
                                                {{ \Carbon\Carbon::parse($userAtt->date_to)->format('F d, Y') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $userAtt->regular_working_days ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_working_hours ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_overtime_hours ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_nd_hours ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_nd_overtime_hours ?? 'N/A' }}</td>

                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td>
                                                <div class="action-icon d-inline-flex">
                                                    @if (in_array('Update', $permission))
                                                        <a href="#" class="me-2" data-bs-toggle="modal"
                                                            data-bs-target="#edit_bulk_attendance"
                                                            data-id="{{ $userAtt->id }}"
                                                            data-date-from="{{ $userAtt->date_from }}"
                                                            data-date-to="{{ $userAtt->date_to }}"
                                                            data-working-days="{{ $userAtt->regular_working_days }}"
                                                            data-regular-hours="{{ $userAtt->regular_working_hours }}"
                                                            data-ot-hours="{{ $userAtt->regular_overtime_hours }}"
                                                            data-nd-hours="{{ $userAtt->regular_nd_hours }}"
                                                            data-nd-ot-hours="{{ $userAtt->regular_nd_overtime_hours }}"
                                                            data-rest-day="{{ $userAtt->rest_day_work ? '1' : '0' }}"
                                                            data-rest-day-ot="{{ $userAtt->rest_day_ot ? '1' : '0' }}"
                                                            data-rest-day-nd="{{ $userAtt->rest_day_nd ? '1' : '0' }}"
                                                            data-regular-holiday="{{ $userAtt->regular_holiday_hours }}"
                                                            data-special-holiday="{{ $userAtt->special_holiday_hours }}"
                                                            data-regular-holiday-ot="{{ $userAtt->regular_holiday_ot }}"
                                                            data-special-holiday-ot="{{ $userAtt->special_holiday_ot }}"
                                                            data-regular-holiday-nd="{{ $userAtt->regular_holiday_nd }}"
                                                            data-special-holiday-nd="{{ $userAtt->special_holiday_nd }}"><i
                                                                class="ti ti-edit"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="me-2 btn-delete" data-bs-toggle="modal"
                                                            data-bs-target="#delete_bulk_attendance"
                                                            data-id="{{ $userAtt->id }}"
                                                            data-first-name="{{ $userAtt->user->personalInformation->full_name }}"><i
                                                                class="ti ti-trash"></i></a>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Modal -->
        <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Clock-In Location</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="mapModalContainer" style="width:100%;height:400px;"></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Upload Modal --}}
        <div class="modal fade" id="attendance_upload_modal" tabindex="-1" aria-labelledby="attendanceUploadLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('importAttendanceCSV') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="attendanceUploadLabel">Upload Attendance CSV(Per Row)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Select CSV File</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control"
                                    accept=".csv" required>
                                <small class="form-text text-muted">Ensure you use the correct template.
                                    <a href="{{ asset('templates/attendance_template.csv') }}" class="text-primary"
                                        target="_blank">
                                        Download Template
                                    </a>
                                </small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-white me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-upload me-1"></i> Import File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bulk Attendance Upload Modal --}}
        <div class="modal fade" id="bulk_attendance_upload_modal" tabindex="-1" aria-labelledby="attendanceUploadLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('bulkImportAttendanceCSV') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="attendanceUploadLabel">Upload Attendance CSV(Bulk)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Select CSV File</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control"
                                    accept=".csv" required>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-white me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-upload me-1"></i> Import File
                            </button>
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
        function filter() {
            var dateRange = $('#dateRange_filter').val();
            var branch = $('#branch_filter').val();
            var department = $('#department_filter').val();
            var designation = $('#designation_filter').val();
            var status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('bulkAdminAttendanceFilter') }}',
                type: 'GET',
                data: {
                    branch: branch,
                    department: department,
                    designation: designation,
                    dateRange: dateRange,
                    status: status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#adminBulkAttTableBody').html(response.html);
                    } else if (response.status === 'error') {
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
    {{-- Edit Attendance --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Edit Bulk Attendance Modal - Fill form with data attributes
            document.addEventListener("click", function(e) {
                const button = e.target.closest('[data-bs-target="#edit_bulk_attendance"]');
                if (!button) return;

                document.getElementById("bulkAttendanceId").value = button.dataset.id || '';
                document.getElementById("bulkAttendanceDateFrom").value = button.dataset.dateFrom || '';
                document.getElementById("bulkAttendanceDateTo").value = button.dataset.dateTo || '';
                document.getElementById("bulkAttendanceWorkingDays").value = button.dataset.workingDays ||
                    '';
                document.getElementById("bulkAttendanceRegularHours").value = button.dataset.regularHours ||
                    '';
                document.getElementById("bulkAttendanceOvertimeHours").value = button.dataset.otHours || '';
                document.getElementById("bulkAttendanceNDHours").value = button.dataset.ndHours || '';
                document.getElementById("bulkAttendanceNDOTHours").value = button.dataset.ndOtHours || '';
                document.getElementById("bulkAttendanceRegularHoliday").value = button.dataset
                    .regularHoliday || '';
                document.getElementById("bulkAttendanceSpecialHoliday").value = button.dataset
                    .specialHoliday || '';
                document.getElementById("bulkAttendanceRegularHolidayOT").value = button.dataset
                    .regularHolidayOt || '';
                document.getElementById("bulkAttendanceSpecialHolidayOT").value = button.dataset
                    .specialHolidayOt || '';
                document.getElementById("bulkAttendanceRegularHolidayND").value = button.dataset
                    .regularHolidayNd || '';
                document.getElementById("bulkAttendanceSpecialHolidayND").value = button.dataset
                    .specialHolidayNd || '';

                // Set checkboxes for Rest Day fields
                document.getElementById("bulkAttendanceRestDayRegular").checked = button.dataset.restDay ===
                    "1";
                document.getElementById("bulkAttendanceRestDayOT").checked = button.dataset.restDayOt ===
                    "1";
                document.getElementById("bulkAttendanceRestDayND").checked = button.dataset.restDayNd ===
                    "1";
            });

            // Handle update bulk attendance form submit
            document.getElementById("bulkAttendanceEdit")?.addEventListener("submit", async function(e) {
                e.preventDefault();

                const id = document.getElementById("bulkAttendanceId").value;
                const payload = {
                    date_from: document.getElementById("bulkAttendanceDateFrom").value,
                    date_to: document.getElementById("bulkAttendanceDateTo").value,
                    regular_working_days: document.getElementById("bulkAttendanceWorkingDays")
                        .value,
                    regular_working_hours: document.getElementById("bulkAttendanceRegularHours")
                        .value,
                    regular_overtime_hours: document.getElementById("bulkAttendanceOvertimeHours")
                        .value,
                    regular_nd_hours: document.getElementById("bulkAttendanceNDHours").value,
                    regular_nd_overtime_hours: document.getElementById("bulkAttendanceNDOTHours")
                        .value,
                    regular_holiday_hours: document.getElementById("bulkAttendanceRegularHoliday")
                        .value,
                    special_holiday_hours: document.getElementById("bulkAttendanceSpecialHoliday")
                        .value,
                    regular_holiday_ot: document.getElementById("bulkAttendanceRegularHolidayOT")
                        .value,
                    special_holiday_ot: document.getElementById("bulkAttendanceSpecialHolidayOT")
                        .value,
                    regular_holiday_nd: document.getElementById("bulkAttendanceRegularHolidayND")
                        .value,
                    special_holiday_nd: document.getElementById("bulkAttendanceSpecialHolidayND")
                        .value,
                    rest_day_work: document.getElementById("bulkAttendanceRestDayRegular").checked ?
                        1 : 0,
                    rest_day_ot: document.getElementById("bulkAttendanceRestDayOT").checked ? 1 : 0,
                    rest_day_nd: document.getElementById("bulkAttendanceRestDayND").checked ? 1 : 0,
                };

                // Basic validation
                if (!payload.date_from || !payload.date_to) {
                    toastr.error("Date From and Date To are required.");
                    return;
                }

                try {
                    const res = await fetch(`/api/attendance-admin/bulk-attendance/update/${id}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": `Bearer ${authToken}`,
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (res.ok) {
                        toastr.success("Bulk attendance updated successfully!");
                        $('#edit_bulk_attendance').modal('hide');
                        filter();
                    } else {
                        (data.errors ?
                            Object.values(data.errors).flat().forEach(msg => toastr.error(msg)) :
                            toastr.error(data.message || "Update failed.")
                        );
                    }
                } catch (err) {
                    console.error(err);
                    toastr.error("Something went wrong.");
                }
            });
        });
    </script>

    {{-- Delete --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const authToken = localStorage.getItem("token");
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const bulkAttendanceConfirmDeleteBtn = document.getElementById('bulkAttendanceConfirmDeleteBtn');
            const bulkUserPlaceholder = document.getElementById('bulkUserPlaceholder');

            // Set up the delete buttons to capture data
            document.addEventListener("click", function(e) {
                const button = e.target.closest('[data-bs-target="#delete_bulk_attendance"]');
                if (button) {
                    deleteId = button.getAttribute('data-id');
                    const userName = button.getAttribute('data-first-name');
                    if (bulkUserPlaceholder) {
                        bulkUserPlaceholder.textContent = userName;
                    }
                }
            });

            // Confirm delete button click event
            bulkAttendanceConfirmDeleteBtn?.addEventListener('click', function() {
                if (!deleteId) return; // Ensure both deleteId and userId are available

                fetch(`/api/attendance-admin/bulk-attendance/delete/${deleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute(
                                    "content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Attendance deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_bulk_attendance'));
                            deleteModal.hide();
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting attendance.");
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

    {{-- toastr for import --}}
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
     <script>
        function populateDropdown($select, items, placeholder = 'Select') {
            $select.empty();
            $select.append(`<option value="">All ${placeholder}</option>`);
            items.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $(document).ready(function() {

            $('#branch_filter').on('input', function() {
                const branchId = $(this).val();

                $.get('/api/filter-from-branch', {
                    branch_id: branchId
                }, function(res) {
                    if (res.status === 'success') {
                        populateDropdown($('#department_filter'), res.departments, 'Departments');
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });


            $('#department_filter').on('input', function() {
                const departmentId = $(this).val();
                const branchId = $('#branch_filter').val();

                $.get('/api/filter-from-department', {
                    department_id: departmentId,
                    branch_id: branchId,
                }, function(res) {
                    if (res.status === 'success') {
                        if (res.branch_id) {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                        }
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });

            $('#designation_filter').on('input', function() {
                const designationId = $(this).val();
                const branchId = $('#branch_filter').val();
                const departmentId = $('#department_filter').val();

                $.get('/api/filter-from-designation', {
                    designation_id: designationId,
                    branch_id: branchId,
                    department_id: departmentId
                }, function(res) {
                    if (res.status === 'success') {
                        if (designationId === '') {
                            populateDropdown($('#designation_filter'), res.designations,
                                'Designations');
                        } else {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                            $('#department_filter').val(res.department_id).trigger('change');
                        }
                    }
                });
            });
        });
    </script>
@endpush
