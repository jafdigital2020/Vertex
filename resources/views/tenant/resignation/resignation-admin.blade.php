<?php $page = 'resignation'; ?>
@extends('layout.mainlayout')
@section('content')
  <style>
    .remarks-chat {
        background-color: #f9f9f9;
        padding: 10px;
        border-radius: 8px;
        overflow-y: auto;
    }

    .chat-bubble {
        padding: 10px 14px;
        border-radius: 15px;
        max-width: 70%;
        word-wrap: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
 
    .chat-right {
        background-color: #f1f1f1;
        color: #333;
        text-align: left;
        border-top-left-radius: 0;
    }
 
    .chat-left {
        background-color: #d1f7d6;
        color: #333;
        text-align: left;
        border-top-right-radius: 0;
    }

   </style>
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Resignation Admin</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Resignation
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Resignation Admin</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap "> 
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Resignation List -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5 class="d-flex align-items-center">Resignation List</h5>
                            <div class="d-flex align-items-center flex-wrap row-gap-3">
                               
                            </div>
                        </div>
                        <div class="card-body p-0">

                            <div class="custom-datatable-filter table-responsive">
                                <table class="table datatable">
                                    <thead class="thead-light">
                                        <tr class="text-center"> 
                                            <th>Date Filed</th>
                                            <th class="text-center">Resigning Employee</th> 
                                            <th class="text-center">Branch</th>
                                            <th class="text-center">Department</th>
                                            <th class="text-center">Designation</th>
                                            <th class="text-center">Resignation Letter</th>  
                                            <th class="text-center">Date Accepted</th>
                                            <th class="text-center">Remaining Days</th>
                                            <th class="text-center">Resignation Date</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Remarks</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        @foreach ($resignations as $resignation)
                                            <tr class="text-center">
                                                <td>{{$resignation->date_filed}}</td>
                                                <td>{{$resignation->personalInformation->first_name ?? '' }} {{$resignation->personalInformation->last_name ?? '' }}</td> 
                                                <td>{{$resignation->employmentDetail->branch->name ?? ''}}</td>
                                                <td>{{$resignation->employmentDetail->department->department_name ?? ''}}</td>
                                                <td>{{$resignation->employmentDetail->designation->designation_name ?? ''}}</td>
                                                <td>
                                                    <button 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="viewResignationFile('{{ asset('storage/' . $resignation->resignation_file) }}', '{{$resignation->reason}}')">
                                                        View <i class="fa fa-file"></i>
                                                    </button>
                                                </td>  
                                                <td>{{$resignation->accepted_date ?? '-'}}</td>    
                                                 @php
                                                    if ($resignation->resignation_date !== null) {
                                                        $remainingDays = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($resignation->resignation_date), false);
                                                    } else {
                                                        $remainingDays = null;
                                                    }
                                                @endphp

                                                <td>
                                                    @if ($remainingDays === null)
                                                        -
                                                    @elseif ($remainingDays > 0)
                                                        {{ $remainingDays }} days
                                                    @else
                                                        Expired
                                                    @endif
                                                </td>

                                                <td>{{$resignation->resignation_date ?? '-'}}</td>
                                                <td>
                                                     @if($resignation->status === 0) 
                                                    <span>For Approval</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date === null )
                                                    <span>For Acceptance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 0)
                                                    <span>For Clearance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 )
                                                    <span>Resigned</span>
                                                    @elseif($resignation->status === 2)
                                                    <span>Rejected</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($resignation->status_remarks !== null || $resignation->accepted_remarks !== null)
                                                         <button 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="viewResignationRemarks( '{{$resignation->id }}')">
                                                        View <i class="fa fa-sticky-note"></i>
                                                    </button>
                                                    @else 
                                                    -
                                                    @endif
                                                </td>
                                                <td> 
                                                    @if($resignation->status === 0)
                                                   <button class="btn btn-success btn-sm" onclick="openApprovalModal({{ $resignation->id }}, 'approve')">
                                                        Approve
                                                    </button> 
                                                    <button class="btn btn-danger btn-sm" onclick="openApprovalModal({{ $resignation->id }}, 'reject')">
                                                        Reject
                                                    </button> 
                                                    @elseif($isActiveHR && $resignation->status === 1 && $resignation->accepted_by === null)
                                                    <button class="btn btn-success btn-sm" onclick="openAcceptanceModal({{ $resignation->id }}, 'accept')">
                                                        Accept
                                                    </button> 

                                                    @elseif($isActiveHR && $resignation->status === 1 && $resignation->accepted_by !== null && $resignation->cleared_status === 0   ) 
 
                                                    <div class="action-icon d-inline-flex text-center">  
                                                        <button type="button" 
                                                            class="btn btn-sm btn-primary me-2"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadAttachmentsModal-{{ $resignation->id }}">
                                                            <i class="bi bi-file-earmark-check"></i>
                                                        </button>

                                                        <div class="modal fade" id="uploadAttachmentsModal-{{ $resignation->id }}" tabindex="-1" aria-labelledby="uploadAttachmentsModalLabel-{{ $resignation->id }}" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered modal-md">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="uploadAttachmentsModalLabel-{{ $resignation->id }}">
                                                                            Validate Attachments
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>

                                                                    <form action="{{ route('resignation.attachments.updateStatuses', $resignation->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('PUT')

                                                                        <div class="modal-body">
                                                                            @php
                                                                                $employeeAttachments = $resignation->resignationAttachment->where('uploader_role', 'employee');
                                                                            @endphp

                                                                            <div class="mb-4">
                                                                                @if ($employeeAttachments->isNotEmpty())
                                                                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                                                        <table class="table table-sm table-bordered table-striped align-middle shadow-sm mb-0">
                                                                                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                                                                                <tr>
                                                                                                    <th class="text-center" style="width: 5%;">No.</th>
                                                                                                    <th class="text-center">Uploaded File</th>
                                                                                                    <th class="text-center" >Status</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                @foreach ($employeeAttachments as $index => $file)
                                                                                                    <tr>
                                                                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                                                                        <td class="text-center">
                                                                                                            <a href="{{ asset('storage/resignation_attachments/' . $file->filename) }}"
                                                                                                            target="_blank"
                                                                                                            class="text-decoration-none text-primary fw-semibold text-truncate d-inline-block"
                                                                                                            style="max-width: 250px;"
                                                                                                            title="{{ $file->filename }}">
                                                                                                                <i class="bi bi-file-earmark-text me-1 text-secondary"></i>
                                                                                                                {{ $file->filename }}
                                                                                                            </a>
                                                                                                            <br>
                                                                                                            <small class="text-muted">{{ strtoupper($file->filetype ?? 'FILE') }}</small>
                                                                                                        </td>
                                                                                                        <td class="text-center">
                                                                                                            <select name="statuses[{{ $file->id }}]" class="form-select select2 select form-select-sm text-center">
                                                                                                                <option value="pending"  {{ $file->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                                                                <option value="approved" {{ $file->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                                                                                                <option value="rejected" {{ $file->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                                                                            </select>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                @else
                                                                                    <p class="text-muted mb-4">Employee hasn’t uploaded any attachments yet.</p>
                                                                                @endif
                                                                            </div>
                                                                        </div>

                                                                        <div class="modal-footer">
                                                                            <button class="btn btn-primary" type="submit">
                                                                                <i class="bi bi-check2-circle me-1"></i> Update Status
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                            <button type="button" 
                                                                class="btn btn-sm btn-primary me-2"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#returnAssetsModal-{{ $resignation->id }}"
                                                                data-bs-toggle="tooltip" 
                                                                title="Receive Returned Assets">
                                                                <i class="bi bi-box-arrow-in-down"></i>
                                                            </button>
                                                            <div class="modal fade" id="returnAssetsModal-{{ $resignation->id }}" tabindex="-1" aria-labelledby="returnAssetsModalLabel-{{ $resignation->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content"> 
                                                                    <div class="modal-header ">
                                                                        <h5 class="modal-title" id="returnAssetsModalLabel-{{ $resignation->id }}"> 
                                                                           Receive Assets
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div> 
                                                                        <div class="modal-body"> 
                                                                        <div class="mb-3"> 
                                                                   <form action="{{ route('resignation.assets.return') }}" method="POST">
                                                                      @csrf 
                                                                    <table class="table table-sm table-bordered align-middle">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>Asset Name</th>
                                                                                <th class="text-center">Condition</th>
                                                                                <th class="text-center">Remarks</th>
                                                                                <th class="text-center">Asset Status</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($resignation->deployedAssets as $asset)
                                                                                <tr>
                                                                                    <td class="text-start">{{ $asset->assets->name }}</td>

                                                                                    <td>
                                                                                        <select name="condition[{{ $asset->id }}]"
                                                                                                class="form-select form-select-sm asset-condition"
                                                                                                data-id="{{ $asset->id }}"
                                                                                                onchange="checkCondition(this)"
                                                                                                required>
                                                                                            <option value="">Select</option>
                                                                                            <option value="Brand New" {{ $asset->asset_condition == 'Brand New' ? 'selected' : '' }}>Brand New</option>
                                                                                            <option value="Good Working Condition" {{ $asset->asset_condition == 'Good Working Condition' ? 'selected' : '' }}>Good Working Condition</option>
                                                                                            <option value="Under Maintenance" {{ $asset->asset_condition == 'Under Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                                                                            <option value="Defective" {{ $asset->asset_condition == 'Defective' ? 'selected' : '' }}>Defective</option>
                                                                                            <option value="Unservicable" {{ $asset->asset_condition == 'Unservicable' ? 'selected' : '' }}>Unservicable</option>
                                                                                        </select>
                                                                                    </td>

                                                                                    <td class="text-center">
                                                                                    <button type="button" 
                                                                                                class="btn btn-xs btn-primary"
                                                                                                id="showAssetBTN-{{ $asset->id }}"
                                                                                                onclick="viewAssetRemarks('{{ $asset->id }}')">
                                                                                            <i class="fa fa-sticky-note"></i>
                                                                                        </button> 
                                                                                        <!-- Modal -->
                                                                                        <div class="modal fade" id="asset_remarks_modal_{{ $asset->id }}" tabindex="-1" >
                                                                                            <div class="modal-dialog modal-dialog-centered modal-md">
                                                                                                <div class="modal-content">
                                                                                                    <div class="modal-header">
                                                                                                        <h5 class="modal-title">Asset Remarks - {{ $asset->assets->name }}</h5>
                                                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                                    </div>

                                                                                                    <div class="modal-body">
                                                                                                    <div id="remarksContainer{{ $asset->id }}" class="p-2 border rounded">
                                                                                                    @if ($asset->remarks->count()) 
                                                                                                        <div class="remarks-chat p-2 border rounded" style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;">
                                                                                                            @foreach ($asset->remarks as $remark)
                                                                                                                <div class="d-flex mb-3 {{ $remark->remarks_from === 'Employee' ? 'justify-content-start' : 'justify-content-end' }}">
                                                                                                                    <div class="chat-bubble col-9
                                                                                                                                {{ $remark->remarks_from === 'HR' ? 'chat-left' : 'chat-right' }}">
                                                                                                                        <strong class="small text-muted d-block mb-1">
                                                                                                                          {{ $remark->remarks_from === 'HR' 
                                                                                                                            ? 'HR' 
                                                                                                                            : optional($remark->personalInformation)->first_name . ' ' . optional($remark->personalInformation)->last_name }}

                                                                                                                        </strong>
                                                                                                                        <span class="d-block">{{ $remark->condition_remarks }}</span>
                                                                                                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                                                                                                            {{ $remark->created_at->format('M d, Y h:i A') }}
                                                                                                                        </small>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            @endforeach
                                                                                                        </div> 
                                                                                                    @else
                                                                                                        <p class="text-muted">No remarks yet.</p>
                                                                                                    @endif  
                                                                                                    @if($asset->status != 'Available')
                                                                                                    </div> 
                                                                                                        <div class="form-group mt-3 text-start">
                                                                                                            <label for="remarkText{{ $asset->id }}" class="fw-bold">Remarks:</label>
                                                                                                            <textarea class="form-control myTextarea" rows="3"
                                                                                                                    id="remarkText{{ $asset->id }}"
                                                                                                                    name="resignation_assets_remarks{{ $asset->id }}"
                                                                                                                    placeholder="Write your reply here..."></textarea>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    @endif 
                                                                                                    <div class="modal-footer">
                                                                                                         @if($asset->status != 'Available')
                                                                                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                                                                                                        <button type="button" class="btn btn-primary"
                                                                                                                onclick="saveAssetRemarks({{ $asset->id }})">
                                                                                                            Send
                                                                                                        </button>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                    <td> 
                                                                                        <select name="status[{{ $asset->id }}]"
                                                                                                class="form-select form-select-sm asset-status"
                                                                                                data-id="{{ $asset->id }}"
                                                                                                required>
                                                                                            <option value="">Select</option> 
                                                                                            <option value="Deployed" {{ $asset->status == 'Deployed' ? 'selected' : '' }}>Deployed</option>
                                                                                            <option value="Return" {{ $asset->status == 'Return' ? 'selected' : '' }}>Return</option> 
                                                                                            <option value="Available" {{ $asset->status == 'Available'? 'selected' : '' }}> Received</option>
                                                                                        </select> 
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>

                                                                        </div>  
                                                                        </div> 
                                                                        <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                                        </div> 
                                                                    </div>
                                                                </div>
                                                            </div> 
                                                        </div>     
                                                        <button class="btn btn-primary btn-sm"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmClearModal"
                                                                data-id="{{ $resignation->id }}">
                                                            <i class="fa fa-check"></i>
                                                        </button> 
                                                    @endif
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
      <div class="modal fade" id="viewResignationModal" tabindex="-1" aria-labelledby="viewResignationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resignation Letter Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-center">

                    <!-- Reason Section -->
                    <div id="resignationReasonContainer" class="mb-4 text-start d-none">
                        <h6 class="fw-bold">Reason for Resignation:</h6>
                        <p id="resignationReasonText" class="border rounded p-2 bg-light"></p>
                    </div>
                    <!-- File Preview Section -->
                    <iframe id="resignationPreviewFrame" src="" style="width:100%;height:80vh;border:none;display:none;"></iframe>

                    <div id="resignationWordNotice" class="d-none">
                        <p>This file cannot be previewed directly. Click below to open it in Office viewer:</p>
                        <a id="resignationWordLink" href="#" target="_blank" class="btn btn-primary">Open Document</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="approveResignationModal" tabindex="-1" aria-labelledby="approveResignationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="approvalModalTitle">Approve Resignation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="approveResignationForm">
                        <input type="hidden" id="resignationId" name="resignation_id">
                        <input type="hidden" id="approvalAction" name="action">

                        <div class="mb-3">
                            <label for="status_remarks" class="form-label fw-bold">Remarks</label>
                            <textarea id="status_remarks" name="status_remarks" class="form-control" rows="4" maxlength="500" placeholder="Enter your remarks (optional)"></textarea>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="submitApprovalBtn" class="btn btn-success">Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<!-- HR Acceptance Modal -->
<div class="modal fade" id="acceptResignationModal" tabindex="-1" aria-labelledby="acceptResignationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title">Accept Resignation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> 
           <form id="acceptResignationForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="acceptResignationId" name="resignation_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Resignation Date</label>
                        <input type="date" class="form-control" name="resignation_date" id="resignation_date" required>
                    </div>

                    <div class="mb-3">
                        <label for="accept_remarks" class="form-label fw-bold">Remarks</label>
                        <textarea id="accept_remarks" name="accepted_remarks" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="accept_instruction" class="form-label fw-bold">Instruction</label>
                        <textarea id="accept_instruction" name="accepted_instruction" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Attachment</label>
                        <input type="file" name="resignation_attachment[]" id="resignation_attachment" class="form-control" multiple> 
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Accept Resignation</button>
                </div>
            </form> 
        </div>
    </div>
</div>
<div class="modal fade" id="viewRemarksModal" tabindex="-1" aria-labelledby="viewRemarksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRemarksModalLabel">Resignation Remarks</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="remarksContent" class="text-dark"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>  
<div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmClearModalLabel">Confirm Clearance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="mb-2">
                    By marking this resignation as <strong>cleared</strong>, you are confirming that:
                </p>
                <ul class="mb-2">
                    <li>All employee <strong>assets</strong> received have been verified and will be marked as <strong>Available</strong>.</li>
                    <br>
                    <li>All necessary <strong>attachments</strong> have been reviewed and approved.</li>
                </ul>
                <p class="mb-0 text-danger fw-semibold">
                    This action cannot be undone.
                </p>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="resignationIdToClear">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success " id="confirmClearBtn">
                    <i class="fa fa-check me-1"></i> Yes, Mark as Cleared
                </button>
            </div>
        </div>
    </div>
</div>
  
   <script>
    function viewResignationFile(fileUrl, reason) {
        const fileExtension = fileUrl.split('.').pop().toLowerCase();
        const iframe = document.getElementById('resignationPreviewFrame');
        const wordNotice = document.getElementById('resignationWordNotice');
        const wordLink = document.getElementById('resignationWordLink');
        const reasonContainer = document.getElementById('resignationReasonContainer');
        const reasonText = document.getElementById('resignationReasonText');

        // Hide iframe and notice by default
        iframe.style.display = 'none';
        wordNotice.classList.add('d-none');

        // Handle file preview logic
        if (fileExtension === 'pdf') {
            iframe.src = fileUrl;
            iframe.style.display = 'block';
        } else if (fileExtension === 'doc' || fileExtension === 'docx') {
            wordLink.href = `https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(fileUrl)}`;
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `
                <p>This file cannot be previewed directly. Click below to open it in Office viewer:</p>
                <a id="resignationWordLink" href="${wordLink.href}" target="_blank" class="btn btn-primary">Open Document</a>
            `;
        } else {
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `<p>Unsupported file format. <a href="${fileUrl}" target="_blank">Download file</a></p>`;
        }

        // Show reason only if provided
        if (reason && reason.trim() !== '') {
            reasonText.textContent = reason;
            reasonContainer.classList.remove('d-none');
        } else {
            reasonContainer.classList.add('d-none');
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('viewResignationModal'));
        modal.show();
    }
    </script>
<script>
function openApprovalModal(resignationId, action) {
    const modalTitle = document.getElementById('approvalModalTitle');
    const submitBtn = document.getElementById('submitApprovalBtn');
    const actionInput = document.getElementById('approvalAction');
    const remarksField = document.getElementById('status_remarks');

    // Set modal data
    document.getElementById('resignationId').value = resignationId;
    actionInput.value = action;
    remarksField.value = ''; // clear previous input

    // Adjust modal appearance based on action
    if (action === 'approve') {
        modalTitle.textContent = 'Approve Resignation';
        submitBtn.textContent = 'Approve';
        submitBtn.className = 'btn btn-success';
    } else if (action === 'reject') {
        modalTitle.textContent = 'Reject Resignation';
        submitBtn.textContent = 'Reject';
        submitBtn.className = 'btn btn-danger';
    }

    const modal = new bootstrap.Modal(document.getElementById('approveResignationModal'));
    modal.show();
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('approveResignationForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const resignationId = document.getElementById('resignationId').value;
        const action = document.getElementById('approvalAction').value; 
        const remarks = document.getElementById('status_remarks').value.trim();

        if (!remarks) {
            toastr.warning('Please enter remarks before submitting.', 'Warning');
            return;
        }

        fetch(`/api/resignation/${action}/${resignationId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status_remarks: remarks })
        })
        .then(async (response) => {
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                throw new Error(data?.message || `HTTP ${response.status}`);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                toastr.success(`Resignation successfully ${action}d.`, 'Success');
                setTimeout(() => {
                    location.reload();
                }, 1500);  
            } else {
                toastr.error(data.message || 'Something went wrong.', 'Error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An unexpected error occurred. Please try again.', 'Error');
        });
    });
});

</script>

<script>
function openAcceptanceModal(id) {
    document.getElementById('acceptResignationId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('acceptResignationModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('acceptResignationForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const resignationId = document.getElementById('acceptResignationId').value;
        const formData = new FormData(form);

        try {
            const response = await fetch(`/api/resignation/accept/${resignationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            let data;
            try {
                data = await response.json();
            } catch (err) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                alert('Server returned an unexpected response. Please check console.');
                return;
            }

            if (data.success) {
                alert('Resignation successfully accepted by HR.');
                location.reload();
            } else {
                alert(data.message || 'Something went wrong.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred. Please try again.');
        }
    });
});

   function viewResignationRemarks(resignationId) {

        fetch(`/api/resignation/remarks/${resignationId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const remarksDiv = document.getElementById('remarksContent');
                    remarksDiv.innerHTML = '';  

                    const deptHeadRemarks = data.status_remarks ? `
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary mb-1">Department Head / Reporting To Remarks:</h6>
                            <p class="border rounded p-2 bg-light">${data.status_remarks}</p>
                        </div>` : '';

                    const hrRemarks = data.accepted_remarks ? `
                        <div class="mb-3">
                            <h6 class="fw-bold text-success mb-1">HR Remarks:</h6>
                            <p class="border rounded p-2 bg-light">${data.accepted_remarks}</p>
                        </div>` : '';

                     const hrInstruction = data.instruction ? `
                        <div class="mb-3">
                            <h6 class="fw-bold text-success mb-1">HR Instruction:</h6>
                            <p class="border rounded p-2 bg-light">${data.instruction}</p>
                        </div>` : '';

                    if (deptHeadRemarks || hrRemarks || hrInstruction) {
                        remarksDiv.innerHTML = deptHeadRemarks + hrRemarks + hrInstruction;
                    } else {
                        remarksDiv.innerHTML = '<p class="text-muted mb-0">No remarks available.</p>';
                    }

                    const remarksModal = new bootstrap.Modal(document.getElementById('viewRemarksModal'));
                    remarksModal.show();
                } else {
                    toastr.warning(data.message || 'No remarks found.', 'Notice');
                }
            })
            .catch(error => {
                console.error('Error fetching remarks:', error);
                toastr.error('Failed to load remarks. Please try again.', 'Error');
            });
    } 
    function viewAssetRemarks(assetId) {
            $('#asset_remarks_modal_' + assetId).modal('show'); 
    } 
  function saveAssetRemarks(assetId) {
        const remarkInput = document.getElementById('remarkText' + assetId);
        const remark = remarkInput.value.trim();

        if (!remark) {
            alert('Please enter a remark.');
            return;
        }

        $.ajax({
            url: 'assets/hr/remarks/save',
            method: 'POST',
            data: {
                asset_id: assetId,
                condition_remarks: remark,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
              
                remarkInput.value = '';
                $('#remarksContainer' + assetId).html(response.html); 
                const remarksChat = document.querySelector(
                    '#remarksContainer' + assetId + ' .remarks-chat'
                ); 
                if (remarksChat) {
                        requestAnimationFrame(() => {
                            remarksChat.scrollTo({
                                top: remarksChat.scrollHeight,
                                behavior: 'smooth'
                            });
                        });
                }

            },
            error: function (xhr) {
                alert('Error saving remark.');
                console.log(xhr);
            }
        });
    } 

document.addEventListener('DOMContentLoaded', function () {
    const confirmModal = document.getElementById('confirmClearModal');
    const resignationIdInput = document.getElementById('resignationIdToClear');

    confirmModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const resignationId = button.getAttribute('data-id');
        resignationIdInput.value = resignationId;
      
    }); 
    document.getElementById('confirmClearBtn').addEventListener('click', function () {
        const resignationId = resignationIdInput.value;
        
        $.ajax({
            url: `/resignation/mark-cleared/${resignationId}`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            beforeSend: function () {
                toastr.info('Processing clearance...', 'Please wait', { timeOut: 2000 });
            },
            success: function (response) {
                const modal = bootstrap.Modal.getInstance(confirmModal);
                modal.hide();

                if (response.success) {
                    toastr.success(response.message || 'Resignation cleared successfully.', 'Success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.warning(response.message || 'Something went wrong.');
                }
            },
            error: function (xhr) {
                toastr.error('An error occurred while marking as cleared.', 'Error');
            }
        });
    });
}); 

</script> 
      @include('layout.partials.footer-company') 

    </div>  

    @component('components.modal-popup')
    @endcomponent

@endsection
