<?php

$page = 'mobile-access-license'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Mobile Access License</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('billing.index') }}">Billing</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Mobile Access License</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if ($licensePool->canAssignLicense())
                        <div class="me-2 mb-2">
                            <button type="button" class="btn btn-white border d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#assignAccessModal">
                                <i class="ti ti-plus me-1"></i>
                                Assign Mobile Access
                            </button>
                        </div>
                    @endif
                    <div class="me-2 mb-2">
                        <button type="button" class="btn btn-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#purchaseLicensesModal">
                            <i class="ti ti-shopping-cart me-1"></i>
                            Purchase Licenses
                        </button>
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

            <!-- License Pool Overview Cards -->
            <div class="row g-3 mb-4">
                <!-- Total Licenses -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #0f8b8d 0%, #0b6b67 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Total Licenses</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($licensePool->total_licenses, 2, '0', STR_PAD_LEFT) }}</h2>
                                <small class="text-white-75">Licenses</small>
                            </div>
                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-license" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-license" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Licenses -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Assigned</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($licensePool->used_licenses, 2, '0', STR_PAD_LEFT) }}</h2>
                                <small class="text-white-75">Employees</small>
                            </div>
                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-user-check" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-user-check" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Licenses -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #a33658 0%, #8b2c48 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Available</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($licensePool->available_licenses, 2, '0', STR_PAD_LEFT) }}</h2>
                                <small class="text-white-75">Remaining</small>
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

                <!-- Monthly Cost -->
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Monthly Cost</p>
                                <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:24px;">
                                    ₱{{ number_format($stats['monthly_cost'], 0) }}</h2>
                                <small class="text-white-75">Total Cost</small>
                            </div>
                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-currency-peso" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-currency-peso" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Pool Status & Expiration -->
            @if ($licensePool->total_licenses > 0 || $stats['pool_expires_at'])
                <div class="row mb-4">
                    <!-- License Usage Progress -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-3">License Usage</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $licensePool->used_licenses }} of {{ $licensePool->total_licenses }} licenses used</span>
                                    <span>{{ $licensePool->usage_percentage }}%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar
                                        @if($licensePool->usage_percentage >= 90) bg-danger
                                        @elseif($licensePool->usage_percentage >= 75) bg-warning
                                        @else bg-success @endif"
                                        role="progressbar"
                                        style="width: {{ $licensePool->usage_percentage }}%">
                                    </div>
                                </div>
                                @if ($licensePool->usage_percentage >= 90)
                                    <small class="text-danger mt-2 d-block">
                                        <i class="ti ti-alert-triangle me-1"></i>
                                        Warning: You're running low on available licenses. Consider purchasing more.
                                    </small>
                                @elseif($licensePool->usage_percentage >= 75)
                                    <small class="text-warning mt-2 d-block">
                                        <i class="ti ti-info-circle me-1"></i>
                                        Note: You've used 75% of your licenses.
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pool Expiration Info -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Billing Cycle</h6>
                                @if ($stats['pool_expires_at'])
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Current Period</small>
                                        @if ($stats['pool_started_at'])
                                            <div class="fw-medium">{{ $stats['pool_started_at']->format('M d, Y') }}</div>
                                            <small class="text-muted">to {{ $stats['pool_expires_at']->format('M d, Y') }}</small>
                                        @else
                                            <div class="fw-medium">Expires: {{ $stats['pool_expires_at']->format('M d, Y') }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        @if ($stats['is_pool_expired'])
                                            <span class="badge bg-danger">
                                                <i class="ti ti-alert-circle me-1"></i>EXPIRED
                                            </span>
                                            <small class="text-danger d-block mt-2">
                                                All mobile access has been revoked. Purchase new licenses to renew.
                                            </small>
                                        @elseif ($stats['days_until_expiration'] !== null && $stats['days_until_expiration'] <= 7)
                                            <span class="badge bg-warning">
                                                <i class="ti ti-clock me-1"></i>{{ $stats['days_until_expiration'] }} day{{ $stats['days_until_expiration'] != 1 ? 's' : '' }} left
                                            </span>
                                            <small class="text-warning d-block mt-2">
                                                Pool expires soon. All {{ $licensePool->total_licenses }} licenses will need renewal.
                                            </small>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="ti ti-check me-1"></i>Active ({{ $stats['days_until_expiration'] }} days left)
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="ti ti-info-circle fs-3 text-muted mb-2 d-block"></i>
                                        <small class="text-muted">Purchase licenses to start billing cycle</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mobile Access Assignments Table -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Mobile Access Assignments</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>All Statuses</option>
                                <option value="active">Active</option>
                                <option value="revoked">Revoked</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                onchange="filter()" style="width:200px;">
                                <option value="" selected>All Departments</option>
                                @php
                                    $departments = $assignments->pluck('user.employmentDetail.department')->filter()->unique('id');
                                @endphp
                                @foreach($departments as $dept)
                                    @if($dept)
                                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>Sort By</option>
                                <option value="recent">Recently Assigned</option>
                                <option value="oldest">Oldest First</option>
                                <option value="name">Name (A-Z)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable-filtered" id="mobile_access_assignments_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Employee ID</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                    <th>Assigned Date</th>
                                <th>Expires</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="mobileAccessAssignmentsTableBody">
                                @forelse($assignments as $assignment)
                                    @php
                                        $user = $assignment->user ?? null;
                                        $personalInfo = $user->personalInformation ?? null;
                                        $employmentDetail = $user->employmentDetail ?? null;
                                        $department = $employmentDetail->department ?? null;
                                        $designation = $employmentDetail->designation ?? null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle bg-primary text-white">
                                                        {{ substr($personalInfo->first_name ?? ($user->username ?? 'U'), 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $personalInfo->full_name ?? ($user->username ?? 'Unknown User') }}</h6>
                                                    <small class="text-muted">{{ $user->username ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $employmentDetail->employee_id ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $user->email ?? 'No Email' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $department->department_name ?? 'No Department' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $designation->designation_name ?? 'No Designation' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($assignment->status === 'active')
                                                @if($assignment->expires_at && $assignment->expires_at <= now())
                                                    <span class="badge bg-warning">
                                                        <i class="ti ti-clock me-1"></i>Expired
                                                    </span>
                                                @elseif($assignment->expires_at && $assignment->expires_at <= now()->addDays(7))
                                                    <span class="badge bg-warning">
                                                        <i class="ti ti-alert-triangle me-1"></i>Expiring Soon
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="ti ti-check me-1"></i>Active
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="ti ti-x me-1"></i>Revoked
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y g:i A') : '-' }}
                                            @if($assignment->revoked_at)
                                                <br>
                                                <small class="text-muted">
                                                    Revoked: {{ $assignment->revoked_at->format('M d, Y g:i A') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment->expires_at)
                                                {{ $assignment->expires_at->format('M d, Y') }}
                                                <br>
                                                <small class="text-muted">
                                                    @php
                                                        $daysLeft = now()->diffInDays($assignment->expires_at, false);
                                                    @endphp
                                                    @if($daysLeft > 0)
                                                        {{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }} left
                                                    @elseif($daysLeft === 0)
                                                        Expires today
                                                    @else
                                                        Expired {{ abs($daysLeft) }} day{{ abs($daysLeft) > 1 ? 's' : '' }} ago
                                                    @endif
                                                </small>
                                            @else
                                                <span class="text-muted">No expiration</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($assignment->status === 'active')
                                                @if($assignment->expires_at && $assignment->expires_at <= now()->addDays(7))
                                                    <button class="btn btn-sm btn-warning renew-access-btn me-1" 
                                                            data-assignment-id="{{ $assignment->id }}"
                                                            data-employee-name="{{ $personalInfo->full_name ?? ($user->username ?? 'Unknown User') }}"
                                                            data-bs-toggle="tooltip" 
                                                            title="Renew Mobile Access">
                                                        <i class="ti ti-refresh me-1"></i>Renew
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-danger revoke-access-btn" 
                                                        data-assignment-id="{{ $assignment->id }}"
                                                        data-employee-name="{{ $personalInfo->full_name ?? ($user->username ?? 'Unknown User') }}"
                                                        data-bs-toggle="tooltip" 
                                                        title="Revoke Mobile Access">
                                                    <i class="ti ti-x me-1"></i>Revoke
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ti ti-user-x fs-3 mb-2 d-block"></i>
                                                <p class="mb-0">No mobile access assignments found.</p>
                                                @if($licensePool->canAssignLicense())
                                                    <small>Click "Assign Mobile Access" to get started.</small>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Licenses Modal -->
    <div class="modal fade" id="purchaseLicensesModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Purchase Mobile Access Licenses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="purchaseLicensesForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Number of Licenses</label>
                            <input type="number" class="form-control" name="license_count" id="licenseCount" min="1" max="100" value="1" required>
                            <small class="form-text text-muted">₱49.00 per license</small>
                        </div>
                        <div class="mb-3">
                            <div class="alert alert-info">
                                <h6 class="mb-2">Cost Breakdown:</h6>
                                <div class="d-flex justify-content-between">
                                    <span>Licenses:</span>
                                    <span id="licensesDisplay">1</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Rate per license:</span>
                                    <span>₱49.00</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <span id="subtotalDisplay">₱49.00</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>VAT (12%):</span>
                                    <span id="vatDisplay">₱5.88</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Amount:</span>
                                    <span id="totalCostDisplay">₱54.88</span>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="ti ti-info-circle me-1"></i>
                                    Payment will be processed through HitPay secure payment gateway.
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-shopping-cart me-1"></i>Purchase Licenses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Access Modal -->
    <div class="modal fade" id="assignAccessModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Mobile Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Employee Search -->
                    <div class="mb-3">
                        <label class="form-label">Search Employee</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ti ti-search"></i>
                            </span>
                            <input type="text" class="form-control" id="employeeSearch" placeholder="Search by name or username">
                        </div>
                    </div>

                    <!-- Employee List -->
                    <div id="employeeList" style="max-height: 400px; overflow-y: auto;">
                        @foreach($employees as $employee)
                            @php
                                $userType = $employee->user_type ?? 'tenant_user';
                                // Normalize user_type for database comparison
                                $normalizedUserType = $userType === 'global_admin' ? 'global_user' : $userType;
                                $activeAssignment = $assignments->where('user_id', $employee->id)
                                                              ->where('user_type', $normalizedUserType)
                                                              ->where('status', 'active')
                                                              ->first();
                                $hasAccess = $activeAssignment && (!$activeAssignment->expires_at || $activeAssignment->expires_at > now());
                            @endphp
                            <div class="employee-item border rounded mb-2 {{ $hasAccess ? 'bg-light' : '' }}" 
                                 data-employee-id="{{ $employee->id }}"
                                 data-employee-name="{{ $employee->personalInformation->full_name ?? $employee->username }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-primary text-white">
                                                {{ substr($employee->personalInformation->first_name ?? $employee->username, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $employee->personalInformation->full_name ?? $employee->username }}</h6>
                                            <small class="text-muted">
                                                {{ $employee->employmentDetail->department->department_name ?? 'No Department' }} | 
                                                {{ $employee->username }}
                                            </small>
                                        </div>
                                    </div>
                                    <div>
                                        @if($hasAccess)
                                            <span class="badge bg-success">
                                                <i class="ti ti-check me-1"></i>Has Access
                                            </span>
                                        @else
                                            <button class="btn btn-sm btn-primary assign-access-btn" 
                                                    data-user-id="{{ $employee->id }}"
                                                    data-user-type="{{ $employee->user_type ?? 'tenant_user' }}"
                                                    data-employee-name="{{ $employee->personalInformation->full_name ?? $employee->username }}">
                                                <i class="ti ti-plus me-1"></i>Assign Access
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revoke Access Modal -->
    <div class="modal fade" id="revokeAccessModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Revoke Mobile Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="revokeAccessForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p>Are you sure you want to revoke mobile access from <strong id="revokeEmployeeName"></strong>?</p>
                            <p class="text-muted">This will prevent the employee from accessing the mobile app.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <textarea class="form-control" name="reason" rows="3" placeholder="Enter reason for revoking access..."></textarea>
                        </div>
                        <input type="hidden" id="revokeAssignmentId" name="assignment_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-x me-1"></i>Revoke Access
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layout.partials.footer-company')

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('styles')
    <style>
        .table-empty {
            border-collapse: collapse;
        }
        .table-empty thead th {
            border-bottom: 1px solid #dee2e6;
        }
        .table-empty tbody tr td {
            border-top: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable manually with better control
            if ($('#mobile_access_assignments_table').length) {
                try {
                    // Check if DataTable is already initialized
                    if ($.fn.DataTable.isDataTable('#mobile_access_assignments_table')) {
                        $('#mobile_access_assignments_table').DataTable().destroy();
                    }
                    
                    // Check if table has data rows (excluding header and empty state)
                    const hasData = $('#mobile_access_assignments_table tbody tr').length > 0 && 
                                   !$('#mobile_access_assignments_table tbody tr').first().find('td[colspan]').length;
                    
                    if (hasData) {
                        // Initialize DataTable with full functionality
                        $('#mobile_access_assignments_table').DataTable({
                            "bFilter": true,
                            "ordering": true,
                            "info": true,
                            "responsive": true,
                            "pageLength": 10,
                            "autoWidth": false,
                            "processing": true,
                            "language": {
                                search: ' ',
                                sLengthMenu: 'Show _MENU_ entries',
                                searchPlaceholder: "Search assignments...",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                paginate: {
                                    next: '<i class="ti ti-chevron-right"></i>',
                                    previous: '<i class="ti ti-chevron-left"></i>'
                                },
                            },
                            "columnDefs": [
                                { "orderable": false, "targets": [0, 7] }, // Disable sorting on Employee and Actions columns
                                { "width": "20%", "targets": 0 }, // Employee column
                                { "width": "12%", "targets": 1 }, // Employee ID column
                                { "width": "15%", "targets": 2 }, // Email column
                                { "width": "12%", "targets": 3 }, // Department column
                                { "width": "12%", "targets": 4 }, // Designation column
                                { "width": "10%", "targets": 5 }, // Status column
                                { "width": "15%", "targets": 6 }, // Assigned Date column
                                { "width": "10%", "targets": 7 }  // Actions column
                            ],
                            "order": [[6, 'desc']], // Sort by assigned date descending
                            "drawCallback": function() {
                                // Reinitialize tooltips after table draw
                                $('[data-bs-toggle="tooltip"]').tooltip();
                            }
                        });
                    } else {
                        // For empty tables, just apply basic styling without DataTable functionality
                        console.log('Empty table detected, skipping DataTable initialization');
                        $('#mobile_access_assignments_table').addClass('table-empty');
                        
                        // Hide DataTable controls that aren't relevant for empty tables
                        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate').hide();
                    }
                } catch (error) {
                    console.error('DataTable initialization error:', error);
                    // Fallback: Hide DataTable specific features if initialization fails
                    $('.dataTables_wrapper').hide();
                }
            }

            // Add filter function similar to employee list
            window.filter = function() {
                var statusFilter = $('#status_filter').val();
                var departmentFilter = $('#department_filter').val();
                var sortbyFilter = $('#sortby_filter').val();
                
                var table = $('#mobile_access_assignments_table').DataTable();
                
                // Apply status filter
                if (statusFilter) {
                    table.column(5).search(statusFilter).draw();
                } else {
                    table.column(5).search('').draw();
                }
                
                // Apply department filter  
                if (departmentFilter) {
                    table.column(3).search(departmentFilter).draw();
                } else {
                    table.column(3).search('').draw();
                }
                
                // Apply sorting
                if (sortbyFilter === 'recent') {
                    table.order([[6, 'desc']]).draw();
                } else if (sortbyFilter === 'oldest') {
                    table.order([[6, 'asc']]).draw();
                } else if (sortbyFilter === 'name') {
                    table.order([[0, 'asc']]).draw();
                } else {
                    table.order([[6, 'desc']]).draw();
                }
            }
            // Purchase Licenses - Update cost calculation
            $('#licenseCount').on('input', function() {
                const count = parseInt($(this).val()) || 0;
                const pricePerLicense = 49.00;
                const subtotal = count * pricePerLicense;
                const vatPercentage = 0.12; // 12% VAT
                const vatAmount = subtotal * vatPercentage;
                const totalCost = subtotal + vatAmount;
                
                $('#licensesDisplay').text(count);
                $('#subtotalDisplay').text(`₱${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
                $('#vatDisplay').text(`₱${vatAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
                $('#totalCostDisplay').text(`₱${totalCost.toLocaleString('en-US', {minimumFractionDigits: 2})}`);
            });

            // Purchase Licenses Form
            $('#purchaseLicensesForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = $(this).find('button[type="submit"]');
                const licenseCount = $('#licenseCount').val();
                
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating Invoice...');
                
                $.ajax({
                    url: "{{ route('billing.mobile-access-license.purchase') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        console.log('Purchase response:', data);
                        
                        if (data.success && data.requires_payment) {
                            // Show payment redirect message
                            toastr.info('Redirecting to payment gateway...', '', {
                                timeOut: 2000
                            });
                            
                            // Close modal
                            $('#purchaseLicensesModal').modal('hide');
                            
                            // Redirect to HitPay payment page
                            setTimeout(() => {
                                window.location.href = data.payment_url;
                            }, 1500);
                        } else if (data.success) {
                            toastr.success(data.message);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toastr.error(data.message);
                            submitBtn.prop('disabled', false).html('<i class="ti ti-shopping-cart me-1"></i>Purchase Licenses');
                        }
                    },
                    error: function(xhr) {
                        console.log('Purchase error:', xhr.responseText);
                        
                        if (xhr.status === 503 && xhr.responseJSON?.development_mode) {
                            // Handle development mode HitPay unavailable
                            const response = xhr.responseJSON;
                            toastr.warning(response.message, 'Development Mode', {
                                timeOut: 5000
                            });
                            console.log('HitPay Error Details:', response.error_details);
                        } else {
                            const errorMessage = xhr.responseJSON?.message || 'An error occurred while creating invoice.';
                            toastr.error(errorMessage);
                        }
                        
                        submitBtn.prop('disabled', false).html('<i class="ti ti-shopping-cart me-1"></i>Purchase Licenses');
                    }
                });
            });

            // Assign Access functionality
            $(document).on('click', '.assign-access-btn', function() {
                const userId = $(this).data('user-id');
                const userType = $(this).data('user-type');
                const employeeName = $(this).data('employee-name');
                
                if (confirm(`Assign mobile access to ${employeeName}?`)) {
                    const button = $(this);
                    const originalHtml = button.html();
                    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Assigning...');
                    
                    $.ajax({
                        url: "{{ route('billing.mobile-access-license.assign') }}",
                        method: 'POST',
                        data: {
                            user_id: userId,
                            user_type: userType,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(data) {
                            console.log('Assignment response:', data);
                            if (data.success) {
                                toastr.success(data.message);
                                // Close the modal
                                $('#assignAccessModal').modal('hide');
                                // Reload the page to show updated data
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                toastr.error(data.message);
                                button.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Assignment error:', xhr.responseText);
                            toastr.error('An error occurred while assigning access.');
                            button.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });

            // Revoke Access functionality
            $(document).on('click', '.revoke-access-btn', function() {
                const assignmentId = $(this).data('assignment-id');
                const employeeName = $(this).data('employee-name');
                
                $('#revokeAssignmentId').val(assignmentId);
                $('#revokeEmployeeName').text(employeeName);
                $('#revokeAccessModal').modal('show');
            });

            $('#revokeAccessForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Revoking...');
                
                $.ajax({
                    url: "{{ route('billing.mobile-access-license.revoke') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(data) {
                        if (data.success) {
                            toastr.success(data.message);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toastr.error(data.message);
                            submitBtn.prop('disabled', false).html('<i class="ti ti-x me-1"></i>Revoke Access');
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while revoking access.');
                        submitBtn.prop('disabled', false).html('<i class="ti ti-x me-1"></i>Revoke Access');
                    }
                });
            });

            // Employee search in assign modal
            $('#employeeSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                $('.employee-item').each(function() {
                    const employeeName = $(this).data('employee-name').toLowerCase();
                    if (employeeName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush