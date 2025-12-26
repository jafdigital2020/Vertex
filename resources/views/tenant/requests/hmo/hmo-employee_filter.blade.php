 @foreach ($hmoRequests as $hmo)
        <tr>
            <td>
                <div class="d-flex align-items-center file-name-icon">
                    <a href="#" class="avatar avatar-md border avatar-rounded">
                        <img src="{{ asset('storage/' . $hmo->user->personalInformation->profile_picture) }}"
                            class="img-fluid" alt="img">
                    </a>
                    <div class="ms-2">
                        <h6 class="fw-medium"><a
                                href="#">{{ $hmo->user->personalInformation->last_name }},
                                {{ $hmo->user->personalInformation->first_name }}</a></h6>
                        <span
                            class="fs-12 fw-normal ">{{ $hmo->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </td>
            <td class="text-center">
                {{ $hmo->created_at ? \Carbon\Carbon::parse($hmo->created_at)->format('F j, Y') : 'N/A' }}
            </td>
            <td class="text-center">{{ $hmo->hmo_type ?? 'N/A' }}</td>
            <td class="text-center">{{ $hmo->coverage_type ?? 'N/A' }}</td>
            <td class="text-center">{{ $hmo->dependents ?? '0' }}</td>
            <td class="text-center">{{ Str::limit($hmo->purpose ?? 'N/A', 30) }}</td>
            <td class="text-center">
                @php
                    $badgeClass = 'badge-info';
                    if ($hmo->status == 'approved') {
                        $badgeClass = 'badge-success';
                    } elseif ($hmo->status == 'rejected') {
                        $badgeClass = 'badge-warning';
                    }
                @endphp
                <span
                    class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($hmo->status) }}
                </span>
            </td>
            <td class="text-center">
                @if ($hmo->approver_name ?? false)
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);"
                            class="avatar avatar-md border avatar-rounded">
                            <img src="{{ asset('storage/' . $hmo->approver_picture) }}"
                                class="img-fluid" alt="avatar">
                        </a>
                        <div class="ms-2">
                            <h6 class="fw-medium mb-0">
                                {{ $hmo->approver_name }}
                            </h6>
                            <span class="fs-12 fw-normal">
                                {{ $hmo->approver_dept }}
                            </span>
                        </div>
                    </div>
                @else
                    &mdash;
                @endif
            </td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
            <td class="text-center">
                @if ($hmo->status !== 'approved')
                    <div class="action-icon d-inline-flex">
                        @if(in_array('Update',$permission))
                        <a href="#" class="me-2" data-bs-toggle="modal"
                            data-bs-target="#edit_hmo_request" data-id="{{ $hmo->id }}"
                            data-hmo-type="{{ $hmo->hmo_type }}"
                            data-coverage-type="{{ $hmo->coverage_type }}"
                            data-dependents="{{ $hmo->dependents }}"
                            data-purpose="{{ $hmo->purpose }}"
                            data-medical-history="{{ $hmo->medical_history }}"><i
                                class="ti ti-edit"></i></a>
                            @endif
                        @if(in_array('Delete',$permission))
                        <a href="#" data-bs-toggle="modal" class="btn-delete"
                            data-bs-target="#delete_hmo_request"
                            data-id="{{ $hmo->id }}"
                            data-name="{{ $hmo->user->personalInformation->full_name ?? 'N/A' }}"><i
                                class="ti ti-trash"></i></a>
                        @endif
                    </div>
                @endif
            </td>
            @endif
        </tr>
    @endforeach
