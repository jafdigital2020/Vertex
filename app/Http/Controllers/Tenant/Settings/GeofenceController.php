<?php

namespace App\Http\Controllers\Tenant\Settings;

use Exception;
use App\Models\User;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Geofence;
use App\Models\Department;
use App\Models\Designation;
use App\Models\GeofenceUser;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;

class GeofenceController extends Controller
{
    use ResponseTimingTrait;
    private function logGeofenceError(
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


    public function locationFilter(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(18);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $geofences  = $accessData['geofences']->get();

        $html = view('tenant.settings.geofence.location', compact('geofences', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function geofenceIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(18);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $geofences = $accessData['geofences']->get();
        $activeGeofences = Geofence::where('status', 'active')->get();

        $assignedGeofences = $accessData['geofenceUsers']->get();


        $employees = $accessData['employees']->with([
            'personalInformation',
            'employmentDetail.branch',
            'role',
            'designation',
        ])
            ->whereHas('employmentDetail', function ($query) {
                $query->where('status', 'active');
            })
            ->get();

        // Check if the request expects JSON (API call)
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Geofence settings retrieved successfully.',
                'branches' => $branches,
                'geofences' => $geofences,
                'activeGeofences' => $activeGeofences,
                'departments' => $departments,
                'designations' => $designations,
                'employees' => $employees,
                'assignedGeofences' => $assignedGeofences,
            ], 200);
        }

        // Else return the web view
        return view('tenant.settings.geofence.geofence', [
            'branches' => $branches,
            'geofences' => $geofences,
            'activeGeofences' => $activeGeofences,
            'departments' => $departments,
            'designations' => $designations,
            'employees' => $employees,
            'assignedGeofences' => $assignedGeofences,
            'permission' => $permission
        ]);
    }

    public function geofenceStore(Request $request)
    {
        
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(18);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }
        $validated = $request->validate([
            'geofence_name' => 'required|string|max:255',
            'geofence_address' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius' => 'required|integer|min:1',
            'branch_id' => 'nullable|exists:branches,id',
            'geofence_expiration_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            $geofence = Geofence::create([
                'geofence_name' => $validated['geofence_name'],
                'geofence_address' => $validated['geofence_address'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'geofence_radius' => $validated['geofence_radius'],
                'branch_id' => $validated['branch_id'] ?? null,
                'status' => 'active',
                'created_by_id' => Auth::user()->id,
                'created_by_type' => get_class(Auth::user()),
            ]);

            // 🔐 Logging Start
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
                'module'         => 'Geofence Management',
                'action'         => 'Create',
                'description'    => 'Created new geofence: ' . $geofence->geofence_name,
                'affected_id'    => $geofence->id,
                'old_data'       => null,
                'new_data'       => json_encode($geofence->toArray()),
            ]);

            return response()->json([
                'message' => 'Geofence created successfully.',
                'data' => $geofence
            ], 201);
        } catch (\Exception $e) {
            $cleanMessage = "Failed to create geofence. Please try again later.";

            $this->logGeofenceError(
                '[ERROR_CREATING_GEOFENCE]',
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

    public function geofenceUpdate(Request $request, $id)
    {
        
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(18);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }

        try {
            // Validation
            $request->validate([
                'geofence_name'     => 'required|string|max:255',
                'geofence_address'  => 'required|string|max:255',
                'latitude'          => 'required|numeric|between:-90,90',
                'longitude'         => 'required|numeric|between:-180,180',
                'geofence_radius'   => 'required|integer|min:1',
                'branch_id'         => 'nullable|exists:branches,id',
                'expiration_date'   => 'nullable|date',
            ]);

            // Find the geofence and update it
            $geofence = Geofence::findOrFail($id);
            $oldData = $geofence->toArray();

            $geofence->update([
                'geofence_name'     => $request->geofence_name,
                'geofence_address'  => $request->geofence_address,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'geofence_radius'   => $request->geofence_radius,
                'branch_id'         => $request->branch_id,
                'expiration_date'   => $request->expiration_date,
                'status'            => $request->status ?? 'active',
                'updated_by_type'   => Auth::guard('web')->check() ? 'App\Models\User' : 'App\Models\GlobalUser',
                'updated_by_id'     => Auth::id(),
            ]);

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Geofence Management',
                'action'         => 'Update',
                'description'    => 'Updated geofence: ' . $geofence->geofence_name,
                'affected_id'    => $geofence->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => json_encode($geofence->toArray()),
            ]);

            // Return success response
            return response()->json([
                'message' => 'Geofence updated successfully.',
                'data'    => $geofence
            ], 200);
        } catch (ValidationException $e) {
            // Log the validation errors
            Log::error('Geofence Update Validation Error:', [
                'error'   => $e->getMessage(),
                'errors'  => $e->errors(),
                'request' => $request->all(),
            ]);

            // Handle case when validation fails
            return response()->json([
                'message' => 'Please check the form for errors.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            // Log the ModelNotFoundException
            Log::error('Geofence Update Error - Geofence Not Found:', [
                'geofence_id' => $id,
                'error'       => $e->getMessage(),
            ]);

            // Handle case when Geofence is not found
            return response()->json([
                'message' => 'Geofence not found.',
            ], 404);
        } catch (Exception $e) {
            // Log the unexpected error
            Log::error('Geofence Update Unexpected Error:', [
                'error'   => $e->getMessage(),
                'request' => $request->all(),
            ]);

            $cleanMessage = "Geofence Update Unexpected Error. Please try again later.";

            $this->logGeofenceError(
                '[ERROR_UPDATING_GEOFENCE]',
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

    public function geofenceDelete(Request $request, $id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(18);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete.'
            ], 403);
        }

        try {
            $geofence = Geofence::findOrFail($id);
            $geofence->delete();

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Geofence Management',
                'action'         => 'Delete',
                'description'    => 'Deleted geofence: ' . $geofence->geofence_name,
                'affected_id'    => $geofence->id,
                'old_data'       => json_encode($geofence->toArray()),
                'new_data'       => null,
            ]);

            return response()->json([
                'message' => 'Geofence deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Geofence not found.',
            ], 404);
        } catch (Exception $e) {
            $cleanMessage = "Failed to delete geofence. Please try again later.";

            $this->logGeofenceError(
                '[ERROR_DELETING_GEOFENCE]',
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

    // =================== USER GEOFENCE =================== //

    public function userFilter(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(18);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $type = $request->input('type');


        $query  = $accessData['geofenceUsers'];


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
        if ($type) {
            $query->where('assignment_type', $type);
        }

        $assignedGeofences = $query->get();
        $html = view('tenant.settings.geofence.users', compact('assignedGeofences', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function geofenceUserAssign(Request $request)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(18);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }

        $validated = $request->validate([
            'assignments' => 'required|array|min:1',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.geofence_id' => 'required|exists:geofences,id',
            'assignments.*.assignment_type' => 'required|in:manual,exempt',
        ]);

        // 🔐 Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $insertedAssignments = [];
        $submittedData = [];

        // Log validated data
        Log::info('Validated assignments data: ', $validated);

        try {
            foreach ($validated['assignments'] as $assignment) {

                $user = User::findOrFail($assignment['user_id']);
                $geofence = Geofence::findOrFail($assignment['geofence_id']);

                // Check if the user is already assigned to the geofence
                if ($user->geofenceUser()->where('geofence_id', $geofence->id)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User is already assigned to this geofence.',
                    ], 409);
                }

                // Manually insert into the pivot table (geofence_user)
                DB::table('geofence_users')->insert([
                    'user_id' => $user->id,
                    'geofence_id' => $geofence->id,
                    'assignment_type' => $assignment['assignment_type'],
                    'created_by_id' => Auth::user()->id,
                    'created_by_type' => get_class(Auth::user()),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $insertedAssignments[] = $assignment;
                $submittedData[] = $assignment;
            }

            // Log the action after the successful insertions
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Geofence Management',
                'action'         => 'Create Assignment',
                'description'    => 'Assigned geofences to users',
                'affected_id'    => null,
                'old_data'       => null,
                'new_data'       => json_encode($submittedData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Geofences assigned successfully.',
                'insertedAssignments' => $insertedAssignments
            ], 200);
        } catch (ModelNotFoundException $e) {
            Log::error('ModelNotFoundException occurred: ', ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'User or Geofence not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Exception occurred: ', ['exception' => $e->getMessage()]);

            $cleanMessage = "Failed to assign geofences. Please try again later.";

            $this->logGeofenceError(
                '[ERROR_ASSIGNING_GEOFENCES]',
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

    public function geofenceUserAssignEdit(Request $request, $id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(18);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }

        $validated = $request->validate([
            'geofence_id' => 'required|exists:geofences,id',
            'assignment_type' => 'required|in:manual,exempt',
        ]);

        try {
            $geofenceUser = GeofenceUser::findOrFail($id);
            $oldData = $geofenceUser->toArray();

            $geofenceUser->update($validated);

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Geofence Management',
                'action'         => 'Update',
                'description'    => 'Updated geofence user assignment (ID: ' . $geofenceUser->id . ')',
                'affected_id'    => $geofenceUser->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => json_encode($geofenceUser->toArray()),
            ]);

            return response()->json([
                'message' => 'Geofence user assignment updated successfully.',
                'data' => $geofenceUser
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Geofence user assignment not found.',
            ], 404);
        } catch (Exception $e) {

            $cleanMessage = "Failed to update geofence user assignment. Please try again later.";

            $this->logGeofenceError(
                '[ERROR_UPDATING_GEOFENCE_USER_ASSIGNMENT]',
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

    public function geofenceUserDelete(Request $request, $id)
    {
                
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(18);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete.'
            ], 403);
        }

        try {
            $geofenceUser = GeofenceUser::findOrFail($id);
            $geofenceUser->delete();

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Geofence Management',
                'action'         => 'Delete Assignment',
                'description'    => 'Deleted geofence user assignment (ID: ' . $geofenceUser->id . ')',
                'affected_id'    => $geofenceUser->id,
                'old_data'       => json_encode($geofenceUser->toArray()),
                'new_data'       => null,
            ]);

            return response()->json([
                'message' => 'Geofence user assignment deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Geofence user assignment not found.',
            ], 404);
        } catch (Exception $e) {

            $cleanMessage = "Failed to delete geofence user assignment. Please try again later.";

            $this->logGeofenceError(
                '[ERROR_DELETING_GEOFENCE_USER_ASSIGNMENT]',
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
