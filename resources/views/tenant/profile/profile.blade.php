<?php $page = 'profile'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <div class="row">
                <div class="col-xl-4 theiaStickySidebar">
                    <div class="card card-bg-1">
                        <div class="card-body p-0">
                            <span class="avatar avatar-xl avatar-rounded border border-2 border-white m-auto d-flex mb-2">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#profilePictureModal"><img
                                        src="{{ asset('storage/' . ($users->personalInformation?->profile_picture ?? 'default.png')) }}"
                                        class="w-auto h-auto" alt="Img"></a>
                            </span>
                            <div class="text-center px-3 pb-3 border-bottom">
                                <div class="mb-3">
                                    <h5 class="d-flex align-items-center justify-content-center mb-1">
                                        {{ $users->personalInformation->last_name ?? '' }}
                                        {{ $users->personalInformation->suffix ?? '' }},
                                        {{ $users->personalInformation->first_name ?? '' }}
                                        {{ $users->personalInformation->middle_name ?? '' }}
                                        @if ($users && $users->employmentDetail && $users->employmentDetail->status == 1)
                                            <i class="ti ti-discount-check-filled text-success ms-1"></i>
                                        @else
                                            <i class="ti ti-xbox-x-filled text-danger ms-1"></i>
                                        @endif
                                    </h5>
                                    <span class="badge badge-soft-dark fw-medium me-2">
                                        <i
                                            class="ti ti-point-filled me-1"></i>{{ $users->employmentDetail->designation->designation_name ?? '-' }}
                                    </span>

                                </div>
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-id me-2"></i>
                                            Employee ID
                                        </span>
                                        <p class="text-dark">{{ $users->employmentDetail->employee_id ?? 'N/A' }}</p>
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
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center">
                                            <i class="ti ti-user-check me-2"></i>
                                            Reporting To
                                        </span>
                                        @if (
                                            $users &&
                                                $users->employmentDetail &&
                                                $users->employmentDetail->manager &&
                                                $users->employmentDetail->manager->personalInformation)
                                            <p class="text-dark">
                                                {{ $users->employmentDetail->manager->personalInformation->full_name }}</p>
                                        @elseif (
                                            $users &&
                                                $users->employmentDetail &&
                                                $users->employmentDetail->department &&
                                                $users->employmentDetail->department->department_head &&
                                                $users->employmentDetail->department->department_head->personalInformation)
                                            <p class="text-dark">
                                                {{ $users->employmentDetail->department->department_head->personalInformation->full_name }}
                                            </p>
                                        @else
                                            <p class="text-dark">N/A</p>
                                        @endif
                                    </div>

                                    @if ($users && $users->employmentDetail && $users->employmentDetail->biometrics_id)
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="d-inline-flex align-items-center">
                                                <i class="ti ti-fingerprint me-2"></i>
                                                Biometrics ID
                                            </span>
                                            <p class="text-dark">{{ $users->employmentDetail->biometrics_id }}</p>
                                        </div>
                                    @endif

                                    <div class="row gx-2 mt-3">
                                        <div class="col-12">
                                            <div>
                                                <a href="#" class="btn btn-dark w-100" data-bs-toggle="modal"
                                                    data-bs-target="#change_password">
                                                    <i class="ti ti-lock me-1"></i> Change Password
                                                </a>
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
                                        data-user-id="{{ $users->id ?? '' }}"
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
                                        onclick="copyToClipboard('{{ $users->email ?? '' }}')">{{ $users->email ?? '' }}<i
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
                                        Birthday
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
                                        data-user-id="{{ $users->id ?? '' }}"
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
                            data-bs-toggle="modal" data-bs-target="#edit_emergency"
                            data-user-id="{{ $users->id ?? '' }}"
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

                                                    {{-- <a href="{{ url('employees/employee-details/' . $users->id . '/salary-records') }}"
                                                            class="btn btn-sm btn-icon ms-auto"><i class="ti ti-eye"
                                                                title="View Salary Record"></i></a> --}}

                                                    {{-- <a href="javascript:void(0);"
                                                            class="btn btn-sm btn-icon ms-auto disabled"
                                                            title="No Salary Record"><i class="ti ti-eye"></i></a> --}}

                                                    <a href="#"
                                                        class="d-flex align-items-center collapsed collapse-arrow ms-auto"
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
                                                    @php
                                                        // Check if branch salary_type or basic_salary exist
                                                        $branchSalaryType =
                                                            $users->employmentDetail->branch->salary_type ?? null;
                                                        $branchBasicSalary =
                                                            $users->employmentDetail->branch->basic_salary ?? null;

                                                        $userSalaryType = $users->activeSalary->salary_type ?? null;
                                                        $userBasicSalary = $users->activeSalary->basic_salary ?? null;

                                                        $displaySalaryType = $branchSalaryType ?: $userSalaryType;
                                                        $displayBasicSalary = $branchBasicSalary ?: $userBasicSalary;
                                                    @endphp

                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">Basic Salary</span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            @if ($branchBasicSalary)
                                                                {{ $branchBasicSalary }}
                                                            @elseif ($userBasicSalary)
                                                                {{ $userBasicSalary }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </h6>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">Salary Type</span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            @if ($branchSalaryType)
                                                                {{ match ($branchSalaryType) {
                                                                    'hourly_rate' => 'Hourly Rate',
                                                                    'daily_rate' => 'Daily Rate',
                                                                    'monthly_fixed' => 'Monthly Fixed',
                                                                    default => 'No salary type configured.',
                                                                } }}
                                                            @elseif ($userSalaryType)
                                                                {{ match ($userSalaryType) {
                                                                    'hourly_rate' => 'Hourly Rate',
                                                                    'daily_rate' => 'Daily Rate',
                                                                    'monthly_fixed' => 'Monthly Fixed',
                                                                    default => 'No salary type configured.',
                                                                } }}
                                                            @else
                                                                No salary type configured.
                                                            @endif
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <span class="d-inline-flex align-items-center">
                                                            SSS
                                                        </span>
                                                        <h6 class="d-flex align-items-center fw-medium mt-1">
                                                            @if ($users && $users->salaryDetail)
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
                                                        </h6>
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
                                                        class="d-flex align-items-center collapsed collapse-arrow ms-auto"
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
                                                        class="d-flex align-items-center collapsed collapse-arrow ms-auto"
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
                                                    @if ($users->family && $users->family->count())
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
                                                                        <a href="#" class="btn-editFamily"
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
                                                                        <a href="#" class="btn-deleteFamily"
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
                                                            @if ($users->education && $users->education->count())
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
                                                                                    <a href="#"
                                                                                        class="btn-deleteEducation"
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
                                                            @if ($users->experience && $users->experience->count())
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
                                                                                    <a href="#"
                                                                                        class="btn-deleteExperience"
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
                                {{-- Assets and Policy/Memo --}}
                                <div class="card">
                                    <div class="card-body">
                                        <div class="contact-grids-tab p-0 mb-3">
                                            <ul class="nav nav-underline" id="myTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="address-tab2"
                                                        data-bs-toggle="tab" data-bs-target="#address2" type="button"
                                                        role="tab" aria-selected="true">Assets</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="address-tab3" data-bs-toggle="tab"
                                                        data-bs-target="#address3" type="button" role="tab"
                                                        aria-selected="true">Policy/Memo</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="address-tab4" data-bs-toggle="tab"
                                                        data-bs-target="#address4" type="button" role="tab"
                                                        aria-selected="true">Attachments</button>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-content" id="myTabContent3">
                                            {{-- Tab Assets --}}
                                            <div class="tab-pane fade show active" id="address2" role="tabpanel"
                                                aria-labelledby="address-tab2" tabindex="0">
                                                <div class="row">
                                                    <div class="col-md-12 d-flex">
                                                        <div class="card flex-fill">
                                                            <div class="card-body">
                                                                <div class="row align-items-center">



                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Tab Policy/Memo --}}
                                            <div class="tab-pane fade" id="address3" role="tabpanel"
                                                aria-labelledby="address-tab3" tabindex="0">
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
                                                </div>
                                            </div>

                                            {{-- Tab Attachments --}}
                                            <div class="tab-pane fade" id="address4" role="tabpanel"
                                                aria-labelledby="address-tab4" tabindex="0">
                                                <div class="row">
                                                    <div class="col-md-12 d-flex">
                                                        <div class="card flex-fill">
                                                            <div class="card-body">
                                                                <div class="row align-items-center">
                                                                    @if ($users instanceof \App\Models\User && is_iterable($users->attachments ?? null) && count($users->attachments) > 0)
                                                                        @foreach ($users->attachments as $attachment)
                                                                            <div class="col-md-8">
                                                                                <div class="d-flex align-items-center">
                                                                                    <div>
                                                                                        <h6
                                                                                            class="mb-1 d-flex align-items-center gap-2">
                                                                                            @if (!empty($attachment->attachment_path) && !empty($attachment->attachment_name))
                                                                                                <a href="{{ asset($attachment->attachment_path) }}"
                                                                                                    target="_blank"
                                                                                                    class="d-inline-flex align-items-center text-decoration-none">
                                                                                                    {{ $attachment->attachment_name }}
                                                                                                    <i
                                                                                                        class="ti ti-eye ms-2"></i>
                                                                                                </a>
                                                                                                <a href="{{ asset($attachment->attachment_path) }}"
                                                                                                    download
                                                                                                    class="btn btn-link btn-sm ms-1 p-0"
                                                                                                    title="Download">
                                                                                                    <i
                                                                                                        class="ti ti-download"></i>
                                                                                                </a>
                                                                                            @else
                                                                                                <span class="text-muted">No
                                                                                                    attachment
                                                                                                    available</span>
                                                                                            @endif
                                                                                        </h6>
                                                                                        <div
                                                                                            class="d-flex align-items-center">
                                                                                            <p>
                                                                                                Upload on
                                                                                                {{ $attachment->created_at ? $attachment->created_at->format('d M, Y h:iA') : 'N/A' }}
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div>
                                                                                    <span class="mb-1 d-block">Upload
                                                                                        By</span>
                                                                                    @if ($attachment->uploadBy instanceof \App\Models\User)
                                                                                        <a href="#"
                                                                                            class="fw-normal d-flex align-items-center">
                                                                                            <img class="avatar avatar-sm rounded-circle me-2"
                                                                                                src="{{ $attachment->uploadBy->personalInformation && $attachment->uploadBy->personalInformation->profile_picture
                                                                                                    ? asset('storage/' . $attachment->uploadBy->personalInformation->profile_picture)
                                                                                                    : asset('build/img/profiles/avatar-01.jpg') }}"
                                                                                                alt="Img">
                                                                                            {{ $attachment->uploadBy->personalInformation->full_name ?? 'Unnamed User' }}
                                                                                        </a>
                                                                                        @elseif ($attachment->uploadBy instanceof \App\Models\GlobalUser)
                                                                                        <a href="#"
                                                                                            class="fw-normal d-flex align-items-center">
                                                                                            <img class="avatar avatar-sm rounded-circle me-2"
                                                                                                src="{{ asset('build/img/profiles/avatar-01.jpg') }}"
                                                                                                alt="Img">
                                                                                            {{ $attachment->uploadBy->username ?? 'Unnamed Global User' }}
                                                                                        </a>
                                                                                    @else
                                                                                        <span
                                                                                            class="text-muted">Unknown</span>
                                                                                    @endif

                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <p class="text-muted">No attachments available for
                                                                            this user.</p>
                                                                    @endif
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
    @include('layout.partials.footer-company')
    </div>

    <!-- Modal for Profile Picture Update -->
    <div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profilePictureModalLabel">Update Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form for Uploading Profile Picture -->
                    <form id="profilePictureForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profilePictureInput" class="form-label">Select New Profile Picture</label>
                            <input type="file" class="form-control" id="profilePictureInput" name="profile_picture"
                                accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Changing Password -->
    <div class="modal fade" id="change_password" tabindex="-1" aria-labelledby="change_passwordLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="change_passwordLabel">Change Your Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="pass-group">
                                <input type="password" class="pass-input form-control" id="newPassword"
                                    name="new_password" required>
                                <span class="ti toggle-password ti-eye-off" id="toggleNewPassword"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <div class="pass-group">
                                <input type="password" class="pass-input form-control" id="confirmPassword"
                                    name="new_password_confirmation" required>
                                <span class="ti toggle-password ti-eye-off" id="toggleConfirmPassword"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Edit Basic Information -->
    <div class="modal fade" id="edit_basic">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Basic Info</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="basicInformationForm">
                    <div class="modal-body pb-0">
                        <input type="hidden" name="user_id" id="basicInfoUserId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone </span></label>
                                    <input type="text" class="form-control" name="phone_number"
                                        id="basicInforPhoneNumber">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" id="gender" class="select">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Birthdate</label>
                                    <input type="date" class="form-control" name="birth_date" id="birthDate">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Birthplace</label>
                                    <input type="text" class="form-control" name="birth_place" id="birthPlace">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Complete Address</label>
                                    <textarea name="complete_address" id="completeAddress" cols="30" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal For Edit Personal Information -->
    <div class="modal fade" id="edit_personal">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Personal Info</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="personalInformationForm">
                    <div class="modal-body pb-0">
                        <input type="hidden" name="user_id" id="personalInfoUserId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nationality </span></label>
                                    <input type="text" class="form-control" name="nationality" id="nationality">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Religion</label>
                                    <input type="text" class="form-control" name="religion" id="religion">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Civil Status</label>
                                    <input type="text" class="form-control" name="civil_status" id="civilStatus">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No. of children</label>
                                    <input type="text" class="form-control" name="no_of_children" id="noOfChildren">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Spouse Name</label>
                                    <input type="text" class="form-control" name="spouse_name" id="spouseName">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal For Emergency Contact -->
    <div class="modal fade" id="edit_emergency">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Emergency Contact Details</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="emergencyContactForm">
                    <div class="modal-body pb-0">
                        <div class="border-bottom mb-3 ">
                            <input type="hidden" name="user_id" id="emergencyContactId">
                            <div class="row">
                                <h5 class="mb-3">Primary Contact Details</h5>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Name <span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" name="primary_name" id="primaryName">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Relationship </label>
                                        <input type="text" class="form-control" name="primary_relationship"
                                            id="primaryRelationship">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone No 1 <span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" name="primary_phone_one"
                                            id="primaryPhoneOne">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone No 2 </label>
                                        <input type="text" class="form-control" name="primary_phone_two"
                                            id="primaryPhoneTwo">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h5 class="mb-3">Secondary Contact Details</h5>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name </label>
                                    <input type="text" class="form-control" name="secondary_name" id="secondaryName">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Relationship </label>
                                    <input type="text" class="form-control" name="secondary_relationship"
                                        id="secondaryRelationship">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone No 1 </label>
                                    <input type="text" class="form-control" name="secondary_phone_one"
                                        id="secondaryPhoneOne">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone No 2 </label>
                                    <input type="text" class="form-control" name="secondary_phone_two"
                                        id="secondaryPhoneTwo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal For Add Family Information -->
    <div class="modal fade" id="edit_familyinformation">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Family Information</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="familyForm">
                    <div class="modal-body pb-0">
                        <!-- Container for Dynamic Fields -->
                        <div id="familyFieldsContainer">
                            <div class="row family-info">
                                <input type="hidden" id="familyUserId" name="user_id">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Name <span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" name="name[]" id="name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Relationship<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" name="relationship[]"
                                            id="relationship">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Phone </label>
                                        <input type="text" class="form-control" name="phone_number[]"
                                            id="phoneNumber">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Date of birth <span class="text-danger">
                                                *</span></label>
                                        <input type="date" class="form-control" name="birthdate[]" id="birthdate"
                                            placeholder="dd/mm/yyyy">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Add More Button -->
                        <button type="button" class="btn btn-success btn-sm mb-3" id="addFamilyField">
                            <i class="ti ti-plus"></i> Add More
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal For Edit Family Information -->
    <div class="modal fade" id="edit_family_information">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Family Information</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="editFamilyForm">
                    <div class="modal-body pb-0">
                        <!-- Container for Dynamic Fields -->
                        <div id="familyFieldsContainer">
                            <div class="row family-info">
                                <input type="hidden" id="editFamilyId" name="family_id">
                                <input type="hidden" id="editFamilyUserId" name="user_id">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Name <span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" name="name" id="editName">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Relationship<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" name="relationship"
                                            id="editRelationship">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Phone </label>
                                        <input type="text" class="form-control" name="phone_number"
                                            id="editPhoneNumber">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Date of birth <span class="text-danger">
                                                *</span></label>
                                        <input type="date" class="form-control" name="birthdate" id="editBirthdate"
                                            placeholder="dd/mm/yyyy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateFamilyBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal For Delete Family Information -->
    <div class="modal fade" id="delete_family_information">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">
                        Are you sure you want to delete <strong><span id="familyNamePlaceHolder">this
                                details</span></strong>? This can’t be undone.
                    </p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <a href="javascript:void(0);" class="btn btn-danger" id="familyInfoDeleteBtn">Yes, Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Education -->
    <div class="modal fade" id="add_education">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Education Details</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="educationForm">
                    <div class="modal-body pb-0">
                        <input type="hidden" id="educationUserId" name="user_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Institution Name <span class="text-danger">
                                            *</span></label>
                                    <input type="text" class="form-control" name="institution_name"
                                        id="institutionName">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="course_or_level"
                                        id="courseOrLevel">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_from" id="dateFrom">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date <span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_to" id="dateTo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Education -->

    <!-- Edit Education -->
    <div class="modal fade" id="edit_education">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Education Details</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="editEducationForm">
                    <div class="modal-body pb-0">
                        <input type="hidden" id="editEducationUserId" name="user_id">
                        <input type="hidden" id="editEducationId" name="education_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Institution Name <span class="text-danger">
                                            *</span></label>
                                    <input type="text" class="form-control" name="institution_name"
                                        id="editInstitutionName">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Course <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="course_or_level"
                                        id="editCourseOrLevel">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_from" id="editDateFrom">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date <span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_to" id="editDateTo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateEducationBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Edit Education -->

    <!-- Delete Education -->
    <div class="modal fade" id="delete_education">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">
                        Are you sure you want to delete <strong><span id="institutionPlaceHolderName">this
                                detail</span></strong>? This can’t be undone.
                    </p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <a href="javascript:void(0);" class="btn btn-danger" id="educationDeleteBtn">Yes, Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Education -->

    <!-- Add Experience -->
    <div class="modal fade" id="add_experience">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Company Information</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="experienceForm">
                    <div class="modal-body pb-0">
                        <input type="hidden" id="experienceUserId" name="user_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Company Name <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="previous_company"
                                        id="previousCompany">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Designation <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="designation" id="designation">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_from" id="experienceDateFrom">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_to" id="experienceDateTo">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-check-label d-flex align-items-center mt-0">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" checked=""
                                            name="is_present" id="isPresent">
                                        <span class="text-dark">Check if you working present</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Experience -->

    <!-- Edit Experience -->
    <div class="modal fade" id="edit_experience">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Company Information</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="editExperienceForm">
                    <div class="modal-body pb-0">
                        <input type="hidden" id="editExperienceUserId" name="user_id">
                        <input type="hidden" id="editExperienceId" name="experience_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Company Name <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="previous_company"
                                        id="editPreviousCompany">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Designation <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="designation"
                                        id="editDesignation">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Start Date <span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_from" id="editExperienceDateFrom">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" placeholder="dd/mm/yyyy"
                                        name="date_to" id="editExperienceDateTo">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-check-label d-flex align-items-center mt-0">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" checked=""
                                            name="is_present" id="editIsPresent">
                                        <span class="text-dark">Check if you working present</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white border me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateExperienceBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Edit Experience -->

    <!-- Delete Experience -->
    <div class="modal fade" id="delete_experience">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">
                        Are you sure you want to delete <strong><span id="companyPlaceHolderName">this
                                detail</span></strong>? This can’t be undone.
                    </p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <a href="javascript:void(0);" class="btn btn-danger" id="experienceDeleteBtn">Yes, Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Experience -->

    <!-- /Page Wrapper -->
    @component('components.modal-popup')
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

    {{-- Password Toggle --}}
    <script>
        $(document).ready(function() {
            // Toggle visibility of New Password
            $('#toggleNewPassword').on('click', function() {
                const newPasswordInput = $('#newPassword');
                const icon = $(this);

                if (newPasswordInput.attr('type') === 'password') {
                    newPasswordInput.attr('type', 'text');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                } else {
                    newPasswordInput.attr('type', 'password');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                }
            });

            // Toggle visibility of Confirm Password
            $('#toggleConfirmPassword').on('click', function() {
                const confirmPasswordInput = $('#confirmPassword');
                const icon = $(this);

                if (confirmPasswordInput.attr('type') === 'password') {
                    confirmPasswordInput.attr('type', 'text');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                } else {
                    confirmPasswordInput.attr('type', 'password');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                }
            });
        });
    </script>

    {{-- Profile Picture --}}
    <script>
        $(document).ready(function() {
            $('#profilePictureForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: '/api/profile/update/profile-picture',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.success('Profile picture updated successfully!');
                            $('#profilePictureModal').modal('hide');
                            location
                                .reload();
                        } else {
                            toastr.error(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error(
                            'Something went wrong while updating the profile picture.');
                    }
                });
            });
        });
    </script>

    {{-- Change Password --}}
    <script>
        $(document).ready(function() {
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();

                // Check if the passwords match on the frontend
                if (newPassword !== confirmPassword) {
                    toastr.error("The new password and confirmation password do not match.");
                    return; // Stop form submission if passwords don't match
                }

                var formData = $(this).serialize(); // Serialize form data, including both passwords

                $.ajax({
                    url: '/api/profile/change-password', // Your route here
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'), // CSRF token for security
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#change_password').modal('hide');
                            $('#changePasswordForm')[0].reset();
                        }
                    },
                    error: function(xhr, status, error) {
                        var response = xhr.responseJSON;
                        if (response.errors) {
                            toastr.error(response.errors.new_password ? response.errors
                                .new_password[0] : 'Something went wrong.');
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            });
        });
    </script>

    {{-- Edit Basic Information --}}
    <script>
        $(document).ready(function() {
            $(".ediBasicInformation").on('click', function() {
                var userId = $(this).data('user-id');
                var phoneNumber = $(this).data('phone-number');
                var gender = $(this).data('gender');
                var birthDate = $(this).data('birthdate');
                var birthPlace = $(this).data('birthplace');
                var completeAddress = $(this).data('complete-address');

                // Populate modal with data
                $('#basicInfoUserId').val(userId);
                $('#basicInforPhoneNumber').val(phoneNumber);
                $('#gender').val(gender);
                $('#birthDate').val(birthDate);
                $('#birthPlace').val(birthPlace);
                $('#completeAddress').val(completeAddress);
            });

            // Handling form submission
            $('#basicInformationForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: '/api/profile/update/basic-information',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success === true || response.status === 'success') {
                            $('#edit_basic').modal('hide');
                            toastr.success('User information updated successfully!');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error('Error updating user information.');
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            // Validation errors
                            var errors = xhr.responseJSON.errors;
                            var errorMessages = '';

                            $.each(errors, function(key, messages) {
                                $.each(messages, function(i, message) {
                                    errorMessages += message + '\n';
                                });
                            });

                            toastr.error(errorMessages);
                        } else {
                            toastr.error('Something went wrong. Please try again.');
                        }
                    }
                });
            });
        });
    </script>

    {{-- Edit Personal Information --}}
    <script>
        $(document).ready(function() {
            $(".editPersonalInformation").on('click', function() {
                var userId = $(this).data('user-id');
                var nationality = $(this).data('nationality');
                var religion = $(this).data('religion');
                var civilStatus = $(this).data('civil-status');
                var spouseName = $(this).data('spouse-name');
                var noChildren = $(this).data('no-children');

                // Populate modal with data
                $('#personalInfoUserId').val(userId);
                $('#nationality').val(nationality);
                $('#religion').val(religion);
                $('#civilStatus').val(civilStatus);
                $('#spouseName').val(spouseName);
                $('#noOfChildren').val(noChildren);
            });

            // Handling form submission
            $('#personalInformationForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: '/api/profile/update/personal-information',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success === true || response.status === 'success') {
                            $('#edit_personal').modal('hide');
                            toastr.success('User information updated successfully!');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error('Error updating user information.');
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            // Validation errors
                            var errors = xhr.responseJSON.errors;
                            var errorMessages = '';

                            $.each(errors, function(key, messages) {
                                $.each(messages, function(i, message) {
                                    errorMessages += message + '\n';
                                });
                            });

                            toastr.error(errorMessages);
                        } else {
                            toastr.error('Something went wrong. Please try again.');
                        }
                    }
                });
            });
        });
    </script>

    {{-- Edit Emergency Contact --}}
    <script>
        $(document).ready(function() {
            $(".editEmergencyContact").on('click', function() {
                var userId = $(this).data('user-id');
                var primaryName = $(this).data('primary-name');
                var primaryRelationship = $(this).data('primary-relationship');
                var primaryPhoneOne = $(this).data('primary-phoneone');
                var primaryPhoneTwo = $(this).data('primary-phonetwo');
                var secondaryName = $(this).data('secondary-name');
                var secondaryRelationship = $(this).data('secondary-relationship');
                var secondaryPhoneOne = $(this).data('secondary-phoneone');
                var secondaryPhoneTwo = $(this).data('secondary-phonetwo');

                // Populate modal with data
                $('#emergencyContactId').val(userId);
                $('#primaryName').val(primaryName);
                $('#primaryPhoneOne').val(primaryPhoneOne);
                $('#primaryPhoneTwo').val(primaryPhoneTwo);
                $('#primaryRelationship').val(primaryRelationship);
                $('#secondaryName').val(secondaryName);
                $('#secondaryPhoneOne').val(secondaryPhoneOne);
                $('#secondaryPhoneTwo').val(secondaryPhoneTwo);
                $('#secondaryRelationship').val(secondaryRelationship);
            });

            // Handling form submission
            $('#emergencyContactForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: '/api/profile/update/emergency-contact',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success === true || response.status === 'success') {
                            $('#edit_emergency').modal('hide');
                            toastr.success('Emergency contact updated successfully!');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message ||
                                'Error updating emergency contact.');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Something went wrong. Please try again.');
                    }
                });
            });
        });
    </script>

    {{-- Add, Edit and Delete Family Information --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let authToken = localStorage.getItem('token');
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Add a new set of fields
            document.getElementById("addFamilyField").addEventListener("click", function() {
                let container = document.getElementById("familyFieldsContainer");
                let newFieldSet = document.createElement("div");
                newFieldSet.classList.add("row", "family-info");

                // Create new fields with a remove button
                newFieldSet.innerHTML = `
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger"> *</span></label>
                        <input type="text" class="form-control" name="name[]" id="name">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Relationship<span class="text-danger"> *</span></label>
                        <input type="text" class="form-control" name="relationship[]" id="relationship">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Phone </label>
                        <input type="text" class="form-control" name="phone_number[]" id="phoneNumber">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3 position-relative">
                        <div class="mb-3">
                            <label class="form-label">Date of birth <span class="text-danger"> *</span></label>
                                <input type="date" class="form-control" name="birthdate[]" id="birthdate" placeholder="dd/mm/yyyy">
                        </div>
                    </div>
                </div>

                <!-- Remove Button -->
                <div class="col-6 mt-2">
                    <button type="button" class="btn btn-danger btn-sm mb-3 removeFamilyField">
                        <i class="ti ti-x"></i> Remove
                    </button>
                </div>
            `;

                // Append the new field set
                container.appendChild(newFieldSet);

                // Add functionality to remove the added field set
                newFieldSet.querySelector('.removeFamilyField').addEventListener('click', function() {
                    container.removeChild(newFieldSet);
                });
            });


            // Populate User ID
            document.querySelectorAll(".editFamilyInfoBtn").forEach(button => {
                button.addEventListener("click", function() {
                    const userId = this.getAttribute(
                        "data-user-id");

                    document.getElementById("familyUserId").value = userId;
                });
            });

            // Handle form submission
            document.getElementById("familyForm")?.addEventListener("submit", async function(event) {
                event.preventDefault();

                let userId = document.getElementById("familyUserId").value;
                let names = Array.from(document.querySelectorAll("input[name='name[]']")).map(input =>
                    input.value.trim());
                let relationships = Array.from(document.querySelectorAll(
                    "input[name='relationship[]']")).map(input => input.value.trim());
                let phoneNumbers = Array.from(document.querySelectorAll("input[name='phone_number[]']"))
                    .map(input => input.value.trim());
                let birthdates = Array.from(document.querySelectorAll("input[name='birthdate[]']")).map(
                    input => input.value.trim());

                if (!userId) {
                    toastr.error("User ID is missing.");
                    return;
                }

                try {
                    let response = await fetch(
                        `/api/profile/add/family-informations`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                name: names,
                                relationship: relationships,
                                phone_number: phoneNumbers,
                                birthdate: birthdates,
                            })
                        });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success(data.message || "Family informations saved successfully!");
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(data.message || "Failed to save familiy informations.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong. Please try again.");
                }
            });

            // Family Information Edit
            let editId = "";

            // 🌟 1. Populate fields when edit icon is clicked
            document.querySelectorAll('[data-bs-target="#edit_family_information"]').forEach(button => {
                button.addEventListener("click", function() {
                    editId = this.getAttribute("data-id");

                    document.getElementById("editFamilyId").value = editId;
                    document.getElementById("editFamilyUserId").value = this.getAttribute(
                        "data-user-id");
                    document.getElementById("editName").value = this.getAttribute(
                        "data-name");
                    document.getElementById("editRelationship").value = this.getAttribute(
                        "data-relationship");
                    document.getElementById("editPhoneNumber").value = this.getAttribute(
                        "data-phone-number");
                    document.getElementById("editBirthdate").value = this.getAttribute(
                        "data-birthdate");
                });
            });

            // 🌟 2. Handle update button click
            document.getElementById("updateFamilyBtn").addEventListener("click", async function(event) {
                event.preventDefault();

                let userId = document.getElementById("editFamilyUserId").value.trim();
                let editId = document.getElementById("editFamilyId").value.trim();
                let name = document.getElementById("editName").value.trim();
                let relationship = document.getElementById("editRelationship").value.trim();
                let phoneNumber = document.getElementById("editPhoneNumber").value.trim();
                let birthdate = document.getElementById("editBirthdate").value.trim();

                if (name === "" || relationship === "" || phoneNumber === "" ||
                    birthdate === "") {
                    toastr.error("Please complete all fields.");
                    return;
                }

                try {
                    let response = await fetch(
                        `/api/profile/update/family-informations/${editId}`, {
                            method: "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                name: name,
                                relationship: relationship,
                                phone_number: phoneNumber,
                                birthdate: birthdate,
                            })
                        });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success("Family information updated successfully!");
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message || "Update failed.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong.");
                }
            });

            // Delete Family Information
            let deleteId = null;
            let userId = null;

            const deleteButtons = document.querySelectorAll('.btn-deleteFamily');
            const familyInfoDeleteBtn = document.getElementById('familyInfoDeleteBtn');
            const familyNamePlaceHolder = document.getElementById('familyNamePlaceHolder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    userId = this.getAttribute('data-user-id'); // Set userId globally
                    const familyName = this.getAttribute('data-name');

                    if (familyNamePlaceHolder) {
                        familyNamePlaceHolder.textContent =
                            familyName;
                    }
                });
            });

            // Confirm delete button click event
            familyInfoDeleteBtn?.addEventListener('click', function() {
                if (!deleteId || !userId) return; // Ensure both deleteId and userId are available

                fetch(`/api/employees/employee-details/${userId}/family-informations/delete/${deleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Family Information deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_family_information'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting family information.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });

        });
    </script>

    {{-- Add, Edit and Delete Education --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let authToken = localStorage.getItem('token');
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.querySelectorAll(".editEducationBtn").forEach(button => {
                button.addEventListener("click", function() {
                    const userId = this.getAttribute(
                        "data-user-id");

                    document.getElementById("educationUserId").value = userId;
                });
            });

            // Handle form submission
            document.getElementById("educationForm")?.addEventListener("submit", async function(event) {
                event.preventDefault();

                let userId = document.getElementById("educationUserId").value;
                let institutionName = document.getElementById("institutionName").value.trim();
                let courseOrLevel = document.getElementById("courseOrLevel").value.trim();
                let dateFrom = document.getElementById("dateFrom").value.trim();
                let dateTo = document.getElementById("dateTo").value.trim();

                if (!userId) {
                    toastr.error("User ID is missing.");
                    return;
                }

                try {
                    let response = await fetch(
                        `/api/employees/employee-details/${userId}/education-details`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify({
                                institution_name: institutionName,
                                course_or_level: courseOrLevel,
                                date_from: dateFrom,
                                date_to: dateTo,
                            })
                        });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success(data.message || "Education details saved successfully!");
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(data.message || "Failed to save education details.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong. Please try again.");
                }
            });

            //Edit Education Details
            let editEducationId = "";

            // 🌟 1. Populate fields when edit icon is clicked
            document.querySelectorAll('[data-bs-target="#edit_education"]').forEach(button => {
                button.addEventListener("click", function() {
                    editEducationId = this.getAttribute("data-id");

                    document.getElementById("editEducationId").value = editEducationId;
                    document.getElementById("editEducationUserId").value = this.getAttribute(
                        "data-user-id");
                    document.getElementById("editInstitutionName").value = this.getAttribute(
                        "data-institution-name");
                    document.getElementById("editCourseOrLevel").value = this.getAttribute(
                        "data-course-level");
                    document.getElementById("editDateFrom").value = this.getAttribute(
                        "data-date-from");
                    document.getElementById("editDateTo").value = this.getAttribute(
                        "data-date-to");
                });
            });

            // 🌟 2. Handle update button click
            document.getElementById("updateEducationBtn").addEventListener("click", async function(event) {
                event.preventDefault();

                let userId = document.getElementById("editEducationUserId").value.trim();
                let educationId = document.getElementById("editEducationId").value.trim();
                let institutionName = document.getElementById("editInstitutionName").value.trim();
                let courseOrLevel = document.getElementById("editCourseOrLevel").value.trim();
                let dateFrom = document.getElementById("editDateFrom").value.trim();
                let dateTo = document.getElementById("editDateTo").value.trim();

                if (institutionName === "" || courseOrLevel === "" || dateFrom === "" ||
                    dateTo === "") {
                    toastr.error("Please complete all fields.");
                    return;
                }

                try {
                    let response = await fetch(
                        `/api/employees/employee-details/${userId}/education-details/update/${educationId}`, {
                            method: "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                institution_name: institutionName,
                                course_or_level: courseOrLevel,
                                date_from: dateFrom,
                                date_to: dateTo,
                            })
                        });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success("Education details updated successfully!");
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message || "Update failed.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong.");
                }
            });

            // Delete Education
            let educationDeleteId = null;
            let educationUserId = null;

            const educationDeleteButtons = document.querySelectorAll('.btn-deleteEducation');
            const educationDeleteBtn = document.getElementById('educationDeleteBtn');
            const institutionPlaceHolderName = document.getElementById('institutionPlaceHolderName');

            // Set up the delete buttons to capture data
            educationDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    educationDeleteId = this.getAttribute('data-id');
                    educationUserId = this.getAttribute('data-user-id');
                    const institutionName = this.getAttribute('data-institution-name');

                    if (institutionPlaceHolderName) {
                        institutionPlaceHolderName.textContent =
                            institutionName; // Update the modal with the family name
                    }
                });
            });

            // Confirm delete button click event
            educationDeleteBtn?.addEventListener('click', function() {
                if (!educationDeleteId || !educationUserId)
                    return; // Ensure both deleteId and userId are available

                fetch(`/api/employees/employee-details/${educationUserId}/education-details/delete/${educationDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Education detail deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_education'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting education detail.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });

        });
    </script>

    {{-- Add, Edit and Delete Experience --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let authToken = localStorage.getItem('token');
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.querySelectorAll(".editExperienceBtn").forEach(button => {
                button.addEventListener("click", function() {
                    const userId = this.getAttribute(
                        "data-user-id");

                    document.getElementById("experienceUserId").value = userId;
                });
            });

            // Handle "isPresent" checkbox behavior
            const isPresentCheckbox = document.getElementById("isPresent");
            const dateToField = document.getElementById("experienceDateTo");

            if (isPresentCheckbox && dateToField) {
                // Initial state
                if (isPresentCheckbox.checked) {
                    dateToField.disabled = true;
                }

                isPresentCheckbox.addEventListener("change", function() {
                    if (this.checked) {
                        dateToField.disabled = true;
                        dateToField.value = "";
                    } else {
                        dateToField.disabled = false;
                    }
                });
            }

            // Handle form submission
            document.getElementById("experienceForm")?.addEventListener("submit", async function(event) {
                event.preventDefault();

                let userId = document.getElementById("experienceUserId").value;
                let previousCompany = document.getElementById("previousCompany").value.trim();
                let designation = document.getElementById("designation").value.trim();
                let dateFrom = document.getElementById("experienceDateFrom").value.trim();
                let dateTo = document.getElementById("experienceDateTo").value.trim();
                let isPresent = document.getElementById("isPresent").checked ? 1 : 0;

                if (!userId) {
                    toastr.error("User ID is missing.");
                    return;
                }

                const formData = {
                    user_id: userId,
                    previous_company: previousCompany,
                    designation: designation,
                    date_from: dateFrom,
                    date_to: dateTo,
                    is_present: isPresent
                };

                try {
                    let response = await fetch(
                        `/api/employees/employee-details/${userId}/experience-details`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify(formData)
                        });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success(data.message || "Experience details saved successfully!");
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(data.message || "Failed to save experience details.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong. Please try again.");
                }
            });

            // Edit Experience
            let editExperienceId = "";

            // 🌟 1. Populate fields when edit icon is clicked
            document.querySelectorAll('[data-bs-target="#edit_experience"]').forEach(button => {
                button.addEventListener("click", function() {
                    editExperienceId = this.getAttribute("data-id");

                    document.getElementById("editExperienceId").value = editExperienceId;
                    document.getElementById("editExperienceUserId").value = this.getAttribute(
                        "data-user-id");
                    document.getElementById("editPreviousCompany").value = this.getAttribute(
                        "data-previous-company");
                    document.getElementById("editDesignation").value = this.getAttribute(
                        "data-designation");
                    document.getElementById("editExperienceDateFrom").value = this.getAttribute(
                        "data-date-from");
                    document.getElementById("editExperienceDateTo").value = this.getAttribute(
                        "data-date-to");

                    const isPresent = this.getAttribute("data-is-present") == "1";
                    const isPresentCheckbox = document.getElementById("editIsPresent");
                    const dateToField = document.getElementById("editExperienceDateTo");

                    isPresentCheckbox.checked = isPresent;
                    dateToField.disabled = isPresent;
                });
            });

            document.getElementById("editIsPresent").addEventListener("change", function() {
                const dateToField = document.getElementById("editExperienceDateTo");
                dateToField.disabled = this.checked;
                if (this.checked) {
                    dateToField.value = ""; // clear if currently working
                }
            });

            // 🌟 2. Handle update button click
            document.getElementById("updateExperienceBtn").addEventListener("click", async function(event) {
                event.preventDefault();

                let userId = document.getElementById("editExperienceUserId").value.trim();
                let experienceId = document.getElementById("editExperienceId").value.trim();
                let previousCompany = document.getElementById("editPreviousCompany").value.trim();
                let designation = document.getElementById("editDesignation").value.trim();
                let dateFrom = document.getElementById("editExperienceDateFrom").value.trim();
                let dateTo = document.getElementById("editExperienceDateTo").value.trim();
                let isPresent = document.getElementById("editIsPresent").checked ? 1 : 0;


                if (previousCompany === "" || designation === "" || dateFrom === "") {
                    toastr.error("Please complete all fields.");
                    return;
                }

                try {
                    let response = await fetch(
                        `/api/employees/employee-details/${userId}/experience-details/update/${experienceId}`, {
                            method: "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                previous_company: previousCompany,
                                designation: designation,
                                date_from: dateFrom,
                                date_to: dateTo,
                                is_present: isPresent,
                            })
                        });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success("Education details updated successfully!");
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message || "Update failed.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong.");
                }
            });

            // Experience Delete
            let experienceDeleteId = null;
            let experienceUserId = null;

            const experienceDeleteButtons = document.querySelectorAll('.btn-deleteExperience');
            const experienceDeleteBtn = document.getElementById('experienceDeleteBtn');
            const companyPlaceHolderName = document.getElementById('companyPlaceHolderName');

            // Set up the delete buttons to capture data
            experienceDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    experienceDeleteId = this.getAttribute('data-id');
                    experienceUserId = this.getAttribute('data-user-id');
                    const previousCompany = this.getAttribute('data-previous-company');

                    if (companyPlaceHolderName) {
                        companyPlaceHolderName.textContent =
                            previousCompany; // Update the modal with the family name
                    }
                });
            });

            // Confirm delete button click event
            experienceDeleteBtn?.addEventListener('click', function() {
                if (!experienceDeleteId || !experienceUserId)
                    return; // Ensure both deleteId and userId are available

                fetch(`/api/employees/employee-details/${experienceUserId}/experience-details/delete/${experienceDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Experience detail deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_experience'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting experience detail.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });

        });
    </script>
@endpush
