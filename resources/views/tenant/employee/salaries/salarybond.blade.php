<?php $page = 'employees'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Salary Bond</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"> {{ $user->personalInformation->first_name }}'s
                                Salary Bond

                            </li>
                            <input type="hidden" id="userID" value="{{ $user->id }}">
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
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_salary"
                                data-user-id="{{ $user->id }}"
                                class="btn btn-primary d-flex align-items-center addSalaryRecord">
                                <i class="ti ti-circle-plus me-2"></i>Add Salary Bon
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

            <div class="payroll-btns mb-3">
                <a href="{{ route('salaryRecord', $user->id) }}" class="btn btn-white  border me-2">Salary Record</a>
                <a href="{{ route('salaryBond', $user->id) }}" class="btn btn-white active  border me-2">Salary Bond</a>
                <a href="{{ route('adminRequestAttendance') }}" class="btn btn-white border me-2">Employee Allowances</a>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Salary Bond</h5>

                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter" onchange="filter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="salaryType_filter" id="salaryType_filter" class="select2 form-select"
                                onchange="filter()">
                                <option value="" selected>All Salary Types</option>
                                <option value="monthly_fixed">Monthly Fixed</option>
                                <option value="daily_rate">Daily Rate</option>
                                <option value="hourly_rate">Hourly Rate</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
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
                                    <th class="text-center">Basic Salary</th>
                                    <th class="text-center">Salary Type</th>
                                    <th class="text-center">Effective Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Encoded By</th>
                                    <th class="text-center">Remarks</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        @include('layout.partials.footer-company')

    </div>
    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script src="{{ asset('build/js/employeedetails/salary/salary.js') }}"></script>
@endpush
