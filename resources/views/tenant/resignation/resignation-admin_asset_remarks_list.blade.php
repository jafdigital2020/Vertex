 @if ($remarks->count()) 
<div class="remarks-chat p-2 border rounded" style="max-height: 350px; overflow-y: auto; background-color: #f9f9f9;">
@foreach ($remarks as $remark)
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