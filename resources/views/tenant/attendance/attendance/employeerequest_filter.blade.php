 @foreach ($attendances as $req)
     <tr>
         <td>
             <div class="d-flex align-items-center">
                 <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                     {{ $req->request_date->format('Y-m-d') }}</p>
                 <a href="#" class="ms-2" data-bs-toggle="tooltip" data-bs-placement="right"
                     data-bs-title="{{ $req->reason ?? 'No reason provided' }}">
                     <i class="ti ti-info-circle text-info"></i>
                 </a>
             </div>
         </td>
         <td>{{ $req->time_only }}</td>
         <td>{{ $req->time_out_only }}</td>
         <td>{{ $req->total_break_minutes_formatted ?? 'N/A' }}</td>
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
             @php
                 $badgeClass = 'badge-info';
                 if ($req->status == 'approved') {
                     $badgeClass = 'badge-success';
                 } elseif ($req->status == 'rejected') {
                     $badgeClass = 'badge-warning';
                 }
             @endphp
             <span class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                 <i class="ti ti-point-filled me-1"></i>{{ ucfirst($req->status) }}
             </span>
         </td>
         <td>
             @if ($req->lastApproverName)
                 <div class="d-flex align-items-center">
                     <a href="javascript:void(0);" class="avatar avatar-md border avatar-rounded">
                         <img src="{{ asset('storage/' . $req->latestApproval->approver->personalInformation->profile_picture) }}"
                             class="img-fluid" alt="avatar">
                     </a>
                     <div class="ms-2">
                         <h6 class="fw-medium mb-0">
                             {{ $req->lastApproverName }}
                         </h6>
                         <span class="fs-12 fw-normal">
                             {{ $req->lastApproverDept }}
                         </span>
                     </div>
                 </div>
             @else
                 &mdash;
             @endif
         </td>
         <td>
             @if ($req->status !== 'approved')
                 <div class="action-icon d-inline-flex">
                     <a href="#" class="me-2" data-bs-toggle="modal" data-bs-target="#edit_request_attendance"
                         data-id="{{ $req->id }}" data-request-date="{{ $req->request_date }}"
                         data-request-in="{{ $req->request_date_in }}" data-request-out="{{ $req->request_date_out }}"
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
