 @foreach ($assignedUsers as $users)
<tr>
    <td>
        <div class="d-flex align-items-center">
            <a href="#" class="avatar avatar-md" data-bs-toggle="modal"
                data-bs-target="#view_details"><img
                    src="{{ asset('storage/' . ($users->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                    class="img-fluid rounded-circle" alt="img"></a>
            <div class="ms-2">
                <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                        data-bs-toggle="modal" data-bs-target="#view_details">
                        {{ $users->user->personalInformation->last_name ?? '' }}
                        {{ $users->user->personalInformation->suffix ?? '' }},
                        {{ $users->user->personalInformation->first_name ?? '' }}
                        {{ $users->user->personalInformation->middle_name ?? '' }}</a>
                </p>
                <span
                    class="fs-12">{{ $users->user->employmentDetail->department->department_name ?? '' }}</span>
            </div>
        </div>
    </td>
    <td>{{ $users->user->employmentDetail->branch->name ?? 'N/A' }}</td>
    <td>{{ $users->current_balance ?? 'N/A' }}</td>
    <td>
        <div class="action-icon d-inline-flex">
            @if(in_array('Update',$permission) )
            <a href="#" class="me-2" data-bs-toggle="modal"
                data-bs-target="#edit_assigned_users_leave"
                data-id="{{ $users->id }}"
                data-leave-name="{{ $users->leaveType->name }}"
                data-current-balance="{{ $users->current_balance }}" title="Edit"><i
                    class="ti ti-edit"></i></a>
            @endif
            @if(in_array('Delete',$permission) )
            <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                data-bs-target="#delete_assigned_users_leave"
                data-id="{{ $users->id }}" title="Delete"><i
                    class="ti ti-trash"></i></a>
            @endif

        </div>
    </td>
</tr>
@endforeach