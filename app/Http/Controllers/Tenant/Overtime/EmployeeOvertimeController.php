<?php

namespace App\Http\Controllers\Tenant\Overtime;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Holiday;
use App\Models\UserLog;
use App\Models\Overtime;
use Illuminate\Http\Request;
use App\Models\HolidayException;
use App\Models\OvertimeApproval;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use App\Http\Controllers\DataAccessController;

class EmployeeOvertimeController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    private function hasPermission(string $action, int $moduleId = 45): bool
    {
        // For API (token) requests, skip session-based PermissionHelper and allow controller-level ownership checks
        if (request()->is('api/*') || request()->expectsJson()) {
            return true;
        }

        $permission = PermissionHelper::get($moduleId);
        return in_array($action, $permission);
    }

    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(45);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        $query  = Overtime::where('user_id', $authUserId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('overtime_date', 'desc');

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('overtime_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }

        if ($status) {
            $query->where('status', $status);
        }

        $overtimes = $query->get();
        $pendingRequests = $overtimes->where('status', 'pending')->count();
        $approvedRequests = $overtimes->where('status', 'approved')->count();
        $rejectedRequests = $overtimes->where('status', 'rejected')->count();

        $html = view('tenant.overtime.employeeovertime_filter', compact('overtimes', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
        ]);
    }

    /**
     * Display the employee's overtime requests and summary.
     *
     * Shows the authenticated employee's overtime requests for the last 30 days, including summary statistics and approver information.
     *
     * @param \Illuminate\Http\Request $request
     * @queryParam dateRange string Optional. Date range in format "mm/dd/yyyy - mm/dd/yyyy". Example: "11/01/2025 - 11/27/2025"
     * @queryParam status string Optional. Filter overtime requests by status (e.g., "pending", "approved", "rejected").
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "overtimes": [
     *       {
     *         "id": 1,
     *         "overtime_date": "2025-11-01",
     *         "date_ot_in": "2025-11-01 18:00:00",
     *         "date_ot_out": "2025-11-01 20:00:00",
     *         "total_ot_minutes": 120,
     *         "total_night_diff_minutes": 30,
     *         "reason": "Project deadline",
     *         "status": "pending",
     *         "lastApproverName": "Juan Dela Cruz",
     *         "lastApproverDept": "HR"
     *       }
     *     ],
     *     "pendingRequests": 2,
     *     "approvedRequests": 5,
     *     "rejectedRequests": 1,
     *     "permission": ["Create", "Update", "Delete"]
     *   },
     *   "allData": [],
     *   "summary": {
     *     "pendingRequests": 2,
     *     "approvedRequests": 5,
     *     "rejectedRequests": 1
     *   }
     * }
     */
    public function overtimeEmployeeIndex(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(45);
        $overtimes = Overtime::where('user_id', $authUserId)
            ->where('overtime_date', '>=', Carbon::today()->subDays(30)->toDateString())
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('overtime_date', 'desc')
            ->get();

        // Requests count
        $pendingRequests = $overtimes->where('status', 'pending')->count();
        $approvedRequests = $overtimes->where('status', 'approved')->count();
        $rejectedRequests = $overtimes->where('status', 'rejected')->count();

        // All Overtime Data
        $allOvertimedata = Overtime::with([
            'user.employmentDetail',
            'user.personalInformation',
        ])
            ->where('user_id', $authUserId)
            ->orderBy('overtime_date', 'desc')
            ->get();

        foreach ($overtimes as $lr) {
            if ($la = $lr->latestApproval) {
                $approver = $la->otApprover;
                $pi       = optional($approver->personalInformation);
                $dept     = optional($approver->employmentDetail->department)->department_name;

                $lr->lastApproverName = trim("{$pi->first_name} {$pi->last_name}");
                $lr->lastApproverDept = $dept ?: '—';
            } else {
                $lr->lastApproverName = null;
                $lr->lastApproverDept = null;
            }
        }

        // API
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'overtimes' => $overtimes,
                    'pendingRequests' => $pendingRequests,
                    'approvedRequests' => $approvedRequests,
                    'rejectedRequests' => $rejectedRequests,
                    'permission' => $permission
                ],
                'allData' => $allOvertimedata,
                'summary' => [
                    'pendingRequests' => $pendingRequests,
                    'approvedRequests' => $approvedRequests,
                    'rejectedRequests' => $rejectedRequests,
                ],
            ]);
        }

        // Web
        return view('tenant.overtime.employeeovertime', [
            'overtimes' => $overtimes,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'permission' => $permission
        ]);
    }

    // Manual User Notification to Approver
    public function sendOvertimeNotificationToApprover($authUser, $overtimeDate)
    {
        $employment = $authUser->employmentDetail;
        $branchId   = $employment->branch_id ?? null;
        $reportingToId = $employment->reporting_to ?? null;
        $departmentHead = $employment->department->head_of_department ?? null;

        $requestor = trim($authUser->personalInformation->first_name . ' ' . $authUser->personalInformation->last_name);

        $steps = OvertimeApproval::stepsForBranch($branchId);
        $firstStep = $steps->first();

        $notifiedUser = null;
        if ($reportingToId && $firstStep && $firstStep->level == 1) {
            $notifiedUser = User::find($reportingToId);
        }

        if (!$notifiedUser && $firstStep) {

            if ($firstStep->approver_kind === 'department_head' && $departmentHead) {
                $notifiedUser = User::find($departmentHead);
            }

            if ($firstStep->approver_kind === 'user') {
                $userApproverId = $firstStep->approver_user_id ?? null;
                $notifiedUser = User::find($userApproverId);
            }
        }

        if ($notifiedUser) {
            $notifiedUser->notify(
                new UserNotification(
                    "New overtime request from {$requestor}: {$overtimeDate}. Pending your approval."
                )
            );
        }
    }

    /**
     * Submit a manual overtime request.
     *
     * Allows an employee to manually file an overtime request for a specific date, including clock-in/out times, total minutes, night differential, reason, and optional file attachment.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam overtime_date date required The date for the overtime request. Example: "2025-11-01"
     * @bodyParam date_ot_in datetime required Overtime clock-in date and time. Example: "2025-11-01 18:00:00"
     * @bodyParam date_ot_out datetime required Overtime clock-out date and time. Must be after clock-in. Example: "2025-11-01 20:00:00"
     * @bodyParam total_ot_minutes integer Optional. Total overtime minutes. Example: 120
     * @bodyParam total_night_diff_minutes integer Optional. Night differential minutes. Example: 30
     * @bodyParam file_attachment file Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB).
     * @bodyParam offset_date date Optional. Offset date for overtime. Example: "2025-11-02"
     * @bodyParam reason string required Reason for the overtime request. Example: "Project deadline"
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Overtime added successfully.",
     *   "data": {
     *     "id": 1,
     *     "overtime_date": "2025-11-01",
     *     "date_ot_in": "2025-11-01 18:00:00",
     *     "date_ot_out": "2025-11-01 20:00:00",
     *     "total_ot_minutes": 120,
     *     "total_night_diff_minutes": 30,
     *     "file_attachment": "overtime_attachments/xyz.pdf",
     *     "offset_date": "2025-11-02",
     *     "reason": "Project deadline",
     *     "status": "pending"
     *   }
     * }
     * @response 403 {
     *   "status": "error",
     *   "message": "You do not have the permission to create."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "You already have an overtime entry for this date."
     * }
     */
    public function overtimeEmployeeManualCreate(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;

        if (!$this->hasPermission('Create')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }
        // Validation
        $request->validate([
            'overtime_date'      => 'required|date',
            'date_ot_in'         => 'required|date',
            'date_ot_out'        => 'required|date|after_or_equal:date_ot_in',
            'total_ot_minutes'   => 'nullable|numeric',
            'total_night_diff_minutes'   => 'nullable|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // max 5MB
            'offset_date'        => 'nullable|date',
            'reason'             => 'required|string|max:500',
        ]);

        // Check if an overtime exists for this user & date
        $existing = Overtime::where('user_id', $authUserId)
            ->whereDate('overtime_date', $request->overtime_date)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an overtime entry for this date.',
            ], 422);
        }

        // File upload
        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('overtime_attachments', 'public');
        }

        $overtime = Overtime::create([
            'user_id'           => $authUserId,
            'overtime_date'     => $request->overtime_date,
            'date_ot_in'        => $request->date_ot_in,
            'date_ot_out'       => $request->date_ot_out,
            'total_ot_minutes'  => $request->total_ot_minutes,
            'total_night_diff_minutes'  => $request->total_night_diff_minutes ?? 0,
            'file_attachment'   => $filePath,
            'reason'            => $request->reason,
            'offset_date'       => $request->offset_date,
            'status'            => 'pending',
            'ot_login_type'    => 'manual',
        ]);

        $this->sendOvertimeNotificationToApprover($authUser, $request->overtime_date);

        // Logging Start
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
            'action'     => 'add_overtime',
            'description' => 'Added manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => null,
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime added successfully.',
            'data'    => $overtime,
        ]);
    }

    /**
     * Edit a manual overtime request.
     *
     * Allows an employee to update an existing manual overtime request, including clock-in/out times, total minutes, night differential, reason, offset date, and optional file attachment. Approved requests cannot be edited.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the overtime request to edit.
     * @bodyParam overtime_date date required The date for the overtime request. Example: "2025-11-01"
     * @bodyParam date_ot_in datetime required Overtime clock-in date and time. Example: "2025-11-01 18:00:00"
     * @bodyParam date_ot_out datetime required Overtime clock-out date and time. Must be after clock-in. Example: "2025-11-01 20:00:00"
     * @bodyParam total_ot_minutes integer required Total overtime minutes. Example: 120
     * @bodyParam total_night_diff_minutes integer Optional. Night differential minutes. Example: 30
     * @bodyParam file_attachment file Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB).
     * @bodyParam offset_date date Optional. Offset date for overtime. Example: "2025-11-02"
     * @bodyParam reason string required Reason for the overtime request. Example: "Project deadline"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Overtime updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "overtime_date": "2025-11-01",
     *     "date_ot_in": "2025-11-01 18:00:00",
     *     "date_ot_out": "2025-11-01 20:00:00",
     *     "total_ot_minutes": 120,
     *     "total_night_diff_minutes": 30,
     *     "file_attachment": "overtime_attachments/xyz.pdf",
     *     "offset_date": "2025-11-02",
     *     "reason": "Project deadline",
     *     "status": "pending"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "This overtime entry is already approved and cannot be edited."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "You already have an overtime entry for this date."
     * }
     */
    public function overtimeEmployeeManualUpdate(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;

        if (!$this->hasPermission('Update')) {
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
            'reason'             => 'required|string|max:500',
        ]);

        $overtime = Overtime::findOrFail($id);

        if ($overtime->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This overtime entry is already approved and cannot be edited.',
            ], 403);
        }

        // Prevent duplicate for same user & date, excluding this record
        $exists = Overtime::where('user_id', $authUserId)
            ->whereDate('overtime_date', $request->overtime_date)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an overtime entry for this date.',
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
     * Delete a manual overtime request.
     *
     * Allows an employee to delete their own manual overtime request. Approved requests cannot be deleted.
     *
     * @param int $id The ID of the overtime request to delete.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Overtime deleted successfully."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You do not have the permission to delete."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "This overtime entry is already approved and cannot be deleted."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Overtime request not found."
     * }
     */
    public function overtimeEmployeeManualDelete($id)
    {

        if (!$this->hasPermission('Delete')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $overtime = Overtime::findOrFail($id);

        if ($overtime->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This overtime entry is already approved and cannot be deleted.',
            ], 403);
        }

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
            'old_data'   => json_encode($overtime->toArray()),
            'new_data'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime deleted successfully.',
        ]);
    }

    /**
     * Clock in for overtime.
     *
     * Allows an employee to clock in for overtime for the current date. Supports file attachment, offset date, and reason. Prevents duplicate clock-in for the same date.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam file_attachment file Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB).
     * @bodyParam offset_date date Optional. Offset date for overtime. Example: "2025-11-02"
     * @bodyParam reason string required Reason for the overtime request. Example: "Project deadline"
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Overtime clocked in successfully.",
     *   "data": {
     *     "id": 1,
     *     "overtime_date": "2025-11-01",
     *     "date_ot_in": "2025-11-01 18:00:00",
     *     "status": "pending",
     *     "file_attachment": "overtime_attachments/xyz.pdf",
     *     "offset_date": "2025-11-02",
     *     "reason": "Project deadline",
     *     "is_holiday": false,
     *     "holiday_id": null
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "You already have an overtime entry for this date."
     * }
     */
    public function overtimeEmployeeClockIn(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $todayMonthDay = Carbon::today()->format('m-d');
        $today = Carbon::today()->toDateString();
        $now = now();

        $request->validate([
            'file_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'offset_date'     => 'nullable|date',
            'reason'          => 'required|string|max:500',
        ]);

        // Check if an overtime exists for this user & date
        $existing = Overtime::where('user_id', $authUserId)
            ->whereDate('overtime_date', $now->toDateString())
            ->whereNull('date_ot_out')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an overtime entry for this date.',
            ], 422);
        }

        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('overtime_attachments', 'public');
        }

        // Holiday Check
        $exception = HolidayException::where('user_id', $authUserId)
            ->where('status', 'active')
            ->whereHas('holiday', function ($q) use ($today, $todayMonthDay) {
                $q->where(function ($q1) use ($today, $todayMonthDay) {
                    $q1->where('recurring', true)
                        ->where('month_day', $todayMonthDay);
                })->orWhere(function ($q2) use ($today) {
                    $q2->where('recurring', false)
                        ->where('date', $today);
                });
            })
            ->first();

        if ($exception) {
            $isHoliday = false;
            $holidayId = null;
        } else {
            $holiday = Holiday::where('status', 'active')
                ->where(function ($q) use ($today, $todayMonthDay) {
                    $q->where(function ($q2) use ($todayMonthDay) {
                        $q2->where('recurring', true)
                            ->where('month_day', $todayMonthDay);
                    })
                        ->orWhere(function ($q3) use ($today) {
                            $q3->where('recurring', false)
                                ->where('date', $today);
                        });
                })
                ->first();

            $isHoliday = (bool) $holiday;
            $holidayId = $holiday?->id;
        }

        $overtime = Overtime::create([
            'user_id'           => $authUserId,
            'overtime_date'     => $now->toDateString(),
            'date_ot_in'        => $now->toDateTimeString(),
            'status'            => 'pending',
            'ot_login_type'     => 'OT Buttons',
            'file_attachment'   => $filePath,
            'reason'           => $request->reason,
            'offset_date'      => $request->offset_date,
            'is_holiday'       => $isHoliday,
            'holiday_id'       => $holidayId,
        ]);

        // Logging Start
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
            'action'     => 'clock_in_overtime',
            'description' => 'Clocked in for overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => null,
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime clocked in successfully.',
            'data'    => $overtime,
        ]);
    }

    /**
     * Clock out for overtime.
     *
     * Allows an employee to clock out from their open overtime entry for the current date. Calculates total overtime and night differential minutes.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Overtime clock-out successful.",
     *   "data": {
     *     "id": 1,
     *     "overtime_date": "2025-11-01",
     *     "date_ot_in": "2025-11-01 18:00:00",
     *     "date_ot_out": "2025-11-01 20:00:00",
     *     "total_ot_minutes": 120,
     *     "total_night_diff_minutes": 30,
     *     "status": "pending"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "No open overtime entry to clock out from."
     * }
     */
    public function overtimeEmployeeClockOut(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $now = now();

        // Find the latest open overtime entry for this user (clocked in, not clocked out)
        $overtime = Overtime::where('user_id', $authUserId)
            ->whereNull('date_ot_out')
            ->orderByDesc('date_ot_in')
            ->first();

        if (!$overtime) {
            return response()->json([
                'success' => false,
                'message' => 'No open overtime entry to clock out from.',
            ], 404);
        }


        $overtime->date_ot_out = $now->toDateTimeString();

        $in = \Carbon\Carbon::parse($overtime->date_ot_in);
        $out = \Carbon\Carbon::parse($overtime->date_ot_out);

        // Step 1: total minutes
        $totalMinutes = max(0, $in->diffInMinutes($out));

        // Step 2: Night diff window
        $ndStart = $in->copy()->setTime(22, 0, 0);
        $ndEnd = $ndStart->copy()->addDay()->setTime(6, 0, 0);

        // Get night diff minutes
        $nightStart = ($in > $ndStart) ? $in : $ndStart;
        $nightEnd = ($out < $ndEnd) ? $out : $ndEnd;
        $ndMinutes = ($nightStart < $nightEnd) ? $nightStart->diffInMinutes($nightEnd) : 0;

        // Step 3: Regular OT = total - night diff (never negative)
        $otMinutes = max(0, $totalMinutes - $ndMinutes);

        $overtime->total_ot_minutes = $otMinutes;
        $overtime->total_night_diff_minutes = $ndMinutes;

        $overtime->save();

        // Logging Start
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
            'action'     => 'clock_out_overtime',
            'description' => 'Clocked out from overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => null,
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime clock-out successful.',
            'data'    => $overtime
        ]);
    }
}
