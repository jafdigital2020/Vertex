<?php $page = 'request-loan'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Request Loan</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Request Loan</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>Loan Request Management</h5>
                            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_loan_request">
                                    <i class="ti ti-plus"></i>New Loan Request
                                </button>
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
                                            <th>Loan Amount</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th class="no-sort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Add your data rows here when you have data --}}
                                        {{-- Example:
                                        @forelse($loanRequests as $request)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input"></td>
                                            <td>{{ $request->employee_name }}</td>
                                            <td>{{ $request->loan_amount }}</td>
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
                            @if(!isset($loanRequests) || count($loanRequests ?? []) === 0)
                            <div class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-currency-dollar fs-1 d-block mb-3"></i>
                                    <p class="mt-2">No loan requests found</p>
                                </div>
                            </div>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- Add Loan Request Modal -->
    <div class="modal fade" id="add_loan_request" tabindex="-1" aria-labelledby="add_loan_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_loan_request_label">
                        <i class="ti ti-currency-dollar me-2"></i>New Loan Request
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
                                <label for="loan_type" class="form-label">Loan Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="loan_type" name="loan_type" required>
                                    <option value="">Select Loan Type</option>
                                    <option value="Emergency Loan">Emergency Loan</option>
                                    <option value="Salary Loan">Salary Loan</option>
                                    <option value="Personal Loan">Personal Loan</option>
                                    <option value="Educational Loan">Educational Loan</option>
                                    <option value="Housing Loan">Housing Loan</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="loan_amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="loan_amount" name="loan_amount" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="repayment_period" class="form-label">Repayment Period (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="repayment_period" name="repayment_period" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="request_date" class="form-label">Request Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="request_date" name="request_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                                <input type="number" class="form-control" id="interest_rate" name="interest_rate" step="0.01" value="0" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose of Loan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="2" placeholder="Explain the purpose of the loan..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="collateral" class="form-label">Collateral (if any)</label>
                                <textarea class="form-control" id="collateral" name="collateral" rows="2" placeholder="Describe any collateral or guarantor..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="attachment" class="form-label">Supporting Documents</label>
                                <input type="file" class="form-control" id="attachment" name="attachment">
                                <small class="text-muted">Upload any supporting documents (ID, proof of income, etc.) - Maximum file size: 5MB</small>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <small>Loan requests are subject to approval and company loan policy. Processing time may take 3-5 business days.</small>
                                </div>
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
    <!-- /Add Loan Request Modal -->
@endsection