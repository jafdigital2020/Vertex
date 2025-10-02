<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\User;
use App\Models\UserLog;
use App\Models\Allowance;
use Illuminate\Http\Request;
use App\Models\UserAllowance;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AllowanceController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    // Allowance
    public function payrollItemsAllowance(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $allowances = Allowance::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items allowances',
                'data' => $allowances
            ]);
        }

        return view('tenant.payroll.payroll-items.allowance.allowance', compact('allowances'));
    }

    // Create Allowance
    public function allowanceStore(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        // Validate incoming request
        $validated = $request->validate([
            'allowance_name'                    => ['required', 'string', 'max:100'],
            'calculation_basis'      => ['required', Rule::in(['fixed', 'per_attended_day', 'per_attended_hour'])],
            'amount'          => ['required', 'numeric', 'min:0'],
            'is_taxable'              => ['required', 'boolean'],
            'apply_to_all_employees'  => ['required', 'boolean'],
            'description'           => ['nullable', 'string'],
        ]);

        $allowance = new Allowance();
        $allowance->tenant_id              = $tenantId;
        $allowance->allowance_name         = $validated['allowance_name'];
        $allowance->calculation_basis      = $validated['calculation_basis'];
        $allowance->amount                 = $validated['amount'];
        $allowance->is_taxable             = $validated['is_taxable'];
        $allowance->apply_to_all_employees = $validated['apply_to_all_employees'];
        $allowance->description            = $validated['description'] ?? null;
        $allowance->created_by_id          = $authUserId;
        $allowance->created_by_type        = get_class($authUser);

        $allowance->save();

        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Allowances',
            'action'         => 'Create',
            'description'    => 'Created Allowance (ID: ' . $allowance->id . ', Name: ' . $allowance->name . ').',
            'affected_id'    => $allowance->id,
            'old_data'       => null,
            'new_data'       => json_encode($allowance->toArray()),
        ]);

        // 5. Return JSON response with 201 status
        return response()->json([
            'message'      => 'Allowance created successfully.',
            'allowance' => $allowance,
        ], 201);
    }

    // Update Allowance
    public function allowanceUpdate(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        // Validate incoming request
        $validated = $request->validate([
            'allowance_name'                    => ['required', 'string', 'max:100'],
            'calculation_basis'      => ['required', Rule::in(['fixed', 'per_attended_day', 'per_attended_hour'])],
            'amount'          => ['required', 'numeric', 'min:0'],
            'is_taxable'              => ['required', 'boolean'],
            'apply_to_all_employees'  => ['required', 'boolean'],
            'description'           => ['nullable', 'string'],
        ]);

        $allowance = Allowance::findOrFail($id);
        $oldData = $allowance->toArray();

        $allowance->allowance_name                   = $validated['allowance_name'];
        $allowance->calculation_basis     = $validated['calculation_basis'];
        $allowance->amount         = $validated['amount'];
        $allowance->is_taxable             = $validated['is_taxable'];
        $allowance->apply_to_all_employees = $validated['apply_to_all_employees'];
        $allowance->description = $validated['description'] ?? null;
        $allowance->updated_by_id =  $authUserId;
        $allowance->updated_by_type =  get_class($authUser);

        $allowance->save();

        // Log Starts
        UserLog::create([
            'user_id'        => $authUserId,
            'global_user_id' => null,
            'module'         => 'Allowances',
            'action'         => 'Update',
            'description'    => 'Updated Allowance (ID: ' . $allowance->id . ', Name: ' . $allowance->allowance_name . ').',
            'affected_id'    => $allowance->id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($allowance->toArray()),
        ]);

        // Return JSON response with 200 status
        return response()->json([
            'message'      => 'Allowance updated successfully.',
            'allowance' => $allowance,
        ], 200);
    }

    // Delete Allowance
    public function allowanceDelete($id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        $allowance = Allowance::findOrFail($id);
        $allowance->delete();

        // Log Starts
        UserLog::create([
            'user_id'        =>  $authUserId,
            'global_user_id' => null,
            'module'         => 'Allowances',
            'action'         => 'Delete',
            'description'    => 'Deleted Allowance (ID: ' . $allowance->id . ', Name: ' . $allowance->allowance_name . ').',
            'affected_id'    => $allowance->id,
            'old_data'       => json_encode($allowance->toArray()),
            'new_data'       => null,
        ]);

        // Return JSON response with 200 status
        return response()->json([
            'message' => 'Allowance deleted successfully.',
        ], 200);
    }

    // ============== User Allowance =============== //
    public function userAllowanceIndex(Request $request, $userId)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        $allowances = Allowance::where('tenant_id', $tenantId)->get();
        $user = User::with('userAllowances')->findOrFail($userId);

        if (request()->expectsJson()) {
            return response()->json([
                'user' => $user->only(['id', 'name']),
            ]);
        }

        return view('tenant.employee.salaries.allowanceuser', compact('user', 'allowances'));
    }

    // Assign Allowance to User
    public function userAllowanceAssign(Request $request, $userId)
    {
        try {
            $validated = $request->validate([
                'allowance_id'         => ['required', 'integer', 'exists:allowances,id'],
                'override_enabled'     => ['sometimes', 'boolean'],               // optional
                'override_amount'      => ['nullable', 'numeric', 'min:0'],
                'calculation_basis'    => ['nullable', Rule::in(['fixed', 'per_attended_day', 'per_attended_hour'])],
                'frequency'            => ['required', Rule::in(['every_payroll', 'every_other', 'one_time'])],
                'effective_start_date' => ['required', 'date'],
                'effective_end_date'   => [
                    'nullable',
                    'date',
                    'after_or_equal:effective_start_date'
                ],
            ]);

            // Find the user
            $user = User::findOrFail($userId);

            // Create the user allowance
            $userAllowance = $user->userAllowances()->create([
                'user_id' => $user->id,
                'allowance_id' => $validated['allowance_id'],
                'type' => 'include',
                'override_enabled' => $validated['override_enabled'] ?? false,
                'override_amount' => $validated['override_amount'] ?? null,
                'calculation_basis' => $validated['calculation_basis'] ?? null,
                'frequency' => $validated['frequency'],
                'effective_start_date' => $validated['effective_start_date'],
                'effective_end_date' => $validated['effective_end_date'],
                'status' => 'active',
                'created_by_id' => $this->authUser()->id,
                'created_by_type' => get_class($this->authUser()),
            ]);

            // User Logs
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
                'module' => 'Allowances',
                'action' => 'Create',
                'description' => 'Created Allowance for user ID: ' . $userId,
                'affected_id' => $userAllowance->id,
                'old_data' => null,
                'new_data' => json_encode($userAllowance->toArray()),
            ]);

            return response()->json([
                'message' => 'Allowance created successfully.',
                'userAllowance' => $userAllowance,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Please check the information you entered.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Employee not found. Please select a valid employee.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while creating the allowance. Please try again.',
            ], 500);
        }
    }

    // Edit User Allowance
    public function userAllowanceUpdate(Request $request, $userAllowanceId)
    {
        try {
            $userAllowance = UserAllowance::findOrFail($userAllowanceId);
            $overrideEnabledRaw = $request->input('override_enabled');
            $overrideEnabled = in_array($overrideEnabledRaw, [true, 'true', 1, '1', 'on'], true);

            $validated = $request->validate([
                'allowance_id'         => ['required', 'integer', 'exists:allowances,id'],
                'override_enabled'     => ['sometimes'],
                'override_amount'      => $overrideEnabled ? ['required', 'numeric', 'min:0'] : ['nullable', 'numeric', 'min:0'],
                'calculation_basis'    => ['nullable', Rule::in(['fixed', 'per_attended_day', 'per_attended_hour'])],
                'frequency'            => ['required', Rule::in(['every_payroll', 'every_other', 'one_time'])],
                'effective_start_date' => ['required', 'date'],
                'effective_end_date'   => ['nullable', 'date', 'after_or_equal:effective_start_date'],
                'status'               => ['sometimes', Rule::in(['active', 'inactive', 'complete', 'hold'])],
                'notes'                => ['nullable', 'string', 'max:255'],
            ]);

            $already = UserAllowance::where('user_id', $userAllowance->user_id)
                ->where('allowance_id', $validated['allowance_id'])
                ->where('id', '<>', $userAllowanceId)
                ->whereIn('status', ['active', 'hold'])
                ->exists();

            if ($already) {
                throw ValidationException::withMessages([
                    'allowance_id' => ['This allowance is already assigned to the user.'],
                ]);
            }

            $oldData = $userAllowance->toArray();

            $userAllowance->update([
                'allowance_id'         => $validated['allowance_id'],
                'override_enabled'     => $overrideEnabled ? 1 : 0,
                'override_amount'      => $validated['override_amount'] ?? null,
                'calculation_basis'    => $validated['calculation_basis'] ?? null,
                'frequency'            => $validated['frequency'],
                'effective_start_date' => $validated['effective_start_date'] ?? null,
                'effective_end_date'   => $validated['effective_end_date'] ?? null,
                'status'               => $validated['status'] ?? $userAllowance->status,
                'notes'                => $validated['notes'] ?? $userAllowance->notes,
                'updated_by_id'        => optional($this->authUser())->id ?? null,
                'updated_by_type'      => $this->authUser() ? get_class($this->authUser()) : null,
            ]);

            // Logs
            $empId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'        => $empId,
                'global_user_id' => $globalUserId,
                'module'         => 'Allowances',
                'action'         => 'Update',
                'description'    => 'Updated allowance record ID: ' . $userAllowanceId,
                'affected_id'    => $userAllowance->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => json_encode($userAllowance->toArray()),
            ]);

            return response()->json([
                'message'        => 'User allowance updated successfully.',
                'userAllowance'  => $userAllowance,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Please check the information you entered.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User allowance not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong while updating the allowance. Please try again.'], 500);
        }
    }

    // Delete User Allowance
    public function userAllowanceDelete($userAllowanceId)
    {
        try {
            $userAllowance = UserAllowance::findOrFail($userAllowanceId);
            $oldData = $userAllowance->toArray();
            $userAllowance->delete();

            // Logs
            $empId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'        => $empId,
                'global_user_id' => $globalUserId,
                'module'         => 'Allowances',
                'action'         => 'Delete',
                'description'    => 'Deleted allowance record ID: ' . $userAllowanceId,
                'affected_id'    => $userAllowanceId,
                'old_data'       => json_encode($oldData),
                'new_data'       => null,
            ]);

            return response()->json(['message' => 'User allowance deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User allowance not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong while deleting the allowance. Please try again.'], 500);
        }
    }
}
