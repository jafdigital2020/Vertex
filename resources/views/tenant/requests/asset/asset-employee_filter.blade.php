 @foreach ($assetRequests as $asset)
        <tr>
            <td>
                <div class="d-flex align-items-center file-name-icon">
                    <a href="#" class="avatar avatar-md border avatar-rounded">
                        <img src="{{ asset('storage/' . $asset->user->personalInformation->profile_picture) }}"
                            class="img-fluid" alt="img">
                    </a>
                    <div class="ms-2">
                        <h6 class="fw-medium"><a
                                href="#">{{ $asset->user->personalInformation->last_name }},
                                {{ $asset->user->personalInformation->first_name }}</a></h6>
                        <span
                            class="fs-12 fw-normal ">{{ $asset->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </td>
            <td class="text-center">
                {{ $asset->created_at ? \Carbon\Carbon::parse($asset->created_at)->format('F j, Y') : 'N/A' }}
            </td>
            <td class="text-center">{{ $asset->asset_type ?? 'N/A' }}</td>
            <td class="text-center">{{ $asset->asset_name ?? 'N/A' }}</td>
            <td class="text-center">{{ $asset->quantity ?? 'N/A' }}</td>
            <td class="text-center">{{ Str::limit($asset->purpose ?? 'N/A', 30) }}</td>
            <td class="text-center">
                @if ($asset->attachment)
                    <a href="{{ asset('storage/' . $asset->attachment) }}"
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
                    if ($asset->status == 'approved') {
                        $badgeClass = 'badge-success';
                    } elseif ($asset->status == 'rejected') {
                        $badgeClass = 'badge-warning';
                    }
                @endphp
                <span
                    class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($asset->status) }}
                </span>
            </td>
            <td class="text-center">
                @if ($asset->approver_name ?? false)
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);"
                            class="avatar avatar-md border avatar-rounded">
                            <img src="{{ asset('storage/' . $asset->approver_picture) }}"
                                class="img-fluid" alt="avatar">
                        </a>
                        <div class="ms-2">
                            <h6 class="fw-medium mb-0">
                                {{ $asset->approver_name }}
                            </h6>
                            <span class="fs-12 fw-normal">
                                {{ $asset->approver_dept }}
                            </span>
                        </div>
                    </div>
                @else
                    &mdash;
                @endif
            </td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
            <td class="text-center">
                @if ($asset->status !== 'approved')
                    <div class="action-icon d-inline-flex">
                        @if(in_array('Update',$permission))
                        <a href="#" class="me-2" data-bs-toggle="modal"
                            data-bs-target="#edit_asset_request" data-id="{{ $asset->id }}"
                            data-asset-type="{{ $asset->asset_type }}"
                            data-asset-name="{{ $asset->asset_name }}"
                            data-quantity="{{ $asset->quantity }}"
                            data-purpose="{{ $asset->purpose }}"
                            data-specifications="{{ $asset->specifications }}"
                            data-attachment="{{ $asset->attachment }}"><i
                                class="ti ti-edit"></i></a>
                            @endif
                        @if(in_array('Delete',$permission))
                        <a href="#" data-bs-toggle="modal" class="btn-delete"
                            data-bs-target="#delete_asset_request"
                            data-id="{{ $asset->id }}"
                            data-name="{{ $asset->user->personalInformation->full_name ?? 'N/A' }}"><i
                                class="ti ti-trash"></i></a>
                        @endif
                    </div>
                @endif
            </td>
            @endif
        </tr>
    @endforeach
