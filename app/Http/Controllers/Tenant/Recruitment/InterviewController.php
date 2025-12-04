<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Helpers\PermissionHelper;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InterviewController extends Controller
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
        $permission = PermissionHelper::get(61);

        $interviews = Interview::with([
            'jobApplication.candidate',
            'jobApplication.jobPosting',
            'primaryInterviewer'
        ]);

        if ($request->has('status') && $request->status) {
            $interviews->where('status', $request->status);
        }

        if ($request->has('type') && $request->type) {
            $interviews->where('type', $request->type);
        }

        if ($request->has('interviewer_id') && $request->interviewer_id) {
            $interviews->byInterviewer($request->interviewer_id);
        }

        if ($request->has('date') && $request->date) {
            $interviews->whereDate('scheduled_at', $request->date);
        }

        if ($request->has('search') && $request->search) {
            $interviews->whereHas('jobApplication.candidate', function($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }

        $interviews = $interviews->orderBy('scheduled_at', 'asc')->paginate(15);

        $interviewers = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        $statuses = ['scheduled', 'confirmed', 'rescheduled', 'completed', 'cancelled', 'no_show'];
        $types = ['phone', 'video', 'in_person', 'technical', 'panel', 'hr'];

        return view('tenant.recruitment.interviews.index', compact(
            'interviews',
            'interviewers',
            'statuses',
            'types',
            'permission',
            'authUser'
        ));
    }

    public function create(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(61);

        $jobApplicationId = $request->job_application_id;
        $jobApplication = null;

        if ($jobApplicationId) {
            $jobApplication = JobApplication::with(['candidate', 'jobPosting'])->find($jobApplicationId);
        }

        $interviewers = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        $types = ['phone', 'video', 'in_person', 'technical', 'panel', 'hr'];
        $rounds = ['1', '2', '3', '4', '5', 'final'];

        return view('tenant.recruitment.interviews.create', compact(
            'jobApplication',
            'interviewers',
            'types',
            'rounds',
            'permission',
            'authUser'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_application_id' => 'required|exists:job_applications,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:phone,video,in_person,technical,panel,hr',
            'round' => 'required|in:1,2,3,4,5,final',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'primary_interviewer' => 'required|exists:users,id',
            'panel_interviewers' => 'nullable|array',
            'panel_interviewers.*' => 'exists:users,id',
            'agenda' => 'nullable|string',
            'questions' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $interviewCode = 'INT-' . strtoupper(Str::random(8));
        while (Interview::where('interview_code', $interviewCode)->exists()) {
            $interviewCode = 'INT-' . strtoupper(Str::random(8));
        }

        $interview = Interview::create([
            'interview_code' => $interviewCode,
            'job_application_id' => $request->job_application_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'round' => $request->round,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'status' => 'scheduled',
            'primary_interviewer' => $request->primary_interviewer,
            'panel_interviewers' => $request->panel_interviewers,
            'agenda' => $request->agenda,
            'questions' => $request->questions
        ]);

        $interview->jobApplication()->update(['status' => 'interview_scheduled']);

        return response()->json([
            'success' => true,
            'message' => 'Interview scheduled successfully',
            'data' => $interview->load(['jobApplication.candidate', 'primaryInterviewer'])
        ]);
    }

    public function show(Interview $interview)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(61);

        $interview->load([
            'jobApplication.candidate.education',
            'jobApplication.candidate.experience',
            'jobApplication.jobPosting',
            'primaryInterviewer'
        ]);

        return view('tenant.recruitment.interviews.show', compact(
            'interview',
            'permission',
            'authUser'
        ));
    }

    public function edit(Interview $interview)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(61);

        $interview->load(['jobApplication.candidate', 'jobApplication.jobPosting']);

        $interviewers = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        $types = ['phone', 'video', 'in_person', 'technical', 'panel', 'hr'];
        $rounds = ['1', '2', '3', '4', '5', 'final'];

        return view('tenant.recruitment.interviews.edit', compact(
            'interview',
            'interviewers',
            'types',
            'rounds',
            'permission',
            'authUser'
        ));
    }

    public function update(Request $request, Interview $interview)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:phone,video,in_person,technical,panel,hr',
            'round' => 'required|in:1,2,3,4,5,final',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'primary_interviewer' => 'required|exists:users,id',
            'panel_interviewers' => 'nullable|array',
            'panel_interviewers.*' => 'exists:users,id',
            'agenda' => 'nullable|string',
            'questions' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $interview->update([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'round' => $request->round,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'primary_interviewer' => $request->primary_interviewer,
            'panel_interviewers' => $request->panel_interviewers,
            'agenda' => $request->agenda,
            'questions' => $request->questions
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interview updated successfully',
            'data' => $interview
        ]);
    }

    public function updateStatus(Request $request, Interview $interview)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:scheduled,confirmed,rescheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = ['status' => $request->status];

        if ($request->status === 'completed') {
            $updateData['actual_start_time'] = now();
            $updateData['actual_end_time'] = now()->addMinutes($interview->duration_minutes);
        }

        if ($request->notes) {
            $updateData['notes'] = $request->notes;
        }

        $interview->update($updateData);

        if ($request->status === 'completed') {
            $interview->jobApplication()->update(['status' => 'interviewed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Interview status updated successfully'
        ]);
    }

    public function addFeedback(Request $request, Interview $interview)
    {
        $validator = Validator::make($request->all(), [
            'feedback' => 'required|string',
            'score' => 'nullable|numeric|min:0|max:100',
            'recommendation' => 'nullable|in:strong_hire,hire,hold,no_hire'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $interview->update([
            'feedback' => $request->feedback,
            'score' => $request->score,
            'recommendation' => $request->recommendation
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interview feedback added successfully'
        ]);
    }

    public function reschedule(Request $request, Interview $interview)
    {
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $interview->update([
            'scheduled_at' => $request->scheduled_at,
            'status' => 'rescheduled',
            'notes' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interview rescheduled successfully'
        ]);
    }

    public function calendar(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(61);

        $interviews = Interview::with([
            'jobApplication.candidate',
            'jobApplication.jobPosting',
            'primaryInterviewer'
        ])->upcoming();

        if ($request->has('interviewer_id') && $request->interviewer_id) {
            $interviews->byInterviewer($request->interviewer_id);
        }

        $interviews = $interviews->get();

        $events = $interviews->map(function ($interview) {
            return [
                'id' => $interview->id,
                'title' => $interview->title,
                'start' => $interview->scheduled_at->toISOString(),
                'end' => $interview->scheduled_at->addMinutes($interview->duration_minutes)->toISOString(),
                'candidate' => $interview->jobApplication->candidate->full_name,
                'position' => $interview->jobApplication->jobPosting->title,
                'interviewer' => $interview->primaryInterviewer->username,
                'type' => $interview->type,
                'status' => $interview->status,
                'location' => $interview->location,
                'meeting_link' => $interview->meeting_link
            ];
        });

        return view('tenant.recruitment.interviews.calendar', compact(
            'events',
            'permission',
            'authUser'
        ));
    }

    public function todaysInterviews()
    {
        $interviews = Interview::today()
            ->with([
                'jobApplication.candidate',
                'jobApplication.jobPosting',
                'primaryInterviewer'
            ])
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $interviews
        ]);
    }

    public function upcomingInterviews(Request $request)
    {
        $days = $request->get('days', 7);
        
        $interviews = Interview::where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addDays($days))
            ->with([
                'jobApplication.candidate',
                'jobApplication.jobPosting',
                'primaryInterviewer'
            ])
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $interviews
        ]);
    }
}