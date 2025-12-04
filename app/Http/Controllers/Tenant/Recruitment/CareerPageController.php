<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\Candidate;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Notifications\JobApplicationSubmitted;

class CareerPageController extends Controller
{
    public function index(Request $request)
    {
        $jobs = JobPosting::with(['department', 'designation'])
            ->open()
            ->active();

        if ($request->has('department') && $request->department) {
            $jobs->where('department_id', $request->department);
        }

        if ($request->has('location') && $request->location) {
            $jobs->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('employment_type') && $request->employment_type) {
            $jobs->where('employment_type', $request->employment_type);
        }

        if ($request->has('search') && $request->search) {
            $jobs->where(function($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $jobs = $jobs->orderBy('posted_date', 'desc')->paginate(12);

        $departments = JobPosting::open()->distinct('department_id')
            ->with('department')
            ->get()
            ->pluck('department')
            ->filter();

        $locations = JobPosting::open()
            ->whereNotNull('location')
            ->distinct('location')
            ->pluck('location');

        $employmentTypes = ['full-time', 'part-time', 'contract', 'internship'];

        return view('career.index', compact(
            'jobs',
            'departments',
            'locations',
            'employmentTypes'
        ));
    }

    public function show(JobPosting $job)
    {
        if ($job->status !== 'open' || !$job->is_active || $job->is_expired) {
            abort(404, 'Job posting not found or no longer available');
        }

        $job->load(['department', 'designation']);

        $relatedJobs = JobPosting::open()
            ->active()
            ->where('id', '!=', $job->id)
            ->where('department_id', $job->department_id)
            ->take(3)
            ->get();

        return view('career.show', compact('job', 'relatedJobs'));
    }

    public function showApplicationForm(JobPosting $job)
    {
        if ($job->status !== 'open' || !$job->is_active || $job->is_expired) {
            return redirect()->route('career.show', $job->id)
                ->with('error', 'This job posting is no longer accepting applications.');
        }

        $relatedJobs = JobPosting::open()
            ->active()
            ->where('id', '!=', $job->id)
            ->where('department_id', $job->department_id)
            ->take(3)
            ->get();

        $candidate = auth('candidate')->user();

        return view('career.apply', compact('job', 'relatedJobs', 'candidate'));
    }

    public function apply(Request $request, JobPosting $job)
    {
        if ($job->status !== 'open' || !$job->is_active || $job->is_expired) {
            return response()->json([
                'success' => false,
                'message' => 'This job posting is no longer available'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cover_letter' => 'required|string',
            'expected_salary' => 'nullable|numeric|min:0',
            'available_start_date' => 'nullable|date|after_or_equal:today',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'linkedin_profile' => 'nullable|url',
            'education' => 'nullable|array',
            'education.*.institution' => 'required_with:education|string',
            'education.*.degree' => 'required_with:education|string',
            'education.*.field_of_study' => 'nullable|string',
            'education.*.start_year' => 'required_with:education|integer|min:1950|max:' . (date('Y') + 10),
            'education.*.end_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 10),
            'education.*.is_current' => 'boolean',
            'experience' => 'nullable|array',
            'experience.*.company' => 'required_with:experience|string',
            'experience.*.position' => 'required_with:experience|string',
            'experience.*.start_date' => 'required_with:experience|date',
            'experience.*.end_date' => 'nullable|date|after_or_equal:experience.*.start_date',
            'experience.*.is_current' => 'boolean',
            'experience.*.description' => 'nullable|string',
            'skills' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated candidate
        $candidate = auth('candidate')->user();

        // Check if candidate has already applied for this job
        $existingApplication = JobApplication::where('job_posting_id', $job->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this position'
            ], 400);
        }

        // Update candidate information with form data
        $candidate->update([
            'phone' => $request->phone ?: $candidate->phone,
            'address' => $request->address ?: $candidate->address,
            'linkedin_profile' => $request->linkedin_profile ?: $candidate->linkedin_profile,
            'expected_salary' => $request->expected_salary ?: $candidate->expected_salary,
        ]);

        // Process skills field - convert string to array if needed
        $skills = $request->skills;
        if (is_string($skills) && !empty($skills)) {
            $skills = array_map('trim', explode(',', $skills));
            $skills = array_filter($skills); // Remove empty values
            $candidate->update(['skills' => $skills]);
        }

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('candidates/resumes', 'public');
            $candidate->update(['resume_path' => $resumePath]);
        }

        // Handle education
        if ($request->has('education') && is_array($request->education)) {
            // Delete existing education records
            $candidate->education()->delete();
            
            foreach ($request->education as $edu) {
                if (!empty($edu['institution']) && !empty($edu['degree'])) {
                    CandidateEducation::create([
                        'candidate_id' => $candidate->id,
                        'institution' => $edu['institution'],
                        'degree' => $edu['degree'],
                        'field_of_study' => $edu['field_of_study'] ?? null,
                        'start_year' => $edu['start_year'],
                        'end_year' => $edu['end_year'] ?? null,
                        'is_current' => $edu['is_current'] ?? false
                    ]);
                }
            }
        }

        // Handle experience  
        if ($request->has('experience') && is_array($request->experience)) {
            // Delete existing experience records
            $candidate->experience()->delete();
            
            foreach ($request->experience as $exp) {
                if (!empty($exp['company']) && !empty($exp['position'])) {
                    CandidateExperience::create([
                        'candidate_id' => $candidate->id,
                        'company' => $exp['company'],
                        'position' => $exp['position'],
                        'description' => $exp['description'] ?? null,
                        'start_date' => $exp['start_date'],
                        'end_date' => $exp['end_date'] ?? null,
                        'is_current' => $exp['is_current'] ?? false
                    ]);
                }
            }
        }

        $applicationCode = 'APP-' . strtoupper(Str::random(8));
        while (JobApplication::where('application_code', $applicationCode)->exists()) {
            $applicationCode = 'APP-' . strtoupper(Str::random(8));
        }

        $application = JobApplication::create([
            'application_code' => $applicationCode,
            'job_posting_id' => $job->id,
            'candidate_id' => $candidate->id,
            'status' => 'applied',
            'cover_letter' => $request->cover_letter,
            'expected_salary' => $request->expected_salary,
            'available_start_date' => $request->available_start_date,
            'applied_at' => Carbon::now(),
            'stage' => 1
        ]);

        $application->workflowHistory()->create([
            'from_status' => null,
            'to_status' => 'applied',
            'changed_by' => $candidate->id,
            'notes' => 'Application submitted via career page'
        ]);

        // Send email notification to candidate
        $candidate->notify(new JobApplicationSubmitted($application, $job));

        // Check if this is an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully! We will review your application and get back to you soon.',
                'data' => [
                    'application_code' => $application->application_code,
                    'job_title' => $job->title
                ]
            ]);
        }

        // Regular form submission - redirect with success message
        return redirect()->route('career.show', $job->id)
            ->with('success', 'Application submitted successfully! We will review your application and get back to you soon.')
            ->with('application_code', $application->application_code);
    }

    public function applicationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'application_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = Candidate::where('email', $request->email)->first();

        if (!$candidate) {
            return response()->json([
                'success' => false,
                'message' => 'No applications found for this email address'
            ], 404);
        }

        $applications = $candidate->applications()
            ->with(['jobPosting', 'interviews', 'offers'])
            ->when($request->application_code, function($query) use ($request) {
                return $query->where('application_code', $request->application_code);
            })
            ->orderBy('applied_at', 'desc')
            ->get();

        if ($applications->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No applications found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $applications->map(function($app) {
                return [
                    'application_code' => $app->application_code,
                    'job_title' => $app->jobPosting->title,
                    'company' => $app->jobPosting->department->department_name,
                    'status' => $app->status_label,
                    'applied_date' => $app->applied_at->format('M d, Y'),
                    'last_updated' => $app->last_updated_at ? $app->last_updated_at->format('M d, Y') : null,
                    'interviews_count' => $app->interviews->count(),
                    'offers_count' => $app->offers->count()
                ];
            })
        ]);
    }

    public function withdrawApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_code' => 'required|exists:job_applications,application_code',
            'email' => 'required|email',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $application = JobApplication::where('application_code', $request->application_code)
            ->whereHas('candidate', function($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        if (in_array($application->status, ['hired', 'rejected', 'withdrawn'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot withdraw application in current status'
            ], 400);
        }

        $application->updateStatus('withdrawn', $application->candidate_id, $request->reason, 'Withdrawn by candidate');

        return response()->json([
            'success' => true,
            'message' => 'Application withdrawn successfully'
        ]);
    }

    public function searchJobs(Request $request)
    {
        $query = $request->get('q', '');
        
        $jobs = JobPosting::open()
            ->active()
            ->where(function($queryBuilder) use ($query) {
                $queryBuilder->where('title', 'like', '%' . $query . '%')
                            ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->with('department')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs->map(function($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'department' => $job->department->department_name,
                    'location' => $job->location,
                    'employment_type' => $job->employment_type,
                    'url' => route('career.show', $job->id)
                ];
            })
        ]);
    }
}