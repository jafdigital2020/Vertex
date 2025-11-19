<?php $page = 'admin-dashboard'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Admin Dashboard</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Dashboard
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Export', $permission))
                        <div class="me-2 mb-2 d-flex align-items-center">
                            <div class="dropdown me-2">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            {{-- <div class="ms-2">
                                <select class="form-select select2" style="min-width: 140px;" id="branchSelect"
                                    name="branch">
                                    <option value="">All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                        </div>
                    @endif
                    <div class="mb-2">
                        <div class="input-icon w-120 position-relative">
                            <span class="input-icon-addon">
                                <i class="ti ti-calendar text-gray-9"></i>
                            </span>
                            <input type="text" class="form-control yearpicker" value="2025">
                        </div>
                    </div>
                    <div class="ms-2 head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Welcome Wrap -->
            <div class="card border-0">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap pb-1">
                    <div class="d-flex align-items-center mb-3">
                        <span class="avatar avatar-xl flex-shrink-0">
                            @if (Auth::check() && Auth::user()->personalInformation && Auth::user()->personalInformation->profile_picture)
                                <img src="{{ asset(Auth::user()->personalInformation->profile_picture) }}"
                                    class="rounded-circle" alt="img">
                            @else
                                <img src="{{ URL::asset('build/img/profiles/avatar-31.jpg') }}" class="rounded-circle"
                                    alt="img">
                            @endif
                        </span>
                        <div class="ms-3">
                            @if (Auth::check() && Auth::user()->personalInformation && Auth::user()->personalInformation->full_name)
                                <h3 class="mb-2">Welcome Back, {{ Auth::user()->personalInformation->full_name }} <a
                                        href="javascript:void(0);" class="edit-icon"><i class="ti ti-edit fs-14"></i></a>
                                </h3>
                            @else
                                @php
                                    $user = Auth::guard('web')->user() ?? Auth::guard('global')->user();
                                @endphp

                                @if ($user)
                                    <h3 class="mb-2">Welcome Back, {{ $user->username }} <a href="javascript:void(0);"
                                            class="edit-icon"><i class="ti ti-edit fs-14"></i></a></h3>
                                @else
                                    <h3 class="mb-2">Welcome Back!</h3>
                                @endif
                            @endif
                            {{-- <p>You have <span class="text-primary text-decoration-underline">21</span> Pending Approvals & <span class="text-primary text-decoration-underline">14</span> Leave Requests</p> --}}
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap mb-1">
                        <a href="{{ url('employees') }}" class="btn btn-secondary btn-md me-2 mb-2"><i
                                class="ti ti-square-rounded-plus me-1"></i>Add Employee</a>
                        <a href="{{ url('branches') }}" class="btn btn-primary btn-md mb-2"><i
                                class="ti ti-square-rounded-plus me-1"></i>Add Branches</a>
                    </div>
                </div>
            </div>
            <!-- /Welcome Wrap -->

            {{-- Cards --}}
            <div class="row">
                <!-- Statistics Cards -->
                <div class="col-12 mb-3">
                    <div class="row g-2">
                        <!-- Total Employees Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-md bg-secondary bg-opacity-10 rounded-2">
                                                <i class="ti ti-users fs-20 text-secondary"></i>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-light border-0" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical text-muted fs-20"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ route('employees') }}">View All</a>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-2 mt-4">
                                        <h4 class="fw-bold mb-1 text-dark">{{ $totalActiveUsers }}</h4>
                                        <p class="text-muted mb-0 fs-12">
                                            <span class="fw-medium"></span> Total Employees
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted fs-11">Active Rate</small>
                                        <span class="badge bg-success-subtle text-success fs-10 px-2 py-1">
                                            <i
                                                class="ti ti-trending-up me-1"></i>{{ number_format($totalUserPercentage, 1) }}%
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-success" style="width: {{ $totalUserPercentage }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Present Today Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-md bg-success bg-opacity-10 rounded-2">
                                                <i class="ti ti-check fs-20 text-success"></i>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-light border-0" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical text-muted fs-20"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ route('attendance-admin') }}">View
                                                        All</a></li>

                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-2 mt-4">
                                        <h4 class="fw-bold mb-1 text-dark">{{ $presentTodayUsersCount }}</h4>
                                        <p class="text-muted mb-0 fs-12">Present Today</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted fs-11">Attendance Rate</small>
                                        <span class="badge bg-success-subtle text-success fs-10 px-2 py-1">
                                            <i
                                                class="ti ti-trending-up me-1"></i>{{ number_format($presentTodayUsersPercentage, 1) }}%
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-success"
                                            style="width: {{ $presentTodayUsersPercentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Late Today Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-md bg-warning bg-opacity-10 rounded-2">
                                                <i class="ti ti-clock-edit fs-20 text-warning"></i>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-light border-0" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical text-muted fs-20"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ route('attendance-admin') }}">View
                                                        All</a></li>

                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-2 mt-4">
                                        <h4 class="fw-bold mb-1 text-dark">{{ $lateTodayUsersCount }}</h4>
                                        <p class="text-muted mb-0 fs-12">Late Arrivals</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted fs-11">Late Rate</small>
                                        <span class="badge bg-warning-subtle text-warning fs-10 px-2 py-1">
                                            <i
                                                class="ti ti-clock me-1"></i>{{ number_format($lateTodayUsersPercentage, 1) }}%
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-warning"
                                            style="width: {{ $lateTodayUsersPercentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Today Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-md bg-info bg-opacity-10 rounded-2">
                                                <i class="ti ti-beach fs-20 text-info"></i>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-light border-0" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical text-muted fs-20"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ route('leave-admin') }}">View
                                                        All</a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('leave-employees') }}">Request Leave</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mb-2 mt-4">
                                        <h4 class="fw-bold mb-1 text-dark">{{ $leaveTodayUsers }}</h4>
                                        <p class="text-muted mb-0 fs-12">Employees on Leave</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted fs-11">Leave Status</small>
                                        <span class="badge bg-info-subtle text-info fs-10 px-2 py-1">
                                            <i class="ti ti-beach me-1"></i>On Leave
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-info"
                                            style="width: {{ $totalUsers > 0 ? ($leaveTodayUsers / $totalUsers) * 100 : 0 }}%">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">

                <!-- Birthdays -->
                <div class="col-xxl-4 col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap bg-mustard">
                            <h5 class="mb-2 text-white">Birthdays</h5>
                        </div>
                        <div class="card-body pb-1">
                            <h6 class="mb-2">Today</h6>
                            {{-- Display users with birthdays today --}}
                            @if (count($birthdayTodayUsers) > 0)
                                @foreach ($birthdayTodayUsers as $user)
                                    <div class="bg-light p-2 border border-dashed rounded-top mb-3 text-muted">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="avatar">
                                                    <img src="{{ asset('storage/' . ($user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                        class="rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2 overflow-hidden">
                                                    <h6 class="fs-medium">
                                                        {{ $user->personalInformation->first_name }}
                                                        {{ $user->personalInformation->middle_name ? $user->personalInformation->middle_name . ' ' : '' }}
                                                        {{ $user->personalInformation->last_name }}
                                                        {{ $user->personalInformation->suffix ?? '' }}
                                                    </h6>
                                                    <p class="fs-13">
                                                        {{ $user->employmentDetail->department->department_name ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="badge bg-success fs-10 px-3 py-1">🎉 Happy Birthday</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="bg-light-mustard p-3 border border-dashed rounded-top mb-3 text-center text-muted w-100" style="width:100%; min-width:100%;">
                                    No Birthdays Today
                                </div>
                            @endif

                            {{-- Display upcoming birthdays --}}
                            <h6 class="mb-2">Upcoming Birthdays</h6>
                            @if (count($nearestBirthdays) > 0)
                                @foreach ($nearestBirthdays as $nearestBirthday)
                                    <div class="bg-light p-2 border border-dashed rounded-top mb-3 text-muted">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="avatar">
                                                    <img src="{{ asset('storage/' . ($nearestBirthday->profile_picture ?? 'default-profile.jpg')) }}"
                                                        class="rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2 overflow-hidden">
                                                    <h6 class="fs-medium">
                                                        {{ $nearestBirthday->first_name ?? '' }}
                                                        {{ $nearestBirthday->middle_name ?? '' }}
                                                        {{ $nearestBirthday->last_name ?? '' }}
                                                        {{ $nearestBirthday->suffix ?? '' }}
                                                    </h6>
                                                    <p class="fs-13">
                                                        {{ $nearestBirthday->employmentDetail?->department?->department_name ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="javascript:void(0);" class="btn btn-mustard btn-xs">
                                                <i class="ti ti-cake me-1"></i>
                                                {{ $nearestBirthday->birth_date ? \Carbon\Carbon::parse($nearestBirthday->birth_date)->format('F, d') : 'N/A' }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="bg-light-mustard p-2 border border-dashed rounded-top mb-3 text-center text-muted w-100" style="width:100%; min-width:100%;">
                                    No Upcoming Birthdays
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
                <!-- /Birthdays -->

                <!-- Attendance Overview -->
                <div class="col-xxl-4 col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap bg-raspberry">
                            <h5 class="mb-2 text-white">Attendance</h5>
                        </div>
                        <div class="card-body">
                            <div class="chartjs-wrapper-demo position-relative mb-4">
                                <canvas id="attendanceOverview" height="200"></canvas>
                                <div class="position-absolute text-center attendance-canvas">
                                    <p class="fs-13 mb-1">Total Attendance</p>
                                    <h3 id="total-attendance-count">0</h3>
                                </div>
                            </div>

                            <h6 class="mb-3">Status</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="f-13 mb-2"><i class="ti ti-circle-filled text-success me-1"></i>Present</p>
                                <p class="f-13 fw-medium text-gray-9 mb-2" id="present-percentage">0%</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="f-13 mb-2"><i class="ti ti-circle-filled text-secondary me-1"></i>Late</p>
                                <p class="f-13 fw-medium text-gray-9 mb-2" id="late-percentage">0%</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="f-13 mb-2"><i class="ti ti-circle-filled text-warning me-1"></i>Official
                                    Business</p>
                                <p class="f-13 fw-medium text-gray-9 mb-2" id="official-business-percentage">0%</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <p class="f-13 mb-2"><i class="ti ti-circle-filled text-danger me-1"></i>Absent</p>
                                <p class="f-13 fw-medium text-gray-9 mb-2" id="absent-percentage">0%</p>
                            </div>


                            {{-- No clock In --}}
                            <div
                                class="bg-light br-5 box-shadow-xs p-2 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center">
                                    <p class="mb-2 me-2">No Clock-in Today</p>
                                    @if (count($noClockInToday) > 0)
                                        <div class="avatar-list-stacked avatar-group-sm mb-2">
                                            @foreach ($noClockInToday as $userNoClockIn)
                                                <span class="avatar avatar-rounded"
                                                    title="{{ $userNoClockIn->personalInformation->full_name ?? 'No Name' }}">
                                                    <img src="{{ asset('storage/' . ($userNoClockIn->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                        alt="img">
                                                </span>
                                            @endforeach
                                            @if (count($noClockInToday) > 5)
                                                <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10"
                                                    href="javascript:void(0);">
                                                    +{{ count($noClockInToday) - 5 }}
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <a href="javascript:void(0);" class="fs-13 link-primary text-decoration-underline mb-2"
                                    data-bs-toggle="modal" data-bs-target="#noClockInModal">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Attendance Overview -->

                <!-- Clock-In/Out -->
                <div class="col-xxl-4 col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap bg-coral">
                            <h5 class="mb-2 text-white">Clock-In/Out</h5>
                        </div>
                        <div class="card-body">
                            <div>
                                @foreach ($presentTodayUsers->take(3) as $present)
                                    <div
                                        class="d-flex align-items-center justify-content-between mb-3 p-2 border border-dashed br-5">
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                                <img src="{{ asset('storage/' . ($present->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                    class="rounded-circle border" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fs-14 fw-medium text-truncate">
                                                    {{ $present->personalInformation->full_name }}</h6>
                                                <p class="fs-13">
                                                    {{ $present->employmentDetail->department->department_name ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="link-default me-2"><i
                                                    class="ti ti-clock-share"></i></a>
                                            <span
                                                class="fs-10 fw-medium d-inline-flex align-items-center badge badge-success">
                                                <i class="ti ti-circle-filled fs-5 me-1"></i>
                                                {{ $present->attendance->first()->time_only ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <h6 class="mb-2">Late</h6>
                            <div
                                class="d-flex align-items-center justify-content-between mb-3 p-2 border border-dashed br-5">
                                @foreach ($lateTodayUsers->take(2) as $late)
                                    <div
                                        class="d-flex align-items-center justify-content-between mb-3 p-2 border border-dashed br-5">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar flex-shrink-0">
                                                <img src="{{ asset('storage/' . ($late->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                    class="rounded-circle border" alt="img">
                                            </span>
                                            <div class="ms-2">
                                                <h6 class="fs-14 fw-medium text-truncate">
                                                    {{ $late->personalInformation->full_name }}
                                                    <span
                                                        class="fs-10 fw-medium d-inline-flex align-items-center badge badge-success">
                                                        <i class="ti ti-clock-hour-11 me-1"></i>
                                                        {{ $late->attendance->first()->total_late_formatted ?? '-' }}
                                                    </span>
                                                </h6>
                                                <p class="fs-13">
                                                    {{ $late->employmentDetail->department->department_name ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="link-default me-2">
                                                <i class="ti ti-clock-share"></i>
                                            </a>
                                            <span
                                                class="fs-10 fw-medium d-inline-flex align-items-center badge badge-danger">
                                                <i class="ti ti-circle-filled fs-5 me-1"></i>
                                                {{ $late->attendance->first()->time_only ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('attendance-admin') }}" class="btn btn-coral btn-md w-100">View All
                                Attendance</a>
                        </div>
                    </div>
                </div>
                <!-- /Clock-In/Out -->

            </div>

            <div class="row">
                <!-- Payroll Overview -->
                <div class="col-xl-7 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                            <h5 class="mb-2">Payroll Overview</h5>
                        </div>
                        <div class="card-body pb-0">

                            <canvas id="payroll-overview"></canvas>
                        </div>
                    </div>
                </div>
                <!-- /Payroll Overview -->

                <!-- Overtime / Holiday -->
                <div class="col-xl-5 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                            <h5 class="mb-2">Overtime</h5>
                        </div>
                        <div class="card-body pb-0">
                            <canvas id="overtime-pay-chart"></canvas>
                        </div>
                    </div>
                </div>
                <!-- /Overtime / Holiday -->

            </div>

            {{-- <div class="row">
                <!-- Projects -->
                <div class="col-xxl-8 col-xl-7 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                            <h5 class="mb-2">Projects</h5>
                            <div class="d-flex align-items-center">
                                <div class="dropdown mb-2">
                                    <a href="javascript:void(0);"
                                        class="btn btn-white border btn-sm d-inline-flex align-items-center"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-calendar me-1"></i>This Week
                                    </a>
                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">This Month</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">This Week</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Team</th>
                                            <th>Hours</th>
                                            <th>Deadline</th>
                                            <th>Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><a href="{{ url('project-details') }}" class="link-default">PRO-001</a>
                                            </td>
                                            <td>
                                                <h6 class="fw-medium"><a href="{{ url('project-details') }}">Office
                                                        Management App</a></h6>
                                            </td>
                                            <td>
                                                <div class="avatar-list-stacked avatar-group-sm">
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-02.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-03.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-05.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1">15/255 Hrs</p>
                                                <div class="progress progress-xs w-100" role="progressbar"
                                                    aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-primary" style="width: 40%"></div>
                                                </div>
                                            </td>
                                            <td>12 Sep 2024</td>
                                            <td>
                                                <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>High
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="{{ url('project-details') }}" class="link-default">PRO-002</a>
                                            </td>
                                            <td>
                                                <h6 class="fw-medium"><a href="{{ url('project-details') }}">Clinic
                                                        Management </a></h6>
                                            </td>
                                            <td>
                                                <div class="avatar-list-stacked avatar-group-sm">
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-06.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-07.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-08.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10 fw-medium"
                                                        href="javascript:void(0);">
                                                        +1
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1">15/255 Hrs</p>
                                                <div class="progress progress-xs w-100" role="progressbar"
                                                    aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-primary" style="width: 40%"></div>
                                                </div>
                                            </td>
                                            <td>24 Oct 2024</td>
                                            <td>
                                                <span
                                                    class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>Low
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="{{ url('project-details') }}" class="link-default">PRO-003</a>
                                            </td>
                                            <td>
                                                <h6 class="fw-medium"><a href="{{ url('project-details') }}">Educational
                                                        Platform</a></h6>
                                            </td>
                                            <td>
                                                <div class="avatar-list-stacked avatar-group-sm">
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-06.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-08.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-09.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1">40/255 Hrs</p>
                                                <div class="progress progress-xs w-100" role="progressbar"
                                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-primary" style="width: 50%"></div>
                                                </div>
                                            </td>
                                            <td>18 Feb 2024</td>
                                            <td>
                                                <span class="badge badge-pink d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>Medium
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="{{ url('project-details') }}" class="link-default">PRO-004</a>
                                            </td>
                                            <td>
                                                <h6 class="fw-medium"><a href="{{ url('project-details') }}">Chat & Call
                                                        Mobile App</a></h6>
                                            </td>
                                            <td>
                                                <div class="avatar-list-stacked avatar-group-sm">
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-11.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-12.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-13.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1">35/155 Hrs</p>
                                                <div class="progress progress-xs w-100" role="progressbar"
                                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-primary" style="width: 50%"></div>
                                                </div>
                                            </td>
                                            <td>19 Feb 2024</td>
                                            <td>
                                                <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>High
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="{{ url('project-details') }}" class="link-default">PRO-005</a>
                                            </td>
                                            <td>
                                                <h6 class="fw-medium"><a href="{{ url('project-details') }}">Travel
                                                        Planning Website</a></h6>
                                            </td>
                                            <td>
                                                <div class="avatar-list-stacked avatar-group-sm">
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-17.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-18.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-19.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1">50/235 Hrs</p>
                                                <div class="progress progress-xs w-100" role="progressbar"
                                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-primary" style="width: 50%"></div>
                                                </div>
                                            </td>
                                            <td>18 Feb 2024</td>
                                            <td>
                                                <span class="badge badge-pink d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>Medium
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><a href="{{ url('project-details') }}" class="link-default">PRO-006</a>
                                            </td>
                                            <td>
                                                <h6 class="fw-medium"><a href="{{ url('project-details') }}">Service
                                                        Booking Software</a></h6>
                                            </td>
                                            <td>
                                                <div class="avatar-list-stacked avatar-group-sm">
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-06.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-08.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-09.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="mb-1">40/255 Hrs</p>
                                                <div class="progress progress-xs w-100" role="progressbar"
                                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-primary" style="width: 50%"></div>
                                                </div>
                                            </td>
                                            <td>20 Feb 2024</td>
                                            <td>
                                                <span
                                                    class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>Low
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-0"><a href="{{ url('project-details') }}"
                                                    class="link-default">PRO-008</a></td>
                                            <td class="border-0">
                                                <h6 class="fw-medium"><a href="{{ url('project-details') }}">Travel
                                                        Planning Website</a></h6>
                                            </td>
                                            <td class="border-0">
                                                <div class="avatar-list-stacked avatar-group-sm">
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-15.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-16.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <span class="avatar avatar-rounded">
                                                        <img class="border border-white"
                                                            src="{{ URL::asset('build/img/profiles/avatar-17.jpg') }}"
                                                            alt="img">
                                                    </span>
                                                    <a class="avatar bg-primary avatar-rounded text-fixed-white fs-10 fw-medium"
                                                        href="javascript:void(0);">
                                                        +2
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="border-0">
                                                <p class="mb-1">15/255 Hrs</p>
                                                <div class="progress progress-xs w-100" role="progressbar"
                                                    aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-primary" style="width: 45%"></div>
                                                </div>
                                            </td>
                                            <td class="border-0">17 Oct 2024</td>
                                            <td class="border-0">
                                                <span class="badge badge-pink d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>Medium
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Projects -->

                <!-- Tasks Statistics -->
                <div class="col-xxl-4 col-xl-5 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-2 d-flex align-items-center justify-content-between flex-wrap">
                            <h5 class="mb-2">Tasks Statistics</h5>
                            <div class="d-flex align-items-center">
                                <div class="dropdown mb-2">
                                    <a href="javascript:void(0);"
                                        class="btn btn-white border btn-sm d-inline-flex align-items-center"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-calendar me-1"></i>This Week
                                    </a>
                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">This Month</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">This Week</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chartjs-wrapper-demo position-relative mb-4">
                                <canvas id="mySemiDonutChart" height="190"></canvas>
                                <div class="position-absolute text-center attendance-canvas">
                                    <p class="fs-13 mb-1">Total Tasks</p>
                                    <h3>124/165</h3>
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-wrap">
                                <div class="border-end text-center me-2 pe-2 mb-3">
                                    <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                            class="ti ti-circle-filled fs-10 me-1 text-warning"></i>Ongoing</p>
                                    <h5>24%</h5>
                                </div>
                                <div class="border-end text-center me-2 pe-2 mb-3">
                                    <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                            class="ti ti-circle-filled fs-10 me-1 text-info"></i>On Hold </p>
                                    <h5>10%</h5>
                                </div>
                                <div class="border-end text-center me-2 pe-2 mb-3">
                                    <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                            class="ti ti-circle-filled fs-10 me-1 text-danger"></i>Overdue</p>
                                    <h5>16%</h5>
                                </div>
                                <div class="text-center me-2 pe-2 mb-3">
                                    <p class="fs-13 d-inline-flex align-items-center mb-1"><i
                                            class="ti ti-circle-filled fs-10 me-1 text-success"></i>Ongoing</p>
                                    <h5>40%</h5>
                                </div>
                            </div>
                            <div class="bg-dark br-5 p-3 pb-0 d-flex align-items-center justify-content-between">
                                <div class="mb-2">
                                    <h4 class="text-success">389/689 hrs</h4>
                                    <p class="fs-13 mb-0">Spent on Overall Tasks This Week</p>
                                </div>
                                <a href="{{ url('tasks') }}" class="btn btn-sm btn-light mb-2 text-nowrap">View
                                    All</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Tasks Statistics -->

            </div> --}}
        </div>

        {{-- No Clock In Users Modal --}}
        <div class="modal fade" id="noClockInModal" tabindex="-1" aria-labelledby="noClockInModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title fw-bold" id="noClockInModalLabel">
                            <i class="ti ti-clock-off me-2 text-danger"></i>No Clock-In Users Today
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        @if (count($noClockInToday) > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($noClockInToday as $userNoClockIn)
                                    @php
                                        $today = \Carbon\Carbon::today()->toDateString();
                                        $weekday = strtolower(\Carbon\Carbon::today()->format('D'));
                                        $shiftsToday = $userNoClockIn->shiftAssignment->filter(function (
                                            $assignment,
                                        ) use ($today, $weekday) {
                                            if ($assignment->is_rest_day) {
                                                return false;
                                            }
                                            if ($assignment->type === 'recurring') {
                                                return \Carbon\Carbon::parse($assignment->start_date)->lte($today) &&
                                                    (!$assignment->end_date ||
                                                        \Carbon\Carbon::parse($assignment->end_date)->gte($today)) &&
                                                    in_array($weekday, $assignment->days_of_week) &&
                                                    (!$assignment->excluded_dates ||
                                                        !in_array($today, $assignment->excluded_dates));
                                            }
                                            if ($assignment->type === 'custom') {
                                                return in_array($today, $assignment->custom_dates ?? []) &&
                                                    (!$assignment->excluded_dates ||
                                                        !in_array($today, $assignment->excluded_dates));
                                            }
                                            return false;
                                        });
                                    @endphp
                                    <div
                                        class="list-group-item py-3 px-2 d-flex align-items-center justify-content-between border-0 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-rounded me-3 shadow-sm"
                                                style="width:48px;height:48px;">
                                                <img src="{{ asset('storage/' . ($userNoClockIn->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                    alt="img" class="img-fluid rounded-circle">
                                            </span>
                                            <div>
                                                <div class="fw-semibold fs-15 mb-1">
                                                    {{ $userNoClockIn->personalInformation->full_name ?? 'No Name' }}</div>
                                                <div class="text-muted fs-13">
                                                    <i class="ti ti-building me-1"></i>
                                                    {{ $userNoClockIn->employmentDetail->department->department_name ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                        @if ($shiftsToday->isNotEmpty())
                                            <div class="text-end ms-3">
                                                @foreach ($shiftsToday as $shiftAssign)
                                                    @php $shift = $shiftAssign->shift; @endphp
                                                    <div class="fs-13 text-muted mb-1">
                                                        <span
                                                            class="badge bg-primary-subtle text-primary fw-normal px-2 py-1">
                                                            <i class="ti ti-clock me-1"></i>
                                                            {{ $shift->name ?? 'Unnamed Shift' }}
                                                        </span>
                                                        <br>
                                                        <span class="text-dark">
                                                            <i class="ti ti-clock-hour-3 me-1"></i>
                                                            {{ $shift->start_time ?? 'N/A' }} -
                                                            {{ $shift->end_time ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-end ms-3">
                                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">No Shift
                                                    Assigned</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="ti ti-checklist fs-32 mb-2 text-success"></i>
                                <div>No users without clock-in today.</div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Close
                        </button>
                    </div>
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
        if (document.getElementById('attendanceOverview')) {
            fetch('/admin-dashboard/attendance-overview')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('attendanceOverview').getContext('2d');
                    const total = data.totalAttendance || 0;

                    // Chart Data
                    const chartData = {
                        labels: ['Late', 'Present', 'Official Business', 'Absent'],
                        datasets: [{
                            label: 'Attendance',
                            data: [data.late, data.present, data.official_business, data.absent],
                            backgroundColor: ['#0C4B5E', '#03C95A', '#FFC107', '#E70D0D'],
                            borderWidth: 5,
                            borderRadius: 10,
                            borderColor: '#fff',
                            cutout: '60%',
                        }]
                    };

                    const chartOptions = {
                        rotation: -100,
                        circumference: 200,
                        layout: {
                            padding: {
                                top: -20,
                                bottom: -20
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        return `${label}: ${value}`;
                                    }
                                }
                            }
                        },
                    };

                    new Chart(ctx, {
                        type: 'doughnut',
                        data: chartData,
                        options: chartOptions
                    });

                    // Update center count (show blank if no attendance)
                    document.getElementById('total-attendance-count').innerText = total > 0 ? total : '';

                    // Update percentages (show 0% if no attendance)
                    document.getElementById('present-percentage').innerText =
                        total > 0 ? `${((data.present / total) * 100).toFixed(0)}%` : '0%';
                    document.getElementById('late-percentage').innerText =
                        total > 0 ? `${((data.late / total) * 100).toFixed(0)}%` : '0%';
                    document.getElementById('official-business-percentage').innerText =
                        total > 0 ? `${((data.official_business / total) * 100).toFixed(0)}%` : '0%';
                    document.getElementById('absent-percentage').innerText =
                        total > 0 ? `${((data.absent / total) * 100).toFixed(0)}%` : '0%';
                })
                .catch(error => {
                    console.error('Failed to load attendance summary:', error);
                });
        }
    </script>

    {{-- Payroll Overview Chart --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/admin-dashboard/payroll-overview')
                .then(response => response.json())
                .then(data => {
                    const monthlyNetPay = data.monthlyNetPay;
                    const months = [
                        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                    ];

                    const ctx = document.getElementById('payroll-overview').getContext('2d');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: months,
                                datasets: [{
                                    label: 'Salary (PHP)',
                                    data: monthlyNetPay,
                                    fill: false,
                                    borderColor: '#12515D',
                                    backgroundColor: '#12515D',
                                    tension: 0.3,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#12515D',
                                    pointHoverRadius: 6,
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `₱${context.parsed.y.toLocaleString()}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: '#eee'
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return value.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Error fetching payroll data:', error));
        });
    </script>

    {{-- Payroll Overtime and Holiday Pay --}}
    <script>
        // Fetch Overtime Pay Data from the backend
        fetch('/admin-dashboard/overtime-overview')
            .then(response => response.json())
            .then(data => {
                const months = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                const overtimePay = data.monthlyOvertimePay;

                const ctx = document.getElementById('overtime-pay-chart').getContext('2d');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Overtime Pay',
                            data: overtimePay,
                            borderColor: '#12515D',
                            backgroundColor: '#12515D',
                            fill: true,
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#12515D',
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `₱${context.parsed.y.toLocaleString()}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#eee'
                                },

                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error fetching payroll data:', error));
    </script>
@endpush
