<?php $page = 'suspension'; ?>
@extends('layout.mainlayout')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">My Suspensions</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Suspension</li>
                            <li class="breadcrumb-item active" aria-current="page">My Suspension Records</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Suspension List -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5 class="d-flex align-items-center mb-0">Suspension List</h5>
                            <select id="suspension-status" class="form-select form-select-sm w-auto">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="awaiting_reply">Awaiting Reply</option>
                                <option value="under_investigation">Under Investigation</option>
                                <option value="for_dam_issuance">For DAM Issuance</option>
                                <option value="suspended">Suspended</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="card-body p-3">
                            <div id="suspension-loading" class="text-center py-4">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div id="suspension-error" class="alert alert-danger d-none" role="alert"></div>

                            <div class="table-responsive d-none" id="suspension-table-wrap">
                                <table class="table table-striped align-middle" id="suspension-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Offense Details</th>
                                            <th>Status</th>
                                            <th>Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Report File</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="suspension-tbody">
                                        <!-- rows injected here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reply Suspension Modal -->
        <div class="modal fade" id="replySuspensionModal" tabindex="-1" aria-labelledby="replySuspensionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="replySuspensionForm" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="replySuspensionModalLabel">Submit Your Reply</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="reply_suspension_id" name="suspension_id">
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

        <!-- View Suspension Info Modal -->
        <div class="modal fade" id="viewSuspensionInfoModal" tabindex="-1" aria-labelledby="viewSuspensionInfoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewSuspensionInfoModalLabel">
                            <i class="ti ti-file-info me-2"></i>Suspension Details
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

                            <!-- Suspension Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Suspension Information</h6>
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
    <script>
        (function () {
            const url = "{{ route('suspension-employee-list') }}";

            function showLoading(show) {
                document.getElementById('suspension-loading').classList.toggle('d-none', !show);
            }

            function showError(message) {
                const el = document.getElementById('suspension-error');
                el.textContent = message;
                el.classList.remove('d-none');
            }

            function hideError() {
                document.getElementById('suspension-error').classList.add('d-none');
            }

            function getStatusColor(status) {
                switch (status) {
                    case 'pending': return 'warning';
                    case 'awaiting_reply': return 'info';
                    case 'suspended': return 'danger';
                    case 'completed': return 'success';
                    default: return 'secondary';
                }
            }

            function renderTable(suspensions) {
                const wrap = document.getElementById('suspension-table-wrap');
                const tbody = document.getElementById('suspension-tbody');
                tbody.innerHTML = '';

                if (!suspensions || suspensions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center">No suspensions found.</td></tr>';
                } else {
                    suspensions.forEach((s, idx) => {
                        const canReply = s.status === 'awaiting_reply' || s.status === 'pending';
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${idx + 1}</td>
                        <td>${s.offense_details ?? ''}</td>
                        <td><span class="badge bg-${getStatusColor(s.status)}">${s.status ?? ''}</span></td>
                        <td>${s.suspension_type ?? ''}</td>
                        <td>${s.start_date ?? ''}</td>
                        <td>${s.end_date ?? ''}</td>
                        <td>${s.information_report_file
                                ? `<a href="/storage/${s.information_report_file}" target="_blank">View</a>`
                                : '—'}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info view-info" data-id="${s.id}" title="View Details">
                                <i class="ti ti-eye"></i>
                            </button>
                            ${canReply
                                ? `<button class="btn btn-sm btn-outline-success reply-suspension ms-1" data-id="${s.id}" title="Submit Reply">
                                       <i class="ti ti-message"></i>
                                   </button>`
                                : ''
                            }
                        </td>`;
                        tbody.appendChild(tr);
                    });
                }
                wrap.classList.remove('d-none');

                document.querySelectorAll('.view-info').forEach(btn => {
                    btn.addEventListener('click', () => openViewInfoModal(btn.dataset.id));
                });

                document.querySelectorAll('.reply-suspension').forEach(btn => {
                    btn.addEventListener('click', () => openReplySuspensionModal(btn.dataset.id));
                });
            }

            async function loadSuspensions() {
                showLoading(true);
                hideError();
                const status = document.getElementById('suspension-status')?.value || '';

                try {
                    const res = await fetch(`${url}?status=${status}`, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });

                    if (!res.ok) throw new Error('Failed to fetch suspensions: ' + res.status);
                    const data = await res.json();

                    if (data.status === 'success' && Array.isArray(data.suspensions)) {
                        renderTable(data.suspensions);
                    } else {
                        throw new Error('Unexpected response from server.');
                    }
                } catch (err) {
                    showError(err.message || 'Error while loading suspensions.');
                } finally {
                    showLoading(false);
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                loadSuspensions();
                document.getElementById('suspension-status')?.addEventListener('change', loadSuspensions);
            });
        })();

        // Reply Suspension Modal
        document.addEventListener('DOMContentLoaded', () => {
            const replyModal = new bootstrap.Modal(document.getElementById('replySuspensionModal'));
            const form = document.getElementById('replySuspensionForm');
            const errorBox = document.getElementById('reply-error');
            const successBox = document.getElementById('reply-success');
            const idField = document.getElementById('reply_suspension_id');

            window.openReplySuspensionModal = function (id) {
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
                const suspensionId = idField.value;

                try {
                    const res = await fetch(`{{ url('/api/suspension') }}/${suspensionId}/receive-reply`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    });

                    const data = await res.json();
                    if (data.status === 'success') {
                        successBox.textContent = data.message || 'Reply submitted successfully.';
                        successBox.classList.remove('d-none');
                        setTimeout(() => {
                            replyModal.hide();
                            location.reload();
                        }, 1500);
                    } else {
                        throw new Error(data.message || 'Submission failed.');
                    }
                } catch (err) {
                    errorBox.textContent = err.message;
                    errorBox.classList.remove('d-none');
                }
            });
        });

        // View Suspension Info Modal
        document.addEventListener('DOMContentLoaded', () => {
            const apiSuspensionBase = "{{ url('/api/suspension') }}";
            const viewModal = new bootstrap.Modal(document.getElementById('viewSuspensionInfoModal'));
            const viewLoading = document.getElementById('view-info-loading');
            const viewError = document.getElementById('view-info-error');
            const viewContent = document.getElementById('view-info-content');

            window.openViewInfoModal = function (suspensionId) {
                viewLoading.classList.remove('d-none');
                viewError.classList.add('d-none');
                viewContent.classList.add('d-none');
                
                if (!suspensionId) {
                    viewError.textContent = 'Invalid suspension id.';
                    viewError.classList.remove('d-none');
                    viewLoading.classList.add('d-none');
                    return;
                }
                
                viewModal.show();
                fetchSuspensionDetails(suspensionId);
            };

            async function fetchSuspensionDetails(suspensionId) {
                try {
                    const res = await fetch(`${apiSuspensionBase}/${suspensionId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        credentials: 'same-origin'
                    });

                    if (!res.ok) {
                        throw new Error(`Failed to fetch suspension details (${res.status})`);
                    }

                    const data = await res.json();
                    
                    if (data.status === 'success' && data.suspension) {
                        displaySuspensionDetails(data.suspension);
                    } else {
                        throw new Error(data.message || 'Failed to load suspension details.');
                    }
                } catch (err) {
                    viewError.textContent = err.message || 'Error loading suspension details.';
                    viewError.classList.remove('d-none');
                    viewLoading.classList.add('d-none');
                }
            }

            function displaySuspensionDetails(suspension) {
                // Update Progress Flow
                updateProgressFlow(suspension.status);

                // Offense Details
                document.getElementById('view-offense-details').textContent = suspension.offense_details || 'No details provided.';

                // Suspension Information
                const statusBadge = document.getElementById('view-status');
                const status = suspension.status || 'N/A';
                statusBadge.textContent = status.replace('_', ' ').toUpperCase();
                statusBadge.className = 'badge bg-' + getStatusColorForView(status);

                document.getElementById('view-type').textContent = suspension.suspension_type ? 
                    suspension.suspension_type.replace('_', ' ').toUpperCase() : 'N/A';
                document.getElementById('view-filed-date').textContent = suspension.created_at || 'N/A';
                document.getElementById('view-start-date').textContent = suspension.suspension_start_date || 'N/A';
                document.getElementById('view-end-date').textContent = suspension.suspension_end_date || 'N/A';
                document.getElementById('view-duration').textContent = suspension.suspension_days ? 
                    `${suspension.suspension_days} day(s)` : 'N/A';

                // Employee Reply (show card only if available)
                const replyCard = document.getElementById('view-reply-card');
                if (suspension.employee_reply) {
                    const reply = suspension.employee_reply;
                    
                    // Show reply text if available
                    const replyTextSection = document.getElementById('view-reply-text-section');
                    if (reply.description && reply.description.trim() !== '') {
                        document.getElementById('view-reply-text').textContent = reply.description;
                        replyTextSection.style.display = 'block';
                    } else {
                        replyTextSection.style.display = 'none';
                    }
                    
                    // Show reply date
                    document.getElementById('view-reply-date').textContent = reply.action_date || 'N/A';
                    
                    // Show file download if available
                    const replyFileDiv = document.getElementById('view-reply-file');
                    if (reply.file_path) {
                        const fileLink = document.getElementById('view-reply-file-link');
                        fileLink.href = `/storage/${reply.file_path}`;
                        replyFileDiv.style.display = 'block';
                    } else {
                        replyFileDiv.style.display = 'none';
                    }
                    
                    replyCard.style.display = 'block';
                } else {
                    replyCard.style.display = 'none';
                }

                // Attachments
                const attachmentsCard = document.getElementById('view-attachments-card');
                const attachmentsList = document.getElementById('view-attachments-list');
                attachmentsList.innerHTML = '';

                const attachments = [];
                if (suspension.information_report_file) {
                    attachments.push({ name: 'Information Report', url: suspension.information_report_file });
                }
                if (suspension.nowe_file) {
                    attachments.push({ name: 'NOWE Document', url: suspension.nowe_file });
                }
                if (suspension.dam_file) {
                    attachments.push({ name: 'DAM Document', url: suspension.dam_file });
                }

                if (attachments.length > 0) {
                    attachments.forEach(att => {
                        const link = document.createElement('a');
                        link.href = `/storage/${att.url}`;
                        link.target = '_blank';
                        link.className = 'btn btn-sm btn-outline-primary me-2 mb-2';
                        link.innerHTML = `<i class="ti ti-download me-1"></i>${att.name}`;
                        attachmentsList.appendChild(link);
                    });
                    attachmentsCard.style.display = 'block';
                } else {
                    attachmentsCard.style.display = 'none';
                }

                viewLoading.classList.add('d-none');
                viewContent.classList.remove('d-none');
            }

            function updateProgressFlow(status) {
                // Reset all steps
                document.querySelectorAll('.timeline-step').forEach(step => {
                    step.classList.remove('active', 'completed');
                });

                const statusFlow = {
                    'pending': ['step-pending'],
                    'awaiting_reply': ['step-pending', 'step-nowe'],
                    'under_investigation': ['step-pending', 'step-nowe', 'step-investigation'],
                    'for_dam_issuance': ['step-pending', 'step-nowe', 'step-investigation'],
                    'suspended': ['step-pending', 'step-nowe', 'step-investigation', 'step-dam', 'step-suspended'],
                    'completed': ['step-pending', 'step-nowe', 'step-investigation', 'step-dam', 'step-suspended', 'step-completed']
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

                completedSteps.forEach((stepId, index) => {
                    const stepEl = document.getElementById(stepId);
                    if (stepEl) {
                        if (stepId === currentStep && status !== 'completed') {
                            stepEl.classList.add('active');
                        } else {
                            stepEl.classList.add('completed');
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
@endsection