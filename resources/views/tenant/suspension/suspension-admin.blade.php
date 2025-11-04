<?php $page = 'suspension'; ?>
@extends('layout.mainlayout')
@section('content')

        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content">

                <!-- Breadcrumb -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Employee Suspensions</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                                </li>
                                <li class="breadcrumb-item">Suspension</li>
                                <li class="breadcrumb-item active" aria-current="page">Employee Suspensions</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                        <div class="head-icons ms-2">
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                                data-bs-original-title="Collapse" id="collapse-header">
                                <i class="ti ti-chevrons-up"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Breadcrumb -->

                <!-- Suspension List -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                                <h5 class="d-flex align-items-center mb-0">Suspension List</h5>

                                <div class="d-flex align-items-center flex-wrap row-gap-2">
                                    <input type="text" id="suspension-search" class="form-control form-control-sm me-2" 
                                        placeholder="Search employee name..." style="width: 200px;">
                                    
                                    <select id="suspension-status" class="form-select form-select-sm w-auto me-2">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="implemented">Implemented</option>
                                        <option value="completed">Completed</option>
                                    </select>

                                    <!-- File Suspension Button -->
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#fileSuspensionModal">
                                        <i class="ti ti-file-plus"></i> File Suspension
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-3">
                                <div id="suspension-loading" class="text-center py-4">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>

                                <div id="suspension-error" class="alert alert-danger d-none" role="alert"></div>

                                <div class="table-responsive d-none" id="suspension-table-wrap">
                                    <table class="table table-striped align-middle" id="suspension-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <!-- <th>Employee ID</th> -->
                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Status</th>
                                                <th>Type</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="suspension-tbody">
                                            <!-- rows injected here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @component('components.modal-popup') @endcomponent

            <!-- Custom Styles for Enhanced Select2 -->
            <style>
                /* Modern Select2 Styling */
                .select2-container--bootstrap-5 .select2-selection {
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    min-height: 42px;
                    transition: all 0.3s ease;
                }

                .select2-container--bootstrap-5 .select2-selection:hover {
                    border-color: #86b7fe;
                    box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.1);
                }

                .select2-container--bootstrap-5.select2-container--focus .select2-selection {
                    border-color: #86b7fe;
                    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                }

                .select2-container--bootstrap-5 .select2-dropdown {
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                }

                .select2-container--bootstrap-5 .select2-results__option {
                    padding: 0.5rem 0.75rem;
                    transition: all 0.2s ease;
                }

                .select2-container--bootstrap-5 .select2-results__option--highlighted {
                    background-color: #0d6efd;
                    color: white;
                }

                .select2-container--bootstrap-5 .select2-results__option--selected {
                    background-color: #e7f1ff;
                    color: #0d6efd;
                }

                /* Avatar for employee options */
                .avatar-xs {
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .bg-primary-transparent {
                    background-color: rgba(13, 110, 253, 0.1);
                    color: #0d6efd;
                }

                /* Search input styling */
                .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    padding: 0.5rem 0.75rem;
                    font-size: 0.875rem;
                }

                .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
                    border-color: #86b7fe;
                    outline: 0;
                    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                }

                /* Multiple select badges */
                .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
                    background-color: #0d6efd;
                    border: 1px solid #0d6efd;
                    color: white;
                    border-radius: 0.25rem;
                    padding: 0.25rem 0.5rem;
                    margin: 0.25rem;
                    font-size: 0.875rem;
                }

                .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
                    color: white;
                    margin-right: 0.25rem;
                    opacity: 0.8;
                }

                .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
                    opacity: 1;
                }

                /* Loading state */
                .select2-container--bootstrap-5 .select2-results__option--loading {
                    color: #6c757d;
                    font-style: italic;
                }

                /* No results */
                .select2-container--bootstrap-5 .select2-results__option--no-results {
                    color: #dc3545;
                    padding: 1rem;
                    text-align: center;
                }

                /* Improve label styling */
                .form-label i {
                    color: #0d6efd;
                }
            </style>

            <!-- File Suspension Report Modal -->
            <div class="modal fade" id="fileSuspensionModal" tabindex="-1" aria-labelledby="fileSuspensionModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fileSuspensionModalLabel">File Suspension Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                    <form id="fileSuspensionForm" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="branch" class="form-label">
                                    <i class="ti ti-building me-1"></i>Select Branch(es) <span class="text-danger">*</span>
                                </label>
                                <select id="branch" class="form-select select2" multiple required style="width: 100%;">
                                    <option value="all">📍 All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    <i class="ti ti-info-circle me-1"></i>Select one or more branches, or choose "All Branches"
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="employee" class="form-label">
                                    <i class="ti ti-users me-1"></i>Select Employee <span class="text-danger">*</span>
                                </label>
                                <select name="user_id" id="employee" class="form-select select2" required style="width: 100%;">
                                    <option value="">Select branch(es) first</option>
                                </select>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted" id="employee-count"></small>
                                    <small class="text-info"><i class="ti ti-info-circle me-1"></i>Type to search by name</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="offense_details" class="form-label">Offense Details <span class="text-danger">*</span></label>
                                <textarea name="offense_details" id="offense_details" class="form-control" rows="3"
                                    placeholder="Enter details of the offense..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="information_report_file" class="form-label">Attach Report (Optional)</label>
                                <input type="file" name="information_report_file" id="information_report_file" class="form-control"
                                    accept=".pdf,.doc,.docx">
                            </div>

                            <div id="file-suspension-error" class="alert alert-danger d-none"></div>
                            <div id="file-suspension-success" class="alert alert-success d-none"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Report</button>
                        </div>
                    </form>

                    </div>
                </div>
            </div>

            <!-- Issue NOWE Modal -->
            <div class="modal fade" id="issueNoweModal" tabindex="-1" aria-labelledby="issueNoweModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="issueNoweModalLabel">Issue Notice of Written Explanation (NOWE)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="issueNoweForm" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="nowe_suspension_id" name="suspension_id">

                                <div class="mb-3">
                                    <label for="nowe_file" class="form-label">Upload NOWE Document</label>
                                    <input type="file" name="nowe_file" id="nowe_file" class="form-control" accept=".pdf,.doc,.docx"
                                        required>
                                    <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max 2MB)</small>
                                </div>

                                <div id="nowe-error" class="alert alert-danger d-none"></div>
                                <div id="nowe-success" class="alert alert-success d-none"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning">Issue NOWE</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- Investigation Report Modal -->
            <div class="modal fade" id="InvestigationReportModal" tabindex="-1" aria-labelledby="InvestigationReportModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="InvestigationReportModalLabel">Upload Investigation Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="investigationForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="investigation_suspension_id" name="suspension_id">

                                <div class="mb-3">
                                    <label for="investigation_notes" class="form-label">Investigation Notes</label>
                                    <textarea name="investigation_notes" id="investigation_notes" class="form-control" rows="4"
                                        placeholder="Enter findings and observations..." required></textarea>
                                </div>

                                <div id="investigation-error" class="alert alert-danger d-none"></div>
                                <div id="investigation-success" class="alert alert-success d-none"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit Investigation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Issue DAM Modal -->
            <div class="modal fade" id="IssueDamModal" tabindex="-1" aria-labelledby="IssueDamModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="IssueDamModalLabel">Issue Disciplinary Action Memo (DAM)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="issueDamForm" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="dam_suspension_id" name="suspension_id">

                                <div class="mb-3">
                                    <label for="dam_file" class="form-label">Upload DAM File <span class="text-danger">*</span></label>
                                    <input type="file" name="dam_file" id="dam_file" class="form-control" accept=".pdf,.doc,.docx"
                                        required>
                                    <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max 2MB)</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Suspension Type <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="suspension_type" id="suspension_with_pay" value="with_pay" required>
                                        <label class="form-check-label" for="suspension_with_pay">
                                            <strong>With Pay</strong>
                                            <small class="d-block text-muted">Employee will receive salary during suspension</small>
                                        </label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="radio" name="suspension_type" id="suspension_without_pay" value="without_pay" required>
                                        <label class="form-check-label" for="suspension_without_pay">
                                            <strong>Without Pay</strong>
                                            <small class="d-block text-muted">Employee will NOT receive salary during suspension</small>
                                        </label>
                                    </div>
                                </div>

                                <div id="dam-error" class="alert alert-danger d-none"></div>
                                <div id="dam-success" class="alert alert-success d-none"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Issue DAM</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Implement Suspension Modal -->
            <div class="modal fade" id="implementSuspensionModal" tabindex="-1" aria-labelledby="implementSuspensionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="implementSuspensionModalLabel">Implement Suspension</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="implementSuspensionForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="implement_suspension_id" name="suspension_id">

                                <div class="mb-3">
                                    <label for="suspension_start_date" class="form-label">Suspension Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="suspension_start_date" id="suspension_start_date" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="suspension_end_date" class="form-label">Suspension End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="suspension_end_date" id="suspension_end_date" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="implementation_remarks" class="form-label">Implementation Remarks (Optional)</label>
                                    <textarea name="implementation_remarks" id="implementation_remarks" class="form-control" rows="3" 
                                        placeholder="Enter any remarks..." maxlength="1000"></textarea>
                                    <small class="text-muted">Maximum 1000 characters</small>
                                </div>

                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <strong>Note:</strong> This will implement suspension without pay. The employee's status will be updated accordingly.
                                </div>

                                <div id="implement-suspension-error" class="alert alert-danger d-none"></div>
                                <div id="implement-suspension-success" class="alert alert-success d-none"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Implement Suspension</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Suspension Info Modal -->
            <div class="modal fade" id="viewSuspensionModal" tabindex="-1" aria-labelledby="viewSuspensionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewSuspensionModalLabel">
                                <i class="ti ti-file-info me-2"></i>Suspension Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div id="view-suspension-loading" class="text-center py-4">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div id="view-suspension-error" class="alert alert-danger d-none"></div>

                            <div id="view-suspension-content" class="d-none">
                                <!-- Employee Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-user me-2"></i>Employee Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Name:</strong> <span id="view_employee_name"></span></p>
                                                <p class="mb-2"><strong>Employee ID:</strong> <span id="view_employee_id"></span></p>
                                                <p class="mb-2"><strong>Branch:</strong> <span id="view_branch"></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Department:</strong> <span id="view_department"></span></p>
                                                <p class="mb-2"><strong>Designation:</strong> <span id="view_designation"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Suspension Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-alert-circle me-2"></i>Suspension Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Status:</strong> <span id="view_status" class="badge"></span></p>
                                                <p class="mb-2"><strong>Type:</strong> <span id="view_type"></span></p>
                                                <p class="mb-2"><strong>Filed Date:</strong> <span id="view_filed_date"></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Start Date:</strong> <span id="view_start_date"></span></p>
                                                <p class="mb-2"><strong>End Date:</strong> <span id="view_end_date"></span></p>
                                                <p class="mb-2"><strong>Duration:</strong> <span id="view_duration"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Offense Details -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-file-description me-2"></i>Offense Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <p id="view_offense_details" class="mb-0"></p>
                                    </div>
                                </div>

                                <!-- Investigation Notes -->
                                <div class="card mb-3" id="view_investigation_card" style="display: none;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-search me-2"></i>Investigation Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        <p id="view_investigation_notes" class="mb-0"></p>
                                    </div>
                                </div>

                                <!-- Implementation Remarks -->
                                <div class="card mb-3" id="view_implementation_card" style="display: none;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-notes me-2"></i>Implementation Remarks</h6>
                                    </div>
                                    <div class="card-body">
                                        <p id="view_implementation_remarks" class="mb-0"></p>
                                    </div>
                                </div>

                                <!-- Employee Reply -->
                                <div class="card mb-3" id="view_employee_reply_card" style="display: none;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-message-reply me-2"></i>Employee Reply</h6>
                                    </div>
                                    <div class="card-body">
                                        <p id="view_employee_reply_text" class="mb-2"></p>
                                        <div id="view_employee_reply_file" style="display: none;">
                                            <hr>
                                            <p class="mb-1"><strong>Attachment:</strong></p>
                                            <a id="view_employee_reply_file_link" href="#" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="ti ti-download me-1"></i>Download Reply Document
                                            </a>
                                        </div>
                                        <div class="text-muted small mt-2">
                                            <i class="ti ti-calendar me-1"></i>Submitted: <span id="view_employee_reply_date"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attachments -->
                                <div class="card" id="view_attachments_card" style="display: none;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-paperclip me-2"></i>Attachments</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="view_attachments_list"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Suspension Modal -->
            <div class="modal fade" id="editSuspensionModal" tabindex="-1" aria-labelledby="editSuspensionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSuspensionModalLabel">
                                <i class="ti ti-edit me-2"></i>Edit Suspension
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="editSuspensionForm" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="edit_suspension_id" name="suspension_id">

                                <!-- Employee Info (Read-only) -->
                                <div class="alert alert-info">
                                    <strong>Employee:</strong> <span id="edit_employee_info"></span>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_offense_details" class="form-label">Offense Details <span class="text-danger">*</span></label>
                                    <textarea name="offense_details" id="edit_offense_details" class="form-control" rows="4" 
                                        placeholder="Enter details of the offense..." maxlength="2000" required></textarea>
                                    <small class="text-muted">Maximum 2000 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_disciplinary_action" class="form-label">Disciplinary Action</label>
                                    <textarea name="disciplinary_action" id="edit_disciplinary_action" class="form-control" rows="3" 
                                        placeholder="Enter disciplinary action taken..." maxlength="1000"></textarea>
                                    <small class="text-muted">Maximum 1000 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_remarks" class="form-label">Remarks</label>
                                    <textarea name="remarks" id="edit_remarks" class="form-control" rows="3" 
                                        placeholder="Enter any additional remarks..." maxlength="1000"></textarea>
                                    <small class="text-muted">Maximum 1000 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_information_report_file" class="form-label">Update Report File (Optional)</label>
                                    <input type="file" name="information_report_file" id="edit_information_report_file" class="form-control"
                                        accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max 2MB)</small>
                                    <div id="current_file_info" class="mt-2"></div>
                                </div>

                                <div id="edit-suspension-error" class="alert alert-danger d-none"></div>
                                <div id="edit-suspension-success" class="alert alert-success d-none"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Return to Work Modal -->
            <div class="modal fade" id="returnToWorkModal" tabindex="-1" aria-labelledby="returnToWorkModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="returnToWorkModalLabel">
                                <i class="ti ti-check-circle me-2"></i>Mark Return to Work
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="returnToWorkForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="return_suspension_id" name="suspension_id">

                                <!-- Employee Info (Read-only) -->
                                <div class="alert alert-info">
                                    <strong>Employee:</strong> <span id="return_employee_info"></span>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    <strong>Confirmation Required</strong>
                                    <p class="mb-0 mt-2">This action will mark the employee as returned to work and close the suspension case. The suspension status will be changed to "Completed".</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Return Date</label>
                                    <input type="text" class="form-control" :value="new Date().toLocaleDateString()" readonly>
                                    <small class="text-muted">Employee will be marked as returned today</small>
                                </div>

                                <div id="return-error" class="alert alert-danger d-none"></div>
                                <div id="return-success" class="alert alert-success d-none"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="ti ti-check me-1"></i>Confirm Return to Work
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            <!-- Scripts -->
            <script>
                (function () {
                    const url = "{{ route('suspension-admin') }}";

                    function showLoading(show) {
                        document.getElementById('suspension-loading').classList.toggle('d-none', !show);
                    }

                    function showError(message) {
                        const el = document.getElementById('suspension-error');
                        el.textContent = message;
                        el.classList.remove('d-none');
                    }

                    function hideError() {
                        document.getElementById('suspension-error').classList.add('d-none');
                    }

                   function renderTable(employees) {
                        const wrap = document.getElementById('suspension-table-wrap');
                        const tbody = document.getElementById('suspension-tbody');
                        tbody.innerHTML = '';

                        // Get search term
                        const searchTerm = document.getElementById('suspension-search')?.value?.toLowerCase() || '';
                        
                        // Filter employees by search term
                        const filteredEmployees = employees.filter(emp => {
                            if (!searchTerm) return true;
                            const name = (emp.name || '').toLowerCase();
                            const employeeId = (emp.employee_id || '').toLowerCase();
                            const branch = (emp.branch || '').toLowerCase();
                            const department = (emp.department || '').toLowerCase();
                            return name.includes(searchTerm) || 
                                   employeeId.includes(searchTerm) || 
                                   branch.includes(searchTerm) || 
                                   department.includes(searchTerm);
                        });

                        if (!filteredEmployees || filteredEmployees.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="11" class="text-center">${searchTerm ? 'No matching employees found.' : 'No employees found.'}</td></tr>`;
                        } else {
                            filteredEmployees.forEach((emp, idx) => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                    <td>${idx + 1}</td>
                    <td>${emp.name ?? ''}</td>
                    <!-- <td>${emp.employee_id ?? ''}</td> -->
                    <td>${emp.branch ?? ''}</td>
                    <td>${emp.department ?? ''}</td>
                    <td>${emp.designation ?? ''}</td>
                    <td><span class="badge bg-${getStatusColor(emp.suspension_status)}">${emp.suspension_status ?? ''}</span></td>
                    <td>${emp.suspension_type ?? ''}</td>
                    <td>${emp.suspension_start ?? ''}</td>
                    <td>${emp.suspension_end ?? ''}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary edit-suspension" data-id="${emp.suspension_id || emp.id}" title="Edit Suspension">
                                <i class="ti ti-edit"></i>
                            </button>
                            ${ (emp.suspension_status === 'pending') ? `
                                <button class="btn btn-sm btn-outline-warning issue-nowe" data-id="${emp.suspension_id || emp.id}" title="Issue NOWE">
                                    <i class="ti ti-mail"></i>
                                </button>
                            ` : `
                                <button class="btn btn-sm btn-outline-secondary view-suspension" data-id="${emp.suspension_id || emp.id}" title="View Suspension Details">
                                    <i class="ti ti-eye"></i>
                                </button>
                            `}
                        </div>
                        ${(() => {
                            const status = emp.suspension_status;
                            if (!status) return '';
                            switch (status) {
                                case 'pending':
                                    // Only show nothing or basic info - investigation comes after NOWE
                                    return '';
                                case 'awaiting_reply':
                                case 'under_investigation':
                                    // Show investigation button only after NOWE is issued
                                    return ''
                                        + '<button class="btn btn-sm btn-outline-info" onclick="openInvestigationModal(' + (emp.suspension_id || emp.id) + ')" title="Upload Investigation Report">'
                                        + '<i class="ti ti-upload"></i></button>';
                                case 'for_dam_issuance':
                                    // Show DAM button only if DAM file doesn't exist yet
                                    if (!emp.dam_file) {
                                        return ''
                                            + '<button class="btn btn-sm btn-outline-success" onclick="openDamModal(' + (emp.suspension_id || emp.id) + ')" title="Issue DAM">'
                                            + '<i class="ti ti-file-check"></i></button>';
                                    }
                                    return '';
                                case 'suspended':
                                    // Show suspend button and complete button
                                    let buttons = '';
                                    // Only show DAM button if no DAM file exists yet
                                    if (!emp.dam_file) {
                                        buttons += '<button class="btn btn-sm btn-outline-success" onclick="openDamModal(' + (emp.suspension_id || emp.id) + ')" title="Issue DAM">'
                                            + '<i class="ti ti-file-check"></i></button>';
                                    }
                                    // Show implement suspension button (this sets the dates)
                                    buttons += '<button class="btn btn-sm btn-outline-danger ms-1" onclick="openSuspendModal(' + (emp.suspension_id || emp.id) + ')" title="Implement Suspension">'
                                        + '<i class="ti ti-ban"></i></button>';
                                    // Show complete button
                                    buttons += '<button class="btn btn-sm btn-outline-secondary ms-1" onclick="completeSuspension(' + (emp.suspension_id || emp.id) + ')" title="Complete Suspension">'
                                        + '<i class="ti ti-check"></i></button>';
                                    return buttons;
                                case 'completed':
                                    return '<button class="btn btn-sm btn-outline-secondary" disabled title="Completed"><i class="ti ti-check"></i></button>';
                                default:
                                    return '';
                            }
                        })()}
    

                        </div>
                    </td>
                `;
                                tbody.appendChild(tr);
                            });
                        }

                        wrap.classList.remove('d-none');

                        // ✅ Attach action listeners
                        document.querySelectorAll('.edit-suspension').forEach(btn => {
                            btn.addEventListener('click', () => openEditSuspensionModal(btn.dataset.id));
                        });

                        document.querySelectorAll('.issue-nowe').forEach(btn => {
                            btn.addEventListener('click', () => openNoweModal(btn.dataset.id));
                        });

                        document.querySelectorAll('.view-suspension').forEach(btn => {
                            btn.addEventListener('click', () => viewSuspensionDetails(btn.dataset.id));
                        });

                        document.querySelectorAll('.implement-dam').forEach(btn => {
                            btn.addEventListener('click', () => openDamModal(btn.dataset.id));
                        });
                    }

                    function getStatusColor(status) {
                        switch (status) {
                            case 'pending': return 'warning';
                            case 'implemented': return 'info';
                            case 'completed': return 'success';
                            default: return 'secondary';
                        }
                    }

                    async function loadEmployees() {
                        showLoading(true);
                        hideError();
                        const status = document.getElementById('suspension-status')?.value || '';

                        try {
                            const res = await fetch(`${url}?status=${status}`, {
                                method: 'GET',
                                headers: { 'Accept': 'application/json' },
                                credentials: 'same-origin'
                            });

                            if (!res.ok) throw new Error('Failed to fetch employees: ' + res.status);
                            const data = await res.json();

                            if (data.status === 'success' && Array.isArray(data.employees)) {
                                renderTable(data.employees);
                            } else {
                                throw new Error('Unexpected response from server.');
                            }
                        } catch (err) {
                            showError(err.message || 'An error occurred while loading employees.');
                        } finally {
                            showLoading(false);
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function () {
                        loadEmployees();
                        document.getElementById('suspension-status')?.addEventListener('change', loadEmployees);
                        
                        // Add search functionality with debounce
                        const searchInput = document.getElementById('suspension-search');
                        let searchTimeout;
                        
                        if (searchInput) {
                            searchInput.addEventListener('input', function() {
                                clearTimeout(searchTimeout);
                                searchTimeout = setTimeout(() => {
                                    loadEmployees();
                                }, 500); // Wait 500ms after user stops typing
                            });
                        }
                    });
                })();

                // File Suspension Report Submission
                document.addEventListener('DOMContentLoaded', () => {
                    const fileForm = document.getElementById('fileSuspensionForm');
                    const errorBox = document.getElementById('file-suspension-error');
                    const successBox = document.getElementById('file-suspension-success');

                    fileForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        errorBox.classList.add('d-none');
                        successBox.classList.add('d-none');

                        const formData = new FormData(fileForm);

                        try {
                                      const res = await fetch("{{ route('api.suspensionFileReport') }}", {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: formData
                            });

                            const data = await res.json();

                            if (data.status === 'success') {
                                successBox.textContent = data.message;
                                successBox.classList.remove('d-none');
                                fileForm.reset();
                                setTimeout(() => {
                                    document.querySelector('#fileSuspensionModal .btn-close').click();
                                    location.reload();
                                }, 1500);
                            } else {
                                throw new Error(data.message || 'Something went wrong.');
                            }
                        } catch (err) {
                            errorBox.textContent = err.message;
                            errorBox.classList.remove('d-none');
                        }
                    });
                });


                document.addEventListener('DOMContentLoaded', () => {
                        const branchSelect = document.getElementById('branch');
                        const employeeSelect = document.getElementById('employee');
                        const employeeCountEl = document.getElementById('employee-count');
                        const employeeApiUrl = "{{ route('suspension.employees-by-branch') }}";

                        // Initialize Branch Select2
                        const $branchSelect = $('#branch').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Select branch(es)',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#fileSuspensionModal'),
                            closeOnSelect: false,
                            language: {
                                noResults: function() {
                                    return "No branches found";
                                }
                            },
                            templateResult: function(branch) {
                                if (!branch.id) {
                                    return branch.text;
                                }
                                if (branch.id === 'all') {
                                    return $(`<span><i class="ti ti-map-pins me-2"></i><strong>${branch.text}</strong></span>`);
                                }
                                return $(`<span><i class="ti ti-building me-2"></i>${branch.text}</span>`);
                            }
                        });

                        // Initialize Select2 with modern styling
                        const $employeeSelect = $('#employee').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Select branch(es) first',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#fileSuspensionModal'),
                            language: {
                                noResults: function() {
                                    return "No employees found - try selecting different branch(es)";
                                },
                                searching: function() {
                                    return "Searching employees...";
                                }
                            },
                            templateResult: formatEmployeeOption,
                            templateSelection: formatEmployeeSelection
                        });

                        // Format employee option with icons and styling
                        function formatEmployeeOption(employee) {
                            if (!employee.id) {
                                return employee.text;
                            }
                            
                            return $(`
                                <div class="d-flex align-items-center py-1">
                                    <div class="avatar avatar-xs bg-primary-transparent rounded-circle me-2">
                                        <i class="ti ti-user fs-6"></i>
                                    </div>
                                    <div class="fw-semibold">${employee.text}</div>
                                </div>
                            `);
                        }

                        // Format selected employee (just the name)
                        function formatEmployeeSelection(employee) {
                            return employee.text;
                        }

                        // Use Select2 change event instead of native
                        $branchSelect.on('change', async function() {
                            // Get all selected branch values from Select2
                            const selectedBranches = $(this).val() || [];
                            
                            // Clear Select2 and show loading
                            $employeeSelect.empty().trigger('change');
                            $employeeSelect.append(new Option('Loading employees...', '', false, false)).trigger('change');
                            $employeeSelect.prop('disabled', true);
                            
                            if (employeeCountEl) employeeCountEl.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Loading...';

                            if (selectedBranches.length === 0) {
                                $employeeSelect.empty().trigger('change');
                                $employeeSelect.append(new Option('Select branch(es) first', '', false, false)).trigger('change');
                                $employeeSelect.prop('disabled', false);
                                if (employeeCountEl) employeeCountEl.textContent = '';
                                return;
                            }

                            // Check if "All Branches" is selected
                            const isAllBranches = selectedBranches.includes('all');
                            const branchIds = isAllBranches ? 'all' : selectedBranches.join(',');

                            try {
                                const res = await fetch(`${employeeApiUrl}?branch_id=${branchIds}`);
                                const data = await res.json();

                                if (data.status === 'success') {
                                    $employeeSelect.empty().trigger('change');
                                    $employeeSelect.append(new Option('Select Employee', '', false, false)).trigger('change');
                                    
                                    if (data.employees && data.employees.length > 0) {
                                        data.employees.forEach(emp => {
                                            const optionText = emp.name; // Just the full name
                                            const option = new Option(optionText, emp.id, false, false);
                                            $employeeSelect.append(option);
                                        });
                                        $employeeSelect.trigger('change');
                                        
                                        if (employeeCountEl) {
                                            employeeCountEl.innerHTML = `<i class="ti ti-check-circle text-success me-1"></i>${data.employees.length} employee(s) available`;
                                            employeeCountEl.className = 'text-success';
                                        }
                                    } else {
                                        $employeeSelect.empty().trigger('change');
                                        $employeeSelect.append(new Option('No employees found', '', false, false)).trigger('change');
                                        
                                        if (employeeCountEl) {
                                            employeeCountEl.innerHTML = '<i class="ti ti-alert-circle text-warning me-1"></i>No employees found in selected branch(es)';
                                            employeeCountEl.className = 'text-warning';
                                        }
                                    }
                                } else {
                                    $employeeSelect.empty().trigger('change');
                                    $employeeSelect.append(new Option('No employees found', '', false, false)).trigger('change');
                                }
                            } catch (err) {
                                $employeeSelect.empty().trigger('change');
                                $employeeSelect.append(new Option('Error loading employees', '', false, false)).trigger('change');
                                
                                if (employeeCountEl) {
                                    employeeCountEl.innerHTML = '<i class="ti ti-alert-triangle text-danger me-1"></i>Error loading employees';
                                    employeeCountEl.className = 'text-danger';
                                }
                            } finally {
                                $employeeSelect.prop('disabled', false);
                            }
                        });

                        // Reset Select2 when modal is closed
                        $('#fileSuspensionModal').on('hidden.bs.modal', function () {
                            $branchSelect.val(null).trigger('change');
                            $employeeSelect.val(null).trigger('change');
                            if (employeeCountEl) employeeCountEl.textContent = '';
                        });

                        // Re-initialize Select2 when modal is opened to ensure proper rendering
                        $('#fileSuspensionModal').on('shown.bs.modal', function () {
                            $branchSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Select branch(es)',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#fileSuspensionModal'),
                                closeOnSelect: false
                            });
                            
                            $employeeSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Select branch(es) first',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#fileSuspensionModal')
                            });
                        });
                    });


                    // NOWE

                    // ✅ Issue NOWE modal + submission logic
                        document.addEventListener('DOMContentLoaded', () => {
                            const issueNoweModal = new bootstrap.Modal(document.getElementById('issueNoweModal'));
                            const noweForm = document.getElementById('issueNoweForm');
                            const noweError = document.getElementById('nowe-error');
                            const noweSuccess = document.getElementById('nowe-success');
                            const noweFile = document.getElementById('nowe_file');
                            const noweSuspensionId = document.getElementById('nowe_suspension_id');

                            // Expect a suspension id (not employee id)
                            window.openNoweModal = function (suspensionId) {
                                noweForm.reset();
                                noweError.classList.add('d-none');
                                noweSuccess.classList.add('d-none');

                                // Basic validation of the incoming id
                                if (!suspensionId) {
                                    noweError.textContent = "Invalid suspension id.";
                                    noweError.classList.remove('d-none');
                                    return;
                                }

                                noweSuspensionId.value = suspensionId;
                                issueNoweModal.show();
                            };

                            // Form submit handler
                            noweForm.addEventListener('submit', async (e) => {
                                e.preventDefault();
                                noweError.classList.add('d-none');
                                noweSuccess.classList.add('d-none');

                                const suspensionId = noweSuspensionId.value;
                                if (!suspensionId) {
                                    noweError.textContent = "Invalid suspension record.";
                                    noweError.classList.remove('d-none');
                                    return;
                                }

                                if (!noweFile.files || noweFile.files.length === 0) {
                                    noweError.textContent = "Please select a NOWE file to upload.";
                                    noweError.classList.remove('d-none');
                                    return;
                                }

                                const file = noweFile.files[0];
                                // Optional: enforce 2MB size limit (same hint shown in form)
                                const MAX_SIZE = 2 * 1024 * 1024;
                                if (file.size > MAX_SIZE) {
                                    noweError.textContent = "File is too large. Maximum allowed size is 2MB.";
                                    noweError.classList.remove('d-none');
                                    return;
                                }

                                const formData = new FormData();
                                formData.append('nowe_file', file);

                                try {
                                    const res = await fetch(`{{ url('/api/suspension') }}/${suspensionId}/issue-nowe`, {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: formData
                                    });

                                    // handle non-JSON or error status
                                    const text = await res.text();
                                    let data;
                                    try { data = JSON.parse(text); } catch (_) {
                                        throw new Error('Unexpected server response.');
                                    }

                                    if (data.status === 'success') {
                                        noweSuccess.textContent = data.message || 'NOWE issued successfully.';
                                        noweSuccess.classList.remove('d-none');

                                        setTimeout(() => {
                                            issueNoweModal.hide();
                                            location.reload();
                                        }, 1500);
                                    } else {
                                        throw new Error(data.message || 'Something went wrong.');
                                    }
                                } catch (err) {
                                    noweError.textContent = err.message || 'An error occurred while issuing NOWE.';
                                    noweError.classList.remove('d-none');
                                }
                            });
                        });


            </script>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // base API url for suspension endpoints
                    const apiSuspensionBase = "{{ url('/api/suspension') }}";

                    // Investigation Modal
                    const investigationModal = new bootstrap.Modal(document.getElementById('InvestigationReportModal'));
                    const investigationForm = document.getElementById('investigationForm');
                    const investigationError = document.getElementById('investigation-error');
                    const investigationSuccess = document.getElementById('investigation-success');
                    const investigationSuspensionId = document.getElementById('investigation_suspension_id');

                    // ✅ Function to open modal
                    window.openInvestigationModal = function (suspensionId) {
                        investigationForm.reset();
                        investigationError.classList.add('d-none');
                        investigationSuccess.classList.add('d-none');

                        if (!suspensionId) {
                            investigationError.textContent = 'Invalid suspension id.';
                            investigationError.classList.remove('d-none');
                            return;
                        }

                        investigationSuspensionId.value = suspensionId;
                        investigationModal.show();
                    };

                    // ✅ Handle submission — aligned with controller route: POST /suspension/{id}/investigate
                    investigationForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        investigationError.classList.add('d-none');
                        investigationSuccess.classList.add('d-none');

                        const id = investigationSuspensionId.value;
                        const notesEl = document.getElementById('investigation_notes');
                        const notes = notesEl ? notesEl.value.trim() : '';

                        if (!id) {
                            investigationError.textContent = 'Invalid suspension record.';
                            investigationError.classList.remove('d-none');
                            return;
                        }

                        if (!notes) {
                            investigationError.textContent = 'Please provide investigation notes.';
                            investigationError.classList.remove('d-none');
                            return;
                        }

                        if (notes.length > 2000) {
                            investigationError.textContent = 'Investigation notes must not exceed 2000 characters.';
                            investigationError.classList.remove('d-none');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('investigation_notes', notes);

                        try {
                            const res = await fetch(`${apiSuspensionBase}/${id}/investigate`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: formData,
                                credentials: 'same-origin'
                            });

                            const text = await res.text();
                            let data;
                            try { data = JSON.parse(text); } catch (_) {
                                throw new Error('Unexpected server response.');
                            }

                            if (res.ok && data.status === 'success') {
                                investigationSuccess.textContent = data.message || 'Investigation recorded successfully.';
                                investigationSuccess.classList.remove('d-none');
                                setTimeout(() => {
                                    investigationModal.hide();
                                    location.reload();
                                }, 1200);
                            } else {
                                throw new Error(data.message || `Server error (${res.status}).`);
                            }
                        } catch (err) {
                            investigationError.textContent = err.message || 'Error submitting investigation.';
                            investigationError.classList.remove('d-none');
                        }
                    });

                    // DAM Modal
                    const damModal = new bootstrap.Modal(document.getElementById('IssueDamModal'));
                    const damForm = document.getElementById('issueDamForm');
                    const damError = document.getElementById('dam-error');
                    const damSuccess = document.getElementById('dam-success');
                    const damSuspensionId = document.getElementById('dam_suspension_id');

                    // ✅ Updated to use suspension_id (not user_id)
                    window.openDamModal = function (suspensionId) {
                        damForm.reset();
                        damError.classList.add('d-none');
                        damSuccess.classList.add('d-none');
                        
                        if (!suspensionId) {
                            damError.textContent = 'Invalid suspension id.';
                            damError.classList.remove('d-none');
                            return;
                        }
                        
                        damSuspensionId.value = suspensionId;
                        damModal.show();
                    };

                    damForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        damError.classList.add('d-none');
                        damSuccess.classList.add('d-none');

                        const id = damSuspensionId.value;
                        const fileInput = document.getElementById('dam_file');
                        const file = fileInput.files[0];
                        const suspensionType = document.querySelector('input[name="suspension_type"]:checked');
                        
                        if (!id) {
                            damError.textContent = 'Invalid suspension record.';
                            damError.classList.remove('d-none');
                            return;
                        }
                        
                        if (!file) {
                            damError.textContent = 'Please upload a DAM file.';
                            damError.classList.remove('d-none');
                            return;
                        }

                        if (!suspensionType) {
                            damError.textContent = 'Please select suspension type (with pay or without pay).';
                            damError.classList.remove('d-none');
                            return;
                        }

                        const MAX_SIZE = 2 * 1024 * 1024;
                        if (file.size > MAX_SIZE) {
                            damError.textContent = 'File exceeds 2MB limit.';
                            damError.classList.remove('d-none');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('dam_file', file);
                        formData.append('suspension_type', suspensionType.value);

                        try {
                            // First attempt: assume `id` is a suspension id
                            const res = await fetch(`${apiSuspensionBase}/${id}/issue-dam`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: formData,
                                credentials: 'same-origin'
                            });

                            const text = await res.text();
                            let data;
                            try { data = JSON.parse(text); } catch (_) {
                                throw new Error('Unexpected server response.');
                            }

                            if (res.ok && data.status === 'success') {
                                damSuccess.textContent = data.message || 'DAM issued successfully.';
                                damSuccess.classList.remove('d-none');
                                setTimeout(() => {
                                    damModal.hide();
                                    location.reload();
                                }, 1500);
                                return;
                            }

                            // If server says suspension not found, attempt fallback:
                            if (res.status === 404 || (data && /not found/i.test(data.message || ''))) {
                                // Try to resolve a suspension id for the provided value (maybe an employee id was passed)
                                try {
                                    const lookupRes = await fetch(`${apiSuspensionBase}?employee_id=${encodeURIComponent(id)}&status=for_dam_issuance`, {
                                        method: 'GET',
                                        headers: { 'Accept': 'application/json' },
                                        credentials: 'same-origin'
                                    });

                                    if (!lookupRes.ok) throw new Error('No suspension found for given employee.');

                                    const lookupData = await lookupRes.json();
                                    // Expecting an array in lookupData.suspensions or lookupData.data or lookupData (best-effort)
                                    const arr = lookupData.suspensions || lookupData.data || lookupData;
                                    const first = Array.isArray(arr) && arr.length ? arr[0] : null;

                                    if (!first || !first.id) {
                                        throw new Error('Suspension case not found for this employee.');
                                    }

                                    // Retry using the found suspension id
                                    const retryRes = await fetch(`${apiSuspensionBase}/${first.id}/issue-dam`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        },
                                        body: formData,
                                        credentials: 'same-origin'
                                    });

                                    const retryText = await retryRes.text();
                                    let retryData;
                                    try { retryData = JSON.parse(retryText); } catch (_) {
                                        throw new Error('Unexpected server response on retry.');
                                    }

                                    if (retryRes.ok && retryData.status === 'success') {
                                        damSuccess.textContent = retryData.message || 'DAM issued successfully.';
                                        damSuccess.classList.remove('d-none');
                                        setTimeout(() => {
                                            damModal.hide();
                                            location.reload();
                                        }, 1500);
                                        return;
                                    }

                                    throw new Error(retryData.message || `Server error (${retryRes.status}).`);
                                } catch (lookupErr) {
                                    throw lookupErr;
                                }
                            }

                            // Other errors
                            throw new Error(data.message || `Server error (${res.status}).`);
                        } catch (err) {
                            damError.textContent = err.message || 'Error issuing DAM.';
                            damError.classList.remove('d-none');
                        }
                    });
                });
            </script>

            <script>
                // View Suspension Info Modal
                document.addEventListener('DOMContentLoaded', () => {
                    const apiSuspensionBase = "{{ url('/api/suspension') }}";
                    const viewModal = new bootstrap.Modal(document.getElementById('viewSuspensionModal'));
                    const viewLoading = document.getElementById('view-suspension-loading');
                    const viewError = document.getElementById('view-suspension-error');
                    const viewContent = document.getElementById('view-suspension-content');

                    // Open view modal function
                    window.viewSuspensionDetails = function (suspensionId) {
                        // Reset states
                        viewLoading.classList.remove('d-none');
                        viewError.classList.add('d-none');
                        viewContent.classList.add('d-none');
                        
                        if (!suspensionId) {
                            viewError.textContent = 'Invalid suspension id.';
                            viewError.classList.remove('d-none');
                            viewLoading.classList.add('d-none');
                            return;
                        }
                        
                        viewModal.show();
                        fetchSuspensionDetails(suspensionId);
                    };

                    async function fetchSuspensionDetails(suspensionId) {
                        try {
                            const res = await fetch(`${apiSuspensionBase}/${suspensionId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                credentials: 'same-origin'
                            });

                            if (!res.ok) {
                                throw new Error(`Failed to fetch suspension details (${res.status})`);
                            }

                            const data = await res.json();
                            
                            if (data.status === 'success' && data.suspension) {
                                displaySuspensionDetails(data.suspension);
                            } else {
                                throw new Error(data.message || 'Failed to load suspension details.');
                            }
                        } catch (err) {
                            viewError.textContent = err.message || 'Error loading suspension details.';
                            viewError.classList.remove('d-none');
                            viewLoading.classList.add('d-none');
                        }
                    }

                    function displaySuspensionDetails(suspension) {
                        // Employee Information
                        document.getElementById('view_employee_name').textContent = suspension.employee_name || 'N/A';
                        document.getElementById('view_employee_id').textContent = suspension.employee_id || 'N/A';
                        document.getElementById('view_branch').textContent = suspension.branch || 'N/A';
                        document.getElementById('view_department').textContent = suspension.department || 'N/A';
                        document.getElementById('view_designation').textContent = suspension.designation || 'N/A';

                        // Suspension Information
                        const statusBadge = document.getElementById('view_status');
                        const status = suspension.status || 'N/A';
                        statusBadge.textContent = status;
                        statusBadge.className = 'badge bg-' + getStatusColor(status);

                        document.getElementById('view_type').textContent = suspension.suspension_type ? 
                            suspension.suspension_type.replace('_', ' ').toUpperCase() : 'N/A';
                        document.getElementById('view_filed_date').textContent = suspension.created_at ? 
                            new Date(suspension.created_at).toLocaleDateString() : 'N/A';
                        document.getElementById('view_start_date').textContent = suspension.suspension_start_date || 'N/A';
                        document.getElementById('view_end_date').textContent = suspension.suspension_end_date || 'N/A';
                        document.getElementById('view_duration').textContent = suspension.suspension_days ? 
                            `${suspension.suspension_days} day(s)` : 'N/A';

                        // Offense Details
                        document.getElementById('view_offense_details').textContent = suspension.offense_details || 'No details provided.';

                        // Investigation Notes (show card only if available)
                        const investigationCard = document.getElementById('view_investigation_card');
                        if (suspension.investigation_notes) {
                            document.getElementById('view_investigation_notes').textContent = suspension.investigation_notes;
                            investigationCard.style.display = 'block';
                        } else {
                            investigationCard.style.display = 'none';
                        }

                        // Implementation Remarks (show card only if available)
                        const implementationCard = document.getElementById('view_implementation_card');
                        if (suspension.implementation_remarks) {
                            document.getElementById('view_implementation_remarks').textContent = suspension.implementation_remarks;
                            implementationCard.style.display = 'block';
                        } else {
                            implementationCard.style.display = 'none';
                        }

                        // Employee Reply (show card only if available)
                        const employeeReplyCard = document.getElementById('view_employee_reply_card');
                        if (suspension.employee_reply) {
                            const reply = suspension.employee_reply;
                            document.getElementById('view_employee_reply_text').textContent = reply.description || 'No reply text provided.';
                            document.getElementById('view_employee_reply_date').textContent = reply.action_date || 'N/A';
                            
                            // Show file download if available
                            const replyFileDiv = document.getElementById('view_employee_reply_file');
                            if (reply.file_path) {
                                const fileLink = document.getElementById('view_employee_reply_file_link');
                                fileLink.href = `/storage/${reply.file_path}`;
                                replyFileDiv.style.display = 'block';
                            } else {
                                replyFileDiv.style.display = 'none';
                            }
                            
                            employeeReplyCard.style.display = 'block';
                        } else {
                            employeeReplyCard.style.display = 'none';
                        }

                        // Attachments
                        const attachmentsCard = document.getElementById('view_attachments_card');
                        const attachmentsList = document.getElementById('view_attachments_list');
                        attachmentsList.innerHTML = '';

                        const attachments = [];
                        if (suspension.information_report_file) {
                            attachments.push({ name: 'Information Report', url: suspension.information_report_file });
                        }
                        if (suspension.nowe_file) {
                            attachments.push({ name: 'NOWE Document', url: suspension.nowe_file });
                        }
                        if (suspension.dam_file) {
                            attachments.push({ name: 'DAM Document', url: suspension.dam_file });
                        }

                        if (attachments.length > 0) {
                            attachments.forEach(att => {
                                const link = document.createElement('a');
                                link.href = `/storage/${att.url}`;
                                link.target = '_blank';
                                link.className = 'btn btn-sm btn-outline-primary me-2 mb-2';
                                link.innerHTML = `<i class="ti ti-download me-1"></i>${att.name}`;
                                attachmentsList.appendChild(link);
                            });
                            attachmentsCard.style.display = 'block';
                        } else {
                            attachmentsCard.style.display = 'none';
                        }

                        // Show content, hide loading
                        viewLoading.classList.add('d-none');
                        viewContent.classList.remove('d-none');
                    }

                    function getStatusColor(status) {
                        switch (status) {
                            case 'pending': return 'warning';
                            case 'awaiting_reply': return 'info';
                            case 'under_investigation': return 'primary';
                            case 'for_dam_issuance': return 'secondary';
                            case 'suspended': return 'danger';
                            case 'completed': return 'success';
                            default: return 'secondary';
                        }
                    }
                });
            </script>

            <script>
                // Implement Suspension Modal
                document.addEventListener('DOMContentLoaded', () => {
                    const apiSuspensionBase = "{{ url('/api/suspension') }}";
                    const implementModal = new bootstrap.Modal(document.getElementById('implementSuspensionModal'));
                    const implementForm = document.getElementById('implementSuspensionForm');
                    const implementError = document.getElementById('implement-suspension-error');
                    const implementSuccess = document.getElementById('implement-suspension-success');
                    const implementSuspensionId = document.getElementById('implement_suspension_id');

                    // Open modal function
                    window.openSuspendModal = function (suspensionId) {
                        implementForm.reset();
                        implementError.classList.add('d-none');
                        implementSuccess.classList.add('d-none');
                        
                        if (!suspensionId) {
                            implementError.textContent = 'Invalid suspension id.';
                            implementError.classList.remove('d-none');
                            return;
                        }
                        
                        implementSuspensionId.value = suspensionId;
                        
                        // Set minimum date to today
                        const today = new Date().toISOString().split('T')[0];
                        document.getElementById('suspension_start_date').setAttribute('min', today);
                        document.getElementById('suspension_end_date').setAttribute('min', today);
                        
                        implementModal.show();
                    };

                    // Update end date minimum when start date changes
                    document.getElementById('suspension_start_date').addEventListener('change', function() {
                        const startDate = this.value;
                        if (startDate) {
                            document.getElementById('suspension_end_date').setAttribute('min', startDate);
                        }
                    });

                    // Handle form submission
                    implementForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        implementError.classList.add('d-none');
                        implementSuccess.classList.add('d-none');

                        const id = implementSuspensionId.value;
                        const startDate = document.getElementById('suspension_start_date').value;
                        const endDate = document.getElementById('suspension_end_date').value;
                        const remarks = document.getElementById('implementation_remarks').value;
                        
                        if (!id) {
                            implementError.textContent = 'Invalid suspension record.';
                            implementError.classList.remove('d-none');
                            return;
                        }
                        
                        if (!startDate || !endDate) {
                            implementError.textContent = 'Please provide both start and end dates.';
                            implementError.classList.remove('d-none');
                            return;
                        }

                        // Validate end date is after or equal to start date
                        if (new Date(endDate) < new Date(startDate)) {
                            implementError.textContent = 'End date must be after or equal to start date.';
                            implementError.classList.remove('d-none');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('suspension_start_date', startDate);
                        formData.append('suspension_end_date', endDate);
                        if (remarks) {
                            formData.append('implementation_remarks', remarks);
                        }

                        try {
                            const res = await fetch(`${apiSuspensionBase}/${id}/implement`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: formData,
                                credentials: 'same-origin'
                            });

                            const text = await res.text();
                            let data;
                            try { 
                                data = JSON.parse(text); 
                            } catch (_) {
                                throw new Error('Unexpected server response.');
                            }

                            if (res.ok && data.status === 'success') {
                                implementSuccess.textContent = data.message || 'Suspension implemented successfully.';
                                implementSuccess.classList.remove('d-none');
                                setTimeout(() => {
                                    implementModal.hide();
                                    location.reload();
                                }, 1500);
                            } else {
                                throw new Error(data.message || `Server error (${res.status}).`);
                            }
                        } catch (err) {
                            implementError.textContent = err.message || 'Error implementing suspension.';
                            implementError.classList.remove('d-none');
                        }
                    });
                });
            </script>

            <script>
                // Edit Suspension Modal
                document.addEventListener('DOMContentLoaded', () => {
                    const apiSuspensionBase = "{{ url('/api/suspension') }}";
                    const editModal = new bootstrap.Modal(document.getElementById('editSuspensionModal'));
                    const editForm = document.getElementById('editSuspensionForm');
                    const editError = document.getElementById('edit-suspension-error');
                    const editSuccess = document.getElementById('edit-suspension-success');
                    const editSuspensionId = document.getElementById('edit_suspension_id');

                    // Open edit modal function
                    window.openEditSuspensionModal = function (suspensionId) {
                        editForm.reset();
                        editError.classList.add('d-none');
                        editSuccess.classList.add('d-none');
                        document.getElementById('current_file_info').innerHTML = '';
                        
                        if (!suspensionId) {
                            editError.textContent = 'Invalid suspension id.';
                            editError.classList.remove('d-none');
                            return;
                        }
                        
                        editSuspensionId.value = suspensionId;
                        
                        // Fetch suspension data and populate form
                        fetchAndPopulateSuspension(suspensionId);
                        
                        editModal.show();
                    };

                    async function fetchAndPopulateSuspension(suspensionId) {
                        try {
                            const res = await fetch(`${apiSuspensionBase}/${suspensionId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                credentials: 'same-origin'
                            });

                            if (!res.ok) {
                                throw new Error(`Failed to fetch suspension details (${res.status})`);
                            }

                            const data = await res.json();
                            
                            if (data.status === 'success' && data.suspension) {
                                const suspension = data.suspension;
                                
                                // Populate employee info (read-only)
                                document.getElementById('edit_employee_info').textContent = 
                                    `${suspension.employee_name || 'N/A'} (${suspension.employee_id || 'N/A'})`;
                                
                                // Populate form fields
                                document.getElementById('edit_offense_details').value = suspension.offense_details || '';
                                document.getElementById('edit_disciplinary_action').value = suspension.disciplinary_action || '';
                                document.getElementById('edit_remarks').value = suspension.remarks || '';
                                
                                // Display current file info if exists
                                if (suspension.information_report_file) {
                                    const fileInfo = document.getElementById('current_file_info');
                                    fileInfo.innerHTML = `<small class="text-info">
                                        <i class="ti ti-file-check"></i> Current file: 
                                        <a href="/storage/${suspension.information_report_file}" target="_blank">View Document</a>
                                    </small>`;
                                }
                            } else {
                                throw new Error(data.message || 'Failed to load suspension details.');
                            }
                        } catch (err) {
                            editError.textContent = err.message || 'Error loading suspension details.';
                            editError.classList.remove('d-none');
                        }
                    }

                    // Handle form submission
                    editForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        editError.classList.add('d-none');
                        editSuccess.classList.add('d-none');

                        const id = editSuspensionId.value;
                        const offenseDetails = document.getElementById('edit_offense_details').value;
                        const disciplinaryAction = document.getElementById('edit_disciplinary_action').value;
                        const remarks = document.getElementById('edit_remarks').value;
                        const fileInput = document.getElementById('edit_information_report_file');
                        
                        if (!id) {
                            editError.textContent = 'Invalid suspension record.';
                            editError.classList.remove('d-none');
                            return;
                        }
                        
                        if (!offenseDetails.trim()) {
                            editError.textContent = 'Offense details is required.';
                            editError.classList.remove('d-none');
                            return;
                        }

                        // Validate file if provided
                        if (fileInput.files.length > 0) {
                            const file = fileInput.files[0];
                            const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                            const maxSize = 2 * 1024 * 1024; // 2MB

                            if (!allowedTypes.includes(file.type)) {
                                editError.textContent = 'Invalid file type. Only PDF, DOC, and DOCX are allowed.';
                                editError.classList.remove('d-none');
                                return;
                            }

                            if (file.size > maxSize) {
                                editError.textContent = 'File size must not exceed 2MB.';
                                editError.classList.remove('d-none');
                                return;
                            }
                        }

                        const formData = new FormData();
                        formData.append('_method', 'PUT'); // Laravel method spoofing for FormData
                        formData.append('offense_details', offenseDetails);
                        if (disciplinaryAction) {
                            formData.append('disciplinary_action', disciplinaryAction);
                        }
                        if (remarks) {
                            formData.append('remarks', remarks);
                        }
                        if (fileInput.files.length > 0) {
                            formData.append('information_report_file', fileInput.files[0]);
                        }

                        try {
                            const res = await fetch(`${apiSuspensionBase}/${id}`, {
                                method: 'POST', // Use POST with _method=PUT for FormData
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: formData,
                                credentials: 'same-origin'
                            });

                            const text = await res.text();
                            let data;
                            try { 
                                data = JSON.parse(text); 
                            } catch (_) {
                                throw new Error('Unexpected server response.');
                            }

                            if (res.ok && data.status === 'success') {
                                editSuccess.textContent = data.message || 'Suspension updated successfully.';
                                editSuccess.classList.remove('d-none');
                                setTimeout(() => {
                                    editModal.hide();
                                    location.reload();
                                }, 1500);
                            } else {
                                throw new Error(data.message || `Server error (${res.status}).`);
                            }
                        } catch (err) {
                            editError.textContent = err.message || 'Error updating suspension.';
                            editError.classList.remove('d-none');
                        }
                    });

                    // Add event listener to edit buttons in the table
                    document.querySelectorAll('.edit-suspension').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const suspensionId = btn.dataset.id;
                            openEditSuspensionModal(suspensionId);
                        });
                    });
                });
            </script>

            <script>
                // Return to Work Modal
                document.addEventListener('DOMContentLoaded', () => {
                    const apiSuspensionBase = "{{ url('/api/suspension') }}";
                    const returnModal = new bootstrap.Modal(document.getElementById('returnToWorkModal'));
                    const returnForm = document.getElementById('returnToWorkForm');
                    const returnError = document.getElementById('return-error');
                    const returnSuccess = document.getElementById('return-success');
                    const returnSuspensionId = document.getElementById('return_suspension_id');

                    // Open return to work modal function
                    window.completeSuspension = function (suspensionId) {
                        returnForm.reset();
                        returnError.classList.add('d-none');
                        returnSuccess.classList.add('d-none');
                        
                        if (!suspensionId) {
                            returnError.textContent = 'Invalid suspension id.';
                            returnError.classList.remove('d-none');
                            return;
                        }
                        
                        returnSuspensionId.value = suspensionId;
                        
                        // Fetch suspension data to show employee info
                        fetchSuspensionForReturn(suspensionId);
                        
                        returnModal.show();
                    };

                    async function fetchSuspensionForReturn(suspensionId) {
                        try {
                            const res = await fetch(`${apiSuspensionBase}/${suspensionId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                credentials: 'same-origin'
                            });

                            if (!res.ok) {
                                throw new Error(`Failed to fetch suspension details (${res.status})`);
                            }

                            const data = await res.json();
                            
                            if (data.status === 'success' && data.suspension) {
                                const suspension = data.suspension;
                                
                                // Populate employee info (read-only)
                                document.getElementById('return_employee_info').textContent = 
                                    `${suspension.employee_name || 'N/A'} (${suspension.employee_id || 'N/A'})`;
                            } else {
                                throw new Error(data.message || 'Failed to load suspension details.');
                            }
                        } catch (err) {
                            returnError.textContent = err.message || 'Error loading suspension details.';
                            returnError.classList.remove('d-none');
                        }
                    }

                    // Handle form submission
                    returnForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        returnError.classList.add('d-none');
                        returnSuccess.classList.add('d-none');

                        const id = returnSuspensionId.value;
                        
                        if (!id) {
                            returnError.textContent = 'Invalid suspension record.';
                            returnError.classList.remove('d-none');
                            return;
                        }

                        try {
                            const res = await fetch(`${apiSuspensionBase}/${id}/return`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                credentials: 'same-origin'
                            });

                            const text = await res.text();
                            let data;
                            try { 
                                data = JSON.parse(text); 
                            } catch (_) {
                                throw new Error('Unexpected server response.');
                            }

                            if (res.ok && data.status === 'success') {
                                returnSuccess.textContent = data.message || 'Employee successfully returned to work.';
                                returnSuccess.classList.remove('d-none');
                                setTimeout(() => {
                                    returnModal.hide();
                                    location.reload();
                                }, 1500);
                            } else {
                                throw new Error(data.message || `Server error (${res.status}).`);
                            }
                        } catch (err) {
                            returnError.textContent = err.message || 'Error marking return to work.';
                            returnError.classList.remove('d-none');
                        }
                    });
                });
            </script>




@endsection