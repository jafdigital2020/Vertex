<?php $page = 'request-budget'; ?>
@extends('layout.mainlayout')

@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Request Budget</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Request Budget</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>Budget Request Management</h5>
                            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                @php
                                    $canCreate = isset($role_data['user_permission_ids'][52]) || $role_data['role_id'] == 'global_user';
                                @endphp
                                @if($canCreate)
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_budget_request">
                                        <i class="ti ti-plus"></i>New Budget Request
                                    </button>
                                @endif
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
                                            <th>Employee</th>
                                            <th>Budget Category</th>
                                            <th>Amount</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th class="no-sort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Add your data rows here when you have data --}}
                                        {{-- Example:
                                        @forelse($budgetRequests as $request)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input"></td>
                                            <td>{{ $request->employee_name }}</td>
                                            <td>{{ $request->category }}</td>
                                            <td>{{ $request->amount }}</td>
                                            <td>{{ $request->request_date }}</td>
                                            <td><span class="badge">{{ $request->status }}</span></td>
                                            <td>Actions</td>
                                        </tr>
                                        @empty
                                        @endforelse
                                        --}}
                                    </tbody>
                                </table>
                            </div>
                            @if(!isset($budgetRequests) || count($budgetRequests ?? []) === 0)
                            <div class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-calculator fs-1 d-block mb-3"></i>
                                    <p class="mt-2">No budget requests found</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- Add Budget Request Modal -->
    <div class="modal fade" id="add_budget_request" tabindex="-1" aria-labelledby="add_budget_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_budget_request_label">
                        <i class="ti ti-calculator me-2"></i>New Budget Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_name" class="form-label">Employee Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="employee_name" name="employee_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="budget_category" class="form-label">Budget Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="budget_category" name="budget_category" required>
                                    <option value="">Select Category</option>
                                    <option value="Office Supplies">Office Supplies</option>
                                    <option value="Equipment">Equipment</option>
                                    <option value="Travel">Travel</option>
                                    <option value="Training">Training</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="request_date" class="form-label">Request Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="request_date" name="request_date" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter budget request details..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="attachment" class="form-label">Attachment</label>
                                <input type="file" class="form-control" id="attachment" name="attachment">
                                <small class="text-muted">Maximum file size: 5MB</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Budget Request Modal -->
@endsection
