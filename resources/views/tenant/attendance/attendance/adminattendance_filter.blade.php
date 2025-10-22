 @foreach ($userAttendances as $userAtt)
     @php
         $status = $userAtt->status;
         $statusText = ucfirst($status);
         if ($status === 'present') {
             $badgeClass = 'badge-success-transparent';
         } elseif ($status === 'late') {
             $badgeClass = 'badge-danger-transparent';
         } else {
             $badgeClass = 'badge-secondary-transparent';
         }
     @endphp
     <tr data-attendance-id="{{ $userAtt->id }}">
         <td>
             <div class="form-check form-check-md">
                 <input class="form-check-input" type="checkbox" value="{{ $userAtt->id }}">
             </div>
         </td>
         <td>
             <div class="d-flex align-items-center file-name-icon">
                 <a href="#" class="avatar avatar-md border avatar-rounded">
                     @if ($userAtt->user->personalInformation->profile_picture)
                         <img src="{{ asset('storage/' . $userAtt->user->personalInformation->profile_picture) }}"
                             class="img-fluid" alt="img">
                     @else
                         <img src="{{ URL::asset('build/img/users/user-49.jpg') }}" class="img-fluid" alt="img">
                     @endif
                 </a>
                 <div class="ms-2">
                     <h6 class="fw-medium"><a href="#">{{ $userAtt->user->personalInformation->last_name }},
                             {{ $userAtt->user->personalInformation->first_name }}
                             {{ $userAtt->user->personalInformation->middle_name }}.</a>
                     </h6>
                     <span
                         class="fs-12 fw-normal ">{{ $userAtt->user->employmentDetail->department->department_name }}</span>
                 </div>
             </div>
         </td>
         <td class="text-center">
             @if ($userAtt->attendance_date)
                 {{ \Carbon\Carbon::parse($userAtt->attendance_date)->format('F j, Y') }}
             @else
                 <span class="text-muted">-</span>
             @endif
         </td>
         <td class="text-center">{{ $userAtt->shift->name ?? '-' }}</td>
         <td class="text-center">
             <span class="badge {{ $badgeClass }} d-inline-flex align-items-center">
                 <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
             </span>
             @if ($status === 'late')
                 <a href="#" class="ms-2" data-bs-toggle="tooltip" data-bs-placement="right"
                     title="{{ $userAtt->late_status_box }}">
                     <i class="ti ti-info-circle text-info"></i>
                 </a>
             @endif
         </td>
         <td class="text-center">{{ $userAtt->time_only }}</td>
         <td class="text-center">
             @if (empty($userAtt->break_in_only) && empty($userAtt->break_out_only))
                 <span class="text-muted">-</span>
             @else
                 <div class="d-flex flex-column align-items-center">
                     <span>{{ $userAtt->break_in_only }} - {{ $userAtt->break_out_only }}</span>
                     @if (!empty($userAtt->break_late) && $userAtt->break_late > 0)
                         <span class="badge badge-danger-transparent d-inline-flex align-items-center mt-1"
                             data-bs-toggle="tooltip" data-bs-placement="top"
                             title="Extended break time by {{ $userAtt->break_late }} minutes">
                             <i class="ti ti-alert-circle me-1"></i>Over Break: {{ $userAtt->break_late }} min
                         </span>
                     @endif
                 </div>
             @endif
         </td>
         <td class="text-center">{{ $userAtt->time_out_only }}</td>
         <td class="text-center">{{ $userAtt->total_late_formatted }}</td>
         <td>
             @if ($userAtt->time_in_photo_path || $userAtt->time_out_photo_path)
                 <div class="btn-group" style="position: static; overflow: visible;">
                     <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                         data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-container="body">
                         View Photo
                     </button>
                     <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                         @if ($userAtt->time_in_photo_path)
                             <li>
                                 <a class="dropdown-item" href="{{ Storage::url($userAtt->time_in_photo_path) }}"
                                     target="_blank">Clock-In Photo</a>
                             </li>
                         @endif
                         @if ($userAtt->time_out_photo_path)
                             <li>
                                 <a class="dropdown-item" href="{{ Storage::url($userAtt->time_out_photo_path) }}"
                                     target="_blank">Clock-Out Photo</a>
                             </li>
                         @endif
                     </ul>
                 </div>
             @else
                 <span class="text-muted">No Photo</span>
             @endif
         </td>
         <td>
             @if (
                 ($userAtt->time_in_latitude && $userAtt->time_in_longitude) ||
                     ($userAtt->time_out_latitude && $userAtt->time_out_longitude))
                 <div class="btn-group" style="position: static; overflow: visible;">
                     <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                         data-bs-toggle="dropdown">
                         View Location
                     </button>
                     <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                         @if ($userAtt->time_in_latitude && $userAtt->time_in_longitude)
                             <li>
                                 <a class="dropdown-item view-map-btn" href="#"
                                     data-lat="{{ $userAtt->time_in_latitude }}"
                                     data-lng="{{ $userAtt->time_in_longitude }}">Clock-In
                                     Location</a>
                             </li>
                         @endif
                         @if ($userAtt->time_out_latitude && $userAtt->time_out_longitude)
                             <li>
                                 <a class="dropdown-item view-map-btn" href="#"
                                     data-lat="{{ $userAtt->time_out_latitude }}"
                                     data-lng="{{ $userAtt->time_out_longitude }}">Clock-Out
                                     Location</a>
                             </li>
                         @endif
                     </ul>
                 </div>
             @else
                 <span class="text-muted">No Location</span>
             @endif
         </td>
         <td>
             @if ($userAtt->clock_in_method || $userAtt->clock_out_method)
                 <div class="btn-group" style="position: static; overflow: visible;">
                     <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                         data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-container="body">
                         View Device
                     </button>
                     <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                         @if ($userAtt->clock_in_method)
                             <li>
                                 <a class="dropdown-item" href="#">
                                     Clock-In Device ({{ $userAtt->clock_in_method }})</a>
                             </li>
                         @endif
                         @if ($userAtt->clock_out_method)
                             <li>
                                 <a class="dropdown-item" href="#">
                                     Clock-Out Device ({{ $userAtt->clock_out_method }})</a>
                             </li>
                         @endif
                     </ul>
                 </div>
             @else
                 <span class="text-muted">No Device</span>
             @endif
         </td>
         <td>
             <span class="badge badge-success d-inline-flex align-items-center">
                 <i class="ti ti-clock-hour-11 me-1"></i>
                 {{ $userAtt->total_work_minutes_formatted }}
             </span>
             @if (!empty($userAtt->total_night_diff_minutes_formatted) && $userAtt->total_night_diff_minutes_formatted !== '00:00')
                 <br>
                 <span class="badge badge-info d-inline-flex align-items-center mt-1">
                     <i class="ti ti-moon me-1"></i>
                     Night: {{ $userAtt->total_night_diff_minutes_formatted }}
                 </span>
             @endif
         </td>
         @if (in_array('Update', $permission) || in_array('Delete', $permission))
             <td>
                 <div class="action-icon d-inline-flex">
                     @if (in_array('Update', $permission))
                         <a href="#" class="me-2" data-bs-toggle="modal" data-bs-target="#edit_attendance"
                             data-id="{{ $userAtt->id }}"
                             data-clock-in="{{ optional($userAtt->date_time_in)->format('H:i') }}"
                             data-clock-out="{{ optional($userAtt->date_time_out)->format('H:i') }}"
                             data-total-late="{{ $userAtt->total_late_formatted }}"
                             data-work-minutes="{{ $userAtt->total_work_minutes_formatted }}"
                             data-attendance-date="{{ $userAtt->attendance_date->format('Y-m-d') }}"
                             data-nightdiff-minutes="{{ $userAtt->total_night_diff_minutes_formatted }}"
                             data-undertime-minutes="{{ $userAtt->total_undertime_minutes_formatted }}"
                             data-status="{{ $userAtt->status }}"><i class="ti ti-edit"></i></a>
                     @endif
                     @if (in_array('Delete', $permission))
                         <a href="#" class="me-2 btn-delete" data-bs-toggle="modal"
                             data-bs-target="#delete_attendance" data-id="{{ $userAtt->id }}"
                             data-first-name="{{ $userAtt->user->personalInformation->first_name }}"><i
                                 class="ti ti-trash"></i></a>
                     @endif
                 </div>
             </td>
         @endif
     </tr>
 @endforeach
