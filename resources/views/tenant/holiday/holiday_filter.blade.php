 @foreach ($holidays as $holiday)
    @php
        $statusClass = $holiday->status === 'active' ? 'badge-success' : 'badge-danger';
        $statusLabel = ucfirst($holiday->status);

        $paidClass = $holiday->is_paid ? 'badge-success' : 'badge-secondary';
        $paidLabel = $holiday->is_paid ? 'Paid' : 'Unpaid';
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td>
            <h6 class="fw-medium"><a href="#">{{ $holiday->name }}</a></h6>
        </td>
        <td>
            @if ($holiday->recurring && $holiday->month_day)
                {{ \Carbon\Carbon::createFromFormat('m-d', $holiday->month_day)->format('F j') }}
                <span class="badge bg-primary fs-9 py-0 px-1">Recurring</span>
            @elseif($holiday->date)
                {{ \Carbon\Carbon::parse($holiday->date)->format('F j, Y') }}
            @else
                —
            @endif
        </td>
        <td>{{ ucfirst(strtolower($holiday->type)) }}</td>
        <td class="text-center"> <span class="badge {{ $paidClass }}">
                <i class="ti ti-point-filled"></i> {{ $paidLabel }}
            </span></td>
        <td class="text-center">
            <span class="badge {{ $statusClass }}">
                <i class="ti ti-point-filled"></i> {{ $statusLabel }}
            </span>
        </td>
        @if(in_array('Update', $permission) || in_array('Delete',$permission))
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if(in_array('Update', $permission))
                <a href="#" class="me-2" data-bs-toggle="modal"
                    data-bs-target="#edit_holiday" data-id="{{ $holiday->id }}"
                    data-name="{{ $holiday->name }}"
                    data-holiday-type="{{ $holiday->type }}"
                    data-recurring="{{ $holiday->recurring ? '1' : '0' }}"
                    data-month-day="{{ $holiday->month_day }}"
                    data-date="{{ $holiday->date }}"
                    data-is-paid="{{ $holiday->is_paid ? '1' : '0' }}"
                    data-status="{{ $holiday->status }}"><i class="ti ti-edit"></i></a>
                @endif
                @if(in_array('Delete', $permission))
                <a href="javascript:void(0);" data-bs-toggle="modal" class="btn-delete"
                    data-bs-target="#delete_holiday" data-id="{{ $holiday->id }}"
                    data-name="{{ $holiday->name }}"><i class="ti ti-trash"></i></a>
                @endif
            </div>
        </td>
        @endif
    </tr>
@endforeach