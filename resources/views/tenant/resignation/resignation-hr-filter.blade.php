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
                                                    @if($resignation->status === 1 && $resignation->accepted_date === null )
                                                    <span>For Acceptance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 0)
                                                    <span>For Clearance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 )
                                                    <span>Resigned</span> 
                                                    @endif
                                                </td>
                                             
                                                <td>  
 
                                                    @if($resignation->status === 1 && $resignation->accepted_by === null)
                                                       <button class="btn btn-primary btn-sm" onclick="openAcceptanceModal({{ $resignation->id }}, 'accept')">
                                                        Accept <i class="bi bi-hand-thumbs-up ms-1"></i>  
                                                    </button> 
                                                    @elseif( $resignation->status === 1 && $resignation->accepted_by !== null && $resignation->cleared_status === 0) 
 
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