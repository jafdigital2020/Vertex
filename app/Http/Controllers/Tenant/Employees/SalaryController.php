<?php

namespace App\Http\Controllers\Tenant\Employees;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;
use Illuminate\Support\Facades\DB;


class SalaryController extends Controller
{
    use ResponseTimingTrait;        

 private function logSalaryError(
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


      public function salaryRecordFilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(53);
        $dataAccessController = new DataAccessController();
        $userID = $request->input('userID');
        $dateRange = $request->input('dateRange');
        $salaryType = $request->input('salaryType');
        $status = $request->input('status');

        $user = User::with('salaryRecord')->findOrFail( $userID);

        $query = $user->salaryRecord()
            ->orderByDesc('is_active')
            ->orderByDesc('effective_date');

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();
                $query->whereBetween('effective_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($salaryType) {
            $query->where('salary_type', $salaryType);
        }

        if (!is_null($status)) {
            $query->where('is_active', (int) $status);
        }

        $salaryRecords = $query->get();

        $html = view('tenant.employee.salary_filter', compact( 'user','salaryRecords', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }


    public function salaryRecordIndex($userId)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(53);
        $user = User::with('salaryRecord')->findOrFail($userId);

        $activeSalary = $user->salaryRecord()->where('is_active', true)->first();
        $salaryHistory = $user->salaryRecord()->orderByDesc('created_at')->get();
        $salaryRecords = $user->salaryRecord()
            ->orderByDesc('is_active')
            ->orderByDesc('effective_date')
            ->get();

        // For API response
        if (request()->expectsJson()) {
            return response()->json([
                'user' => $user->only(['id', 'name']),
                'active_salary' => $activeSalary,
                'salary_history' => $salaryHistory
            ]);
        }

        // For web view
        return view('tenant.employee.salary', compact('user', 'permission','activeSalary', 'salaryHistory', 'salaryRecords'));
    }

    public function salaryRecord(Request $request, $userId)
    {
        $permission = PermissionHelper::get(53);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }

        $validated = $request->validate([
            'salary_type' => 'required|in:monthly_fixed,daily_rate,hourly_rate',
            'basic_salary' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $effectiveDate = Carbon::parse($validated['effective_date']);
        $today = Carbon::today();

        // Check for duplicate effective_date
        $duplicate = SalaryRecord::where('user_id', $userId)
            ->whereDate('effective_date', $effectiveDate)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'A salary record with the same effective date already exists.'
            ], 422);
        }

        $isActive = false;

        if ($effectiveDate->lte($today)) {
            // Check if this is the latest backdated or today record
            $latestValid = SalaryRecord::where('user_id', $userId)
                ->where('effective_date', '<=', $today)
                ->orderByDesc('effective_date')
                ->first();

            if (!$latestValid || $effectiveDate->gte(Carbon::parse($latestValid->effective_date))) {
                // Deactivate the currently active record
                SalaryRecord::where('user_id', $userId)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                $isActive = true;
            }
        }

        // Logging Start
        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $salaryRecord = SalaryRecord::create([
            'user_id' => $userId,
            'basic_salary' => $validated['basic_salary'],
            'salary_type' => $validated['salary_type'],
            'effective_date' => $validated['effective_date'],
            'remarks' => $validated['remarks'],
            'is_active' => $isActive,
            'created_by_id' => Auth::user()->id,
            'created_by_type' => get_class(Auth::user()),
        ]);

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Salary Records',
            'action' => 'Create',
            'description' => 'Created salary record for user ID: ' . $userId,
            'affected_id' => $salaryRecord->id,
            'old_data' => null,
            'new_data' => json_encode($salaryRecord->toArray()),
        ]);

        return response()->json([
            'message' => 'Salary record added successfully!',
            'data' => $salaryRecord
        ], 201);
    }

    // Salary Record Update
    public function salaryRecordUpdate(Request $request, $userId, $salaryId)
    {
        
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(53);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to Update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'basic_salary' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'remarks' => 'nullable|string|max:255',
            'salary_type' => 'required|in:monthly_fixed,daily_rate,hourly_rate',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $salary = SalaryRecord::where('id', $salaryId)
                ->where('user_id', $userId)
                ->first();

            if (!$salary) {
                return response()->json([
                    'message' => 'Salary record not found.'
                ], 404);
            }

            $today = Carbon::today();
            $effectiveDate = Carbon::parse($request->effective_date);
            $wasActive = $salary->is_active;

            // Check for duplicate effective_date
            $duplicate = SalaryRecord::where('user_id', $userId)
                ->where('effective_date', $effectiveDate)
                ->where('id', '!=', $salaryId) // Ignore the one being edited
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'message' => 'A salary record with the same effective date already exists.'
                ], 422);
            }

            // **NEW**: Check if there is at least one active or backdated record
            $futureCheck = SalaryRecord::where('user_id', $userId)
                ->where('id', '!=', $salaryId)
                ->where('effective_date', '<=', $today)
                ->exists();

            // Include the new effective date you're updating to
            if (!$futureCheck && $effectiveDate->gt($today)) {
                return response()->json([
                    'message' => 'At least one active or backdated salary record must exist.'
                ], 422);
            }

            // Update the record
            $salary->update([
                'basic_salary' => $request->basic_salary,
                'salary_type' => $request->salary_type,
                'effective_date' => $effectiveDate,
                'remarks' => $request->remarks,
            ]);

            // Now determine the record that SHOULD be active (latest on or before today)
            $activeRecord = SalaryRecord::where('user_id', $userId)
                ->where('effective_date', '<=', $today)
                ->orderByDesc('effective_date')
                ->first();

            if ($activeRecord) {
                // Set all to inactive EXCEPT the one that should remain active
                SalaryRecord::where('user_id', $userId)
                    ->where('id', '!=', $activeRecord->id)
                    ->update(['is_active' => 0]);

                $activeRecord->update(['is_active' => 1]);
            }

            // Logging Start
            $empId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $empId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id' => $empId,
                'global_user_id' => $globalUserId,
                'module' => 'Salary Record',
                'action' => 'Update',
                'description' => 'Updated salary record',
                'affected_id' => $salary->id,
                'old_data' => json_encode($salary->getOriginal()),
                'new_data' => json_encode($salary->toArray()),
            ]);

            return response()->json([
                'message' => 'Salary updated successfully.',
                'data' => $salary
            ], 200);
        } catch (\Exception $e) {
            Log::error('Salary update failed', ['error' => $e->getMessage()]);

            DB::rollBack();

            $cleanMessage = "Salary update failed. Please try again later.";

            $this->logSalaryError(
                '[ERROR_UPDATING_SALARY_RECORD]',
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
    }

    // Salary Record Delete
    public function salaryRecordDelete($userId, $salaryId)
    {
        $permission = PermissionHelper::get(53);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to Delete.'
            ], 403);
        }

        // Check if the user exists
        $user = User::findOrFail($userId);

        // Get the salary record
        $salary = SalaryRecord::where('user_id', $userId)->where('id', $salaryId)->first();

        if (!$salary) {
            return response()->json([
                'message' => 'Salary record not found.'
            ], 404);
        }

        // For Logging
        $oldData = $salary->toArray();

        // Prevent deleting active salary record
        if ($salary->is_active) {
            return response()->json([
                'message' => 'Cannot delete an active salary record.'
            ], 422);
        }

        $salary->delete();

        // Logging Start
        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Salary Record',
            'action' => 'Delete',
            'description' => 'Deleted salary record',
            'affected_id' => $salaryId,
            'old_data' => json_encode($oldData),
            'new_data' => null,
        ]);

        return response()->json([
            'message' => 'Salary record deleted successfully.'
        ]);
    }
}
