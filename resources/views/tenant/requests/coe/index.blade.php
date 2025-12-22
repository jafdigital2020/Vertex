<?php $page = 'request-coe'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Request COE</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Requests
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Request COE</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>COE Request Management</h5>
                            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_coe_request">
                                    <i class="ti ti-plus"></i>New COE Request
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
                                            <th>COE Type</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th class="no-sort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Add your data rows here when you have data --}}
                                        {{-- Example:
                                        @forelse($coeRequests as $request)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input"></td>
                                            <td>{{ $request->employee_name }}</td>
                                            <td>{{ $request->coe_type }}</td>
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
                            @if(!isset($coeRequests) || count($coeRequests ?? []) === 0)
                            <div class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-certificate fs-1 d-block mb-3"></i>
                                    <p class="mt-2">No COE requests found</p>
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

    <!-- Add COE Request Modal -->
    <div class="modal fade" id="add_coe_request" tabindex="-1" aria-labelledby="add_coe_request_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_coe_request_label">
                        <i class="ti ti-certificate me-2"></i>New Certificate of Employment Request
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
                                <label for="coe_type" class="form-label">COE Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="coe_type" name="coe_type" required>
                                    <option value="">Select COE Type</option>
                                    <option value="Standard COE">Standard COE</option>
                                    <option value="COE with Compensation">COE with Compensation</option>
                                    <option value="COE for Visa Application">COE for Visa Application</option>
                                    <option value="COE for Bank Loan">COE for Bank Loan</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="request_date" class="form-label">Request Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="request_date" name="request_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="needed_date" class="form-label">Date Needed <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="needed_date" name="needed_date" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="2" placeholder="State the purpose of the COE..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="addressee" class="form-label">Addressee (To Whom It May Concern)</label>
                                <input type="text" class="form-control" id="addressee" name="addressee" placeholder="e.g., Philippine Embassy, Bank Manager, etc.">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="copies_needed" class="form-label">Number of Copies <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="copies_needed" name="copies_needed" min="1" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="delivery_method" class="form-label">Delivery Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="delivery_method" name="delivery_method" required>
                                    <option value="">Select Delivery Method</option>
                                    <option value="Pick-up">Pick-up at HR</option>
                                    <option value="Email">Email (PDF)</option>
                                    <option value="Courier">Courier</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="special_instructions" class="form-label">Special Instructions</label>
                                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="2" placeholder="Any special formatting or content requirements..."></textarea>
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
    <!-- /Add COE Request Modal -->
@endsection
