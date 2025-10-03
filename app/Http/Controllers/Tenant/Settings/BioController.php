<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Models\UserLog;
use App\Models\ZktecoDevice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BioController extends Controller
{
    public function biometricsIndex(Request $request)
    {
        $biometrics = ZktecoDevice::query()
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $biometrics
            ]);
        }

        return view('tenant.settings.biometrics', compact('biometrics'));
    }

    // BioTime Device Store
    public function biometricsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:zkteco_devices',
            'biotime_server_url' => 'required|regex:/^https?:\/\/.+/',
            'biotime_username' => 'required|string|max:255',
            'biotime_password' => 'required|string|max:255',
        ]);

        $biometric = ZktecoDevice::create([
            'name' => $request->name,
            'serial_number' => $request->serial_number,
            'connection_method' => 'biotime',
            'biotime_server_url' => $request->biotime_server_url,
            'biotime_username' => $request->biotime_username,
            'biotime_password' => $request->biotime_password,
            'device_type' => 'api',
            'status' => 'active',
        ]);

        // User Logs
        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Biometrics',
            'action'      => 'Create',
            'description' => 'Created new biometric device: ' . $biometric->name,
            'affected_id' => $biometric->id,
            'old_data'    => null,
            'new_data'    => json_encode($biometric),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'BioTime device added successfully.',
            'data' => $biometric
        ]);
    }

    // BioTime Device Update
    public function biometricsUpdate(Request $request, $id)
    {
        $biometric = ZktecoDevice::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:zkteco_devices,serial_number,' . $biometric->id,
            'biotime_server_url' => 'required|regex:/^https?:\/\/.+/',
            'biotime_username' => 'required|string|max:255',
            'biotime_password' => 'nullable|string|max:255',
        ]);

        $oldData = $biometric->toArray();

        $biometric->name = $request->name;
        $biometric->serial_number = $request->serial_number;
        $biometric->biotime_server_url = $request->biotime_server_url;
        $biometric->biotime_username = $request->biotime_username;
        $biometric->biotime_password = $request->biotime_password;
        $biometric->save();

        // User Logs
        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Biometrics',
            'action'      => 'Update',
            'description' => 'Updated biometric device: ' . $biometric->name,
            'affected_id' => $biometric->id,
            'old_data'    => json_encode($oldData),
            'new_data'    => json_encode($biometric),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'BioTime device updated successfully.',
            'data' => $biometric
        ]);
    }

    // BioTime Device Delete
    public function biometricsDestroy($id)
    {
        $biometric = ZktecoDevice::findOrFail($id);

        $oldData = $biometric->toArray();

        $biometric->delete();

        // User Logs
        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Biometrics',
            'action'      => 'Delete',
            'description' => 'Deleted biometric device: ' . $biometric->name,
            'affected_id' => $biometric->id,
            'old_data'    => json_encode($oldData),
            'new_data'    => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'BioTime device deleted successfully.'
        ]);
    }
}
