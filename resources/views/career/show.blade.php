<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $job->title }} - Career Opportunities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .job-detail-card {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
        }
        .apply-section {
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        .related-jobs .card {
            transition: transform 0.2s;
        }
        .related-jobs .card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('career.index') }}">
                <i class="fas fa-arrow-left"></i> Back to Jobs
            </a>
        </div>
    </nav>

    <!-- Job Header -->
    <div class="job-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">{{ $job->title }}</h1>
                    <div class="mb-4">
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-building"></i> {{ $job->department->department_name }}
                        </span>
                        @if($job->location)
                            <span class="badge bg-light text-dark me-2">
                                <i class="fas fa-map-marker-alt"></i> {{ $job->location }}
                            </span>
                        @endif
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-briefcase"></i> {{ ucwords(str_replace('-', ' ', $job->employment_type)) }}
                        </span>
                        @if($job->designation)
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-tag"></i> {{ $job->designation->designation_name }}
                            </span>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-light">Posted</small>
                            <div class="fw-bold">{{ $job->posted_date ? $job->posted_date->format('M d, Y') : 'Recently' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-light">Positions</small>
                            <div class="fw-bold">{{ $job->vacancies }} Available</div>
                        </div>
                        @if($job->days_to_expiry !== null)
                            <div class="col-md-4">
                                <small class="text-light">Application Deadline</small>
                                <div class="fw-bold">
                                    @if($job->days_to_expiry > 0)
                                        {{ $job->days_to_expiry }} days left
                                    @elseif($job->days_to_expiry == 0)
                                        Today
                                    @else
                                        Expired
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> {{ session('success') }}
                @if(session('application_code'))
                    <br><small>Your application reference: <strong>{{ session('application_code') }}</strong></small>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <div class="row">
            <!-- Job Details -->
            <div class="col-lg-8">
                <div class="card job-detail-card">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Job Description</h4>
                        <div class="mb-4">
                            {!! nl2br(e($job->description)) !!}
                        </div>

                        @if($job->requirements)
                            <h5 class="mb-3">Requirements</h5>
                            <ul class="list-unstyled">
                                @foreach($job->requirements as $requirement)
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        {{ $requirement }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if($job->skills)
                            <h5 class="mb-3 mt-4">Required Skills</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($job->skills as $skill)
                                    <span class="badge bg-primary">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($job->salary_min || $job->salary_max)
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="mb-2">Salary Range</h6>
                                <div class="text-success fw-bold fs-5">
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
                    </div>
                </div>
            </div>

            <!-- Apply Section -->
            <div class="col-lg-4">
                <div class="card job-detail-card sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Ready to Apply?</h5>
                        <p class="text-muted mb-4">Join our team and start your career journey with us.</p>
                        
                        @if($job->is_expired)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                This job posting has expired.
                            </div>
                        @else
                            @auth('candidate')
                                <a href="{{ route('career.apply', $job->id) }}" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-paper-plane"></i> Apply Now
                                </a>
                            @else
                                <button class="btn btn-primary w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <i class="fas fa-paper-plane"></i> Apply Now
                                </button>
                            @endauth
                        @endif

                        <hr>
                        <div class="text-center">
                            <small class="text-muted">Have questions about this role?</small><br>
                            <a href="mailto:hr@company.com" class="btn btn-outline-secondary btn-sm mt-2">
                                <i class="fas fa-envelope"></i> Contact HR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Jobs -->
        @if($relatedJobs->count() > 0)
            <div class="mt-5">
                <h4 class="mb-4">Related Opportunities</h4>
                <div class="row related-jobs">
                    @foreach($relatedJobs as $relatedJob)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $relatedJob->title }}</h6>
                                    <p class="card-text text-muted small">
                                        {{ Str::limit(strip_tags($relatedJob->description), 80) }}
                                    </p>
                                    <a href="{{ route('career.show', $relatedJob->id) }}" class="btn btn-outline-primary btn-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Login Modal -->
    @guest('candidate')
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="loginModalLabel">
                            <i class="fas fa-sign-in-alt me-2"></i>Account Required
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                            <h5>Sign in to apply for this position</h5>
                            <p class="text-muted">You need to create an account or sign in to submit your job application.</p>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('career.register') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </a>
                            <a href="{{ route('career.login') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <small class="text-muted w-100 text-center">
                            Registration is quick and free. Start your application journey today!
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>