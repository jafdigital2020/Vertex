<?php $page = 'suspension'; ?>
@extends('layout.mainlayout')
@section('content')

        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content">

                <!-- Breadcrumb -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Admin Suspension</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                                </li>
                                <li class="breadcrumb-item">Suspension</li>
                                <li class="breadcrumb-item active" aria-current="page">Admin Suspension</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                        <div class="mb-2">
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#fileSuspensionModal">
                                    <i class="ti ti-file-plus"></i> File Suspension
                                </button>
                        </div>
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
                                    <div class="form-group me-2" style="max-width:200px;">
                                        <select name="branch_filter" id="branch_filter" class="select2 form-select" style="width:150px;"  oninput="filter()">
                                            <option value="" selected>All Branches</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group me-2">
                                        <select name="department_filter" id="department_filter" class="select2 form-select" style="width:150px;"
                                            oninput="filter()">
                                            <option value="" selected>All Departments</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group me-2">
                                        <select name="designation_filter" id="designation_filter" class="select2 form-select" style="width:150px;"
                                            oninput="filter()">
                                            <option value="" selected>All Designations</option>
                                            @foreach ($designations as $designation)
                                                <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group me-2"> 
                                    <select id="suspension-status" class="form-select select2" style="width:150px;" oninput="filter()">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="implemented">Implemented</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                    </div>

                                    <!-- File Suspension Button -->
                                
                                </div>
                            </div>

                            <div class="card-body p-3">  
                                <div class="table-responsive" id="suspension-table-wrap">
                                    <table class="table datatable table-striped align-middle" id="suspension-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th >Name</th> 
                                                <th class="text-center">Branch</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Designation</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Type</th>
                                                <th class="text-center">Start Date</th>
                                                <th class="text-center">End Date</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="suspension-tbody">
                                            @php
                                                function getStatusColor($status) {
                                                    switch ($status) {
                                                        case 'pending': 
                                                            return 'warning';
                                                        case 'implemented': 
                                                            return 'info';
                                                        case 'completed': 
                                                            return 'success';
                                                        default: 
                                                            return 'secondary';
                                                    }
                                                }
                                            @endphp
                                               @foreach ($suspension as $idx => $sus) 
                                                    <tr>    
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td>{{  $sus->employee->personalInformation->first_name ?? '' }}  {{   $sus->employee->personalInformation->last_name ?? '' }}</td> 
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->employee_id ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->department->department_name ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->designation->designation_name ?? '' }}</td>
                                                        <td class="text-center">
                                                            @if($sus->status === 'suspended' && $sus->suspension_start_date === null && $sus->suspension_end_date === null)
                                                                <span class="badge bg-secondary">
                                                                    For Suspension
                                                                </span>
                                                            @else
                                                                <span class="badge bg-{{ getStatusColor($sus->status) }}">
                                                                    {{ $sus->status ?? '' }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $sus->suspension_type ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->suspension_start_date ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->suspension_end_date ?? '' }}</td>  
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center align-items-center gap-1 flex-nowrap">

                                                            <button class="btn btn-sm btn-primary edit-suspension"
                                                                data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                title="Edit Suspension">
                                                                <i class="ti ti-edit"></i>
                                                            </button>

                                                            @if ($sus->status === 'pending')
                                                                <button class="btn btn-sm btn-warning issue-nowe"
                                                                    data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                    title="Issue NOWE">
                                                                    <i class="ti ti-mail"></i>
                                                                </button>
                                                            @else
                                                                <button class="btn btn-sm btn-secondary view-suspension"
                                                                    data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                    title="View Suspension Details">
                                                                    <i class="ti ti-eye"></i>
                                                                </button>
                                                            @endif

                                                            @switch($sus->status)  
                                                                @case('under_investigation')
                                                                    <button class="btn btn-sm btn-info"
                                                                        onclick="openInvestigationModal({{ $sus->id ?? $sus->employee->id }})"
                                                                        title="Upload Investigation Report">
                                                                        <i class="ti ti-upload"></i>
                                                                    </button>
                                                                    @break

                                                                @case('for_dam_issuance')
                                                                    @if (!$sus->dam_file)
                                                                        <button class="btn btn-sm btn-success"
                                                                            onclick="openDamModal({{ $sus->id ?? $sus->employee->id }})"
                                                                            title="Issue DAM">
                                                                            <i class="ti ti-file-check"></i>
                                                                        </button>
                                                                    @endif
                                                                    @break

                                                                @case('suspended')
                                                                    @if (!$sus->dam_file)
                                                                        <button class="btn btn-sm btn-success"
                                                                            onclick="openDamModal({{ $sus->id ?? $sus->employee->id }})"
                                                                            title="Issue DAM">
                                                                            <i class="ti ti-file-check"></i>
                                                                        </button>
                                                                    @endif
                                                                    @if($sus->suspension_start_date === null && $sus->suspension_end_date === null)
                                                                    <button class="btn btn-sm btn-danger"
                                                                        onclick="openSuspendModal({{ $sus->id ?? $sus->employee->id }})"
                                                                        title="Implement Suspension">
                                                                        <i class="ti ti-ban"></i>
                                                                    </button>
                                                                    @endif
                                                                    @if($sus->suspension_start_date !== null && $sus->suspension_end_date !== null)
                                                                    <button class="btn btn-sm btn-secondary"
                                                                        onclick="completeSuspension({{ $sus->id ?? $sus->employee->id }})"
                                                                        title="Complete Suspension">
                                                                        <i class="ti ti-check"></i>
                                                                    </button>
                                                                    @endif
                                                                    @break  
                                                            @endswitch

                                                        </div>
                                                    </td>  
                                                    </tr> 
                                                @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @component('components.modal-popup') @endcomponent 
            
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
                                    Select Branch(es) <span class="text-danger">*</span>
                                </label>
                                <select id="branch" class="form-select select2" multiple required style="width: 100%;">
                                    <option value="all">All Branches</option>
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
                                    Select Employee <span class="text-danger">*</span>
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
                                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::now()->toDateString() }}" readonly> 
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


            @push('scripts')
            <!-- Scripts -->
            <script>
                 
                function filter() { 
                    const branch = $('#branch_filter').val();
                    const department = $('#department_filter').val();
                    const designation = $('#designation_filter').val();
                    const status = $('#suspension-status').val(); 
                    $.ajax({
                            url: '{{ route('suspension-admin-filter') }}',
                            type: 'GET',
                            data: {
                                branch,
                                department,
                                designation, 
                                status,
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    $('#suspension-table').DataTable().destroy();
                                    $('#suspension-tbody').html(response.html);
                                    $('#suspension-table').DataTable();
                                    
                                } else {
                                    toastr.error(response.message || 'Something went wrong.');
                                }
                            },
                            error: function(xhr) {
                                let message = 'An unexpected error occurred.';
                                if (xhr.status === 403) {
                                    message = 'You are not authorized to perform this action.';
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                toastr.error(message);
                            }
                        });
                    }
            </script>
            <script>
                
                $('#branch').on('change', function () {
                    let branchIds = $(this).val();  

                    $.ajax({
                        url: "{{ route('suspension.employees-by-branch') }}",
                        type: "GET",
                        data: {
                            branch_id: Array.isArray(branchIds) ? branchIds.join(',') : branchIds
                        },
                        beforeSend: function () {
                            $('#employee').html('<option>Loading...</option>');
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                $('#employee').empty();
                                $('#employee').append('<option value="">Select employee</option>'); 
                                response.employees.forEach(function (emp) {
                                    $('#employee').append(
                                        `<option value="${emp.id}">
                                            ${emp.name} (${emp.employee_id ?? ''})
                                        </option>`
                                    );
                                });

                                $('#employee').trigger('change'); 
                            }
                        }
                    });
                });


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
                                toastr.success('Suspension filed successfully','Success');
                                fileForm.reset(); 
                                document.querySelector('#fileSuspensionModal .btn-close').click();
                                filter(); 
                            } else {
                                   toastr.error(data.message || 'Something went wrong.','Error');
                            }
                        } catch (err) {
                             toastr.error(err.message || 'Something went wrong.','Error');
                        }
                    });
                }); 
                    // NOWE

                    // ✅ Issue NOWE modal + submission logic
                      $(document).ready(function () {

                        const issueNoweModal =  $('#issueNoweModal');
                        const noweForm = $('#issueNoweForm');
                        const noweError = $('#nowe-error');
                        const noweSuccess = $('#nowe-success');
                        const noweFile = $('#nowe_file');
                        const noweSuspensionId = $('#nowe_suspension_id');
   
                        $(document).on('click', '.issue-nowe', function (e) { 
                                e.preventDefault(); 
                                const $btn = $(this);
                                const suspensionId = $btn.data('id'); 
                                $(noweSuspensionId).val(suspensionId);
                                issueNoweModal.modal('show');
                                
                        });
 
                        noweForm.on('submit', function (e) {
                            e.preventDefault();
  
                            const suspensionId = noweSuspensionId.val();
                            if (!suspensionId) { 
                                toast.error('Undefined suspension id','Error');
                                return;
                            }

                            if (!noweFile[0].files || noweFile[0].files.length === 0) { 
                                toast.error('Please select a NOWE file to upload.','Error');
                                return;
                            }

                            const file = noweFile[0].files[0];
                            const MAX_SIZE = 2 * 1024 * 1024;

                            if (file.size > MAX_SIZE) {
                                toast.error('File is too large. Maximum allowed size is 2MB.','Error'); 
                                return;
                            }

                            const formData = new FormData();
                            formData.append('nowe_file', file);

                            $.ajax({
                                url: `{{ url('/api/suspension') }}/${suspensionId}/issue-nowe`,
                                type: "POST",
                                data: formData,
                                processData: false,
                                contentType: false,
                                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },

                                success: function (data) {
                                    if (data.status === "success") { 
                                        toastr.success('NOWE issued successfully','Success');
                                        issueNoweModal.hide(); 
                                        $('body').removeClass('modal-open');
                                        $('.modal-backdrop').remove();
                                        filter(); 
                                    } else {
                                       toastr.error('Something went wrong','Error'); 
                                    }
                                },

                                error: function (xhr) {
                                    let msg = "An error occurred while issuing NOWE.";

                                    if (xhr.responseText) {
                                        try {
                                            const json = JSON.parse(xhr.responseText);
                                            msg = json.message || msg;
                                        } catch (e) {}
                                      }
                                         toastr.error(msg,'Error'); 
                                }
                            });  
                        }); 
                    });  
            </script> 
            <script>
                $(document).ready(function () {
                    const apiSuspensionBase = "{{ url('/api/suspension') }}"; 
                    const $investigationModal = $('#InvestigationReportModal');
                    const investigationModal = new bootstrap.Modal($investigationModal[0]);
                    const $investigationForm = $('#investigationForm');
                    const $investigationError = $('#investigation-error');
                    const $investigationSuccess = $('#investigation-success');
                    const $investigationSuspensionId = $('#investigation_suspension_id');
 
                    window.openInvestigationModal = function (suspensionId) {
                        $investigationForm[0].reset();
                        $investigationError.addClass('d-none');
                        $investigationSuccess.addClass('d-none');

                        if (!suspensionId) {
                            $investigationError.text('Invalid suspension id.').removeClass('d-none');
                            return;
                        }

                        $investigationSuspensionId.val(suspensionId);
                        investigationModal.show();
                    };
 
                    $investigationForm.on('submit', function (e) {
                        e.preventDefault();
                        $investigationError.addClass('d-none');
                        $investigationSuccess.addClass('d-none');

                        const id = $investigationSuspensionId.val();
                        const notes = $('#investigation_notes').val()?.trim() || '';

                        if (!id) {
                            $investigationError.text('Invalid suspension record.').removeClass('d-none');
                            return;
                        }

                        if (!notes) {
                            $investigationError.text('Please provide investigation notes.').removeClass('d-none');
                            return;
                        }

                        if (notes.length > 2000) {
                            $investigationError.text('Investigation notes must not exceed 2000 characters.').removeClass('d-none');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('investigation_notes', notes);

                        $.ajax({
                            url: `${apiSuspensionBase}/${id}/investigate`,
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status === 'success') {
                                    toastr.success(data.message || 'Investigation recorded successfully.','Success'); 
                                    investigationModal.hide();
                                    filter(); 
                                } else {
                                    toastr.error(data.message || 'Error submitting investigation.','Error');
                                }
                            },
                            error: function (xhr) {
                                toastr.error(xhr.responseJSON?.message || `Error (${xhr.status}) submitting investigation.`,'Error');
                            }
                        });
                    });
 
                    const $damModal = $('#IssueDamModal');
                    const damModal = new bootstrap.Modal($damModal[0]);
                    const $damForm = $('#issueDamForm');
                    const $damError = $('#dam-error');
                    const $damSuccess = $('#dam-success');
                    const $damSuspensionId = $('#dam_suspension_id');
 
                    window.openDamModal = function (suspensionId) {
                        $damForm[0].reset();
                        $damError.addClass('d-none');
                        $damSuccess.addClass('d-none');

                        if (!suspensionId) {
                            $damError.text('Invalid suspension id.').removeClass('d-none');
                            return;
                        }

                        $damSuspensionId.val(suspensionId);
                        damModal.show();
                    };
 
                    $damForm.on('submit', function (e) {
                        e.preventDefault();
                        $damError.addClass('d-none');
                        $damSuccess.addClass('d-none');

                        const id = $damSuspensionId.val();
                        const file = $('#dam_file')[0].files[0];
                        const suspensionType = $('input[name="suspension_type"]:checked').val();

                        if (!id) { $damError.text('Invalid suspension record.').removeClass('d-none'); return; }
                        if (!file) { $damError.text('Please upload a DAM file.').removeClass('d-none'); return; }
                        if (!suspensionType) { $damError.text('Please select suspension type.').removeClass('d-none'); return; }
                        if (file.size > 2 * 1024 * 1024) { $damError.text('File exceeds 2MB limit.').removeClass('d-none'); return; }

                        const formData = new FormData();
                        formData.append('dam_file', file);
                        formData.append('suspension_type', suspensionType);

                        const submitDam = (url) => $.ajax({
                            url: url,
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            data: formData,
                            processData: false,
                            contentType: false,
                        });

                        // Try primary submission
                        submitDam(`${apiSuspensionBase}/${id}/issue-dam`)
                        .done(function (data) {
                            if (data.status === 'success') {
                                 toastr.success(data.message || 'DAM issued successfully.','Success');
                                 damModal.hide();  
                                 filter();
                            } else {
                                 toastr.error(data.message || 'Error issuing DAM.','Error');
                            }
                        })
                        .fail(function (xhr) {
                            if (xhr.status === 404 || /not found/i.test(xhr.responseJSON?.message || '')) {
                                // Fallback: lookup suspension by employee id
                                $.getJSON(`${apiSuspensionBase}?employee_id=${encodeURIComponent(id)}&status=for_dam_issuance`)
                                .done(function (lookupData) {
                                    const first = (lookupData.suspensions || lookupData.data || lookupData)[0];
                                    if (!first?.id) { $damError.text('Suspension case not found for this employee.').removeClass('d-none'); return; }
                                    // Retry DAM submission
                                    submitDam(`${apiSuspensionBase}/${first.id}/issue-dam`).done(function (retryData) {
                                        if (retryData.status === 'success') {
                                            toastr.success(retryData.message || 'DAM issued successfully.','Success');
                                            damModal.hide(); 
                                            filter();

                                        } else {
                                            toastr.error(retryData.message || 'Error issuing DAM.','Error');
                                        }
                                    }).fail(function () {
                                        toastr.error('Error issuing DAM on retry.','Error');
                                    });
                                })
                                .fail(function () {
                                    toastr.error('No suspension found for given employee.','Error');
                                });
                            } else {
                                toastr.error(xhr.responseJSON?.message || `Error (${xhr.status}) issuing DAM.`,'Error');
                            }
                        });
                    });
                });

            </script>

            <script>
               // View Suspension Info Modal
                $(document).ready(function () {
                    const apiSuspensionBase = "{{ url('/api/suspension') }}";
                    const viewModal = $('#viewSuspensionModal');
                    const $viewLoading = $('#view-suspension-loading');
                    const $viewError = $('#view-suspension-error');
                    const $viewContent = $('#view-suspension-content');
    
 
                    $(document).on('click', '.view-suspension', function (e) { 
                        e.preventDefault(); 
                        const $btn = $(this);
                        const suspensionId = $btn.data('id');  
                        fetchSuspensionDetails(suspensionId);
                        viewModal.modal('show');
                            
                    }); 

                    function fetchSuspensionDetails(suspensionId) {
                        $.ajax({
                            url: `${apiSuspensionBase}/${suspensionId}`,
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (data) {
                                if (data.status === 'success' && data.suspension) {
                                    displaySuspensionDetails(data.suspension);
                                } else { 
                                    toastr.error('Failed to load suspension details','Error');
                                }
                            },
                            error: function (xhr, status, error) { 
                                toastr.error(`Error fetching suspension details (${xhr.status}): ${error}`,'Error');
                            }
                        });
                    } 

                    function displaySuspensionDetails(suspension) {
                        // Employee Information
                        $('#view_employee_name').text(suspension.employee_name || 'N/A');
                        $('#view_employee_id').text(suspension.employee_id || 'N/A');
                        $('#view_branch').text(suspension.branch || 'N/A');
                        $('#view_department').text(suspension.department || 'N/A');
                        $('#view_designation').text(suspension.designation || 'N/A');

                        // Suspension Information
                        const statusBadge = $('#view_status');
                        const status = suspension.status || 'N/A';
                        statusBadge.text(status).attr('class', 'badge bg-' + getStatusColor(status));

                        $('#view_type').text(suspension.suspension_type ? suspension.suspension_type.replace('_', ' ').toUpperCase() : 'N/A');
                        $('#view_filed_date').text(suspension.created_at ? new Date(suspension.created_at).toLocaleDateString() : 'N/A');
                        $('#view_start_date').text(suspension.suspension_start_date || 'N/A');
                        $('#view_end_date').text(suspension.suspension_end_date || 'N/A');
                        $('#view_duration').text(suspension.suspension_days ? `${suspension.suspension_days} day(s)` : 'N/A');

                        // Offense Details
                        $('#view_offense_details').text(suspension.offense_details || 'No details provided.'); 
                        // Investigation Notes
                        if (suspension.investigation_notes) {
                            $('#view_investigation_notes').text(suspension.investigation_notes);
                            $('#view_investigation_card').show();
                        } else {
                            $('#view_investigation_card').hide();
                        }

                        // Implementation Remarks
                        if (suspension.implementation_remarks) {
                            $('#view_implementation_remarks').text(suspension.implementation_remarks);
                            $('#view_implementation_card').show();
                        } else {
                            $('#view_implementation_card').hide();
                        }

                        // Employee Reply
                        if (suspension.employee_reply) {
                            const reply = suspension.employee_reply;
                            $('#view_employee_reply_text').text(reply.description || 'No reply text provided.');
                            $('#view_employee_reply_date').text(reply.action_date || 'N/A');

                            if (reply.file_path) {
                                $('#view_employee_reply_file_link').attr('href', `/storage/${reply.file_path}`);
                                $('#view_employee_reply_file').show();
                            } else {
                                $('#view_employee_reply_file').hide();
                            }

                            $('#view_employee_reply_card').show();
                        } else {
                            $('#view_employee_reply_card').hide();
                        }

                        // Attachments
                        const attachments = [];
                        if (suspension.information_report_file) attachments.push({ name: 'Information Report', url: suspension.information_report_file });
                        if (suspension.nowe_file) attachments.push({ name: 'NOWE Document', url: suspension.nowe_file });
                        if (suspension.dam_file) attachments.push({ name: 'DAM Document', url: suspension.dam_file });

                        const $attachmentsList = $('#view_attachments_list');
                        $attachmentsList.empty();

                        if (attachments.length > 0) {
                            attachments.forEach(att => {
                                const link = `<a href="/storage/${att.url}" target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-2">
                                                <i class="ti ti-download me-1"></i>${att.name}
                                            </a>`;
                                $attachmentsList.append(link);
                            });
                            $('#view_attachments_card').show();
                        } else {
                            $('#view_attachments_card').hide();
                        }

                        // Show content, hide loading
                        $viewLoading.addClass('d-none');
                        $viewContent.removeClass('d-none');
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
                                toastr.success(data.message || 'Suspension implemented successfully.','Success'); 
                                implementModal.hide();
                                filter();
                            } else {
                                throw new Error(data.message || `Server error (${res.status}).`);
                            }
                        } catch (err) {
                            toastr.error(err.message || 'Error implementing suspension.','Error');
                            
                        }
                    });
                });
            </script>
            <script>
                $(document).ready(function() {
                    const apiSuspensionBase = "{{ url('/api/suspension') }}";

                    const $editModal = $('#editSuspensionModal');
                    const $editForm = $('#editSuspensionForm');
                    const $editError = $('#edit-suspension-error');
                    const $editSuccess = $('#edit-suspension-success');
                    const $editSuspensionId = $('#edit_suspension_id');
                    const $currentFileInfo = $('#current_file_info');
 
                    window.openEditSuspensionModal = function(suspensionId) {
                        $editForm[0].reset();
                        $editError.addClass('d-none').text('');
                        $editSuccess.addClass('d-none').text('');
                        $currentFileInfo.html('');

                        if (!suspensionId) {
                            $editError.text('Invalid suspension id.').removeClass('d-none');
                            return;
                        }

                        $editSuspensionId.val(suspensionId);
 
                        fetchAndPopulateSuspension(suspensionId);

                        $editModal.modal('show');
                    };

                    function fetchAndPopulateSuspension(suspensionId) {
                        $.ajax({
                            url: `${apiSuspensionBase}/${suspensionId}`,
                            method: 'GET',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            dataType: 'json',
                            success: function(data) {
                                if (data.status === 'success' && data.suspension) {
                                    const suspension = data.suspension;

                                    $('#edit_employee_info').text(`${suspension.employee_name || 'N/A'} (${suspension.employee_id || 'N/A'})`);
                                    $('#edit_offense_details').val(suspension.offense_details || '');
                                    $('#edit_disciplinary_action').val(suspension.disciplinary_action || '');
                                    $('#edit_remarks').val(suspension.remarks || '');

                                    if (suspension.information_report_file) {
                                        $currentFileInfo.html(
                                            `<small class="text-info">
                                                <i class="ti ti-file-check"></i> Current file: 
                                                <a href="/storage/${suspension.information_report_file}" target="_blank">View Document</a>
                                            </small>`
                                        );
                                    }
                                } else {
                                   toastr.error('Failed to load suspension details','Error');
                                }
                            },
                            error: function(xhr, status, error) {
                                  toastr.error('Error loading suspension details' + error ,'Error');
                            }
                        });
                    }
 
                    $editForm.on('submit', function(e) {
                        e.preventDefault();
                        $editError.addClass('d-none').text('');
                        $editSuccess.addClass('d-none').text('');

                        const id = $editSuspensionId.val();
                        const offenseDetails = $('#edit_offense_details').val().trim();
                        const disciplinaryAction = $('#edit_disciplinary_action').val().trim();
                        const remarks = $('#edit_remarks').val().trim();
                        const fileInput = $('#edit_information_report_file')[0];

                        if (!id) {
                            $editError.text('Invalid suspension record.').removeClass('d-none');
                            return;
                        }

                        if (!offenseDetails) {
                            $editError.text('Offense details is required.').removeClass('d-none');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('_method', 'PUT');
                        formData.append('offense_details', offenseDetails);
                        if (disciplinaryAction) formData.append('disciplinary_action', disciplinaryAction);
                        if (remarks) formData.append('remarks', remarks);
                        if (fileInput.files.length > 0) formData.append('information_report_file', fileInput.files[0]);

                        $.ajax({
                            url: `${apiSuspensionBase}/${id}`,
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(data) {
                                if (data.status === 'success') {
                                   toastr.success('Suspension updated successfully','Success');
                                   filter();
                                   $editModal.modal('hide'); 
                                } else {
                                    toastr.error('Error updating suspension','Error');
                                }
                            },
                            error: function(xhr, status, error) {
                               toastr.error('Error updating suspension' + error ,'Error');
                            }
                        });
                    });
 
                    $(document).on('click', '.edit-suspension', function() {
                        const suspensionId = $(this).data('id');
                        openEditSuspensionModal(suspensionId);
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
                                toastr.success('Employee successfully returned to work.','Success');
                                filter();
                                $('#returnToWorkModal').modal('hide');
                            } else {
                                toastr.error(data.message,'Error');
                                throw new Error(data.message || `Server error (${res.status}).`);
                            }
                        } catch (err) {
                            toastr.error(err.message,'Error'); 
                        }
                    });
                });
                        function populateDropdown($select, items, placeholder = 'Select') {
            $select.empty();
            $select.append(`<option value="">All ${placeholder}</option>`);
            items.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $(document).ready(function() {

            $('#branch_filter').on('input', function() {
                const branchId = $(this).val();

                $.get('/api/filter-from-branch', {
                    branch_id: branchId
                }, function(res) {
                    if (res.status === 'success') {
                        populateDropdown($('#department_filter'), res.departments, 'Departments');
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });


            $('#department_filter').on('input', function() {
                const departmentId = $(this).val();
                const branchId = $('#branch_filter').val();

                $.get('/api/filter-from-department', {
                    department_id: departmentId,
                    branch_id: branchId,
                }, function(res) {
                    if (res.status === 'success') {
                        if (res.branch_id) {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                        }
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });

            $('#designation_filter').on('change', function() {
                const designationId = $(this).val();
                const branchId = $('#branch_filter').val();
                const departmentId = $('#department_filter').val();

                $.get('/api/filter-from-designation', {
                    designation_id: designationId,
                    branch_id: branchId,
                    department_id: departmentId
                }, function(res) {
                    if (res.status === 'success') {
                        if (designationId === '') {
                            populateDropdown($('#designation_filter'), res.designations,
                                'Designations');
                        } else {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                            $('#department_filter').val(res.department_id).trigger('change');
                        }
                    }
                });
            });

        });
            </script>


        @endpush

@endsection