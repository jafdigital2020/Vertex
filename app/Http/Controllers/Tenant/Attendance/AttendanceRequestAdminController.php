<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\ShiftAssignment;
use App\Helpers\PermissionHelper;
use App\Models\RequestAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\RequestAttendanceApproval;
use App\Http\Controllers\DataAccessController;

class AttendanceRequestAdminController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(14);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  = $accessData['userAttendances'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('request_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }

        if ($branch) {
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }
        if ($status) {
            $query->where('status', $status);
        }

        $userAttendances = $query->get();

        foreach ($userAttendances as $req) {
            $branchId = optional($req->user->employmentDetail)->branch_id;
            $steps = RequestAttendanceApproval::stepsForBranch($branchId);
            $req->total_steps     = $steps->count();

            $req->next_approvers = RequestAttendanceApproval::nextApproversFor($req, $steps);

            if ($latest = $req->latestApproval) {
                $approver = $latest->attendanceApprover;
                $pi       = optional($approver->personalInformation);

                $req->last_approver = trim("{$pi->first_name} {$pi->last_name}");

                $req->last_approver_type = optional(
                    optional($approver->employmentDetail)->branch
                )->name ?? 'Global';
            } else {
                $req->last_approver      = null;
                $req->last_approver_type = null;
            }
        }

        $html = view('tenant.attendance.attendance.adminrequest_filter', compact('userAttendances', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function adminRequestAttendanceIndex(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $today = Carbon::today()->toDateString();
        $permission = PermissionHelper::get(14);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments  = $accessData['departments']->get();
        $designations = $accessData['designations']->get();

        $userAttendances =  $accessData['userAttendances']->whereBetween('request_date', [
            now()->subDays(29)->startOfDay(),
            now()->endOfDay()
        ])
            ->with([
                'user.personalInformation',
                'user.employmentDetail.branch',
                'user.employmentDetail.department',
                'user.employmentDetail.designation',
                'latestApproval.attendanceApprover.personalInformation',
                'latestApproval.attendanceApprover.employmentDetail.branch'
            ])
            ->get();

        // Total Present for today
        $totalPresent = Attendance::whereDate('attendance_date', $today)
            ->whereIn('status', ['present', 'late'])
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            })
            ->count();

        // Total Late for today
        $totalLate = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'late')
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            })
            ->count();

        // Total Absent
        $totalAbsent = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'absent')
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            })
            ->count();

        // Get next approvers for each request
        foreach ($userAttendances as $req) {

            // Refresh Data
            $req->refresh();
            $req->user->refresh();
            $req->user->load('personalInformation', 'employmentDetail.branch', 'employmentDetail.department', 'employmentDetail.designation');

            $branchId = optional($req->user->employmentDetail)->branch_id;
            $steps = RequestAttendanceApproval::stepsForBranch($branchId);
            $req->total_steps     = $steps->count();

            // Reporting to ID
            $reportingToId = optional($req->user->employmentDetail)->reporting_to;

            if ($req->status === 'pending') {
                if ($req->current_step === 1 && $reportingToId) {
                    $manager = User::with('personalInformation')->find($reportingToId);
                    if ($manager && $manager->personalInformation) {
                        $managerName = trim("{$manager->personalInformation->first_name} {$manager->personalInformation->last_name}");
                        $req->next_approvers = [$managerName];
                    } else {
                        $req->next_approvers = ['Manager'];
                    }
                } else {
                    $req->next_approvers = RequestAttendanceApproval::nextApproversFor($req, $steps);
                }
            } else {
                $req->next_approvers = [];
            }
            if ($latest = $req->latestApproval) {
                $approver = $latest->approver ?? $latest->attendanceApprover;

                if ($approver && $approver->personalInformation) {
                    $pi = $approver->personalInformation;
                    $req->latest_approver = trim("{$pi->first_name} {$pi->last_name}");
                    $req->latest_approver_type = optional(
                        optional($approver->employmentDetail)->branch
                    )->name ?? 'Global';
                } else {
                    $req->latest_approver = 'Unknown user';
                    $req->latest_approver_type = 'Unknown';
                }
            } else {
                $req->latest_approver      = null;
                $req->latest_approver_type = null;
            }
        }

        // Api Route
        if ($request->wantsJson()) {
            return response()->json([
                'status'         => true,
                'userAttendance' => $userAttendances,
                'total_present'   => $totalPresent,
                'total_late' => $totalLate,
                'total_absent' => $totalAbsent,
            ]);
        }

        // Web Route
        return view('tenant.attendance.attendance.adminrequest', [
            'userAttendances' => $userAttendances,
            'totalPresent'   => $totalPresent,
            'totalLate' => $totalLate,
            'totalAbsent' => $totalAbsent,
            'permission' => $permission,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations
        ]);
    }

    // Approve Request Attendance
    public function requestAttendanceApproval(Request $request, RequestAttendance $req)
    {
        // 1) Validate payload
        $data = $request->validate([
            'action'  => 'required|in:approved,rejected,pending',
            'comment' => 'nullable|string',
        ]);

        $user      = $request->user();
        $currStep  = $req->current_step;
        $branchId  = (int) optional($req->user->employmentDetail)->branch_id;
        $oldStatus = $req->status;
        $requester = $req->user;
        $reportingToId = optional($req->user->employmentDetail)->reporting_to;

        // Prevent self-approval
        if ($user->id === $requester->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot take action on your own attendance request.',
            ], 403);
        }

        // Prevent spamming a second “REJECTED”
        if ($data['action'] === 'rejected' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Attendance request has already been rejected.',
            ], 400);
        }

        // 2) Build the approval workflow for this overtime-owner’s branch
        $steps = RequestAttendanceApproval::stepsForBranch($branchId);
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
            DB::transaction(function () use ($req, $data, $user, $newStatus, $maxLevel) {
                RequestAttendanceApproval::create([
                    'request_attendance_id' => $req->id,
                    'approver_id'          => $user->id,
                    'step_number'         => 1,
                    'action'              => strtolower($data['action']),
                    'comment'             => $data['comment'] ?? null,
                    'acted_at'            => Carbon::now(),
                ]);
                if ($data['action'] === 'approved') {
                    $req->update([
                        'current_step' => 1,
                        'status'       => 'approved',
                    ]);
                    // Attendance Update
                    $this->updateAttendanceForRequestAttendance($req);
                } else {
                    $req->update(['status' => $newStatus]);
                }
            });

            $req->refresh();
            return response()->json([
                'success'        => true,
                'message'        => 'Action recorded.',
                'data'           => $req,
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
                $deptHead = optional(optional($req->user->employmentDetail)->department)
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
            $req,
            $data,
            $user,
            $currStep,
            $steps,
            $newStatus,
            $oldStatus,
            $maxLevel
        ) {
            RequestAttendanceApproval::create([
                'request_attendance_id' => $req->id,
                'approver_id'          => $user->id,
                'step_number'         => $currStep,
                'action'              => strtolower($data['action']),
                'comment'             => $data['comment'] ?? null,
                'acted_at'         => Carbon::now(),
            ]);
            if ($data['action'] === 'approved') {
                if ($currStep < $maxLevel) {
                    $req->update([
                        'current_step' => $currStep + 1,
                        'status'       => 'pending',
                    ]);
                } else {
                    $req->update(['status' => 'approved']);

                    // Attendance Update
                    $this->updateAttendanceForRequestAttendance($req);
                }
            } else {
                // REJECTED or CHANGES_REQUESTED
                $req->update(['status' => $newStatus]);
            }
        });

        // 7) Return JSON
        $req->refresh();
        $next = RequestAttendanceApproval::nextApproversFor($req, $steps);

        return response()->json([
            'success'        => true,
            'message'        => 'Action recorded.',
            'data'           => $req,
            'next_approvers' => $next,
        ]);
    }

    // Request Attendance Approval
    public function requestAttendanceReject(Request $request, RequestAttendance $req)
    {
        $data = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $request->merge([
            'action'  => 'rejected',
            'comment' => $data['comment'],
        ]);

        return $this->requestAttendanceApproval($request, $req);
    }

    // Update Attendance
    protected function updateAttendanceForRequestAttendance(RequestAttendance $req)
    {
        $reqDate = Carbon::parse($req->request_date)->toDateString();
        $reqDateTime = Carbon::parse($req->request_date);
        $todayDay = strtolower($reqDateTime->format('D')); // mon, tue, wed, etc.

        $shiftAssignment = $this->findShiftAssignmentForDate($req->user_id, $reqDate, $todayDay);

        // Get shift details and maximum allowed hours
        $shift = null;
        $maxAllowedHours = null;
        $shiftId = null;
        $shiftAssignmentId = null;

        if ($shiftAssignment && $shiftAssignment->shift) {
            $shift = $shiftAssignment->shift;
            $maxAllowedHours = $shift->maximum_allowed_hours;
            $shiftId = $shift->id;
            $shiftAssignmentId = $shiftAssignment->id;

            Log::info('Found shift assignment for attendance request', [
                'request_id' => $req->id,
                'user_id' => $req->user_id,
                'date' => $reqDate,
                'shift_name' => $shift->name,
                'max_allowed_hours' => $maxAllowedHours,
                'shift_assignment_id' => $shiftAssignmentId
            ]);
        } else {
            Log::info('No shift assignment found for attendance request', [
                'request_id' => $req->id,
                'user_id' => $req->user_id,
                'date' => $reqDate,
                'day_of_week' => $todayDay
            ]);
        }

        // Calculate and cap work hours if shift has maximum allowed hours
        $originalRequestMinutes = $req->total_request_minutes ?? 0;
        $originalNightDiffMinutes = $req->total_request_nd_minutes ?? 0;
        $totalRequestMinutes = $originalRequestMinutes;
        $totalNightDiffMinutes = $originalNightDiffMinutes;

        // Apply maximum hours cap if shift is assigned and has a limit
        if ($maxAllowedHours && $maxAllowedHours > 0) {
            $maxAllowedMinutes = $maxAllowedHours * 60;

            // Calculate total requested time (regular + night diff)
            $totalRequestedTime = $originalRequestMinutes + $originalNightDiffMinutes;

            if ($totalRequestedTime > $maxAllowedMinutes) {
                // Cap the total time and distribute proportionally
                $ratio = $maxAllowedMinutes / $totalRequestedTime;

                $totalRequestMinutes = floor($originalRequestMinutes * $ratio);
                $totalNightDiffMinutes = floor($originalNightDiffMinutes * $ratio);

                // Ensure we don't exceed the cap
                if (($totalRequestMinutes + $totalNightDiffMinutes) > $maxAllowedMinutes) {
                    $excess = ($totalRequestMinutes + $totalNightDiffMinutes) - $maxAllowedMinutes;
                    $totalRequestMinutes = max(0, $totalRequestMinutes - $excess);
                }
            }
        }

        // Check if attendance already exists for the same date
        $attendance = Attendance::where('user_id', $req->user_id)
            ->where(DB::raw('DATE(attendance_date)'), $reqDate)
            ->first();

        if ($attendance) {
            Log::info('Updating existing attendance record from request.');

            // ✅ Update with capped values and shift information
            $attendance->update([
                'attendance_date' => $reqDate,
                'status' => 'Request',
                'date_time_in' => $req->request_date_in,
                'date_time_out' => $req->request_date_out,
                'total_work_minutes' => $totalRequestMinutes,
                'total_night_diff_minutes' => $totalNightDiffMinutes,
                'shift_id' => $shiftId, // ✅ Set shift if found
                'shift_assignment_id' => $shiftAssignmentId, // ✅ Set shift assignment if found
            ]);
        } else {
            // ✅ Create new attendance with capped values and shift information
            $newAttendance = Attendance::create([
                'user_id' => $req->user_id,
                'attendance_date' => $req->request_date,
                'date_time_in' => $req->request_date_in,
                'date_time_out' => $req->request_date_out,
                'total_work_minutes' => $totalRequestMinutes,
                'total_night_diff_minutes' => $totalNightDiffMinutes,
                'status' => 'Request',
                'shift_id' => $shiftId, // ✅ Set shift if found
                'shift_assignment_id' => $shiftAssignmentId, // ✅ Set shift assignment if found
            ]);
        }
    }

    // Find Shift Assignment for Date
    private function findShiftAssignmentForDate($userId, $date, $dayOfWeek)
    {
        try {
            $assignments = ShiftAssignment::with('shift')
                ->where('user_id', $userId)
                ->get()

                ->filter(function ($assignment) use ($date, $dayOfWeek) {
                    // Skip rest day assignments
                    if ($assignment->is_rest_day) {
                        return false;
                    }

                    // Skip excluded dates
                    if ($assignment->excluded_dates && in_array($date, $assignment->excluded_dates)) {
                        return false;
                    }

                    // Check recurring assignments
                    if ($assignment->type === 'recurring') {
                        $start = Carbon::parse($assignment->start_date);
                        $end = $assignment->end_date ? Carbon::parse($assignment->end_date) : Carbon::now()->addYear();
                        $requestDate = Carbon::parse($date);

                        return $requestDate->between($start, $end) &&
                            in_array($dayOfWeek, $assignment->days_of_week ?? []);
                    }

                    // Check custom assignments
                    if ($assignment->type === 'custom') {
                        return in_array($date, $assignment->custom_dates ?? []);
                    }

                    return false;
                })

                // Filter out assignments without shifts
                ->filter(function ($assignment) {
                    return $assignment->shift !== null;
                })

                // Sort by shift start time (prioritize earlier shifts)
                ->sortBy(function ($assignment) {
                    return $assignment->shift->start_time ?? '00:00:00';
                });

            // Return the first (earliest) matching assignment
            $foundAssignment = $assignments->first();

            if ($foundAssignment) {
                Log::info('Found shift assignment for date', [
                    'user_id' => $userId,
                    'date' => $date,
                    'day_of_week' => $dayOfWeek,
                    'assignment_id' => $foundAssignment->id,
                    'shift_id' => $foundAssignment->shift_id,
                    'shift_name' => $foundAssignment->shift->name,
                    'assignment_type' => $foundAssignment->type,
                    'max_allowed_hours' => $foundAssignment->shift->maximum_allowed_hours
                ]);
            } else {
                Log::info('No shift assignment found for date', [
                    'user_id' => $userId,
                    'date' => $date,
                    'day_of_week' => $dayOfWeek,
                    'total_assignments_checked' => $assignments->count()
                ]);
            }

            return $foundAssignment;
        } catch (\Exception $e) {
            Log::error('Error finding shift assignment for date', [
                'user_id' => $userId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    // Bulk Action
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'request_attendance_ids' => 'required|array|min:1',
            'request_attendance_ids.*' => 'exists:request_attendances,id',
            'comment' => 'nullable|string|max:500'
        ]);

        $action = $request->action;
        $requestAttendanceIds = $request->request_attendance_ids;
        $comment = $request->comment ?? "Bulk {$action} by admin";
        $userId = Auth::id();

        Log::info("Starting bulk action", [
            'action' => $action,
            'request_attendance_ids' => $requestAttendanceIds,
            'user_id' => $userId,
            'comment' => $comment
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errors = [];

            foreach ($requestAttendanceIds as $requestAttendanceId) {
                Log::info("Processing request attendance", [
                    'request_attendance_id' => $requestAttendanceId,
                    'action' => $action
                ]);

                try {
                    $requestAttendance = RequestAttendance::where('id', $requestAttendanceId)
                        ->first();

                    if (!$requestAttendance) {
                        $error = "Request attendance {$requestAttendanceId} not found";
                        $errors[] = $error;
                        Log::warning("Request attendance not found", [
                            'request_attendance_id' => $requestAttendanceId,
                        ]);
                        continue;
                    }

                    // Check if already processed
                    if ($requestAttendance->status !== 'pending') {
                        $error = "Request attendance {$requestAttendanceId} is already {$requestAttendance->status}";
                        $errors[] = $error;
                        Log::warning("Request attendance already processed", [
                            'request_attendance_id' => $requestAttendanceId,
                            'current_status' => $requestAttendance->status,
                            'attempted_action' => $action
                        ]);
                        continue;
                    }

                    // Process the action
                    if ($action === 'approve') {
                        $this->approveRequestAttendance($requestAttendance, $comment, $userId);
                        Log::info("Request attendance approved successfully", [
                            'request_attendance_id' => $requestAttendanceId,
                            'user_id' => $userId
                        ]);
                    } else {
                        $this->rejectRequestAttendance($requestAttendance, $comment, $userId);
                        Log::info("Request attendance rejected successfully", [
                            'request_attendance_id' => $requestAttendanceId,
                            'user_id' => $userId
                        ]);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $error = "Failed to {$action} request attendance {$requestAttendanceId}: " . $e->getMessage();
                    $errors[] = $error;
                    Log::error("Failed to process request attendance in bulk action", [
                        'request_attendance_id' => $requestAttendanceId,
                        'action' => $action,
                        'error_message' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString(),
                        'user_id' => $userId,
                    ]);
                }
            }

            DB::commit();

            $message = "Successfully {$action}d {$successCount} leave request(s).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " failed.";
            }

            Log::info("Bulk action completed", [
                'action' => $action,
                'total_processed' => count($requestAttendanceIds),
                'successful' => $successCount,
                'failed' => count($errors),
                'errors' => $errors
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $successCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Bulk action transaction failed", [
                'action' => $action,
                'request_attendance_ids' => $requestAttendanceIds,
                'user_id' => $userId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function approveRequestAttendance($requestAttendance, $comment, $userId)
    {
        $user = User::find($userId);
        $requester = $requestAttendance->user;

        $requester->refresh();
        $requester->load('employmentDetail');

        $currStep = $requestAttendance->current_step;
        $branchId = (int) optional($requester->employmentDetail)->branch_id;

        // 1) Prevent self-approval
        if ($user->id === $requester->id) {
            throw new \Exception('Cannot approve own request attendance.');
        }

        // 2) Build the approval workflow
        $steps = RequestAttendanceApproval::stepsForBranch($branchId);
        $maxLevel = $steps->max('level');

        // Get CURRENT reporting_to
        $reportingToId = optional($requester->employmentDetail)->reporting_to;

        // 3) Special rule: If reporting_to exists at step 1
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                throw new \Exception('Only the current reporting manager can approve this request.');
            }

            // Auto-final approve for reporting manager
            RequestAttendanceApproval::create([
                'request_attendance_id' => $requestAttendance->id,
                'approver_id' => $user->id,
                'step_number' => 1,
                'action' => 'approved',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $requestAttendance->update([
                'current_step' => 1,
                'status' => 'approved',
            ]);

            // ✅ FIXED: Call the correct method
            $this->updateAttendanceForRequestAttendance($requestAttendance);
            return;
        }

        // 4) Normal workflow
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
        RequestAttendanceApproval::create([
            'request_attendance_id' => $requestAttendance->id,
            'approver_id' => $user->id,
            'step_number' => $currStep,
            'action' => 'approved',
            'comment' => $comment,
            'acted_at' => now(),
        ]);

        // 7) Update request attendance based on step progression
        if ($currStep < $maxLevel) {
            // Move to next step
            $requestAttendance->update([
                'current_step' => $currStep + 1,
                'status' => 'pending',
            ]);
        } else {
            // Final approval
            $requestAttendance->update(['status' => 'approved']);

            $this->updateAttendanceForRequestAttendance($requestAttendance);
        }
    }

    private function rejectRequestAttendance($requestAttendance, $comment, $userId)
    {
        $user = User::find($userId);
        $requester = $requestAttendance->user;
        $currStep = $requestAttendance->current_step;
        $branchId = (int) optional($requestAttendance->user->employmentDetail)->branch_id;
        $oldStatus = $requestAttendance->status;

        // 1) Prevent self-approval
        if ($user->id === $requester->id) {
            throw new \Exception('Cannot reject own request attendance.');
        }

        // 2) Build the approval workflow
        $steps = RequestAttendanceApproval::stepsForBranch($branchId);
        $reportingToId = optional($requestAttendance->user->employmentDetail)->reporting_to;

        // 3) Special rule: If reporting_to exists at step 1
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                throw new \Exception('Only reporting manager can reject this request.');
            }

            // Direct rejection by reporting manager
            RequestAttendanceApproval::create([
                'request_attendance_id' => $requestAttendance->id,
                'approver_id' => $user->id,
                'step_number' => 1,
                'action' => 'rejected',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $requestAttendance->update(['status' => 'rejected']);
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
                $deptHead = optional(optional($requestAttendance->user->employmentDetail)->department)
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
        RequestAttendanceApproval::create([
            'request_attendance_id' => $requestAttendance->id,
            'approver_id' => $user->id,
            'step_number' => $currStep,
            'action' => 'rejected',
            'comment' => $comment,
            'acted_at' => now(),
        ]);

        // 7) Update request attendance status
        $requestAttendance->update(['status' => 'rejected']);
    }

}
