@foreach ($policies as $policy)
<tr>
    <td>
        <div class="form-check form-check-md">
            <input class="form-check-input" type="checkbox">
        </div>
    </td>
    <td  class="text-center">
        <h6 class="fs-14 fw-medium text-gray-9">{{ $policy->policy_title }}</h6>
    </td>
    <td  class="text-center">{{ \Carbon\Carbon::parse($policy->effective_date)->format('F j, Y') }}</td>

    <td  class="text-center">
        @foreach ($policy->targets->groupBy('target_type') as $targetType => $targets)
            @if ($targetType == 'company-wide')
                <span>{{ ucfirst($targetType) }}</span>
            @else
                <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#targetModal{{ $policy->id }}{{ ucfirst($targetType) }}"
                    data-policy-id="{{ $policy->id }}"><i class="ti ti-eye me-1"></i>
                    {{ ucfirst($targetType) }}
                </button>
            @endif
        @endforeach
    </td>

    <td  class="text-center">
        @if ($policy->attachment_path)
            <a href="{{ Storage::url($policy->attachment_path) }}" target="_blank"
                class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                <i class="ti ti-file-description me-1"></i> View
            </a>
        @else
            <span class="text-muted fst-italic">No Attachment</span>
        @endif
    </td>
    <td  class="text-center">{{ $policy->createdBy->personalInformation->last_name ?? 'N/A' }},
        {{ $policy->createdBy->personalInformation->first_name ?? 'N/A' }}</td>
    @if(in_array('Update',$permission) || in_array('Delete',$permission))
    <td  class="text-center">
        <div class="action-icon d-inline-flex">
            @if(in_array('Update',$permission))
            <a href="#" class="me-2" data-bs-toggle="modal"
                data-bs-target="#edit_policy" data-id="{{ $policy->id }}"
                data-policy-title="{{ $policy->policy_title }}"
                data-policy-content="{{ $policy->policy_content }}"
                data-effective-date="{{ $policy->effective_date }}"
                data-attachment-type="{{ $policy->attachment_type }}"><i
                    class="ti ti-edit"></i></a>
            @endif
            @if(in_array('Delete',$permission))
            <a href="#" data-bs-toggle="modal" class="btn-delete"
                data-bs-target="#delete_policy" data-id="{{ $policy->id }}"
                data-policy-title="{{ $policy->policy_title }}"><i
                    class="ti ti-trash"></i></a>
            @endif
        </div>
    </td>
    @endif
</tr>
@endforeach