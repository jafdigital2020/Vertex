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

                            <button type="button" id="addEmployeeBtn" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="ti ti-circle-plus"></i> Add Employee
                            </button>

                            {{-- <a href="#" data-bs-toggle="modal" data-bs-target="#upload_employee"
                                class="btn btn-secondary d-flex align-items-center gap-2">
                                <i class="ti ti-upload"></i>Upload Employee
                            </a> --}}

                            @if(auth()->check() && auth()->user()->employmentDetail?->branch_id)
                                <a href="#" id="topUpBtn" data-bs-toggle="modal" data-bs-target="#topup_credits"
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

            <div class="row g-3 mb-4">
                <!-- Total Employee -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #0f8b8d 0%, #0b6b67 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Total Employee</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($employees->count(), 2, '0', STR_PAD_LEFT) }}
                                </h2>
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
                                    <div
                                        style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
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
                                    <div
                                        style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-user-share"
                                            style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
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
                                    <div
                                        style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-user-pause"
                                            style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->employmentDetail?->branch_id)
                    <!-- Employee Credits -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card text-white position-relative overflow-hidden"
                            style="border-radius:10px; background: linear-gradient(135deg, #f6b21a 0%, #f09f00 100%); min-height:120px;">
                            <div class="card-body d-flex align-items-center justify-content-between p-3">
                                <div class="me-3" style="z-index:3;">
                                    <p class="fs-12 fw-medium mb-1 text-white-75">Employee Credits</p>
                                    <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;"><span
                                            id="employee-credits-count"></span></h2>
                                    <small class="text-white-75">Credits</small>
                                </div>

                                <!-- Right icon circle group -->
                                <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                    <div
                                        style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                        <i class="ti ti-user-plus" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                    </div>
                                    <div
                                        style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                        <div
                                            style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                            <i class="ti ti-user-plus" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
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
                                                <div
                                                    style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                    <i class="ti ti-user-plus" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" oninput="filter();"
                                style="width:200px;">
                                @foreach ($branches as $i => $branch)
                                    <option value="{{ $branch->id }}" {{ $i === 0 ? 'selected' : '' }}>{{ $branch->name }}
                                    </option>
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
                            <select name="status_filter" id="status_filter" class="select2 form-select" oninput="filter()"
                                style="width:150px;">
                                <option value="" selected>All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select" onchange="filter()"
                                style="width:150px;">
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
                                                <a href="{{ url('employees/employee-details/' . $employee->id) }}" class="me-2"
                                                    title="View Full Details"><i class="ti ti-eye"></i></a>
                                            @endif
                                            @if (in_array('Update', $permission))
                                                <a href="#" class="me-2" onclick="editEmployee({{ $employee->id }})"><i
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
                                            <span class="badge d-inline-flex align-items-center badge-xs {{ $badgeClass }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
                                            </span>
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td>
                                                <div class="action-icon d-inline-flex">

                                                    @if (in_array('Update', $permission))
                                                        @if ($status == 0)
                                                            <a href="#" class="btn-activate" onclick="activateEmployee({{ $employee->id }})"
                                                                title="Activate"><i class="ti ti-circle-check"></i></a>
                                                        @else
                                                            <a href="#" class="btn-deactivate"
                                                                onclick="deactivateEmployee({{ $employee->id }})"><i class="ti ti-cancel"
                                                                    title="Deactivate"></i></a>
                                                        @endif
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="btn-delete" onclick="deleteEmployee({{ $employee->id }})">
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
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="addEmployeeForm" enctype="multipart/form-data">
                    <div class="contact-grids-tab">
                        <ul class="nav nav-underline" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab" data-bs-toggle="tab"
                                    data-bs-target="#basic-info" type="button" role="tab" aria-selected="true">Basic
                                    Information</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="info-tab"
                            tabindex="0">
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
                                                        <input type="file" name="profile_picture" id="profileImageInput"
                                                            class="form-control image-sign" accept="image/*"
                                                            onchange="previewSelectedImageAdd(event)">
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
                                                    <input type="text" class="form-control" name="employee_id"
                                                        id="employeeId">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Biometrics ID</label>
                                            <input type="text" class="form-control" name="biometrics_id" id="biometricsId"
                                                placeholder="Enter biometrics ID">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="fullname" id="fullName"
                                                placeholder="Enter full name" required>
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
                                            <select name="role_id" id="role_id" class="form-select select2"
                                                placeholder="Select Role">
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
                                            <select id="employmentType" name="employment_type" class="form-select select2"
                                                placeholder="Select Type">
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
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="editEmployeeForm" enctype="multipart/form-data">
                    <input type="hidden" name="editUserId" id="editUserId">
                    <div class="contact-grids-tab">
                        <ul class="nav nav-underline" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab" data-bs-toggle="tab"
                                    data-bs-target="#basic-info" type="button" role="tab" aria-selected="true">Basic
                                    Information</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="info-tab"
                            tabindex="0">
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
                                                        <input type="file" name="profile_picture" id="editProfileImageInput"
                                                            class="form-control image-sign" accept="image/*"
                                                            onchange="editPreviewSelectedImage(event)">
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
                                            <input type="text" class="form-control" name="first_name" id="editFirstName">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" id="editLastName">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" name="middle_name" id="editMiddleName">
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
                                            <label class="form-label">Employee ID <span class="text-danger">*</span></label>
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
                                            <input type="text" class="form-control" name="biometrics_id"
                                                id="editBiometricsId" placeholder="Enter biometrics ID">
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
                                            <input type="text" class="form-control" name="username" id="editUserName">
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
                                                        {{ $department->department_name }}
                                                    </option>
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
                                                        {{ $designation->designation_name }}
                                                    </option>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="topupCreditsForm">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header text-white position-relative overflow-hidden"
                        style="background: linear-gradient(135deg, #008080 0%, #12515D 100%); border: none;">
                        <!-- Decorative background elements -->
                        <div
                            style="position: absolute; right: -30px; top: -30px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.1);">
                        </div>
                        <div
                            style="position: absolute; right: 20px; bottom: -20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.08);">
                        </div>

                        <div class="d-flex align-items-center" style="z-index: 2;">
                            <div class="avatar avatar-md text-white rounded-circle me-3"
                                style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                                <i class="ti ti-wallet"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 text-white" id="topupCreditsLabel">Top Up Employee Credits</h5>
                                <small style="color: rgba(255,255,255,0.8);">Add more employee slots to your account</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Current Credits Display -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body d-flex align-items-center p-3">
                                        <div class="avatar avatar-lg rounded-circle me-3 flex-shrink-0"
                                            style="background: linear-gradient(135deg, #008080 0%, #12515D 100%);">
                                            <i class="ti ti-wallet text-white fs-20"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="mb-1 fw-semibold text-gray-700">Current Credits</h6>
                                                    <h4 class="mb-0 fw-bold text-gray-900" id="current-credits-display">--
                                                    </h4>
                                                    <small class="text-muted">Available slots</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body d-flex align-items-center p-3">
                                        <div class="avatar avatar-lg rounded-circle me-3 flex-shrink-0"
                                            style="background: linear-gradient(135deg, #ed7464 0%, #b53654 100%);">
                                            <i class="ti ti-user-check text-white fs-20"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="mb-1 fw-semibold text-gray-700">Active Employees</h6>
                                                    <h4 class="mb-0 fw-bold text-gray-900">{{ $employees->filter(function ($e) {
        return $e->employmentDetail && $e->employmentDetail->status == 1; })->count() }}</h4>
                                                    <small class="text-muted">Currently employed</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-building me-1"></i>Select Business
                            </label>
                            <select id="topup_branch_id" name="branch_id" class="form-select" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Choose the business to add credits to</small>
                        </div>

                        <!-- Credit Amount -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center mb-3">
                                <span class="avatar avatar-sm rounded-circle me-2"
                                    style="background: linear-gradient(135deg, #FFB400 0%, #ed7464 100%);">
                                    <i class="ti ti-shopping-cart text-white fs-12"></i>
                                </span>
                                <span style="color: #FFB400;">Credits to Purchase</span>
                            </label>

                            <div class="card border-0 shadow-sm" style="border-left: 4px solid #FFB400 !important;">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-gray-900 mb-2">Enter Number of Credits</h6>
                                                <p class="text-gray-600 mb-0">
                                                    <i class="ti ti-info-circle me-1" style="color: #FFB400;"></i>
                                                    Each credit allows you to add one employee to your account
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="flex-grow-1">
                                                    <label
                                                        class="form-label small fw-medium text-gray-600 mb-1">Credits</label>
                                                    <input type="number" min="1" step="1"
                                                        class="form-control form-control-lg text-center fw-bold border-2"
                                                        id="topup_amount" name="amount" placeholder="0"
                                                        style="border-color: #FFB400; color: #008080; font-size: 1.1rem;"
                                                        required>
                                                </div>
                                                <div class="text-center">
                                                    <div class="small text-gray-500 mb-1">Price each</div>
                                                    <div class="badge px-3 py-2"
                                                        style="background: linear-gradient(135deg, #FFB40015 0%, #ed746415 100%); color: #008080; font-size: 0.9rem;">
                                                        ₱49.00
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quick Selection Buttons -->
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="d-flex align-items-center mb-2">
                                            <small class="text-gray-500 fw-medium">Quick select:</small>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-outline-secondary btn-sm quick-select"
                                                data-credits="1">
                                                1 Credit
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm quick-select"
                                                data-credits="5">
                                                5 Credits
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm quick-select"
                                                data-credits="10">
                                                10 Credits
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm quick-select"
                                                data-credits="20">
                                                20 Credits
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm quick-select"
                                                data-credits="50">
                                                50 Credits
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #008080 !important;">
                            <div class="card-header border-bottom-0 py-3"
                                style="background: linear-gradient(90deg, #00808015 0%, #12515D15 100%);">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <span class="avatar avatar-sm rounded-circle me-2"
                                        style="background: linear-gradient(135deg, #008080 0%, #12515D 100%);">
                                        <i class="ti ti-receipt text-white fs-12"></i>
                                    </span>
                                    <span style="color: #008080; font-weight: 600;">Order Summary</span>
                                </h6>
                            </div>
                            <div class="card-body pt-3">
                                <div class="d-flex justify-content-between mb-3 p-3 rounded"
                                    style="background: linear-gradient(90deg, #00808008 0%, #12515D08 100%);">
                                    <span class="fw-medium text-gray-700">Credits to add:</span>
                                    <span class="fw-bold" style="color: #008080;" id="summary-credits">0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-gray-600">Price per credit:</span>
                                    <span class="fw-semibold text-gray-900" id="summary-price-per">₱49.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="fw-semibold text-gray-900" id="summary-subtotal">₱0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <small class="text-gray-500">VAT (12%):</small>
                                    <small class="text-gray-600" id="summary-fee">₱0.00</small>
                                </div>
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold fs-16 text-gray-900">Total Amount:</span>
                                        <span class="fw-bold fs-18" style="color: #008080;" id="summary-total">₱0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Information Alert -->
                        <div class="alert border-0 shadow-sm"
                            style="background: linear-gradient(90deg, #b5365415 0%, #ed746415 100%); border-left: 4px solid #b53654 !important;"
                            role="alert">
                            <div class="d-flex">
                                <span class="avatar avatar-sm rounded-circle me-3 flex-shrink-0"
                                    style="background: linear-gradient(135deg, #b53654 0%, #ed7464 100%);">
                                    <i class="ti ti-info-circle text-white fs-12"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold mb-2" style="color: #b53654;">About Employee Credits</div>
                                    <ul class="mb-0 ps-3 small text-gray-600">
                                        <li>Each credit allows you to add one employee to your account</li>
                                        <li>Credits never expire and can be used anytime</li>
                                        <li>All prices are inclusive of 12% VAT</li>
                                        <li>Credits are automatically applied to your selected business</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <div class="d-flex gap-3 w-100">
                            <button type="button" class="btn btn-outline-secondary flex-fill py-2" data-bs-dismiss="modal">
                                <i class="ti ti-x me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn text-white flex-fill py-2" id="topupSubmitBtn" disabled
                                style="background: linear-gradient(135deg, #008080 0%, #12515D 100%); border: none; box-shadow: 0 2px 4px rgba(0, 128, 128, 0.2);">
                                <i class="ti ti-credit-card me-1"></i>Proceed to Payment
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="upload_employee" tabindex="-1" aria-labelledby="uploadEmployeeLabel" aria-hidden="true">
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
                            <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
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
        $(document).ready(function () {
            $('#csvUploadForm').on('submit', function (e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('#errorList').empty();

                $.ajax({
                    url: '{{ route('importEmployeeCSV') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        $('#csvUploadForm button[type="submit"]').prop('disabled', true).text(
                            'Uploading...');
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(
                                'Import successfully queued. It will be processed in the background.'
                            );

                            // Check if there are any import warnings
                            if (response.errors.length > 0) {
                                response.errors.forEach(function (err) {
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
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message ||
                            'An unexpected error occurred.';
                        toastr.error(msg);
                    },
                    complete: function () {
                        $('#csvUploadForm button[type="submit"]').prop('disabled', false).text(
                            'Upload');
                    }
                });
            });
        });
    </script>

    <script>
        window.previewSelectedImageAdd = function (event) {
            const input = event.target;
            const preview = document.getElementById('previewImage');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        document.getElementById('cancelImageBtn')?.addEventListener('click', function () {
            const preview = document.getElementById('previewImage');
            const input = document.getElementById('profileImageInput');

            preview.src = "{{ URL::asset('build/img/users/user-13.jpg') }}";
            input.value = '';
        });
    </script>

    <script>
        window.editPreviewSelectedImage = function (event) {
            const editInput = event.target;
            const editPreview = document.getElementById('editPreviewImage');

            if (editInput.files && editInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    editPreview.src = e.target.result;
                };
                reader.onerror = function () {
                    alert('Error reading file.');
                };
                reader.readAsDataURL(editInput.files[0]);
            }
        };

        document.getElementById('editCancelImageBtn')?.addEventListener('click', function () {
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
                success: function (response) {
                    $('#employeeId').val(response.next_employee_serial);
                },
                error: function (xhr) {
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
                success: function (response) {
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
                error: function () {
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

        $(document).ready(function () {
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
            $('#branch_filter').on('change', function () {
                fetchEmployeeCredits($(this).val());
            });
        });
    </script>

    <script>
        // Enhanced Top-up Credits Modal Functionality
        $(document).ready(function () {
            // Update current credits display when modal opens
            $('#topup_credits').on('show.bs.modal', function () {
                const currentCredits = $('#employee-credits-count').text() || '00';
                $('#current-credits-display').text(currentCredits);

                // Set default branch
                const currentBranchFilter = $('#branch_filter').val();
                if (currentBranchFilter) {
                    $('#topup_branch_id').val(currentBranchFilter);
                }

                updateOrderSummary();
            });

            // Handle amount input
            $('#topup_amount').on('input', function () {
                updateOrderSummary();
                updateQuickSelectButtons();
            });

            // Handle quick select buttons
            $('.quick-select').on('click', function () {
                const credits = $(this).data('credits');
                $('#topup_amount').val(credits);
                updateOrderSummary();
                updateQuickSelectButtons();
            });

            // Update quick select button states
            function updateQuickSelectButtons() {
                const currentValue = parseInt($('#topup_amount').val()) || 0;
                $('.quick-select').removeClass('btn-primary').addClass('btn-outline-secondary');
                $('.quick-select').each(function () {
                    if ($(this).data('credits') === currentValue) {
                        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
                    }
                });
            }

            // Update order summary
            function updateOrderSummary() {
                const credits = parseInt($('#topup_amount').val()) || 0;
                const pricePerCredit = 49.00;
                const subtotal = credits * pricePerCredit;
                const vatAmount = credits > 0 ? subtotal * 0.12 : 0;
                const finalTotal = subtotal + vatAmount;

                // Update display
                $('#summary-credits').text(credits);
                $('#summary-price-per').text('₱' + pricePerCredit.toFixed(2));
                $('#summary-subtotal').text('₱' + subtotal.toFixed(2));
                $('#summary-fee').text('₱' + vatAmount.toFixed(2));
                $('#summary-total').text('₱' + finalTotal.toFixed(2));

                // Enable/disable submit button
                $('#topupSubmitBtn').prop('disabled', credits <= 0);
            }

            // Handle branch change
            $('#topup_branch_id').on('change', function () {
                const branchId = $(this).val();
                if (branchId) {
                    // Fetch current credits for selected branch
                    $.ajax({
                        url: "{{ route('api.employee-credits') }}",
                        type: 'GET',
                        data: { branch_id: branchId },
                        success: function (response) {
                            const credits = parseInt(response.employee_credits ?? 0, 10);
                            $('#current-credits-display').text(credits.toString().padStart(2, '0'));
                        },
                        error: function () {
                            $('#current-credits-display').text('--');
                        }
                    });
                }
            });

            // Handle form submission
            $('#topupCreditsForm').on('submit', function (e) {
                e.preventDefault();

                const branchId = $('#topup_branch_id').val();
                const additionalCredits = parseInt($('#topup_amount').val(), 10);
                const totalAmount = parseFloat($('#summary-total').text().replace('₱', '').replace(',', ''));

                if (!branchId || additionalCredits < 1) {
                    toastr.error('Please select a branch and enter valid credits.');
                    return;
                }

                // Show loading state
                const $submitBtn = $('#topupSubmitBtn');
                const originalText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Processing...');

                const endpoint = `/api/subscriptions/${branchId}/add-employee-credits`;

                $.ajax({
                    url: endpoint,
                    method: 'POST',
                    data: JSON.stringify({
                        additional_credits: additionalCredits,
                        total_amount: totalAmount,
                        price_per_credit: parseFloat($('#summary-price-per').text().replace('₱', '').replace(',', ''))
                    }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.success && res.checkoutUrl) {
                            // Redirect to payment
                            window.location.href = res.checkoutUrl;
                        } else {
                            toastr.success(res.message || 'Credits added successfully!');
                            $('#topup_credits').modal('hide');

                            // Refresh credits display
                            fetchEmployeeCredits(branchId);
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to process credit top-up.';
                        toastr.error(msg);

                        // Reset button
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Reset modal when closed
            $('#topup_credits').on('hidden.bs.modal', function () {
                $('#topup_amount').val('');
                updateOrderSummary();
                updateQuickSelectButtons();
                $('#topupSubmitBtn').prop('disabled', true).html('<i class="ti ti-credit-card me-1"></i>Proceed to Payment');
            });
        });
    </script>
@endpush