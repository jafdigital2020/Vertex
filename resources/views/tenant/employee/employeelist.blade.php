<?php $page = 'employees'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee List</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
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
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Download Template</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#bulk_uploadEmployee"
                            class="btn btn-secondary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Bulk Upload
                        </a>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_employee"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add Employee
                        </a>
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

            <div class="row">
                <!-- Total Plans -->
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-dark rounded-circle"><i class="ti ti-users"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Total Employee</p>
                                    <h4>1007</h4>
                                </div>
                            </div>
                            <div>
                                <span class="badge badge-soft-purple badge-sm fw-normal">
                                    <i class="ti ti-arrow-wave-right-down"></i>
                                    +19.01%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Total Plans -->

                <!-- Total Plans -->
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-success rounded-circle"><i
                                            class="ti ti-user-share"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Active</p>
                                    <h4>1007</h4>
                                </div>
                            </div>
                            <div>
                                <span class="badge badge-soft-primary badge-sm fw-normal">
                                    <i class="ti ti-arrow-wave-right-down"></i>
                                    +19.01%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Total Plans -->

                <!-- Inactive Plans -->
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-danger rounded-circle"><i
                                            class="ti ti-user-pause"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">InActive</p>
                                    <h4>1007</h4>
                                </div>
                            </div>
                            <div>
                                <span class="badge badge-soft-dark badge-sm fw-normal">
                                    <i class="ti ti-arrow-wave-right-down"></i>
                                    +19.01%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Inactive Companies -->

                <!-- No of Plans  -->
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-info rounded-circle"><i
                                            class="ti ti-user-plus"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">New Joiners</p>
                                    <h4>67</h4>
                                </div>
                            </div>
                            <div>
                                <span class="badge badge-soft-secondary badge-sm fw-normal">
                                    <i class="ti ti-arrow-wave-right-down"></i>
                                    +19.01%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /No of Plans -->
            </div>

            @php
                $selectedBranch = $branches->where('id', $selectedBranchId)->first();
                $branchLabel = $selectedBranch ? $selectedBranch->name : 'All Branches';

                $selectedDepartment = $departments->where('id', $selectedDepartmentId)->first();
                $departmentLabel = $selectedDepartment ? $selectedDepartment->department_name : ' All Departments';

                $selectedDesignation = $designations->where('id', $selectedDesignationId)->first();
                $designationLabel = $selectedDesignation
                    ? $selectedDesignation->designation_name
                    : ' All Designations ';
            @endphp

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee List</h5>
                    {{-- Search Filter --}}
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="branchDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ $branchLabel }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 branch-filter"
                                        data-id="" data-name="All Branches">
                                        All Branches
                                    </a>
                                </li>
                                @foreach ($branches as $branch)
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1 branch-filter"
                                            data-id="{{ $branch->id }}" data-name="{{ $branch->name }}">
                                            {{ $branch->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="departmentDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ $departmentLabel }}
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter"
                                        data-id="" data-name="All Departments">All Departments</a>
                                </li>
                                @foreach ($departments as $department)
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter"
                                            data-id="{{ $department->id }}"
                                            data-name="{{ $department->department_name }}">{{ $department->department_name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="designationDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ $designationLabel }}
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 designation-filter"
                                        data-id="" data-name="All Designations">All Designations</a>
                                </li>
                                @foreach ($designations as $designation)
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1 designation-filter"
                                            data-id="{{ $designation->id }}"
                                            data-name="{{ $designation->designation_name }}">{{ $designation->designation_name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="statusDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ $selectedStatus ? ucfirst($selectedStatus) : 'Status' }}
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 status-filter"
                                        data-value="active">Active</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 status-filter"
                                        data-value="inactive">Inactive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" id="sortDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Sort By:
                                @if ($selectedSort == 'recently_added')
                                    Recently Added
                                @elseif ($selectedSort == 'asc')
                                    Ascending
                                @elseif ($selectedSort == 'desc')
                                    Descending
                                @elseif ($selectedSort == 'last_month')
                                    Last Month
                                @elseif ($selectedSort == 'last_7_days')
                                    Last 7 Days
                                @else
                                    Last 7 Days
                                @endif
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="recently_added">Recently Added</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="asc">Ascending</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="desc">Descending</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="last_month">Last Month</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="last_7_days">Last 7 Days</a></li>
                            </ul>
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
                                    <th>Emp ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Joining Date</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    @php
                                        $detail = $employee->employmentDetail;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td><a href="{{ url('employee-details') }}">{{ $detail->employee_id }}</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                    data-bs-toggle="modal" data-bs-target="#view_details"><img
                                                        src="{{ asset('storage/' . $employee->personalInformation->profile_picture) }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                                            data-bs-toggle="modal" data-bs-target="#view_details">
                                                            {{ $employee->personalInformation->last_name }}
                                                            {{ $employee->personalInformation->suffix }},
                                                            {{ $employee->personalInformation->first_name }}
                                                            {{ $employee->personalInformation->middle_name }}</a></p>
                                                    <span class="fs-12"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->email }}</td>
                                        <td>{{ $detail?->department?->department_name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown me-3">
                                                <a href="#"
                                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                                    data-bs-toggle="dropdown">
                                                    {{ $detail?->designation?->designation_name ?? 'N/A' }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>{{ $detail->date_hired }}</td>
                                        <td>
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $detail->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($detail->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">

                                                <a href="{{ url('employees/employee-details/' . $employee->id) }}"
                                                    class="me-2" title="View Full Details"><i
                                                        class="ti ti-eye"></i></a>

                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_employee" data-id="{{ $employee->id }}"
                                                    data-first_name="{{ $employee->personalInformation->first_name }}"
                                                    data-last_name="{{ $employee->personalInformation->last_name }}"
                                                    data-middle_name="{{ $employee->personalInformation->middle_name }}"
                                                    data-phone_number="{{ $employee->personalInformation->phone_number }}"
                                                    data-suffix="{{ $employee->suffix }}"
                                                    data-profile_picture="{{ $employee->profile_picture }}"
                                                    data-username="{{ $employee->username }}"
                                                    data-email="{{ $employee->email }}"
                                                    data-password="{{ $employee->password }}"
                                                    data-role_id="{{ $employee->role_id }}"
                                                    data-designation_id="{{ $detail->designation_id }}"
                                                    data-department_id="{{ $detail->department_id }}"
                                                    data-date_hired="{{ $detail->date_hired }}"
                                                    data-employee_id="{{ $detail->employee_id }}"
                                                    data-employment_type="{{ $detail->employment_type }}"
                                                    data-employment_status="{{ $detail->employment_status }}"
                                                    data-branch_id="{{ $detail->branch_id }}">
                                                    <i class="ti ti-edit" title="Edit"></i></a>

                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-user-id="{{ $detail->user_id }}"
                                                    data-employee-name="{{ $employee->personalInformation->first_name }} {{ $employee->personalInformation->last_name }}"
                                                    data-bs-target="#delete_modal">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>

                                                <a href="#" class="btn-deactivate" data-bs-toggle="modal"
                                                    data-bs-target="#deactivate_modal"
                                                    data-user-id="{{ $detail->user_id }}"
                                                    data-employee-name="{{ $employee->personalInformation->first_name }} {{ $employee->personalInformation->last_name }}"><i
                                                        class="ti ti-cancel" title="Deactivate"></i></a>

                                                <a href="#" class="btn-activate" data-bs-toggle="modal"
                                                    data-bs-target="#activate_modal"
                                                    data-user-id="{{ $detail->user_id }}"
                                                    data-employee-name="{{ $employee->personalInformation->first_name }} {{ $employee->personalInformation->last_name }}"
                                                    title="Activate"><i class="ti ti-circle-check"></i></a>
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

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2025 &copy; OneJAF Vertex.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">JAF Digital Group Inc.</a>
            </p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'departments' => $departments,
        'designations' => $designations,
        'roles' => $roles,
        'employees' => $employees,
        'branches' => $branches,
        'leaveTypes' => $leaveTypes,
    ])
    @endcomponent
@endsection

@push('scripts')
    <script>
        var currentImagePath =
            "{{ asset('storage/' . ($employee->personalInformation->profile_picture ?? 'default-profile.jpg')) }}";
    </script>
    <script src="{{ asset('build/js/employeelist.js') }}"></script>
    <!-- Filter JS -->
    <script src="{{ asset('build/js/department/filters.js') }}"></script>
@endpush
