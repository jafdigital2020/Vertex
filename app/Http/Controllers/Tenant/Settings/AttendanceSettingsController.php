<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\AttendanceSettings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;
use Illuminate\Support\Facades\Log;


class AttendanceSettingsController extends Controller
{
    use ResponseTimingTrait;    

    private function logAttendanceSettingsError(
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

    public function attendanceSettingsIndex(Request $request)
    {
        $attendanceSettings = AttendanceSettings::first();

        // If the request expects JSON (API call)
        if ($request->wantsJson()) {
            return response()->json($attendanceSettings, 200);
        }

        // Else return the web view
        return view('tenant.settings.attendancesettings', [
            'attendanceSettings' => $attendanceSettings,
        ]);
    }

    public function attendanceSettingsCreate(Request $request)
    {  
        $permission = PermissionHelper::get(18);

        if (!in_array('Update', $permission) && !in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create or update.'
            ], 403);
        }
        $rules = [
            'geotagging_enabled' => 'boolean',
            'geofencing_enabled' => 'boolean',
            'allow_multiple_clock_ins' => 'boolean',
            'require_photo_capture' => 'boolean',
            'enable_break_hour_buttons' => 'boolean',
            'lunch_break_limit' => 'numeric|nullable',
            'coffee_break_limit' => 'numeric|nullable',
            'rest_day_time_in_allowed' => 'boolean',
            'enable_late_status_box' => 'boolean',
            'maximum_allowed_hours' => 'numeric|nullable',
            'time_display_format' => 'string|in:24,12',
            'grace_period' => 'numeric|nullable',
            'geofence_buffer' => 'numeric|nullable',
            'geofence_allowed_geotagging' => 'boolean',
        ];

        // Only validate the fields that are present in the request
        $data = $request->validate(array_intersect_key($rules, $request->all()));

        try {
            // Get existing record or create new one
            $settings = AttendanceSettings::first() ?? new AttendanceSettings();

            // Update only provided fields
            foreach ($data as $key => $value) {
                $settings->$key = $value;
            }

            $settings->save();

            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // ✨ Log the action
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Attendance Settings',
                'action'         => 'Update',
                'description'    => 'Updated attendance settings.',
                'affected_id'    => $settings->id,
                'old_data'       => null,
                'new_data'       => json_encode($settings->only(array_keys($data))),
            ]);

            return response()->json([
                'message' => __('Attendance settings updated successfully.'),
                'data' => $settings
            ]);
        } catch (\Exception $e) {

            $cleanMessage = "Failed to update attendance settings. Please try again later.";

            $this->logAttendanceSettingsError(
                '[FAILED_TO_UPDATE_ATTENDANCE_SETTINGS]',
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
}
