   @foreach ($overtimes as $ot)
    <tr>
        <td>
            <div class="d-flex align-items-center file-name-icon">
                <a href="#" class="avatar avatar-md border avatar-rounded">
                    <img src="{{ asset('storage/' . $ot->user->personalInformation->profile_picture) }}"
                        class="img-fluid" alt="img">
                </a>
                <div class="ms-2">
                    <h6 class="fw-medium"><a
                            href="#">{{ $ot->user->personalInformation->last_name }},
                            {{ $ot->user->personalInformation->first_name }}</a></h6>
                    <span
                        class="fs-12 fw-normal ">{{ $ot->user->employmentDetail->department->department_name }}</span>
                </div>
            </div>
        </td>
        <td class="text-center">
            {{ $ot->overtime_date ? $ot->overtime_date->format('F j, Y') : 'N/A' }}
        </td>
            <td class="text-center">
            {{ $ot->date_ot_in ? $ot->date_ot_in->format('g:i A') : 'N/A' }} - 
            {{ $ot->date_ot_out ? $ot->date_ot_out->format('g:i A') : 'N/A' }}
        </td>

            <td class="text-center">{{ $ot->total_ot_minutes_formatted }}</td>
            <td class="text-center">
            @if ($ot->file_attachment)
                <a href="{{ asset('storage/' . $ot->file_attachment) }}"
                    class="text-primary" target="_blank">
                    <i class="ti ti-file-text"></i> View Attachment
                </a>
            @else
                <span class="text-muted">No Attachment</span>
            @endif
        </td>
            <td class="text-center">
            {{ $ot->offset_date ? \Carbon\Carbon::parse($ot->offset_date)->format('F j, Y') : 'N/A' }}
        </td>
            <td class="text-center">
            @if ($ot->lastApproverName)
                <div class="d-flex align-items-center">
                    <a href="javascript:void(0);"
                        class="avatar avatar-md border avatar-rounded">
                        <img src="{{ asset('storage/' . $ot->latestApproval->approver->personalInformation->profile_picture) }}"
                            class="img-fluid" alt="avatar">
                    </a>
                    <div class="ms-2">
                        <h6 class="fw-medium mb-0">
                            {{ $ot->lastApproverName }}
                        </h6>
                        <span class="fs-12 fw-normal">
                            {{ $ot->lastApproverDept }}
                        </span>
                    </div>
                </div>
            @else
                &mdash;
            @endif
        </td>
            <td class="text-center">
            @php
                $badgeClass = 'badge-info';
                if ($ot->status == 'approved') {
                    $badgeClass = 'badge-success';
                } elseif ($ot->status == 'rejected') {
                    $badgeClass = 'badge-warning';
                }
            @endphp
            <span
                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($ot->status) }}
            </span>
        </td>
           
        <td class="text-center">
            @if ($ot->status !== 'approved') 
        <div class="action-icon d-inline-flex">
                @if(in_array('Update',$permission) )
                <a href="#" class="me-2" data-bs-toggle="modal"
                    data-bs-target="#edit_employee_overtime"
                    data-id="{{ $ot->id }}"
                    data-overtime-date="{{ $ot->overtime_date }}"
                    data-ot-in="{{ $ot->date_ot_in }}"
                    data-ot-out="{{ $ot->date_ot_out }}"
                    data-total-ot="{{ $ot->total_ot_minutes }}"
                    data-file-attachment="{{ $ot->file_attachment }}"
                    data-offset-date="{{ $ot->offset_date }}"
                    data-status="{{ $ot->status }}"><i class="ti ti-edit"></i></a> 
                @endif
                @if(in_array('Delete',$permission) )
                <a href="#" data-bs-toggle="modal"
                    data-bs-target="#delete_employee_overtime"
                    data-id="{{ $ot->id }}"><i class="ti ti-trash"></i></a>
                @endif
        </div>
            @endif
        </td>  
    </tr>
@endforeach