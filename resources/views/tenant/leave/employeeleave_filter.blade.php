 @foreach ($leaveRequests as $lr)
<tr>
    <td>
        <div class="form-check form-check-md">
            <input class="form-check-input" type="checkbox">
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                {{ $lr->leaveType->name }}</p>
            <a href="#" class="ms-2" data-bs-toggle="tooltip"
                data-bs-placement="right"
                data-bs-title="{{ $lr->reason ?? 'No reason provided' }}">
                <i class="ti ti-info-circle text-info"></i>
            </a>
        </div>
    </td>
    <td class="text-center">
        {{ \Carbon\Carbon::parse($lr->start_date)->format('d M Y') }}
    </td>
    <td class="text-center">
        {{ \Carbon\Carbon::parse($lr->end_date)->format('d M Y') }}
    </td>
    <td class="text-center">
        @if ($lr->lastApproverName)
            <div class="d-flex align-items-center">
                <a href="javascript:void(0);"
                    class="avatar avatar-md border avatar-rounded">
                    <img src="{{ asset('storage/' . $lr->latestApproval->approver->personalInformation->profile_picture) }}"
                        class="img-fluid" alt="avatar">
                </a>
                <div class="ms-2">
                    <h6 class="fw-medium mb-0">
                        {{ $lr->lastApproverName }}
                    </h6>
                    <span class="fs-12 fw-normal">
                        {{ $lr->lastApproverDept }}
                    </span>
                </div>
            </div>
        @else
            &mdash;
        @endif
    </td>
    <td class="text-center">
        {{ $lr->days_requested }}
    </td>
    <td class="text-center">
        <div class="dropdown">
            <a href="javascript:void(0);"
                class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                data-bs-toggle="dropdown">
                @php
                    $status = strtolower($lr->status);
                    switch ($status) {
                        case 'approved':
                            $color = 'success';
                            break;
                        case 'pending':
                            $color = 'primary';
                            break;
                        case 'rejected':
                            $color = 'danger';
                            break;
                        default:
                            $color = 'secondary';
                    }
                @endphp
                <span
                    class="rounded-circle bg-transparent-{{ $color }} d-flex justify-content-center align-items-center me-2">
                    <i class="ti ti-point-filled text-{{ $color }}"></i>
                </span>
                {{ Str::ucfirst($status) }}
            </a>
        </div>
    </td>
    <td class="text-center">
        <div class="action-icon d-inline-flex"> 
            <a href="#" class="me-2" data-bs-toggle="modal"
                data-bs-target="#edit_request_leave" data-id="{{ $lr->id }}"
                data-leave-id="{{ $lr->leave_type_id }}"
                data-start-date="{{ $lr->start_date }}"
                data-end-date="{{ $lr->end_date }}"
                data-half-day="{{ $lr->half_day_type }}"
                data-reason="{{ $lr->reason }}"
                data-current-step="{{ $lr->current_step }}"
                data-status="{{ $lr->status }}"><i class="ti ti-edit"></i></a> 
            <a href="javascript:void(0);" data-bs-toggle="modal" class="btn-delete"
                data-bs-target="#delete_request_leave" data-id="{{ $lr->id }}"
                data-leave-name="{{ $lr->leaveType->name }}"><i
                    class="ti ti-trash"></i></a> 
        </div>
    </td>
</tr>
@endforeach