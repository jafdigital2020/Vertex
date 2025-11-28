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
 
    .chat-right { position: ;
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
    .bs-bullet-list {
    counter-reset: item;
    padding-left: 0;
    list-style: none;
    }

    .bs-bullet-list li {
        counter-increment: item;
        position: relative;
        padding-left: 22px; padding: ;
        margin-bottom: 6px;
        font-size: 14px;
    }

    .bs-bullet-list li::before {
        content: "•";  
        position: absolute;
        left: 0;
        top: 2px;
        font-size: 18px;
        line-height: 1;
        color: #6c757d; 
    }

   </style>
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Resignation HR</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Resignation
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Resignation HR</li>
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
                                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3"> 
                                    <div class="me-3">
                                        <div class="input-icon-end position-relative">
                                            <input type="text" class="form-control date-range bookingrange-filtered"
                                                placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                            <span class="input-icon-addon">
                                                <i class="ti ti-chevron-down"></i>
                                            </span>
                                        </div>
                                    </div>
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
                                        <select name="status_filter" id="status_filter" class="select2 form-select" style="width:150px;"
                                            oninput="filter()">
                                            <option value="" selected>All Status</option>  
                                            <option value="1">For Acceptance</option>
                                            <option value="3">For Clearance</option>
                                            <option value="4">Rendering</option> 
                                            <option value="5">Resigned</option> 
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">

                            <div class="custom-datatable-filter table-responsive">
                                <table class="table datatable" id="resignationHRTable">
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
                                            <th class="text-center">Remarks</th>
                                            <th class="text-center">HR Attachment</th>
                                            <th class="text-center">Status</th> 
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resignationHRTableBody">  
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
                                                        if ($resignation->accepted_date !== null) { 
                                                            $today = \Carbon\Carbon::today(); 
                                                            $acceptedDate = \Carbon\Carbon::parse($resignation->accepted_date)->startOfDay(); 
                                                            $remainingDays = ($resignation->added_rendering_days ?? 0) - $acceptedDate->diffInDays($today); 
                                                            $remainingDays = $remainingDays < 0 ? 0 : $remainingDays;
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
                                                        0 days left
                                                    @endif
                                                    @if($resignation->resignation_date !== null)
                                                    <button class="btn btn-sm btn-primary add-rendering-days-btn" 
                                                            data-id="{{ $resignation->id }}" 
                                                            data-remaining="{{ $remainingDays }}">
                                                        <i class="fa fa-plus"></i>
                                                    </button> 
                                                    @endif
                                                </td>

                                                <td>{{$resignation->resignation_date ?? '-'}}</td>
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
                                                @if ($resignation->hrResignationAttachments->isNotEmpty())
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewHrAttachmentsModal-{{ $resignation->id }}">
                                                        View <i class="fa fa-file"></i>
                                                    </button> 
                                                    <div class="modal fade" id="viewHrAttachmentsModal-{{ $resignation->id }}" tabindex="-1" aria-labelledby="viewHrAttachmentsModalLabel-{{ $resignation->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-md">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="viewHrAttachmentsModalLabel-{{ $resignation->id }}">HR Attachments</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>

                                                                <form id="uploadHrAttachmentsForm-{{ $resignation->id }}" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                                            <table class="table table-sm table-bordered align-middle">
                                                                                <thead class="table-light">
                                                                                    <tr>
                                                                                        <th class="text-center" style="width: 10%;">No.</th>
                                                                                        <th class="text-center">Uploaded Attachment</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="hrAttachmentsTableBody-{{ $resignation->id }}">
                                                                                      @forelse ($resignation->hrResignationAttachments as $attachment)
                                                                                    <tr class="text-center">
                                                                                        <td>{{ $loop->iteration }}</td>
                                                                                        <td style="max-width: 300px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; vertical-align: middle;">
                                                                                            <a href="{{ asset('storage/resignation_attachments/' . basename( $attachment->filename)) }}"
                                                                                            target="_blank"
                                                                                            style="display: inline-block; width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 10px;"
                                                                                            title="{{  $attachment->filename }}">
                                                                                                <i class="bi bi-file-earmark-text me-1 text-secondary"></i>
                                                                                                {{ basename( $attachment->filename) }}
                                                                                            </a>
                                                                                        </td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr>
                                                                                        <td colspan="2" class="text-center text-muted">No attachments uploaded yet.</td>
                                                                                    </tr>
                                                                                @endforelse
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        @if($resignation->cleared_status !== 1 )
                                                                        <div class="mt-4 text-start">
                                                                            <label for="hr_resignation_attachment_{{ $resignation->id }}" class="form-label fw-bold">
                                                                                Upload Additional Attachment  <span class="text-danger">*</span>
                                                                            </label>
                                                                            <input type="file"
                                                                            name="hr_resignation_attachment[]"
                                                                            id="hr_resignation_attachment_{{ $resignation->id }}"
                                                                            class="form-control"
                                                                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*"
                                                                            multiple> 
                                                                            <small class="text-muted d-block mt-1 text-start">You can upload multiple PDF, DOC, DOCX or image files.</small>
                                                                        </div>
                                                                        @endif
                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                                                                        @if($resignation->cleared_status !== 1 )
                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i class="bi bi-upload me-1"></i> Upload
                                                                        </button>
                                                                        @endif
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @else
                                                    -
                                                    @endif
                                                </td> 
                                                <td>
                                                    @if($resignation->status === 1 && $resignation->accepted_date === null )
                                                    <span>For Acceptance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 0)
                                                    <span>For Clearance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 && $remainingDays > 0 )
                                                    <span>Rendering</span> 
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 && $remainingDays <= 0 )
                                                    <span>Resigned</span> 
                                                    @endif
                                                </td>
                                             
                                                <td> 
                                                        @if($resignation->status === 1 && $resignation->accepted_by === null)
                                                        <button class="btn btn-primary btn-sm" onclick="openAcceptanceModal({{ $resignation->id }}, 'accept')">
                                                            Accept <i class="bi bi-hand-thumbs-up ms-1"></i>  
                                                        </button> 

                                                        @elseif( $resignation->status === 1 && $resignation->accepted_by !== null && $resignation->cleared_status === 0   ) 
    
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

                                                                        <form action="{{ route('resignation.attachments.updateStatuses', $resignation->id) }}" id="updateStatusesForm" method="POST">
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
                                                                                                        <th class="text-center" style="width: 1%;">No.</th>
                                                                                                        <th class="text-center" style="width: 5%;">Uploaded File</th>
                                                                                                        <th class="text-center" style="width:1%">Remarks</th>
                                                                                                        <th class="text-center" style="width: 10%" >Status</th>
                                                                                                    </tr>
                                                                                                </thead>
                                                                                                <tbody>
                                                                                                    @foreach ($employeeAttachments as $index => $file)
                                                                                                        <tr class="text-xs">
                                                                                                            <td  style="font-size: 11px;">{{ $loop->iteration }}</td> 
                                                                                                            <td style="max-width: 100px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; vertical-align: middle;">
                                                                                                                <a href="{{ asset('storage/resignation_attachments/' . basename($file->filename)) }}"
                                                                                                                target="_blank"
                                                                                                                style="display: inline-block; width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 10px;"
                                                                                                                title="{{ $file->filename }}">
                                                                                                                    <i class="bi bi-file-earmark-text me-1 text-secondary"></i>
                                                                                                                    {{ basename($file->filename) }}
                                                                                                                </a>
                                                                                                            </td> 
                                                                                                        <td class="text-center">
                                                                                                             <button type="button"
                                                                                                                    class="btn btn-xs btn-primary position-relative"
                                                                                                                    onclick="viewResignationAttachmentRemarks('{{ $file->id }}')">
                                                                                                                <i class="fa fa-sticky-note"></i>

                                                                                                                @if($file->remarks->where('remarks_from_role','Employee')->where('is_read', false)->count() > 0)
                                                                                                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                                                                                                        <span class="visually-hidden">New</span>
                                                                                                                    </span>
                                                                                                                @endif
                                                                                                            </button>

                                                                                                            {{-- Modal --}}
                                                                                                            <div class="modal fade" id="remarks_modal_{{ $file->id }}" tabindex="-1">
                                                                                                                <div class="modal-dialog modal-dialog-centered modal-md">
                                                                                                                    <div class="modal-content">
                                                                                                                     <div class="modal-header">
                                                                                                                        <h5 class="modal-title text-sm" title="{{ basename($file->filename) }}">
                                                                                                                            Remarks - {{ \Illuminate\Support\Str::limit(basename($file->filename), 20, '...') }}
                                                                                                                        </h5>
                                                                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                                                    </div> 
                                                                                                                        <div class="modal-body">
                                                                                                                            <div id="remarksContainer{{ $file->id }}"
                                                                                                                                class="p-2 border rounded"
                                                                                                                                style="max-height: 300px; overflow-y: auto; background: #f9f9f9;">
                                                                                                                                 @if ($file->remarks->count()) 
                                                                                                                                    @php
                                                                                                                                            $hasRemarks = false;
                                                                                                                                        @endphp 
                                                                                                                                        <div class="remarks-chat p-2 border rounded" style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;">
                                                                                                                                            @foreach ($file->remarks as $remark) 
                                                                                                                                                @php $hasRemarks = true; @endphp
                                                                                                                                                <div class="d-flex mb-3 {{ $remark->remarks_from_role === 'Employee' ? 'justify-content-start' : 'justify-content-end' }}">
                                                                                                                                                    <div class="chat-bubble col-9
                                                                                                                                                                {{ $remark->remarks_from_role === 'HR' ? 'chat-left' : 'chat-right' }}">
                                                                                                                                                        <strong class="small text-muted d-block mb-1">
                                                                                                                                                        {{ $remark->remarks_from_role  === 'HR' 
                                                                                                                                                            ? 'HR' 
                                                                                                                                                            : optional($remark->personalInformation)->first_name . ' ' . optional($remark->personalInformation)->last_name }}

                                                                                                                                                        </strong>
                                                                                                                                                        <span class="d-block">{{ $remark->remarks }}</span>
                                                                                                                                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                                                                                                                                            {{ $remark->created_at->format('M d, Y h:i A') }}
                                                                                                                                                        </small>
                                                                                                                                                    </div>
                                                                                                                                                </div> 
                                                                                                                                                @endforeach  
                                                                                                                                        @if (!$hasRemarks)
                                                                                                                                            <p class="text-muted mb-0">No remarks yet.</p>
                                                                                                                                        @endif
                                                                                                                                        </div> 
                                                                                                                                    @else
                                                                                                                                        <p class="text-muted">No remarks yet.</p>
                                                                                                                                    @endif   
                                                                                                                            </div>

                                                                                                                            <div class="form-group mt-3">
                                                                                                                                <label class="fw-bold">Add Remark:</label>
                                                                                                                                <textarea class="form-control myTextarea"
                                                                                                                                        rows="3"
                                                                                                                                        id="remarkText{{ $file->id }}"
                                                                                                                                        placeholder="Write your remark here..."></textarea>
                                                                                                                            </div>
                                                                                                                        </div>

                                                                                                                        <div class="modal-footer">
                                                                                                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                                                                                                                            <button type="button" class="btn btn-primary"
                                                                                                                                    onclick="saveResignationAttachmentRemark({{ $file->id }})">
                                                                                                                                Send
                                                                                                                            </button>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                        <td class="text-center">
                                                                                                          <select name="statuses[{{ $file->id }}]" 
                                                                                                                    class="form-select form-select-sm text-center p-1"
                                                                                                                    style="font-size: 11px;">
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
                                                                            <button class="btn btn-primary"  type="button" id="btnUpdateStatuses">
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
                                                                     <form action="{{ route('resignation.assets.receive') }}" method="POST">
                                                                      @csrf 
                                                                    <div class="modal-body"> 
                                                                    <div class="mb-3"> 
                                                                  
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
                                                                                    <td class="text-start">{{ $asset->assets->name }} {{ isset($asset->order_no) ? 'Item No. ' . $asset->order_no : '' }}</td> 
                                                                                    <td>
                                                                                        <select name="condition[{{ $asset->id }}]"
                                                                                                class="form-select form-select-sm asset-condition"
                                                                                                data-id="{{ $asset->id }}" 
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
                                                                                        class="btn btn-xs btn-primary position-relative"
                                                                                        id="showAssetBTN-{{ $asset->id }}-{{ $asset->order_no }}"
                                                                                        onclick="viewAssetRemarks('{{ $asset->id }}', '{{ $asset->order_no }}')">
                                                                                        <i class="fa fa-sticky-note"></i>

                                                                                        @if($asset->remarks->where('remarks_from', 'Employee')->where('is_read', false)->count() > 0)
                                                                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                                                                                <span class="visually-hidden">New</span>
                                                                                            </span>
                                                                                        @endif
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
                                                                                                       @php
                                                                                                            $hasRemarks = false;
                                                                                                        @endphp 
                                                                                                        <div class="remarks-chat p-2 border rounded" style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;">
                                                                                                            @foreach ($asset->remarks as $remark)
                                                                                                             @if ($asset->order_no == $remark->item_no)
                                                                                                                @php $hasRemarks = true; @endphp
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
                                                                                                           @endif
                                                                                                        @endforeach  
                                                                                                        @if (! $hasRemarks)
                                                                                                            <p class="text-muted mb-0">No remarks yet.</p>
                                                                                                        @endif
                                                                                                        </div> 
                                                                                                    @else
                                                                                                        <p class="text-muted">No remarks yet.</p>
                                                                                                    @endif  
                                                                                                     </div> 
                                                                                                    @if($asset->status != 'Available')
                                                                                                  
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
                                                                    </form>
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
                                                     
                                                @elseif($resignation->status === 1 && $resignation->accepted_by !== null && $resignation->cleared_status === 1) 
                                                    <button class="btn btn-sm btn-danger" onclick="openUndoClearModal('{{ $resignation->id }}')">
                                                        Undo Clearance
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
  
                    <div id="resignationReasonContainer" class="mb-4 text-start d-none">
                        <h6 class="fw-bold">Reason for Resignation:</h6>
                        <p id="resignationReasonText" class="border rounded p-2 bg-light"></p>
                    </div>  
                    <iframe id="resignationPreviewFrame" src="" style="width:100%;height:80vh;border:none;display:none;"></iframe> 
                    <div id="resignationWordNotice" class="d-none">
                        <p>This file cannot be previewed directly. Click below to open it in Office viewer:</p>
                        <a id="resignationWordLink" href="#" target="_blank" class="btn btn-primary">Open Document</a>
                    </div>

                </div>
            </div>
        </div>
    </div> 
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
                        <label class="form-label fw-bold">Resignation Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="resignation_date" id="resignation_date" required>
                    </div>

                    <div class="mb-3">
                        <label for="accept_remarks" class="form-label fw-bold">Remarks <span class="text-danger">*</span></label>
                        <textarea id="accept_remarks" name="accepted_remarks" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="accept_instruction" class="form-label fw-bold">Instruction</label>
                        <textarea id="accept_instruction" name="accepted_instruction" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Attachment <span class="text-danger">*</span></label>
                        <input 
                            type="file" 
                            name="resignation_attachment[]" 
                            id="resignation_attachment" 
                            class="form-control" 
                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*"
                            multiple
                        > 
                        <small class="text-muted">You can upload multiple files (PDF, DOC, DOCX,IMAGES only).</small>
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
               <ol class="mb-2 bs-bullet-list">
                    <li>All employee <strong>assets</strong> received have been verified and will be marked as <strong>Available</strong>.</li>
                    <li>All remaining <strong>pending</strong> attachment validations will be automatically marked as <strong>Approved</strong>.</li>
                </ol> 
                <p class="mb-0 text-danger text-center fw-semibold">
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
<div class="modal fade" id="undoClearModal" tabindex="-1" aria-labelledby="undoClearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header  ">
                <h5 class="modal-title" id="undoClearModalLabel">Undo Clearance Confirmation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="mb-2">
                    You are about to <strong>undo</strong> the clearance for this resignation.
                </p>
                <ol class="mb-2 bs-bullet-list">
                    <li>The employee’s clearance status will be reverted to <strong>For Clearance</strong>.</li>
                    <li>Assets and validations previously marked as cleared will remain unchanged.</li>
                </ol>
                <p class="mb-0 text-danger text-center fw-semibold">
                    Are you sure you want to continue?
                </p>
            </div> 
            <div class="modal-footer">
                <input type="hidden" id="resignationIdToUndo">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmUndoBtn">
                    <i class="fa fa-undo me-1"></i> Yes, Undo Clearance
                </button>
            </div>
        </div>
    </div>
</div> 
<div class="modal fade" id="modalAddRenderingDays" tabindex="-1" aria-labelledby="modalAddRenderingDaysLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAddRenderingDays">
      @csrf
      <input type="hidden" name="resignation_id" id="resignation_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddRenderingDaysLabel">Add Remaining Days</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body"> 
          <div class="mb-3">
            <label for="current_remaining_days" class="form-label">Current Remaining Days</label>
            <input type="number" class="form-control" id="current_remaining_days" readonly>
          </div> 
          <div class="mb-3">
            <label for="extra_days" class="form-label">Extra Days to Add</label>
            <input type="number" min="1" class="form-control" name="extra_days" id="extra_days" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Days</button>
        </div>
      </div>
    </form>
  </div>
</div>



  @push('scripts')

    <script> 
  
     if ($('.bookingrange-filtered').length > 0) {

            var start = moment().subtract(29, 'days');
            var end = moment();

            function booking_range(start, end) {
                $('.bookingrange-filtered span').html(start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'));
            }

            $('.bookingrange-filtered').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Next Year': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                }
            }, booking_range);

            booking_range(start, end);
        }

        $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
            filter();
        });

    function filter() {
 
            const dateRange = $('#dateRange_filter').val();
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('resignation-hr-filter') }}',
                type: 'GET',
                data: {
                    branch,
                    department,
                    designation,
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#resignationHRTable').DataTable().destroy();
                        $('#resignationHRTableBody').html(response.html);
                        $('#resignationHRTable').DataTable();
                        
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

    function viewResignationFile(fileUrl, reason) {
        const fileExtension = fileUrl.split('.').pop().toLowerCase();
        const iframe = document.getElementById('resignationPreviewFrame');
        const wordNotice = document.getElementById('resignationWordNotice');
        const wordLink = document.getElementById('resignationWordLink');
        const reasonContainer = document.getElementById('resignationReasonContainer');
        const reasonText = document.getElementById('resignationReasonText');
 
        iframe.style.display = 'none';
        wordNotice.classList.add('d-none');
 
        if (fileExtension === 'pdf') {
            iframe.src = fileUrl;
            iframe.style.display = 'block';
            wordNotice.classList.add('d-none');  
            const existingImg = document.getElementById('image-preview');
            if (existingImg) existingImg.remove();
        } else if (fileExtension === 'doc' || fileExtension === 'docx') {
            const existingImg = document.getElementById('image-preview');
            if (existingImg) existingImg.remove();
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `
                <p>This file cannot be previewed directly. Click below to open it in a new tab:</p>
                <a href="${fileUrl}" target="_blank" class="btn btn-primary">Open Document</a>
            `;
            iframe.style.display = 'none';
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
            const imgPreview = document.createElement('img');
            imgPreview.src = fileUrl;
            imgPreview.style.maxWidth = '100%';
            imgPreview.style.height = 'auto';
            wordNotice.classList.add('d-none');  
            iframe.style.display = 'none'; 
            const existingImg = document.getElementById('image-preview');
            if (existingImg) existingImg.remove();
            imgPreview.id = 'image-preview';
            iframe.parentNode.insertBefore(imgPreview, iframe.nextSibling);
        } else {
            const existingImg = document.getElementById('image-preview');
            if (existingImg) existingImg.remove();
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `<p>Unsupported file format. <a href="${fileUrl}" target="_blank">Download file</a></p>`;
            iframe.style.display = 'none';
            
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
    function openApprovalModal(resignationId, action) {
        const modalTitle = document.getElementById('approvalModalTitle');
        const submitBtn = document.getElementById('submitApprovalBtn');
        const actionInput = document.getElementById('approvalAction');
        const remarksField = document.getElementById('status_remarks');
 
        document.getElementById('resignationId').value = resignationId;
        actionInput.value = action;
        remarksField.value = '';  
 
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
            const acceptedRemarks = document.getElementById('accept_remarks');
            const acceptedInstruction = document.getElementById('accept_instruction');
            const resignationDate = document.getElementById('resignation_date');

            if (!resignationDate.value.trim()) {
                toastr.error('Please select a resignation date.', 'Warning');
                resignationDate.focus();
                return;
            }
            if (!acceptedRemarks.value.trim()) {
                toastr.error('Please enter remarks.', 'Warning');
                acceptedRemarks.focus();
                return;
            }
  
            const formData = new FormData(form);
    
            const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
            const files = form.querySelectorAll('input[type="file"]');
            for (let input of files) {
                for (let file of input.files) {
                    const fileExt = file.name.split('.').pop().toLowerCase();
                    if (!allowedExtensions.includes(fileExt)) {
                        toastr.error(`Invalid file type: ${file.name}. Only PDF, DOC, and DOCX files are allowed.`, 'Warning');
                        return;
                    }
                }
            } 
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
                    toastr.error('Server returned an unexpected response. Please check console.', 'Error');
                    return;
                }

                if (data.success) {
                    toastr.success('Resignation successfully accepted by HR.', 'Success');
                    filter();
                    $('#acceptResignationModal').modal('hide');
                } else {
                    toastr.error(data.message || 'Something went wrong.', 'Error');
                }
            } catch (error) {
                console.error('Error:', error);
                toastr.error('An unexpected error occurred. Please try again.', 'Error');
            }
        });
    });
 
    $(document).ready(function () {
        // Delegated submit handler - works for dynamically inserted forms
        $(document).on('submit', 'form[action="{{ route('resignation.assets.receive') }}"]', function (e) {
            e.preventDefault();

            const form = $(this);
            const formData = form.serialize(); // use FormData if you have file inputs

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                beforeSend: function () {
                    form.find('button[type="submit"]').prop('disabled', true).text('Submitting...');
                },
                success: function (response) {
                    toastr.success('Assets status and condition successfully updated!', 'Success');
                    $('.modal').modal('hide');
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Error submitting assets. Please try again.');
                },
                complete: function () {
                    form.find('button[type="submit"]').prop('disabled', false).text('Submit');
                }
            });
        });
    });

    $(document).ready(function() {
        $('form[id^="uploadHrAttachmentsForm-"]').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const resignationId = form.attr('id').split('-')[1];
            const fileInput = form.find('input[type="file"]')[0]; 
            const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
 
            for (let file of fileInput.files) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(ext)) {
                    toastr.warning(`Invalid file type: ${file.name}. Only PDF, DOC,DOCX and images are allowed.`, 'Warning');
                    return;
                }
            }
 
            if (!fileInput.files.length) {
                toastr.error('Please select at least one file to upload.', 'Warning');
                return;
            }

            const formData = new FormData(form[0]);

            $.ajax({
                url: `/api/resignation/hr-attachments/${resignationId}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        toastr.success('Attachments uploaded successfully.', 'Success'); 
                        $(`#hrAttachmentsTableBody-${resignationId}`).html(response.html); 
                        form[0].reset();
                    } else {
                        toastr.error(response.message || 'Something went wrong.', 'Error');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Failed to upload attachments. Please try again.', 'Error');
                }
            });
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

    
     function viewAssetRemarks(assetId, itemNo) { 
        $('#asset_remarks_modal_' + assetId).modal('show');
 
        $.ajax({
            url: `/api/asset-remarks/hr/mark-as-read/${assetId}/${itemNo}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) { 
                const btn = $(`#showAssetBTN-${assetId}-${itemNo}`);
                btn.find('.bg-danger').remove();
            },
            error: function(xhr) {
                console.error('Error marking remarks as read:', xhr);
            }
        });
    }

    function saveAssetRemarks(assetId) {
            const remarkInput = document.getElementById('remarkText' + assetId);
            const remark = remarkInput.value.trim();

            if (!remark) {
                alert('Please enter a remark.');
                return;
            }

            $.ajax({
                url: '/api/resignation/assets/hr/remarks/save',
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
    
       $(document).ready(function () { 
        let originalStatuses = {};

        $('select[name^="statuses"]').each(function () {
            const id = $(this).attr('name').match(/\d+/)[0];
            originalStatuses[id] = $(this).val();
        }); 
            $(document).on('click', '#btnUpdateStatuses', function(e) {
            e.preventDefault();
 
            let form = $('#updateStatusesForm'); 
            let url = form.attr('action'); 
            let changedData = {};
 
            $('select[name^="statuses"]').each(function () {
                const id = $(this).attr('name').match(/\d+/)[0];
                const newVal = $(this).val();
                if (newVal !== originalStatuses[id]) {
                    changedData[id] = newVal;
                }
            });

            if ($.isEmptyObject(changedData)) {
                toastr.info('No changes detected.');
                return;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    statuses: changedData
                },
                success: function (response) {
                    toastr.success('Statuses updated successfully!');
                    @if(isset($resignation))
                        $('#uploadAttachmentsModal-{{ $resignation->id }}').modal('hide');
                    @endif 
                },
                error: function (xhr) {
                    toastr.error('Something went wrong while updating.');
                    console.error(xhr.responseText);
                }
            });
        });
    }); 
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
                url: `/api/resignation/mark-cleared/${resignationId}`,
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
                        filter();
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
        function viewResignationAttachmentRemarks(id) {
            $('#remarks_modal_' + id).modal('show'); 

            $.ajax({
                url: '/api/resignation/remarks/mark-as-read/' + id,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log(response.message);
                    $('button[onclick="viewResignationAttachmentRemarks(\'' + id + '\')"] .bg-danger').remove();
                },
                error: function(xhr) {
                    console.error('Failed to mark remarks as read.');
                }
            });
        }

        function saveResignationAttachmentRemark(id) {
            let remarks = $('#remarkText' + id).val();

            if (!remarks.trim()) {
                alert('Please enter a remark.');
                return;
            } 
            $.ajax({
                url: '/api/resignation-attachments/' + id + '/remarks',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { remarks },
                success: function (response) { 
                    $('#remarksContainer' + id).html(response.html);
                    $('#remarkText' + id).val('');  
                    const remarksChat = document.querySelector(
                        '#remarksContainer' + id + ' .remarks-chat'
                    ); 

                    if (remarksChat) {
                        requestAnimationFrame(() => {
                            remarksChat.scrollTo({
                                top: remarksChat.scrollHeight,
                                behavior: 'smooth'
                            });
                        });
                    }
                }
            });
        }
      
        function openUndoClearModal(resignationId) { 
            document.getElementById('resignationIdToUndo').value = resignationId; 
            const modal = new bootstrap.Modal(document.getElementById('undoClearModal'));
            modal.show();
        }
 

        $('#confirmUndoBtn').on('click', function() {
            const resignationId = $('#resignationIdToUndo').val();

            const btn = $(this);
            btn.prop('disabled', true);
            btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Undoing...');

            $.ajax({
                url: '/api/resignations/' + resignationId + '/undo-clearance',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) { 
                    const modalEl = document.getElementById('undoClearModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
  
                    toastr.success(response.message ||'The clearance status has been successfully undone.', 'Success');
                    filter();
                },
                error: function(xhr) { 
                    toastr.error( xhr.responseJSON?.message , 'Error');
                },
                complete: function() {
                    btn.prop('disabled', false);
                    btn.html('<i class="fa fa-undo me-1"></i> Yes, Undo Clearance');
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

                $.get('/api/resignation/filter-from-branch', {
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

                $.get('/api/resignation/filter-from-department', {
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

                $.get('/api/resignation/filter-from-designation', {
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
        $(document).ready(function() { 

             $(document).on('click', '.add-rendering-days-btn', function() {
                let resignationId = $(this).data('id');
                let remainingDays = $(this).data('remaining');

                $('#resignation_id').val(resignationId);
                $('#current_remaining_days').val(remainingDays);
                $('#extra_days').val('');

                $('#modalAddRenderingDays').modal('show');
            }); 
            $('#formAddRenderingDays').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    url: "/api/resignations/add-days",
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            filter();
                            $('#modalAddRenderingDays').modal('hide');
                        }
                    },
                    error: function(xhr) {
                         toastr.success(xhr.responseJSON.message || 'Error occurred');
                    }
                });
            });
        });
   </script>   
    @endpush
    @include('layout.partials.footer-company') 

</div>  

@component('components.modal-popup')
@endcomponent

@endsection
