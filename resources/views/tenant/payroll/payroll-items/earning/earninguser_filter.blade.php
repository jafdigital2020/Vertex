   @foreach ($userEarnings as $userEarning)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td>{{ $userEarning->user->personalInformation->last_name }},
            {{ $userEarning->user->personalInformation->first_name }} </td>
        <td class="text-center">{{ $userEarning->earningType->name }}</td>
        <td class="text-center">{{ $userEarning->amount }}</td>
        <td class="text-center">{{ ucwords(str_replace('_', ' ', $userEarning->frequency)) }}</td>
        <td class="text-center">{{ $userEarning->effective_start_date?->format('M j, Y') ?? '' }} -
            {{ $userEarning->effective_end_date?->format('M j, Y') ?? '' }} </td>
        <td class="text-center">{{ ucfirst($userEarning->type) }}</td>
        <td class="text-center">
            <span
                class="badge d-inline-flex align-items-center badge-xs
                {{ $userEarning->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($userEarning->status) }}
            </span>
        </td>
        <td class="text-center">{{ $userEarning->creator_name }}</td>
        <td class="text-center">{{ $userEarning->updater_name }}</td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if(in_array('Update',$permission))
                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_earning_user"
                    data-id="{{ $userEarning->id }}"
                    data-earning-type-id="{{ $userEarning->earning_type_id }}"
                    data-type="{{ $userEarning->type }}"
                    data-amount="{{ $userEarning->amount }}"
                    data-frequency="{{ $userEarning->frequency }}"
                    data-effective_start_date="{{ $userEarning->effective_start_date?->format('Y-m-d') ?? '' }}"
                    data-effective_end_date="{{ $userEarning->effective_end_date?->format('Y-m-d') ?? '' }}"
                    data-status="{{ $userEarning->status }}">
                    <i class="ti ti-edit" title="Edit"></i>
                </a>
                @endif
                @if(in_array('Delete',$permission))
                <a href="#" class="btn-delete" data-bs-toggle="modal"
                    data-bs-target="#delete_earning_user"
                    data-id="{{ $userEarning->id }}"
                    data-name="{{ $userEarning->user->personalInformation->last_name }}, {{ $userEarning->user->personalInformation->first_name }}">
                    <i class="ti ti-trash" title="Delete"></i>
                </a>
                @endif
            </div>
        </td>
        @endif
    </tr>
@endforeach