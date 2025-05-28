<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Models\AttendanceSettings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AttendanceSettingsController extends Controller
{
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
            return response()->json([
                'message' => __('Failed to update attendance settings.'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
