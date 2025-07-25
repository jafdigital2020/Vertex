 @foreach ($salaryRecords as $salaryRecord)
        <tr>
            <td>
                <div class="form-check form-check-md">
                    <input class="form-check-input" type="checkbox">
                </div>
            </td>
            <td><a
                    href="{{ url('employee-details') }}">{{ $user->employmentDetail->employee_id }}</a>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                        data-bs-toggle="modal" data-bs-target="#view_details"><img
                            src="{{ asset('storage/' . $user->personalInformation->profile_picture) }}"
                            class="img-fluid rounded-circle" alt="img"></a>
                    <div class="ms-2">
                        <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                data-bs-toggle="modal" data-bs-target="#view_details">
                                {{ $user->personalInformation->last_name }}
                                {{ $user->personalInformation->suffix }},
                                {{ $user->personalInformation->first_name }}
                                {{ $user->personalInformation->middle_name }}</a></p>
                        <span class="fs-12"></span>
                    </div>
                </div>
            </td>
            <td class="text-center">{{ $salaryRecord->basic_salary }}</td>
            <td class="text-center">
                @if ($salaryRecord->salary_type == 'monthly_fixed')
                    Monthly Fixed
                @elseif ($salaryRecord->salary_type == 'daily_rate')
                    Daily Rate
                @elseif ($salaryRecord->salary_type == 'hourly_rate')
                    Hourly Rate
                @else
                    N/A
                @endif
            </td>
            <td class="text-center">{{ $salaryRecord->effective_date->format('F d, Y') }}</td>
            <td class="text-center">
                <span
                    class="badge d-inline-flex align-items-center badge-xs
                    {{ $salaryRecord->is_active == 1 ? 'badge-success' : 'badge-danger' }}">
                    <i class="ti ti-point-filled me-1"></i>
                    {{ $salaryRecord->is_active == 1 ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td class="text-center">{{ $salaryRecord->creator_name }}</td>
            <td class="text-center">{{ $salaryRecord->remarks ?? 'N/A' }}</td>
            @if (in_array('Update', $permission) || in_array('Delete',$permission))
            <td class="text-center">
                <div class="action-icon d-inline-flex">
                    @if (in_array('Update', $permission))
                    <a href="#" class="me-2" data-bs-toggle="modal"
                        data-bs-target="#edit_salary" data-id="{{ $salaryRecord->id }}"
                        data-user-id="{{ $salaryRecord->user_id }}"
                        data-basic-salary="{{ $salaryRecord->basic_salary }}"
                        data-effective-date="{{ $salaryRecord->effective_date->format('Y-m-d') }}"
                        data-is-active="{{ $salaryRecord->is_active }}"
                        data-remarks="{{ $salaryRecord->remarks }}"
                        data-salary-type="{{ $salaryRecord->salary_type }}">
                        <i class="ti ti-edit" title="Edit"></i></a>
                    @endif
                    @if (in_array('Delete',$permission))
                    <a href="#" class="btn-delete" data-bs-toggle="modal"
                        data-bs-target="#delete_salary" data-id="{{ $salaryRecord->id }}"
                        data-user-id="{{ $salaryRecord->user_id }}">
                        <i class="ti ti-trash" title="Delete"></i>
                    </a>
                    @endif
                </div>
            </td>
            @endif
        </tr>
    @endforeach