
@foreach ($roles as $role)
    <tr class="text-center">
        <td>{{ $role->role_name }}</td>
        <td>{{$role->data_access_level->access_name ?? 'No Specified Access'}}</td>
        <td>
            @if ($role->status == 1)
                <span
                    class="badge badge-success d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i> Active
                </span>
            @else
                <span
                    class="badge badge-danger d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i> Inactive
                </span>
            @endif
        </td>
        @if (in_array('Update', $permission))
            <td>
                <div class="action-icon d-inline-flex">
                    {{-- <a href="#" class="me-2"
                        onclick="permissionEdit({{ $role->id }})"><i
                            class="ti ti-shield"></i></a> --}}
                    <a href="#" class="me-2"
                        onclick="roleEdit({{ $role->id }})"><i
                            class="ti ti-edit"></i></a>
                </div>
            </td>
        @endif
    </tr>
@endforeach
