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
                                               @foreach ($violation as $idx => $sus) 
                                                    <tr>    
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td>{{  $sus->employee->personalInformation->first_name ?? '' }}  {{   $sus->employee->personalInformation->last_name ?? '' }}</td> 
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->employee_id ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->department->department_name ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->employee->employmentDetail->designation->designation_name ?? '' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ getStatusColor($sus->status) }}">
                                                                {{ $sus->status ?? '' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">{{ $sus->violation_type ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->violation_start_date ?? '' }}</td>
                                                        <td class="text-center">{{ $sus->violation_end_date ?? '' }}</td>  
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center align-items-center gap-1 flex-nowrap">

                                                            <button class="btn btn-sm btn-primary edit-violation"
                                                                data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                title="Edit Violation">
                                                                <i class="ti ti-edit"></i>
                                                            </button>

                                                            @if ($sus->status === 'pending')
                                                                <button class="btn btn-sm btn-warning issue-nowe"
                                                                    data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                    title="Issue NOWE">
                                                                    <i class="ti ti-mail"></i>
                                                                </button>
                                                            @else
                                                                <button class="btn btn-sm btn-secondary view-violation"
                                                                    data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                    title="View Violation Details">
                                                                    <i class="ti ti-eye"></i>
                                                                </button>
                                                            @endif

                                                            @switch($sus->status)  
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
                                                                            onclick="openDamModal({{ $sus->id ?? $sus->employee->id }})"
                                                                            title="Issue DAM">
                                                                            <i class="ti ti-file-check"></i>
                                                                        </button>
                                                                    @endif
                                                                    @if($sus->violation_start_date === null && $sus->violation_end_date === null)
                                                                    <button class="btn btn-sm btn-danger"
                                                                        onclick="openSuspendModal({{ $sus->id ?? $sus->employee->id }})"
                                                                        title="Implement Violation">
                                                                        <i class="ti ti-ban"></i>
                                                                    </button>
                                                                    @endif
                                                                    @if($sus->violation_start_date !== null && $sus->violation_end_date !== null)
                                                                    <button class="btn btn-sm btn-secondary"
                                                                        onclick="completeViolation({{ $sus->id ?? $sus->employee->id }})"
                                                                        title="Complete Violation">
                                                                        <i class="ti ti-check"></i>
                                                                    </button>
                                                                    @endif
                                                                    @break  
                                                            @endswitch

                                                        </div>
                                                    </td>  
                                                    </tr> 
                                                @endforeach