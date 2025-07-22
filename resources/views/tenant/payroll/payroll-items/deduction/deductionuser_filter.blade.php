@foreach ($userDeductions as $userDeduction)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ $userDeduction->user->personalInformation->last_name }},
                                            {{ $userDeduction->user->personalInformation->first_name }} </td>
                                        <td class="text-center">{{ $userDeduction->deductionType->name }}</td>
                                        <td class="text-center">{{ $userDeduction->amount }}</td>
                                        <td class="text-center">{{ ucwords(str_replace('_', ' ', $userDeduction->frequency)) }}</td>
                                        <td class="text-center">{{ $userDeduction->effective_start_date?->format('M j, Y') ?? '' }} -
                                            {{ $userDeduction->effective_end_date?->format('M j, Y') ?? '' }} </td>
                                        <td class="text-center">{{ ucfirst($userDeduction->type) }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $userDeduction->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($userDeduction->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $userDeduction->creator_name }}</td>
                                        <td class="text-center">{{ $userDeduction->updater_name }}</td>
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if(in_array('Update',$permission))
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_deduction_user"
                                                    data-id="{{ $userDeduction->id }}"
                                                    data-deduction-type-id="{{ $userDeduction->deduction_type_id }}"
                                                    data-type="{{ $userDeduction->type }}"
                                                    data-amount="{{ $userDeduction->amount }}"
                                                    data-frequency="{{ $userDeduction->frequency }}"
                                                    data-effective_start_date="{{ $userDeduction->effective_start_date?->format('Y-m-d') ?? '' }}"
                                                    data-effective_end_date="{{ $userDeduction->effective_end_date?->format('Y-m-d') ?? '' }}"
                                                    data-status="{{ $userDeduction->status }}">
                                                    <i class="ti ti-edit" title="Edit"></i>
                                                </a>
                                                @endif
                                                @if( in_array('Delete',$permission))
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_deduction_user"
                                                    data-id="{{ $userDeduction->id }}"
                                                    data-name="{{ $userDeduction->user->personalInformation->last_name }}, {{ $userDeduction->user->personalInformation->first_name }}">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach