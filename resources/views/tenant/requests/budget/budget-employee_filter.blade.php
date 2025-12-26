 @foreach ($budgetRequests as $budget)
        <tr>
            <td>
                <div class="d-flex align-items-center file-name-icon">
                    <a href="#" class="avatar avatar-md border avatar-rounded">
                        <img src="{{ asset('storage/' . $budget->user->personalInformation->profile_picture) }}"
                            class="img-fluid" alt="img">
                    </a>
                    <div class="ms-2">
                        <h6 class="fw-medium"><a
                                href="#">{{ $budget->user->personalInformation->last_name }},
                                {{ $budget->user->personalInformation->first_name }}</a></h6>
                        <span
                            class="fs-12 fw-normal ">{{ $budget->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </td>
            <td class="text-center">
                {{ $budget->created_at ? \Carbon\Carbon::parse($budget->created_at)->format('F j, Y') : 'N/A' }}
            </td>
            <td class="text-center">{{ $budget->project_name ?? 'N/A' }}</td>
            <td class="text-center">{{ $budget->budget_category ?? 'N/A' }}</td>
            <td class="text-center">₱{{ number_format($budget->requested_amount, 2) }}</td>
            <td class="text-center">
                @if ($budget->start_date && $budget->end_date)
                    {{ \Carbon\Carbon::parse($budget->start_date)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($budget->end_date)->format('M j, Y') }}
                @else
                    N/A
                @endif
            </td>
            <td class="text-center">{{ Str::limit($budget->justification ?? 'N/A', 30) }}</td>
            <td class="text-center">
                @if ($budget->attachment)
                    <a href="{{ asset('storage/' . $budget->attachment) }}"
                        class="text-primary" target="_blank">
                        <i class="ti ti-file-text"></i> View Attachment
                    </a>
                @else
                    <span class="text-muted">No Attachment</span>
                @endif
            </td>
            <td class="text-center">
                @php
                    $badgeClass = 'badge-info';
                    if ($budget->status == 'approved') {
                        $badgeClass = 'badge-success';
                    } elseif ($budget->status == 'rejected') {
                        $badgeClass = 'badge-warning';
                    }
                @endphp
                <span
                    class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($budget->status) }}
                </span>
            </td>
            <td class="text-center">
                @if ($budget->approver_name ?? false)
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);"
                            class="avatar avatar-md border avatar-rounded">
                            <img src="{{ asset('storage/' . $budget->approver_picture) }}"
                                class="img-fluid" alt="avatar">
                        </a>
                        <div class="ms-2">
                            <h6 class="fw-medium mb-0">
                                {{ $budget->approver_name }}
                            </h6>
                            <span class="fs-12 fw-normal">
                                {{ $budget->approver_dept }}
                            </span>
                        </div>
                    </div>
                @else
                    &mdash;
                @endif
            </td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
            <td class="text-center">
                @if ($budget->status !== 'approved')
                    <div class="action-icon d-inline-flex">
                        @if(in_array('Update',$permission))
                        <a href="#" class="me-2" data-bs-toggle="modal"
                            data-bs-target="#edit_budget_request" data-id="{{ $budget->id }}"
                            data-project-name="{{ $budget->project_name }}"
                            data-budget-category="{{ $budget->budget_category }}"
                            data-requested-amount="{{ $budget->requested_amount }}"
                            data-start-date="{{ $budget->start_date }}"
                            data-end-date="{{ $budget->end_date }}"
                            data-justification="{{ $budget->justification }}"
                            data-expected-outcome="{{ $budget->expected_outcome }}"
                            data-attachment="{{ $budget->attachment }}"><i
                                class="ti ti-edit"></i></a>
                            @endif
                        @if(in_array('Delete',$permission))
                        <a href="#" data-bs-toggle="modal" class="btn-delete"
                            data-bs-target="#delete_budget_request"
                            data-id="{{ $budget->id }}"
                            data-name="{{ $budget->user->personalInformation->full_name ?? 'N/A' }}"><i
                                class="ti ti-trash"></i></a>
                        @endif
                    </div>
                @endif
            </td>
            @endif
        </tr>
    @endforeach
