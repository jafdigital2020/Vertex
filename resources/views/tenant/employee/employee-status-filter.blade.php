@foreach ($employees as $employee)
    <tr>
        @if (in_array('Update', $permission))
            <td><input type="checkbox" class="employee-checkbox" value="{{ $employee->id }}"></td>
        @endif
        <td>{{ $employee->employmentDetail->employee_id ?? 'N/A' }}</td>
        <td>{{ $employee->personalInformation->full_name ?? 'N/A' }}</td>
        <td>{{ $employee->employmentDetail->branch->name ?? 'N/A' }}</td>
        <td>{{ $employee->employmentDetail->department->department_name ?? 'N/A' }}</td>
        <td>{{ $employee->employmentDetail->designation->designation_name ?? 'N/A' }}</td>
        <td>
            @php
                $status = $employee->employmentDetail->employment_state ?? 'N/A';
                $badgeClass = match ($status) {
                    'Active' => 'bg-success',
                    'AWOL' => 'bg-dark',
                    'Resigned' => 'bg-info',
                    'Terminated' => 'bg-danger',
                    'Suspended' => 'bg-secondary',
                    'Floating' => 'bg-primary',
                    default => 'bg-light text-dark'
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $status }}</span>
        </td>
        @if (in_array('Update', $permission))
            <td>
                <button class="btn btn-sm btn-primary update-status-btn" data-user-id="{{ $employee->id }}"
                    data-current-status="{{ $employee->employmentDetail->employment_state ?? '' }}"
                    data-employee-name="{{ $employee->personalInformation->full_name ?? '' }}">
                    <i class="ti ti-edit"></i> Update
                </button>
            </td>
        @endif
    </tr>
@endforeach