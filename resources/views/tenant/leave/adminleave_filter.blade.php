  @foreach ($leaveRequests as $lr)
        @php
            $status = strtolower($lr->status);
            $colors = [
                'approved' => 'success',
                'rejected' => 'danger',
                'pending' => 'primary',
            ];
        @endphp
        <tr data-leave-id="{{ $lr->id }}">
            <td>
                <div class="form-check form-check-md">
                    <input class="form-check-input" type="checkbox" value="{{ $lr->id }}">
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center file-name-icon">
                    <a href="javascript:void(0);"
                        class="avatar avatar-md border avatar-rounded">
                        <img src="{{ URL::asset('build/img/users/user-32.jpg') }}"
                            class="img-fluid" alt="img">
                    </a>
                    <div class="ms-2">
                        <h6 class="fw-medium"><a
                                href="javascript:void(0);">{{ $lr->user->personalInformation->last_name }},
                                {{ $lr->user->personalInformation->first_name }}</a>
                        </h6>
                        <span
                            class="fs-12 fw-normal ">{{ $lr->user->employmentDetail->department->department_name ?? 'No Department' }}</span>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <div class="d-flex align-items-center">
                    <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                        {{ $lr->leaveType->name }}</p>
                    <a href="#" class="ms-2" data-bs-toggle="tooltip"
                        data-bs-placement="right" title="{{ $lr->reason }}">
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
                {{ $lr->days_requested }}
            </td>
                <td class="text-center">
                <div class="dropdown" style="position: static; overflow: visible;">
                    <a href="#"
                        class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
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
                                data-action="APPROVED" data-leave-id="{{ $lr->id }}"
                                data-bs-toggle="modal" data-bs-target="#approvalModal">
                                <span
                                    class="rounded-circle bg-transparent-{{ $colors['approved'] }} d-flex justify-content-center align-items-center me-2">
                                    <i
                                        class="ti ti-point-filled text-{{ $colors['approved'] }}"></i>
                                </span>
                                Approved
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'rejected' ? 'active' : '' }}"
                                data-action="REJECTED" data-leave-id="{{ $lr->id }}"
                                data-bs-toggle="modal" data-bs-target="#approvalModal">
                                <span
                                    class="rounded-circle bg-transparent-{{ $colors['rejected'] }} d-flex justify-content-center align-items-center me-2">
                                    <i
                                        class="ti ti-point-filled text-{{ $colors['rejected'] }}"></i>
                                </span>
                                Rejected
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="dropdown-item d-flex align-items-center js-approve {{ $status === 'pending' ? 'active' : '' }}"
                                data-action="CHANGES_REQUESTED"
                                data-leave-id="{{ $lr->id }}">
                                <span
                                    class="rounded-circle bg-transparent-{{ $colors['pending'] }} d-flex justify-content-center align-items-center me-2">
                                    <i
                                        class="ti ti-point-filled text-{{ $colors['pending'] }}"></i>
                                </span>
                                Pending
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
                <td class="text-center">
                @if (count($lr->next_approvers))
                    {{ implode(', ', $lr->next_approvers) }}
                @else
                    —
                @endif
            </td>
            <td class="align-middle text-center">
                <div class="d-flex flex-column">
                    {{-- 1) Approver name --}}
                    <span class="fw-semibold">
                        {{ $lr->last_approver ?? '—' }}
                        <a href="#" data-bs-toggle="tooltip" data-bs-placement="right"
                            data-bs-title="{{ $lr->latestApproval->comment ?? 'No comment' }}">
                            <i class="ti ti-info-circle text-info"></i></a>
                    </span>
                    {{-- Approval date/time --}}
                    @if ($lr->latestApproval)
                        <small class="text-muted mt-1">
                            {{ \Carbon\Carbon::parse($lr->latestApproval->acted_at)->format('d M Y, h:i A') }}
                        </small>
                    @endif
                </div>
            </td>
                <td class="text-center">
                <div class="action-icon d-inline-flex">
                    @if(in_array('Update',$permission))
                    <a href="#" class="me-2" data-bs-toggle="modal"
                        data-bs-target="#leave_admin_edit" data-id="{{ $lr->id }}"
                        data-leave-id="{{ $lr->leave_type_id }}"
                        data-start-date="{{ $lr->start_date }}"
                        data-end-date="{{ $lr->end_date }}"
                        data-half-day="{{ $lr->half_day_type }}"
                        data-reason="{{ $lr->reason }}"
                        data-current-step="{{ $lr->current_step }}"
                        data-status="{{ $lr->status }}"
                        data-remaining-balance="{{ $lr->remaining_balance }}"
                        data-file-attachment="{{ $lr->file_attachment }}"><i
                            class="ti ti-edit"></i></a>
                    @endif
                    @if(in_array('Delete',$permission))
                    <a href="#" class="btn-delete" data-bs-toggle="modal"
                        data-bs-target="#leave_admin_delete" data-id="{{ $lr->id }}"
                        data-name="{{ $lr->user->personalInformation->first_name }} {{ $lr->user->personalInformation->last_name }}"><i
                            class="ti ti-trash"></i></a>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach