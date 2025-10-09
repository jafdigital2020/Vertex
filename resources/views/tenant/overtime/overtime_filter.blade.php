@foreach ($overtimes as $ot)
    @php
        $status = strtolower($ot->status);
        $colors = [
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'info',
        ];
      @endphp
    <tr data-overtime-id="{{ $ot->id }}">
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox" value="{{ $ot->id }}">
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center file-name-icon">
                <a href="#" class="avatar avatar-md border avatar-rounded">
                    <img src="{{ asset('storage/' . $ot->user->personalInformation->profile_picture) }}" class="img-fluid"
                        alt="img">
                </a>
                <div class="ms-2">
                    <h6 class="fw-medium"><a href="#">{{ $ot->user->personalInformation->last_name }},
                            {{ $ot->user->personalInformation->first_name }}</a></h6>
                    <span class="fs-12 fw-normal ">{{ $ot->user->employmentDetail->department->department_name }}</span>
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

        <td class="text-center">
            <div>
                <span class="d-block">
                    <strong>OT:</strong> {{ $ot->total_ot_minutes_formatted }}
                </span>
                <span class="d-block">
                    <strong>ND:</strong> {{ $ot->total_night_diff_minutes_formatted }}
                </span>
            </div>
        </td>
        <td class="text-center">
            @if ($ot->file_attachment)
                <a href="{{ asset('storage/' . $ot->file_attachment) }}" class="text-primary" target="_blank">
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
            <div class="dropdown" style="position: static; overflow: visible;">
                <a href="#" class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                    data-bs-toggle="dropdown">
                    <span
                        class="rounded-circle bg-transparent-{{ $colors[$status] }} d-flex justify-content-center align-items-center me-2">
                        <i class="ti ti-point-filled text-{{ $colors[$status] }}"></i>
                    </span>
                    {{ ucfirst($status) }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-3">
                    <li>
                        <a href="#"
                            class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'approved' ? 'active' : '' }}"
                            data-action="approved" data-overtime-id="{{ $ot->id }}" data-bs-toggle="modal"
                            data-bs-target="#approvalModal">
                            <span
                                class="rounded-circle bg-transparent-{{ $colors['approved'] }} d-flex justify-content-center align-items-center me-2">
                                <i class="ti ti-point-filled text-{{ $colors['approved'] }}"></i>
                            </span>
                            Approved
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'rejected' ? 'active' : '' }}"
                            data-action="rejected" data-overtime-id="{{ $ot->id }}" data-bs-toggle="modal"
                            data-bs-target="#approvalModal">
                            <span
                                class="rounded-circle bg-transparent-{{ $colors['rejected'] }} d-flex justify-content-center align-items-center me-2">
                                <i class="ti ti-point-filled text-{{ $colors['rejected'] }}"></i>
                            </span>
                            Rejected
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="dropdown-item d-flex align-items-center js-approve {{ $status === 'pending' ? 'active' : '' }}"
                            data-action="CHANGES_REQUESTED" data-overtime-id="{{ $ot->id }}">
                            <span
                                class="rounded-circle bg-transparent-{{ $colors['pending'] }} d-flex justify-content-center align-items-center me-2">
                                <i class="ti ti-point-filled text-{{ $colors['pending'] }}"></i>
                            </span>
                            Pending
                        </a>
                    </li>
                </ul>
            </div>
        </td>
        <td class="text-center">{{ $ot->ot_login_type }}</td>
        <td class="text-center">
            @if (count($ot->next_approvers))
                {{ implode(', ', $ot->next_approvers) }}
            @else
                —
            @endif
        </td>
        <td class="align-middle text-center">
            <div class="d-flex flex-column">
                {{-- 1) Approver name --}}
                <span class="fw-semibold">
                    {{ $ot->last_approver ?? '—' }}
                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="{{ $ot->latestApproval->comment ?? 'No comment' }}">
                        <i class="ti ti-info-circle text-info"></i></a>
                </span>
                {{-- Approval date/time --}}
                @if ($ot->latestApproval)
                    <small class="text-muted mt-1">
                        {{ \Carbon\Carbon::parse($ot->latestApproval->acted_at)->format('d M Y, h:i A') }}
                    </small>
                @endif
            </div>
        </td>
        @if (in_array('Update', $permission) || in_array('Delete', $permission))
            <td class="text-center">
                <div class="action-icon d-inline-flex">
                    @if (in_array('Update', $permission))
                        <a href="#" class="me-2" data-bs-toggle="modal" data-bs-target="#edit_admin_overtime"
                            data-id="{{ $ot->id }}" data-user-id="{{ $ot->user_id }}" data-overtime-date="{{ $ot->overtime_date }}"
                            data-ot-in="{{ $ot->date_ot_in }}" data-ot-out="{{ $ot->date_ot_out }}"
                            data-total-ot="{{ $ot->total_ot_minutes }}" data-file-attachment="{{ $ot->file_attachment }}"
                            data-offset-date="{{ $ot->offset_date }}" data-status="{{ $ot->status }}"><i class="ti ti-edit"></i></a>
                    @endif
                    @if (in_array('Delete', $permission))
                        <a href="#" class="btn-delete" data-bs-toggle="modal" data-bs-target="#delete_admin_overtime"
                            data-id="{{ $ot->id }}"
                            data-user-name="{{ $ot->user->personalInformation->first_name }} {{ $ot->user->personalInformation->last_name }}"><i
                                class="ti ti-trash"></i></a>
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach