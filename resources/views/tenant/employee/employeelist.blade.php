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
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee List</li>
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
                                    {{-- <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                                class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                    </li> --}}
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
                    @if (in_array('Create', $permission))
                        <div class="d-flex gap-2 mb-2">
                            <a href="#" id="addEmployeeBtn" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="ti ti-circle-plus"></i>Add Employee
                            </a>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#upload_employee"
                                class="btn btn-secondary d-flex align-items-center gap-2">
                                <i class="ti ti-upload"></i>Upload Employee
                            </a>
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

            <div class="row g-3 mb-4">
                <!-- Total Employee -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #0f8b8d 0%, #0b6b67 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Total Employee</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($employees->count(), 2, '0', STR_PAD_LEFT) }}</h2>
                                <small class="text-white-75">Employees</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-users" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-users" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Employees -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #21c48a 0%, #14a86a 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Active Employees</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad(
                                        $employees->filter(function ($e) {
                                                return $e->employmentDetail && $e->employmentDetail->status == 1;
                                            })->count(),
                                        2,
                                        '0',
                                        STR_PAD_LEFT,
                                    ) }}
                                </h2>
                                <small class="text-white-75">Employees</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-user-share" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-user-share" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inactive Employees -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #a33658 0%, #8b2c48 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Inactive Employees</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad(
                                        $employees->filter(function ($e) {
                                                return $e->employmentDetail && $e->employmentDetail->status == 0;
                                            })->count(),
                                        2,
                                        '0',
                                        STR_PAD_LEFT,
                                    ) }}
                                </h2>
                                <small class="text-white-75">Employees</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-user-pause" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-user-pause" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Joiners -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #f6b21a 0%, #f09f00 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">New Joiners</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad(
                                        $employees->filter(function ($e) {
                                                return $e->employmentDetail &&
                                                    $e->employmentDetail->date_hired &&
                                                    \Carbon\Carbon::parse($e->employmentDetail->date_hired)->isSameMonth(now());
                                            })->count(),
                                        2,
                                        '0',
                                        STR_PAD_LEFT,
                                    ) }}
                                </h2>
                                <small class="text-white-75">Employees</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-user-plus" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-user-plus" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="payroll-btns mb-3">
                <a href="#" class="btn btn-primary active border me-2" id="employeeListTab">Employee List</a>
                <a href="#" class="btn btn-white border me-2" id="employeeArchiveTab">Employee Archive</a>
            </div>

            <!-- Employee List Card -->
            <div class="card" id="employeeListCard">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                oninput="filter();" style="width:200px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                oninput="filter()" style="width:200px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                oninput="filter()" style="width:200px;">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                oninput="filter()" style="width:150px;">
                                <option value="" selected>All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
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
                        <table class="table datatable-filtered" id="employee_list_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Joining Date</th>
                                    <th>Status</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="employeeListTableBody">
                                @php
                                    $counter = 1;
                                @endphp

                                @foreach ($employees as $employee)
                                    @php
                                        $detail = $employee->employmentDetail;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if (in_array('Read', $permission) && in_array('Update', $permission))
                                                <a href="{{ url('employees/employee-details/' . $employee->id) }}"
                                                    class="me-2" title="View Full Details"><i
                                                        class="ti ti-eye"></i></a>
                                            @endif
                                            @if (in_array('Update', $permission))
                                                <a href="#" class="me-2"
                                                    onclick="editEmployee({{ $employee->id }})"><i
                                                        class="ti ti-edit"></i></a>
                                            @endif
                                            {{ $detail->employee_id ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                    data-bs-toggle="modal" data-bs-target="#view_details"><img
                                                        src="{{ $employee->personalInformation && $employee->personalInformation->profile_picture ? asset('storage/' . $employee->personalInformation->profile_picture) : URL::asset('build/img/users/user-13.jpg') }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                                            data-bs-toggle="modal" data-bs-target="#view_details">
                                                            {{ $employee->personalInformation->last_name ?? '' }}
                                                            {{ $employee->personalInformation->suffix ?? '' }},
                                                            {{ $employee->personalInformation->first_name ?? '' }}
                                                            {{ $employee->personalInformation->middle_name ?? '' }}</a>
                                                    </p>
                                                    <span
                                                        class="fs-12">{{ $employee->employmentDetail->branch->name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->email ?? '-' }}</td>
                                        <td>{{ $detail?->department?->department_name ?? 'N/A' }}</td>
                                        <td> {{ $detail?->designation?->designation_name ?? 'N/A' }}</td>
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
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td>
                                                <div class="action-icon d-inline-flex">

                                                    @if (in_array('Update', $permission))
                                                        @if ($status == 0)
                                                            <a href="#" class="btn-activate"
                                                                onclick="activateEmployee({{ $employee->id }})"
                                                                title="Activate"><i class="ti ti-circle-check"></i></a>
                                                        @else
                                                            <a href="#" class="btn-deactivate"
                                                                onclick="deactivateEmployee({{ $employee->id }})"><i
                                                                    class="ti ti-cancel" title="Deactivate"></i></a>
                                                        @endif
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="btn-delete"
                                                            onclick="deleteEmployee({{ $employee->id }})">
                                                            <i class="ti ti-trash" title="Delete"></i>
                                                        </a>
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

            <!-- Employee Archive Card -->
            <div class="card" id="employeeArchiveCard" style="display: none;">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee Archive</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="archive_branch_filter" id="archive_branch_filter" class="select2 form-select"
                                oninput="filterArchive();" style="width:200px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="archive_department_filter" id="archive_department_filter" class="select2 form-select"
                                oninput="filterArchive()" style="width:200px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="archive_designation_filter" id="archive_designation_filter" class="select2 form-select"
                                oninput="filterArchive()" style="width:200px;">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="archive_sortby_filter" id="archive_sortby_filter" class="select2 form-select"
                                onchange="filterArchive()" style="width:150px;">
                                <option value="" selected>All Sort By</option>
                                <option value="ascending">Ascending</option>
                                <option value="descending">Descending</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_7_days">Last 7 days</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Archive Table Controls -->
                <div class="d-flex align-items-center justify-content-between flex-wrap px-3 border-bottom">
                    <div class="dataTables_length">
                        <label class="d-flex align-items-center fs-12 mb-0">Row Per Page
                            <select name="archive_entries" id="archive_entries" class="form-select form-select-sm" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            Entries
                        </label>
                    </div>
                    <div class="dataTables_filter">
                        <label class="d-flex align-items-center gap-1 fs-12 mb-0">Search:
                            <input type="search" class="form-control form-control-sm" placeholder="Search..." 
                                   id="archive_search" onkeyup="filterArchive()" style="width: 200px;">
                        </label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable-filtered" id="employee_archive_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Joining Date</th>
                                    <th>Status</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="employeeArchiveTableBody">
                                @php
                                    // For now, use empty collection and rely on AJAX filtering to load inactive employees
                                    $inactiveEmployees = collect();
                                @endphp

                                @foreach ($inactiveEmployees as $employee)
                                    @php
                                        $detail = $employee->employmentDetail;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if (in_array('Read', $permission) && in_array('Update', $permission))
                                                <a href="{{ url('employees/employee-details/' . $employee->id) }}"
                                                    class="me-2" title="View Full Details"><i
                                                        class="ti ti-eye"></i></a>
                                            @endif
                                            @if (in_array('Update', $permission))
                                                <a href="#" class="me-2"
                                                    onclick="editEmployee({{ $employee->id }})"><i
                                                        class="ti ti-edit"></i></a>
                                            @endif
                                            {{ $detail->employee_id ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                    data-bs-toggle="modal" data-bs-target="#view_details"><img
                                                        src="{{ $employee->personalInformation && $employee->personalInformation->profile_picture ? asset('storage/' . $employee->personalInformation->profile_picture) : URL::asset('build/img/users/user-13.jpg') }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                                            data-bs-toggle="modal" data-bs-target="#view_details">
                                                            {{ $employee->personalInformation->last_name ?? '' }}
                                                            {{ $employee->personalInformation->suffix ?? '' }},
                                                            {{ $employee->personalInformation->first_name ?? '' }}
                                                            {{ $employee->personalInformation->middle_name ?? '' }}</a>
                                                    </p>
                                                    <span
                                                        class="fs-12">{{ $employee->employmentDetail->branch->name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->email ?? '-' }}</td>
                                        <td>{{ $detail?->department?->department_name ?? 'N/A' }}</td>
                                        <td> {{ $detail?->designation?->designation_name ?? 'N/A' }}</td>
                                        <td>{{ $detail->date_hired ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge d-inline-flex align-items-center badge-xs badge-danger">
                                                <i class="ti ti-point-filled me-1"></i>Inactive
                                            </span>
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td>
                                                <div class="action-icon d-inline-flex">
                                                    @if (in_array('Update', $permission))
                                                        <a href="#" class="btn-activate"
                                                            onclick="activateEmployee({{ $employee->id }})"
                                                            title="Activate"><i class="ti ti-circle-check"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="btn-delete"
                                                            onclick="deleteEmployee({{ $employee->id }})">
                                                            <i class="ti ti-trash" title="Delete"></i>
                                                        </a>
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


        @include('layout.partials.footer-company')
    </div>

    <!-- Add Employee -->
    <div class="modal fade" id="add_employee">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Add New Employee</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="addEmployeeForm" enctype="multipart/form-data">
                    <div class="contact-grids-tab">
                        <ul class="nav nav-underline" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab" data-bs-toggle="tab"
                                    data-bs-target="#basic-info" type="button" role="tab"
                                    aria-selected="true">Basic Information</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel"
                            aria-labelledby="info-tab" tabindex="0">
                            <div class="modal-body pb-0 ">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div
                                            class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
                                            <div
                                                class="d-flex align-items-center justify-content-center avatar avatar-xxl rounded-circle border border-dashed me-2 flex-shrink-0 text-dark frames">
                                                <img id="previewImage" alt="Profile Image" class="rounded-circle"
                                                    src="{{ URL::asset('build/img/users/user-13.jpg') }}">
                                            </div>
                                            <div class="profile-upload">
                                                <div class="mb-2">
                                                    <h6 class="mb-1">Upload Profile Image</h6>
                                                    <p class="fs-12">Image should be below 4 mb</p>
                                                </div>
                                                <div class="profile-uploader d-flex align-items-center">
                                                    <div class="drag-upload-btn btn btn-sm btn-primary me-2">
                                                        Upload
                                                        <input type="file" name="profile_picture"
                                                            id="profileImageInput" class="form-control image-sign"
                                                            accept="image/*" onchange="previewSelectedImageAdd(event)">
                                                    </div>
                                                    <a href="javascript:void(0);" id="cancelImageBtn"
                                                        class="btn btn-light btn-sm">Cancel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">First Name <span class="text-danger">
                                                    *</span></label>
                                            <input type="text" class="form-control" name="first_name" id="firstName">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" id="lastName">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" name="middle_name"
                                                id="middleName">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Suffix</label>
                                            <input type="text" class="form-control" name="suffix" id="suffix">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Biometrics ID</label>
                                            <input type="text" class="form-control" name="biometrics_id"
                                                id="biometricsId">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Employee ID <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="col-md-6">
                                                    <select class="select" id="empIdPrefix" name="emp_prefix">
                                                        <option value=""></option>
                                                        @foreach ($prefixes as $prefix)
                                                            <option value="{{ $prefix->prefix_name }}">
                                                                {{ $prefix->prefix_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <span>-</span>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="employee_id"
                                                        id="employeeId">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Joining Date <span class="text-danger">
                                                    *</span></label>
                                            <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                                name="date_hired" id="dateHired">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username <span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" name="username" id="username">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger"> *</span></label>
                                            <input type="email" class="form-control" name="email" id="email">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3 ">
                                            <label class="form-label">Password <span class="text-danger"> *</span></label>
                                            <div class="pass-group">
                                                <input type="password" class="pass-input form-control" name="password"
                                                    id="password">
                                                <span class="ti toggle-password ti-eye-off"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3 ">
                                            <label class="form-label">Confirm Password <span class="text-danger">
                                                    *</span></label>
                                            <div class="pass-group">
                                                <input type="password" class="pass-inputs form-control"
                                                    name="confirm_password" id="confirmPassword">
                                                <span class="ti toggle-passwords ti-eye-off"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" name="phone_number"
                                                id="phoneNumber">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Role<span class="text-danger"> *</span></label>
                                            <select name="role_id" id="role_id" class="form-select select2"
                                                placeholder="Select Role">
                                                <option value="" disabled selected>Select Role</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Branch</label>
                                            <select id="addBranchId" name="branch_id" class="form-select select2"
                                                placeholder="Select Branch">
                                                <option value="" disabled selected>Select Branch</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Department</label>
                                            <select id="add_departmentSelect" name="department_id"
                                                class="form-select select2" placeholder="Select Department">
                                                <option value="" disabled selected>Select Department</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}">
                                                        {{ $department->department_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Designation</label>
                                            <select id="add_designationSelect" name="designation_id"
                                                class="form-select select2" placeholder="Select Designation">
                                                <option value="" disabled selected>Select Designation</option>
                                                @foreach ($designations as $designation)
                                                    <option value="{{ $designation->id }}">
                                                        {{ $designation->designation_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Employment Status</label>
                                            <select id="employmentStatus" name="employment_status"
                                                class="form-select select2" placeholder="Select Status">
                                                <option value="" disabled selected>Select Status</option>
                                                <option value="Probationary">Probationary</option>
                                                <option value="Regular">Regular</option>
                                                <option value="Project-Based">Project Based</option>
                                                <option value="Seasonal">Seasonal</option>
                                                <option value="Contractual">Contractual</option>
                                                <option value="Casual">Casual</option>
                                                <option value="Intern/OJT">Intern/OJT</option>
                                                <option value="Trainee">Trainee</option>
                                                <option value="Consultant">Consultant</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Employment Type</label>
                                            <select id="employmentType" name="employment_type"
                                                class="form-select select2" placeholder="Select Type">
                                                <option value="" disabled selected>Select Type</option>
                                                <option value="Full-Time">Full-Time</option>
                                                <option value="Part-Time">Part-time</option>
                                                <option value="Freelancer">Freelancer</option>
                                                <option value="Apprentice">Apprentice</option>
                                                <option value="Remote">Remote</option>
                                                <option value="Field-Based">Field-Based</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Reporting To:</label>
                                            <select id="reportingTo" name="reporting_to" class="form-select select2">
                                                <option value="" disabled selected>Select Employee</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}">
                                                        {{ $employee->personalInformation->full_name ?? '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-light border me-2"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Employee -->

    {{-- EDIT EMPLOYEE --}}
    <div class="modal fade" id="edit_employee">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Edit Employee</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="editEmployeeForm" enctype="multipart/form-data">
                    <input type="hidden" name="editUserId" id="editUserId">
                    <div class="contact-grids-tab">
                        <ul class="nav nav-underline" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="edit-info-tab" data-bs-toggle="tab"
                                    data-bs-target="#basic-info" type="button" role="tab"
                                    aria-selected="true">Basic Information</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel"
                            aria-labelledby="info-tab" tabindex="0">
                            <div class="modal-body pb-0 ">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div
                                            class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
                                            <div
                                                class="d-flex align-items-center justify-content-center avatar avatar-xxl rounded-circle border border-dashed me-2 flex-shrink-0 text-dark frames">
                                                <img id="editPreviewImage" alt="Profile Image" class="rounded-circle"
                                                    src="{{ isset($employee->personalInformation?->profile_picture) ? asset('storage/' . $employee->personalInformation->profile_picture) : asset('storage/default-profile.jpg') }}">
                                            </div>
                                            <div class="profile-upload">
                                                <div class="mb-2">
                                                    <h6 class="mb-1">Upload Profile Image</h6>
                                                    <p class="fs-12">Image should be below 4 mb</p>
                                                </div>
                                                <div class="profile-uploader d-flex align-items-center">
                                                    <div class="drag-upload-btn btn btn-sm btn-primary me-2">
                                                        Upload
                                                        <input type="file" name="profile_picture"
                                                            id="editProfileImageInput" class="form-control image-sign"
                                                            accept="image/*" onchange="editPreviewSelectedImage(event)">
                                                    </div>
                                                    <a href="javascript:void(0);" id="editCancelImageBtn"
                                                        class="btn btn-light btn-sm">Cancel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">First Name <span class="text-danger">
                                                    *</span></label>
                                            <input type="text" class="form-control" name="first_name"
                                                id="editFirstName">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name"
                                                id="editLastName">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" name="middle_name"
                                                id="editMiddleName">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Suffix</label>
                                            <input type="text" class="form-control" name="suffix" id="editSuffix">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Biometrics ID</label>
                                            <input type="text" class="form-control" name="biometrics_id"
                                                id="editBiometricsId">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Employee ID <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="col-md-6">
                                                    <select class="form-select" id="editEmpIdPrefix" name="emp_prefix">
                                                        <option value=""></option>
                                                        @foreach ($prefixes as $prefix)
                                                            <option value="{{ $prefix->prefix_name }}">
                                                                {{ $prefix->prefix_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <span>-</span>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="employee_id"
                                                        id="editEmployeeId">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Joining Date <span class="text-danger">
                                                    *</span></label>
                                            <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                                name="date_hired" id="editDateHired">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username <span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" name="username"
                                                id="editUserName">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger"> *</span></label>
                                            <input type="email" class="form-control" name="email" id="editEmail">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3 ">
                                            <label class="form-label">Password</label>
                                            <div class="pass-group">
                                                <input type="password" class="pass-input form-control" name="password"
                                                    id="editPassword">
                                                <span class="ti toggle-password ti-eye-off"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3 ">
                                            <label class="form-label">Confirm Password</label>
                                            <div class="pass-group">
                                                <input type="password" class="pass-inputs form-control"
                                                    name="confirm_password" id="editConfirmPassword">
                                                <span class="ti toggle-passwords ti-eye-off"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" name="phone_number"
                                                id="editPhoneNumber">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Role<span class="text-danger"> *</span></label>
                                            <select name="role_id" id="editRoleId" class="form-select select2"
                                                placeholder="Select Role">
                                                <option value="" disabled selected>Select Role</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Branch<span class="text-danger"> *</span></label>
                                            <select id="editBranchId" name="branch_id" class="form-select select2"
                                                placeholder="Select Branch">
                                                <option value="" disabled selected>Select Branch</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Department<span class="text-danger">
                                                    *</span></label>
                                            <select id="editDepartmentSelect" name="department_id"
                                                class="form-select select2" placeholder="Select Department">
                                                <option value="" disabled selected>Select Department</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}">
                                                        {{ $department->department_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Designation<span class="text-danger">
                                                    *</span></label>
                                            <select id="editDesignationSelect" name="designation_id"
                                                class="form-select select2" placeholder="Select Designation">
                                                <option value="" disabled selected>Select Designation</option>
                                                @foreach ($designations as $designation)
                                                    <option value="{{ $designation->id }}">
                                                        {{ $designation->designation_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Employment Status<span class="text-danger">
                                                    *</span></label>
                                            <select id="editEmploymentStatus" name="employment_status"
                                                class="form-select select2" placeholder="Select Status">
                                                <option value="" disabled selected>Select Status</option>
                                                <option value="Probationary">Probationary</option>
                                                <option value="Regular">Regular</option>
                                                <option value="Project-Based">Project Based</option>
                                                <option value="Seasonal">Seasonal</option>
                                                <option value="Contractual">Contractual</option>
                                                <option value="Casual">Casual</option>
                                                <option value="Intern/OJT">Intern/OJT</option>
                                                <option value="Trainee">Trainee</option>
                                                <option value="Consultant">Consultant</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Employment Type<span class="text-danger">
                                                    *</span></label>
                                            <select id="editEmploymentType" name="employment_type"
                                                class="form-select select2" placeholder="Select Type">
                                                <option value="" disabled selected>Select Type</option>
                                                <option value="Full-Time">Full-Time</option>
                                                <option value="Part-Time">Part-time</option>
                                                <option value="Freelancer">Freelancer</option>
                                                <option value="Apprentice">Apprentice</option>
                                                <option value="Remote">Remote</option>
                                                <option value="Field-Based">Field-Based</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Reporting To:</label>
                                            <select id="editReportingTo" name="reporting_to" class="form-select select2">
                                                <option value="">Select Employee</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}">
                                                        {{ $employee->personalInformation->full_name ?? '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-light border me-2"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Edit Employee -->

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

    {{-- Upload Employee --}}
    <div class="modal fade" id="upload_employee" tabindex="-1" aria-labelledby="uploadEmployeeLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="csvUploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Employees via CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv"
                                required>
                            <small class="text-muted">Follow the format. <a
                                    href="{{ asset('templates/employee_template.csv') }}" download>Download
                                    sample</a></small>
                        </div>

                        <!-- Import Status Container -->
                        <div id="importStatusContainer" style="display: none;">
                            <div class="alert alert-info mb-0">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Processing...</span>
                                    </div>
                                    <span>Processing import in the background... This may take a few moments.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Error List Container -->
                        <div id="errorList" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Upload</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="exportForm" method="GET" action="{{ route('exportEmployee') }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Export Employees by Branch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="branch_id" class="form-label">Select Branch</label>
                            <select class="form-select" name="branch_id" id="branch_id" required>
                                <option value="" selected disabled>Select branch</option>
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- License Overage Confirmation Modal -->
    <div class="modal fade" id="license_overage_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-alert-triangle me-2 text-white"></i>
                        <h4 class="modal-title text-white mb-0">License Limit Exceeded</h4>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning border-0">
                        <div class="d-flex align-items-start">
                            <i class="ti ti-info-circle me-2 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Additional License Required</h6>
                                <p class="mb-0 small">Adding this employee will exceed your current subscription limit.</p>
                            </div>
                        </div>
                    </div>

                    <!-- License Details -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h5 class="mb-1" id="currentLicenseCount">-</h5>
                                <small class="text-muted">Current Active</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h5 class="mb-1" id="baseLicenseLimit">-</h5>
                                <small class="text-muted">Plan Limit</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <p class="small text-muted mb-0">
                            <i class="ti ti-info-circle me-1"></i>
                            This additional cost will be billed according to your subscription cycle.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmOverageBtn">
                        <i class="ti ti-check me-1"></i>Proceed with Additional License
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Implementation Fee Modal --}}
    <div class="modal fade" id="implementation_fee_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                style="width:48px; height:48px; border:2px solid rgba(0,0,0,0.06);">
                                <i class="ti ti-coin fs-20 text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white">Implementation Fee Required</h5>
                            <small class="opacity-75">Upgrade your plan to add more users</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body py-4">
                    <div class="alert alert-warning border-0 bg-gradient rounded-3 mb-4">
                        <div class="d-flex align-items-start">
                            <i class="ti ti-alert-triangle fs-4 me-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading fw-semibold mb-1">Plan Limit Reached</h6>
                                <p class="mb-0">Your Starter plan allows up to <strong>10 users</strong>. Adding more
                                    users requires an implementation fee.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-xl-6">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-header bg-transparent border-bottom py-2">
                                    <h6 class="card-title mb-0 text-primary fw-semibold">
                                        <i class="ti ti-users me-2"></i>User Count Overview
                                    </h6>
                                </div>
                                <div class="card-body py-3 px-3">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1">Current Active Users</small>
                                            <h4 class="mb-0 fw-bold text-primary" id="impl_current_users">-</h4>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1">After Adding New User</small>
                                            <h4 class="mb-0 fw-bold text-success" id="impl_new_user_count">-</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-header bg-transparent border-bottom py-2">
                                    <h6 class="card-title mb-0 text-primary fw-semibold">
                                        <i class="ti ti-currency-peso me-2"></i>Implementation Fee
                                    </h6>
                                </div>
                                <div class="card-body py-3 px-3">
                                    <div
                                        class="bg-gradient bg-primary bg-opacity-10 rounded-3 p-3 border-start border-primary border-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-white d-block">One-time Payment</small>
                                                <h4 class="mb-0 fw-bold text-success" id="impl_fee_amount">-</h4>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width:48px; height:48px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                                                    <i class="ti ti-credit-card fs-20" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-light shadow-sm">
                        <div class="card-header bg-transparent border-bottom">
                            <h6 class="card-title mb-0 text-primary fw-semibold">
                                <i class="ti ti-info-circle me-2"></i>What's Included After Payment
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="ti ti-check-circle text-success fs-5 me-3 mt-1"></i>
                                        <div>
                                            <h6 class="fw-semibold mb-1">Access to 11-20 Users</h6>
                                            <p class="text-muted small mb-0">Expand your team with additional user slots
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="ti ti-shield-check text-success fs-5 me-3 mt-1"></i>
                                        <div>
                                            <h6 class="fw-semibold mb-1">Enhanced Support</h6>
                                            <p class="text-muted small mb-0">Priority customer service and assistance</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-top">
                                <h6 class="fw-semibold mb-3 text-primary">
                                    <i class="ti ti-gift me-2"></i>Additional Inclusions
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <i class="ti ti-school text-info fs-5 me-3 mt-1"></i>
                                            <div>
                                                <h6 class="fw-semibold mb-1">2 Days Free Training</h6>
                                                <p class="text-muted small mb-0">Comprehensive onboarding and training
                                                    sessions</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <i class="ti ti-book text-info fs-5 me-3 mt-1"></i>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Knowledge Base</h6>
                                                <p class="text-muted small mb-0">Access to comprehensive documentation and
                                                    guides</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-start">
                                            <i class="ti ti-mail text-info fs-5 me-3 mt-1"></i>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Lifetime Email Support</h6>
                                                <p class="text-muted small mb-0">Monday to Friday, 9am to 6pm for ongoing
                                                    assistance</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-currency-peso text-primary fs-5 me-3"></i>
                                    <div>
                                        <h6 class="fw-semibold mb-1">Pricing Structure</h6>
                                        <p class="text-muted small mb-0">₱49/user/month for up to 20 users after
                                            implementation fee</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary px-4" id="confirmImplementationFeeBtn">
                        <i class="ti ti-credit-card me-1"></i>Proceed to Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Plan Upgrade Modal --}}
    <div class="modal fade" id="plan_upgrade_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                style="width:48px; height:48px; border:2px solid rgba(0,0,0,0.06);">
                                <i class="ti ti-rocket fs-20 text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white">
                                Plan Upgrade Required
                            </h5>
                            <small class="opacity-75">Upgrade your plan to accommodate more users</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body py-4">
                    <div class="alert alert-info border-0 bg-gradient rounded-3 mb-4">
                        <div class="d-flex align-items-start">
                            <i class="ti ti-info-circle fs-4 me-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading fw-semibold mb-1">Plan Limit Reached</h6>
                                <p class="mb-0"><strong>You've reached the maximum user limit for your current
                                        plan.</strong> Upgrade to access more user slots and enhanced features.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">

                        <div class="col-lg-4 col-md-6">
                            <div class="card text-white position-relative overflow-hidden"
                                style="border-radius:10px; background: linear-gradient(135deg, #0f8b8d 0%, #0b6b67 100%); min-height:120px;">
                                <div class="card-body d-flex align-items-center justify-content-between p-3">
                                    <div class="me-3" style="z-index:3;">
                                        <p class="fs-12 fw-medium mb-1 text-white-75">Current Plan</p>
                                        <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:20px;">
                                            <span id="upgrade_current_plan_name">-</span>
                                        </h2>
                                        <small class="text-white-75" id="upgrade_current_plan_limit">-</small>
                                    </div>

                                    <!-- Right icon circle group -->
                                    <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                        <div
                                            style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                            <i class="ti ti-package" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                        </div>
                                        <div
                                            style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                            <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                <i class="ti ti-package" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="card text-white position-relative overflow-hidden"
                                style="border-radius:10px; background: linear-gradient(135deg, #0f8b8d 0%, #0b6b67 100%); min-height:120px;">
                                <div class="card-body d-flex align-items-center justify-content-between p-3">
                                    <div class="me-3" style="z-index:3;">
                                        <p class="fs-12 fw-medium mb-1 text-white-75">Current Active Users</p>
                                        <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:20px;">
                                            <span id="upgrade_current_users">-</span>
                                        </h2>
                                        <small class="text-white-75">Active users</small>
                                    </div>

                                    <!-- Right icon circle group -->
                                    <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                        <div
                                            style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                            <i class="ti ti-users" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                        </div>
                                        <div
                                            style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                            <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                <i class="ti ti-users" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="card text-white position-relative overflow-hidden"
                                style="border-radius:10px; background: linear-gradient(135deg, #0f8b8d 0%, #0b6b67 100%); min-height:120px;">
                                <div class="card-body d-flex align-items-center justify-content-between p-3">
                                    <div class="me-3" style="z-index:3;">
                                        <p class="fs-12 fw-medium mb-1 text-white-75">After Adding New User</p>
                                        <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:20px;">
                                            <span id="upgrade_new_user_count">-</span>
                                        </h2>
                                        <small class="text-white-75">Total users</small>
                                    </div>

                                    <!-- Right icon circle group -->
                                    <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                        <div
                                            style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                            <i class="ti ti-users" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                        </div>
                                        <div
                                            style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                            <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                <i class="ti ti-users" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-4 fw-semibold text-primary">
                        <i class="ti ti-package me-2"></i>Select Your Upgrade Plan
                    </h6>

                    {{-- Billing Cycle Toggle --}}
                    <div class="d-flex justify-content-center align-items-center mb-4">
                        <span class="me-3 fw-medium" id="billing_cycle_label_monthly">Monthly</span>
                        <div class="form-check form-switch form-check-lg">
                            <input class="form-check-input" type="checkbox" role="switch" id="billing_cycle_toggle"
                                style="cursor: pointer; width: 3.5rem; height: 1.75rem;">
                        </div>
                        <span class="ms-3 fw-medium" id="billing_cycle_label_yearly">
                            Yearly <span class="badge bg-success ms-1">Save more!</span>
                        </span>
                    </div>

                    <div id="available_plans_container" class="row g-4 mb-4">
                        <!-- Plans will be dynamically inserted here -->
                    </div>

                    <div class="card border-light shadow-sm" id="selected_plan_summary" style="display: none;">
                        <div class="card-header bg-transparent border-bottom">
                            <h6 class="card-title mb-0 text-primary fw-semibold">
                                <i class="ti ti-receipt me-2"></i>Upgrade Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1">Selected Plan</small>
                                            <h5 class="mb-0 fw-bold text-primary" id="summary_plan_name">-</h5>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">User Limit</small>
                                                <p class="mb-0 fw-medium" id="summary_plan_limit">-</p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Monthly Price</small>
                                                <p class="mb-0 fw-medium" id="summary_plan_price">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div
                                        class="bg-gradient bg-primary bg-opacity-10 rounded-3 p-3 border-start border-primary border-3">
                                        <!-- Implementation Fee Difference (conditionally shown) -->
                                        <div id="summary_impl_fee_row" style="display: none;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-white">Implementation Fee Difference</small>
                                                <span class="fw-medium" id="summary_impl_fee_difference">-</span>
                                            </div>
                                        </div>

                                        <!-- Plan Price Difference -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-white">Plan Price Difference</small>
                                            <span class="fw-medium" id="summary_plan_price_difference">-</span>
                                        </div>

                                        <!-- Subtotal -->
                                        <div
                                            class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                            <small class="text-white">Subtotal</small>
                                            <span class="fw-medium" id="summary_subtotal">-</span>
                                        </div>

                                        <!-- VAT -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-white">VAT (<span
                                                    id="summary_vat_percentage">12</span>%)</small>
                                            <span class="fw-medium" id="summary_vat_amount">-</span>
                                        </div>

                                        <!-- Total -->
                                        <div class="border-top pt-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold text-white">Total Amount Due:</span>
                                                <h4 class="text-success fw-bold mb-0" id="summary_total_amount">-</h4>
                                            </div>
                                            <small class="text-white opacity-75">Implementation fee + plan price
                                                difference + VAT</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary px-4" id="confirmPlanUpgradeBtn" disabled>
                        <i class="ti ti-arrow-up-circle me-1"></i>Proceed with Upgrade
                    </button>
                </div>
            </div>
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
        'prefixes' => $prefixes,
    ])
    @endcomponent
@endsection


@push('scripts')
    <script>
        var currentImagePath =
            "{{ asset('storage/' . ($employee->personalInformation->profile_picture ?? 'default-profile.jpg')) }}";
    </script>
    <script src="{{ asset('build/js/datatable-filtered.js') }}"></script>
    <script src="{{ asset('build/js/employeelist.js') }}?v={{ config('app.asset_version', time()) }}"></script>
    <script>
        const routes = {
            employeeAdd: "{{ route('employeeAdd') }}",
            employeeEdit: "{{ route('employeeEdit') }}",
            employeeActivate: "{{ route('employeeActivate') }}",
            employeeDeactivate: "{{ route('employeeDeactivate') }}",
            employeeDelete: "{{ route('employeeDelete') }}",
            getEmployeeDetails: "{{ route('getEmployeeDetails') }}",
            emplistfilter: "{{ route('empList-filter') }}",
            branchAutoFilter: "{{ route('branchAuto-filter') }}",
            departmentAutoFilter: "{{ route('departmentAuto-filter') }}",
            designationAutoFilter: "{{ route('designationAuto-filter') }}"
        };
    </script>

    <script>
        let importStatusInterval = null;

        $(document).ready(function() {
            // Reset form and stop polling when modal is closed
            $('#upload_employee').on('hidden.bs.modal', function() {
                if (importStatusInterval) {
                    clearInterval(importStatusInterval);
                    importStatusInterval = null;
                }
                $('#csvUploadForm')[0].reset();
                $('#csv_file').prop('disabled', false);
                $('#importStatusContainer').hide();
                $('#errorList').empty();
            });

            $('#csvUploadForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('#errorList').empty();

                $.ajax({
                    url: '{{ route('importEmployeeCSV') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#csvUploadForm button[type="submit"]').prop('disabled', true).text(
                            'Uploading...');
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.info(
                                'CSV file uploaded successfully! Processing employees in the background...',
                                'Processing Import', {
                                    timeOut: 5000,
                                    progressBar: true
                                }
                            );

                            // Clear form but keep modal open with status indicator
                            $('#csvUploadForm')[0].reset();
                            $('#csv_file').prop('disabled', true);
                            $('#importStatusContainer').slideDown();

                            // Start polling for import status
                            startImportStatusPolling();
                        } else {
                            toastr.error(response.message || 'Upload failed.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            // Validation errors
                            let errorMessages = '';
                            $.each(xhr.responseJSON.errors, function(key, messages) {
                                errorMessages += messages.join('<br>') + '<br>';
                            });
                            toastr.error(errorMessages, 'Validation Error');
                        } else {
                            const msg = xhr.responseJSON?.message ||
                                'An unexpected error occurred.';
                            toastr.error(msg);
                        }
                    },
                    complete: function() {
                        $('#csvUploadForm button[type="submit"]').prop('disabled', false).text(
                            'Upload');
                    }
                });
            });
        });

        function startImportStatusPolling() {
            // Clear any existing interval
            if (importStatusInterval) {
                clearInterval(importStatusInterval);
            }

            // Poll every 3 seconds
            importStatusInterval = setInterval(function() {
                checkImportStatus();
            }, 3000);
        }

        function checkImportStatus() {
            $.ajax({
                url: '{{ route('emp.checkImportStatus') }}',
                method: 'GET',
                success: function(response) {
                    if (response.status === 'success' && response.results) {
                        // Stop polling
                        clearInterval(importStatusInterval);
                        importStatusInterval = null;

                        // Display results
                        displayImportResults(response.results);
                    }
                    // If no results yet, keep polling
                },
                error: function(xhr) {
                    // If no import found or error, keep polling
                    console.log('No import status found yet...');
                }
            });
        }

        function displayImportResults(results) {
            const summary = results.summary;
            const errors = results.errors || [];
            const status = results.status;

            // Hide status indicator and re-enable file input
            $('#importStatusContainer').slideUp();
            $('#csv_file').prop('disabled', false);

            // Clear previous errors
            $('#errorList').empty();

            // Clear the import status from backend after displaying
            $.post('{{ route('emp.clearImportStatus') }}', {
                _token: '{{ csrf_token() }}'
            });

            if (status === 'blocked') {
                // License limit exceeded
                const licenseError = errors.find(e => e.type === 'license_limit_exceeded');
                if (licenseError) {
                    toastr.error(licenseError.error, 'Import Blocked - License Limit', {
                        timeOut: 10000,
                        extendedTimeOut: 5000
                    });

                    // Show error in the modal
                    $('#errorList').html(
                        `<div class="alert alert-danger">
                            <strong><i class="ti ti-alert-circle me-1"></i>Import Blocked - License Limit Exceeded</strong>
                            <p class="mb-0 mt-2">${licenseError.error}</p>
                        </div>`
                    );
                } else {
                    toastr.error('Import was blocked. Please check the requirements.', 'Import Blocked');
                }
                return;
            }

            if (status === 'failed') {
                toastr.error('Import failed. Please check the errors below.', 'Import Failed');

                if (errors.length > 0) {
                    let errorHtml = '<div class="alert alert-danger"><strong>Import Errors:</strong><hr>';
                    errors.forEach(function(err) {
                        errorHtml += `<div class="mb-1">${err.error || err.message || JSON.stringify(err)}</div>`;
                    });
                    errorHtml += '</div>';
                    $('#errorList').html(errorHtml);
                }
                return;
            }

            // Success case
            if (summary.successful_imports > 0) {
                toastr.success(
                    `Successfully imported ${summary.successful_imports} employee(s)!` +
                    (summary.skipped_records > 0 ? ` (${summary.skipped_records} skipped - already exist)` : ''),
                    'Import Completed', {
                        timeOut: 7000,
                        progressBar: true
                    }
                );

                // Close modal and reload the employee list
                $('#upload_employee').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }

            if (summary.errors_count > 0) {
                toastr.warning(
                    `Import completed with ${summary.errors_count} error(s). Check details below.`,
                    'Import Completed with Errors', {
                        timeOut: 7000
                    }
                );

                // Show errors in the error list
                let errorHtml = '<div class="alert alert-warning"><strong>Import Errors:</strong><hr>';
                errors.forEach(function(err) {
                    errorHtml += `<div class="mb-1"><strong>Row ${err.row}:</strong> ${err.error}</div>`;
                });
                errorHtml += '</div>';
                $('#errorList').html(errorHtml);
            }

            if (summary.successful_imports === 0 && summary.errors_count === 0 && summary.skipped_records > 0) {
                toastr.info(
                    `All ${summary.skipped_records} employee(s) already exist in the system.`,
                    'No New Records', {
                        timeOut: 5000
                    }
                );

                // Close modal after showing message
                setTimeout(function() {
                    $('#upload_employee').modal('hide');
                }, 2000);
            }
        }
    </script>

    <script>
        window.previewSelectedImageAdd = function(event) {
            const input = event.target;
            const preview = document.getElementById('previewImage');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        document.getElementById('cancelImageBtn')?.addEventListener('click', function() {
            const preview = document.getElementById('previewImage');
            const input = document.getElementById('profileImageInput');

            preview.src = "{{ URL::asset('build/img/users/user-13.jpg') }}";
            input.value = '';
        });
    </script>

    <script>
        window.editPreviewSelectedImage = function(event) {
            const editInput = event.target;
            const editPreview = document.getElementById('editPreviewImage');

            if (editInput.files && editInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    editPreview.src = e.target.result;
                };
                reader.onerror = function() {
                    alert('Error reading file.');
                };
                reader.readAsDataURL(editInput.files[0]);
            }
        };

        document.getElementById('editCancelImageBtn')?.addEventListener('click', function() {
            const editPreview = document.getElementById('editPreviewImage');
            const editInput = document.getElementById('editProfileImageInput');

            editPreview.src = currentImagePath;
            editInput.value = ''; // Reset the file input
        });
    </script>

    <script>
        function fetchNextEmployeeId() {
            const prefix = $('#empIdPrefix').val();

            if (!prefix) {
                $('#employeeId').val('');
                return;
            }

            $.ajax({
                url: '{{ route('getNextEmployeeId') }}',
                type: 'GET',
                data: {
                    prefix: prefix
                },
                success: function(response) {
                    $('#employeeId').val(response.next_employee_serial);
                },
                error: function(xhr) {
                    console.error(xhr);
                    toastr.error('Failed to fetch Employee ID');
                }
            });
        }

        // Fetch for add modal
        $('#empIdPrefix').on('change', fetchNextEmployeeId);

        $(document).ready(fetchNextEmployeeId);
    </script>

    <script>
        function editEmployee(id) {
            $.ajax({
                url: routes.getEmployeeDetails,
                method: 'GET',
                data: {
                    emp_id: id,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        const emp = response.employee;

                        $('#editUserId').val(emp.id);
                        $('#editFirstName').val(emp.personal_information.first_name);
                        $('#editMiddleName').val(emp.personal_information.middle_name);
                        $('#editLastName').val(emp.personal_information.last_name);
                        $('#editSuffix').val(emp.personal_information.suffix);
                        $('#editEmail').val(emp.email);
                        $('#editUserName').val(emp.username);
                        $('#editPassword').val('');
                        $('#editConfirmPassword').val('');
                        $('#editPhoneNumber').val(emp.personal_information.phone_number);
                        $('#editDateHired').val(emp.employment_detail.date_hired);
                        $('#editBiometricsId').val(emp.employment_detail.biometrics_id);

                        // Set profile picture
                        let profilePictureSrc = "{{ asset('storage/default-profile.jpg') }}";
                        if (emp.personal_information.profile_picture) {
                            profilePictureSrc = "{{ asset('storage/') }}/" + emp.personal_information
                                .profile_picture;
                        }
                        $('#editPreviewImage').attr('src', profilePictureSrc);

                        // Null handling for role id with logs
                        let roleId = '';
                        if (emp.user_permission && emp.user_permission.role_id) {
                            roleId = emp.user_permission.role_id;
                        } else {
                            console.log('Role ID is null or missing.');
                            console.log('emp.user_permission:', emp.user_permission);
                            console.log('emp:', emp);
                        }
                        $('#editRoleId').val(roleId).trigger('change');

                        $('#editBranchId').val(emp.employment_detail.branch_id).trigger('change');
                        $('#editDepartmentSelect').val(emp.employment_detail.department_id).trigger('change');
                        $('#editDesignationSelect').val(emp.employment_detail.designation_id).trigger('change');
                        $('#editEmploymentType').val(emp.employment_detail.employment_type).trigger('change');
                        $('#editEmploymentStatus').val(emp.employment_detail.employment_status).trigger(
                            'change');
                        $('#editReportingTo').val(emp.employment_detail.reporting_to).trigger('change');

                        const fullId = emp.employment_detail.employee_id;

                        if (fullId) {
                            const parts = fullId.split('-');

                            if (parts.length >= 2) {
                                const prefix = parts.slice(0, -1).join('-');
                                const serial = parts[parts.length - 1];

                                $('#editEmpIdPrefix').val(prefix).trigger('change');
                                $('#editEmployeeId').val(serial);
                            } else {

                                $('#editEmpIdPrefix').val('').trigger('change');
                                $('#editEmployeeId').val(fullId);
                            }
                        } else {
                            console.warn('Employee ID is empty or null');
                            $('#editEmpIdPrefix').val('').trigger('change');
                            $('#editEmployeeId').val('');
                        }

                        $('#edit_employee').modal('show');
                    } else {
                        toastr.warning('Employee not found.');
                    }
                },
                error: function() {
                    toastr.error('An error occurred while getting employee details.');
                }
            });
        }
    </script>

    <script>
        // Tab switching functionality
        $(document).ready(function() {
            // Employee List Tab
            $('#employeeListTab').on('click', function(e) {
                e.preventDefault();
                
                // Update tab styling
                $('#employeeListTab').removeClass('btn-white').addClass('btn-primary active');
                $('#employeeArchiveTab').removeClass('btn-primary active').addClass('btn-white');
                
                // Show/Hide cards
                $('#employeeListCard').show();
                $('#employeeArchiveCard').hide();
            });

            // Employee Archive Tab
            $('#employeeArchiveTab').on('click', function(e) {
                e.preventDefault();
                
                // Update tab styling
                $('#employeeArchiveTab').removeClass('btn-white').addClass('btn-primary active');
                $('#employeeListTab').removeClass('btn-primary active').addClass('btn-white');
                
                // Show/Hide cards
                $('#employeeListCard').hide();
                $('#employeeArchiveCard').show();
                
                // Load inactive employees when archive tab is clicked
                filterArchive();
            });
            
            // Add event listener for entries dropdown in archive
            $('#archive_entries').on('change', function() {
                filterArchive();
            });
        });

        // Filter function for archive
        function filterArchive() {
            const branch = $('#archive_branch_filter').val();
            const department = $('#archive_department_filter').val();
            const designation = $('#archive_designation_filter').val();
            const sort = $('#archive_sortby_filter').val();
            const search = $('#archive_search').val();
            const entries = $('#archive_entries').val();

            $.ajax({
                url: '{{ route('empList-filter') }}',
                type: 'GET',
                data: {
                    branch: branch,
                    department: department,
                    designation: designation,
                    status: 0, // Only inactive employees for archive
                    sort_by: sort,
                    search: search,
                    length: entries
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#employeeArchiveTableBody').html(response.html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error filtering archive:', error);
                }
            });
        }
    </script>
@endpush
