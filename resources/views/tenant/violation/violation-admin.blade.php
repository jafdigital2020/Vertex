<?php $page = 'violation'; ?>
@extends('layout.mainlayout')
@section('content')

        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content">

                <!-- Breadcrumb -->
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Admin Violation</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                                </li>
                                <li class="breadcrumb-item">Violation</li>
                                <li class="breadcrumb-item active" aria-current="page">Admin Violation</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                        <div class="mb-2">
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#fileViolationModal">
                                    <i class="ti ti-file-plus"></i> File Violation
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

                <!-- Violation List -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                                <h5 class="d-flex align-items-center mb-0">Violation List</h5>

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
                                    <div class="form-group me-2" style="max-width:200px;"> 
                                        <select id="violation-status" class="form-select select2" style="max-width:200px;" oninput="filter()">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="awaiting_reply">Awaiting Reply</option>
                                            <option value="under_investigation">Under Investigation</option>
                                            <option value="for_dam_issuance">For DAM Issuance</option>
                                            <option value="implemented">Implemented</option> 
                                        </select>
                                    </div>
                                    <div class="form-group me-2" style="max-width:200px;"> 
                                        <select id="violation-type-filter" class="form-select select2" style="max-width:200px;" oninput="filter()">
                                            <option value="">All Violation Types</option>
                                            @foreach($violationTypes as $vT)
                                                <option value="{{$vT->id}}">{{$vT->name}}</option> 
                                            @endforeach
                                        </select>
                                    </div>  
                                </div>
                            </div>

                            <div class="card-body p-3">  
                                <div class="table-responsive" id="violation-table-wrap">
                                    <table class="table datatable table-striped align-middle" id="violation-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th >Name</th> 
                                                <th class="text-center">Branch</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Designation</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Type</th>
                                                <th class="text-center">Reprimand Date</th> 
                                                <th class="text-center">Suspension Start Date</th>
                                                <th class="text-center">Suspension End Date</th>
                                                <th class="text-center">Termination Date</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="violation-tbody">
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

                                            
                                               @foreach ($violation as $idx => $sus) 
                                               
                                                    <tr>    
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td>{{  $sus->employee->personalInformation->first_name ?? '' }}  {{   $sus->employee->personalInformation->last_name ?? '' }}</td> 
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->employee_id ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->department->department_name ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->designation->designation_name ?? '' }}</td>
                                                        <td class="text-center">
                                                            @if($sus->status === 'suspended' && $sus->violation_start_date === null && $sus->violation_end_date === null)
                                                                <span class="badge bg-secondary">
                                                                    For Violation
                                                                </span>
                                                            @else
                                                                <span class="badge bg-{{ getStatusColor($sus->status) }}">
                                                                    {{ $sus->status ?? '' }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $sus->violationType->name ?? '-' }}</td>
                                                        <td class="text-center">
                                                            @if($sus->verbal_reprimand_date)
                                                                {{ $sus->verbal_reprimand_date }}
                                                            @elseif($sus->written_reprimand_date)
                                                                {{ $sus->written_reprimand_date }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td> 
                                                        <td class="text-center">{{ $sus->suspension_start_date ?? '-' }}</td>
                                                        <td class="text-center">{{ $sus->suspension_end_date ?? '-' }}</td>  
                                                        <td class="text-center">{{ $sus->termination_date ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center align-items-center gap-1 flex-nowrap"> 
                                                            <button class="btn btn-sm btn-secondary view-violation"
                                                                data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                title="View Violation Details">
                                                                <i class="ti ti-eye"></i>
                                                            </button>

                                                            <button class="btn btn-sm btn-primary edit-violation"
                                                                data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                title="Edit Violation">
                                                                <i class="ti ti-edit"></i>
                                                            </button>

                                                            @if ($sus->status === 'pending')
                                                                <button class="btn btn-sm btn-warning issue-nowe"
                                                                    data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                    title="Issue NOWE">
                                                                    <i class="ti ti-mail"></i>
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

                                                                    @case('dam_issued')
                                                                        @if (!$sus->dam_file)
                                                                            <button class="btn btn-sm btn-success"
                                                                                onclick="openDamModal({{ $sus->id ?? $sus->employee->id }})"
                                                                                title="Issue DAM">
                                                                                <i class="ti ti-file-check"></i>
                                                                            </button>
                                                                        @endif
                                                                        <button class="btn btn-sm btn-danger"
                                                                            onclick="openViolationModal(
                                                                                {{ $sus->id ?? $sus->employee->id }},
                                                                                '{{ addslashes($sus->violationType->name) }}'
                                                                            )"
                                                                            title="Implement Violation">
                                                                            <i class="ti ti-ban"></i>
                                                                        </button>
                                                                        
                                                                        <!-- @if($sus->violation_start_date === null && $sus->violation_end_date === null)
                                                                        <button class="btn btn-sm btn-danger"
                                                                            onclick="openSuspendModal({{ $sus->id ?? $sus->employee->id }})"
                                                                            title="Implement Violation">
                                                                            <i class="ti ti-ban"></i>
                                                                        </button>
                                                                        @endif
                                                                        @if($sus->violation_start_date !== null && $sus->violation_end_date !== null)
                                                                        <button class="btn btn-sm btn-secondary"
                                                                            onclick="completeViolation({{ $sus->id ?? $sus->employee->id }})"
                                                                            title="Complete Violation">
                                                                            <i class="ti ti-check"></i>
                                                                        </button>
                                                                        @endif -->
                                                                    @break  

                                                                    @case('implemented')
                                                                        @if($sus->termination_date !== null && $sus->last_pay_status !== 1 )
                                                                        <button class="btn btn-sm btn-primary"
                                                                            onclick="processLastPay({{ $sus->employee->id }} , {{$sus->id}})"
                                                                            title="Process Last Pay">
                                                                            <i class="ti ti-receipt"></i>
                                                                        </button> 
                                                                        @endif
                                                                       @if($sus->termination_date !== null && $sus->last_pay_status === 1 )
                                                                        <button class="btn btn-sm btn-primary" onclick="viewEditLastPay({{$sus->last_payroll_id}})" title="View/Edit Last Pay"><i class="ti ti-report-money"></i></button>
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
            
            <!-- File Violation Report Modal -->
            <div class="modal fade" id="fileViolationModal" tabindex="-1" aria-labelledby="fileViolationModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fileViolationModalLabel">File Violation Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                    <form id="fileViolationForm" enctype="multipart/form-data">
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
                            <label for="violation_attachments" class="form-label">
                                Attachments (Optional)
                                <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="You can upload multiple files at once or add more files"></i>
                            </label>
                            <input type="file" name="attachments[]" id="violation_attachments" class="form-control"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                            <small class="text-muted d-block mt-1">
                                <i class="ti ti-paperclip me-1"></i>Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB per file)
                            </small>
                            <small class="text-info d-block">
                                <i class="ti ti-plus me-1"></i>You can select multiple files at once. Click "Choose Files" again to add more.
                            </small>
                            <div id="attachment-preview" class="mt-3"></div>
                        </div>                            <div id="file-violation-error" class="alert alert-danger d-none"></div>
                            <div id="file-violation-success" class="alert alert-success d-none"></div>
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
                                <input type="hidden" id="nowe_violation_id" name="violation_id"> 
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
                                <input type="hidden" id="investigation_violation_id" name="violation_id">

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
                                <input type="hidden" id="dam_violation_id" name="violation_id"> 
                                <div class="mb-3">
                                    <label for="dam_file" class="form-label">Upload DAM File <span class="text-danger">*</span></label>
                                    <input type="file" name="dam_file" id="dam_file" class="form-control" accept=".pdf,.doc,.docx"
                                        required>
                                    <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max 2MB)</small>
                                </div>

                                <div class="mb-3 mt-2">
                                   <label class="form-label">Violation Type <span class="text-danger">*</span></label> 
                                    <div class="row">
                                        @foreach ($violationTypes as $item)
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">

                                                    <input class="form-check-input" 
                                                        type="radio" 
                                                        name="violation_type_id" 
                                                        value="{{ $item->id }}" 
                                                        id="violation_{{ $item->id }}" 
                                                        required> 
                                                    <label class="form-check-label" 
                                                        for="violation_{{ $item->id }}" 
                                                        style="font-size:14px;"> 
                                                        <strong>{{ $item->name }}</strong> 
                                                        @if($item->description)
                                                            <small class="d-block text-muted">{{ $item->description }}</small>
                                                        @endif
                                                    </label>

                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    
                                    {{-- <div class="form-check">
                                        <input class="form-check-input" type="radio" name="violation_type" id="violation_with_pay" value="with_pay" required>
                                        <label class="form-check-label" for="violation_with_pay">
                                            <strong>With Pay</strong>
                                            <small class="d-block text-muted">Employee will receive salary during violation</small>
                                        </label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="radio" name="violation_type" id="violation_without_pay" value="without_pay" required>
                                        <label class="form-check-label" for="violation_without_pay">
                                            <strong>Without Pay</strong>
                                            <small class="d-block text-muted">Employee will NOT receive salary during violation</small>
                                        </label>
                                    </div> --}}
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

            <!-- Implement Violation Modal -->
            <div class="modal fade" id="implementViolationModal" tabindex="-1" aria-labelledby="implementViolationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="implementViolationModalLabel">Implement Violation: <span id="violationTypeName"></span> </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="implementViolationForm">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="implement_violation_id" name="violation_id">
                                <div id="violation-verbal" style="display:none;">
                                    <div class="mb-3">
                                        <label for="verbal_reprimand_date" class="form-label">Verbal Reprimand Date</label>
                                        <input type="date" class="form-control" id="verbal_reprimand_date">
                                    </div>
                                    <div class="mb-3">
                                        <label for="verbal_reprimand_file" class="form-label">Verbal Reprimand File (optional)</label> 
                                        <input type="file" name="verbal_reprimand_file[]" id="verbal_reprimand_file" class="form-control"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple> 
                                    </div>
                                </div>
                                <div id="violation-written" style="display:none;">
                                     <div class="mb-3">
                                        <label for="written_reprimand_date" class="form-label">Written Reprimand Date (optional)</label>
                                        <input type="date" class="form-control" id="written_reprimand_date">
                                    </div>
                                    <div class="mb-3">
                                        <label for="written_reprimand_file" class="form-label">Written Reprimand File</label>
                                        <input type="file" name="written_reprimand_file[]" id="written_reprimand_file" class="form-control"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                                    </div>
                                    
                                </div>
                                <div id="violation-suspension" style="display:none;">
                                    <div class="mb-3">
                                        <label for="suspension_start_date" class="form-label">Suspension Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="suspension_start_date" id="suspension_start_date" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label for="suspension_end_date" class="form-label">Suspension End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="suspension_end_date" id="suspension_end_date" class="form-control">
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Note:</strong> This will implement suspension without pay. The employee's status will be updated accordingly.
                                    </div> 
                                </div> 
                                 <div id="violation-termination" style="display:none;">
                                    <div class="mb-3">
                                        <label for="termination_date" class="form-label">Termination Date <span class="text-danger">*</span></label>
                                        <input type="date" name="termination_date" id="termination_date" class="form-control">
                                    </div> 
                                </div> 
                                <div class="mb-3">
                                    <label for="implementation_remarks" class="form-label">Implementation Remarks (Optional)</label>
                                    <textarea name="implementation_remarks" id="implementation_remarks" class="form-control" rows="3" 
                                        placeholder="Enter any remarks..." maxlength="1000"></textarea>
                                    <small class="text-muted">Maximum 1000 characters</small>
                                </div> 
                                <div id="implement-violation-error" class="alert alert-danger d-none"></div>
                                <div id="implement-violation-success" class="alert alert-success d-none"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Implement Violation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Violation Info Modal -->
            <div class="modal fade" id="viewViolationModal" tabindex="-1" aria-labelledby="viewViolationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewViolationModalLabel">
                                <i class="ti ti-file-info me-2"></i>Violation Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div id="view-violation-loading" class="text-center py-4">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div id="view-violation-error" class="alert alert-danger d-none"></div>

                            <div id="view-violation-content" class="d-none">
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

                                <!-- Violation Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-alert-circle me-2"></i>Violation Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Filed Date:</strong> <span id="view_filed_date"></span></p> 
                                                <p class="mb-2"><strong>Type:</strong> <span id="view_type"></span></p>
                                                <p class="mb-2"><strong>Status:</strong> <span id="view_status" class="badge"></span></p>
                                               
                                            </div>
                                            <div class="col-md-6">
                                                <div id="view_verbal" style="display:none;">
                                                    <p class="mb-2"><strong>Verbal Reprimand Date:</strong> <span id="view_verbal_date"></span></p>
                                                </div>
                                                  <div id="view_written" style="display:none;">
                                                    <p class="mb-2"><strong>Written Reprimand Date:</strong> <span id="view_written_date"></span></p>
                                                </div>
                                                <div id="view_suspension" style="display:none;">
                                                    <p class="mb-2"><strong>Start Date:</strong> <span id="view_start_date"></span></p>
                                                    <p class="mb-2"><strong>End Date:</strong> <span id="view_end_date"></span></p>
                                                    <p class="mb-2"><strong>Duration:</strong> <span id="view_duration"></span></p> 
                                                </div> 
                                                <div id="view_termination" style="display:none;">
                                                    <p class="mb-2"><strong>Termination Date:</strong> <span id="view_termination_date"></span></p>
                                                </div>
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

            <!-- Edit Violation Modal -->
            <div class="modal fade" id="editViolationModal" tabindex="-1" aria-labelledby="editViolationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editViolationModalLabel">
                                <i class="ti ti-edit me-2"></i>Edit Violation
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="editViolationForm" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="edit_violation_id" name="violation_id">
 
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
                                <label for="edit_information_report_file" class="form-label">Current Attachments</label>
                                <div id="edit_current_attachments" class="border rounded p-3 bg-light">
                                    <p class="text-muted mb-0"><i>Loading attachments...</i></p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_new_attachments" class="form-label">
                                    Add More Attachments (Optional)
                                    <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="You can upload multiple files at once"></i>
                                </label>
                                <input type="file" name="attachments[]" id="edit_new_attachments" class="form-control"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                                <small class="text-muted d-block mt-1">
                                    <i class="ti ti-paperclip me-1"></i>Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB per file)
                                </small>
                                <div id="edit_attachment_preview" class="mt-2"></div>
                            </div>                                <div id="edit-violation-error" class="alert alert-danger d-none"></div>
                                <div id="edit-violation-success" class="alert alert-success d-none"></div>
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
                                <input type="hidden" id="return_violation_id" name="violation_id">

                                <!-- Employee Info (Read-only) -->
                                <div class="alert alert-info">
                                    <strong>Employee:</strong> <span id="return_employee_info"></span>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    <strong>Confirmation Required</strong>
                                    <p class="mb-0 mt-2">This action will mark the employee as returned to work and close the violation case. The violation status will be changed to "Completed".</p>
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
            @php
                $currentYear = date('Y');
                $currentMonth = date('n');
                $currentDate = date('Y-m-d');
            @endphp
                          
            <div class="modal fade" id="processLastPayModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="ti ti-receipt me-2"></i>Process Last Pay
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <form id="finalpayrollProcessForm"
                                class="row g-4"
                                method="POST"
                                >
                                @csrf
 
                                <input type="hidden" name="user_id[]" id="lastPayEmployeeId">
                                <input type="hidden" name="violation_id" id="violation_id">
 
                                <div class="col-xl-5">
                                    <div class="mb-3 row align-items-center">
                                        <label class="col-sm-4 col-form-label">Payroll Type</label>
                                        <div class="col-sm-8">
                                            <select class="form-select" name="payroll_type" id="payrollType" readonly>
                                                <option value="last_payroll" selected>Last Pay</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3 row align-items-center">
                                        <label class="col-sm-4 col-form-label">Year</label>
                                        <div class="col-sm-8">
                                            <select class="form-select" name="year" required>
                                                @for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++)
                                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                                        {{ $year }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3 row align-items-center">
                                        <label class="col-sm-4 col-form-label">Month</label>
                                        <div class="col-sm-8">
                                            <select class="form-select" name="month" required>
                                                @foreach (range(1, 12) as $month)
                                                    <option value="{{ $month }}" {{ $month == $currentMonth ? 'selected' : '' }}>
                                                        {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3 row align-items-center">
                                        <label class="col-sm-4 col-form-label">Start Date</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="start_date" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 row align-items-center">
                                        <label class="col-sm-4 col-form-label">End Date</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="end_date" required>
                                        </div>
                                    </div>
                                </div>
 
                                <div class="col-xl-5">
                                    <div class="mb-3 row align-items-center">
                                        <label class="col-sm-4 col-form-label">Transaction Date</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control"
                                                name="transaction_date"
                                                value="{{ $currentDate }}" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 row align-items-center">
                                        <label class="col-sm-4 col-form-label">Assignment Type</label>
                                        <div class="col-sm-8">
                                            <select name="assignment_type" class="form-select" required>
                                                <option value="">Select</option> 
                                                <option value="manual" selected>Manual</option>
                                            </select>
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-xl-2">
                                    <div class="mb-3">
                                        <label class="form-label">SSS</label>
                                        <div class="d-flex gap-2">
                                            <input type="radio" name="sss_option" value="yes" required> Yes
                                            <input type="radio" name="sss_option" value="no"> No
                                            <input type="radio" name="sss_option" value="full"> Full
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">PhilHealth</label>
                                        <div class="d-flex gap-2">
                                            <input type="radio" name="philhealth_option" value="yes" required> Yes
                                            <input type="radio" name="philhealth_option" value="no"> No
                                            <input type="radio" name="philhealth_option" value="full"> Full
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Pag-IBIG</label>
                                        <div class="d-flex gap-2">
                                            <input type="radio" name="pagibig_option" value="yes" required> Yes
                                            <input type="radio" name="pagibig_option" value="no"> No
                                            <input type="radio" name="pagibig_option" value="full"> Full
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-settings me-1"></i>
                                        Process Last Pay
                                    </button>   
                                </div>
                            </form> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="edit_payroll" tabindex="-1" aria-labelledby="payrollModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" style="max-width: 85%;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="payrollModalLabel">Last Pay Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editPayrollForm" enctype="multipart/form-data">
                                <!-- Payroll Details -->
                                <div class="row">
                                    <input type="hidden" id="payroll_id" name="payroll_id">
                                    <div class="col-md-3 mb-4">
                                        <label for="payroll_type" class="form-label">Payroll Type</label>
                                        <input type="text" class="form-control" id="payroll_type" name="payroll_type">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="payroll_period" class="form-label">Payroll Period</label>
                                        <input type="text" class="form-control" id="payroll_period" name="payroll_period">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="payroll_period_start" class="form-label">Payroll Period Start</label>
                                        <input type="date" class="form-control" id="payroll_period_start"
                                            name="payroll_period_start">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="payroll_period_end" class="form-label">Payroll Period End</label>
                                        <input type="date" class="form-control" id="payroll_period_end" name="payroll_period_end">
                                    </div>
                                </div>
                                <!-- Time Tracking Fields -->
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label for="total_worked_minutes" class="form-label">Worked Minutes</label>
                                        <input type="number" class="form-control" id="total_worked_minutes"
                                            name="total_worked_minutes">
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="total_late_minutes" class="form-label">Late Minutes</label>
                                        <input type="number" class="form-control" id="total_late_minutes" name="total_late_minutes">
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="total_undertime_minutes" class="form-label">Undertime Minutes</label>
                                        <input type="number" class="form-control" id="total_undertime_minutes"
                                            name="total_undertime_minutes">
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="total_overtime_minutes" class="form-label">Overtime Minutes</label>
                                        <input type="number" class="form-control" id="total_overtime_minutes"
                                            name="total_overtime_minutes">
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="total_night_differential_minutes" class="form-label">Night Differential
                                            Minutes</label>
                                        <input type="number" class="form-control" id="total_night_differential_minutes"
                                            name="total_night_differential_minutes">
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="total_overtime_night_differential_minutes" class="form-label">OT Night
                                            Differential Minutes</label>
                                        <input type="number" class="form-control" id="total_overtime_night_differential_minutes"
                                            name="total_overtime_night_differential_minutes">
                                    </div>
                                </div>
                                <!-- Pay Breakdown -->
                                <h4 class="mb-3 text-primary">Pay Breakdown</h4>
                                <div class="row">
                                    <div class="col-md-3 mb-4">
                                        <label for="holiday_pay" class="form-label">Holiday Pay</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="holiday_pay" name="holiday_pay"
                                                step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="leave_pay" class="form-label">Leave Pay</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="leave_pay" name="leave_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="overtime_pay" class="form-label">Overtime Pay</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="overtime_pay" name="overtime_pay"
                                                step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="night_differential_pay" class="form-label">Night Differential Pay</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="night_differential_pay"
                                                name="night_differential_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="overtime_night_differential_pay" class="form-label">Overtime Night
                                            Differential Pay</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="overtime_night_differential_pay"
                                                name="overtime_night_differential_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="late_deduction" class="form-label">Late Deduction</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="late_deduction" name="late_deduction"
                                                step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="undertime_deduction" class="form-label">Undertime Deduction</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="undertime_deduction"
                                                name="undertime_deduction" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="absent_deduction" class="form-label">Absent Deduction</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="absent_deduction" name="absent_deduction"
                                                step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Earnings Section -->
                                <h4 id="earnings_heading" class="mb-3 text-primary">Earnings</h4>
                                <div id="earnings_fields" class="row"></div>

                                <!-- Allowance Section -->
                                <h4 id="allowance_heading" class="mb-3 text-primary">Allowances</h4>
                                <div id="allowance_fields" class="row"></div>

                                <!-- Deductions Section -->
                                <h4 id="deductions_heading" class="mb-3 text-primary">Deductions</h4>
                                <div id="deductions_fields" class="row"></div>

                                <!-- Deminimis Section -->
                                <h4 id="deminimis_heading" class="mb-3 text-primary">Deminimis Benefits</h4>
                                <div id="deminimis_fields" class="row"></div>

                                <!-- Government Mandates -->
                                <h4 class="mb-3 text-primary">Government Mandates Fields</h4>
                                <div class="row">
                                    <div class="col-md-3 mb-4">
                                        <label for="sss_contribution" class="form-label">SSS Contribution</label>
                                        <input type="number" class="form-control" id="sss_contribution" name="sss_contribution"
                                            step="0.01">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="philhealth_contribution" class="form-label">PhilHealth Contribution</label>
                                        <input type="number" class="form-control" id="philhealth_contribution"
                                            name="philhealth_contribution" step="0.01">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="pagibig_contribution" class="form-label">PagIBIG Contribution</label>
                                        <input type="number" class="form-control" id="pagibig_contribution"
                                            name="pagibig_contribution" step="0.01">
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label for="withholding_tax" class="form-label">Withholding Tax</label>
                                        <input type="number" class="form-control" id="withholding_tax" name="withholding_tax"
                                            step="0.01">
                                    </div>
                                </div>

                                {{-- Salary Bond --}}
                                <h4 class="mb-3 text-primary">Salary Bond</h4>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label for="salary_bond" class="form-label">Salary Bond Deduction</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="salary_bond" name="salary_bond"
                                                step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Salary Breakdown -->
                                <h5 class="mb-3 text-primary">Salary Breakdown</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="total_earnings" class="form-label">Total Earnings</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="total_earnings" name="total_earnings"
                                                step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="total_deduction" class="form-label">Total Deduction</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="total_deduction" name="total_deductions"
                                                step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="basic_pay" class="form-label">Basic Pay</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="basic_pay" name="basic_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="gross_pay" class="form-label">Gross Pay</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="gross_pay" name="gross_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="net_salary" class="form-label">Net Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control text-danger" id="net_salary" name="net_salary"
                                                step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <h4 class="mb-3 text-primary">Payment Information</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="payment_date" class="form-label">Payment Date</label>
                                        <input type="date" class="form-control" id="payment_date" name="payment_date">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Processed By</label>
                                        <input type="text" class="form-control" id="processed_by" name="processed_by" readonly>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Payroll</button>
                                    </div>
                                </div>
                            </form>
                        </div>
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
                    const status = $('#violation-status').val(); 
                    const type = $('#violation-type-filter').val();
                    $.ajax({
                            url: '{{ route('violation-admin-filter') }}',
                            type: 'GET',
                            data: {
                                branch,
                                department,
                                designation, 
                                status,
                                type
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    $('#violation-table').DataTable().destroy();
                                    $('#violation-tbody').html(response.html);
                                    $('#violation-table').DataTable();
                                    
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
                        url: "{{ route('violation.employees-by-branch') }}",
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


                // File Violation Report Submission
                document.addEventListener('DOMContentLoaded', () => {
                    const fileForm = document.getElementById('fileViolationForm');
                    const errorBox = document.getElementById('file-violation-error');
                    const successBox = document.getElementById('file-violation-success');
                    const attachmentInput = document.getElementById('violation_attachments');
                    const attachmentPreview = document.getElementById('attachment-preview');
                    
                    let selectedFiles = [];

                    // Handle multiple file selection and preview with ability to add more
                    if (attachmentInput) {
                        attachmentInput.addEventListener('change', function(e) {
                            const newFiles = Array.from(e.target.files);
                            
                            // Add new files to existing selection
                            newFiles.forEach(file => {
                                // Check if file already exists (by name and size)
                                const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                                if (!exists) {
                                    selectedFiles.push(file);
                                }
                            });
                            
                            // Update preview
                            updateFilePreview();
                            
                            // Reset input to allow selecting the same files again if needed
                            e.target.value = '';
                        });
                    }
                    
                    function updateFilePreview() {
                        attachmentPreview.innerHTML = '';
                        
                        if (selectedFiles.length > 0) {
                            const previewContainer = document.createElement('div');
                            previewContainer.className = 'border rounded p-3 bg-light';

                            const heading = document.createElement('div');
                            heading.className = 'd-flex align-items-center justify-content-between mb-3';
                            
                            const headingText = document.createElement('strong');
                            headingText.className = 'text-primary';
                            headingText.innerHTML = `<i class="ti ti-files me-2"></i>${selectedFiles.length} file(s) selected`;
                            
                            const clearAllBtn = document.createElement('button');
                            clearAllBtn.type = 'button';
                            clearAllBtn.className = 'btn btn-sm btn-outline-danger';
                            clearAllBtn.innerHTML = '<i class="ti ti-trash me-1"></i>Clear All';
                            clearAllBtn.onclick = function() {
                                selectedFiles = [];
                                updateFilePreview();
                            };
                            
                            heading.appendChild(headingText);
                            heading.appendChild(clearAllBtn);
                            previewContainer.appendChild(heading);

                            selectedFiles.forEach((file, index) => {
                                const fileItem = document.createElement('div');
                                fileItem.className = 'd-flex align-items-center justify-content-between py-2 px-2 mb-2 bg-white rounded border';

                                const fileInfo = document.createElement('div');
                                fileInfo.className = 'd-flex align-items-center flex-grow-1';

                                const icon = document.createElement('i');
                                const ext = file.name.split('.').pop().toLowerCase();
                                if (ext === 'pdf') {
                                    icon.className = 'ti ti-file-type-pdf me-2 text-danger fs-5';
                                } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
                                    icon.className = 'ti ti-photo me-2 text-success fs-5';
                                } else {
                                    icon.className = 'ti ti-file-text me-2 text-primary fs-5';
                                }

                                const fileDetails = document.createElement('div');
                                fileDetails.className = 'flex-grow-1';
                                
                                const fileName = document.createElement('div');
                                fileName.className = 'text-dark fw-medium';
                                fileName.textContent = file.name;

                                const fileSize = document.createElement('small');
                                fileSize.className = 'text-muted';
                                const sizeInKB = (file.size / 1024).toFixed(2);
                                const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                                fileSize.textContent = sizeInKB > 1024 ? `${sizeInMB} MB` : `${sizeInKB} KB`;
                                
                                fileDetails.appendChild(fileName);
                                fileDetails.appendChild(fileSize);

                                const removeBtn = document.createElement('button');
                                removeBtn.type = 'button';
                                removeBtn.className = 'btn btn-sm btn-outline-danger ms-2';
                                removeBtn.innerHTML = '<i class="ti ti-x"></i>';
                                removeBtn.onclick = function() {
                                    selectedFiles.splice(index, 1);
                                    updateFilePreview();
                                };

                                fileInfo.appendChild(icon);
                                fileInfo.appendChild(fileDetails);
                                fileItem.appendChild(fileInfo);
                                fileItem.appendChild(removeBtn);

                                previewContainer.appendChild(fileItem);
                            });

                            attachmentPreview.appendChild(previewContainer);
                        }
                    }

                    fileForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        errorBox.classList.add('d-none');
                        successBox.classList.add('d-none');

                        const formData = new FormData();
                        
                        // Add form fields
                        formData.append('user_id', document.getElementById('employee').value);
                        formData.append('offense_details', document.getElementById('offense_details').value);
                        
                        // Add all selected files to formData
                        selectedFiles.forEach((file) => {
                            formData.append('attachments[]', file);
                        });

                        try {
                                console.log('Submitting violation with files:', selectedFiles.length);
                                const res = await fetch("{{ route('api.violationFileReport') }}", {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: formData
                            });

                            console.log('Response status:', res.status);
                            const data = await res.json();
                            console.log('Response data:', data);

                            if (data.status === 'success') {
                                toastr.success('Violation filed successfully','Success');
                                fileForm.reset();
                                selectedFiles = [];
                                attachmentPreview.innerHTML = '';
                                document.querySelector('#fileViolationModal .btn-close').click();
                                filter();
                            } else {
                                console.error('Error response:', data);
                                toastr.error(data.message || 'Something went wrong.','Error');
                            }
                        } catch (err) {
                            console.error('Fetch error:', err);
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
                        const noweViolationId = $('#nowe_violation_id');
   
                        $(document).on('click', '.issue-nowe', function (e) { 
                                e.preventDefault(); 
                                const $btn = $(this);
                                const violationId = $btn.data('id'); 
                                $(noweViolationId).val(violationId);
                                issueNoweModal.modal('show');
                                
                        });
 
                        noweForm.on('submit', function (e) {
                            e.preventDefault();
  
                            const violationId = noweViolationId.val();
                            if (!violationId) { 
                                toast.error('Undefined violation id','Error');
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
                                url: `{{ url('/api/violation') }}/${violationId}/issue-nowe`,
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
                    const apiViolationBase = "{{ url('/api/violation') }}"; 
                    const $investigationModal = $('#InvestigationReportModal');
                    const investigationModal = new bootstrap.Modal($investigationModal[0]);
                    const $investigationForm = $('#investigationForm');
                    const $investigationError = $('#investigation-error');
                    const $investigationSuccess = $('#investigation-success');
                    const $investigationViolationId = $('#investigation_violation_id');
 
                    window.openInvestigationModal = function (violationId) {
                        $investigationForm[0].reset();
                        $investigationError.addClass('d-none');
                        $investigationSuccess.addClass('d-none');

                        if (!violationId) {
                            $investigationError.text('Invalid violation id.').removeClass('d-none');
                            return;
                        }

                        $investigationViolationId.val(violationId);
                        investigationModal.show();
                    };
 
                    $investigationForm.on('submit', function (e) {
                        e.preventDefault();
                        $investigationError.addClass('d-none');
                        $investigationSuccess.addClass('d-none');

                        const id = $investigationViolationId.val();
                        const notes = $('#investigation_notes').val()?.trim() || '';

                        if (!id) {
                            $investigationError.text('Invalid violation record.').removeClass('d-none');
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
                            url: `${apiViolationBase}/${id}/investigate`,
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
                    const $damViolationId = $('#dam_violation_id');
 
                    window.openDamModal = function (violationId) {
                        $damForm[0].reset();
                        $damError.addClass('d-none');
                        $damSuccess.addClass('d-none');

                        if (!violationId) {
                            $damError.text('Invalid violation id.').removeClass('d-none');
                            return;
                        }

                        $damViolationId.val(violationId);
                        damModal.show();
                    };
 
                    $damForm.on('submit', function (e) {
                        e.preventDefault();
                        $damError.addClass('d-none');
                        $damSuccess.addClass('d-none');

                        const id = $damViolationId.val();
                        const file = $('#dam_file')[0].files[0];
                        const violationType = $('input[name="violation_type_id"]:checked').val();

                        if (!id) { $damError.text('Invalid violation record.').removeClass('d-none'); return; }
                        if (!file) { $damError.text('Please upload a DAM file.').removeClass('d-none'); return; }
                        if (!violationType) { $damError.text('Please select violation type.').removeClass('d-none'); return; }
                        if (file.size > 2 * 1024 * 1024) { $damError.text('File exceeds 2MB limit.').removeClass('d-none'); return; }

                        const formData = new FormData();
                        formData.append('dam_file', file);
                        formData.append('violation_type_id', violationType);

                        const submitDam = (url) => $.ajax({
                            url: url,
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            data: formData,
                            processData: false,
                            contentType: false,
                        });

                        // Try primary submission
                        submitDam(`${apiViolationBase}/${id}/issue-dam`)
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
                                // Fallback: lookup violation by employee id
                                $.getJSON(`${apiViolationBase}?employee_id=${encodeURIComponent(id)}&status=for_dam_issuance`)
                                .done(function (lookupData) {
                                    const first = (lookupData.violations || lookupData.data || lookupData)[0];
                                    if (!first?.id) { $damError.text('Violation case not found for this employee.').removeClass('d-none'); return; }
                                    // Retry DAM submission
                                    submitDam(`${apiViolationBase}/${first.id}/issue-dam`).done(function (retryData) {
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
                                    toastr.error('No violation found for given employee.','Error');
                                });
                            } else {
                                toastr.error(xhr.responseJSON?.message || `Error (${xhr.status}) issuing DAM.`,'Error');
                            }
                        });
                    });
                });

            </script>

            <script>
               // View Violation Info Modal
                $(document).ready(function () {
                    const apiViolationBase = "{{ url('/api/violation') }}";
                    const viewModal = $('#viewViolationModal');
                    const $viewLoading = $('#view-violation-loading');
                    const $viewError = $('#view-violation-error');
                    const $viewContent = $('#view-violation-content');
    
 
                    $(document).on('click', '.view-violation', function (e) { 
                        e.preventDefault(); 
                        const $btn = $(this);
                        const violationId = $btn.data('id');  
                        fetchViolationDetails(violationId);
                        viewModal.modal('show');
                            
                    }); 

                    function fetchViolationDetails(violationId) {
                        $.ajax({
                            url: `${apiViolationBase}/${violationId}`,
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (data) {
                                if (data.status === 'success' && data.violation) {
                                    displayViolationDetails(data.violation);
                                } else { 
                                    toastr.error('Failed to load violation details','Error');
                                }
                            },
                            error: function (xhr, status, error) { 
                                toastr.error(`Error fetching violation details (${xhr.status}): ${error}`,'Error');
                            }
                        });
                    } 

                    function displayViolationDetails(violation) {
                        // Employee Information
                        $('#view_employee_name').text(violation.employee_name || 'N/A');
                        $('#view_employee_id').text(violation.employee_id || 'N/A');
                        $('#view_branch').text(violation.branch || 'N/A');
                        $('#view_department').text(violation.department || 'N/A');
                        $('#view_designation').text(violation.designation || 'N/A');

                        // Violation Information
                        const statusBadge = $('#view_status');
                        const status = violation.status || 'N/A';
                        statusBadge.text(status).attr('class', 'badge bg-' + getStatusColor(status));

                        $('#view_type').text(violation.violation_type ? violation.violation_type: 'N/A');
                        $('#view_filed_date').text(violation.created_at ? new Date(violation.created_at).toLocaleDateString() : 'N/A');

                        if(violation.violation_type == 'Verbal Reprimand'){
                            $('#view_verbal').show();
                            $('#view_verbal_date').text(violation.verbal_reprimand_date || 'N/A');
                            $('#view_written').hide();
                            $('#view_suspension').hide(); 
                            $('#view_termination').hide();
                        }else if(violation.violation_type == 'Written Reprimand') {
                            $('#view_written').show();
                            $('#view_written_date').text(violation.written_reprimand_date || 'N/A');
                            $('#view_verbal').hide(); 
                            $('#view_suspension').hide(); 
                            $('#view_termination').hide();
                        }else if(violation.violation_type == 'Suspension') { 
                            $('#view_written').hide();
                            $('#view_verbal').hide(); 
                            $('#view_suspension').show(); 
                            $('#view_termination').hide(); 
                            $('#view_start_date').text(violation.suspension_start_date || 'N/A');
                            $('#view_end_date').text(violation.suspension_end_date || 'N/A');
                            $('#view_duration').text(violation.suspension_days ? `${violation.suspension_days} day(s)` : 'N/A');
                        }else if(violation.violation_type == 'Termination') {
                            $('#view_verbal').hide();
                            $('#view_written').hide();
                            $('#view_suspension').hide(); 
                            $('#view_termination').show();
                            $('#view_termination_date').text(violation.termination_date || 'N/A');
                        }else{
                            $('#view_verbal').hide(); 
                            $('#view_written').hide();
                            $('#view_suspension').hide(); 
                            $('#view_termination').hide();
                        }

                        // Offense Details
                        $('#view_offense_details').text(violation.offense_details || 'No details provided.'); 
                        // Investigation Notes
                        if (violation.investigation_notes) {
                            $('#view_investigation_notes').text(violation.investigation_notes);
                            $('#view_investigation_card').show();
                        } else {
                            $('#view_investigation_card').hide();
                        }

                        // Implementation Remarks
                        if (violation.implementation_remarks) {
                            $('#view_implementation_remarks').text(violation.implementation_remarks);
                            $('#view_implementation_card').show();
                        } else {
                            $('#view_implementation_card').hide();
                        }

                        // Employee Reply
                        if (violation.employee_reply) {
                            const reply = violation.employee_reply;
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
                        if (violation.information_report_file) attachments.push({ name: 'Information Report', url: violation.information_report_file, type: 'Legacy', size: null, uploaded_by: null, uploaded_at: null });
                        if (violation.nowe_file) attachments.push({ name: 'NOWE Document', url: violation.nowe_file, type: 'Legacy', size: null, uploaded_by: null, uploaded_at: null });
                        if (violation.dam_file) attachments.push({ name: 'DAM Document', url: violation.dam_file, type: 'Legacy', size: null, uploaded_by: null, uploaded_at: null });

                        // Add attachments from violation_attachments table
                        if (violation.attachments && violation.attachments.length > 0) {
                            violation.attachments.forEach(att => {
                                attachments.push({
                                    name: att.file_name,
                                    url: att.file_path,
                                    type: att.attachment_type ? att.attachment_type.replace('_', ' ').toUpperCase() : 'Attachment',
                                    size: att.file_size,
                                    uploaded_by: att.uploaded_by,
                                    uploaded_at: att.uploaded_at
                                });
                            });
                        }

                        const $attachmentsList = $('#view_attachments_list');
                        $attachmentsList.empty();

                        if (attachments.length > 0) {
                            attachments.forEach((att, index) => {
                                const fileSize = att.size ? `(${(att.size / 1024).toFixed(2)} KB)` : '';
                                const uploadInfo = att.uploaded_at ? `
                                    <small class="text-muted d-block mt-1">
                                        <i class="ti ti-user me-1"></i>${att.uploaded_by || 'N/A'} •
                                        <i class="ti ti-calendar me-1"></i>${att.uploaded_at}
                                    </small>
                                ` : '';

                                const card = `
                                    <div class="border rounded p-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-file me-2 text-primary"></i>
                                                    <strong>${att.name}</strong>
                                                    <span class="badge bg-light text-dark ms-2">${att.type}</span>
                                                    ${fileSize ? `<span class="text-muted ms-2">${fileSize}</span>` : ''}
                                                </div>
                                                ${uploadInfo}
                                            </div>
                                            <a href="/storage/${att.url}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="ti ti-search"></i>
                                            </a>
                                        </div>
                                    </div>
                                `;
                                $attachmentsList.append(card);
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
                // Implement Violation Modal
                document.addEventListener('DOMContentLoaded', () => {
                    const apiViolationBase = "{{ url('/api/violation') }}";
                    const implementModal = new bootstrap.Modal(document.getElementById('implementViolationModal'));
                    const implementForm = document.getElementById('implementViolationForm');
                    const implementError = document.getElementById('implement-violation-error');
                    const implementSuccess = document.getElementById('implement-violation-success');
                    const implementViolationId = document.getElementById('implement_violation_id');

                    // Open modal function
                    window.openViolationModal = function (violationId,violationType) {
                        implementForm.reset();
                        implementError.classList.add('d-none');
                        implementSuccess.classList.add('d-none');
                        
                        if (!violationId) {
                            implementError.textContent = 'Invalid violation id.';
                            implementError.classList.remove('d-none');
                            return;
                        }
                        document.getElementById('violationTypeName').textContent = violationType;
                        if(violationType == 'Verbal Reprimand'){
                            $('#violation-verbal').show();
                            $('#violation-written').hide();
                            $('#violation-suspension').hide();
                            $('#violation-termination').hide();
                        }else if(violationType == 'Written Reprimand'){
                            $('#violation-verbal').hide();
                            $('#violation-written').show();
                            $('#violation-suspension').hide();
                            $('#violation-termination').hide();
                        }else if(violationType == 'Suspension'){
                            $('#violation-verbal').hide();
                            $('#violation-written').hide();
                            $('#violation-suspension').show();
                            $('#violation-termination').hide();
                        }else if(violationType == 'Termination'){
                            $('#violation-verbal').hide();
                            $('#violation-written').hide();
                            $('#violation-suspension').hide();
                            $('#violation-termination').show();
                        }
                        implementViolationId.value = violationId;
                        
                        // Set minimum date to today
                        const today = new Date().toISOString().split('T')[0];
                        document.getElementById('suspension_start_date').setAttribute('min', today);
                        document.getElementById('suspension_end_date').setAttribute('min', today);
                        
                        implementModal.show();
                    };
 
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

                    const id = implementViolationId.value;
                    const violationType = document.getElementById('violationTypeName').textContent.trim();
                    const remarks = document.getElementById('implementation_remarks').value;

                    if (!id) {
                        implementError.textContent = 'Invalid violation record.';
                        implementError.classList.remove('d-none');
                        return;
                    }

                    const formData = new FormData();
                    if(violationType === 'Verbal Reprimand'){   

                        const verbal_reprimand_date = document.getElementById('verbal_reprimand_date').value; 
                        formData.append('verbal_reprimand_date', verbal_reprimand_date);
                        const fileInput = document.getElementById('verbal_reprimand_file');

                        if (fileInput && fileInput.files.length > 0) {
                            for (let i = 0; i < fileInput.files.length; i++) {
                                formData.append('verbal_reprimand_file[]', fileInput.files[i]);
                            }
                        }

                    }else if(violationType === 'Written Reprimand'){
                        const written_reprimand_date = document.getElementById('written_reprimand_date').value; 
                        formData.append('written_reprimand_date', written_reprimand_date);
                        const fileInput = document.getElementById('written_reprimand_file');

                        if (fileInput && fileInput.files.length > 0) {
                            for (let i = 0; i < fileInput.files.length; i++) {
                                formData.append('written_reprimand_file[]', fileInput.files[i]);
                            }
                        }

                    }else if (violationType === 'Suspension') {

                        const startDate = document.getElementById('suspension_start_date').value;
                        const endDate = document.getElementById('suspension_end_date').value; 
                        if (!startDate || !endDate) {
                            implementError.textContent = 'Please provide both start and end dates.';
                            implementError.classList.remove('d-none');
                            return;
                        } 
                        if (new Date(endDate) < new Date(startDate)) {
                            implementError.textContent = 'End date must be after or equal to start date.';
                            implementError.classList.remove('d-none');
                            return;
                        } 
                        formData.append('suspension_start_date', startDate);
                        formData.append('suspension_end_date', endDate);

                    }else if(violationType === 'Termination'){

                        const termination_date = document.getElementById('termination_date').value; 
                        formData.append('termination_date', termination_date);
                    
                    }
 
                    if (remarks) {
                        formData.append('implementation_remarks', remarks);
                    }

                    try {
                        const res = await fetch(`${apiViolationBase}/${id}/implement`, {
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
                            toastr.success(data.message || 'Violation implemented successfully.','Success'); 
                            implementModal.hide();
                            filter();
                        } else {
                            throw new Error(data.message || `Server error (${res.status}).`);
                        }
                    } catch (err) {
                        toastr.error(err.message || 'Error implementing violation.','Error');
                    }
                });

                });
            </script>
            <script>
                $(document).ready(function() {
                    const apiViolationBase = "{{ url('/api/violation') }}";

                    const $editModal = $('#editViolationModal');
                    const $editForm = $('#editViolationForm');
                    const $editError = $('#edit-violation-error');
                    const $editSuccess = $('#edit-violation-success');
                    const $editViolationId = $('#edit_violation_id');
                    const $currentFileInfo = $('#current_file_info');
 
                    window.openEditViolationModal = function(violationId) {
                        $editForm[0].reset();
                        $editError.addClass('d-none').text('');
                        $editSuccess.addClass('d-none').text('');
                        $currentFileInfo.html('');

                        if (!violationId) {
                            $editError.text('Invalid violation id.').removeClass('d-none');
                            return;
                        }

                        $editViolationId.val(violationId);
 
                        fetchAndPopulateViolation(violationId);

                        $editModal.modal('show');
                    };

                function fetchAndPopulateViolation(violationId) {
                    $.ajax({
                        url: `${apiViolationBase}/${violationId}`,
                        method: 'GET',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        dataType: 'json',
                        success: function(data) {
                            if (data.status === 'success' && data.violation) {
                                const violation = data.violation;

                                $('#edit_employee_info').text(`${violation.employee_name || 'N/A'} (${violation.employee_id || 'N/A'})`);
                                $('#edit_offense_details').val(violation.offense_details || '');
                                $('#edit_disciplinary_action').val(violation.disciplinary_action || '');
                                $('#edit_remarks').val(violation.remarks || '');

                                // Display current attachments
                                const $attachmentsDiv = $('#edit_current_attachments');
                                $attachmentsDiv.html('');

                                const attachments = [];
                                if (violation.information_report_file) {
                                    attachments.push({ 
                                        name: 'Information Report', 
                                        url: violation.information_report_file, 
                                        type: 'Report',
                                        size: null
                                    });
                                }

                                if (violation.attachments && violation.attachments.length > 0) {
                                    violation.attachments.forEach(att => {
                                        attachments.push({
                                            name: att.file_name,
                                            url: att.file_path,
                                            type: att.attachment_type || 'Attachment',
                                            size: att.file_size,
                                            uploaded_by: att.uploaded_by,
                                            uploaded_at: att.uploaded_at
                                        });
                                    });
                                }

                                if (attachments.length > 0) {
                                    let html = '<div class="list-group">';
                                    attachments.forEach(att => {
                                        const fileSize = att.size ? ` (${(att.size / 1024).toFixed(2)} KB)` : '';
                                        const uploadInfo = att.uploaded_at ? 
                                            `<small class="text-muted d-block"><i class="ti ti-calendar me-1"></i>${att.uploaded_at}</small>` : '';
                                        
                                        html += `
                                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-file me-2 text-primary"></i>
                                                        <strong>${att.name}</strong>
                                                        <span class="badge bg-light text-dark ms-2">${att.type}</span>
                                                        ${fileSize}
                                                    </div>
                                                    ${uploadInfo}
                                                </div>
                                                <a href="/storage/${att.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </div>
                                        `;
                                    });
                                    html += '</div>';
                                    $attachmentsDiv.html(html);
                                } else {
                                    $attachmentsDiv.html('<p class="text-muted mb-0"><i>No attachments yet</i></p>');
                                }
                            } else {
                               toastr.error('Failed to load violation details','Error');
                            }
                        },
                        error: function(xhr, status, error) {
                              toastr.error('Error loading violation details' + error ,'Error');
                        }
                    });
                }                    $editForm.on('submit', function(e) {
                        e.preventDefault();
                        $editError.addClass('d-none').text('');
                        $editSuccess.addClass('d-none').text('');

                    const id = $editViolationId.val();
                    const offenseDetails = $('#edit_offense_details').val().trim();
                    const disciplinaryAction = $('#edit_disciplinary_action').val().trim();
                    const remarks = $('#edit_remarks').val().trim();
                    const newAttachmentsInput = $('#edit_new_attachments')[0];

                    if (!id) {
                        $editError.text('Invalid violation record.').removeClass('d-none');
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
                    
                    // Add new attachments if any
                    if (newAttachmentsInput && newAttachmentsInput.files.length > 0) {
                        for (let i = 0; i < newAttachmentsInput.files.length; i++) {
                            formData.append('attachments[]', newAttachmentsInput.files[i]);
                        }
                    }                        $.ajax({
                            url: `${apiViolationBase}/${id}`,
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(data) {
                                if (data.status === 'success') {
                                   toastr.success('Violation updated successfully','Success');
                                   filter();
                                   $editModal.modal('hide'); 
                                } else {
                                    toastr.error('Error updating violation','Error');
                                }
                            },
                            error: function(xhr, status, error) {
                               toastr.error('Error updating violation' + error ,'Error');
                            }
                        });
                    });
 
                    $(document).on('click', '.edit-violation', function() {
                        const violationId = $(this).data('id');
                        openEditViolationModal(violationId);
                    });
                });
            </script>
            <script>
                function processLastPay(employeeId,violation_id) {
                    document.getElementById('lastPayEmployeeId').value = employeeId;
                    document.getElementById('violation_id').value = violation_id;
                    let modal = new bootstrap.Modal(
                        document.getElementById('processLastPayModal')
                    );
                    modal.show();
                }
            </script>

            <script>
                $(document).ready(function() {

                    $('#finalpayrollProcessForm').on('submit', function(e) {
                        e.preventDefault();

                        let form = $(this);
                        let submitBtn = form.find('button[type="submit"]');
 
                        submitBtn.prop('disabled', true).html('Processing...');

                        let formData = new FormData(this);

                        $.ajax({
                            url: "{{ route('api.violationProcessLastPay') }}",
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('input[name="_token"]').val()
                            },
                            success: function(response) {
                                toastr.success(response.message || 'Last pay processed successfully');
 
                                $('#processLastPayModal').modal('hide');
 
                                form[0].reset();
                                filter();
                            },
                            error: function(xhr) {
                                let message = 'Something went wrong';
                                if (xhr.status === 422) {
                                    let errors = xhr.responseJSON.errors;
                                    message = Object.values(errors).flat().join('<br>');
                                } else if (xhr.responseJSON?.message) {
                                    message = xhr.responseJSON.message;
                                }
                                toastr.error(message);
                            },
                            complete: function() {
                                submitBtn.prop('disabled', false)
                                        .html('<i class="ti ti-settings me-1"></i> Process Last Pay');
                            }
                        });
                    });

                });
            </script>  
            <script>

                const deminimisBenefits = @json($deminimisBenefits);
 
                function parseJSONSafe(data) {
                    if (!data) return [];
                    try {
                        return JSON.parse(data);
                    } catch {
                        return [];
                    }
                }
 
                function htmlDecode(input) {
                    var e = document.createElement('textarea');
                    e.innerHTML = input;
                    return e.value;
                }

                function viewEditLastPay(payrollId) {
                    
                    fetch(`/api/last_payroll/${payrollId}`)  
                        .then(res => res.json())
                            .then(data => {
                                // Fill static fields
                                document.getElementById('payroll_id').value = data.id;
                                document.getElementById('payroll_type').value = data.payroll_type;
                                document.getElementById('payroll_period').value = data.payroll_period;
                                document.getElementById('payroll_period_start').value = data.payroll_period_start;
                                document.getElementById('payroll_period_end').value = data.payroll_period_end;

                                document.getElementById('total_worked_minutes').value = data.total_worked_minutes;
                                document.getElementById('total_late_minutes').value = data.total_late_minutes;
                                document.getElementById('total_undertime_minutes').value = data.total_undertime_minutes;
                                document.getElementById('total_overtime_minutes').value = data.total_overtime_minutes;
                                document.getElementById('total_night_differential_minutes').value = data.total_night_differential_minutes;
                                document.getElementById('total_overtime_night_differential_minutes').value = data.total_overtime_night_diff_minutes;

                                document.getElementById('holiday_pay').value = data.holiday_pay;
                                document.getElementById('leave_pay').value = data.leave_pay;
                                document.getElementById('overtime_pay').value = data.overtime_pay;
                                document.getElementById('night_differential_pay').value = data.night_differential_pay;
                                document.getElementById('overtime_night_differential_pay').value = data.overtime_night_diff_pay;

                                document.getElementById('late_deduction').value = data.late_deduction;
                                document.getElementById('undertime_deduction').value = data.undertime_deduction;
                                document.getElementById('absent_deduction').value = data.absent_deduction;

                                document.getElementById('sss_contribution').value = data.sss_contribution;
                                document.getElementById('philhealth_contribution').value = data.philhealth_contribution;
                                document.getElementById('pagibig_contribution').value = data.pagibig_contribution;
                                document.getElementById('withholding_tax').value = data.withholding_tax;

                                document.getElementById('salary_bond').value = data.salary_bond;

                                document.getElementById('total_earnings').value = data.total_earnings;
                                document.getElementById('total_deduction').value = data.total_deductions;
                                document.getElementById('basic_pay').value = data.basic_pay;
                                document.getElementById('gross_pay').value = data.gross_pay;
                                document.getElementById('net_salary').value = data.net_salary;

                                document.getElementById('payment_date').value = data.payment_date;
                                document.getElementById('processed_by').value = data.processed_by;
 
                                let raw = data.deminis;
                                let decodedRaw = htmlDecode(raw);
                                let deminimisArr = parseJSONSafe(decodedRaw);
                                if (!deminimisArr.length) {
                                    deminimisArr = parseJSONSafe(raw);
                                }

                                let html = '';
                                if (Array.isArray(deminimisArr) && deminimisArr.length) {
                                    deminimisArr.forEach((item, idx) => {
                                        const benefitName = deminimisBenefits[item.deminimis_benefit_id] ||
                                            `Unknown (${item.deminimis_benefit_id})`;
                                        html += `
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">${benefitName}</label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="deminimis_amounts[${item.deminimis_benefit_id}]"
                                                value="${item.amount}">
                                        </div>
                                    `;
                                    });
                                    $('#deminimis_heading').show();
                                    $('#deminimis_fields').show().html(html);
                                } else {
                                    $('#deminimis_heading').hide();
                                    $('#deminimis_fields').hide().html('');
                                }
 
                                let earningsRaw = data.earnings;
                                let earningsDecoded = htmlDecode(earningsRaw);
                                let earningsArr = parseJSONSafe(earningsDecoded);
                                if (!earningsArr.length) {
                                    earningsArr = parseJSONSafe(earningsRaw);
                                }

                                let earningsHtml = '';
                                if (Array.isArray(earningsArr) && earningsArr.length) {
                                    earningsArr.forEach(function (item, idx) {
                                        earningsHtml += `
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">${item.earning_type_name}</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="earnings[${item.earning_type_id}][applied_amount]"
                                            value="${item.applied_amount}">
                                    </div>
                                `;
                                    });
                                    $('#earnings_heading').show();
                                    $('#earnings_fields').show().html(earningsHtml);
                                } else {
                                    $('#earnings_heading').hide();
                                    $('#earnings_fields').hide().html('');
                                }
 
                                let allowanceRaw = data.allowance;
                                let allowanceDecoded = htmlDecode(allowanceRaw);
                                let allowanceArr = parseJSONSafe(allowanceDecoded);
                                if (!allowanceArr.length) {
                                    allowanceArr = parseJSONSafe(allowanceRaw);
                                }

                                let allowanceHtml = '';
                                if (Array.isArray(allowanceArr) && allowanceArr.length) {
                                    allowanceArr.forEach(function (item, idx) { 
                                        allowanceHtml += `
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">${item.allowance_name}</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="allowances[${item.allowance_id}][applied_amount]"
                                            value="${item.applied_amount}">
                                    </div>
                                `;
                                    });
                                    $('#allowance_heading').show();
                                    $('#allowance_fields').show().html(allowanceHtml);
                                } else {
                                    $('#allowance_heading').hide();
                                    $('#allowance_fields').hide().html('');
                                }
 
                                let deductionsRaw = data.deductions;
                                let deductionsDecoded = htmlDecode(deductionsRaw);
                                let deductionsArr = parseJSONSafe(deductionsDecoded);
                                if (!deductionsArr.length) {
                                    deductionsArr = parseJSONSafe(deductionsRaw);
                                }

                                let deductionsHtml = '';
                                if (Array.isArray(deductionsArr) && deductionsArr.length) {
                                    deductionsArr.forEach(function (item, idx) {
                                        deductionsHtml += `
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">${item.deduction_type_name}</label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="deductions[${item.deduction_type_id}][applied_amount]"
                                                value="${item.applied_amount}">
                                        </div>
                                    `;
                                    });
                                    $('#deductions_heading').show();
                                    $('#deductions_fields').show().html(deductionsHtml);
                                } else {
                                    $('#deductions_heading').hide();
                                    $('#deductions_fields').hide().html('');
                                }
 
                                var modal = new bootstrap.Modal(document.getElementById('edit_payroll'));
                                modal.show();
                            })
                        .catch(err => {
                            console.error(err);
                            alert('Failed to load payroll data from API.');
                        });
                }  

                
            </script>

            <script>
                $('#editPayrollForm').on('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const payrollId = $('#payroll_id').val();
                    const csrfToken = $('meta[name="csrf-token"]').attr('content');
                    const authToken = localStorage.getItem('token');

                    $.ajax({
                        url: '/api/payroll/update/' + payrollId,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Authorization': 'Bearer ' + authToken
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (res) { 
                            toastr.success("Payroll has been updated successfully!");
                            $('#edit_payroll').modal('hide');
                            filter();
                        },
                        error: function (err) {
                            console.error('Update error response:', err);
                            if (err.responseJSON && err.responseJSON.message) {
                                toastr.error(err.responseJSON.message);
                            } else {
                                toastr.error("An error occurred while updating payroll.");
                            }
                        }
                    });
                });
            </script>
            
            <script>
                // Return to Work Modal
                document.addEventListener('DOMContentLoaded', () => {
                    const apiViolationBase = "{{ url('/api/violation') }}";
                    const returnModal = new bootstrap.Modal(document.getElementById('returnToWorkModal'));
                    const returnForm = document.getElementById('returnToWorkForm');
                    const returnError = document.getElementById('return-error');
                    const returnSuccess = document.getElementById('return-success');
                    const returnViolationId = document.getElementById('return_violation_id');

                    // Open return to work modal function
                    window.completeViolation = function (violationId) {
                        returnForm.reset();
                        returnError.classList.add('d-none');
                        returnSuccess.classList.add('d-none');
                        
                        if (!violationId) {
                            returnError.textContent = 'Invalid violation id.';
                            returnError.classList.remove('d-none');
                            return;
                        }
                        
                        returnViolationId.value = violationId;
                        
                        // Fetch violation data to show employee info
                        fetchViolationForReturn(violationId);
                        
                        returnModal.show();
                    };

                    async function fetchViolationForReturn(violationId) {
                        try {
                            const res = await fetch(`${apiViolationBase}/${violationId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                credentials: 'same-origin'
                            });

                            if (!res.ok) {
                                throw new Error(`Failed to fetch violation details (${res.status})`);
                            }

                            const data = await res.json();
                            
                            if (data.status === 'success' && data.violation) {
                                const violation = data.violation;
                                
                                // Populate employee info (read-only)
                                document.getElementById('return_employee_info').textContent = 
                                    `${violation.employee_name || 'N/A'} (${violation.employee_id || 'N/A'})`;
                            } else {
                                throw new Error(data.message || 'Failed to load violation details.');
                            }
                        } catch (err) {
                            returnError.textContent = err.message || 'Error loading violation details.';
                            returnError.classList.remove('d-none');
                        }
                    }

                    // Handle form submission
                    returnForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        returnError.classList.add('d-none');
                        returnSuccess.classList.add('d-none');

                        const id = returnViolationId.value;
                        
                        if (!id) {
                            returnError.textContent = 'Invalid violation record.';
                            returnError.classList.remove('d-none');
                            return;
                        }

                        try {
                            const res = await fetch(`${apiViolationBase}/${id}/return`, {
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