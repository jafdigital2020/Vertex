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
                                                            @if($sus->status === 'suspended' && $sus->violation_start_date === null && $sus->violation_end_date === null)
                                                                <span class="badge bg-secondary">
                                                                    For Violation
                                                                </span>
                                                            @else
                                                                <span class="badge bg-{{ getStatusColor($sus->status) }}">
                                                                    {{ $sus->status ?? '' }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $sus->violationType->name ?? '' }}</td>
                                                        <td class="text-center">
                                                            @if($sus->verbal_reprimand_date)
                                                                {{ $sus->verbal_reprimand_date }}
                                                            @elseif($sus->written_reprimand_date)
                                                                {{ $sus->written_reprimand_date }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td> 
                                                        <td class="text-center">{{ $sus->suspension_start_date ?? '-' }}</td>
                                                        <td class="text-center">{{ $sus->suspension_end_date ?? '-' }}</td>  
                                                        <td class="text-center">{{ $sus->termination_date ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center align-items-center gap-1 flex-nowrap"> 
                                                                <button class="btn btn-sm btn-secondary view-violation"
                                                                    data-id="{{ $sus->id ?? $sus->employee->id }}"
                                                                    title="View Violation Details">
                                                                    <i class="ti ti-eye"></i>
                                                                </button>
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

                                                                    @case('dam_issued')
                                                                        @if (!$sus->dam_file)
                                                                            <button class="btn btn-sm btn-success"
                                                                                onclick="openDamModal({{ $sus->id ?? $sus->employee->id }})"
                                                                                title="Issue DAM">
                                                                                <i class="ti ti-file-check"></i>
                                                                            </button>
                                                                        @endif
                                                                        <button class="btn btn-sm btn-danger"
                                                                            onclick="openViolationModal(
                                                                                {{ $sus->id ?? $sus->employee->id }},
                                                                                '{{ addslashes($sus->violationType->name) }}'
                                                                            )"
                                                                            title="Implement Violation">
                                                                            <i class="ti ti-ban"></i>
                                                                        </button>

                                                                        <!-- @if($sus->violation_start_date === null && $sus->violation_end_date === null)
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
                                                                        @endif -->
                                                                        @break  

                                                                        @case('implemented')
                                                                            @if($sus->termination_date !== null)
                                                                            <button class="btn btn-sm btn-primary"
                                                                                onclick="processLastPay({{ $sus->id ?? $sus->employee->id }})"
                                                                                title="Process Last Pay">
                                                                                <i class="ti ti-receipt"></i>
                                                                            </button> 
                                                                            @endif
                                                                        @break
                                                                @endswitch

                                                            </div>
                                                        </td>  
                                                    </tr> 
                                                @endforeach