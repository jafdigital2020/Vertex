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

            <div class="row">
                <!-- Contract Templates List -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Template List</h4>
                        </div>
                        <div class="card-body">
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
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $template->created_at->format('M d, Y') }}</td>
                                                <td class="text-center">
                                                    <div class="dropdown table-action">
                                                        <a href="#" class="action-icon dropdown-toggle"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ti ti-dots-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a href="{{ route('contract-templates.show', $template->id) }}"
                                                                    class="dropdown-item rounded-1">
                                                                    <i class="ti ti-eye me-2"></i>View
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="{{ route('contract-templates.edit', $template->id) }}"
                                                                    class="dropdown-item rounded-1">
                                                                    <i class="ti ti-edit me-2"></i>Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item rounded-1 delete-template"
                                                                    data-id="{{ $template->id }}">
                                                                    <i class="ti ti-trash me-2"></i>Delete
                                                                </a>
                                                            </li>
                                                        </ul>
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
                                rows="15" placeholder="Paste your contract text here with {{placeholders}}..."></textarea>
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
@endsection

@push('styles')
    <style>
        .table-action .dropdown-menu {
            z-index: 1050 !important;
        }

        .action-icon {
            color: #495057;
            font-size: 18px;
            cursor: pointer;
        }

        .action-icon:hover {
            color: #007bff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable with proper settings
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
                    ]
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
                console.log('Submitting form data:', formData);

                $.ajax({
                    url: '{{ route("contract-templates.store") }}',
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        console.log('Success response:', response);
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#add_template').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function (xhr) {
                        console.error('Error response:', xhr);
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

            // Delete Template
            $('.delete-template').on('click', function () {
                const id = $(this).data('id');

                if (confirm('Are you sure you want to delete this template?')) {
                    $.ajax({
                        url: '/contract-templates/' + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function (xhr) {
                            toastr.error('Failed to delete template');
                        }
                    });
                }
            });
        });
    </script>
@endpush