 @php
     $counter = 1;
 @endphp

 @foreach ($employees as $employee)
     @php
         $detail = $employee->employmentDetail;
     @endphp
     <tr>
         <td>
             @if (in_array('Read', $permission) && in_array('Update', $permission))
                 <a href="{{ url('employees/employee-details/' . $employee->id) }}" class="me-2"
                     title="View Full Details"><i class="ti ti-eye"></i></a>
             @endif
             @if (in_array('Update', $permission))
                 <a href="#" class="me-2" onclick="editEmployee({{ $employee->id }})"><i class="ti ti-edit"></i></a>
             @endif
             {{ $detail->employee_id ?? 'N/A' }}
         </td>
         <td>
             <div class="d-flex align-items-center">
                 <a href="{{ url('employee-details') }}" class="avatar avatar-md" data-bs-toggle="modal"
                     data-bs-target="#view_details"><img
                         src="{{ $employee->personalInformation && $employee->personalInformation->profile_picture ? asset('storage/' . $employee->personalInformation->profile_picture) : URL::asset('build/img/users/user-13.jpg') }}"
                         class="img-fluid rounded-circle" alt="img"></a>
                 <div class="ms-2">
                     <p class="text-dark mb-0"><a href="{{ url('employee-details') }}" data-bs-toggle="modal"
                             data-bs-target="#view_details">
                             {{ $employee->personalInformation->last_name ?? '' }}
                             {{ $employee->personalInformation->suffix ?? '' }},
                             {{ $employee->personalInformation->first_name ?? '' }}
                             {{ $employee->personalInformation->middle_name ?? '' }}</a>
                     </p>
                     <span class="fs-12">{{ $employee->employmentDetail->branch->name ?? '' }}</span>
                 </div>
             </div>
         </td>
         <td>{{ $employee->email ?? '-' }}</td>
         <td>{{ $detail?->department?->department_name ?? 'N/A' }}</td>
         <td> {{ $detail?->designation?->designation_name ?? 'N/A' }}</td>
         <td>{{ $detail->date_hired ?? 'N/A' }}</td>
         <td>
             @php
                 $status = (int) ($detail->status ?? -1);
                 $statusText = $status === 1 ? 'Active' : ($status === 0 ? 'Inactive' : 'Unknown');
                 $badgeClass = $status === 1 ? 'badge-success' : ($status === 0 ? 'badge-danger' : 'badge-secondary');
             @endphp
             <span class="badge d-inline-flex align-items-center badge-xs {{ $badgeClass }}">
                 <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
             </span>
         </td>
         @if (in_array('Update', $permission) || in_array('Delete', $permission))
             <td>
                 <div class="action-icon d-inline-flex">

                     @if (in_array('Update', $permission))
                         @if ($status == 0)
                             <a href="#" class="btn-activate" onclick="activateEmployee({{ $employee->id }})"
                                 title="Activate"><i class="ti ti-circle-check"></i></a>
                         @else
                             <a href="#" class="btn-deactivate"
                                 onclick="deactivateEmployee({{ $employee->id }})"><i class="ti ti-cancel"
                                     title="Deactivate"></i></a>
                         @endif
                     @endif
                     @if (in_array('Delete', $permission))
                         <a href="#" class="btn-delete" onclick="deleteEmployee({{ $employee->id }})">
                             <i class="ti ti-trash" title="Delete"></i>
                         </a>
                     @endif
                 </div>
             </td>
         @endif
     </tr>
 @endforeach
