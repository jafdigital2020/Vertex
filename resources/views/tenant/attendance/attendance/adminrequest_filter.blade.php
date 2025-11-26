     @foreach ($userAttendances as $req)
         @php
             $status = strtolower($req->status);
             $colors = [
                 'approved' => 'success',
                 'rejected' => 'danger',
                 'pending' => 'info',
             ];
         @endphp
         <tr data-attendance-id="{{ $req->id }}">
             <td>
                 <div class="form-check form-check-md">
                     <input class="form-check-input" type="checkbox" value="{{ $req->id }}">
                 </div>
             </td>
             <td>
                 <div class="d-flex align-items-center file-name-icon">
                     <a href="#" class="avatar avatar-md border avatar-rounded">
                         <img src="{{ asset('storage/' . $req->user->personalInformation->profile_picture) }}"
                             class="img-fluid" alt="img">
                     </a>
                     <div class="ms-2">
                         <h6 class="fw-medium"><a href="#">{{ $req->user->personalInformation->last_name }},
                                 {{ $req->user->personalInformation->first_name }}</a></h6>
                         <span
                             class="fs-12 fw-normal ">{{ $req->user->employmentDetail->department->department_name ?? 'No Department' }}</span>
                     </div>
                 </div>
             </td>
             <td>
                 <div class="d-flex align-items-center">
                     <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                         {{ $req->request_date->format('F j, Y') }}</p>
                     <a href="#" class="ms-2" data-bs-toggle="tooltip" data-bs-placement="right"
                         data-bs-title="{{ $req->reason ?? 'No reason provided' }}">
                         <i class="ti ti-info-circle text-info"></i>
                     </a>
                 </div>
             </td>
             <td>{{ $req->time_only }}</td>
             <td>{{ $req->time_out_only }}</td>
             <td>{{ $req->total_break_minutes_formatted }}</td>
             <td>
                 <span class="badge badge-success d-inline-flex align-items-center">
                     <i class="ti ti-clock-hour-11 me-1"></i>
                     {{ $req->total_request_minutes_formatted }}
                 </span>
                 @if (!empty($req->total_request_nd_minutes_formatted) && $req->total_request_nd_minutes_formatted !== '00:00')
                     <br>
                     <span class="badge badge-info d-inline-flex align-items-center mt-1">
                         <i class="ti ti-moon me-1"></i>
                         Night: {{ $req->total_request_nd_minutes_formatted }}
                     </span>
                 @endif
             </td>
             <td>
                 @if ($req->file_attachment)
                     <a href="{{ asset('storage/' . $req->file_attachment) }}" class="text-primary" target="_blank">
                         <i class="ti ti-file-text"></i> View Attachment
                     </a>
                 @else
                     <span class="text-muted">No Attachment</span>
                 @endif
             </td>
             <td>
                 <div class="dropdown" style="position: static; overflow: visible;">
                     <a href="#" class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                         data-bs-toggle="dropdown">
                         <span
                             class="rounded-circle bg-transparent-{{ $colors[$status] }} d-flex justify-content-center align-items-center me-2">
                             <i class="ti ti-point-filled text-{{ $colors[$status] }}"></i>
                         </span>
                         {{ ucfirst($status) }}
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end p-3">
                         <li>
                             <a href="#"
                                 class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'approved' ? 'active' : '' }}"
                                 data-action="approved" data-reqattendance-id="{{ $req->id }}"
                                 data-bs-toggle="modal" data-bs-target="#approvalModal">
                                 <span
                                     class="rounded-circle bg-transparent-{{ $colors['approved'] }} d-flex justify-content-center align-items-center me-2">
                                     <i class="ti ti-point-filled text-{{ $colors['approved'] }}"></i>
                                 </span>
                                 Approved
                             </a>
                         </li>
                         <li>
                             <a href="#"
                                 class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'rejected' ? 'active' : '' }}"
                                 data-action="rejected" data-reqattendance-id="{{ $req->id }}"
                                 data-bs-toggle="modal" data-bs-target="#approvalModal">
                                 <span
                                     class="rounded-circle bg-transparent-{{ $colors['rejected'] }} d-flex justify-content-center align-items-center me-2">
                                     <i class="ti ti-point-filled text-{{ $colors['rejected'] }}"></i>
                                 </span>
                                 Rejected
                             </a>
                         </li>
                         <li>
                             <a href="#"
                                 class="dropdown-item d-flex align-items-center js-approve {{ $status === 'pending' ? 'active' : '' }}"
                                 data-action="CHANGES_REQUESTED" data-reqattendance-id="{{ $req->id }}">
                                 <span
                                     class="rounded-circle bg-transparent-{{ $colors['pending'] }} d-flex justify-content-center align-items-center me-2">
                                     <i class="ti ti-point-filled text-{{ $colors['pending'] }}"></i>
                                 </span>
                                 Pending
                             </a>
                         </li>
                     </ul>
                 </div>
             </td>
             <td>
                 @if (count($req->next_approvers))
                     {{ implode(', ', $req->next_approvers) }}
                 @else
                     —
                 @endif
             </td>
             <td class="align-middle text-center">
                 <div class="d-flex flex-column">
                     {{-- 1) Approver name --}}
                     <span class="fw-semibold">
                         {{ $req->latest_approver ?? '—' }}
                         <a href="#" data-bs-toggle="tooltip" data-bs-placement="right"
                             data-bs-title="{{ $req->latestApproval->comment ?? 'No comment' }}">
                             <i class="ti ti-info-circle text-info"></i></a>
                     </span>
                     {{-- Approval date/time --}}
                     @if ($req->latestApproval)
                         <small class="text-muted mt-1">
                             {{ \Carbon\Carbon::parse($req->latestApproval->acted_at)->format('d M Y, h:i A') }}
                         </small>
                     @endif
                 </div>
             </td>
             <td>
                 @if ($req->status !== 'approved')
                     <div class="action-icon d-inline-flex">
                         <a href="#" class="me-2" data-bs-toggle="modal"
                             data-bs-target="#edit_request_attendance" data-id="{{ $req->id }}"
                             data-request-date="{{ $req->request_date }}"
                             data-request-in="{{ $req->request_date_in }}"
                             data-request-out="{{ $req->request_date_out }}"
                             data-total-minutes="{{ $req->total_request_minutes }}"
                             data-total-break="{{ $req->total_break_minutes }}"
                             data-total-nd="{{ $req->total_request_nd_minutes }}" data-reason="{{ $req->reason }}"
                             data-file-attachment="{{ $req->file_attachment }}"><i class="ti ti-edit"></i></a>
                         <a href="#" data-bs-toggle="modal" class="btn-delete"
                             data-bs-target="#delete_request_attendance" data-id="{{ $req->id }}"><i
                                 class="ti ti-trash"></i></a>
                     </div>
                 @endif
             </td>
         </tr>
     @endforeach
