<?php $page = 'recruitment-job-postings'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Create Job Posting</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('recruitment.job-postings.index') }}">Job Postings</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="me-2 mb-2">
                        <a href="{{ route('recruitment.job-postings.index') }}" class="btn btn-outline-dark d-flex align-items-center">
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
                            <h4 class="card-title">Job Posting Details</h4>
                        </div>
                        <form id="jobPostingForm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <!-- Basic Information -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Job Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" required>
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
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location" placeholder="Office location">
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
                                </div>

                                <!-- Salary Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Salary</label>
                                            <input type="number" class="form-control" name="salary_min" placeholder="₱" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Maximum Salary</label>
                                            <input type="number" class="form-control" name="salary_max" placeholder="₱" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Expiration Date</label>
                                            <input type="date" class="form-control" name="expiration_date" min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Assigned Recruiter</label>
                                            <select class="form-select" name="assigned_recruiter">
                                                <option value="">Select Recruiter</option>
                                                @foreach($recruiters as $recruiter)
                                                    <option value="{{ $recruiter->id }}">{{ $recruiter->username }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Job Description -->
                                <div class="mb-3">
                                    <label class="form-label">Job Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="6" required placeholder="Provide a detailed description of the job role, responsibilities, and expectations..."></textarea>
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

                                <!-- Publishing Options -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="publish_now" id="publishNow">
                                        <label class="form-check-label" for="publishNow">
                                            Publish immediately (job will be visible on career page)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('recruitment.job-postings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-2"></i>Create Job Posting
                                    </button>
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

    document.getElementById('jobPostingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
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
        
        submitBtn.innerHTML = '<i class="ti ti-loader ti-spin me-2"></i>Creating...';
        submitBtn.disabled = true;
        
        fetch('{{ route("recruitment.job-postings.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("recruitment.job-postings.index") }}';
            } else {
                alert('Error creating job posting: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error creating job posting. Please try again.');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
</script>
@endsection