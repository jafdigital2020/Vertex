<?php $page = 'employee-dashboard'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Dashboard</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Dashboard
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Dashboard</li>
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
                            </ul>
                        </div>
                    </div>
                    <div class="input-icon w-120 position-relative mb-2">
                        <span class="input-icon-addon">
                            <i class="ti ti-calendar text-gray-9"></i>
                        </span>
                        <input type="text" class="form-control datetimepicker"
                            value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}">
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

            {{-- <div class="alert bg-secondary-transparent alert-dismissible fade show mb-4">
                Your Leave Request on“24th April 2024”has been Approved!!!
                <button type="button" class="btn-close fs-14" data-bs-dismiss="alert" aria-label="Close"><i
                        class="ti ti-x"></i></button>
            </div> --}}
            <div class="row">
                <div class="col-xl-4 d-flex">
                    <div class="card position-relative flex-fill">
                        <div class="card-header bg-dark">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg avatar-rounded border border-white  flex-shrink-0 me-2">
                                    <img src="{{ URL::asset('build/img/users/user-01.jpg') }}" alt="Img">
                                </span>
                                <div>
                                    @if (Auth::check() && optional(Auth::user()->personalInformation)->full_name)
                                        <h5 class="text-white mb-1">{{ Auth::user()->personalInformation->full_name }}</h5>
                                    @else
                                        <h5 class="text-white mb-1">No Name Available</h5>
                                    @endif
                                    <div class="d-flex align-items-center">
                                        <p class="text-white fs-12 mb-0">
                                            {{ Auth::user()->employmentDetail->designation->designation_name ?? 'No Designation' }}
                                        </p>
                                        <span class="mx-1"><i class="ti ti-point-filled text-primary"></i></span>
                                        <p class="fs-12">
                                            {{ Auth::user()->employmentDetail->department->department_name ?? 'No Department' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="btn btn-icon btn-sm text-white rounded-circle edit-top"><i
                                    class="ti ti-edit"></i></a>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="d-block mb-1 fs-13">Phone Number</span>
                                <p class="text-gray-9">
                                    {{ Auth::user()->personalInformation->phone_number ?? 'No Phone Number' }}</p>
                            </div>
                            <div class="mb-3">
                                <span class="d-block mb-1 fs-13">Email Address</span>
                                <p class="text-gray-9">{{ Auth::user()->email ?? 'No Email Address' }}</p>
                            </div>
                            <div class="mb-3">
                                <span class="d-block mb-1 fs-13">Reporting To</span>
                                <p class="text-gray-9">
                                    {{ Auth::user()->employmentDetail->department->head->personalInformation->full_name ?? 'No Reporting To' }}
                                </p>
                            </div>
                            <div>
                                <span class="d-block mb-1 fs-13">Joined on</span>
                                <p class="text-gray-9">
                                    {{ optional(Auth::user()->employmentDetail)->date_hired ? \Carbon\Carbon::parse(Auth::user()->employmentDetail->date_hired)->format('F d, Y') : 'No Date' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                                <h5>Leave Details</h5>
                                <div class="dropdown">
                                    <a href="javascript:void(0);"
                                        class="btn btn-white border btn-sm d-inline-flex align-items-center"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-calendar me-1"></i>{{ now()->year }}
                                    </a>
                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">2024</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">2023</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">2022</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Total Leaves</span>
                                        <h4>16</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Taken</span>
                                        <h4>10</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Absent</span>
                                        <h4>2</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Request</span>
                                        <h4>0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Worked Days</span>
                                        <h4>240</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Loss of Pay</span>
                                        <h4>2</h4>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div>
                                        <a href="#" class="btn btn-dark w-100" data-bs-toggle="modal"
                                            data-bs-target="#add_leaves">Apply New Leave</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 d-flex">
                    <div class="flex-fill">
                        <div class="card card-bg-5 bg-dark mb-3">
                            <div class="card-body">
                                <div class="text-center">
                                    <h5 class="text-white mb-4">Team Birthday</h5>

                                    @foreach ($branchBirthdayEmployees as $employee)
                                        <span class="avatar avatar-xl avatar-rounded mb-2">
                                            <img src="{{ $employee['profile_picture'] }}"
                                                alt="{{ $employee['full_name'] }}">
                                        </span>
                                        <div class="mb-3">
                                            <h6 class="text-white fw-medium mb-1">{{ $employee['full_name'] }}</h6>
                                            <p>{{ $employee['designation'] }}</p>
                                        </div>
                                    @endforeach

                                    @if ($branchBirthdayEmployees->isNotEmpty())
                                        <a href="#" class="btn btn-sm btn-primary">Send Wishes</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- <div class="card bg-secondary mb-3">
                            <div class="card-body d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <h5 class="text-white mb-1">Leave Policy</h5>
                                    <p class="text-white">Last Updated : Today</p>
                                </div>
                                <a href="#" class="btn btn-white btn-sm px-3">View All</a>
                            </div>
                        </div> --}}
                        <div class="card bg-warning">
                            <div class="card-body d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <h5 class="mb-1">Next Holiday</h5>
                                    @if ($upcomingHoliday)
                                        <p class="text-gray-9">
                                            {{ $upcomingHoliday->name }},
                                            @if ($upcomingHoliday->date)
                                                {{ \Carbon\Carbon::parse($upcomingHoliday->date)->format('d M Y') }}
                                            @elseif ($upcomingHoliday->month_day)
                                                {{ \Carbon\Carbon::createFromFormat('m-d', $upcomingHoliday->month_day)->setYear(now()->year)->format('d M Y') }}
                                            @endif
                                        </p>
                                    @else
                                        <p class="text-gray-9">No upcoming holidays.</p>
                                    @endif
                                </div>
                                <a href="{{ url('holidays') }}" class="btn btn-white btn-sm px-3">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <h5>Team Members</h5>
                                <div>
                                    <a href="#" class="btn btn-light btn-sm">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{ URL::asset('build/img/users/user-27.jpg') }}"
                                            class="rounded-circle border" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Alexander
                                                Jermai</a></h6>
                                        <p class="fs-13">UI/UX Designer</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-phone fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-mail-bolt fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm"><i
                                            class="ti ti-brand-hipchat fs-16"></i></a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{ URL::asset('build/img/users/user-42.jpg') }}"
                                            class="rounded-circle border" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Doglas
                                                Martini</a></h6>
                                        <p class="fs-13">Product Designer</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-phone fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-mail-bolt fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm"><i
                                            class="ti ti-brand-hipchat fs-16"></i></a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{ URL::asset('build/img/users/user-43.jpg') }}"
                                            class="rounded-circle border" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Daniel
                                                Esbella</a></h6>
                                        <p class="fs-13">Project Manager</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-phone fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-mail-bolt fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm"><i
                                            class="ti ti-brand-hipchat fs-16"></i></a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{ URL::asset('build/img/users/user-11.jpg') }}"
                                            class="rounded-circle border" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Daniel
                                                Esbella</a></h6>
                                        <p class="fs-13">Team Lead</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-phone fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-mail-bolt fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm"><i
                                            class="ti ti-brand-hipchat fs-16"></i></a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{ URL::asset('build/img/users/user-44.jpg') }}"
                                            class="rounded-circle border" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Stephan
                                                Peralt</a></h6>
                                        <p class="fs-13">Team Lead</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-phone fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-mail-bolt fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm"><i
                                            class="ti ti-brand-hipchat fs-16"></i></a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                        <img src="{{ URL::asset('build/img/users/user-54.jpg') }}"
                                            class="rounded-circle border" alt="img">
                                    </a>
                                    <div class="ms-2">
                                        <h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Andrew Jermia</a>
                                        </h6>
                                        <p class="fs-13">Project Lead</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-phone fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm me-2"><i
                                            class="ti ti-mail-bolt fs-16"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm"><i
                                            class="ti ti-brand-hipchat fs-16"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <h5>Notifications</h5>
                                <div>
                                    <a href="#" class="btn btn-light btn-sm">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-4">
                                <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                    <img src="{{ URL::asset('build/img/users/user-27.jpg') }}"
                                        class="rounded-circle border" alt="img">
                                </a>
                                <div class="ms-2">
                                    <h6 class="fs-14 fw-medium text-truncate mb-1">Lex Murphy requested access to UNIX
                                    </h6>
                                    <p class="fs-13 mb-2">Today at 9:42 AM</p>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-sm border flex-shrink-0 me-2"><img
                                                src="{{ URL::asset('build/img/social/pdf-icon.svg') }}"
                                                class="w-auto h-auto" alt="Img"></a>
                                        <h6 class="fw-normal"><a href="#">EY_review.pdf</a></h6>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-4">
                                <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                    <img src="{{ URL::asset('build/img/users/user-28.jpg') }}"
                                        class="rounded-circle border" alt="img">
                                </a>
                                <div class="ms-2">
                                    <h6 class="fs-14 fw-medium text-truncate mb-1">Lex Murphy requested access to UNIX
                                    </h6>
                                    <p class="fs-13 mb-0">Today at 10:00 AM</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-4">
                                <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                    <img src="{{ URL::asset('build/img/users/user-29.jpg') }}"
                                        class="rounded-circle border" alt="img">
                                </a>
                                <div class="ms-2">
                                    <h6 class="fs-14 fw-medium text-truncate mb-1">Lex Murphy requested access to UNIX
                                    </h6>
                                    <p class="fs-13 mb-2">Today at 10:50 AM</p>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="btn btn-primary btn-sm me-2">Approve</a>
                                        <a href="#" class="btn btn-outline-primary btn-sm">Decline</a>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-4">
                                <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                    <img src="{{ URL::asset('build/img/users/user-30.jpg') }}"
                                        class="rounded-circle border" alt="img">
                                </a>
                                <div class="ms-2">
                                    <h6 class="fs-14 fw-medium text-truncate mb-1">Lex Murphy requested access to UNIX
                                    </h6>
                                    <p class="fs-13 mb-0">Today at 12:00 PM</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                    <img src="{{ URL::asset('build/img/users/user-33.jpg') }}"
                                        class="rounded-circle border" alt="img">
                                </a>
                                <div class="ms-2">
                                    <h6 class="fs-14 fw-medium text-truncate mb-1">Lex Murphy requested access to UNIX
                                    </h6>
                                    <p class="fs-13 mb-0">Today at 05:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                                <h5>Meetings Schedule</h5>
                                <div class="dropdown">
                                    <a href="javascript:void(0);"
                                        class="btn btn-white border btn-sm d-inline-flex align-items-center"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-calendar me-1"></i>Today
                                    </a>
                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">This Month</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item rounded-1">This Year</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body schedule-timeline">
                            <div class="d-flex align-items-start">
                                <div class="d-flex align-items-center active-time">
                                    <span>09:25 AM</span>
                                    <span><i class="ti ti-point-filled text-primary fs-20"></i></span>
                                </div>
                                <div class="flex-fill ps-3 pb-4 timeline-flow">
                                    <div class="bg-light p-2 rounded">
                                        <p class="fw-medium text-gray-9 mb-1">Marketing Strategy Presentation</p>
                                        <span>Marketing</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="d-flex align-items-center active-time">
                                    <span>09:20 AM</span>
                                    <span><i class="ti ti-point-filled text-secondary fs-20"></i></span>
                                </div>
                                <div class="flex-fill ps-3 pb-4 timeline-flow">
                                    <div class="bg-light p-2 rounded">
                                        <p class="fw-medium text-gray-9 mb-1">Design Review Hospital, doctors Management
                                            Project</p>
                                        <span>Review</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="d-flex align-items-center active-time">
                                    <span>09:18 AM</span>
                                    <span><i class="ti ti-point-filled text-warning fs-20"></i></span>
                                </div>
                                <div class="flex-fill ps-3 pb-4 timeline-flow">
                                    <div class="bg-light p-2 rounded">
                                        <p class="fw-medium text-gray-9 mb-1">Birthday Celebration of Employee</p>
                                        <span>Celebration</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="d-flex align-items-center active-time">
                                    <span>09:10 AM</span>
                                    <span><i class="ti ti-point-filled text-success fs-20"></i></span>
                                </div>
                                <div class="flex-fill ps-3 timeline-flow">
                                    <div class="bg-light p-2 rounded">
                                        <p class="fw-medium text-gray-9 mb-1">Update of Project Flow</p>
                                        <span>Development</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; SmartHR.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection
