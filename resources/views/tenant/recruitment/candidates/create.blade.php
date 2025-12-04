<?php $page = 'recruitment-candidates'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Add Candidate</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('recruitment.candidates.index') }}">Candidates</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Add Candidate</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="me-2 mb-2">
                        <a href="{{ route('recruitment.candidates.index') }}" class="btn btn-outline-dark d-flex align-items-center">
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
                            <h4 class="card-title">Candidate Information</h4>
                        </div>
                        <form id="candidateForm" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="first_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="last_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" name="phone" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Details -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" name="date_of_birth">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select" name="gender">
                                                <option value="">Select Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Years of Experience</label>
                                            <input type="number" class="form-control" name="years_of_experience" min="0" step="0.5">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Current Salary</label>
                                            <input type="number" class="form-control" name="current_salary" placeholder="₱" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="3" placeholder="Complete address"></textarea>
                                </div>

                                <!-- Professional Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">LinkedIn Profile</label>
                                            <input type="url" class="form-control" name="linkedin_url" placeholder="https://linkedin.com/in/...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Expected Salary</label>
                                            <input type="number" class="form-control" name="expected_salary" placeholder="₱" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- Skills -->
                                <div class="mb-3">
                                    <label class="form-label">Skills</label>
                                    <div id="skillsContainer">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="skills[]" placeholder="Enter a skill">
                                            <button type="button" class="btn btn-outline-primary" onclick="addSkill()">
                                                <i class="ti ti-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Add technical skills, software knowledge, or competencies</small>
                                </div>

                                <!-- File Uploads -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Resume/CV <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                                            <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max: 2MB)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Photo</label>
                                            <input type="file" class="form-control" name="photo" accept="image/*">
                                            <small class="text-muted">Optional profile photo</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="4" placeholder="Any additional notes about the candidate..."></textarea>
                                </div>

                                <!-- Source -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Source</label>
                                            <select class="form-select" name="source">
                                                <option value="career_page">Career Page</option>
                                                <option value="referral">Employee Referral</option>
                                                <option value="job_board">Job Board</option>
                                                <option value="linkedin">LinkedIn</option>
                                                <option value="agency">Recruitment Agency</option>
                                                <option value="direct_application">Direct Application</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="new">New</option>
                                                <option value="screening">Screening</option>
                                                <option value="interview">Interview</option>
                                                <option value="evaluation">Evaluation</option>
                                                <option value="offer">Offer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('recruitment.candidates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-2"></i>Save Candidate
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

    document.getElementById('candidateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Convert skills array to proper format
        const skills = Array.from(this.querySelectorAll('input[name="skills[]"]'))
            .map(input => input.value)
            .filter(value => value.trim() !== '');
        
        formData.delete('skills[]');
        formData.append('skills', JSON.stringify(skills));
        
        submitBtn.innerHTML = '<i class="ti ti-loader ti-spin me-2"></i>Saving...';
        submitBtn.disabled = true;
        
        fetch('{{ route("recruitment.candidates.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("recruitment.candidates.index") }}';
            } else {
                alert('Error creating candidate: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error creating candidate. Please try again.');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
</script>
@endsection