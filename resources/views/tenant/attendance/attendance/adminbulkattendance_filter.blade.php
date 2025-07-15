 
  @foreach ($bulkAttendances as $userAtt)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ URL::asset('build/img/users/user-49.jpg') }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $userAtt->user->personalInformation->last_name }},
                                                            {{ $userAtt->user->personalInformation->first_name }}
                                                            {{ $userAtt->user->personalInformation->middle_name }}.</a>
                                                    </h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $userAtt->user->employmentDetail->department->department_name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($userAtt->date_from && $userAtt->date_to)
                                                {{ \Carbon\Carbon::parse($userAtt->date_from)->format('F d, Y') }} -
                                                {{ \Carbon\Carbon::parse($userAtt->date_to)->format('F d, Y') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $userAtt->regular_working_days ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_working_hours ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_overtime_hours ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_nd_hours ?? 'N/A' }}</td>
                                        <td>{{ $userAtt->regular_nd_overtime_hours ?? 'N/A' }}</td>

                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td>
                                                <div class="action-icon d-inline-flex">
                                                    @if (in_array('Update', $permission))
                                                        <a href="#" class="me-2" data-bs-toggle="modal"
                                                            data-bs-target="#edit_bulk_attendance"
                                                            data-id="{{ $userAtt->id }}"
                                                            data-date-from="{{ $userAtt->date_from }}"
                                                            data-date-to="{{ $userAtt->date_to }}"
                                                            data-working-days="{{ $userAtt->regular_working_days }}"
                                                            data-regular-hours="{{ $userAtt->regular_working_hours }}"
                                                            data-ot-hours="{{ $userAtt->regular_overtime_hours }}"
                                                            data-nd-hours="{{ $userAtt->regular_nd_hours }}"
                                                            data-nd-ot-hours="{{ $userAtt->regular_nd_overtime_hours }}"
                                                            data-rest-day="{{ $userAtt->rest_day_work ? '1' : '0' }}"
                                                            data-rest-day-ot="{{ $userAtt->rest_day_ot ? '1' : '0' }}"
                                                            data-rest-day-nd="{{ $userAtt->rest_day_nd ? '1' : '0' }}"
                                                            data-regular-holiday="{{ $userAtt->regular_holiday_hours }}"
                                                            data-special-holiday="{{ $userAtt->special_holiday_hours }}"
                                                            data-regular-holiday-ot="{{ $userAtt->regular_holiday_ot }}"
                                                            data-special-holiday-ot="{{ $userAtt->special_holiday_ot }}"
                                                            data-regular-holiday-nd="{{ $userAtt->regular_holiday_nd }}"
                                                            data-special-holiday-nd="{{ $userAtt->special_holiday_nd }}"><i
                                                                class="ti ti-edit"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="me-2 btn-delete" data-bs-toggle="modal"
                                                            data-bs-target="#delete_bulk_attendance"
                                                            data-id="{{ $userAtt->id }}"
                                                            data-first-name="{{ $userAtt->user->personalInformation->full_name }}"><i
                                                                class="ti ti-trash"></i></a>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach