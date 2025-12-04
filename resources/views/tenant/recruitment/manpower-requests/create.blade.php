<?php $page = 'recruitment-manpower-requests'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Create Manpower Request</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('recruitment.manpower-requests.index') }}">Manpower Requests</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Create Request</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="me-2 mb-2">
                        <a href="{{ route('recruitment.manpower-requests.index') }}" class="btn btn-outline-dark d-flex align-items-center">
                            <i class="ti ti-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Manpower Request Details</h4>
                            <p class="text-muted mb-0">Submit a request for new positions that need to be filled in your department</p>
                        </div>
                        <form id="manpowerRequestForm">
                            @csrf
                            <div class="card-body">
                                <!-- Basic Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Position Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="position" required placeholder="e.g., Senior Software Developer">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Department <span class="text-danger">*</span></label>
                                            <select class="form-select" name="department_id" required>
                                                <option value="">Select Department</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Designation</label>
                                            <select class="form-select" name="designation_id">
                                                <option value="">Select Designation</option>
                                                @foreach($designations as $designation)
                                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                                            <select class="form-select" name="employment_type" required>
                                                <option value="full-time">Full-time</option>
                                                <option value="part-time">Part-time</option>
                                                <option value="contract">Contract</option>
                                                <option value="internship">Internship</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Number of Vacancies <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="vacancies" value="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                                            <select class="form-select" name="priority" required>
                                                <option value="medium">Medium</option>
                                                <option value="low">Low</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Justification -->
                                <div class="mb-3">
                                    <label class="form-label">Business Justification <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="justification" rows="4" required placeholder="Explain why this position is needed, how it will benefit the department/company, and any business impact..."></textarea>
                                </div>

                                <!-- Job Description -->
                                <div class="mb-3">
                                    <label class="form-label">Job Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="job_description" rows="6" required placeholder="Provide detailed job description including responsibilities, duties, and expectations..."></textarea>
                                </div>

                                <!-- Requirements -->
                                <div class="mb-3">
                                    <label class="form-label">Requirements</label>
                                    <div id="requirementsContainer">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="requirements[]" placeholder="Enter a requirement">
                                            <button type="button" class="btn btn-outline-primary" onclick="addRequirement()">
                                                <i class="ti ti-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Add specific qualifications, experience, or education requirements</small>
                                </div>

                                <!-- Skills -->
                                <div class="mb-3">
                                    <label class="form-label">Required Skills</label>
                                    <div id="skillsContainer">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="skills[]" placeholder="Enter a skill">
                                            <button type="button" class="btn btn-outline-primary" onclick="addSkill()">
                                                <i class="ti ti-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Add technical skills, software knowledge, or soft skills required</small>
                                </div>

                                <!-- Salary Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Proposed Minimum Salary</label>
                                            <input type="number" class="form-control" name="salary_min" placeholder="₱" step="0.01">
                                            <small class="text-muted">Optional - final salary will be determined by COO</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Proposed Maximum Salary</label>
                                            <input type="number" class="form-control" name="salary_max" placeholder="₱" step="0.01">
                                            <small class="text-muted">Optional - final salary will be determined by COO</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div class="mb-3">
                                    <label class="form-label">Target Start Date</label>
                                    <input type="date" class="form-control" name="target_start_date" min="{{ date('Y-m-d') }}">
                                    <small class="text-muted">When do you need this position to be filled?</small>
                                </div>

                                <!-- Actions -->
                                <div class="alert alert-info">
                                    <h6><i class="ti ti-info-circle me-2"></i>Next Steps</h6>
                                    <p class="mb-0">After creating this request, you can submit it for COO review and approval. Once approved, HR can post the position to the careers page.</p>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('recruitment.manpower-requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary" data-action="save">
                                            <i class="ti ti-device-floppy me-2"></i>Save as Draft
                                        </button>
                                        <button type="submit" class="btn btn-success" data-action="submit">
                                            <i class="ti ti-send me-2"></i>Save & Submit for Review
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->

@endsection

@section('script')
<script>
    function addRequirement() {
        const container = document.getElementById('requirementsContainer');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control" name="requirements[]" placeholder="Enter a requirement">
            <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                <i class="ti ti-minus"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function addSkill() {
        const container = document.getElementById('skillsContainer');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control" name="skills[]" placeholder="Enter a skill">
            <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                <i class="ti ti-minus"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function removeField(button) {
        button.closest('.input-group').remove();
    }

    document.getElementById('manpowerRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = e.submitter;
        const action = submitBtn.getAttribute('data-action');
        const originalText = submitBtn.innerHTML;
        
        // Determine submit text based on action
        const submitText = action === 'submit' ? 
            '<i class="ti ti-loader ti-spin me-2"></i>Submitting...' : 
            '<i class="ti ti-loader ti-spin me-2"></i>Saving...';
        
        submitBtn.innerHTML = submitText;
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        
        // Convert arrays to proper format
        const requirements = Array.from(this.querySelectorAll('input[name="requirements[]"]'))
            .map(input => input.value)
            .filter(value => value.trim() !== '');
        
        const skills = Array.from(this.querySelectorAll('input[name="skills[]"]'))
            .map(input => input.value)
            .filter(value => value.trim() !== '');
        
        formData.delete('requirements[]');
        formData.delete('skills[]');
        formData.append('requirements', JSON.stringify(requirements));
        formData.append('skills', JSON.stringify(skills));
        formData.append('submit_for_review', action === 'submit' ? '1' : '0');
        
        fetch('{{ route("recruitment.manpower-requests.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and redirect
                const message = action === 'submit' ? 
                    'Manpower request submitted for COO review successfully!' :
                    'Manpower request saved as draft successfully!';
                
                alert(message);
                window.location.href = '{{ route("recruitment.manpower-requests.index") }}';
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error saving request. Please try again.');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
</script>
@endsection