<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Opportunities - Join Our Team</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 30px 0;
        }
        .job-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('career.index') }}">
                <i class="fas fa-briefcase me-2"></i>Careers
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link me-2" href="{{ route('career.login') }}">
                    <i class="fas fa-sign-in-alt me-1"></i>Sign In
                </a>
                <a class="nav-link btn btn-primary text-white px-3" href="{{ route('career.register') }}">
                    <i class="fas fa-user-plus me-1"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Join Our Amazing Team</h1>
                    <p class="lead mb-4">Discover exciting career opportunities and be part of our growing organization</p>
                    <div class="row">
                        <div class="col-md-4">
                            <h3>{{ $jobs->total() }}</h3>
                            <p>Open Positions</p>
                        </div>
                        <div class="col-md-4">
                            <h3>{{ $departments->count() }}</h3>
                            <p>Departments</p>
                        </div>
                        <div class="col-md-4">
                            <h3>{{ $locations->count() }}</h3>
                            <p>Locations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="container">
            <form method="GET" action="{{ route('career.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search Jobs</label>
                        <input type="text" name="search" class="form-control" placeholder="Job title, keywords..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Location</label>
                        <select name="location" class="form-select">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employment Type</label>
                        <select name="employment_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($employmentTypes as $type)
                                <option value="{{ $type }}" {{ request('employment_type') == $type ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('-', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Search Jobs
                        </button>
                        <a href="{{ route('career.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs Listing -->
    <div class="container my-5">
        @if($jobs->count() > 0)
            <div class="row">
                @foreach($jobs as $job)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card job-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title">{{ $job->title }}</h5>
                                    <span class="badge bg-{{ $job->employment_type == 'full-time' ? 'primary' : ($job->employment_type == 'part-time' ? 'warning' : 'info') }} job-badge">
                                        {{ ucwords(str_replace('-', ' ', $job->employment_type)) }}
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-building"></i> {{ $job->department->department_name }}
                                    </small>
                                    @if($job->location)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> {{ $job->location }}
                                        </small>
                                    @endif
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> Posted {{ $job->posted_date ? $job->posted_date->diffForHumans() : 'Recently' }}
                                    </small>
                                </div>

                                <p class="card-text">
                                    {{ Str::limit(strip_tags($job->description), 120) }}
                                </p>

                                @if($job->salary_min || $job->salary_max)
                                    <div class="mb-3">
                                        <small class="text-success fw-bold">
                                            <i class="fas fa-dollar-sign"></i>
                                            @if($job->salary_min && $job->salary_max)
                                                {{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }}
                                            @elseif($job->salary_min)
                                                From {{ number_format($job->salary_min) }}
                                            @else
                                                Up to {{ number_format($job->salary_max) }}
                                            @endif
                                        </small>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $job->vacancies }} position(s) available</small>
                                    <a href="{{ route('career.show', $job->id) }}" class="btn btn-primary btn-sm">
                                        View Details <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $jobs->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No jobs found</h4>
                <p class="text-muted">Try adjusting your search criteria or check back later for new opportunities.</p>
                <a href="{{ route('career.index') }}" class="btn btn-primary">View All Jobs</a>
            </div>
        @endif
    </div>

    <!-- Application Status Check -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <h3>Check Your Application Status</h3>
                    <p class="text-muted mb-4">Already applied? Enter your email to check the status of your applications.</p>
                    
                    <form id="statusCheckForm" class="row g-2 justify-content-center">
                        <div class="col-md-7">
                            <input type="email" class="form-control" id="statusEmail" placeholder="Enter your email address" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary w-100">Check Status</button>
                        </div>
                    </form>

                    <div id="statusResults" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('statusCheckForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('statusEmail').value;
            const resultsDiv = document.getElementById('statusResults');
            
            fetch('{{ route("career.application-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<div class="alert alert-success"><h6>Your Applications:</h6><ul class="mb-0">';
                    data.data.forEach(app => {
                        html += `<li><strong>${app.job_title}</strong> - Status: <span class="badge bg-primary">${app.status}</span> (Applied: ${app.applied_date})</li>`;
                    });
                    html += '</ul></div>';
                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = '<div class="alert alert-warning">' + data.message + '</div>';
                }
                resultsDiv.style.display = 'block';
            })
            .catch(error => {
                resultsDiv.innerHTML = '<div class="alert alert-danger">Error checking status. Please try again.</div>';
                resultsDiv.style.display = 'block';
            });
        });
    </script>
</body>
</html>