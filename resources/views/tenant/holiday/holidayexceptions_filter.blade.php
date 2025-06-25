 @foreach ($holidayExceptions as $holidayException)
    @php
        $statusClass =
            $holidayException->status === 'active' ? 'badge-success' : 'badge-danger';
        $statusLabel = ucfirst($holidayException->status);
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                    data-bs-toggle="modal" data-bs-target="#view_details"><img
                        src="{{ asset('storage/' . $holidayException->user->personalInformation->profile_picture) }}"
                        class="img-fluid rounded-circle" alt="img"></a>
                <div class="ms-2">
                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                            data-bs-toggle="modal" data-bs-target="#view_details">
                            {{ $holidayException->user->personalInformation->last_name }}
                            {{ $holidayException->user->personalInformation->suffix }},
                            {{ $holidayException->user->personalInformation->first_name }}
                            {{ $holidayException->user->personalInformation->middle_name }}</a>
                    </p>
                    <span class="fs-12"></span>
                </div>
            </div>
        </td>
        <td>{{ $holidayException->user->employmentDetail->branch->name }}</td>
        <td>{{ $holidayException->user->employmentDetail->department->department_name }}
        </td>
        <td>{{ $holidayException->holiday->name }}</td>
        <td>
            <span class="badge {{ $statusClass }}">
                <i class="ti ti-point-filled"></i> {{ $statusLabel }}
            </span>
        </td>
        <td>{{ $holidayException->creator_name }}</td>
        <td>{{ $holidayException->updater_name }}</td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                
                @if(in_array('Update',$permission))
                @if( $holidayException->status == 'active')
                <a href="#" class="btn-deactivate" data-bs-toggle="modal"
                    data-bs-target="#deactivate_holiday"
                    data-id="{{ $holidayException->id }}"
                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"><i
                        class="ti ti-cancel" title="Deactivate"></i></a>
                @else
                <a href="#" class="btn-activate" data-bs-toggle="modal"
                    data-bs-target="#activate_holiday"
                    data-id="{{ $holidayException->id }}"
                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"
                    title="Activate"><i class="ti ti-circle-check"></i></a>
                @endif
                @endif 
                @if(in_array('Delete',$permission))
                <a href="javascript:void(0);" data-bs-toggle="modal" class="btn-delete"
                    data-bs-target="#delete_holiday_exception"
                    data-id="{{ $holidayException->id }}"
                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"
                    title="Delete"><i class="ti ti-trash"></i></a>
                @endif
            </div>
        </td>
        @endif
    </tr>
@endforeach