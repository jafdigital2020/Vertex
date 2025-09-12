  @foreach ($users as $user)
    <tr>

        <td>
            <div class="d-flex align-items-center file-name-icon">
                <a href="#" class="avatar avatar-md avatar-rounded">
                    <img src="{{ URL::asset('build/img/users/user-32.jpg') }}"
                        class="img-fluid" alt="img">
                </a>
                <div class="ms-2">
                    <h6 class="fw-medium"><a
                            href="#">{{ $user->personalInformation->first_name ?? '' }}
                            {{ $user->personalInformation->last_name ?? '' }} </a></h6>
                </div>
            </div>
        </td>
        <td>{{ $user->email }}</td>
        <td class="text-center">
            <span class=" badge badge-md p-2 fs-10 badge-pink-transparent">
                {{ $user->userPermission->role->role_name ?? null }}</span>
        </td>
        <td class="text-center">
            {{$user->userPermission->data_access_level->access_name ?? 'No Specified Access' }}
        </td>
        <td>
            @if (isset($user->employmentDetail) && isset($user->employmentDetail->status))
                @if ($user->employmentDetail->status == 1)
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
            @else
                <span class="badge badge-secondary d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i> Unknown
                </span>
            @endif
        </td>
        @if (in_array('Update', $permission))
            <td class="text-center">
                <div class="action-icon d-inline-flex">
                    @if(isset($user->userPermission) && isset($user->userPermission->id))
                        {{-- <a href="#" class="me-2"
                            onclick="user_permissionEdit({{ $user->userPermission->id }})"><i
                                class="ti ti-shield"></i></a> --}}
                        <a href="#" class="me-2"
                            onclick="user_data_accessEdit({{ $user->userPermission->id }})"><i
                            class="ti ti-edit"></i></a>
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach