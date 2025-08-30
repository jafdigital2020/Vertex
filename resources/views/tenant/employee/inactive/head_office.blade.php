<?php $page = 'inactive-employees'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Inactive Employees</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employees
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Inactive</li>
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
                                    <a href="#" class="dropdown-item rounded-1" data-bs-toggle="modal"
                                        data-bs-target="#exportModal">
                                        <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('downloadEmployeeTemplate') }}" class="dropdown-item rounded-1">
                                        <i class="ti ti-file-type-xls me-1"></i>Download Template
                                    </a>
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

            {{-- <div class="row">
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-dark rounded-circle"><i class="ti ti-users"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Total Employee</p>
                                    <h4></h4>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-success rounded-circle"><i
                                            class="ti ti-user-share"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Active Employees</p>
                                    <h4>
                                    </h4>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-danger rounded-circle"><i
                                            class="ti ti-user-pause"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">InActive Employees</p>
                                    <h4>
                                    </h4>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-info rounded-circle"><i
                                            class="ti ti-user-plus"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">New Hired</p>
                                    <h4>
                                        <h4>
                                        </h4>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            {{-- Links --}}
            <div class="payroll-btns mb-3">
                <a href="{{ route('inactive-employees') }}" class="btn btn-white active border me-2">Head Office</a>
                <a href="{{ route('inactive-security-guards') }}" class="btn btn-white border me-2">Security Guard</a>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Inactive Employee List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" oninput="inactiveHOfilter();"
                                style="width:150px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                              oninput="inactiveHOfilter();" style="width:150px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                               oninput="inactiveHOfilter();" style="width:150px;">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div> 
                        <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                              oninput="inactiveHOfilter();" style="width:150px;">
                                <option value="" selected>All Sort By</option>
                                <option value="ascending">Ascending</option>
                                <option value="descending">Descending</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_7_days">Last 7 days</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable-filtered" id="inactive_ho_employee_list_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Joining Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="inactive_ho_employee_list_tableBody">
                                @foreach ($employees as $employee)
                                    @php
                                        $detail = $employee->employmentDetail;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ url('employees/employee-details/' . $employee->id) }}"
                                                class="me-2" title="View Full Details"><i class="ti ti-eye"></i></a>
                                            {{ $detail->employee_id ?? '-' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                    data-bs-toggle="modal" data-bs-target="#view_details">
                                                    <img src="{{ asset('storage/' . ($employee->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0">
                                                        <a href="{{ url('employee-details') }}" data-bs-toggle="modal"
                                                            data-bs-target="#view_details">
                                                            {{ $employee->personalInformation->last_name ?? '' }}
                                                            {{ $employee->personalInformation->suffix ?? '' }},
                                                            {{ $employee->personalInformation->first_name ?? '' }}
                                                            {{ $employee->personalInformation->middle_name ?? '' }}
                                                        </a>
                                                    </p>
                                                    <span
                                                        class="fs-12">{{ $employee->employmentDetail->branch->name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->email ?? '-' }}</td>
                                        <td>{{ $detail?->department?->department_name ?? 'N/A' }}</td>
                                        <td>{{ $detail?->designation?->designation_name ?? 'N/A' }}</td>
                                        <td>{{ $detail->date_hired ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $status = (int) ($detail->status ?? -1);
                                                $statusText =
                                                    $status === 1 ? 'Active' : ($status === 0 ? 'Inactive' : 'Unknown');
                                                $badgeClass =
                                                    $status === 1
                                                        ? 'badge-success'
                                                        : ($status === 0
                                                            ? 'badge-danger'
                                                            : 'badge-secondary');
                                            @endphp
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs {{ $badgeClass }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                @if (in_array('Update', $permission))
                                                @if ($status == 0)
                                                    <a href="#" class="btn-activate me-2"
                                                        onclick="activateEmployee({{ $employee->id }})" title="Activate">
                                                        <i class="ti ti-circle-check"></i>
                                                    </a>
                                                @else
                                                    <a href="#" class="btn-deactivate me-2"
                                                        onclick="deactivateEmployee({{ $employee->id }})"
                                                        title="Deactivate">
                                                        <i class="ti ti-cancel"></i>
                                                    </a>
                                                @endif
                                                @endif
                                                @if (in_array('Delete', $permission))
                                                <a href="#" class="btn-delete"
                                                    onclick="deleteEmployee({{ $employee->id }})" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        @include('layout.partials.footer-company')

    </div>
    @component('components.modal-popup')
    @endcomponent

    <!-- Delete Modal -->
    <div class="modal fade" id="delete_modal">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="deleteEmployeeForm" enctype="multipart/form-data" onsubmit="deleteID()">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="modal-body text-center">
                        <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                            <i class="ti ti-trash-x fs-36"></i>
                        </span>
                        <h4 class="mb-1">Confirm Delete</h4>
                        <p class="mb-3">
                            Are you sure you want to delete <strong><span id="employeeNamePlaceholder">this
                                    employee</span></strong>? This can’t be undone.
                        </p>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Delete Modal -->

    <!-- Deactivate Modal -->
    <div class="modal fade" id="deactivate_modal">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="deactivateEmployeeForm" enctype="multipart/form-data">
                    <input type="hidden" name="deact_id" id="deact_id">
                    <div class="modal-body text-center">
                        <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                            <i class="ti ti-x fs-36"></i>
                        </span>
                        <h4 class="mb-1">Confirm Deactivate</h4>
                        <p class="mb-3">
                            Are you sure you want to deactivate <strong><span id="deactivateEmployeeName">this
                                    employee</span></strong>?
                        </p>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="confirmDeactivateBtn">Yes,
                                Deactivate</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Deactivate Modal -->

    <!-- Activate Modal -->
    <div class="modal fade" id="activate_modal">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form id="activateEmployeeForm" enctype="multipart/form-data" onsubmit="activateID()">
                    <input type="hidden" name="act_id" id="act_id">
                    <div class="modal-body text-center">
                        <span class="avatar avatar-xl bg-transparent-success text-success mb-3">
                            <i class="ti ti-check fs-36"></i>
                        </span>
                        <h4 class="mb-1">Confirm Activate</h4>
                        <p class="mb-3">
                            Are you sure you want to activate <strong><span id="activateEmployeeName">this
                                    employee</span></strong>?
                        </p>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" id="confirmActivateBtn">Yes, Activate</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Acctivate Modal -->
@endsection

@push('scripts')
    <script src="{{ asset('build/js/datatable-filtered.js') }}"></script>
    <script src="{{ asset('build/js/employeelist.js') }}"></script>
    <script>
        function inactiveHOfilter() { 
            var branch = $('#branch_filter').val();
            var department = $('#department_filter').val();
            var designation = $('#designation_filter').val();
            var sortBy = $('#sortby_filter').val();

            $.ajax({
                url: '{{ route('inactive-employees-filter') }}',
                type: 'GET',
                data: {
                    branch: branch,
                    department: department,
                    designation: designation, 
                    sortBy: sortBy,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#inactive_ho_employee_list_table').DataTable().destroy();
                        $('#inactive_ho_employee_list_tableBody').html(response.html);
                        $('#inactive_ho_employee_list_table').DataTable(); 
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
    <script>
        const routes = {
            employeeAdd: "{{ route('employeeAdd') }}",
            employeeEdit: "{{ route('employeeEdit') }}",
            employeeActivate: "{{ route('employeeActivate') }}",
            employeeDeactivate: "{{ route('employeeDeactivate') }}",
            employeeDelete: "{{ route('employeeDelete') }}",
            getEmployeeDetails: "{{ route('getEmployeeDetails') }}", 
            branchAutoFilter: "{{ route('branchAuto-filter') }}",
            departmentAutoFilter: "{{ route('departmentAuto-filter') }}",
            designationAutoFilter: "{{ route('designationAuto-filter') }}"
        };
    </script>
@endpush
