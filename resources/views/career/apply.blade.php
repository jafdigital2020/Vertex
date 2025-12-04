<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for {{ $job->title }} - Career Opportunities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }
        .application-card {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
        }
        .job-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h5 {
            color: #495057;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('career.show', $job->id) }}">
                <i class="fas fa-arrow-left"></i> Back to Job Details
            </a>
            <a class="navbar-brand" href="{{ route('career.index') }}">
                <i class="fas fa-list"></i> All Jobs
            </a>
        </div>
    </nav>

    <!-- Job Header -->
    <div class="job-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="display-6 fw-bold mb-3">Apply for {{ $job->title }}</h2>
                    <div class="mb-3">
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-building"></i> {{ $job->department->department_name }}
                        </span>
                        @if($job->location)
                            <span class="badge bg-light text-dark me-2">
                                <i class="fas fa-map-marker-alt"></i> {{ $job->location }}
                            </span>
                        @endif
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-briefcase"></i> {{ ucwords(str_replace('-', ' ', $job->employment_type)) }}
                        </span>
                    </div>
                    <p class="lead">Join our team and start your career journey with us.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <!-- Application Form -->
            <div class="col-lg-8">
                <div class="card application-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Application Form</h4>
                    </div>
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('career.apply.submit', $job->id) }}" method="POST" enctype="multipart/form-data" id="applicationForm">
                            @csrf
                            
                            <!-- Personal Information -->
                            <div class="form-section">
                                <h5><i class="fas fa-user me-2"></i>Personal Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" value="{{ $candidate->first_name }}" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" value="{{ $candidate->last_name }}" disabled>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" value="{{ $candidate->email }}" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $candidate->phone) }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Complete address">{{ old('address', $candidate->address) }}</textarea>
                                </div>
                            </div>

                            <!-- Professional Information -->
                            <div class="form-section">
                                <h5><i class="fas fa-briefcase me-2"></i>Professional Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">LinkedIn Profile</label>
                                        <input type="url" name="linkedin_profile" class="form-control" placeholder="https://linkedin.com/in/..." value="{{ old('linkedin_profile', $candidate->linkedin_profile) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Expected Salary</label>
                                        <input type="number" name="expected_salary" class="form-control" placeholder="₱" step="0.01" value="{{ old('expected_salary', $candidate->expected_salary) }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Available Start Date</label>
                                    <input type="date" name="available_start_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('available_start_date') }}">
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="form-section">
                                <h5><i class="fas fa-file-upload me-2"></i>Documents</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Resume/CV <span class="text-danger">*</span></label>
                                        <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                                        <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max: 5MB)</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Profile Photo</label>
                                        <input type="file" name="photo" class="form-control" accept="image/*">
                                        <small class="text-muted">Optional profile photo</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Cover Letter -->
                            <div class="form-section">
                                <h5><i class="fas fa-edit me-2"></i>Cover Letter</h5>
                                <div class="mb-3">
                                    <label class="form-label">Why are you interested in this position? <span class="text-danger">*</span></label>
                                    <textarea name="cover_letter" class="form-control" rows="6" required placeholder="Tell us why you're perfect for this role, your relevant experience, and what you can bring to our team...">{{ old('cover_letter') }}</textarea>
                                </div>
                            </div>

                            <!-- Skills -->
                            <div class="form-section">
                                <h5><i class="fas fa-tools me-2"></i>Skills & Experience</h5>
                                <div class="mb-3">
                                    <label class="form-label">Key Skills</label>
                                    <textarea name="skills" class="form-control" rows="3" placeholder="List your relevant skills, technologies, or competencies separated by commas">{{ old('skills', is_array($candidate->skills) ? implode(', ', $candidate->skills) : $candidate->skills) }}</textarea>
                                    <small class="text-muted">Example: PHP, Laravel, JavaScript, Project Management, etc.</small>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div class="form-section">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="termsCheck" required>
                                    <label class="form-check-label" for="termsCheck">
                                        I agree that the information provided is accurate and I consent to the processing of my personal data for recruitment purposes.
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('career.show', $job->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Job Details
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Job Summary Sidebar -->
            <div class="col-lg-4">
                <!-- Job Summary -->
                <div class="card application-card sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Job Summary</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">{{ $job->title }}</h6>
                        <p class="text-muted mb-3">{{ $job->department->department_name }}</p>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Location</small>
                            <div>{{ $job->location ?: 'Not specified' }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Employment Type</small>
                            <div>{{ ucwords(str_replace('-', ' ', $job->employment_type)) }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Positions Available</small>
                            <div>{{ $job->vacancies }} position(s)</div>
                        </div>

                        @if($job->salary_min || $job->salary_max)
                            <div class="mb-3">
                                <small class="text-muted d-block">Salary Range</small>
                                <div class="text-success fw-bold">
                                    @if($job->salary_min && $job->salary_max)
                                        ₱{{ number_format($job->salary_min) }} - ₱{{ number_format($job->salary_max) }}
                                    @elseif($job->salary_min)
                                        From ₱{{ number_format($job->salary_min) }}
                                    @else
                                        Up to ₱{{ number_format($job->salary_max) }}
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($job->expiration_date)
                            <div class="mb-3">
                                <small class="text-muted d-block">Application Deadline</small>
                                <div>{{ $job->expiration_date->format('M d, Y') }}</div>
                                @if($job->days_to_expiry <= 7 && $job->days_to_expiry > 0)
                                    <small class="text-warning">{{ $job->days_to_expiry }} days left</small>
                                @endif
                            </div>
                        @endif

                        <hr>
                        <div class="text-center">
                            <small class="text-muted">Have questions?</small><br>
                            <a href="mailto:hr@company.com" class="btn btn-outline-secondary btn-sm mt-2">
                                <i class="fas fa-envelope"></i> Contact HR
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Related Jobs -->
                @if($relatedJobs->count() > 0)
                    <div class="card application-card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Other Open Positions</h6>
                        </div>
                        <div class="card-body">
                            @foreach($relatedJobs as $relatedJob)
                                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <h6 class="mb-1">
                                        <a href="{{ route('career.show', $relatedJob->id) }}" class="text-decoration-none">
                                            {{ $relatedJob->title }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">{{ $relatedJob->employment_type }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Let the form submit normally - no need to prevent default
            // The form will be processed by the server and redirect back with success/error messages
        });
    </script>
</body>
</html>