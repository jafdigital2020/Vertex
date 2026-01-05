<?php

namespace App\Http\Controllers\Tenant\Leave;

use Throwable;
use Carbon\Carbon;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveSetting;
use Illuminate\Http\Request;
use App\Models\LeaveEntitlement;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;
use Illuminate\Validation\ValidationException;

class LeaveSettingsController extends Controller
{

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function LeaveSettingsIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(21);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $leaveTypes = $accessData['leaveTypes']->get();
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations =  $accessData['designations']->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Leave settings',
                'data' => $leaveTypes,
                'branches' => $branches,
                'departments' => $departments,
                'designations' => $designations,
            ]);
        }

        return view('tenant.leave.leavesettings', [
            'leaveTypes' => $leaveTypes,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'permission' => $permission
        ]);
    }

    public function statusToggle(Request $request, LeaveType $leaveType)
    {
        $permission = PermissionHelper::get(21);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }

        $data = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $leaveType->status = $data['status'];
        $leaveType->save();

        return response()->json([
            'success'    => true,
            'leave_type' => [
                'id'     => $leaveType->id,
                'status' => $leaveType->status,
            ],
        ]);
    }

    public function leaveSettingShow($leaveTypeId)
    {
        // if none exist yet, create with sensible defaults
        $setting = LeaveSetting::firstOrCreate(
            ['leave_type_id' => $leaveTypeId],
            [
                'advance_notice_days' => 0,
                'allow_half_day'      => false,
                'allow_backdated'     => false,
                'backdated_days'      => 0,
                'require_documents'   => false,
            ]
        );

        return response()->json($setting);
    }

    public function leaveSettingsCreate(Request $request)
    {
        // Define validation rules for all possible fields

        $permission = PermissionHelper::get(21);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }

        $rules = [
            'leave_type_id'         => 'required|exists:leave_types,id',
            'advance_notice_days'   => 'integer|min:0|nullable',
            'allow_half_day'        => 'boolean',
            'allow_backdated'       => 'boolean',
            'backdated_days'        => 'integer|min:0|nullable',
            'require_documents'     => 'boolean',
        ];

        // Only validate the fields actually sent
        $data = $request->validate(
            array_intersect_key($rules, $request->all())
        );

        try {
            // Load existing or create new for that leave_type
            $settings = LeaveSetting::firstOrNew([
                'leave_type_id' => $data['leave_type_id']
            ]);

            // Update only provided fields
            foreach ($data as $key => $value) {
                $settings->{$key} = $value;
            }

            $settings->save();

            // Logging
            $userId       = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Leave Settings',
                'action'         => $settings->wasRecentlyCreated ? 'Create' : 'Update',
                'description'    => ($settings->wasRecentlyCreated ? 'Created' : 'Updated')
                    . " settings for leave type #{$settings->leave_type_id}",
                'affected_id'    => $settings->id,
                'old_data'       => null,
                'new_data'       => json_encode($settings->only(array_keys($data))),
            ]);

            return response()->json([
                'message' => 'Leave settings saved successfully.',
                'data'    => $settings,
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Invalid input.',
                'errors'  => $ve->errors(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Error saving leave settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to save leave settings.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // User Assigning
    public function assignUsers(Request $request)
    {
        $permission = PermissionHelper::get(21);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }

        if (! $request->isJson()) {
            return response()->json(['message' => 'Invalid request type. Use JSON.'], 415);
        }

        $data = $request->validate([
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'user_ids'      => 'required|array|min:1',
            'user_ids.*'    => 'integer|exists:users,id',
        ]);

        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $now       = Carbon::now();

        // 1. Determine period_start & period_end once
        if ($leaveType->is_earned) {
            $opening = $current = 0;
            if ($leaveType->earned_interval === 'MONTHLY') {
                $periodStart = $now->copy()->startOfMonth()->toDateString();
                $periodEnd   = $now->copy()->endOfMonth()->toDateString();
            } else {
                $periodStart = $now->copy()->startOfYear()->toDateString();
                $periodEnd   = $now->copy()->endOfYear()->toDateString();
            }
        } else {
            $opening = $current = $leaveType->default_entitle;
            if ($leaveType->accrual_frequency === 'ANNUAL') {
                $periodStart = $now->copy()->startOfYear()->toDateString();
                $periodEnd   = $now->copy()->endOfYear()->toDateString();
            } else {
                // Fallback to annual
                $periodStart = $now->copy()->startOfYear()->toDateString();
                $periodEnd   = $now->copy()->endOfYear()->toDateString();
            }
        }

        $allUsers = $data['user_ids'];

        // 2. Find existing entitlements for this batch & period
        $already = LeaveEntitlement::where('leave_type_id', $leaveType->id)
            ->where('period_start', $periodStart)
            ->whereIn('user_id', $allUsers)
            ->pluck('user_id')
            ->toArray();

        // 3. Filter out duplicates
        $toCreate = array_diff($allUsers, $already);
        if (empty($toCreate)) {
            return response()->json([
                'message' => 'All selected users already have entitlements for this period.',
                'skipped_user_ids' => $already,
            ], 409);
        }

        // 4. Build rows
        $nowTimestamp = now();
        $rows = [];
        foreach ($toCreate as $uid) {
            $rows[] = [
                'user_id'         => $uid,
                'leave_type_id'   => $leaveType->id,
                'opening_balance' => $opening,
                'current_balance' => $current,
                'period_start'    => $periodStart,
                'period_end'      => $periodEnd,
                'created_at'      => $nowTimestamp,
                'updated_at'      => $nowTimestamp,
            ];
        }

        // 5. Insert in a transaction
        DB::transaction(fn() => LeaveEntitlement::insert($rows));

        return response()->json([
            'message'           => 'Leave entitlements assigned successfully.',
            'created_user_ids'  => array_values($toCreate),
            'skipped_user_ids'  => $already,
        ], 201);
    }

    // View Assigned Employee
    public function assignedUsersIndex(Request $request, $id)
    {   
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(21);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $leaveTypes = $accessData['leaveTypes']->get();
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations =  $accessData['designations']->get();
        $assignedUsers = LeaveEntitlement::with(['user', 'leaveType'])
            ->where('leave_type_id', $id)
            ->get();

        $leaveType = LeaveType::findOrFail($id);
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Assigned users for leave type',
                'data' => $assignedUsers,
                'leave_type' => $leaveType,
            ]);
        }

        return view('tenant.leave.assigned-users', [
            'assignedUsers' => $assignedUsers,
            'leaveType' => $leaveType,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'permission' => $permission
        ]);
    }
    public function filter(Request $request){
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(21);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $branch = $request->input('branch');
        $department = $request->input('department');
        $designation = $request->input('designation'); 
        $leavetype_id = $request->input('leaveType_id'); 

        $query = LeaveEntitlement::with(['user', 'leaveType'])
        ->where('leave_type_id', $leavetype_id);
 
        if ($branch) {
            $query->whereHas('user.employmentDetail.branch', function ($q) use ($branch) {
                $q->where('id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail.department', function ($q) use ($department) {
                $q->where('id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail.designation', function ($q) use ($designation) {
                $q->where('id', $designation);
            });
        }
        $assignedUsers = $query->get();

        $html = view('tenant.leave.assigned-users_filter', compact('assignedUsers', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]); 
    }
    // Update Assigned User
    public function assignedUsersUpdate(Request $request, $id)
    {
        $data = $request->validate([
            'current_balance' => 'required|numeric|min:0',
        ]);

        $leaveEntitlement = LeaveEntitlement::findOrFail($id);
        $leaveEntitlement->current_balance = $data['current_balance'];
        $leaveEntitlement->save();

        // Logging
        $userId       = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();
        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Leave Settings',
            'action'         => 'Update',
            'description'    => "Updated assigned leave for user #{$leaveEntitlement->user_id} on leave type #{$leaveEntitlement->leave_type_id}",
            'affected_id' => $leaveEntitlement->id,
            'old_data'       => null,
            'new_data'       => json_encode([
                'current_balance' => $leaveEntitlement->current_balance,
                'leave_type_id'   => $leaveEntitlement->leave_type_id,
                'user_id'         => $leaveEntitlement->user_id,
            ]),
        ]);

        return response()->json([
            'message' => 'Assigned user updated successfully.',
            'data'    => $leaveEntitlement,
        ], 200);
    }

    // Delete Assigned User
    public function assignedUsersDelete($id)
    {
        $leaveEntitlement = LeaveEntitlement::findOrFail($id);
        $leaveEntitlement->delete();

        // Logging
        $userId       = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();
        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Leave Settings',
            'action'         => 'Delete',
            'description'    => "Deleted assigned leave for user #{$leaveEntitlement->user_id} on leave type #{$leaveEntitlement->leave_type_id}",
            'affected_id' => $leaveEntitlement->id,
            'old_data'       => json_encode($leaveEntitlement->only(['user_id', 'leave_type_id', 'current_balance'])),
            'new_data'       => null,
        ]);

        return response()->json([
            'message' => 'Assigned user deleted successfully.',
            'data'    => $leaveEntitlement,
        ], 200);
    }
}
