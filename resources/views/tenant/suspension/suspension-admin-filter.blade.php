 @php
                                                function getStatusColor($status) {
                                                    switch ($status) {
                                                        case 'pending': 
                                                            return 'warning';
                                                        case 'implemented': 
                                                            return 'info';
                                                        case 'completed': 
                                                            return 'success';
                                                        default: 
                                                            return 'secondary';
                                                    }
                                                }
                                            @endphp
                                               @foreach ($suspension as $idx => $sus) 
                                                    <tr>    
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td>{{  $sus->employee->personalInformation->first_name ?? '' }}  {{   $sus->employee->personalInformation->last_name ?? '' }}</td> 
                                                        <td>{{ $sus->employee->employmentDetail->employee_id ?? '' }}</td>
                                                        <td>{{ $sus->employee->employmentDetail->department->department_name ?? '' }}</td>
                                                        <td>{{ $sus->employee->employmentDetail->designation->designation_name ?? '' }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ getStatusColor($sus->status) }}">
                                                                {{ $sus->status ?? '' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $sus->suspension_type ?? '' }}</td>
                                                        <td>{{ $sus->suspension_start_date ?? '' }}</td>
                                                        <td>{{ $sus->suspension_end_date ?? '' }}</td>
                                                        <td class="text-center">
                                                            <div > 
                                                                <button class="btn btn-sm btn-primary edit-suspension"
                                                                    data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                    title="Edit Suspension">
                                                                    <i class="ti ti-edit"></i>
                                                                </button>
 
                                                                @if ($sus->status === 'pending')
                                                                    <button class="btn btn-sm btn-warning issue-nowe"
                                                                        data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                        title="Issue NOWE">
                                                                        <i class="ti ti-mail"></i>
                                                                    </button>
                                                                @else
                                                                    <button class="btn btn-sm btn-secondary view-suspension"
                                                                        data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                        title="View Suspension Details">
                                                                        <i class="ti ti-eye"></i>
                                                                    </button>
                                                                @endif
                                                            </div> 
                                                            @switch($sus->status)
                                                                @case('awaiting_reply')
                                                                @case('under_investigation')
                                                                    <button class="btn btn-sm btn-info"
                                                                        onclick="openInvestigationModal({{ $sus->id ?? $sus->employee->id }})"
                                                                        title="Upload Investigation Report">
                                                                        <i class="ti ti-upload"></i>
                                                                    </button>
                                                                    @break

                                                                @case('for_dam_issuance')
                                                                    @if (!$sus->dam_file)
                                                                        <button class="btn btn-sm btn-success"
                                                                            onclick="openDamModal({{ $sus->id ?? $sus->employee->id }})"
                                                                            title="Issue DAM">
                                                                            <i class="ti ti-file-check"></i>
                                                                        </button>
                                                                    @endif
                                                                    @break

                                                                @case('suspended')
                                                                    @if (!$sus->dam_file)
                                                                        <button class="btn btn-sm btn-success"
                                                                            onclick="openDamModal({{$sus->id ?? $sus->employee->id }})"
                                                                            title="Issue DAM">
                                                                            <i class="ti ti-file-check"></i>
                                                                        </button>
                                                                    @endif
                                                                    <button class="btn btn-sm btn-danger ms-1"
                                                                        onclick="openSuspendModal({{ $sus->id ?? $sus->employee->id  }})"
                                                                        title="Implement Suspension">
                                                                        <i class="ti ti-ban"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-secondary ms-1"
                                                                        onclick="completeSuspension({{ $sus->id ?? $sus->employee->id  }})"
                                                                        title="Complete Suspension">
                                                                        <i class="ti ti-check"></i>
                                                                    </button>
                                                                    @break

                                                                @case('completed')
                                                                    <button class="btn btn-sm btn-secondary" disabled title="Completed">
                                                                        <i class="ti ti-check"></i>
                                                                    </button>
                                                                    @break
                                                            @endswitch
                                                        </td>
                                                    </tr> 
                                                @endforeach