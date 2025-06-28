  @foreach ($employees as $emp)
    <tr data-user-id="{{ $emp->id }}">
        <td class="text-start">
            <div class="d-flex align-items-center">
                <img src="{{ $emp->personalInformation->profile_picture ? asset('storage/' .$emp->personalInformation->profile_picture) : 'https://via.placeholder.com/40' }}"
                    class="rounded-circle me-2" width="40" height="40" alt="Profile Picture"> 
                    {{ $emp->personalInformation->first_name }} {{ $emp->personalInformation->last_name }} 
            </div>
        </td>
        @foreach ($dateRange as $date)
            @php
                $dateStr = $date->format('Y-m-d');
                $shifts = $assignments[$emp->id][$dateStr] ?? [];
            @endphp
            <td class="p-2 align-middle">
                @if (empty($shifts))
                    <span class="badge bg-danger">No Shift</span>
                @else
                    @foreach ($shifts as $shift)
                        @if (!empty($shift['rest_day']))
                            <span class="badge bg-warning text-dark">Rest Day</span>
                        @else
                            <div class="badge bg-outline-success d-flex flex-column align-items-center mb-1">
                                <div>{{ $shift['name'] }}</div>
                                <small>{{ $shift['start_time'] }} - {{ $shift['end_time'] }}</small>
                            </div>
                        @endif
                    @endforeach
                @endif
            </td>
        @endforeach
    </tr>
@endforeach