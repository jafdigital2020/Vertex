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