  @foreach ($assignedGeofences as $geofenceUser)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div
                                                                                            class="form-check form-check-md">
                                                                                            <input class="form-check-input"
                                                                                                type="checkbox">
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>{{ $geofenceUser->user->personalInformation->first_name }}
                                                                                    </td>
                                                                                   <td class="text-center">{{ $geofenceUser->user->branch->name }}
                                                                                    </td>
                                                                                   <td class="text-center">{{ $geofenceUser->geofence->geofence_name }}
                                                                                    </td>
                                                                                   <td class="text-center">
                                                                                        <span
                                                                                            class="badge d-inline-flex align-items-center badge-xs
                                                                                    {{ $geofenceUser->assignment_type === 'manual' ? 'badge-dark' : 'badge-secondary' }}">
                                                                                            <i
                                                                                                class="ti ti-point-filled me-1"></i>{{ ucfirst($geofenceUser->assignment_type) }}
                                                                                        </span>
                                                                                    </td>
                                                                                   <td class="text-center">{{ $geofenceUser->creator_name }}
                                                                                    </td>
                                                                                @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                                                                   <td class="text-center">
                                                                                        <div class="action-icon d-inline-flex">
                                                                                            @if(in_array('Update',$permission))
                                                                                            <a href="#"
                                                                                                class="me-2"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#edit_assign_geofence"
                                                                                                data-id="{{ $geofenceUser->id }}"
                                                                                                data-geofence-id="{{ $geofenceUser->geofence_id }}"
                                                                                                data-assignment-type="{{ $geofenceUser->assignment_type }}">
                                                                                                <i class="ti ti-edit"
                                                                                                    title="Edit"></i></a>
                                                                                            @endif 
                                                                                            @if(in_array('Delete',$permission))
                                                                                            <a href="#"
                                                                                                class="me-2 btn-deleteGeofenceUser"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#delete_assign_geofence"
                                                                                                data-id="{{ $geofenceUser->id }}">
                                                                                                <i class="ti ti-trash"
                                                                                                    title="Delete"></i></a>
                                                                                            @endif
                                                                                        </div>
                                                                                    </td>
                                                                                @endif
                                                                                </tr>
                                                                            @endforeach