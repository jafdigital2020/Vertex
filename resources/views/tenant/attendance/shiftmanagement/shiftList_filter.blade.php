@foreach ($shifts as $shift)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td>{{ $shift->name }}</td>
        <td>{{ $shift->branch->name ?? 'All Branches' }}</td>
        <td class="text-center">{{ $shift->start_time }}</td>
        <td class="text-center">{{ $shift->end_time }}</td>
        <td class="text-center">{{ $shift->break_minutes }}</td>
        <td class="text-center">{{ $shift->creator_name }}</td>
        <td class="text-center">{{ $shift->updater_name }}</td>
        @if (in_array('Update', $permission) || in_array('Delete', $permission))
            <td class="text-center">
                <div class="action-icon d-inline-flex">
                    @if (in_array('Update', $permission))
                        <a href="#" class="me-2 editShiftBtn" data-bs-toggle="modal"
                            data-bs-target="#edit_shiftlist" data-id="{{ $shift->id }}"
                            data-name="{{ $shift->name }}" data-start-time="{{ $shift->start_time }}"
                            data-end-time="{{ $shift->end_time }}" data-break-minutes="{{ $shift->break_minutes }}"
                            data-notes="{{ $shift->notes }}" data-branch-id="{{ $shift->branch_id }}"><i
                                class="ti ti-edit"></i></a>
                    @endif
                    @if (in_array('Delete', $permission))
                        <a href="#" class="btn-delete deleteShiftBtn" data-bs-toggle="modal"
                            data-bs-target="#delete_shift" data-id="{{ $shift->id }}"
                            data-name="{{ $shift->name }}"><i class="ti ti-trash"></i></a>
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach
