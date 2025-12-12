<?php $page = 'sil-eligibility-report'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">SIL Eligibility Report</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Leaves</li>
                            <li class="breadcrumb-item active" aria-current="page">SIL Eligibility</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="me-2 mb-2">
                        <a href="{{ route('sil-eligibility-export', request()->query()) }}" class="btn btn-success d-flex align-items-center">
                            <i class="ti ti-download me-2"></i>Export CSV
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

            {{-- Tab Links --}}
            <div class="payroll-btns mb-3">
                <a href="{{ route('leave-admin') }}" class="btn btn-white border me-2">Leave Requests</a>
                <a href="{{ route('sil-eligibility-report') }}" class="btn btn-white active border me-2">SIL Eligibility</a>
                <a href="{{ route('sil-accrual-history') }}" class="btn btn-white border me-2">SIL History</a>
            </div>

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-primary rounded-circle"><i class="ti ti-users"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Total Employees</p>
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
                                    <span class="avatar avatar-lg bg-success rounded-circle"><i class="ti ti-circle-check"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">SIL Eligible</p>
                                    <h4>{{ str_pad($employees->filter(function($emp) {
                                        return collect($emp['sil_statuses'])->contains('is_eligible', true);
                                    })->count(), 2, '0', STR_PAD_LEFT) }}</h4>
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
                                    <span class="avatar avatar-lg bg-warning rounded-circle"><i class="ti ti-calendar-event"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Upcoming (30 Days)</p>
                                    <h4>{{ str_pad($employees->filter(function($emp) {
                                        return $emp['days_until_anniversary'] <= 30;
                                    })->count(), 2, '0', STR_PAD_LEFT) }}</h4>
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
                                    <span class="avatar avatar-lg bg-info rounded-circle"><i class="ti ti-list-check"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">SIL Leave Types</p>
                                    <h4>{{ str_pad($silLeaveTypes->count(), 2, '0', STR_PAD_LEFT) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee SIL Eligibility Table -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee SIL Eligibility</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="eligibility" id="eligibility_filter" class="select2 form-select" onchange="applyFilters()" style="width:180px;">
                                <option value="all" {{ $eligibilityFilter === 'all' ? 'selected' : '' }}>All Employees</option>
                                <option value="eligible" {{ $eligibilityFilter === 'eligible' ? 'selected' : '' }}>Eligible Only</option>
                                <option value="not_eligible" {{ $eligibilityFilter === 'not_eligible' ? 'selected' : '' }}>Not Eligible</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="upcoming_days" id="upcoming_days_filter" class="select2 form-select" onchange="applyFilters()" style="width:180px;">
                                <option value="" {{ !$upcomingDays ? 'selected' : '' }}>All Anniversaries</option>
                                <option value="30" {{ $upcomingDays == 30 ? 'selected' : '' }}>Next 30 Days</option>
                                <option value="60" {{ $upcomingDays == 60 ? 'selected' : '' }}>Next 60 Days</option>
                                <option value="90" {{ $upcomingDays == 90 ? 'selected' : '' }}>Next 90 Days</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="sil_eligibility_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Hire Date</th>
                                    <th>Years of Service</th>
                                    <th>Next Anniversary</th>
                                    <th>Days Until</th>
                                    <th>SIL Status</th>
                                    <th>Current Balance</th>
                                    <th>Last Accrual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee['employee_id'] }}</td>
                                        <td>
                                            <div>
                                                <p class="text-dark mb-0">{{ $employee['name'] }}</p>
                                                <span class="fs-12 text-muted">{{ $employee['email'] }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $employee['hire_date'] }}</td>
                                        <td>
                                            <span class="badge badge-xs badge-secondary">
                                                <i class="ti ti-point-filled me-1"></i>{{ $employee['years_of_service'] }} years
                                            </span>
                                        </td>
                                        <td>{{ $employee['next_anniversary'] }}</td>
                                        <td>
                                            @if($employee['days_until_anniversary'] <= 30)
                                                <span class="badge badge-xs badge-danger">
                                                    <i class="ti ti-point-filled me-1"></i>{{ $employee['days_until_anniversary'] }} days
                                                </span>
                                            @elseif($employee['days_until_anniversary'] <= 60)
                                                <span class="badge badge-xs badge-warning">
                                                    <i class="ti ti-point-filled me-1"></i>{{ $employee['days_until_anniversary'] }} days
                                                </span>
                                            @else
                                                <span class="badge badge-xs badge-secondary">
                                                    <i class="ti ti-point-filled me-1"></i>{{ $employee['days_until_anniversary'] }} days
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($employee['sil_statuses'] as $silStatus)
                                                <div class="mb-1">
                                                    <small class="text-muted">{{ $silStatus['leave_type_name'] }}:</small>
                                                    @if($silStatus['is_eligible'])
                                                        <span class="badge badge-xs badge-success">
                                                            <i class="ti ti-point-filled me-1"></i>Eligible
                                                        </span>
                                                    @else
                                                        <span class="badge badge-xs badge-danger">
                                                            <i class="ti ti-point-filled me-1"></i>Not Eligible
                                                        </span>
                                                        <small class="text-muted">({{ $silStatus['minimum_months'] }} mos)</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($employee['sil_statuses'] as $silStatus)
                                                <div class="mb-1">
                                                    <small class="text-muted">{{ $silStatus['leave_type_name'] }}:</small>
                                                    <strong>{{ number_format($silStatus['current_balance'], 1) }} days</strong>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($employee['sil_statuses'] as $silStatus)
                                                <div class="mb-1">
                                                    <small>{{ $silStatus['last_accrual'] }}</small>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No employees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->

    <script>
        function applyFilters() {
            const eligibility = document.getElementById('eligibility_filter').value;
            const upcomingDays = document.getElementById('upcoming_days_filter').value;
            
            const params = new URLSearchParams();
            if (eligibility) params.append('eligibility', eligibility);
            if (upcomingDays) params.append('upcoming_days', upcomingDays);
            
            window.location.href = '{{ route("sil-eligibility-report") }}?' + params.toString();
        }
    </script>
@endsection
