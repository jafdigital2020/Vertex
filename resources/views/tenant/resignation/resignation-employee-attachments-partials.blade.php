  @php
$counter = 1;
@endphp
@forelse ($myUploads as $index => $file)
<tr class="text-xs">
<td  class="text-center text-xs" >{{  $counter++ }}</td>
<td style="max-width:100px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; vertical-align: middle;">
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