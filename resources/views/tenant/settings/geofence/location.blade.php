@foreach ($geofences as $geofence)
<tr>
    <td>
        <div
            class="form-check form-check-md">
            <input class="form-check-input"
                type="checkbox">
        </div>
    </td>
    <td>{{ $geofence->geofence_name }}</td>
    <td class="text-center">{{ $geofence->branch->name ?? 'N/A' }}
    </td>
    <td class="text-center">{{ $geofence->geofence_address }}
    </td>
    <td class="text-center">{{ $geofence->geofence_radius }}
    </td>
    <td class="text-center">{{ $geofence->creator_name }}</td>
    <td class="text-center">{{ $geofence->updater_name ?? 'N/A' }}
    </td>
    <td class="text-center">{{ $geofence->expiration_date ?? 'No Expiration' }}
    </td>
    <td class="text-center"> <span
            class="badge d-inline-flex align-items-center badge-xs
        {{ $geofence->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
            <i
                class="ti ti-point-filled me-1"></i>{{ ucfirst($geofence->status) }}
        </span></td>
    @if(in_array('Update',$permission) || in_array('Delete',$permission))
    <td class="text-center">
        <div
            class="action-icon d-inline-flex">
            @if(in_array('Update',$permission))
            <a href="#"
                class="me-2 btn-edit"
                data-bs-toggle="modal"
                data-bs-target="#edit_geofence"
                data-id="{{ $geofence->id }}"
                data-geofence-name="{{ $geofence->geofence_name }}"
                data-geofence-address="{{ $geofence->geofence_address }}"
                data-latitude="{{ $geofence->latitude }}"
                data-longitude="{{ $geofence->longitude }}"
                data-geofence-radius="{{ $geofence->geofence_radius }}"
                data-expiration-date="{{ $geofence->expiration_date }}"
                data-branch-id="{{ $geofence->branch_id }}"
                data-status="{{ $geofence->status }}">
                <i class="ti ti-edit"
                    title="Edit"></i></a>
                @endif
                @if(in_array('Delete',$permission))
            <a href="#"
                class="me-2 btn-delete"
                data-bs-toggle="modal"
                data-bs-target="#delete_geofence"
                data-id="{{ $geofence->id }}"
                data-geofence-name="{{ $geofence->geofence_name }}">
                <i class="ti ti-trash"
                    title="Delete"></i></a>
                @endif
        </div>
    </td>
    @endif
</tr>
@endforeach