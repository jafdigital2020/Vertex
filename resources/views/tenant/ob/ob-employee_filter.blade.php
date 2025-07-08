 @foreach ($obEntries as $ob)
        <tr>
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
                @if ($ob->date_ob_in && $ob->date_ob_out)
                    {{ \Carbon\Carbon::parse($ob->date_ob_in)->format('h:i A') }} -
                    {{ \Carbon\Carbon::parse($ob->date_ob_out)->format('h:i A') }}
                @else
                    N/A
                @endif
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
                @php
                    $badgeClass = 'badge-info';
                    if ($ob->status == 'approved') {
                        $badgeClass = 'badge-success';
                    } elseif ($ob->status == 'rejected') {
                        $badgeClass = 'badge-warning';
                    }
                @endphp
                <span
                    class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($ob->status) }}
                </span>
            </td>
            <td class="text-center">
                @if ($ob->lastApproverName)
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);"
                            class="avatar avatar-md border avatar-rounded">
                            <img src="{{ asset('storage/' . $ob->latestApproval->approver->personalInformation->profile_picture) }}"
                                class="img-fluid" alt="avatar">
                        </a>
                        <div class="ms-2">
                            <h6 class="fw-medium mb-0">
                                {{ $ob->lastApproverName }}
                            </h6>
                            <span class="fs-12 fw-normal">
                                {{ $ob->lastApproverDept }}
                            </span>
                        </div>
                    </div>
                @else
                    &mdash;
                @endif
            </td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
            <td class="text-center">
                @if ($ob->status !== 'approved')
                    <div class="action-icon d-inline-flex">
                        @if(in_array('Update',$permission))
                        <a href="#" class="me-2" data-bs-toggle="modal"
                            data-bs-target="#edit_employee_ob" data-id="{{ $ob->id }}"
                            data-ob-date="{{ $ob->ob_date }}"
                            data-ob-in="{{ $ob->date_ob_in }}"
                            data-ob-out="{{ $ob->date_ob_out }}"
                            data-total-ob="{{ $ob->total_ob_minutes }}"
                            data-purpose="{{ $ob->purpose }}"
                            data-file-attachment="{{ $ob->file_attachment }}"><i
                                class="ti ti-edit"></i></a>
                            @endif
                        @if(in_array('Delete',$permission))
                        <a href="#" data-bs-toggle="modal" class="btn-delete"
                            data-bs-target="#delete_employee_ob"
                            data-id="{{ $ob->id }}"
                            data-name="{{ $ob->user->personalInformation->full_name ?? 'N/A' }}"><i
                                class="ti ti-trash"></i></a>
                        @endif
                    </div>
                @endif
            </td>
            @endif
        </tr>
    @endforeach