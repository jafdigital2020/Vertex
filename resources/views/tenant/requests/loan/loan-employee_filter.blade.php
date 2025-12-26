 @foreach ($loanRequests as $loan)
        <tr>
            <td>
                <div class="d-flex align-items-center file-name-icon">
                    <a href="#" class="avatar avatar-md border avatar-rounded">
                        <img src="{{ asset('storage/' . $loan->user->personalInformation->profile_picture) }}"
                            class="img-fluid" alt="img">
                    </a>
                    <div class="ms-2">
                        <h6 class="fw-medium"><a
                                href="#">{{ $loan->user->personalInformation->last_name }},
                                {{ $loan->user->personalInformation->first_name }}</a></h6>
                        <span
                            class="fs-12 fw-normal ">{{ $loan->user->employmentDetail->department->department_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </td>
            <td class="text-center">
                {{ $loan->created_at ? \Carbon\Carbon::parse($loan->created_at)->format('F j, Y') : 'N/A' }}
            </td>
            <td class="text-center">{{ $loan->loan_type ?? 'N/A' }}</td>
            <td class="text-center">₱{{ number_format($loan->loan_amount, 2) }}</td>
            <td class="text-center">{{ $loan->repayment_period }} months</td>
            <td class="text-center">{{ $loan->purpose ?? 'N/A' }}</td>
            <td class="text-center">
                @if ($loan->attachment)
                    <a href="{{ asset('storage/' . $loan->attachment) }}"
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
                    if ($loan->status == 'approved') {
                        $badgeClass = 'badge-success';
                    } elseif ($loan->status == 'rejected') {
                        $badgeClass = 'badge-warning';
                    }
                @endphp
                <span
                    class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($loan->status) }}
                </span>
            </td>
            <td class="text-center">
                @if ($loan->approver_name ?? false)
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);"
                            class="avatar avatar-md border avatar-rounded">
                            <img src="{{ asset('storage/' . $loan->approver_picture) }}"
                                class="img-fluid" alt="avatar">
                        </a>
                        <div class="ms-2">
                            <h6 class="fw-medium mb-0">
                                {{ $loan->approver_name }}
                            </h6>
                            <span class="fs-12 fw-normal">
                                {{ $loan->approver_dept }}
                            </span>
                        </div>
                    </div>
                @else
                    &mdash;
                @endif
            </td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
            <td class="text-center">
                @if ($loan->status !== 'approved')
                    <div class="action-icon d-inline-flex">
                        @if(in_array('Update',$permission))
                        <a href="#" class="me-2" data-bs-toggle="modal"
                            data-bs-target="#edit_loan_request" data-id="{{ $loan->id }}"
                            data-loan-type="{{ $loan->loan_type }}"
                            data-loan-amount="{{ $loan->loan_amount }}"
                            data-repayment-period="{{ $loan->repayment_period }}"
                            data-purpose="{{ $loan->purpose }}"
                            data-collateral="{{ $loan->collateral }}"
                            data-attachment="{{ $loan->attachment }}"><i
                                class="ti ti-edit"></i></a>
                            @endif
                        @if(in_array('Delete',$permission))
                        <a href="#" data-bs-toggle="modal" class="btn-delete"
                            data-bs-target="#delete_loan_request"
                            data-id="{{ $loan->id }}"
                            data-name="{{ $loan->user->personalInformation->full_name ?? 'N/A' }}"><i
                                class="ti ti-trash"></i></a>
                        @endif
                    </div>
                @endif
            </td>
            @endif
        </tr>
    @endforeach
