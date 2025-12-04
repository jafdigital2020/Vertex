<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataAccessController;
use App\Helpers\PermissionHelper;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Candidate;
use App\Models\ApplicationWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JobApplicationController extends Controller
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
        $permission = PermissionHelper::get(60);

        $applications = JobApplication::with([
            'jobPosting.department',
            'candidate',
            'recruiter',
            'interviews',
            'offers'
        ]);

        if ($request->has('status') && $request->status) {
            $applications->where('status', $request->status);
        }

        if ($request->has('job_posting_id') && $request->job_posting_id) {
            $applications->where('job_posting_id', $request->job_posting_id);
        }

        if ($request->has('recruiter_id') && $request->recruiter_id) {
            $applications->where('assigned_recruiter', $request->recruiter_id);
        }

        if ($request->has('search') && $request->search) {
            $applications->whereHas('candidate', function($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
            })->orWhereHas('jobPosting', function($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            });
        }

        $applications = $applications->latest()->paginate(15);

        $jobPostings = JobPosting::active()->get();
        $statuses = [
            'applied', 'under_review', 'shortlisted', 'interview_scheduled',
            'interviewed', 'evaluation', 'offer_made', 'offer_accepted',
            'offer_rejected', 'hired', 'rejected', 'withdrawn'
        ];

        return view('tenant.recruitment.applications.index', compact(
            'applications',
            'jobPostings',
            'statuses',
            'permission',
            'authUser'
        ));
    }

    public function show(JobApplication $application)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(60);

        $application->load([
            'jobPosting.department',
            'candidate.education',
            'candidate.experience',
            'recruiter',
            'interviews.primaryInterviewer',
            'offers',
            'workflowHistory.changedBy'
        ]);

        return view('tenant.recruitment.applications.show', compact(
            'application',
            'permission',
            'authUser'
        ));
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        $authUser = $this->authUser();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:applied,under_review,shortlisted,interview_scheduled,interviewed,evaluation,offer_made,offer_accepted,offer_rejected,hired,rejected,withdrawn',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$application->canMoveTo($request->status)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition'
            ], 400);
        }

        $application->updateStatus($request->status, $authUser->id, $request->notes, $request->reason);

        if (in_array($request->status, ['hired'])) {
            $application->candidate->update(['status' => 'hired']);
        } elseif (in_array($request->status, ['rejected', 'withdrawn'])) {
            $application->candidate->update(['status' => 'rejected']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'data' => $application->load(['workflowHistory.changedBy'])
        ]);
    }

    public function assignRecruiter(Request $request, JobApplication $application)
    {
        $validator = Validator::make($request->all(), [
            'recruiter_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $application->update(['assigned_recruiter' => $request->recruiter_id]);

        return response()->json([
            'success' => true,
            'message' => 'Recruiter assigned successfully'
        ]);
    }

    public function updateScore(Request $request, JobApplication $application)
    {
        $validator = Validator::make($request->all(), [
            'overall_score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $application->update([
            'overall_score' => $request->overall_score,
            'recruiter_notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application score updated successfully'
        ]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $authUser = $this->authUser();

        $validator = Validator::make($request->all(), [
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:job_applications,id',
            'status' => 'required|in:applied,under_review,shortlisted,interview_scheduled,interviewed,evaluation,offer_made,offer_accepted,offer_rejected,hired,rejected,withdrawn',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $applications = JobApplication::whereIn('id', $request->application_ids)->get();
        $updated = 0;

        foreach ($applications as $application) {
            if ($application->canMoveTo($request->status)) {
                $application->updateStatus($request->status, $authUser->id, $request->notes, $request->reason);
                $updated++;

                if (in_array($request->status, ['hired'])) {
                    $application->candidate->update(['status' => 'hired']);
                } elseif (in_array($request->status, ['rejected', 'withdrawn'])) {
                    $application->candidate->update(['status' => 'rejected']);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} applications updated successfully"
        ]);
    }

    public function getWorkflowHistory(JobApplication $application)
    {
        $history = $application->workflowHistory()
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    public function getApplicationsByStatus(Request $request)
    {
        $applications = JobApplication::byStatus($request->status)
            ->with(['jobPosting', 'candidate'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    public function kanbanView()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(60);

        $statuses = [
            'applied' => JobApplication::byStatus('applied')->with(['candidate', 'jobPosting'])->get(),
            'under_review' => JobApplication::byStatus('under_review')->with(['candidate', 'jobPosting'])->get(),
            'shortlisted' => JobApplication::byStatus('shortlisted')->with(['candidate', 'jobPosting'])->get(),
            'interview_scheduled' => JobApplication::byStatus('interview_scheduled')->with(['candidate', 'jobPosting'])->get(),
            'interviewed' => JobApplication::byStatus('interviewed')->with(['candidate', 'jobPosting'])->get(),
            'evaluation' => JobApplication::byStatus('evaluation')->with(['candidate', 'jobPosting'])->get(),
            'offer_made' => JobApplication::byStatus('offer_made')->with(['candidate', 'jobPosting'])->get(),
            'hired' => JobApplication::byStatus('hired')->with(['candidate', 'jobPosting'])->get(),
            'rejected' => JobApplication::byStatus('rejected')->with(['candidate', 'jobPosting'])->get()
        ];

        return view('tenant.recruitment.applications.kanban', compact(
            'statuses',
            'permission',
            'authUser'
        ));
    }

    public function statistics()
    {
        $stats = [
            'total_applications' => JobApplication::count(),
            'new_applications' => JobApplication::byStatus('applied')->count(),
            'in_review' => JobApplication::byStatus('under_review')->count(),
            'shortlisted' => JobApplication::byStatus('shortlisted')->count(),
            'interviewed' => JobApplication::byStatus('interviewed')->count(),
            'hired' => JobApplication::byStatus('hired')->count(),
            'rejected' => JobApplication::byStatus('rejected')->count(),
            'this_month' => JobApplication::whereBetween('applied_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count(),
            'this_week' => JobApplication::whereBetween('applied_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}