  @foreach ($employees as $employee)
    @php
        $detail = $employee->employmentDetail;
    @endphp
    <tr>
        <td>
            <a href="{{ url('employees/employee-details/' . $employee->id) }}"
                class="me-2" title="View Full Details"><i class="ti ti-eye"></i></a>
            {{ $detail->employee_id ?? '-' }}
        </td>
        <td>
            <div class="d-flex align-items-center">
                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                    data-bs-toggle="modal" data-bs-target="#view_details">
                    <img src="{{ asset('storage/' . ($employee->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                        class="img-fluid rounded-circle" alt="img">
                </a>
                <div class="ms-2">
                    <p class="text-dark mb-0">
                        <a href="{{ url('employee-details') }}" data-bs-toggle="modal"
                            data-bs-target="#view_details">
                            {{ $employee->personalInformation->last_name ?? '' }}
                            {{ $employee->personalInformation->suffix ?? '' }},
                            {{ $employee->personalInformation->first_name ?? '' }}
                            {{ $employee->personalInformation->middle_name ?? '' }}
                        </a>
                    </p>
                    <span
                        class="fs-12">{{ $employee->employmentDetail->branch->name ?? '' }}</span>
                </div>
            </div>
        </td>
        <td>{{ $employee->email ?? '-' }}</td>
        <td>{{ $detail?->department?->department_name ?? 'N/A' }}</td>
        <td>{{ $detail?->designation?->designation_name ?? 'N/A' }}</td>
        <td>{{ $detail->date_hired ?? 'N/A' }}</td>
        <td>
            @php
                $status = (int) ($detail->status ?? -1);
                $statusText =
                    $status === 1 ? 'Active' : ($status === 0 ? 'Inactive' : 'Unknown');
                $badgeClass =
                    $status === 1
                        ? 'badge-success'
                        : ($status === 0
                            ? 'badge-danger'
                            : 'badge-secondary');
            @endphp
            <span
                class="badge d-inline-flex align-items-center badge-xs {{ $badgeClass }}">
                <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
            </span>
        </td>
        <td>
            <div class="action-icon d-inline-flex">
                @if (in_array('Update', $permission))
                @if ($status == 0)
                    <a href="#" class="btn-activate me-2"
                        onclick="activateEmployee({{ $employee->id }})" title="Activate">
                        <i class="ti ti-circle-check"></i>
                    </a>
                @else
                    <a href="#" class="btn-deactivate me-2"
                        onclick="deactivateEmployee({{ $employee->id }})"
                        title="Deactivate">
                        <i class="ti ti-cancel"></i>
                    </a>
                @endif
                @endif
                @if (in_array('Delete', $permission))
                <a href="#" class="btn-delete"
                    onclick="deleteEmployee({{ $employee->id }})" title="Delete">
                    <i class="ti ti-trash"></i>
                </a>
                @endif
            </div>
        </td>
    </tr>
    @endforeach