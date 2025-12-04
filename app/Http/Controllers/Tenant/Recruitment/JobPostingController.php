<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataAccessController;
use App\Helpers\PermissionHelper;
use App\Models\JobPosting;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JobPostingController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        } else {
            $user = Auth::user();
        }

        if ($user && method_exists($user, 'employmentDetail')) {
            $employmentDetail = $user->employmentDetail;
            if ($employmentDetail && isset($employmentDetail->branch_id)) {
                $user->branch_id = $employmentDetail->branch_id;
            } else {
                $user->branch_id = null;
            }
        } else {
            $user->branch_id = null;
        }

        return $user;
    }

    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(58);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
        $jobPostings = JobPosting::with(['department', 'designation', 'creator', 'recruiter'])
            ->whereHas('department', function($query) use ($accessData) {
                $query->whereIn('id', $accessData['departments']->pluck('id'));
            });

        if ($request->has('status') && $request->status) {
            $jobPostings->where('status', $request->status);
        }

        if ($request->has('department_id') && $request->department_id) {
            $jobPostings->where('department_id', $request->department_id);
        }

        if ($request->has('search') && $request->search) {
            $jobPostings->where(function($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('job_code', 'like', '%' . $request->search . '%')
                      ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $jobPostings = $jobPostings->latest()->paginate(15);
        
        $departments = $accessData['departments'];
        $recruiters = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        // Statistics for dashboard cards
        $baseQuery = JobPosting::whereHas('department', function($query) use ($accessData) {
            $query->whereIn('id', $accessData['departments']->pluck('id'));
        });
        
        $statistics = [
            'total' => $baseQuery->count(),
            'draft' => $baseQuery->where('status', 'draft')->count(),
            'open' => $baseQuery->where('status', 'open')->count(),
            'closed' => $baseQuery->where('status', 'closed')->count(),
            'filled' => $baseQuery->where('status', 'filled')->count(),
            'total_applications' => $baseQuery->withCount('applications')->get()->sum('applications_count')
        ];

        return view('tenant.recruitment.job-postings.index', compact(
            'jobPostings', 
            'departments', 
            'recruiters', 
            'permission',
            'authUser',
            'statistics'
        ));
    }

    public function create()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(58);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $recruiters = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        return view('tenant.recruitment.job-postings.create', compact(
            'departments', 
            'designations', 
            'recruiters', 
            'permission',
            'authUser'
        ));
    }

    public function store(Request $request)
    {
        $authUser = $this->authUser();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'vacancies' => 'required|integer|min:1',
            'expiration_date' => 'nullable|date|after_or_equal:today',
            'assigned_recruiter' => 'nullable|exists:users,id',
            'requirements' => 'nullable|array',
            'skills' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jobCode = 'JOB-' . strtoupper(Str::random(8));
        while (JobPosting::where('job_code', $jobCode)->exists()) {
            $jobCode = 'JOB-' . strtoupper(Str::random(8));
        }

        // Parse JSON strings to arrays if needed
        $requirements = $request->requirements;
        if (is_string($requirements)) {
            $requirements = json_decode($requirements, true) ?: [];
        }
        
        $skills = $request->skills;
        if (is_string($skills)) {
            $skills = json_decode($skills, true) ?: [];
        }

        $jobPosting = JobPosting::create([
            'job_code' => $jobCode,
            'title' => $request->title,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'location' => $request->location,
            'description' => $request->description,
            'requirements' => $requirements,
            'skills' => $skills,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'vacancies' => $request->vacancies,
            'status' => $request->has('publish_now') ? 'open' : 'draft',
            'posted_date' => $request->has('publish_now') ? Carbon::today() : null,
            'expiration_date' => $request->expiration_date,
            'created_by' => $authUser->id,
            'assigned_recruiter' => $request->assigned_recruiter,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job posting created successfully',
            'data' => $jobPosting
        ]);
    }

    public function show(JobPosting $jobPosting)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(58);

        $jobPosting->load(['department', 'designation', 'creator', 'recruiter', 'applications.candidate']);
        
        $applicationsStats = [
            'total' => $jobPosting->applications->count(),
            'new' => $jobPosting->applications->where('status', 'applied')->count(),
            'in_review' => $jobPosting->applications->where('status', 'under_review')->count(),
            'interview' => $jobPosting->applications->where('status', 'interview_scheduled')->count(),
            'hired' => $jobPosting->applications->where('status', 'hired')->count(),
            'rejected' => $jobPosting->applications->where('status', 'rejected')->count()
        ];

        return view('tenant.recruitment.job-postings.show', compact(
            'jobPosting', 
            'applicationsStats',
            'permission',
            'authUser'
        ));
    }

    public function edit(JobPosting $jobPosting)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(58);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $recruiters = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        return view('tenant.recruitment.job-postings.edit', compact(
            'jobPosting',
            'departments', 
            'designations', 
            'recruiters', 
            'permission',
            'authUser'
        ));
    }

    public function update(Request $request, JobPosting $jobPosting)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'vacancies' => 'required|integer|min:1',
            'expiration_date' => 'nullable|date',
            'assigned_recruiter' => 'nullable|exists:users,id',
            'requirements' => 'nullable|array',
            'skills' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jobPosting->update([
            'title' => $request->title,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'location' => $request->location,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'skills' => $request->skills,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'vacancies' => $request->vacancies,
            'expiration_date' => $request->expiration_date,
            'assigned_recruiter' => $request->assigned_recruiter
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job posting updated successfully',
            'data' => $jobPosting
        ]);
    }

    public function destroy(JobPosting $jobPosting)
    {
        if ($jobPosting->applications()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete job posting with applications'
            ], 400);
        }

        $jobPosting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job posting deleted successfully'
        ]);
    }

    public function publish(JobPosting $jobPosting)
    {
        $jobPosting->update([
            'status' => 'open',
            'posted_date' => Carbon::today()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job posting published successfully'
        ]);
    }

    public function close(JobPosting $jobPosting)
    {
        $jobPosting->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'message' => 'Job posting closed successfully'
        ]);
    }

    public function clone(JobPosting $jobPosting)
    {
        $authUser = $this->authUser();
        
        $newJobCode = 'JOB-' . strtoupper(Str::random(8));
        while (JobPosting::where('job_code', $newJobCode)->exists()) {
            $newJobCode = 'JOB-' . strtoupper(Str::random(8));
        }

        $newJobPosting = $jobPosting->replicate();
        $newJobPosting->job_code = $newJobCode;
        $newJobPosting->title = $jobPosting->title . ' (Copy)';
        $newJobPosting->status = 'draft';
        $newJobPosting->posted_date = null;
        $newJobPosting->created_by = $authUser->id;
        $newJobPosting->save();

        return response()->json([
            'success' => true,
            'message' => 'Job posting cloned successfully',
            'data' => $newJobPosting
        ]);
    }
}