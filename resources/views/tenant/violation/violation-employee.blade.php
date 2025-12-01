<?php $page = 'violation'; ?>
@extends('layout.mainlayout')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Violation</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Violation</li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Violation</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Violation List -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                         <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5 class="mb-0">Violation List</h5>
                              <div class="d-flex align-items-center flex-wrap row-gap-2"> 
                               <div class="form-group me-2" style="max-width:200px;">
                                <select id="violation-status" class="form-select select2" style="max-width:200px;" oninput="filter()">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="awaiting_reply">Awaiting Reply</option>
                                    <option value="under_investigation">Under Investigation</option>
                                    <option value="for_dam_issuance">For DAM Issuance</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="completed">Completed</option>
                                </select>
                                </div>
                            </div>
                        </div> 
                        <div class="card-body p-3"> 
                            <div id="violation-error" class="alert alert-danger d-none" role="alert"></div> 
                            <div class="table-responsive" id="violation-table-wrap">
                                <table class="table table-striped align-middle datatable" id="violation-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Offense Details</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center">Start Date</th>
                                            <th class="text-center">End Date</th>
                                            <th class="text-center">Report File</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="violation-tbody"> 
                                        @foreach ($violations as $index => $s)
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
                                                <td class="text-center">{{ $s->violation_type ? strtoupper(str_replace('_', ' ', $s->violation_type)) : '' }}</td>
                                                <td class="text-center">{{ $s->violation_start_date ?? '' }}</td>
                                                <td class="text-center">{{ $s->violation_end_date ?? '' }}</td>
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
                                                    <button class="btn btn-sm btn-info view-violation" data-id="{{ $s->id }}" title="View Details">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    @if( $s->status === 'awaiting_reply')
                                                        <button class="btn btn-sm btn-success ms-1" onclick="openReplyViolationModal({{ $s->id }})" title="Submit Reply">
                                                            <i class="ti ti-message"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr> 
                                            @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reply Violation Modal -->
        <div class="modal fade" id="replyViolationModal" tabindex="-1" aria-labelledby="replyViolationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="replyViolationForm" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="replyViolationModalLabel">Submit Your Reply</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="reply_violation_id" name="violation_id">
                            <div class="mb-3">
                                <label for="reply_file" class="form-label">Upload Reply File (PDF/DOC/DOCX)</label>
                                <input type="file" name="reply_file" id="reply_file" class="form-control"
                                    accept=".pdf,.doc,.docx" required>
                            </div>
                            <div id="reply-error" class="alert alert-danger d-none"></div>
                            <div id="reply-success" class="alert alert-success d-none"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Reply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Violation Info Modal -->
        <div class="modal fade" id="viewViolationInfoModal" tabindex="-1" aria-labelledby="viewViolationInfoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewViolationInfoModalLabel">
                            <i class="ti ti-file-info me-2"></i>Violation Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div id="view-info-loading" class="text-center py-4">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        <div id="view-info-error" class="alert alert-danger d-none"></div>

                        <div id="view-info-content" class="d-none">
                            <!-- Progress Flow -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ti ti-timeline me-2"></i>Case Progress</h6>
                                </div>
                                <div class="card-body">
                                    <div class="timeline-progress">
                                        <div class="timeline-step" id="step-pending">
                                            <div class="timeline-icon">
                                                <i class="ti ti-file-check"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-0">Report Received</h6>
                                                <small class="text-muted">Initial filing</small>
                                            </div>
                                        </div>
                                        <div class="timeline-step" id="step-nowe">
                                            <div class="timeline-icon">
                                                <i class="ti ti-mail"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-0">NOWE Issued</h6>
                                                <small class="text-muted">Notice of written explanation</small>
                                            </div>
                                        </div>
                                        <div class="timeline-step" id="step-investigation">
                                            <div class="timeline-icon">
                                                <i class="ti ti-search"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-0">Investigation</h6>
                                                <small class="text-muted">Under review</small>
                                            </div>
                                        </div>
                                        <div class="timeline-step" id="step-dam">
                                            <div class="timeline-icon">
                                                <i class="ti ti-file-alert"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-0">DAM Issued</h6>
                                                <small class="text-muted">Decision & administrative memo</small>
                                            </div>
                                        </div>
                                        <div class="timeline-step" id="step-suspended">
                                            <div class="timeline-icon">
                                                <i class="ti ti-clock-pause"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-0">Suspended</h6>
                                                <small class="text-muted">Implementation period</small>
                                            </div>
                                        </div>
                                        <div class="timeline-step" id="step-completed">
                                            <div class="timeline-icon">
                                                <i class="ti ti-circle-check"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-0">Return to Work</h6>
                                                <small class="text-muted">Case completed</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Offense Details -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ti ti-file-description me-2"></i>Offense Details</h6>
                                </div>
                                <div class="card-body">
                                    <p id="view-offense-details" class="mb-0"></p>
                                </div>
                            </div>

                            <!-- Violation Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Violation Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Status:</strong> <span id="view-status" class="badge"></span></p>
                                            <p class="mb-2"><strong>Type:</strong> <span id="view-type"></span></p>
                                            <p class="mb-2"><strong>Filed Date:</strong> <span id="view-filed-date"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Start Date:</strong> <span id="view-start-date"></span></p>
                                            <p class="mb-2"><strong>End Date:</strong> <span id="view-end-date"></span></p>
                                            <p class="mb-2"><strong>Duration:</strong> <span id="view-duration"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Your Reply -->
                            <div class="card mb-3" id="view-reply-card" style="display: none;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ti ti-message-reply me-2"></i>Your Reply</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3" id="view-reply-text-section" style="display: none;">
                                        <p class="mb-0"><strong>Message:</strong></p>
                                        <p id="view-reply-text" class="mb-0 mt-2 p-3 bg-light rounded"></p>
                                    </div>
                                    <div id="view-reply-file">
                                        <p class="mb-2"><strong>Reply Document:</strong></p>
                                        <a id="view-reply-file-link" href="#" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="ti ti-download me-1"></i>Download Reply Document
                                        </a>
                                    </div>
                                    <div class="text-muted small mt-3 pt-2 border-top">
                                        <i class="ti ti-calendar me-1"></i><strong>Submitted Date:</strong> <span id="view-reply-date"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Attachments -->
                            <div class="card" id="view-attachments-card" style="display: none;">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ti ti-paperclip me-2"></i>Attachments</h6>
                                </div>
                                <div class="card-body">
                                    <div id="view-attachments-list"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Timeline Progress CSS -->
    <style>
        .timeline-progress {
            display: flex;
            flex-direction: column;
            gap: 0;
            position: relative;
        }

        .timeline-step {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px 0;
            position: relative;
            opacity: 0.4;
            transition: opacity 0.3s;
        }

        .timeline-step.active {
            opacity: 1;
        }

        .timeline-step.completed {
            opacity: 1;
        }

        .timeline-step::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 50px;
            width: 2px;
            height: calc(100% - 10px);
            background: #e0e0e0;
        }

        .timeline-step:last-child::before {
            display: none;
        }

        .timeline-step.completed::before {
            background: #28a745;
        }

        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid #e0e0e0;
            position: relative;
            z-index: 1;
        }

        .timeline-step.active .timeline-icon {
            background: #007bff;
            border-color: #007bff;
            color: white;
            animation: pulse 2s infinite;
        }

        .timeline-step.completed .timeline-icon {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .timeline-content {
            flex: 1;
            padding-top: 5px;
        }

        .timeline-content h6 {
            font-weight: 600;
            font-size: 14px;
        }

        .timeline-content small {
            font-size: 12px;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
            }
        }
    </style>

    <!-- Scripts -->

    @push('scripts')
    <script> 
    function filter() {  
        const status = $('#violation-status').val(); 
        $.ajax({
                url: '{{ route('violation-employee-filter') }}',
                type: 'GET',
                data: { 
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#violation-table').DataTable().destroy();
                        $('#violation-tbody').html(response.html);
                        $('#violation-table').DataTable();
                        
                    } else {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    let message = 'An unexpected error occurred.';
                    if (xhr.status === 403) {
                        message = 'You are not authorized to perform this action.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                }
            });
        }
</script>

    <script> 
        const url = "{{ route('violation-employee-list') }}"; 
        // Reply Violation Modal
        document.addEventListener('DOMContentLoaded', () => {
            const replyModal = new bootstrap.Modal(document.getElementById('replyViolationModal'));
            const form = document.getElementById('replyViolationForm');
            const errorBox = document.getElementById('reply-error');
            const successBox = document.getElementById('reply-success');
            const idField = document.getElementById('reply_violation_id');

            window.openReplyViolationModal = function (id) {
                idField.value = id;
                errorBox.classList.add('d-none');
                successBox.classList.add('d-none');
                form.reset();
                replyModal.show();
            };

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                errorBox.classList.add('d-none');
                successBox.classList.add('d-none');

                const formData = new FormData(form);
                const violationId = idField.value;

                try {
                    const res = await fetch(`{{ url('/api/violation') }}/${violationId}/receive-reply`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    });

                    const data = await res.json();
                    if (data.status === 'success') {
                        toastr.success('Reply submitted successfully','Success'); 
                        replyModal.hide(); 
                        filter();
                    } else {
                        throw new Error(data.message || 'Submission failed.');
                    }
                } catch (err) { 
                    toastr.error( err.message || 'Failed to load violation details.'); 
                }
            });
        });

        // View Violation Info Modal
        $(document).ready(function () {

        const apiViolationBase = "{{ url('/api/violation') }}";
        const viewModal = $('#viewViolationInfoModal'); 
        const $viewLoading = $('#view-info-loading');
        const $viewContent = $('#view-info-content');
   
        $(document).on('click', '.view-violation', function (e) { 
            e.preventDefault(); 
            const $btn = $(this);
            const violationId = $btn.data('id');  
            fetchViolationDetails(violationId);
            viewModal.modal('show');
                
        }); 



        function fetchViolationDetails(violationId) {
            $.ajax({
                url: `${apiViolationBase}/${violationId}`,
                method: 'GET',
                headers: { 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (data) {
                    if (data.status === 'success' && data.violation) {
                        displayViolationDetails(data.violation);
                    } else {
                        toastr.error(data.message || 'Failed to load violation details.');
                        $viewLoading.addClass('d-none');
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || `Error fetching violation details (${xhr.status}).`);
                    $viewLoading.addClass('d-none');
                }
            });
        }

        function displayViolationDetails(violation) {
            updateProgressFlow(violation.status);
 
            $('#view-offense-details').text(violation.offense_details || 'No details provided.');
 
            const status = violation.status || 'N/A';
            $('#view-status').text(status.replace('_', ' ').toUpperCase())
                .attr('class', 'badge bg-' + getStatusColorForView(status));

            $('#view-type').text(violation.violation_type ? violation.violation_type.replace('_',' ').toUpperCase() : 'N/A');
            $('#view-filed-date').text(violation.created_at || 'N/A');
            $('#view-start-date').text(violation.violation_start_date || 'N/A');
            $('#view-end-date').text(violation.violation_end_date || 'N/A');
            $('#view-duration').text(violation.violation_days ? violation.violation_days + ' day(s)' : 'N/A');
 
            const $replyCard = $('#view-reply-card');
            if (violation.employee_reply) {
                const reply = violation.employee_reply;

                if (reply.description && reply.description.trim() !== '') {
                    $('#view-reply-text').text(reply.description);
                    $('#view-reply-text-section').show();
                } else {
                    $('#view-reply-text-section').hide();
                }

                $('#view-reply-date').text(reply.action_date || 'N/A');

                if (reply.file_path) {
                    $('#view-reply-file-link').attr('href', '/storage/' + reply.file_path);
                    $('#view-reply-file').show();
                } else {
                    $('#view-reply-file').hide();
                }

                $replyCard.show();
            } else {
                $replyCard.hide();
            }

            // Attachments
            const $attachmentsCard = $('#view-attachments-card');
            const $attachmentsList = $('#view-attachments-list').empty();

            const attachments = [];
            if (violation.information_report_file) attachments.push({ name: 'Information Report', url: violation.information_report_file });
            if (violation.nowe_file) attachments.push({ name: 'NOWE Document', url: violation.nowe_file });
            if (violation.dam_file) attachments.push({ name: 'DAM Document', url: violation.dam_file });

            if (attachments.length > 0) {
                attachments.forEach(att => {
                    const link = $('<a>')
                        .attr({ href: '/storage/' + att.url, target: '_blank' })
                        .addClass('btn btn-sm btn-outline-primary me-2 mb-2')
                        .html(`<i class="ti ti-download me-1"></i>${att.name}`);
                    $attachmentsList.append(link);
                });
                $attachmentsCard.show();
            } else {
                $attachmentsCard.hide();
            }

            $viewLoading.addClass('d-none');
            $viewContent.removeClass('d-none');
        }

        function updateProgressFlow(status) {
            $('.timeline-step').removeClass('active completed');

            const statusFlow = {
                'pending': ['step-pending'],
                'awaiting_reply': ['step-pending','step-nowe'],
                'under_investigation': ['step-pending','step-nowe','step-investigation'],
                'for_dam_issuance': ['step-pending','step-nowe','step-investigation'],
                'suspended': ['step-pending','step-nowe','step-investigation','step-dam','step-suspended'],
                'completed': ['step-pending','step-nowe','step-investigation','step-dam','step-suspended','step-completed']
            };

            const currentStepMap = {
                'pending': 'step-pending',
                'awaiting_reply': 'step-nowe',
                'under_investigation': 'step-investigation',
                'for_dam_issuance': 'step-dam',
                'suspended': 'step-suspended',
                'completed': 'step-completed'
            };

            const completedSteps = statusFlow[status] || [];
            const currentStep = currentStepMap[status];

            completedSteps.forEach(stepId => {
                const $stepEl = $('#' + stepId);
                if ($stepEl.length) {
                    if (stepId === currentStep && status !== 'completed') {
                        $stepEl.addClass('active');
                    } else {
                        $stepEl.addClass('completed');
                    }
                }
            });
        }

        function getStatusColorForView(status) {
            switch (status) {
                case 'pending': return 'warning';
                case 'awaiting_reply': return 'info';
                case 'under_investigation': return 'primary';
                case 'for_dam_issuance': return 'secondary';
                case 'suspended': return 'danger';
                case 'completed': return 'success';
                default: return 'secondary';
            }
        }
    });

    </script>
    @endpush
@endsection