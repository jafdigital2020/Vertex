 @foreach($suspensions as $index => $s)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $s->offense_details ?? '—' }}</td>
                                                @php
                                                    switch($s->status) {
                                                        case 'pending': $statusColor = 'warning'; break;
                                                        case 'awaiting_reply': $statusColor = 'info'; break;
                                                        case 'under_investigation': $statusColor = 'primary'; break;
                                                        case 'for_dam_issuance': $statusColor = 'secondary'; break;
                                                        case 'suspended': $statusColor = 'danger'; break;
                                                        case 'completed': $statusColor = 'success'; break;
                                                        default: $statusColor = 'secondary';
                                                    }
                                                @endphp
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $statusColor }}">
                                                        {{ ucfirst(str_replace('_', ' ', $s->status)) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">{{ $s->suspension_type ? strtoupper(str_replace('_', ' ', $s->suspension_type)) : '' }}</td>
                                                <td class="text-center">{{ $s->suspension_start_date ?? '' }}</td>
                                                <td class="text-center">{{ $s->suspension_end_date ?? '' }}</td>
                                                <td class="text-center">
                                                    @if($s->information_report_file)
                                                        <a href="{{ asset('storage/' . $s->information_report_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ti ti-download me-1"></i>View
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-info view-suspension" data-id="{{ $s->id }}" title="View Details">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    @if( $s->status === 'awaiting_reply')
                                                        <button class="btn btn-sm btn-success ms-1" onclick="openReplySuspensionModal({{ $s->id }})" title="Submit Reply">
                                                            <i class="ti ti-message"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr> 
                                            @endforeach