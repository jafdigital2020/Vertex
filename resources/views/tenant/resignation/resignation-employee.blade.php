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
 
    .chat-left {
        background-color: #f1f1f1;
        color: #333;
        text-align: left;
        border-top-left-radius: 0;
    }
 
    .chat-right {
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
                    <h2 class="mb-1">Resignation Employee</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Resignation
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Resignation Employee</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Create', $permission))
                    <div class="mb-2">
                        <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#upload_resignation"><i class="ti ti-circle-plus me-2"></i>Upload Resignation Letter</a>
                    </div>
                    @endif
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
                                        <tr> 
                                            <th class="text-center">Date Filed</th> 
                                            <th class="text-center">Resignation Letter</th> 
                                            <th class="text-center">Date Accepted</th>
                                            <th class="text-center">Remaining Days</th>
                                            <th class="text-center">Resignation Date</th>  
                                            <th class="text-center">Remarks</th>
                                            <th class="text-center">HR Attachments</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        @foreach ($resignations as $resignation)
                                            <tr class="text-center">
                                                <td>{{$resignation->date_filed}}</td> 
                                               <td>
                                                    <button 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="viewResignationFile('{{ asset('storage/' . $resignation->resignation_file) }}','{{$resignation->reason ?? ''}}')">
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
                                                        0 days left
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
                                                                <div class="modal-body">
                                                                   <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                                        <table class="table table-sm table-bordered align-middle">
                                                                            <thead class="table-light">
                                                                                <tr class="text-center">
                                                                                    <th class="text-center" style="width: 10%;">No.</th>
                                                                                    <th class="text-center">Uploaded Attachment</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
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

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> 
                                                    @else
                                                    -
                                                    @endif
                                                </td> 
                                                 <td>
                                                    @if($resignation->status === 0) 
                                                    <span>For Approval</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date === null )
                                                    <span>For Acceptance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 0 )
                                                    <span>For Clearance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 && $remainingDays > 0 )
                                                    <span>Rendering</span> 
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 && $remainingDays <= 0 )
                                                    <span>Resigned</span> 
                                                    @elseif($resignation->status === 2)
                                                    <span>Rejected</span>
                                                    @endif
                                                </td> 
                                                <td>
                                                    @if($resignation->status === 0)
                                                    <div class="action-icon d-inline-flex text-center">
                                                    @if (in_array('Update', $permission))
                                                      <a href="javascript:void(0);" 
                                                        class="btn-edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#edit_resignation_modal"
                                                        data-id="{{ $resignation->id }}"
                                                        data-reason="{{ $resignation->reason }}"
                                                        data-file="{{ asset('storage/' . $resignation->resignation_file) }}"
                                                        title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                       </a> 
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                            data-bs-target="#delete_resignation_modal" data-id="{{ $resignation->id }}" 
                                                            title="Delete"><i class="ti ti-trash"></i></a>
                                                  
                                                    @endif
                                                    </div>
                                                    @endif
                                                    @if ($resignation->status === 1 && $resignation->accepted_date !== null && $resignation->cleared_status == 0 )
                                                        <div class="action-icon d-inline-flex text-center">  
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-primary me-2"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#uploadAttachmentsModal-{{ $resignation->id }}">
                                                                <i class="bi bi-upload "></i>  
                                                            </button> 
                                                            <div class="modal fade" id="uploadAttachmentsModal-{{ $resignation->id }}" tabindex="-1" aria-labelledby="uploadAttachmentsModalLabel-{{ $resignation->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered modal-md">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="uploadAttachmentsModalLabel-{{ $resignation->id }}">
                                                                                My Uploaded Attachments
                                                                            </h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>

                                                                        <div class="modal-body">  
                                                                            @php
                                                                                $myUploads = $resignation->resignationAttachment
                                                                                    ->where('uploader_role', 'employee');
                                                                            @endphp 
                                                                       <div class="mb-4"> 
                                                                               <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                                                <table class="table table-xs table-bordered table-striped align-middle shadow-sm mb-0">
                                                                                    <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                                                                        <tr>
                                                                                            <th class="text-center" style="width: 1%;">No.</th>
                                                                                            <th class="text-center" style="width: 5%;">Uploaded File</th>
                                                                                            <th class="text-center" style="width:1%">Remarks</th>
                                                                                            <th class="text-center" style="width: 10%" >Status</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody id="myUploadsSection">
                                                                                        @php
                                                                                        $counter = 1;
                                                                                        @endphp
                                                                                        @forelse ($myUploads as $index => $file)
                                                                                            <tr class="text-xs">
                                                                                            <td  class="text-center text-xs" >{{  $counter++ }}</td>
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
                                                                                                                    class="btn btn-xs btn-primary"
                                                                                                                    onclick="viewResignationAttachmentRemarks('{{ $file->id }}')">
                                                                                                                <i class="fa fa-sticky-note"></i>
                                                                                                            </button> 
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
                                                                                                                                                <div class="d-flex mb-3 {{ $remark->remarks_from_role === 'HR' ? 'justify-content-start' : 'justify-content-end' }}">
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
                                                                                                        
                                                                                                <td  class="text-center" >
                                                                                                    @if ($file->status === 'approved')
                                                                                                        <span class="badge bg-success">Approved</span>
                                                                                                    @elseif ($file->status === 'pending')
                                                                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                                                                    @elseif ($file->status === 'rejected')
                                                                                                        <span class="badge bg-danger">Rejected</span>
                                                                                                    @else
                                                                                                        <span class="badge bg-secondary">Unknown</span>
                                                                                                    @endif
                                                                                                </td>
                                                                                            </tr>
                                                                                        @empty
                                                                                            <tr>
                                                                                                <td colspan="4" class="text-center text-muted">No attachments uploaded yet.</td>
                                                                                            </tr>
                                                                                        @endforelse
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
 
                                                                        </div> 
                                                                          <form class="uploadAttachmentForm" 
                                                                                action="{{ url('api/resignation/upload/' . $resignation->id) }}" 
                                                                                method="POST" 
                                                                                enctype="multipart/form-data">
                                                                                @csrf 

                                                                                <div class="mb-3 text-start">
                                                                                    <label for="attachments-{{ $resignation->id }}" class="form-label fw-bold">
                                                                                        Upload New Files <span class="text-danger">*</span>
                                                                                    </label>
                                                                                  <input 
                                                                                        type="file" 
                                                                                        name="attachments[]" 
                                                                                        id="attachments-{{ $resignation->id }}" 
                                                                                        class="form-control" 
                                                                                        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                                                                        multiple
                                                                                    > 
                                                                                    <small class="text-muted">You can upload multiple PDF or Word files.</small>
                                                                                </div> 

                                                                                <div class="text-end">
                                                                                    <button type="submit" class="btn btn-primary">
                                                                                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload Files
                                                                                    </button>
                                                                                </div>
                                                                            </form> 
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div> 
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-primary"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#returnAssetsModal-{{ $resignation->id }}">
                                                                <i class="bi bi-box-arrow-in-down"></i>  
                                                            </button> 
                                                            <div class="modal fade" id="returnAssetsModal-{{ $resignation->id }}" tabindex="-1" aria-labelledby="returnAssetsModalLabel-{{ $resignation->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content"> 
                                                                    <div class="modal-header ">
                                                                        <h5 class="modal-title" id="returnAssetsModalLabel-{{ $resignation->id }}"> 
                                                                            Return Assets
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div> 
                                                                        <div class="modal-body"> 
                                                                        <div class="mb-3"> 
                                                                <form id="employeeAssetsForm" action="{{ route('resignation.assets.return') }}" method="POST">
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
                                                                                     <td class="text-start">
                                                                                        {{ $asset->assets->name }} 
                                                                                        {{ $asset->order_no ? ' Item No. ' . $asset->order_no : '' }}
                                                                                    </td>


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
                                                                                                                <div class="d-flex mb-3 {{ $remark->remarks_from === 'HR' ? 'justify-content-start' : 'justify-content-end' }}">
                                                                                                                    <div class="chat-bubble col-9 {{ $remark->remarks_from === 'HR' ? 'chat-left' : 'chat-right' }}">
                                                                                                                        <strong class="small text-muted d-block mb-1">
                                                                                                                            {{ $remark->remarks_from === 'HR' ? 'HR' : 'Employee' }}
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
                                                                                                        <p class="text-muted mb-0">No remarks yet.</p>
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
                                                                                        @if($asset->status == 'Available')
                                                                                               <span class="badge bg-success">Received</span>
                                                                                        @else
                                                                                            <select name="status[{{ $asset->id }}]"
                                                                                                class="form-select form-select-sm asset-status"
                                                                                                data-id="{{ $asset->id }}"
                                                                                                required>
                                                                                            <option value="">Select</option>
                                                                                            <option value="Return" {{ $asset->status == 'Return' ? 'selected' : '' }}>Return</option>
                                                                                            <option value="Deployed" {{ $asset->status == 'Deployed' ? 'selected' : '' }}>Deployed</option>
                                                                                            </select>
                                                                                        @endif
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
                                                    @endif 
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
 
      @include('layout.partials.footer-company') 

    </div> 
    
    <div class="modal fade" id="upload_resignation" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content"> 
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Resignation Letter</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                  <form id="resignationForm" method="POST" action="{{ route('submit-resignation-letter') }}" enctype="multipart/form-data">
                        @csrf
                    <div class="modal-body"> 
                        <div class="mb-3">
                            <label class="form-label">Resignation Letter</label>
                            <input type="file" class="form-control" name="resignation_letter" id="resignation_letter"
                                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"> 
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (optional)</label>
                            <textarea class="form-control" rows="5" id="resignation_reason" name="resignation_reason"></textarea>
                        </div>
                    </div> 
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form> 
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
 <div class="modal fade" id="edit_resignation_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content"> 
            <div class="modal-header">
                <h5 class="modal-title">Edit Resignation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="editResignationForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') 
                
                <div class="modal-body"> 
                    <input type="hidden" id="edit_resignation_id" name="id">

                    <div class="mb-3">
                        <label class="form-label">Resignation Letter</label>
                       <input type="file" class="form-control" name="resignation_letter" id="edit_resignation_letter"
                        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">

                        <small class="text-muted d-block mt-1" id="current_file_info"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason (optional)</label>
                        <textarea class="form-control" rows="5" id="edit_resignation_reason" name="resignation_reason"></textarea>
                    </div>
                </div> 
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form> 
        </div>
    </div>
</div>

  <div class="modal fade" id="delete_resignation_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                    <i class="ti ti-trash-x fs-36"></i>
                </span>
                <h4 class="mb-1">Confirm Delete</h4>
                <p class="mb-3">Are you sure you want to delete this resignation record?</p>
                <div class="d-flex justify-content-center">
                    <form id="deleteResignationForm">
                        @csrf
                        <input type="hidden" name="id" id="delete_resignation_id">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </form>
                </div>
            </div>
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

@push('scripts')

<script>
 
    $(document).ready(function() {
        $('#resignationForm').on('submit', function(e) {
            e.preventDefault();

            let form = $(this)[0];
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('submit-resignation-letter') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
               success: function(response) {
                    toastr.success('Your resignation letter has been successfully uploaded.', 'Success');

                    $('#upload_resignation').modal('hide');
                    $('#resignationForm')[0].reset(); 
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    if (xhr.status === 422) { 
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        }); 
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message); 
                    } else {
                        toastr.error('Something went wrong. Please try again.'); 
                    }
                }
            });
        }); 
        $('#upload_resignation').on('hidden.bs.modal', function() {
            $('#resignationForm')[0].reset();
        });
    }); 

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
        } else if (fileExtension === 'doc' || fileExtension === 'docx') {
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `
                <p>This file cannot be previewed directly. Click below to open it in a new tab:</p>
                <a href="${fileUrl}" target="_blank" class="btn btn-primary">Open Document</a>
            `;
        } else {
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `<p>Unsupported file format. <a href="${fileUrl}" target="_blank">Download file</a></p>`;
        }
 
        if (reason && reason.trim() !== '') {
            reasonText.textContent = reason;
            reasonContainer.classList.remove('d-none');
        } else {
            reasonContainer.classList.add('d-none');
        }
 
        const modal = new bootstrap.Modal(document.getElementById('viewResignationModal'));
        modal.show();
    } 

    document.addEventListener('DOMContentLoaded', function () {
 
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-edit');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const reason = btn.getAttribute('data-reason') || '';
            const file = btn.getAttribute('data-file') || '';
 
            document.getElementById('edit_resignation_id').value = id;
            document.getElementById('edit_resignation_reason').value = reason;
    
            const fileInfo = document.getElementById('current_file_info');
            if (file) {
                fileInfo.innerHTML = `Current file: <a href="${file}" target="_blank">View</a>`;
            } else {
                fileInfo.textContent = 'No file uploaded yet.';
            }
 
            const form = document.getElementById('editResignationForm');
            form.setAttribute('action', `/api/resignations/${id}`);
        });
 
        const form = document.getElementById('editResignationForm');
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('edit_resignation_id').value;
            const formData = new FormData(form);

            fetch(`/api/resignations/${id}`, {
                method: 'POST',  
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const modalEl = document.getElementById('edit_resignation_modal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                toastr.success(data.message || 'Resignation updated successfully!', 'Success');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                console.error(error);
                toastr.error('Failed to update resignation. Please try again.', 'Error');
            });
        });

    }); 

      document.addEventListener('DOMContentLoaded', function () {
    
        document.addEventListener('click', function (e) {
            if (e.target.closest('.btn-delete')) {
                const button = e.target.closest('.btn-delete');
                const id = button.getAttribute('data-id');
                console.log('Delete clicked, ID:', id);
    
                document.getElementById('delete_resignation_id').value = id;
            }
        });
    
        const deleteForm = document.getElementById('deleteResignationForm');
        deleteForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('delete_resignation_id').value;

            fetch(`/api/resignations/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => { 
                const modalEl = document.getElementById('delete_resignation_modal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
    
                toastr.success(data.message || 'Resignation deleted successfully!', 'Success', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 2000
                });
    
                setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                console.error(error);
                toastr.error('Failed to delete resignation. Please try again.', 'Error', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000
                });
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
   
    $(document).ready(function() {
 
        $(document).on('submit', '#employeeAssetsForm', function(e) {
            e.preventDefault();

            const form = $(this);  
            const formData = form.serialize();
            const url = form.attr('action');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    toastr.success('Assets status and condition successfully updated!', 'Success');
                    console.log(response);
 
                    form.closest('.modal').modal('hide');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Something went wrong while saving.');
                }
            });
        });

    }); 

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
            url: '/api/resignation/assets/remarks/save',
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
    $(document).ready(function() {

        $(document).on('submit', '.uploadAttachmentForm', function(e) {
            e.preventDefault();

            const form = $(this);
            const url = form.attr('action');
            const formData = new FormData(this);  

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,  
                contentType: false,  
                success: function(response) {
                    toastr.success('Files uploaded successfully!');
                    $('#myUploadsSection').html(response.html);
                    form[0].reset();  
                    
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Failed to upload files. Please try again.');
                }
            });
        });

    }); 

        function viewResignationAttachmentRemarks(id) {
            $('#remarks_modal_' + id).modal('show'); 
        }

        function saveResignationAttachmentRemark(id) {
            let remarks = $('#remarkText' + id).val();

            if (!remarks.trim()) {
                alert('Please enter a remark.');
                return;
            } 
            $.ajax({
                url: '/api/resignation-attachments/employee/' + id + '/remarks',
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

    </script> 
    @endpush
    @component('components.modal-popup')
    @endcomponent

@endsection
