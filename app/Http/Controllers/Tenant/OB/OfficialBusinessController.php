<?php

namespace App\Http\Controllers\Tenant\OB;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Models\OfficialBusiness;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Database\QueryException;
use App\Models\OfficialBusinessApproval;
use App\Http\Controllers\DataAccessController;
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;
use Illuminate\Support\Facades\DB;

class OfficialBusinessController extends Controller
{
    use ResponseTimingTrait;     

    // ======= EMPLOYEE OFFICIAL BUSINESS CONTROLLER ======= //

    private function logOBError(
        string $errorType,
        string $message,
        Request $request,
        ?float $startTime = null,
        ?array $responseData = null
    ): void {
        try {
            $processingTime = null;
            $timingData = null;

            if ($responseData && isset($responseData['timing'])) {
                $timingData = $responseData['timing'];
                $processingTime = $timingData['server_processing_time_ms'] ?? null;
            } elseif ($startTime) {
                $timingData = $this->getTimingData($startTime);
                $processingTime = $timingData ? $timingData['server_processing_time_ms'] : null;
            }

            $errorMessage = sprintf("[%s] %s", $errorType, $message);

            // Get authenticated user
            $authUser = $this->authUser();

            // ===== DEBUG LOG START =====
            Log::debug('logPayrollError - Auth User & Tenant Info', [
                'auth_user_id' => $authUser?->id,
                'auth_user_tenant_id' => $authUser?->tenant_id,
                'tenant_loaded' => isset($authUser->tenant),
                'tenant_name_from_relation' => $authUser->tenant?->tenant_name ?? null,
            ]);

            $clientName = $authUser->tenant?->tenant_name ?? 'Unknown Tenant';
            $clientId   = $authUser->tenant?->id ?? null;

            Log::debug('logPayrollError - Sending to ErrorLogger', [
                'client_name' => $clientName,
                'client_id' => $clientId,
                'error_message' => $errorMessage,
            ]);
            // ===== DEBUG LOG END =====

            // Log to remote system
            ErrorLogger::logToRemoteSystem(
                $errorMessage,
                $clientName,
                $clientId,
                $timingData
            );

            // Local Laravel log
            Log::error($errorType, [
                'clean_message' => $message,
                'full_error' => $responseData['full_error'] ?? null,
                'user_id' => $authUser->id ?? null,
                'client_name' => $clientName,
                'client_id' => $clientId,
                'processing_time_ms' => $processingTime,
                'url' => $request->fullUrl(),
                'request_data' => $request->except(['password', 'token', 'api_key'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log error', [
                'original_error' => $message,
                'logging_error' => $e->getMessage()
            ]);
        }
    }


    public function authUser()
    {
        $user = null;
        
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        } else {
            $user = Auth::guard('web')->user();
        }
        
        // Load tenant relationship if user exists
        if ($user) {
            $user->load('tenant');
        }
        
        return $user;
    }


    private function hasPermission(string $action, int $moduleId = 48): bool
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
        $permission = PermissionHelper::get(48);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        $query  =  OfficialBusiness::where('user_id', $authUserId)
            ->orderBy('ob_date', 'desc');

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('ob_date', [$start, $end]);
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

        $obEntries = $query->get();

        $html = view('tenant.ob.ob-employee_filter', compact('obEntries', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    /**
     * Display the employee's official business requests and summary.
     *
     * Shows the authenticated employee's official business (OB) requests for the current year, including summary statistics (approved, pending, rejected) and permission information.
     *
     * @param \Illuminate\Http\Request $request
     * @queryParam dateRange string Optional. Date range in format "mm/dd/yyyy - mm/dd/yyyy". Example: "01/01/2025 - 12/31/2025"
     * @queryParam status string Optional. Filter OB requests by status (e.g., "pending", "approved", "rejected").
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "ob_date": "2025-11-01",
     *       "date_ob_in": "2025-11-01 08:00:00",
     *       "date_ob_out": "2025-11-01 17:00:00",
     *       "ob_break_minutes": 60,
     *       "total_ob_minutes": 480,
     *       "purpose": "Client meeting",
     *       "status": "pending"
     *     }
     *   ],
     *   "totalApprovedOB": 2,
     *   "totalPendingOB": 1,
     *   "totalRejectedOB": 0,
     *   "allData": []
     * }
     */
    public function employeeOBIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(48);
        $authUserTenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $authUserId = $authUser->id ?? null;

        $startOfYear = now()->startOfYear();
        $endOfYear   = now()->endOfYear();

        $obEntries = OfficialBusiness::where('user_id', $authUserId)
            ->whereBetween('ob_date', [$startOfYear, $endOfYear])
            ->orderBy('ob_date', 'desc')
            ->get();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total Approved OB for current month
        $totalApprovedOB = OfficialBusiness::where('user_id', $authUserId)
            ->where('status', 'approved')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->count();

        // Total Pending OB for current month
        $totalPendingOB = OfficialBusiness::where('user_id', $authUserId)
            ->where('status', 'pending')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->count();

        // Total Rejected OB for current month
        $totalRejectedOB = OfficialBusiness::where('user_id', $authUserId)
            ->where('status', 'rejected')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->count();

        // All OB Data
        $allOBData = OfficialBusiness::with([
            'user.employmentDetail',
            'user.personalInformation',
        ])
            ->where('user_id', $authUserId)
            ->orderBy('ob_date', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $obEntries,
                'totalApprovedOB' => $totalApprovedOB,
                'totalPendingOB' => $totalPendingOB,
                'totalRejectedOB' => $totalRejectedOB,
                'allData' => $allOBData,
            ]);
        }

        return view('tenant.ob.ob-employee', [
            'obEntries' => $obEntries,
            'totalApprovedOB' => $totalApprovedOB,
            'totalPendingOB' => $totalPendingOB,
            'totalRejectedOB' => $totalRejectedOB,
            'permission' => $permission
        ]);
    }

    // OB notification to approver
    public function sendOfficialBusinessNotificationToApprover($authUser, $ob_date)
    {
        $employment = $authUser->employmentDetail;
        $reportingToId = optional($employment)->reporting_to;
        $branchId = optional($employment)->branch_id;

        $requestor = trim(optional($authUser->personalInformation)->first_name . ' ' .
            optional($authUser->personalInformation)->last_name);

        $notifiedUser = null;

        if ($reportingToId) {
            $notifiedUser = User::find($reportingToId);
        } else {
            $steps = OfficialBusinessApproval::stepsForBranch($branchId);
            $firstStep = $steps->first();

            if ($firstStep) {

                if ($firstStep->approver_kind === 'department_head') {
                    $departmentHeadId = optional(optional($employment)->department)->head_of_department;
                    if ($departmentHeadId) {
                        $notifiedUser = User::find($departmentHeadId);
                    }
                } elseif ($firstStep->approver_kind === 'user') {
                    $approverUserId = $firstStep->approver_user_id;
                    if ($approverUserId) {
                        $notifiedUser = User::find($approverUserId);
                    }
                }
            }
        }
        if ($notifiedUser) {
            $message = "New official business request from {$requestor}: {$ob_date}. Pending your approval.";
            $notifiedUser->notify(new UserNotification($message));
        }
    }

    /**
     * Submit a new official business request.
     *
     * Allows an employee to file an official business (OB) request for a specific date, including clock-in/out times, break minutes, total minutes, purpose, and optional file attachment. Prevents duplicate entries for the same date.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam ob_date date required The date for the official business request. Example: "2025-11-01"
     * @bodyParam date_ob_in datetime required Official business clock-in date and time. Example: "2025-11-01 08:00:00"
     * @bodyParam date_ob_out datetime required Official business clock-out date and time. Must be after clock-in. Example: "2025-11-01 17:00:00"
     * @bodyParam ob_break_minutes integer Optional. Break minutes during official business. Example: 60
     * @bodyParam total_ob_minutes integer Optional. Total official business minutes. Example: 480
     * @bodyParam file_attachment file Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB).
     * @bodyParam purpose string required Purpose of the official business. Example: "Client meeting"
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Official business request submitted successfully."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You do not have the permission to create."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "You already have an official business entry for this date."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "The OB date must be the same as the date of your official business start time."
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "An unexpected error occurred. Please try again later."
     * }
     */
    public function employeeRequestOB(Request $request)
    {
        $startTime = microtime(true);
        // Validation
        $authUser = $this->authUser();
        $authUserTenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        // ✅ Skip session-based PermissionHelper for API calls (API uses token/auth)
        if (!$this->hasPermission('Create')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        // Validate input
        $request->validate([
            'ob_date'           => 'required|date',
            'date_ob_in'        => 'required|date',
            'date_ob_out'       => 'required|date|after_or_equal:date_ob_in',
            'ob_break_minutes'  => 'nullable|integer|min:0',
            'total_ob_minutes'  => 'nullable|numeric',
            'file_attachment'   => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'purpose'           => 'required|string|max:255',
        ]);

        // Ensure ob_date matches the date of date_ob_in
        if (Carbon::parse($request->ob_date)->format('Y-m-d') !== Carbon::parse($request->date_ob_in)->format('Y-m-d')) {
            return response()->json([
                'success' => false,
                'message' => 'The OB date must be the same as the date of your official business start time.',
            ], 422);
        }

        $authUserId = $authUser->id;

        // Check for existing OB on the same ob_date (i.e., same start date)
        $existing = OfficialBusiness::where('user_id', $authUserId)
            ->whereDate('ob_date', $request->ob_date)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an official business entry for this date.',
            ], 422);
        }

        // File upload
        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('ob_attachments', 'public');
        }

        try {
            $ob = OfficialBusiness::create([
                'user_id'           => $authUserId,
                'ob_date'           => $request->ob_date,
                'date_ob_in'        => $request->date_ob_in,
                'date_ob_out'       => $request->date_ob_out,
                'ob_break_minutes'  => $request->ob_break_minutes,
                'total_ob_minutes'  => $request->total_ob_minutes,
                'file_attachment'   => $filePath,
                'purpose'           => $request->purpose,
                'status'            => 'pending',
            ]);

            $this->sendOfficialBusinessNotificationToApprover($authUser, $request->ob_date);

            return response()->json(['success' => true, 'message' => 'Official business request submitted successfully.']);
        } catch (QueryException $e) {
            Log::info($e->getMessage());
            if ($e->getCode() == '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, we could not process your request. Please make sure your account is active and try again. If the problem persists, contact support.',
                ], 422);
            }

            DB::rollBack();

            $cleanMessage = "Error updating bulk attendance. Please try again later.";

            $this->logOBError(
                '[ERROR_UPDATING_BULK_ATTENDANCE]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }

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
            'module'     => 'Official Business',
            'action'     => 'add_official_business',
            'description' => 'Added official business, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => null,
            'new_data'   => json_encode($ob->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official business added successfully.',
            'data'    => $ob,
        ]);
    }

    /**
     * Edit an official business request (Employee).
     *
     * Allows an employee to update their own official business (OB) request, including date, clock-in/out times, break minutes, total minutes, purpose, and optional file attachment. Approved requests cannot be edited. Prevents duplicate entries for the same date.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the official business request to edit.
     * @bodyParam ob_date date required The date for the official business request. Example: "2025-11-01"
     * @bodyParam date_ob_in datetime required Official business clock-in date and time. Example: "2025-11-01 08:00:00"
     * @bodyParam date_ob_out datetime required Official business clock-out date and time. Must be after clock-in. Example: "2025-11-01 17:00:00"
     * @bodyParam ob_break_minutes integer Optional. Break minutes during official business. Example: 60
     * @bodyParam total_ob_minutes integer required Total official business minutes. Example: 480
     * @bodyParam file_attachment file Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB).
     * @bodyParam purpose string required Purpose of the official business. Example: "Client meeting"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Official Business request updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "ob_date": "2025-11-01",
     *     "date_ob_in": "2025-11-01 08:00:00",
     *     "date_ob_out": "2025-11-01 17:00:00",
     *     "ob_break_minutes": 60,
     *     "total_ob_minutes": 480,
     *     "purpose": "Client meeting",
     *     "file_attachment": "ob_attachments/xyz.pdf",
     *     "status": "pending"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "This official business entry is already approved and cannot be edited."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "You already have an official business entry for this date."
     * }
     */
    public function employeeUpdateOB(Request $request, $id)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(48);
        $authUserTenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!$this->hasPermission('Update')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $request->validate([
            'ob_date'      => 'required|date',
            'date_ob_in'         => 'required|date',
            'date_ob_out'        => 'required|date|after:date_ob_in',
            'ob_break_minutes'  => 'nullable|integer|min:0',
            'total_ob_minutes'   => 'required|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'purpose'            => 'required|string|max:255',
        ]);

        $ob = OfficialBusiness::findOrFail($id);

        if ($ob->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This official business entry is already approved and cannot be edited.',
            ], 403);
        }

        $authUserId = Auth::user()->id;

        // Prevent duplicate for same user & date, excluding this record
        $exists = OfficialBusiness::where('user_id', $authUserId)
            ->whereDate('ob_date', $request->ob_date)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an official business entry for this date.',
            ], 422);
        }

        // Save old data for logging
        $oldData = $ob->toArray();

        // Handle file upload if new file
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('ob_attachments', 'public');
            $ob->file_attachment = $filePath;
        }

        $ob->ob_date = $request->ob_date;
        $ob->date_ob_in = $request->date_ob_in;
        $ob->date_ob_out = $request->date_ob_out;
        $ob->ob_break_minutes = $request->ob_break_minutes;
        $ob->total_ob_minutes = $request->total_ob_minutes;
        $ob->purpose = $request->purpose;

        $ob->save();

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
            'module'     => 'Official Business',
            'action'     => 'Edit Official Business',
            'description' => 'Edited Employee Official Busienss, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($ob->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official Business request updated successfully.',
            'data'    => $ob,
        ]);
    }

    /**
     * Delete an official business request (Employee).
     *
     * Allows an employee to delete their own official business (OB) request. Approved requests cannot be deleted. Only the owner can delete their entry.
     *
     * @param int $id The ID of the official business request to delete.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Official business deleted successfully."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You do not have the permission to delete."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You can only delete your own official business entries."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "This official business entry is already approved and cannot be deleted."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Official business entry not found."
     * }
     */
    public function employeeDeleteOB($id)
    {
        $authUser = $this->authUser();
        $authUserTenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        // ✅ FIXED: Skip permission check for API requests
        if (!$this->hasPermission('Delete')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $ob = OfficialBusiness::findOrFail($id);

        // ✅ SECURITY: Always check ownership for both web and API
        if ($ob->user_id !== $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own official business entries.',
            ], 403);
        }

        if ($ob->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This official business entry is already approved and cannot be deleted.',
            ], 403);
        }

        $ob->delete();

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
            'module'     => 'Official Business',
            'action'     => 'Delete Official Business',
            'description' => 'Deleted Employee Official Busienss, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => json_encode($ob->toArray()),
            'new_data'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official business deleted successfully.',
        ]);
    }
}
