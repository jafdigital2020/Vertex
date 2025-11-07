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
                                            @if (in_array('Create', $permission))

                                                <div class="d-flex gap-2 mb-2">

                                                    <button type="button" id="addEmployeeBtn"  
                                                        class="btn btn-primary d-flex align-items-center gap-2" >
                                                        <i class="ti ti-circle-plus"></i> Add Employee
                                                    </button>

                                                    {{-- <a href="#" data-bs-toggle="modal" data-bs-target="#upload_employee"
                                                        class="btn btn-secondary d-flex align-items-center gap-2">
                                                        <i class="ti ti-upload"></i>Upload Employee
                                                    </a> --}}

                                            @if(auth()->check() && auth()->user()->employmentDetail?->branch_id)
                                                <a href="#" id="topUpBtn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#topup_credits"
                                                    class="btn btn-outline-primary d-flex align-items-center gap-2">
                                                    <i class="ti ti-wallet"></i> Top Up Credits
                                                </a>
                                            @endif

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

                                    <div class="row">
                                        <div class="col-lg-3 col-md-6 d-flex">
                                            <div class="card flex-fill">
                                                <div class="card-body d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center overflow-hidden">
                                                        <div>
                                                            <span class="avatar avatar-lg bg-dark rounded-circle"><i class="ti ti-users"></i></span>
                                                        </div>
                                                        <div class="ms-2 overflow-hidden">
                                                            <p class="fs-12 fw-medium mb-1 text-truncate">Total Employee</p>
                                                            <h4>{{ str_pad($employees->count(), 2, '0', STR_PAD_LEFT) }}</h4>
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
                                                            <h4>{{ str_pad(
        $employees->filter(function ($e) {
            return $e->employmentDetail && $e->employmentDetail->status == 1;
        })->count(),
        2,
        '0',
        STR_PAD_LEFT,
    ) }}
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
                                                            <h4>{{ str_pad(
        $employees->filter(function ($e) {
            return $e->employmentDetail && $e->employmentDetail->status == 0;
        })->count(),
        2,
        '0',
        STR_PAD_LEFT,
    ) }}
                                                            </h4>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(auth()->check() && auth()->user()->employmentDetail?->branch_id)
                                            <div class="col-lg-3 col-md-6 d-flex">
                                                <div class="card flex-fill">
                                                    <div class="card-body d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center overflow-hidden">
                                                            <div>
                                                                <span class="avatar avatar-lg bg-info rounded-circle"><i class="ti ti-user-plus"></i></span>
                                                            </div>
                                                            <div class="ms-2 overflow-hidden">
                                                                <p class="fs-12 fw-medium mb-1 text-truncate">Employee Credits</p>
                                                                <h4><span id="employee-credits-count"></span></h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-lg-3 col-md-6 d-flex">
                                                <div class="card flex-fill">
                                                    <div class="card-body d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center overflow-hidden">
                                                            <div>
                                                                <span class="avatar avatar-lg bg-info rounded-circle"><i class="ti ti-user-plus"></i></span>
                                                            </div>
                                                            <div class="ms-2 overflow-hidden">
                                                                <p class="fs-12 fw-medium mb-1 text-truncate">New Joiners</p>
                                                                <h4>
                                                                    {{ str_pad(
            $employees->filter(function ($e) {
                return $e->employmentDetail && \Carbon\Carbon::parse($e->employmentDetail->date_hired)->isSameMonth(now());
            })->count(),
            2,
            '0',
            STR_PAD_LEFT,
        ) }}
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        </div>
                                        <!-- Remove extra closing divs and margin -->
                                        <div class="card mb-0">
                                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                                                <h5>Employee List</h5>
                                                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                                    <div class="form-group me-2">
                                                        <select name="branch_filter" id="branch_filter" class="select2 form-select" oninput="filter();"
                                                            style="width:200px;">
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
                                                                                src="{{ $employee->personalInformation->profile_picture ? asset('storage/' . $employee->personalInformation->profile_picture) : URL::asset('build/img/users/user-13.jpg') }}"
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
                                                                <td>{{ $detail?->department?->department_name ?? 'Admin Department' }}</td>
                                                                <td>{{ $detail?->designation?->designation_name ?? 'Admin' }}</td>
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

                                </div>

                                @include('layout.partials.footer-company')

                            </div>

                            {{-- ADD EMPLOYEE --}}
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
                                                    <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="col-md-4">
                                                                    <select class="select" id="empIdPrefix" name="emp_prefix">
                                                                        <option value="">Select Prefix</option>
                                                                        @foreach ($prefixes as $i => $prefix)
                                                                            <option value="{{ $prefix->prefix_name }}" {{ $i === 0 ? 'selected' : '' }}>
                                                                                {{ $prefix->prefix_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                        </div>
                                                        <span>-</span>
                                                        <div class="col-md-4">
                                                            <input type="text" id="monthYear" name="month_year" class="form-control"
                                                                value="{{ date('m') . '-' . date('Y') }}">
                                                        </div>
                                                        <span>-</span>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control" name="employee_id" id="employeeId">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Biometrics ID</label>
                                                    <input type="text" class="form-control" name="biometrics_id" id="biometricsId" placeholder="Enter biometrics ID">
                                                </div>
                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" name="fullname" id="fullName" placeholder="Enter full name" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Suffix</label>
                                                                    <input type="text" class="form-control" name="suffix" id="suffix">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 d-none d-lg-block">
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
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Phone Number
                                                                        <input type="text" class="form-control" name="phone_number"
                                                                            id="phoneNumber">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Role<span class="text-danger"> *</span></label>
                                                                    <select name="role_id" id="role_id" class="form-select select2" placeholder="Select Role">
                                                                        <option value="" disabled>Select Role</option>
                                                                        @foreach ($roles->unique('role_name') as $role)
                                                                            <option value="{{ $role->id }}" {{ strtolower($role->role_name) == 'employee' ? 'selected' : '' }}>
                                                                                {{ $role->role_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Branch</label>
                                                                    <select id="addBranchId" name="branch_id" class="form-select select2"
                                                                        placeholder="Select Branch">
                                                                        @foreach ($branches as $i => $branch)
                                                                            <option value="{{ $branch->id }}" {{ $i === 0 ? 'selected' : '' }}>
                                                                                {{ $branch->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Department</label>
                                                                    <select id="add_departmentSelect" name="department_id"
                                                                        class="form-select select2" placeholder="Select Department">
                                                                        @foreach ($departments as $i => $department)
                                                                            <option value="{{ $department->id }}" {{ $i === 0 ? 'selected' : '' }}>
                                                                                {{ $department->department_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Designation</label>
                                                                    <select id="add_designationSelect" name="designation_id"
                                                                        class="form-select select2" placeholder="Select Designation">
                                                                        @foreach ($designations as $i => $designation)
                                                                            <option value="{{ $designation->id }}" {{ $i === 0 ? 'selected' : '' }}>
                                                                                {{ $designation->designation_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Employment Status</label>
                                                                    <select id="employmentStatus" name="employment_status"
                                                                        class="form-select select2" placeholder="Select Status">
                                                                        <option value="" disabled>Select Status</option>
                                                                        <option value="Probationary">Probationary</option>
                                                                        <option value="Regular" selected>Regular</option>
                                                                        <option value="Project-Based">Project Based</option>
                                                                        <option value="Seasonal">Seasonal</option>
                                                                        <option value="Contractual">Contractual</option>
                                                                        <option value="Casual">Casual</option>
                                                                        <option value="Intern/OJT">Intern/OJT</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Employment Type</label>
                                                                    <select id="employmentType" name="employment_type"
                                                                        class="form-select select2" placeholder="Select Type">
                                                                        <option value="" disabled>Select Type</option>
                                                                        <option value="Full-Time" selected>Full-Time</option>
                                                                        <option value="Part-Time">Part-time</option>
                                                                        <option value="Freelancer">Freelancer</option>
                                                                        <option value="Consultant">Consultant</option>
                                                                        <option value="Apprentice">Apprentice</option>
                                                                        <option value="Remote">Remote</option>
                                                                        <option value="Field-Based">Field-Based</option>
                                                                        <option value="Reliever">Reliever</option>
                                                                        <option value="Striker">Striker</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 d-none d-lg-block">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Reporting To:</label>
                                                                    <select id="reportingTo" name="reporting_to" class="form-select select2">
                                                                        <option value="" disabled>Select Employee</option>
                                                                        @foreach ($employees as $i => $employee)
                                                                            <option value="{{ $employee->id }}" {{ $i === count($employees) - 1 ? 'selected' : '' }}>
                                                                                {{ $employee->personalInformation->full_name ?? '' }}
                                                                            </option>
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
                                                                        <img id="editPreviewImage" alt="Profile Image" class="rounded-circle"
                                                                            src="{{ asset('storage/' . ($employee->personalInformation->profile_picture ?? 'default-profile.jpg')) }}">
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
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Middle Name</label>
                                                                    <input type="text" class="form-control" name="middle_name"
                                                                        id="editMiddleName">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Suffix</label>
                                                                    <input type="text" class="form-control" name="suffix" id="editSuffix">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Employee ID <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="d-flex align-items-center gap-1">
                                                                        <div class="col-md-4">
                                                                            <select class="form-select" id="editEmpIdPrefix" name="emp_prefix">
                                                                                <option value="" disabled>Select Prefix</option>
                                                                                @foreach ($prefixes as $i => $prefix)
                                                                                    <option value="{{ $prefix->prefix_name }}" {{ $i === 0 ? 'selected' : '' }}>
                                                                                        {{ $prefix->prefix_name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <span>-</span>
                                                                        <div class="col-md-4">
                                                                            <input type="text" id="editMonthYear" name="month_year"
                                                                                class="form-control" value="{{ date('m') . '-' . date('Y') }}">
                                                                        </div>
                                                                        <span>-</span>
                                                                        <div class="col-md-3">
                                                                            <input type="text" class="form-control" name="employee_id"
                                                                                id="editEmployeeId">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Biometrics ID</label>
                                                                    <input type="text" class="form-control" name="biometrics_id" id="editBiometricsId" placeholder="Enter biometrics ID">
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
                                                                        <option value="Consultant">Consultant</option>
                                                                        <option value="Apprentice">Apprentice</option>
                                                                        <option value="Remote">Remote</option>
                                                                        <option value="Field-Based">Field-Based</option>
                                                                        <option value="Reliever">Reliever</option>
                                                                        <option value="Striker">Striker</option>
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

                            <!-- Credits Top Up Modal -->
                            <div class="modal fade" id="topup_credits" tabindex="-1" aria-labelledby="topupCreditsLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form id="topupCreditsForm">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="topupCreditsLabel">Top Up Employee Credits</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Business</label>
                                                    <select id="topup_branch_id" name="branch_id" class="form-select" required>
                                                        @foreach ($branches as $branch)
                                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Defaults to your current business or selected filter.</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Credits to add</label>
                                                    <input type="number" min="1" step="1" class="form-control" id="topup_amount" name="amount" required>
                                                    <small class="text-muted">Enter how many credits you want to add.</small>
                                                </div>

                                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                                    <i class="ti ti-info-circle me-2"></i>
                                                    <span>Credits increase the allowed number of employees you can add.</span>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" id="topupSubmitBtn">Add Credits</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            var currentImagePath =
                "{{ asset('storage/' . ($employee->personalInformation->profile_picture ?? 'default-profile.jpg')) }}";
        </script>
        <script src="{{ asset('build/js/datatable-filtered.js') }}"></script>
        <script src="{{ asset('build/js/employeelist.js') }}"></script>
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
            $(document).ready(function() {
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
                                toastr.success(
                                    'Import successfully queued. It will be processed in the background.'
                                );

                                // Check if there are any import warnings
                                if (response.errors.length > 0) {
                                    response.errors.forEach(function(err) {
                                        toastr.warning(
                                            `Import warning: Row ${err.row} - ${err.error}`
                                        );
                                        $('#errorList').append(
                                            `<div class="alert alert-warning small">
                                        <strong>Row:</strong> ${err.row}<br>
                                        <strong>Error:</strong> ${err.error}
                                    </div>`
                                        );
                                    });
                                }

                                // Clear form and close modal after a delay
                                $('#csvUploadForm')[0].reset();
                                setTimeout(() => {
                                    $('#upload_employee').modal('hide');
                                }, 1500);
                            } else {
                                toastr.error(response.message || 'Upload failed.');
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message ||
                                'An unexpected error occurred.';
                            toastr.error(msg);
                        },
                        complete: function() {
                            $('#csvUploadForm button[type="submit"]').prop('disabled', false).text(
                                'Upload');
                        }
                    });
                });
            });
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
                const monthYear = $('#monthYear').val();

                if (!prefix || !monthYear) {
                    $('#employeeId').val('');
                    return;
                }

                $.ajax({
                    url: '{{ route('getNextEmployeeId') }}',
                    type: 'GET',
                    data: {
                        prefix: prefix,
                        month_year: monthYear
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

            $('#empIdPrefix, #monthYear').on('change', fetchNextEmployeeId);

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

                            // Set profile picture
                            let profilePictureSrc = "{{ asset('storage/default-profile.jpg') }}";
                            if (emp.personal_information.profile_picture) {
                                profilePictureSrc = "{{ asset('storage/') }}/" + emp.personal_information.profile_picture;
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

                            // Add biometrics_id
                            $('#editBiometricsId').val(emp.employment_detail.biometrics_id || '');

                            const fullId = emp.employment_detail.employee_id;
                            const parts = fullId.split('-');
                            if (parts.length >= 4) {
                                const prefix = parts.slice(0, parts.length - 3).join('-');
                                const monthYear = parts[parts.length - 3] + '-' + parts[parts.length - 2];
                                const serial = parts[parts.length - 1];

                                $('#editEmpIdPrefix').val(prefix).trigger('change');
                                $('#editMonthYear').val(monthYear);
                                $('#editEmployeeId').val(serial);
                            } else {
                                console.warn('Invalid employee_id format:', fullId);
                                $('#editEmpIdPrefix').val('').trigger('change');
                                $('#editMonthYear').val('');
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
          
            // Fetch and display employee credits for selected branch
            function fetchEmployeeCredits(branchId) {
                if (!branchId) {
                    $('#employee-credits-count').text('00');
                    // $('#addEmployeeBtn').prop('disabled', true);
                    return;
                } 
                
                $.ajax({
                    url: "{{ route('api.employee-credits') }}",
                    type: 'GET',
                    data: { branch_id: branchId },
                    success: function (response) {
                        let credits = parseInt(response.employee_credits ?? 0, 10);
                        $('#employee-credits-count').text(credits.toString().padStart(2, '0')); 

                        $('#addEmployeeBtn').click(function (e) {
                            e.preventDefault();   
                            if (credits === 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'No Credits Available',
                                    text: 'You need to top up credits before adding an employee.',
                                    confirmButtonText: 'OK'
                                });
                            } else { 
                                $('#add_employee').modal('show');
                            }
                        });
                    },
                    error: function () {
                        $('#employee-credits-count').text('00');
                        $('#addEmployeeBtn').prop('disabled', true);
                    }
                });
            }

                $(document).ready(function() {
                    // Replace the Employee Credits count with a span for dynamic update
                    const creditsCard = $('p:contains("Employee Credits")').closest('.card-body').find('h4').first();
                    if (creditsCard.find('#employee-credits-count').length === 0) {
                        const currentVal = creditsCard.text().trim();
                        creditsCard.html('<span id="employee-credits-count">' + currentVal + '</span>');
                    }

                    // Get the branch id of the authenticated user from backend
                    let userBranchId = "{{ auth()->user()->employmentDetail->branch_id ?? '' }}";

                    // Initial fetch (use user's branch if available, else selected branch)
                    const initialBranch = userBranchId || $('#branch_filter').val();
                    fetchEmployeeCredits(initialBranch);

                    // Update on branch filter change
                    $('#branch_filter').on('change', function() {
                        fetchEmployeeCredits($(this).val());
                    });
                });
            </script>

            <script>
                $('#topupCreditsForm').on('submit', function(e) {
                    e.preventDefault();

                    const branchId = $('#topup_branch_id').val();
                    const additionalCredits = parseInt($('#topup_amount').val(), 10);

                    if (!branchId || additionalCredits < 1) {
                        toastr.error('Please select a branch and enter valid credits.');
                        return;
                    }

                    const endpoint = `/api/subscriptions/${branchId}/add-employee-credits`;

                    $.ajax({
                        url: endpoint,
                        method: 'POST',
                        data: JSON.stringify({ additional_credits: additionalCredits }),
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success && res.checkoutUrl) {
                                window.location.href = res.checkoutUrl;
                            } else {
                                toastr.success(res.message || 'Top-up initiated.');
                                $('#topup_credits').modal('hide');
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Failed to top up credits.';
                            toastr.error(msg);
                        }
                    });
                });
            </script>
@endpush
