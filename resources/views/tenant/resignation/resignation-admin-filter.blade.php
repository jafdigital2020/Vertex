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
       @if($resignation->status === 0) 
        <span>For Approval</span>
        @elseif($resignation->status === 1 && $resignation->accepted_date === null )
        <span>For Acceptance</span>
        @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 0)
        <span>For Clearance</span>
        @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 && $remainingDays > 0 )
        <span>Rendering</span> 
        @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 && $remainingDays < 0 )
        <span>Resigned</span> 
        @elseif($resignation->status === 2)
        <span>Rejected</span>
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
    @endif

    </td>
    </tr>
@endforeach