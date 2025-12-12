<?php $page = 'sil-accrual-history'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">SIL Accrual History</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Leaves</li>
                            <li class="breadcrumb-item active" aria-current="page">SIL History</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
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
                <a href="{{ route('sil-eligibility-report') }}" class="btn btn-white border me-2">SIL Eligibility</a>
                <a href="{{ route('sil-accrual-history') }}" class="btn btn-white active border me-2">SIL History</a>
            </div>

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-primary rounded-circle"><i class="ti ti-list-numbers"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Total Accruals</p>
                                    <h4>{{ str_pad($accrualHistory->total(), 2, '0', STR_PAD_LEFT) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-success rounded-circle"><i class="ti ti-calendar-plus"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Total Days Credited</p>
                                    <h4>{{ number_format($accrualHistory->sum('days_credited'), 1) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <span class="avatar avatar-lg bg-info rounded-circle"><i class="ti ti-file-text"></i></span>
                                </div>
                                <div class="ms-2 overflow-hidden">
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Current Page</p>
                                    <h4>{{ $accrualHistory->currentPage() }} of {{ $accrualHistory->lastPage() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SIL Accrual History Table -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Accrual History Records</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="employee_id" id="employee_filter" class="select2 form-select" onchange="applyFilters()" style="width:200px;">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->employee_id }} - {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="leave_type_id" id="leave_type_filter" class="select2 form-select" onchange="applyFilters()" style="width:180px;">
                                <option value="">All SIL Types</option>
                                @foreach($silLeaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}" {{ request('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                        {{ $leaveType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <input type="date" name="from_date" id="from_date_filter" class="form-control" value="{{ request('from_date') }}" onchange="applyFilters()" style="width:160px;">
                        </div>
                        <div class="form-group">
                            <input type="date" name="to_date" id="to_date_filter" class="form-control" value="{{ request('to_date') }}" onchange="applyFilters()" style="width:160px;">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table" id="sil_history_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Accrual Date</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Days Credited</th>
                                    <th>Service Years</th>
                                    <th>Anniversary Date</th>
                                    <th>Processed By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accrualHistory as $record)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($record->accrual_date)->format('M d, Y') }}</td>
                                        <td>
                                            <div>
                                                <p class="text-dark mb-0">
                                                    @if($record->user && $record->user->personalInformation)
                                                        {{ trim($record->user->personalInformation->first_name . ' ' . $record->user->personalInformation->last_name) }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </p>
                                                <span class="fs-12 text-muted">{{ $record->user->employmentDetail->employee_id ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-xs badge-primary">
                                                <i class="ti ti-point-filled me-1"></i>{{ $record->leaveType->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">+{{ number_format($record->days_credited, 1) }} days</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-xs badge-secondary">
                                                <i class="ti ti-point-filled me-1"></i>{{ $record->service_years }} years
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($record->anniversary_date)->format('M d, Y') }}</td>
                                        <td>
                                            @if($record->processed_by === 'system')
                                                <span class="badge badge-xs badge-info">
                                                    <i class="ti ti-point-filled me-1"></i>System (Auto)
                                                </span>
                                            @else
                                                <span class="badge badge-xs badge-warning">
                                                    <i class="ti ti-point-filled me-1"></i>Manual
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $record->notes ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No accrual history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($accrualHistory->hasPages())
                        <div class="d-flex justify-content-center mt-4 mb-3">
                            {{ $accrualHistory->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->

    <script>
        function applyFilters() {
            const employeeId = document.getElementById('employee_filter').value;
            const leaveTypeId = document.getElementById('leave_type_filter').value;
            const fromDate = document.getElementById('from_date_filter').value;
            const toDate = document.getElementById('to_date_filter').value;
            
            const params = new URLSearchParams();
            if (employeeId) params.append('employee_id', employeeId);
            if (leaveTypeId) params.append('leave_type_id', leaveTypeId);
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);
            
            window.location.href = '{{ route("sil-accrual-history") }}?' + params.toString();
        }
    </script>
@endsection
