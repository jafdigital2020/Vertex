<?php

namespace App\Http\Controllers\Tenant\Settings;

use Throwable;
use App\Models\UserLog;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LeaveTypeSettingsController extends Controller
{

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }
    public function leaveTypeSettingsIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(21);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $leaveTypes = $accessData['leaveTypes']->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Leave type settings',
                'data' => $leaveTypes,
            ]);
        }
        return view('tenant.settings.leavetypesettings', [
            'leaveTypes' => $leaveTypes,
            'permission'=> $permission
        ]);
    }

    // Create/Store Leave Type
    public function leaveTypeSettingsStore(Request $request)
    {
        $permission = PermissionHelper::get(21);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ],403);
        }

        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('leave_types', 'name')->where(function ($query) {
                        return $query->where('tenant_id', Auth::user()->tenant_id ?? null);
                    }),
                ],
                'is_earned'         => 'required|boolean',

                // only validate when is_earned = 1
                'earned_rate'       => [
                    'exclude_unless:is_earned,1',
                    'required',
                    'numeric',
                    'min:0',
                ],
                'earned_interval'   => [
                    'exclude_unless:is_earned,1',
                    'required',
                    Rule::in(['ANNUAL', 'MONTHLY', 'NONE']),
                ],

                // only validate when is_earned = 0
                'default_entitle'   => 'required_if:is_earned,0|numeric|min:0',
                'accrual_frequency' => ['required_if:is_earned,0', Rule::in(['ANNUAL', 'MONTHLY', 'NONE'])],
                'max_carryover'     => 'required_if:is_earned,0|numeric|min:0',

                'is_paid'           => 'required|boolean',
                'is_cash_convertible' => [
                    'nullable',
                    'boolean',
                ],

                'conversion_rate' => [
                    'nullable',
                    'required_if:is_cash_convertible,1',
                    'numeric',
                    'min:0',
                ],


            ], $this->validationMessages());

            $tenantId = Auth::user()->tenant_id ?? null;

            // create the leave type in one go:
            $leaveType = LeaveType::create([
                'tenant_id'         => $tenantId,
                'name'              => $validated['name'],
                'is_earned'         => $validated['is_earned'],
                'earned_rate'       => $validated['earned_rate']     ?? null,
                'earned_interval'   => $validated['earned_interval'] ?? null,
                'default_entitle'   => $validated['default_entitle']   ?? 0,
                'accrual_frequency' => $validated['accrual_frequency'] ?? 'NONE',
                'max_carryover'     => $validated['max_carryover']     ?? 0,
                'is_paid'           => $validated['is_paid'],
                'is_cash_convertible' => $validated['is_cash_convertible'] ?? false,
                'conversion_rate'     => $validated['conversion_rate'] ?? null,
            ]);

            // Logging (unchanged)
            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Leave Types',
                'action'         => 'Create',
                'description'    => 'Created leave type: ' . $leaveType->name,
                'affected_id'    => $leaveType->id,
                'old_data'       => null,
                'new_data'       => json_encode($leaveType->only(array_keys($validated))),
            ]);

            return response()->json([
                'message'   => 'Leave type created successfully',
                'leaveType' => $leaveType
            ], 201);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Some fields are invalid. Please check your input.',
                'errors'  => $ve->errors()
            ], 422);
        } catch (Throwable $e) {
            Log::error('Error creating leave type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Unable to save the leave type due to an internal error. Please try again later.'
            ], 500);
        }
    }

    // Edit LeaveType
    public function leaveTypeSettingsUpdate(Request $request, $id)
    {

        $permission = PermissionHelper::get(21);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ],403);
        }
        try {
            $validated = $request->validate([
                'name'              => 'required|string|max:100|unique:leave_types,name,' . $id,
                'is_earned'         => 'required|boolean',

                // 🔹 Earned fields — only validated when is_earned = true
                'earned_rate'       => [
                    'exclude_if:is_earned,0',
                    'required',
                    'numeric',
                    'min:0',
                ],
                'earned_interval'   => [
                    'exclude_if:is_earned,0',
                    'required',
                    Rule::in(['ANNUAL', 'MONTHLY', 'NONE']),
                ],

                // 🔹 Global defaults — only validated when is_earned = false
                'default_entitle'   => 'required|numeric|min:0',
                'accrual_frequency' => [
                    'exclude_if:is_earned,1',
                    'required',
                    Rule::in(['ANNUAL', 'MONTHLY', 'NONE']),
                ],
                'max_carryover'     => [
                    'exclude_if:is_earned,1',
                    'required',
                    'numeric',
                    'min:0',
                ],
                'is_paid'           => 'required|boolean',
                'is_cash_convertible' => 'nullable|boolean',
                'conversion_rate'     => [
                    'required_if:is_cash_convertible,1',
                    'nullable',
                    'numeric',
                    'min:0',
                ],

            ], $this->validationMessages());

            $leaveType = LeaveType::findOrFail($id);
            $oldData   = $leaveType->only(array_keys($validated));

            $leaveType->update([
                'name'              => $validated['name'],
                'is_earned'         => $validated['is_earned'],
                'earned_rate'       => $validated['earned_rate']     ?? null,
                'earned_interval'   => $validated['earned_interval'] ?? null,
                'default_entitle'   => $validated['default_entitle']   ?? 0,
                'accrual_frequency' => $validated['accrual_frequency'] ?? 'NONE',
                'max_carryover'     => $validated['max_carryover']     ?? 0,
                'is_paid'           => $validated['is_paid'],
                'is_cash_convertible' => $validated['is_cash_convertible'],
                'conversion_rate'     => $validated['is_cash_convertible'] ? ($validated['conversion_rate'] ?? 0) : null,
            ]);

            // Logging (unchanged)
            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Leave Types',
                'action'         => 'Update',
                'description'    => 'Updated leave type: ' . $leaveType->name,
                'affected_id'    => $leaveType->id,
                'old_data'       => $oldData,
                'new_data'       => json_encode($leaveType->only(array_keys($validated))),
            ]);

            return response()->json([
                'message'   => 'Leave type updated successfully',
                'leaveType' => $leaveType
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Some fields are invalid. Please check your input.',
                'errors'  => $ve->errors()
            ], 422);
        } catch (Throwable $e) {
            Log::error('Error updating leave type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Unable to update the leave type due to an internal error.'
            ], 500);
        }
    }

    // Validation Message
    private function validationMessages(): array
    {
        return [
            'name.required'            => 'Please enter the leave type name.',
            'name.string'              => 'The leave type name must be text.',
            'name.max'                 => 'The leave type name cannot exceed 100 characters.',
            'name.unique'              => 'A leave type with that name already exists.',
            'default_entitle.*'        => 'Please enter a valid number of entitlement days.',
            'accrual_frequency.*'      => 'Please select a valid accrual frequency.',
            'is_paid.*'                => 'Please select if the leave type is paid or unpaid.',
            'max_carryover.*'          => 'Please enter a valid number for max carry over days.',
        ];
    }

    public function leaveTypeSettingsDelete($id)
    {
        $permission = PermissionHelper::get(21);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ],403);
        }

        try {
            $leaveType = LeaveType::findOrFail($id);
            $oldData = $leaveType->toArray();

            $leaveType->delete();

            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            // 📝 Log deletion
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Leave Types',
                'action'         => 'Delete',
                'description'    => 'Deleted leave type: ' . ($leaveType->name ?? 'N/A') . ', ID: ' . $id,
                'affected_id'    => $id,
                'old_data'       => json_encode($oldData),
                'new_data'       => null,
            ]);

            return response()->json([
                'message' => 'Leave type deleted successfully.',
                'deleted_id' => $id
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Leave type not found.',
            ], 404);
        } catch (Throwable $e) {
            Log::error('Error deleting leave type', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to delete leave type. Please try again later.',
            ], 500);
        }
    }
}
