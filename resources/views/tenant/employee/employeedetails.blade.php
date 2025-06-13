<?php $page = 'employee-details'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0"><a href="{{ url('employees') }}">
                            <i class="ti ti-arrow-left me-2"></i>Employee Details</a>
                    </h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_salary"
                            data-user-id="{{ $users->id }}"
                            class="btn btn-primary d-flex align-items-center addSalaryRecord"><i
                                class="ti ti-circle-plus me-2"></i>Add Salary</a>
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
                <div class="col-xl-4 theiaStickySidebar">
                    <div class="card card-bg-1">
                        <div class="card-body p-0">
                            <span class="avatar avatar-xl avatar-rounded border border-2 border-white m-auto d-flex mb-2">
                                <img src="{{ asset('storage/' . ($users->personalInformation?->profile_picture ?? 'default.png')) }}"
                                    class="w-auto h-auto" alt="Img">
                            </span>
                            <div class="text-center px-3 pb-3 border-bottom">
                                <div class="mb-3">
                                    <h5 class="d-flex align-items-center justify-content-center mb-1">
                                        {{ $users->personalInformation->last_name ?? 'N/A' }}
                                        {{ $users->personalInformation->suffix }},
                                        {{ $users->personalInformation->first_name ?? 'N/A' }}
                                        {{ $users->personalInformation->middle_name }}
                                        @if ($users->employmentDetail->status == 1)
                                            <i class="ti ti-discount-check-filled text-success ms-1"></i>
                                        @else
                                            <i class="ti ti-xbox-x-filled text-danger ms-1"></i>
                                        @endif
                                    </h5>
                                    <span class="badge badge-soft-dark fw-medium me-2">
                                        <i
                                            class="ti ti-point-filled me-1"></i>{{ $users->employmentDetail->designation->designation_name ?? 'N/A' }}
                                    </span>

                                </div>
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-id me-2"></i>
                                            Employee ID
                                        </span>
                                        <p class="text-dark">{{ $users->employmentDetail->employee_id }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-star me-2"></i>
                                            Team
                                        </span>
                                        <p class="text-dark">
                                            {{ $users->employmentDetail->department->department_name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-calendar-check me-2"></i>
                                            Date Of Join
                                        </span>
                                        <p class="text-dark">{{ $users->employmentDetail->date_hired ?? 'N/A' }}</p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-building me-2"></i>
                                            Branch
                                        </span>
                                        <p class="text-dark">{{ $users->branch->name ?? 'N/A' }}</p>
                                    </div> 
                                    <div class="row gx-2 mt-3">
                                        <div class="col-12">
                                            <div>
                                                <a href="#" class="btn btn-dark w-100" data-bs-toggle="modal"
                                                    data-bs-target="#edit_viewemployee" data-user-id="{{ $users->id }}"
                                                    data-first-name="{{ $users->personalInformation->first_name ?? '' }}"
                                                    data-last-name="{{ $users->personalInformation->last_name ?? '' }}"
                                                    data-middle-name="{{ $users->personalInformation->middle_name ?? '' }}"
                                                    data-suffix="{{ $users->personalInformation->suffix ?? '' }}"
                                                    data-profile-picture="{{ $users->personalinformation->profile_picture ?? '' }}"
                                                    data-username="{{ $users->username ?? '' }}"
                                                    data-email="{{ $users->email }}"
                                                    data-role-id="{{ $users->userPermission->role_id ?? '' }}"
                                                    data-department-id="{{ $users->employmentDetail->department_id ?? '' }}"
                                                    data-designation-id="{{ $users->employmentDetail->designation_id ?? '' }}"
                                                    data-branch-id="{{ $users->employmentDetail->branch_id ?? '' }}"
                                                    data-date-hired="{{ $users->employmentDetail->date_hired ?? '' }}"
                                                    data-employee-id="{{ $users->employmentDetail->employee_id ?? '' }}"
                                                    data-employment-type="{{ $users->employmentDetail->employment_type ?? '' }}"
                                                    data-employment-status="{{ $users->employmentDetail->employment_status ?? '' }}"
                                                    data-reporting-to="{{ $users->employmentDetail->reporting_to ?? '' }}"><i
                                                        class="ti ti-edit me-1"></i>Edit
                                                    Info</a>
                                            </div>
                                        </div>
                                        <!-- Coming Soon -->
                                        {{-- <div class="col-6">
                                            <div>
                                                <a href="{{ url('chat') }}" class="btn btn-primary w-100"><i
                                                        class="ti ti-message-heart me-1"></i>Message</a>
                                            </div>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6>Basic information</h6>
                                    <a href="javascript:void(0);" class="btn btn-icon btn-sm ediBasicInformation"
                                        data-bs-toggle="modal" data-bs-target="#edit_basic"
                                        data-user-id="{{ $users->id }}"
                                        data-phone-number="{{ $users->personalInformation->phone_number ?? '' }}"
                                        data-gender="{{ $users->personalInformation->gender ?? '' }}"
                                        data-birthdate="{{ $users->personalInformation->birth_date ?? '' }}"
                                        data-birthplace="{{ $users->personalInformation->birth_place ?? '' }}"
                                        data-complete-address="{{ $users->personalInformation->complete_address ?? '' }}"><i
                                            class="ti ti-edit"></i></a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-phone me-2"></i>
                                        Phone
                                    </span>
                                    <p class="text-dark">{{ $users->personalInformation->phone_number ?? 'N/A' }}</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-mail-check me-2"></i>
                                        Email
                                    </span>
                                    <a href="javascript:void(0);" class="text-info d-inline-flex align-items-center"
                                        onclick="copyToClipboard('{{ $users->email }}')">{{ $users->email }}<i
                                            class="ti ti-copy text-dark ms-2"></i></a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-gender-male me-2"></i>
                                        Gender
                                    </span>
                                    <p class="text-dark text-end">{{ $users->personalInformation->gender ?? 'N/A' }}</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-cake me-2"></i>
                                        Birdthday
                                    </span>
                                    <p class="text-dark text-end">{{ $users->personalInformation->birth_date ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-building me-2"></i>
                                        Birthplace
                                    </span>
                                    <p class="text-dark text-end">{{ $users->personalInformation->birth_place ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-map-pin-check me-2"></i>
                                        Address
                                    </span>
                                    <p class="text-dark text-end">
                                        {{ $users->personalInformation->complete_address ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6>Personal Information</h6>
                                    <a href="javascript:void(0);" class="btn btn-icon btn-sm btn editPersonalInformation"
                                        data-bs-toggle="modal" data-bs-target="#edit_personal"
                                        data-user-id="{{ $users->id }}"
                                        data-nationality="{{ $users->personalInformation->nationality ?? '' }}"
                                        data-religion="{{ $users->personalInformation->religion ?? '' }}"
                                        data-civil-status="{{ $users->personalInformation->civil_status ?? '' }}"
                                        data-no-children="{{ $users->personalInformation->no_of_children ?? '' }}"
                                        data-spouse-name="{{ $users->personalInformation->spouse_name ?? '' }}"><i
                                            class="ti ti-edit"></i></a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-gender-male me-2"></i>
                                        Nationality
                                    </span>
                                    <p class="text-dark text-end">{{ $users->personalInformation->nationality ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-bookmark-plus me-2"></i>
                                        Religion
                                    </span>
                                    <p class="text-dark text-end">{{ $users->personalInformation->religion ?? 'N/A' }}</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-calendar-x me-2"></i>
                                        Civil Status
                                    </span>
                                    <p class="text-dark text-end">{{ $users->personalInformation->civil_status ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-briefcase-2 me-2"></i>
                                        Spouse
                                    </span>
                                    <p class="text-dark text-end">{{ $users->personalInformation->spouse_name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="d-inline-flex align-items-center">
                                        <i class="ti ti-baby-bottle me-2"></i>
                                        No. of children
                                    </span>
                                    <p class="text-dark text-end">
                                        {{ $users->personalInformation->no_of_children ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6>Emergency Contact Number</h6>
                        <a href="javascript:void(0);" class="btn btn-icon btn-sm editEmergencyContact"
                            data-bs-toggle="modal" data-bs-target="#edit_emergency" data-user-id="{{ $users->id }}"
                            data-primary-name="{{ $users->emergency->primary_name ?? '' }}"
                            data-primary-relationship="{{ $users->emergency->primary_relationship ?? '' }}"
                            data-primary-phoneone="{{ $users->emergency->primary_phone_one ?? '' }}"
                            data-primary-phonetwo="{{ $users->emergency->primary_phone_two ?? '' }}"
                            data-secondary-name="{{ $users->emergency->secondary_name ?? '' }}"
                            data-secondary-relationship="{{ $users->emergency->secondary_relationship ?? '' }}"
                            data-secondary-phoneone="{{ $users->emergency->secondary_phone_one ?? '' }}"
                            data-secondary-phonetwo="{{ $users->emergency->secondary_phone_two ?? '' }}"><i
                                class="ti ti-edit"></i></a>
                    </div>
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="d-inline-flex align-items-center">
                                            Primary
                                        </span>
                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                            {{ $users->emergency->primary_name ?? 'N/A' }} <span
                                                class="d-inline-flex mx-1"><i
                                                    class="ti ti-point-filled text-danger"></i></span>{{ $users->emergency->primary_relationship ?? 'N/A' }}
                                        </h6>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-dark mb-1">{{ $users->emergency->primary_phone_one ?? 'N/A' }}</p>
                                        <p class="text-dark mb-0">{{ $users->emergency->primary_phone_two ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="d-inline-flex align-items-center">
                                            Secondary
                                        </span>
                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                            {{ $users->emergency->secondary_name ?? 'N/A' }} <span
                                                class="d-inline-flex mx-1"><i
                                                    class="ti ti-point-filled text-danger"></i></span>{{ $users->emergency->secondary_relationship ?? 'N/A' }}
                                        </h6>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-dark mb-1">{{ $users->emergency->secondary_phone_one ?? 'N/A' }}
                                        </p>
                                        <p class="text-dark mb-0">{{ $users->emergency->secondary_phone_two ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div>
                        <div class="tab-content custom-accordion-items">
                            <div class="tab-pane active show" id="bottom-justified-tab1" role="tabpanel">
                                <div class="accordion accordions-items-seperate" id="accordionExample">

                                    <div class="accordion-item">
                                        <div class="accordion-header" id="headingZero">
                                            <div class="accordion-button">
                                                <div class="d-flex align-items-center flex-fill">
                                                    <h5>Salary and Contribution Computation</h5>
                                                    <a href="{{ url('employees/employee-details/' . $users->id . '/salary-records') }}"
                                                        class="btn btn-sm btn-icon ms-auto"><i class="ti ti-eye"
                                                            title="View Salary Record"></i></a>
                                                    <a href="#" class="btn btn-sm btn-icon editSalaryContribution"
                                                        data-bs-toggle="modal" data-bs-target="#edit_salary"
                                                        data-user-id="{{ $users->id }}"
                                                        data-sss-contribution="{{ $users->salaryDetail->sss_contribution ?? '' }}"
                                                        data-philhealth-contribution="{{ $users->salaryDetail->philhealth_contribution ?? '' }}"
                                                        data-pagibig-contribution="{{ $users->salaryDetail->pagibig_contribution ?? '' }}"
                                                        data-withholding-tax="{{ $users->salaryDetail->withholding_tax ?? '' }}"
                                                        data-worked-days="{{ $users->salaryDetail->worked_days_per_year ?? '' }}"
                                                        data-sss-override="{{ $users->salaryDetail->sss_contribution_override ?? '' }}"
                                                        data-philheath-override="{{ $users->salaryDetail->philhealth_contribution_override ?? '' }}"
                                                        data-pagibig-override="{{ $users->salaryDetail->pagibig_contribution_override ?? '' }}"
                                                        data-withholding-override="{{ $users->salaryDetail->withholding_tax_override ?? '' }}"><i
                                                            class="ti ti-edit"></i></a>
                                                    <a href="#"
                                                        class="d-flex align-items-center collapsed collapse-arrow"
                                                        data-bs-toggle="collapse" data-bs-target="#primaryBorderZero"
                                                        aria-expanded="false" aria-controls="primaryBorderZero">
                                                        <i class="ti ti-chevron-down fs-18"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="primaryBorderZero" class="accordion-collapse collapse border-top"
                                            aria-labelledby="headingZero" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">
                                                            Basic Salary
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->activeSalary->basic_salary ?? 'N/A' }}</h6>
                                                    </div>
                                                    @php
                                                        $salaryType = $users->activeSalary->salary_type ?? null;
                                                    @endphp
                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">
                                                            Salary Type
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ match ($salaryType) {
                                                                'hourly_rate' => 'Hourly Rate',
                                                                'daily_rate' => 'Daily Rate',
                                                                'monthly_fixed' => 'Monthly Fixed',
                                                                default => 'No salary type configured.',
                                                            } }}
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">
                                                            SSS
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            @if ($users->salaryDetail)
                                                                @if ($users->salaryDetail->sss_contribution == 'manual')
                                                                    {{ $users->salaryDetail->sss_contribution_override }}
                                                                @elseif ($users->salaryDetail->sss_contribution == 'system')
                                                                    {{ $users->salaryDetail->sss_contribution }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            @else
                                                                N/A
                                                            @endif
                                                    </div>
                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">
                                                            PhilHealth
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->salaryDetail->philhealth_contribution ?? 'N/A' }}
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">
                                                            HDMF
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->salaryDetail->pagibig_contribution ?? 'N/A' }}</h6>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">
                                                            Withholding
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->salaryDetail->withholding_tax ?? 'N/A' }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <div class="accordion-header" id="headingOne">
                                            <div class="accordion-button">
                                                <div class="d-flex align-items-center flex-fill">
                                                    <h5>Government Details</h5>
                                                    <a href="#"
                                                        class="btn btn-sm btn-icon ms-auto editGovernmentBtn"
                                                        data-bs-toggle="modal" data-bs-target="#edit_government"
                                                        data-user-id="{{ $users->id }}"
                                                        data-sss-number="{{ $users->governmentId->sss_number ?? '' }}"
                                                        data-philhealth-number="{{ $users->governmentId->philhealth_number ?? '' }}"
                                                        data-pagibig-number="{{ $users->governmentId->pagibig_number ?? '' }}"
                                                        data-tin-number="{{ $users->governmentId->tin_number ?? '' }}"><i
                                                            class="ti ti-edit"></i></a>
                                                    <a href="#"
                                                        class="d-flex align-items-center collapsed collapse-arrow"
                                                        data-bs-toggle="collapse" data-bs-target="#primaryBorderOne"
                                                        aria-expanded="false" aria-controls="primaryBorderOne">
                                                        <i class="ti ti-chevron-down fs-18"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="primaryBorderOne" class="accordion-collapse collapse border-top"
                                            aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            SSS Number
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->governmentId->sss_number ?? 'N/A' }}</h6>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            PhilHealth Number
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->governmentId->philhealth_number ?? 'N/A' }}
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            HDMF Number
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->governmentId->pagibig_number ?? 'N/A' }}</h6>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            TIN Number
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->governmentId->tin_number ?? 'N/A' }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <div class="accordion-header" id="headingTwo">
                                            <div class="accordion-button">
                                                <div class="d-flex align-items-center flex-fill">
                                                    <h5>Bank Information</h5>
                                                    <a href="#"
                                                        class="btn btn-sm btn-icon ms-auto editBankDetailsBtn"
                                                        data-bs-toggle="modal" data-bs-target="#edit_bank"
                                                        data-user-id="{{ $users->id }}"
                                                        data-bank-id="{{ $users->employeeBank->bank_id ?? '' }}"
                                                        data-account-name="{{ $users->employeeBank->account_name ?? '' }}"
                                                        data-account-number="{{ $users->employeeBank->account_number ?? '' }}"><i
                                                            class="ti ti-edit"></i></a>
                                                    <a href="#"
                                                        class="d-flex align-items-center collapsed collapse-arrow"
                                                        data-bs-toggle="collapse" data-bs-target="#primaryBorderTwo"
                                                        aria-expanded="false" aria-controls="primaryBorderTwo">
                                                        <i class="ti ti-chevron-down fs-18"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="primaryBorderTwo" class="accordion-collapse collapse border-top"
                                            aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            Bank Name
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->employeeBank->bank->bank_name ?? 'N/A' }}</h6>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            Bank Code
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->employeeBank->bank->bank_code ?? 'N/A' }}
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            Account Name
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->employeeBank->account_name ?? 'N/A' }}</h6>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="d-inline-flex align-items-center">
                                                            Account Number
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            {{ $users->employeeBank->account_number ?? 'N/A' }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <div class="accordion-header" id="headingThree">
                                            <div class="accordion-button">
                                                <div class="d-flex align-items-center justify-content-between flex-fill">
                                                    <h5>Family Information</h5>
                                                    <div class="d-flex">
                                                        <a href="#" class="btn btn-icon btn-sm editFamilyInfoBtn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#edit_familyinformation"
                                                            data-user-id="{{ $users->id }}"><i
                                                                class="ti ti-edit"></i></a>
                                                        <a href="#"
                                                            class="d-flex align-items-center collapsed collapse-arrow"
                                                            data-bs-toggle="collapse" data-bs-target="#primaryBorderThree"
                                                            aria-expanded="false" aria-controls="primaryBorderThree">
                                                            <i class="ti ti-chevron-down fs-18"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="primaryBorderThree" class="accordion-collapse collapse border-top"
                                            aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    @if ($users->family->count())
                                                        <div class="row">
                                                            <div class="col-md-3">Name</div>
                                                            <div class="col-md-3">Relationship</div>
                                                            <div class="col-md-3">Date of Birth</div>
                                                            <div class="col-md-2">Phone</div>
                                                            <div class="col-md-1"></div>
                                                        </div>

                                                        @foreach ($users->family as $family)
                                                            <div class="row mb-2">
                                                                <div class="col-md-3">
                                                                    <h6 class="fw-medium mt-1">{{ $family->name }}</h6>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <h6 class="fw-medium mt-1">
                                                                        {{ $family->relationship }}
                                                                    </h6>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <h6 class="fw-medium mt-1">{{ $family->birthdate }}
                                                                    </h6>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <h6 class="fw-medium mt-1">
                                                                        {{ $family->phone_number }}
                                                                    </h6>
                                                                </div>
                                                                <div class="col-md-1">
                                                                    <div class="action-icon d-inline-flex">
                                                                        <a href="#" class="btn-edit"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#edit_family_information"
                                                                            data-id="{{ $family->id }}"
                                                                            data-user-id="{{ $family->user_id }}"
                                                                            data-name="{{ $family->name }}"
                                                                            data-relationship="{{ $family->relationship }}"
                                                                            data-phone-number="{{ $family->phone_number }}"
                                                                            data-birthdate="{{ $family->birthdate }}">
                                                                            <i class="ti ti-edit" title="Edit"></i>
                                                                        </a>
                                                                        <a href="#" class="btn-delete"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#delete_family_information"
                                                                            data-id="{{ $family->id }}"
                                                                            data-name="{{ $family->name }}"
                                                                            data-user-id="{{ $family->user_id }}">
                                                                            <i class="ti ti-trash" title="Delete"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <p>No family information available.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="accordion-item">
                                                <div class="row">
                                                    <div class="accordion-header" id="headingFour">
                                                        <div class="accordion-button">
                                                            <div
                                                                class="d-flex align-items-center justify-content-between flex-fill">
                                                                <h5>Education Details</h5>
                                                                <div class="d-flex">
                                                                    <a href="#"
                                                                        class="btn btn-icon btn-sm editEducationBtn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#add_education"
                                                                        data-user-id="{{ $users->id }}"><i
                                                                            class="ti ti-edit"></i></a>
                                                                    <a href="#"
                                                                        class="d-flex align-items-center collapsed collapse-arrow"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#primaryBorderFour"
                                                                        aria-expanded="false"
                                                                        aria-controls="primaryBorderFour">
                                                                        <i class="ti ti-chevron-down fs-18"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="primaryBorderFour"
                                                        class="accordion-collapse collapse border-top"
                                                        aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            @if ($users->education->count())
                                                                @foreach ($users->education as $education)
                                                                    <div class="mb-3">
                                                                        <div class="row align-items-center">
                                                                            <!-- Institution + Course -->
                                                                            <div class="col-md-6">
                                                                                <strong
                                                                                    class="fw-normal">{{ $education->institution_name }}</strong><br>
                                                                                <h6><span>{{ $education->course_or_level }}</span>
                                                                                </h6>
                                                                            </div>
                                                                            <!-- Date Range -->
                                                                            <div class="col-md-3">
                                                                                <span class="text-dark">
                                                                                    {{ \Carbon\Carbon::parse($education->date_from)->format('Y') }}
                                                                                    -
                                                                                    {{ \Carbon\Carbon::parse($education->date_to)->format('Y') }}
                                                                                </span>
                                                                            </div>
                                                                            <!-- Action Buttons -->
                                                                            <div class="col-md-3 text-end">
                                                                                <div class="action-icon d-inline-flex">
                                                                                    <a href="#" class="btn-edit"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#edit_education"
                                                                                        data-id="{{ $education->id }}"
                                                                                        data-user-id="{{ $education->user_id }}"
                                                                                        data-institution-name="{{ $education->institution_name }}"
                                                                                        data-course-level="{{ $education->course_or_level }}"
                                                                                        data-date-from="{{ $education->date_from }}"
                                                                                        data-date-to="{{ $education->date_to }}">
                                                                                        <i class="ti ti-edit"></i>
                                                                                    </a>
                                                                                    <a href="#" class="btn-delete"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#delete_education"
                                                                                        data-id="{{ $education->id }}"
                                                                                        data-institution-name="{{ $education->institution_name }}"
                                                                                        data-user-id="{{ $education->user_id }}">
                                                                                        <i class="ti ti-trash"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <p>No education details available.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="accordion-item">
                                                <div class="row">
                                                    <div class="accordion-header" id="headingFive">
                                                        <div class="accordion-button collapsed">
                                                            <div
                                                                class="d-flex align-items-center justify-content-between flex-fill">
                                                                <h5>Experience</h5>
                                                                <div class="d-flex">
                                                                    <a href="#"
                                                                        class="btn btn-icon btn-sm editExperienceBtn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#add_experience"
                                                                        data-user-id="{{ $users->id }}"><i
                                                                            class="ti ti-edit"></i></a>
                                                                    <a href="#"
                                                                        class="d-flex align-items-center collapsed collapse-arrow"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#primaryBorderFive"
                                                                        aria-expanded="false"
                                                                        aria-controls="primaryBorderFive">
                                                                        <i class="ti ti-chevron-down fs-18"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="primaryBorderFive"
                                                        class="accordion-collapse collapse border-top"
                                                        aria-labelledby="headingFive" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            @if ($users->experience->count())
                                                                <div>
                                                                    @foreach ($users->experience as $experience)
                                                                        <div class="mb-3">
                                                                            <div
                                                                                class="d-flex align-items-center justify-content-between">
                                                                                <div class="col-md-5">
                                                                                    <div class="d-flex flex-column">
                                                                                        <h6 class="fw-medium mb-1">
                                                                                            {{ $experience->previous_company }}
                                                                                        </h6>
                                                                                        <span
                                                                                            class="badge bg-secondary-transparent"
                                                                                            style="width: fit-content; display: inline-flex; align-items: center;">
                                                                                            <i
                                                                                                class="ti ti-point-filled me-1"></i>
                                                                                            {{ $experience->designation }}
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-4">
                                                                                    <p class="text-dark">
                                                                                        {{ \Carbon\Carbon::parse($experience->date_from)->format('M Y') }}
                                                                                        -
                                                                                        @if ($experience->is_present)
                                                                                            Present
                                                                                        @else
                                                                                            {{ \Carbon\Carbon::parse($experience->date_to)->format('M Y') }}
                                                                                        @endif
                                                                                    </p>
                                                                                </div>
                                                                                <div class="action-icon d-inline-flex">
                                                                                    <a href="#" class="btn-edit"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#edit_experience"
                                                                                        data-id="{{ $experience->id }}"
                                                                                        data-user-id="{{ $experience->user_id }}"
                                                                                        data-previous-company="{{ $experience->previous_company }}"
                                                                                        data-designation="{{ $experience->designation }}"
                                                                                        data-date-from="{{ $experience->date_from }}"
                                                                                        data-date-to="{{ $experience->date_to }}"
                                                                                        data-is-present="{{ $experience->is_present }}">
                                                                                        <i class="ti ti-edit"></i>
                                                                                    </a>
                                                                                    <a href="#" class="btn-delete"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#delete_experience"
                                                                                        data-id="{{ $experience->id }}"
                                                                                        data-previous-company="{{ $experience->previous_company }}"
                                                                                        data-user-id="{{ $experience->user_id }}">
                                                                                        <i class="ti ti-trash"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <p>No experience details available.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="contact-grids-tab p-0 mb-3">
                                            <ul class="nav nav-underline" id="myTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="address-tab2"
                                                        data-bs-toggle="tab" data-bs-target="#address2" type="button"
                                                        role="tab" aria-selected="true">Assets</button>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-content" id="myTabContent3">
                                            <div class="tab-pane fade show active" id="address2" role="tabpanel"
                                                aria-labelledby="address-tab2" tabindex="0">
                                                <div class="row">
                                                    <div class="col-md-12 d-flex">
                                                        <div class="card flex-fill">
                                                            <div class="card-body">
                                                                <div class="row align-items-center">
                                                                    <div class="col-md-8">
                                                                        <div class="d-flex align-items-center">
                                                                            <a href="{{ url('project-details') }}"
                                                                                class="flex-shrink-0 me-2">
                                                                                <img src="{{ URL::asset('build/img/products/product-05.jpg') }}"
                                                                                    class="img-fluid rounded-circle"
                                                                                    alt="img">
                                                                            </a>
                                                                            <div>
                                                                                <h6 class="mb-1"><a
                                                                                        href="{{ url('project-details') }}">Dell
                                                                                        Laptop - #343556656</a></h6>
                                                                                <div class="d-flex align-items-center">
                                                                                    <p><span class="text-primary">AST -
                                                                                            001<i
                                                                                                class="ti ti-point-filled text-primary mx-1"></i></span>Assigned
                                                                                        on 22 Nov, 2022 10:32AM </p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div>
                                                                            <span class="mb-1 d-block">Assigned
                                                                                by</span>
                                                                            <a href="#"
                                                                                class="fw-normal d-flex align-items-center">
                                                                                <img class="avatar avatar-sm rounded-circle me-2"
                                                                                    src="{{ URL::asset('build/img/profiles/avatar-01.jpg') }}"
                                                                                    alt="Img">
                                                                                Andrew Symon
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <div class="dropdown ms-2">
                                                                            <a href="javascript:void(0);"
                                                                                class="d-inline-flex align-items-center"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="false">
                                                                                <i class="ti ti-dots-vertical"></i>
                                                                            </a>
                                                                            <ul
                                                                                class="dropdown-menu dropdown-menu-end p-3">
                                                                                <li>
                                                                                    <a href="javascript:void(0);"
                                                                                        class="dropdown-item rounded-1"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#asset_info">View
                                                                                        Info</a>
                                                                                </li>
                                                                                <li>
                                                                                    <a href="javascript:void(0);"
                                                                                        class="dropdown-item rounded-1"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#refuse_msg">Raise
                                                                                        Issue </a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 d-flex">
                                                        <div class="card flex-fill mb-0">
                                                            <div class="card-body">
                                                                <div class="row align-items-center">
                                                                    <div class="col-md-8">
                                                                        <div class="d-flex align-items-center">
                                                                            <a href="{{ url('project-details') }}"
                                                                                class="flex-shrink-0 me-2">
                                                                                <img src="{{ URL::asset('build/img/products/product-06.jpg') }}"
                                                                                    class="img-fluid rounded-circle"
                                                                                    alt="img">
                                                                            </a>
                                                                            <div>
                                                                                <h6 class="mb-1"><a
                                                                                        href="{{ url('project-details') }}">Bluetooth
                                                                                        Mouse - #478878</a></h6>
                                                                                <div class="d-flex align-items-center">
                                                                                    <p><span class="text-primary">AST -
                                                                                            001<i
                                                                                                class="ti ti-point-filled text-primary mx-1"></i></span>Assigned
                                                                                        on 22 Nov, 2022 10:32AM </p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div>
                                                                            <span class="mb-1 d-block">Assigned
                                                                                by</span>
                                                                            <a href="#"
                                                                                class="fw-normal d-flex align-items-center">
                                                                                <img class="avatar avatar-sm rounded-circle me-2"
                                                                                    src="{{ URL::asset('build/img/profiles/avatar-01.jpg') }}"
                                                                                    alt="Img">
                                                                                Andrew Symon
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <div class="dropdown ms-2">
                                                                            <a href="javascript:void(0);"
                                                                                class="d-inline-flex align-items-center"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="false">
                                                                                <i class="ti ti-dots-vertical"></i>
                                                                            </a>
                                                                            <ul
                                                                                class="dropdown-menu dropdown-menu-end p-3">
                                                                                <li>
                                                                                    <a href="javascript:void(0);"
                                                                                        class="dropdown-item rounded-1"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#asset_info">View
                                                                                        Info</a>
                                                                                </li>
                                                                                <li>
                                                                                    <a href="javascript:void(0);"
                                                                                        class="dropdown-item rounded-1"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#refuse_msg">Raise
                                                                                        Issue </a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
 
       <div class="modal fade" id="edit_viewemployee">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title me-2">Edit Employee</h4><span>Employee  ID : {{ $users->employmentDetail->employee_id ?? 'N/A' }}</span>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="detailsEmployeeForm" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" id="detailsInfoUserId">
                    <div class="contact-grids-tab">
                        <ul class="nav nav-underline" id="myTab2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab3" data-bs-toggle="tab" data-bs-target="#basic-info3" type="button" role="tab" aria-selected="true">Employee Information</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="myTabContent2">
                        <div class="tab-pane fade show active" id="basic-info3" role="tabpanel" aria-labelledby="info-tab3" tabindex="0">
                                <div class="modal-body pb-0 ">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center flex-wrap row-gap-3 bg-light w-100 rounded p-3 mb-4">
                                                <div class="d-flex align-items-center justify-content-center avatar avatar-xxl rounded-circle border border-dashed me-2 flex-shrink-0 text-dark frames">
                                                    <img id="detailsPreviewImage" src="{{ asset('storage/'. ($users->personalInformation->profile_picture ?? 'user-13.png')) }}" alt="img" class="rounded-circle">
                                                </div>
                                                <div class="profile-upload">
                                                    <div class="mb-2">
                                                        <h6 class="mb-1">Upload Profile Image</h6>
                                                        <p class="fs-12">Image should be below 4 mb</p>
                                                    </div>
                                                    <div class="profile-uploader d-flex align-items-center">
                                                        <div class="drag-upload-btn btn btn-sm btn-primary me-2">
                                                            Upload
                                                            <input type="file" name="profile_picture" id="detailsProfileImageInput" class="form-control image-sign" multiple="" accept="image/*" onchange="previewDetailsSelectedImage(event)">
                                                        </div>
                                                        <a href="javascript:void(0);" id="detailsCancelImageBtn" class="btn btn-light btn-sm">Cancel</a>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">First Name <span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="first_name" id="detailsFirstName">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="last_name" id="detailsLastName">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Middle Name</label>
                                                <input type="text" class="form-control" name="middle_name" id="detailsMiddleName">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Suffix </label>
                                                <input type="text" class="form-control" name="suffix" id="detailsSuffix">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Employee ID <span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="employee_id" id="detailsEmployeeId">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Joining Date <span class="text-danger"> *</span></label>
                                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy" name="date_hired" id="detailsDateHired">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Username <span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="username" id="detailsUsername">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email <span class="text-danger"> *</span></label>
                                                <input type="email" class="form-control" name="email" id="detailsEmail">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3 ">
                                                <label class="form-label">Password</label>
                                                <div class="pass-group">
                                                    <input type="password" class="pass-input form-control" name="password" id="detailsPassword">
                                                    <span class="ti toggle-password ti-eye-off"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3 ">
                                                <label class="form-label">Confirm Password</label>
                                                <div class="pass-group">
                                                    <input type="password" class="pass-inputs form-control" name="confirm_password" id="detailsConfirmPassword">
                                                    <span class="ti toggle-passwords ti-eye-off"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Role<span class="text-danger"> *</span></label>
                                               <select name="role_id" id="detailsRoleId" class="form-select select2" placeholder="Select Role">
                                                <option value="" disabled selected>Select Role</option>
                                                    @foreach ($roles as $role )
                                                           <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Branch<span class="text-danger"> *</span></label>
                                                <select id="detailsBranchId" name="branch_id" class="form-select select2" oninput="autoFilterBranch('detailsBranchId','detailsDepartmentId','detailsDesignationId',false)" placeholder="Select Branch">
                                                    <option value="" disabled selected>Select Branch</option>
                                                        @foreach ($branches as $branch)
                                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Department<span class="text-danger"> *</span></label>
                                                <select id="detailsDepartmentId" name="department_id" class="form-select select2"  oninput="autoFilterDepartment('detailsDepartmentId','detailsBranchId','detailsDesignationId',false)" placeholder="Select Department">
                                                    <option value="" disabled selected>Select Department</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Designation<span class="text-danger"> *</span></label>
                                                <select id="detailsDesignationId" name="designation_id" class="form-select select2" oninput="autoFilterDesignation('detailsDesignationId','detailsBranchId','detailsDepartmentId',false)" placeholder="Select Designation">
                                                    <option value="" disabled selected>Select Designation</option>
                                                        @foreach ($designations as $designation)
                                                            <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                                        @endforeach
                                                </select>
                                            </div>
                                        </div>
                                       
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Employment Status<span class="text-danger"> *</span></label>
                                                <select id="detailsEmploymentStatus" name="employment_status" class="form-select select2" placeholder="Select Status">
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
                                                <label class="form-label">Employment Type<span class="text-danger"> *</span></label>
                                                <select id="detailsEmploymentType" name="employment_type" class="form-select select2" placeholder="Select Type">
                                                    <option value="" disabled selected>Select Type</option>
                                                    <option value="Full-Time">Full-Time</option>
                                                    <option value="Part-Time">Part-time</option>
                                                    <option value="Freelancer">Freelancer</option>
                                                    <option value="Consultant">Consultant</option>
                                                    <option value="Apprentice">Apprentice</option>
                                                    <option value="Remote">Remote</option>
                                                    <option value="Field-Based">Field-Based</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save </button>
                                </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'banks' => $banks,
        'users' => $users,
        'departments' => $departments,
        'designations' => $designations,
        'roles' => $roles,
        'branches' => $branches,
        'employees' => $employees,
    ])
    @endcomponent
@endsection

@push('scripts')
    {{-- Copy to clipboard --}}
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                toastr.success('Copied: ' + text); // Optional: Show alert when copied
            }).catch(function(err) {
                toastr.error('Error copying text: ', err);
            });
        }
    </script>
    <script>
        var currentImagePath =
            "{{ asset('storage/' . ($users->personalInformation->profile_picture ?? 'user-13.png')) }}";
    </script>
    <script>
     function autoFilterBranch(branchSelect,departmentSelect,designationSelect,isFilter = false){
        var branch = $('#' + branchSelect ).val();
        var departmentSelect  = $('#' + departmentSelect );
        var designationSelect = $('#' + designationSelect);
        var departmentPlaceholder = isFilter ? 'All Departments' : 'Select Department';
        var designationPlaceholder = isFilter ? 'All Designations' : 'Select Designation';
          
        
        $.ajax({
            url: '{{ route("branchAuto-filter")}}',
            method: 'GET',
            data: { 
                branch: branch, 
            },
            success: function(response) {
                if (response.status === 'success') { 
                departmentSelect.empty().append(`<option value="" selected>${departmentPlaceholder}</option>`);
                designationSelect.empty().append(`<option value="" selected>${designationPlaceholder}</option>`);
 
                    $.each(response.departments, function(i, department) {
                        departmentSelect.append(
                            $('<option>', {
                                value: department.id,
                                text: department.department_name
                            })
                        );
                    }); 
                    $.each(response.designations, function(i, designation) {
                        designationSelect.append(
                            $('<option>', {
                                value: designation.id,
                                text: designation.designation_name
                            })
                        );
                    }); 
                } else {
                    toastr.warning('Failed to get departments and designation list.');
                }
            },
            error: function() {
                toastr.error('An error occurred while getting departments and designation list.');
            }
        }); 
    }
  
     function autoFilterDepartment(departmentSelect,branchSelect,designationSelect,isFilter = false) {
        let department = $('#' + departmentSelect ).val();
        let branch_select = $('#' + branchSelect);
        let designation_select = $('#' + designationSelect); 
        var designationPlaceholder = isFilter ? 'All Designations' : 'Select Designation';
        
        $.ajax({
            url: '{{ route("departmentAuto-filter")}}',
            method: 'GET',
            data: { 
                department: department, 
                branch: branch_select.val(),
            },
            success: function(response) {
                if (response.status === 'success') {  
                    if(response.branch_id !== ''){ 
                       branch_select.val(response.branch_id).trigger('change'); 
                    }
                   designation_select.empty().append(`<option value="" selected>${designationPlaceholder}</option>`);
                    $.each(response.designations, function(i, designation) {
                        designation_select.append(
                            $('<option>', {
                                value: designation.id,
                                text: designation.designation_name
                            })
                        );
                    }); 
                } else {
                    toastr.warning('Failed to get branch and designation list.');
                }
            },
            error: function() {
                toastr.error('An error occurred while getting branch and designation list.');
            }
        });
    }
    
     function autoFilterDesignation(designationSelect,branchSelect,departmentSelect,isFilter = false) {
        let designation = $('#'+ designationSelect).val();
        let branch_select = $('#' + branchSelect);
        let department_select = $('#' + departmentSelect);
        
        $.ajax({
            url: '{{ route("designationAuto-filter")}}',
            method: 'GET',
            data: { 
                designation: designation, 
            },
            success: function(response) {
                if (response.status === 'success') { 
                    if(response.department_id !== ''){ 
                       department_select.val(response.department_id).trigger('change'); 
                    }
                    if(response.branch_id !== ''){ 
                       branch_select.val(response.branch_id).trigger('change'); 
                    } 
                } else {
                    toastr.warning('Failed to get branch and department list.');
                }
            },
            error: function() {
                toastr.error('An error occurred while getting branch and department list.');
            }
        });
        
    } 
    </script>
    <script src="{{ asset('build/js/employeedetails/employeedetails.js') }}"></script>
    <script src="{{ asset('build/js/employeedetails/salary/salary.js') }}"></script>
@endpush
