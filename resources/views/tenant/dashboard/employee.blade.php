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
                                    {{ optional(optional(Auth::user())->employmentDetail)?->date_hired ? \Carbon\Carbon::parse(optional(Auth::user()->employmentDetail)->date_hired)->format('F d, Y') : 'No Date' }}
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
                                        <h4>0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Taken</span>
                                        <h4>0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Absent</span>
                                        <h4>0</h4>
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
                                        <h4>0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Loss of Pay</span>
                                        <h4>0</h4>
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
        </div>

        {{-- Footer Company --}}
       @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection
