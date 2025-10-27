
                                @foreach ($obEntries as $ob)
                                    @php
                                        $status = strtolower($ob->status);
                                        $colors = [
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'pending' => 'info',
                                        ];
                                    @endphp
                                    <tr data-ob-id="{{ $ob->id }}">
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox" value="{{ $ob->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $ob->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $ob->user->personalInformation->last_name }},
                                                            {{ $ob->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $ob->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $ob->ob_date ? \Carbon\Carbon::parse($ob->ob_date)->format('F j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $ob->date_ob_in ? \Carbon\Carbon::parse($ob->date_ob_in)->format('g:i A') : 'N/A' }}
                                            -
                                            {{ $ob->date_ob_out ? \Carbon\Carbon::parse($ob->date_ob_out)->format('g:i A') : 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ $ob->ob_minutes_formatted }}</td>
                                        <td class="text-center">{{ $ob->purpose ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if ($ob->file_attachment)
                                                <a href="{{ asset('storage/' . $ob->file_attachment) }}"
                                                    class="text-primary" target="_blank">
                                                    <i class="ti ti-file-text"></i> View Attachment
                                                </a>
                                            @else
                                                <span class="text-muted">No Attachment</span>
                                            @endif
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
                                                            data-action="approved" data-official-id="{{ $ob->id }}"
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
                                                            data-action="rejected" data-official-id="{{ $ob->id }}"
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
                                                            data-official-id="{{ $ob->id }}">
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
                                            @if (count($ob->next_approvers))
                                                {{ implode(', ', $ob->next_approvers) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex flex-column">
                                                {{-- 1) Approver name --}}
                                                <span class="fw-semibold">
                                                    {{ $ob->last_approver ?? '—' }}
                                                    @if($ob->latestApproval)
                                                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="right"
                                                        data-bs-title="{{ $ob->latestApproval->comment ?? 'No comment' }}">
                                                        <i class="ti ti-info-circle text-info"></i></a>
                                                    @endif
                                                </span>
                                                {{-- Approval date/time --}}
                                                @if ($ob->latestApproval)
                                                    <small class="text-muted mt-1">
                                                        {{ \Carbon\Carbon::parse($ob->latestApproval->acted_at)->format('d M Y, h:i A') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                            @if(in_array('Update',$permission))
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_admin_ob" data-id="{{ $ob->id }}"
                                                    data-ob-date="{{ $ob->ob_date }}"
                                                    data-ob-in="{{ $ob->date_ob_in }}"
                                                    data-ob-out="{{ $ob->date_ob_out }}"
                                                    data-total-ob="{{ $ob->total_ob_minutes }}"
                                                    data-purpose="{{ $ob->purpose }}"
                                                    data-file-attachment="{{ $ob->file_attachment }}"><i
                                                        class="ti ti-edit"></i></a>
                                            @endif
                                            @if(in_array('Delete',$permission))
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_admin_ob" data-id="{{ $ob->id }}"
                                                    data-user-name="{{ $ob->user->personalInformation->first_name }} {{ $ob->user->personalInformation->last_name }}"><i
                                                        class="ti ti-trash"></i></a>
                                            @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
