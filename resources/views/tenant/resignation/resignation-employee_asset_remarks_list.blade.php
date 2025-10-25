 @if ($remarks->count())
    <div class="mb-3">
    <h6 class="fw-bold mb-3 text-start">Remarks History:</h6>

    <div class="remarks-chat p-2 border rounded" style="max-height: 350px; overflow-y: auto;">
    @foreach ($remarks as $remark)
    <div class="d-flex mb-3 {{ $remark->remarks_from === 'HR' ? 'justify-content-start' : 'justify-content-end' }}">
    <div class="p-2 rounded-3 shadow-sm col-12"
        style="max-width: 70%;
                background-color: {{ $remark->remarks_from === 'HR' ? '#f1f1f1' : '#d1f7d6' }};">
        <strong class="small text-muted d-block mb-1">
            {{ $remark->remarks_from === 'HR' ? 'HR' : 'Employee' }}
        </strong>
        <span class="d-block">{{ $remark->condition_remarks }}</span>
        <small class="text-muted d-block mt-1" style="font-size: 11px;">
            {{ $remark->created_at->format('M d, Y h:i A') }}
        </small>
</div>
</div>
@endforeach
</div>
</div>
@else
<p class="text-muted">No remarks yet.</p>
@endif  