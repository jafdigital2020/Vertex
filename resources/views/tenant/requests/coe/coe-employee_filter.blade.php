 @foreach ($coeRequests as $coe)
        <tr>
            <td>
                <div class="d-flex align-items-center file-name-icon">
                    <a href="#" class="avatar avatar-md border avatar-rounded">
                        <img src="{{ asset('storage/' . $coe->user->personalInformation->profile_picture) }}"
                            class="img-fluid" alt="img">
                    </a>
                    <div class="ms-2">
                        <h6 class="fw-medium"><a
                                href="#">{{ $coe->user->personalInformation->last_name }},
                                {{ $coe->user->personalInformation->first_name }}</a></h6>
                        <span
                            class="fs-12 fw-normal ">{{ $coe->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </td>
            <td class="text-center">
                {{ $coe->created_at ? \Carbon\Carbon::parse($coe->created_at)->format('F j, Y') : 'N/A' }}
            </td>
            <td class="text-center">{{ $coe->purpose ?? 'N/A' }}</td>
            <td class="text-center">{{ $coe->recipient_name ?? 'N/A' }}</td>
            <td class="text-center">
                {{ $coe->needed_by_date ? \Carbon\Carbon::parse($coe->needed_by_date)->format('F j, Y') : 'N/A' }}
            </td>
            <td class="text-center">{{ Str::limit($coe->address_to ?? 'N/A', 30) }}</td>
            <td class="text-center">
                @php
                    $badgeClass = 'badge-info';
                    if ($coe->status == 'approved') {
                        $badgeClass = 'badge-success';
                    } elseif ($coe->status == 'rejected') {
                        $badgeClass = 'badge-warning';
                    }
                @endphp
                <span
                    class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($coe->status) }}
                </span>
            </td>
            <td class="text-center">
                @if ($coe->approver_name ?? false)
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);"
                            class="avatar avatar-md border avatar-rounded">
                            <img src="{{ asset('storage/' . $coe->approver_picture) }}"
                                class="img-fluid" alt="avatar">
                        </a>
                        <div class="ms-2">
                            <h6 class="fw-medium mb-0">
                                {{ $coe->approver_name }}
                            </h6>
                            <span class="fs-12 fw-normal">
                                {{ $coe->approver_dept }}
                            </span>
                        </div>
                    </div>
                @else
                    &mdash;
                @endif
            </td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
            <td class="text-center">
                @if ($coe->status !== 'approved')
                    <div class="action-icon d-inline-flex">
                        @if(in_array('Update',$permission))
                        <a href="#" class="me-2" data-bs-toggle="modal"
                            data-bs-target="#edit_coe_request" data-id="{{ $coe->id }}"
                            data-purpose="{{ $coe->purpose }}"
                            data-recipient-name="{{ $coe->recipient_name }}"
                            data-recipient-company="{{ $coe->recipient_company }}"
                            data-address-to="{{ $coe->address_to }}"
                            data-needed-by-date="{{ $coe->needed_by_date }}"><i
                                class="ti ti-edit"></i></a>
                            @endif
                        @if(in_array('Delete',$permission))
                        <a href="#" data-bs-toggle="modal" class="btn-delete"
                            data-bs-target="#delete_coe_request"
                            data-id="{{ $coe->id }}"
                            data-name="{{ $coe->user->personalInformation->full_name ?? 'N/A' }}"><i
                                class="ti ti-trash"></i></a>
                        @endif
                    </div>
                @endif
            </td>
            @endif
        </tr>
    @endforeach
