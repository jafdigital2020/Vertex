<?php

namespace App\Http\Controllers\Tenant\Overtime;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\Overtime;
use Illuminate\Http\Request;
use App\Models\EmploymentDetail;
use App\Models\OvertimeApproval;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class OvertimeController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    private function buildOvertimeQuery($query, $dateRange = null, $branch = null, $department = null, $designation = null, $status = null)
    {
        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange, 2);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();
                $query->whereBetween('overtime_date', [$start, $end]);
            } catch (\Exception $e) {
                Log::error('Error parsing overtime date range: ' . $e->getMessage());
                // Optional: you can choose to ignore invalid dates or return error
            }
        }

        if ($branch) {
            $query->whereHas('user.employmentDetail', fn($q) => $q->where('branch_id', $branch));
        }

        if ($department) {
            $query->whereHas('user.employmentDetail', fn($q) => $q->where('department_id', $department));
        }

        if ($designation) {
            $query->whereHas('user.employmentDetail', fn($q) => $q->where('designation_id', $designation));
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        Log::info('User ID:', ['id' => $authUser->id]);
        Log::info('Overtime base query SQL:', [$accessData['overtimes']->toSql()]);
        Log::info('Overtime count (unfiltered):', ['count' => $accessData['overtimes']->count()]);

        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');

        // Start with the base query from access control
        $baseQuery = $accessData['overtimes'];

        // Apply filters
        $filteredQuery = $this->buildOvertimeQuery(
            $baseQuery,
            $dateRange,
            $branch,
            $department,
            $designation,
            $status
        );

        $overtimes = $filteredQuery->get();

        // Stats: last 30 days (as per your original logic)
        $thirtyDaysAgo = Carbon::today()->subDays(29)->toDateString();
        $today = Carbon::today()->toDateString();

        $pendingCount = $overtimes->where('status', 'pending')
            ->whereBetween('overtime_date', [$thirtyDaysAgo, $today])
            ->count();

        $approvedCount = $overtimes->where('status', 'approved')
            ->whereBetween('overtime_date', [$thirtyDaysAgo, $today])
            ->count();

        $rejectedCount = $overtimes->where('status', 'rejected')
            ->whereBetween('overtime_date', [$thirtyDaysAgo, $today])
            ->count();

        $totalRequests = $pendingCount + $approvedCount + $rejectedCount;

        // Enrich data (approvers, steps, etc.)
        $this->enrichOvertimeData($overtimes);

        $permission = PermissionHelper::get(17);
        $html = view('tenant.overtime.overtime_filter', compact('overtimes', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'totalRequests' => $totalRequests,
        ]);
    }

    /**
     * Display all overtime requests and summary (Admin).
     *
     * Shows all overtime requests for the selected date range, including branch, department, designation, status, and summary statistics (pending, approved, rejected). Only accessible by users with overtime admin permissions.
     *
     * @param \Illuminate\Http\Request $request
     * @queryParam dateRange string Optional. Date range in format "mm/dd/yyyy - mm/dd/yyyy". Example: "11/01/2025 - 11/27/2025"
     * @queryParam branch integer Optional. Filter overtime requests by branch ID. Example: 1
     * @queryParam department integer Optional. Filter overtime requests by department ID. Example: 2
     * @queryParam designation integer Optional. Filter overtime requests by designation ID. Example: 3
     * @queryParam status string Optional. Filter overtime requests by status ("pending", "approved", "rejected").
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "overtimes": [
     *       {
     *         "id": 1,
     *         "user_id": 10,
     *         "overtime_date": "2025-11-01",
     *         "date_ot_in": "2025-11-01 18:00:00",
     *         "date_ot_out": "2025-11-01 20:00:00",
     *         "total_ot_minutes": 120,
     *         "total_night_diff_minutes": 30,
     *         "reason": "Project deadline",
     *         "status": "pending",
     *         "last_approver": "Juan Dela Cruz",
     *         "last_approver_type": "HR"
     *       }
     *     ],
     *     "pendingCount": 2,
     *     "approvedCount": 5,
     *     "rejectedCount": 1,
     *     "totalRequests": 8
     *   }
     * }
     */
    public function overtimeIndex(Request $request)
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        // Default date range: last 30 days
        $defaultStart = Carbon::today()->subDays(29)->format('m/d/Y');
        $defaultEnd = Carbon::today()->format('m/d/Y');
        $defaultDateRange = "$defaultStart - $defaultEnd";

        // Get filters from request (with defaults)
        $dateRange = $request->input('dateRange', $defaultDateRange);
        $branch = $request->input('branch');
        $department = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');

        // Apply filters
        $filteredQuery = $this->buildOvertimeQuery(
            $accessData['overtimes'],
            $dateRange,
            $branch,
            $department,
            $designation,
            $status
        );

        $overtimes = $filteredQuery->with(['user.personalInformation', 'user.employmentDetail'])->get();

        // Stats: last 30 days (same logic)
        $thirtyDaysAgo = Carbon::today()->subDays(29)->toDateString();
        $today = Carbon::today()->toDateString();

        $pendingCount = $overtimes->where('status', 'pending')
            ->whereBetween('overtime_date', [$thirtyDaysAgo, $today])
            ->count();

        $approvedCount = $overtimes->where('status', 'approved')
            ->whereBetween('overtime_date', [$thirtyDaysAgo, $today])
            ->count();

        $rejectedCount = $overtimes->where('status', 'rejected')
            ->whereBetween('overtime_date', [$thirtyDaysAgo, $today])
            ->count();

        $totalRequests = $pendingCount + $approvedCount + $rejectedCount;

        // Enrich data
        $this->enrichOvertimeData($overtimes);

        // Get dropdown options (for web view)
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $permission = PermissionHelper::get(17);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'overtimes' => $overtimes,
                    'pendingCount' => $pendingCount,
                    'approvedCount' => $approvedCount,
                    'rejectedCount' => $rejectedCount,
                    'totalRequests' => $totalRequests,
                ]
            ]);
        }

        return view('tenant.overtime.overtime', [
            'overtimes' => $overtimes,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'totalRequests' => $totalRequests,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'permission' => $permission
        ]);
    }

    private function enrichOvertimeData($overtimes)
    {
        foreach ($overtimes as $ot) {
            $branchId = optional($ot->user->employmentDetail)->branch_id;
            $steps = OvertimeApproval::stepsForBranch($branchId);
            $ot->total_steps = $steps->count();

            $reportingToId = optional($ot->user->employmentDetail)->reporting_to;

            if ($ot->status === 'pending') {
                if ($ot->current_step === 1 && $reportingToId) {
                    $manager = User::with('personalInformation')->find($reportingToId);
                    if ($manager?->personalInformation) {
                        $managerName = trim("{$manager->personalInformation->first_name} {$manager->personalInformation->last_name}");
                        $ot->next_approvers = [$managerName];
                    } else {
                        $ot->next_approvers = ['Manager'];
                    }
                } else {
                    $ot->next_approvers = OvertimeApproval::nextApproversFor($ot, $steps);
                }
            } else {
                $ot->next_approvers = [];
            }

            if ($latest = $ot->latestApproval) {
                $approver = $latest->otApprover;
                $pi = optional($approver->personalInformation);
                $ot->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $ot->last_approver_type = optional($approver->employmentDetail)?->branch?->name ?? 'Global';
            } else {
                $ot->last_approver = null;
                $ot->last_approver_type = null;
            }
        }
    }

    /**
     * Approve, reject, or request changes for an overtime request.
     *
     * Allows an authorized approver to approve, reject, or request changes for an overtime request. Handles multi-step approval workflows, sends notifications, and updates overtime status.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Overtime $overtime The overtime request to act on.
     * @bodyParam action string required The action to perform ("approved", "rejected", "pending"). Example: "approved"
     * @bodyParam comment string Optional. Comment or reason for the action.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Action recorded.",
     *   "data": {
     *     "id": 123,
     *     "status": "approved",
     *     "current_step": 2,
     *     "last_approver": "Maria Santos"
     *   },
     *   "next_approvers": ["Juan Dela Cruz", "HR Manager"]
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You cannot take action on your own overtime request."
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Overtime request has already been rejected."
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Approval step misconfigured."
     * }
     */
    public function overtimeApproval(Request $request, Overtime $overtime)
    {
        // 1) Validate payload
        $data = $request->validate([
            'action'  => 'required|in:approved,rejected,pending',
            'comment' => 'nullable|string',
        ]);

        $user      = $request->user();
        $currStep  = $overtime->current_step;
        $branchId  = (int) optional($overtime->user->employmentDetail)->branch_id;
        $oldStatus = $overtime->status;
        $requester = $overtime->user;
        $reportingToId = optional($overtime->user->employmentDetail)->reporting_to;

        //  Prevent self-approval
        if ($user->id === $requester->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot take action on your own overtime request.',
            ], 403);
        }

        // 1.a) Prevent spamming a second “REJECTED”
        if ($data['action'] === 'rejected' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Overtime request has already been rejected.',
            ], 400);
        }

        // 2) Build the approval workflow for this overtime-owner’s branch
        $steps = OvertimeApproval::stepsForBranch($branchId);
        $maxLevel = $steps->max('level');

        if ($currStep > $maxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid step level.',
            ], 400);
        }

        // 3) Special rule: if reporting_to exists, only that user can approve at step 1, and auto-approved na dapat
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {

                return response()->json([
                    'success' => false,
                    'message' => 'You cannot approve this request.',
                ], 403);
            }

            $newStatus = strtolower($data['action']);
            DB::transaction(function () use ($overtime, $data, $user, $newStatus, $maxLevel) {
                OvertimeApproval::create([
                    'overtime_id'   => $overtime->id,
                    'approver_id'   => $user->id,
                    'step_number'   => 1,
                    'action'        => strtolower($data['action']),
                    'comment'       => $data['comment'] ?? null,
                    'acted_at'      => Carbon::now(),
                ]);
                if ($data['action'] === 'approved') {
                    $overtime->update([
                        'current_step' => 1,
                        'status'       => 'approved',
                    ]);
                } else {
                    $overtime->update(['status' => $newStatus]);
                }
            });

            $overtime->refresh();

            $requester->notify(
                new UserNotification(
                    "Your overtime request on {$overtime->overtime_date} was {$data['action']} by " .
                        $user->personalInformation->first_name . ' ' . $user->personalInformation->last_name . "."
                )
            );


            return response()->json([
                'success'        => true,
                'message'        => 'Action recorded.',
                'data'           => $overtime,
                'next_approvers' => [],
            ]);
        }

        // 4) If NO reporting_to, continue with the normal step workflow
        $cfg = $steps->firstWhere('level', $currStep);
        if (! $cfg) {
            return response()->json([
                'success' => false,
                'message' => 'Approval step misconfigured.',
            ], 500);
        }

        // 5) Authorization (same as before)
        $allowed = false;
        $deptHead = null;
        switch ($cfg->approver_kind) {
            case 'user':
                $allowed = ($user->id === $cfg->approver_user_id);
                break;
            case 'department_head':
                $deptHead = optional(optional($overtime->user->employmentDetail)->department)
                    ->head_of_department;
                $allowed  = ($deptHead && $user->id === $deptHead);
                break;
            case 'role':
                $allowed = $user->hasRole($cfg->approver_value);
                break;
        }
        if (! $allowed) {

            return response()->json([
                'success' => false,
                'message' => 'Not authorized for this step.',
            ], 403);
        }

        $newStatus = strtolower($data['action']);

        DB::transaction(function () use (
            $overtime,
            $data,
            $user,
            $currStep,
            $steps,
            $newStatus,
            $oldStatus,
            $maxLevel
        ) {
            OvertimeApproval::create([
                'overtime_id' => $overtime->id,
                'approver_id'      => $user->id,
                'step_number'      => $currStep,
                'action'           => strtolower($data['action']),
                'comment'          => $data['comment'] ?? null,
                'acted_at'         => Carbon::now(),
            ]);

            if ($data['action'] === 'approved') {
                if ($currStep < $maxLevel) {
                    $overtime->update([
                        'current_step' => $currStep + 1,
                        'status'       => 'pending',
                    ]);
                } else {
                    $overtime->update(['status' => 'approved']);
                }
            } else {
                // REJECTED or CHANGES_REQUESTED
                $overtime->update(['status' => $newStatus]);
            }
        });

        // 7) Return JSON
        $overtime->refresh();

        $next = OvertimeApproval::nextApproversFor($overtime, $steps);

        $requester->notify(
            new UserNotification("Your overtime request on {$overtime->overtime_date} was {$data['action']} by " .
                $user->personalInformation->first_name . ' ' . $user->personalInformation->last_name . ".")
        );

        if ($overtime->status === 'pending') {
            $requesterName = $requester->personalInformation->first_name . ' ' . $requester->personalInformation->last_name;

            foreach ($next as $approver) {
                $approver->notify(
                    new UserNotification(
                        "New overtime request from {$requesterName} ({$overtime->overtime_date}) is pending your approval."
                    )
                );
            }
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Action recorded.',
            'data'           => $overtime,
            'next_approvers' => $next,
        ]);
    }

    /**
     * Reject an overtime request.
     *
     * Allows an authorized approver to reject an overtime request, optionally providing a comment. Handles multi-step approval workflows and sends notifications.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Overtime $overtime The overtime request to reject.
     * @bodyParam comment string Optional. Reason for rejection or additional comments.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Action recorded.",
     *   "data": {
     *     "id": 123,
     *     "status": "rejected",
     *     "current_step": 2,
     *     "last_approver": "Maria Santos"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You cannot reject your own overtime request."
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Overtime request has already been rejected."
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Approval step misconfigured."
     * }
     */
    public function overtimeReject(Request $request, Overtime $overtime)
    {
        $data = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $request->merge([
            'action'  => 'rejected',
            'comment' => $data['comment'],
        ]);

        return $this->overtimeApproval($request, $overtime);
    }

    /**
     * Edit a manual overtime request (Admin).
     *
     * Allows an admin to update an employee's manual overtime request, including overtime date, clock-in/out times, total minutes, night differential, offset date, reason, and optional file attachment. Prevents duplicate entries for the same user and date.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the overtime request to edit.
     * @bodyParam user_id integer required The user ID of the employee. Example: 10
     * @bodyParam overtime_date date required The date for the overtime request. Example: "2025-11-01"
     * @bodyParam date_ot_in datetime required Overtime clock-in date and time. Example: "2025-11-01 18:00:00"
     * @bodyParam date_ot_out datetime required Overtime clock-out date and time. Must be after clock-in. Example: "2025-11-01 20:00:00"
     * @bodyParam total_ot_minutes integer required Total overtime minutes. Example: 120
     * @bodyParam total_night_diff_minutes integer Optional. Night differential minutes. Example: 30
     * @bodyParam file_attachment file Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB).
     * @bodyParam offset_date date Optional. Offset date for overtime. Example: "2025-11-02"
     * @bodyParam reason string Optional. Reason for the overtime request. Example: "Project deadline"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Overtime updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "user_id": 10,
     *     "overtime_date": "2025-11-01",
     *     "date_ot_in": "2025-11-01 18:00:00",
     *     "date_ot_out": "2025-11-01 20:00:00",
     *     "total_ot_minutes": 120,
     *     "total_night_diff_minutes": 30,
     *     "file_attachment": "overtime_attachments/xyz.pdf",
     *     "offset_date": "2025-11-02",
     *     "reason": "Project deadline"
     *   }
     * }
     * @response 403 {
     *   "status": "error",
     *   "message": "You do not have the permission to update."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "The user has an overtime entry for this date."
     * }
     */
    public function overtimeAdminUpdate(Request $request, $id)
    {
        $permission = PermissionHelper::get(17);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $request->validate([
            'overtime_date'      => 'required|date',
            'date_ot_in'         => 'required|date',
            'date_ot_out'        => 'required|date|after:date_ot_in',
            'total_ot_minutes'   => 'required|numeric',
            'total_night_diff_minutes' => 'nullable|numeric|min:0',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'offset_date'        => 'nullable|date',
            'reason'             => 'nullable|string|max:500',
        ]);

        $overtime = Overtime::findOrFail($id);
        $userRequesterId = $request->user_id;

        // Prevent duplicate for same user & date, excluding this record
        $exists = Overtime::where('user_id', $userRequesterId)
            ->whereDate('overtime_date', $request->overtime_date)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'The user has an overtime entry for this date.',
            ], 422);
        }

        // Save old data for logging
        $oldData = $overtime->toArray();

        // Handle file upload if new file
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('overtime_attachments', 'public');
            $overtime->file_attachment = $filePath;
        }

        $overtime->overtime_date = $request->overtime_date;
        $overtime->date_ot_in = $request->date_ot_in;
        $overtime->date_ot_out = $request->date_ot_out;
        $overtime->total_ot_minutes = $request->total_ot_minutes;
        $overtime->total_night_diff_minutes = $request->total_night_diff_minutes ?? 0;
        $overtime->offset_date = $request->offset_date;
        $overtime->reason = $request->reason;

        $overtime->save();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'edit_overtime',
            'description' => 'Edited manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime updated successfully.',
            'data'    => $overtime,
        ]);
    }

    /**
     * Delete a manual overtime request (Admin).
     *
     * Allows an admin to delete any manual overtime request. Only users with delete permission can perform this action. Deletes any attached file as well.
     *
     * @param int $id The ID of the overtime request to delete.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Overtime deleted successfully."
     * }
     * @response 403 {
     *   "status": "error",
     *   "message": "You do not have the permission to delete."
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "Overtime request not found."
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "Server error while deleting overtime request: [error details]"
     * }
     */
    public function overtimeAdminDelete($id)
    {
        $permission = PermissionHelper::get(17);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $overtime = Overtime::findOrFail($id);

        // Save old data for logging
        $oldData = $overtime->toArray();

        $overtime->delete();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'delete_overtime',
            'description' => 'Deleted manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime deleted successfully.',
        ]);
    }

    // Import Overtime
    public function importOvertimeCSV(Request $request)
    {
        $permission = PermissionHelper::get(17);

        if (!in_array('Import', $permission)) {
            return back()->with('toastr_error', "You do not have permission to import");
        }
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        $expectedHeader = [
            'Employee ID',
            'Overtime Date',
            'Offset Date (optional)',
            'OT In Time',
            'OT Out Time',
            'Is Rest Day',
            'Is Holiday',
            'Status'
        ];

        if ($header !== $expectedHeader) {
            return back()->with('toastr_error', 'CSV headers do not match the required template format.');
        }

        $imported = 0;
        $skipped = 0;
        $skippedDetails = [];

        $employeeMap = EmploymentDetail::pluck('user_id', 'employee_id');

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = array_combine($header, $row);

            $employeeId = trim($data['Employee ID']);
            $userId = $employeeMap[$employeeId] ?? null;

            if (! $userId) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Employee ID '{$employeeId}' not found.";
                continue;
            }

            // Lowercase and trim fields for case-insensitive validation
            $isRestDay = strtolower(trim($data['Is Rest Day']));
            $isHoliday = strtolower(trim($data['Is Holiday']));
            $status    = strtolower(trim($data['Status']));

            // Manual validation
            $validator = Validator::make([
                'Overtime Date'  => $data['Overtime Date'],
                'Offset Date'    => $data['Offset Date (optional)'],
                'OT In Time'     => $data['OT In Time'],
                'OT Out Time'    => $data['OT Out Time'],
                'Is Rest Day'    => $isRestDay,
                'Is Holiday'     => $isHoliday,
                'Status'         => $status,
            ], [
                'Overtime Date'  => 'required|date',
                'Offset Date'    => 'nullable|date',
                'OT In Time'     => 'required|string',
                'OT Out Time'    => 'required|string',
                'Is Rest Day'    => 'required|in:true,false',
                'Is Holiday'     => 'required|in:true,false',
                'Status'         => 'required|in:pending,approved,rejected',
            ]);

            if ($validator->fails()) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                $otIn = Carbon::parse("{$data['Overtime Date']} {$data['OT In Time']}");
                $baseOutDate = !empty($data['Offset Date (optional)'])
                    ? $data['Offset Date (optional)']
                    : $data['Overtime Date'];
                $otOut = Carbon::parse("{$baseOutDate} {$data['OT Out Time']}");

                if ($otOut->lessThanOrEqualTo($otIn) && empty($data['Offset Date (optional)'])) {
                    $otOut->addDay(); // handle overnight shift without offset date
                }

                $finalDate = $otOut->format('Y-m-d');
                $exists = Overtime::where('user_id', $userId)
                    ->whereDate('overtime_date', $finalDate)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $skippedDetails[] = "Row $rowNumber: Overtime already exists for {$finalDate}.";
                    continue;
                }

                // Compute total minutes
                $totalMinutes = max(0, $otIn->diffInMinutes($otOut));

                // Night diff window (22:00 to 06:00)
                $ndStart = $otIn->copy()->setTime(22, 0, 0);
                $ndEnd = $ndStart->copy()->addDay()->setTime(6, 0, 0);

                // Compute overlap
                $nightStart = ($otIn > $ndStart) ? $otIn : $ndStart;
                $nightEnd = ($otOut < $ndEnd) ? $otOut : $ndEnd;
                $nightMinutes = ($nightStart < $nightEnd) ? $nightStart->diffInMinutes($nightEnd) : 0;

                // Final regular OT minutes (minus night diff)
                $regularOtMinutes = max(0, $totalMinutes - $nightMinutes);

                Overtime::create([
                    'user_id'           => $userId,
                    'overtime_date'     => $finalDate,
                    'date_ot_in'        => $otIn,
                    'date_ot_out'       => $otOut,
                    'total_ot_minutes'  => $regularOtMinutes,
                    'total_night_diff_minutes' => $nightMinutes,
                    'is_rest_day'       => $isRestDay === 'true',
                    'is_holiday'        => $isHoliday === 'true',
                    'status'            => $status,
                    'current_step'      => 1,
                    'ot_login_type'     => 'Uploaded',
                ]);

                $imported++;
            } catch (\Exception $e) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Error saving. " . $e->getMessage();
            }
        }

        // Flash toastr messages
        if ($imported > 0) {
            return back()->with('toastr_success', "$imported record(s) imported. $skipped skipped.")
                ->with('toastr_details', $skippedDetails);
        } else {
            return back()->with('toastr_error', "No records imported. $skipped skipped.")
                ->with('toastr_details', $skippedDetails);
        }
    }

    // Import Overtime Template Download
    public function downloadOvertimeTemplate()
    {
        $path = public_path('templates/overtime_template.csv');

        if (!file_exists($path)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($path, 'overtime_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // Overtime Bulk Action
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'overtime_ids' => 'required|array|min:1',
            'overtime_ids.*' => 'exists:overtimes,id',
            'comment' => 'nullable|string|max:500'
        ]);

        $action = $request->action;
        $overtimeIds = $request->overtime_ids;
        $comment = $request->comment ?? "Bulk {$action} by admin";
        $userId = Auth::id();

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errors = [];

            foreach ($overtimeIds as $overtimeId) {
                Log::info("Processing overtime request", [
                    'overtime_id' => $overtimeId,
                    'action' => $action
                ]);

                try {
                    $overtimeRequest = Overtime::where('id', $overtimeId)
                        ->first();

                    if (!$overtimeRequest) {
                        $error = "Overtime request {$overtimeId} not found";
                        $errors[] = $error;
                        Log::warning("Overtime request not found", [
                            'overtime_id' => $overtimeId,
                        ]);
                        continue;
                    }

                    // For delete action, we can delete regardless of status
                    if ($action === 'delete') {
                        $overtimeRequest->delete();
                        Log::info("Overtime request deleted successfully", [
                            'overtime_id' => $overtimeId,
                            'user_id' => $userId
                        ]);
                    }
                    // For approve/reject, only process if status is pending
                    else {
                        // Check if already processed
                        if ($overtimeRequest->status !== 'pending') {
                            $error = "Overtime request {$overtimeId} is already {$overtimeRequest->status}";
                            $errors[] = $error;
                            Log::warning("Overtime request already processed", [
                                'overtime_id' => $overtimeId,
                                'current_status' => $overtimeRequest->status,
                                'attempted_action' => $action
                            ]);
                            continue;
                        }

                        // Process the action
                        if ($action === 'approve') {
                            $this->approveOvertimeRequest($overtimeRequest, $comment, $userId);
                            Log::info("Overtime request approved successfully", [
                                'overtime_id' => $overtimeId,
                                'user_id' => $userId
                            ]);
                        } else {
                            $this->rejectOvertimeRequest($overtimeRequest, $comment, $userId);
                            Log::info("Overtime request rejected successfully", [
                                'overtime_id' => $overtimeId,
                                'user_id' => $userId
                            ]);
                        }
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $error = "Failed to {$action} overtime request {$overtimeId}: " . $e->getMessage();
                    $errors[] = $error;
                }
            }

            DB::commit();

            $message = "Successfully {$action}d {$successCount} leave request(s).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " failed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $successCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Helper method for approving overtime requests bulk action
    private function approveOvertimeRequest($overtimeRequest, $comment, $userId)
    {
        $user = User::find($userId);
        $requester = $overtimeRequest->user;

        $requester->refresh();
        $requester->load('employmentDetail');

        $currStep = $overtimeRequest->current_step;
        $branchId = (int) optional($requester->employmentDetail)->branch_id;
        $oldStatus = $overtimeRequest->status;

        // 1) Prevent self-approval
        if ($user->id === $requester->id) {
            throw new \Exception('Cannot approve own overtime request.');
        }

        // 2) Build the approval workflow
        $steps = OvertimeApproval::stepsForBranch($branchId);
        $maxLevel = $steps->max('level');

        // ✅ CRITICAL: Get CURRENT reporting_to (not cached)
        $reportingToId = optional($requester->employmentDetail)->reporting_to;

        // 3) Special rule: If reporting_to exists at step 1
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                throw new \Exception('Only the current reporting manager can approve this request.');
            }

            // Auto-final approve for reporting manager
            OvertimeApproval::create([
                'overtime_id' => $overtimeRequest->id,
                'approver_id' => $user->id,
                'step_number' => 1,
                'action' => 'approved',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $overtimeRequest->update([
                'current_step' => 1,
                'status' => 'approved',
            ]);
        }

        // 4) If NO reporting_to, continue with normal step workflow
        $cfg = $steps->firstWhere('level', $currStep);
        if (!$cfg) {
            throw new \Exception('Approval step misconfigured.');
        }

        // 5) Authorization check (same as main method)
        $allowed = false;
        switch ($cfg->approver_kind) {
            case 'user':
                $allowed = ($user->id === $cfg->approver_user_id);
                break;
            case 'department_head':
                $deptHead = optional(optional($requester->employmentDetail)->department)->head_of_department;
                $allowed = ($deptHead && $user->id === $deptHead);
                break;
            case 'role':
                $allowed = $user->hasRole($cfg->approver_value);
                break;
        }

        if (!$allowed) {
            throw new \Exception('Not authorized for this approval step.');
        }

        // 6) Create approval record
        OvertimeApproval::create([
            'overtime_id' => $overtimeRequest->id,
            'approver_id' => $user->id,
            'step_number' => $currStep,
            'action' => 'approved',
            'comment' => $comment,
            'acted_at' => now(),
        ]);

        // 7) Update overtime request based on step progression
        if ($currStep < $maxLevel) {
            // Move to next step
            $overtimeRequest->update([
                'current_step' => $currStep + 1,
                'status' => 'pending',
            ]);
        } else {
            // Final approval - deduct balance only on final approval
            $overtimeRequest->update(['status' => 'approved']);
        }
    }

    // ✅ Helper method for rejecting overtime requests bulk action
    private function rejectOvertimeRequest($overtimeRequest, $comment, $userId)
    {
        $user = User::find($userId);
        $requester = $overtimeRequest->user;
        $currStep = $overtimeRequest->current_step;
        $branchId = (int) optional($overtimeRequest->user->employmentDetail)->branch_id;
        $oldStatus = $overtimeRequest->status;

        // 1) Prevent self-approval
        if ($user->id === $requester->id) {
            throw new \Exception('Cannot reject own overtime request.');
        }

        // 2) Build the approval workflow
        $steps = OvertimeApproval::stepsForBranch($branchId);
        $reportingToId = optional($overtimeRequest->user->employmentDetail)->reporting_to;

        // 3) Special rule: If reporting_to exists at step 1
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                throw new \Exception('Only reporting manager can reject this request.');
            }

            // Direct rejection by reporting manager
            OvertimeApproval::create([
                'overtime_id' => $overtimeRequest->id,
                'approver_id' => $user->id,
                'step_number' => 1,
                'action' => 'rejected',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $overtimeRequest->update(['status' => 'rejected']);
            return;
        }

        // 4) Normal workflow authorization check
        $cfg = $steps->firstWhere('level', $currStep);
        if (!$cfg) {
            throw new \Exception('Approval step misconfigured.');
        }

        // 5) Authorization check
        $allowed = false;
        switch ($cfg->approver_kind) {
            case 'user':
                $allowed = ($user->id === $cfg->approver_user_id);
                break;
            case 'department_head':
                $deptHead = optional(optional($overtimeRequest->user->employmentDetail)->department)
                    ->head_of_department;
                $allowed = ($deptHead && $user->id === $deptHead);
                break;
            case 'role':
                $allowed = $user->hasRole($cfg->approver_value);
                break;
        }

        if (!$allowed) {
            throw new \Exception('Not authorized for this approval step.');
        }

        // 6) Create rejection record
        OvertimeApproval::create([
            'overtime_id' => $overtimeRequest->id,
            'approver_id' => $user->id,
            'step_number' => $currStep,
            'action' => 'rejected',
            'comment' => $comment,
            'acted_at' => now(),
        ]);

        // 7) Update overtime request status
        $overtimeRequest->update(['status' => 'rejected']);
    }
}
