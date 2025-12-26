<?php $page = 'contract-templates'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Contract Templates</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('admin-dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employees
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Contract Templates</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_template"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add Template
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Contract Templates List -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h4 class="mb-0">Template List</h4>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="templates_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Template Name</th>
                                    <th>Contract Type</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $template->contract_type }}</span>
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge badge-secondary d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $template->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="javascript:void(0);" class="view-template me-2"
                                                    data-id="{{ $template->id }}"
                                                    data-name="{{ $template->name }}"
                                                    data-type="{{ $template->contract_type }}"
                                                    data-status="{{ $template->is_active }}"
                                                    data-content="{{ $template->content ?? '' }}"
                                                    data-pdf-path="{{ $template->pdf_template_path ?? '' }}"
                                                    data-created="{{ $template->created_at->format('M d, Y') }}"
                                                    title="View">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="edit-template me-2"
                                                    data-id="{{ $template->id }}"
                                                    data-status="{{ $template->is_active }}"
                                                    title="Edit Status">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No templates found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- Add Template Modal -->
    <div class="modal fade" id="add_template" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Contract Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTemplateForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="contract_type" id="contract_type_add" required>
                                <option value="">Select Type</option>
                                <option value="Probationary">Probationary</option>
                                <option value="Regular">Regular</option>
                                <option value="Contractual">Contractual</option>
                                <option value="Project-Based">Project-Based</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Template Source <span class="text-danger">*</span></label>
                            <select class="form-select" name="template_source" id="template_source_add" required>
                                <option value="">Select Source</option>
                                <option value="pdf">PDF Template (Recommended)</option>
                                <option value="text">Text/HTML Content</option>
                            </select>
                        </div>
                        <div class="mb-3" id="pdf_template_section_add" style="display: none;">
                            <label class="form-label">PDF Template File <span class="text-danger">*</span></label>
                            <select class="form-select" name="html_template_path" id="html_template_path_add">
                                <option value="">Select PDF Template</option>
                                <option value="templates/contracts/01 Rev. 04 Probationary Employment 2024.pdf">01 Rev. 04
                                    Probationary Employment 2024.pdf</option>
                                <option value="templates/contracts/02 Rev. 02 Regular Employment 2024.pdf">02 Rev. 02
                                    Regular Employment 2024.pdf</option>
                            </select>
                            <small class="text-muted">
                                The system will automatically fill employee data into the PDF template.
                            </small>
                        </div>
                        <div class="mb-3" id="pdf_fillable_content_section_add" style="display: none;">
                            <label class="form-label">PDF Fillable Content (Optional)</label>
                            <textarea class="form-control" name="html_content" id="html_content_add"
                                rows="15" placeholder="Paste your contract text here with @{{'{{'}}placeholders@{{'}}}'}}..."></textarea>
                            <small class="text-muted">
                                Add editable contract text with placeholders: @{{ '{{party_name}}' }}, @{{ '{{start_date}}'
                                }}, @{{ '{{end_date}}' }},
                                @{{ '{{employee_full_name}}' }}, @{{ '{{employee_id}}' }}, @{{ '{{position}}' }}, @{{
                                '{{department}}' }}, @{{ '{{current_date}}' }}
                            </small>
                        </div>
                        <div class="mb-3" id="content_section_add" style="display: none;">
                            <label class="form-label">Template Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" id="content_add" rows="10"></textarea>
                            <small class="text-muted">
                                Available placeholders: @{{ '{{employee_full_name}}' }}, @{{ '{{date_hired}}' }}, @{{
                                '{{probationary_end_date}}' }},
                                @{{ '{{employee_id}}' }}, @{{ '{{position}}' }}, @{{ '{{department}}' }}, @{{
                                '{{current_date}}' }}
                            </small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Template Modal -->
    <div class="modal fade" id="view_template" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Contract Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Template Name:</label>
                            <p id="view_name"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Contract Type:</label>
                            <p><span id="view_type" class="badge bg-info"></span></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status:</label>
                            <p id="view_status"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Created Date:</label>
                            <p id="view_created"></p>
                        </div>
                    </div>
                    <div class="row mb-3" id="view_pdf_section" style="display:none;">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">PDF Template Path:</label>
                            <p id="view_pdf_path"></p>
                        </div>
                    </div>
                    <div class="row mb-3" id="view_content_section" style="display:none;">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Template Content:</label>
                            <div id="view_content" class="border p-3 bg-light" style="max-height: 300px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Template Status Modal -->
    <div class="modal fade" id="edit_template" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Template Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editTemplateForm">
                    @csrf
                    <input type="hidden" id="edit_template_id" name="template_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Template Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="is_active" id="edit_status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .action-icon {
            display: inline-flex;
            gap: 10px;
        }

        .action-icon a {
            color: #495057;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .action-icon a:hover {
            color: #007bff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            if (!$.fn.DataTable.isDataTable('#templates_table')) {
                $('#templates_table').DataTable({
                    "pageLength": 10,
                    "ordering": true,
                    "searching": true,
                    "lengthChange": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "order": [[3, 'desc']], // Sort by Created Date descending
                    "columnDefs": [
                        { "orderable": false, "targets": 4 } // Disable sorting on Actions column
                    ],
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "infoEmpty": "Showing 0 to 0 of 0 entries",
                        "infoFiltered": "(filtered from _MAX_ total entries)",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    }
                });
            }

            // Toggle template source sections
            $('#template_source_add').on('change', function () {
                const source = $(this).val();
                if (source === 'pdf') {
                    $('#pdf_template_section_add').show();
                    $('#pdf_fillable_content_section_add').show();
                    $('#content_section_add').hide();
                    $('#html_template_path_add').prop('required', true);
                    $('#content_add').prop('required', false);
                } else if (source === 'text') {
                    $('#pdf_template_section_add').hide();
                    $('#pdf_fillable_content_section_add').hide();
                    $('#content_section_add').show();
                    $('#html_template_path_add').prop('required', false);
                    $('#content_add').prop('required', true);
                } else {
                    $('#pdf_template_section_add').hide();
                    $('#pdf_fillable_content_section_add').hide();
                    $('#content_section_add').hide();
                    $('#html_template_path_add').prop('required', false);
                    $('#content_add').prop('required', false);
                }
            });

            // Auto-select PDF template based on contract type
            $('#contract_type_add').on('change', function () {
                const contractType = $(this).val();
                const templateSource = $('#template_source_add').val();

                if (templateSource === 'pdf') {
                    if (contractType === 'Probationary') {
                        $('#html_template_path_add').val('templates/contracts/01 Rev. 04 Probationary Employment 2024.pdf');
                    } else if (contractType === 'Regular') {
                        $('#html_template_path_add').val('templates/contracts/02 Rev. 02 Regular Employment 2024.pdf');
                    }
                }
            });

            // Add Template
            $('#addTemplateForm').on('submit', function (e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: '{{ route("contract-templates.store") }}',
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#add_template').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to create template';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join('<br>');
                            }
                        }

                        toastr.error(errorMessage);
                    }
                });
            });

            // View Template
            $(document).on('click', '.view-template', function () {
                const name = $(this).data('name');
                const type = $(this).data('type');
                const status = $(this).data('status');
                const content = $(this).data('content');
                const pdfPath = $(this).data('pdf-path');
                const created = $(this).data('created');

                $('#view_name').text(name);
                $('#view_type').text(type);
                $('#view_created').text(created);
                
                if (status == 1) {
                    $('#view_status').html('<span class="badge badge-success">Active</span>');
                } else {
                    $('#view_status').html('<span class="badge badge-secondary">Inactive</span>');
                }

                if (pdfPath) {
                    $('#view_pdf_section').show();
                    $('#view_pdf_path').text(pdfPath);
                } else {
                    $('#view_pdf_section').hide();
                }

                if (content) {
                    $('#view_content_section').show();
                    $('#view_content').html(content.replace(/\n/g, '<br>'));
                } else {
                    $('#view_content_section').hide();
                }

                $('#view_template').modal('show');
            });

            // Edit Template Status
            $(document).on('click', '.edit-template', function () {
                const id = $(this).data('id');
                const status = $(this).data('status');

                $('#edit_template_id').val(id);
                $('#edit_status').val(status);
                $('#edit_template').modal('show');
            });

            // Submit Edit Template Form
            $('#editTemplateForm').on('submit', function (e) {
                e.preventDefault();

                const id = $('#edit_template_id').val();
                const status = $('#edit_status').val();

                $.ajax({
                    url: '{{ url("contract-templates") }}/' + id + '/toggle-status',
                    method: 'POST',
                    data: { 
                        _token: '{{ csrf_token() }}',
                        is_active: status
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#edit_template').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function (xhr) {
                        toastr.error('Failed to update template status');
                    }
                });
            });
        });
    </script>
@endpush
