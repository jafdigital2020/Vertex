  @foreach ($attendances as $att)
      @php
          $status = $att->status;
          $statusText = ucfirst($status);
          if ($status === 'present') {
              $badgeClass = 'badge-success-transparent';
          } elseif ($status === 'late') {
              $badgeClass = 'badge-danger-transparent';
          } else {
              $badgeClass = 'badge-secondary-transparent';
          }
      @endphp
      <tr>
          <td>
              {{ $att->attendance_date->format('Y-m-d') }}
          </td>
          <td>
              @if ($att->is_rest_day)
                  <span class="badge badge-info-transparent d-inline-flex align-items-center">
                      <i class="ti ti-calendar-off me-1"></i>Rest Day
                  </span>
              @elseif ($att->is_holiday)
                  <span class="badge badge-warning-transparent d-inline-flex align-items-center">
                      <i class="ti ti-confetti me-1"></i>Holiday
                      @if ($att->shift)
                          <span class="ms-1">({{ $att->shift->name }})</span>
                      @endif
                  </span>
              @else
                  {{ $att->shift->name ?? '-' }}
              @endif
          </td>
          <td>{{ $att->time_only }}</td>
          <td>
              <span class="badge {{ $badgeClass }} d-inline-flex align-items-center">
                  <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
              </span>
              @if ($status === 'late')
                  <a href="#" class="ms-2" data-bs-toggle="tooltip" data-bs-placement="right"
                      title="{{ $att->late_status_box }}">
                      <i class="ti ti-info-circle text-info"></i>
                  </a>
              @endif
          </td>
          <td class="text-center">
              @if (empty($att->break_in_only) && empty($att->break_out_only))
                  <span class="text-muted">-</span>
              @else
                  <div class="d-flex flex-column align-items-center">
                      <span>{{ $att->break_in_only }} -
                          {{ $att->break_out_only }}</span>
                      @if (!empty($att->break_late) && $att->break_late > 0)
                          <span class="badge badge-danger-transparent d-inline-flex align-items-center mt-1"
                              data-bs-toggle="tooltip" data-bs-placement="top"
                              title="Extended break time by {{ $att->break_late }} minutes">
                              <i class="ti ti-alert-circle me-1"></i>Over Break:
                              {{ $att->break_late }} min
                          </span>
                      @endif
                  </div>
              @endif
          </td>
          <td>
              {{ $att->time_out_only }}
          </td>
          <td>
              {{ $att->total_late_formatted }}
          </td>
          <td>
              @if ($att->time_in_photo_path || $att->time_out_photo_path)
                  <div class="btn-group" style="position: static; overflow: visible;">
                      <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                          data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-container="body"
                          title="View Photo" aria-expanded="false">
                          <i class="ti ti-camera fs-15"></i>
                      </button>
                      <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                          @if ($att->time_in_photo_path)
                              <li>
                                  <a class="dropdown-item" href="{{ Storage::url($att->time_in_photo_path) }}"
                                      target="_blank">Clock-In Photo</a>
                              </li>
                          @endif
                          @if ($att->time_out_photo_path)
                              <li>
                                  <a class="dropdown-item" href="{{ Storage::url($att->time_out_photo_path) }}"
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
              @if (($att->time_in_latitude && $att->time_in_longitude) || ($att->time_out_latitude && $att->time_out_longitude))
                  <div class="btn-group" style="position: static; overflow: visible;">
                      <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                          data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View Location">
                          <i class="ti ti-map-pin fs-15"></i>
                      </button>
                      <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                          @if ($att->time_in_latitude && $att->time_in_longitude)
                              <li>
                                  <a class="dropdown-item view-map-btn" href="#"
                                      data-lat="{{ $att->time_in_latitude }}"
                                      data-lng="{{ $att->time_in_longitude }}">Clock-In
                                      Location</a>
                              </li>
                          @endif
                          @if ($att->time_out_latitude && $att->time_out_longitude)
                              <li>
                                  <a class="dropdown-item view-map-btn" href="#"
                                      data-lat="{{ $att->time_out_latitude }}"
                                      data-lng="{{ $att->time_out_longitude }}">Clock-Out
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
              @if ($att->clock_in_method || $att->clock_out_method)
                  <div class="btn-group" style="position: static; overflow: visible;">
                      <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                          data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-container="body"
                          title="View Device" aria-label="View Device">
                          <i class="ti ti-device-mobile fs-15"></i>
                      </button>
                      <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                          @if ($att->clock_in_method)
                              <li>
                                  <a class="dropdown-item" href="#">
                                      Clock-In Device ({{ $att->clock_in_method }})</a>
                              </li>
                          @endif
                          @if ($att->clock_out_method)
                              <li>
                                  <a class="dropdown-item" href="#">
                                      Clock-Out Device ({{ $att->clock_out_method }})</a>
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
                  {{ $att->total_work_minutes_formatted }}
              </span>
              @if (!empty($att->total_night_diff_minutes_formatted) && $att->total_night_diff_minutes_formatted !== '00:00')
                  <br>
                  <span class="badge badge-info d-inline-flex align-items-center mt-1">
                      <i class="ti ti-moon me-1"></i>
                      Night: {{ $att->total_night_diff_minutes_formatted }}
                  </span>
              @endif
          </td>
      </tr>
  @endforeach
