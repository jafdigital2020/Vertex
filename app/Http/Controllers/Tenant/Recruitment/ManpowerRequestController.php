<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataAccessController;
use App\Models\ManpowerRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManpowerRequestController extends Controller
{
    private function authUser()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'User not authenticated');
        }
        return $user;
    }

    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(63); // Manpower requests submodule

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
        $query = ManpowerRequest::with(['department', 'designation', 'requester', 'reviewer', 'approver', 'jobPosting'])
            ->whereHas('department', function($q) use ($accessData) {
                $q->whereIn('id', $accessData['departments']->pluck('id'));
            })
            ->active()
            ->latest();

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->requester_id) {
            $query->where('requested_by', $request->requester_id);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhere('position', 'like', "%{$request->search}%")
                  ->orWhereHas('department', function($dept) use ($request) {
                      $dept->where('department_name', 'like', "%{$request->search}%");
                  });
            });
        }

        $requests = $query->paginate(15);
        
        $departments = $accessData['departments'];
        $requesters = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        // Statistics for dashboard cards
        $stats = [
            'total' => ManpowerRequest::active()->count(),
            'pending_coo' => ManpowerRequest::active()->where('status', 'pending_coo_approval')->count(),
            'ready_to_post' => ManpowerRequest::active()->where('status', 'approved')->whereNull('job_posting_id')->count(),
            'posted' => ManpowerRequest::active()->where('status', 'posted')->count(),
            'filled' => ManpowerRequest::active()->where('status', 'filled')->count(),
            'rejected' => ManpowerRequest::active()->where('status', 'rejected')->count(),
            'closed' => ManpowerRequest::active()->where('status', 'closed')->count(),
        ];

        return view('tenant.recruitment.manpower-requests.index', compact(
            'requests', 
            'departments', 
            'requesters', 
            'permission',
            'authUser',
            'stats'
        ));
    }

    public function create()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(63);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
        $departments = $accessData['departments'];
        $designations = $accessData['designations'];

        return view('tenant.recruitment.manpower-requests.create', compact(
            'departments', 
            'designations', 
            'permission',
            'authUser'
        ));
    }

    public function store(Request $request)
    {
        $authUser = $this->authUser();

        $validator = Validator::make($request->all(), [
            'position' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'vacancies' => 'required|integer|min:1',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'justification' => 'required|string',
            'job_description' => 'required|string',
            'target_start_date' => 'nullable|date|after_or_equal:today',
            'priority' => 'required|in:low,medium,high,urgent',
            'requirements' => 'nullable|array',
            'skills' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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

        $requestNumber = 'MR-' . date('Y') . '-' . str_pad(
            ManpowerRequest::whereYear('created_at', date('Y'))->count() + 1, 
            3, 
            '0', 
            STR_PAD_LEFT
        );

        $status = $request->submit_for_review ? 'pending_coo_approval' : 'pending';
        
        $manpowerRequest = ManpowerRequest::create([
            'request_number' => $requestNumber,
            'position' => $request->position,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'vacancies' => $request->vacancies,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'justification' => $request->justification,
            'job_description' => $request->job_description,
            'requirements' => $requirements,
            'skills' => $skills,
            'target_start_date' => $request->target_start_date,
            'priority' => $request->priority,
            'requested_by' => $authUser->id,
            'status' => $status,
            'submitted_at' => now(),
            'reviewed_by' => $request->submit_for_review ? $authUser->id : null,
            'reviewed_at' => $request->submit_for_review ? now() : null,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Manpower request submitted successfully',
            'data' => $manpowerRequest
        ]);
    }

    public function show(ManpowerRequest $manpowerRequest)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(63);

        $manpowerRequest->load(['department', 'designation', 'requester', 'reviewer', 'approver', 'jobPosting.applications']);

        return view('tenant.recruitment.manpower-requests.show', compact(
            'manpowerRequest', 
            'permission',
            'authUser'
        ));
    }

    public function edit(ManpowerRequest $manpowerRequest)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(63);

        if (!$manpowerRequest->can_edit) {
            return redirect()->route('recruitment.manpower-requests.index')
                ->with('error', 'This request cannot be edited in its current status.');
        }

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
        $departments = $accessData['departments'];
        $designations = $accessData['designations'];

        return view('tenant.recruitment.manpower-requests.edit', compact(
            'manpowerRequest',
            'departments', 
            'designations', 
            'permission',
            'authUser'
        ));
    }

    public function update(Request $request, ManpowerRequest $manpowerRequest)
    {
        if (!$manpowerRequest->can_edit) {
            return response()->json(['error' => 'This request cannot be edited'], 403);
        }

        $validator = Validator::make($request->all(), [
            'position' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'vacancies' => 'required|integer|min:1',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'justification' => 'required|string',
            'job_description' => 'required|string',
            'target_start_date' => 'nullable|date|after_or_equal:today',
            'priority' => 'required|in:low,medium,high,urgent',
            'requirements' => 'nullable|array',
            'skills' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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

        $manpowerRequest->update([
            'position' => $request->position,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'vacancies' => $request->vacancies,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'justification' => $request->justification,
            'job_description' => $request->job_description,
            'requirements' => $requirements,
            'skills' => $skills,
            'target_start_date' => $request->target_start_date,
            'priority' => $request->priority
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Manpower request updated successfully'
        ]);
    }

    public function approve(Request $request, ManpowerRequest $manpowerRequest)
    {
        $authUser = $this->authUser();

        if (!$manpowerRequest->can_approve) {
            return response()->json(['error' => 'This request cannot be approved'], 403);
        }

        $manpowerRequest->approve($authUser->id, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Manpower request approved successfully'
        ]);
    }

    public function reject(Request $request, ManpowerRequest $manpowerRequest)
    {
        $authUser = $this->authUser();

        if (!$manpowerRequest->can_reject) {
            return response()->json(['error' => 'This request cannot be rejected'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $manpowerRequest->reject($authUser->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Manpower request rejected'
        ]);
    }

    public function postJob(ManpowerRequest $manpowerRequest)
    {
        $authUser = $this->authUser();

        if (!$manpowerRequest->can_post) {
            return response()->json(['error' => 'This request cannot be posted as a job'], 403);
        }

        $jobPosting = $manpowerRequest->createJobPosting($authUser->id);

        return response()->json([
            'success' => true,
            'message' => 'Job posting created successfully',
            'data' => [
                'job_posting_id' => $jobPosting->id,
                'job_code' => $jobPosting->job_code
            ]
        ]);
    }

    public function submitForReview(Request $request, ManpowerRequest $manpowerRequest)
    {
        $authUser = $this->authUser();

        if ($manpowerRequest->status !== 'pending') {
            return response()->json(['error' => 'Only pending requests can be submitted for review'], 403);
        }

        $manpowerRequest->submitForReview($authUser->id, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Request submitted for COO approval'
        ]);
    }
}