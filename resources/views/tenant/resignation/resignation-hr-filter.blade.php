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