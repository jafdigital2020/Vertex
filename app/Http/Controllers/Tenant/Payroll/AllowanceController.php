<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Allowance;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\UserAllowance;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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

    // Allowance Store Function
    public function allowanceStore(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        // Validate incoming request
        $validated = $request->validate([
            'allowance_name'                    => ['required', 'string', 'max:100', 'unique:allowances,allowance_name'],
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

    // Allowance Update Function
    public function allowanceUpdate(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        // Validate incoming request
        $validated = $request->validate([
            'allowance_name'                    => ['required', 'string', 'max:100', Rule::unique('allowances')->ignore($id)],
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

    // Allowance Delete Function
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

    // ============ User Allowances  ================== //
    public function userAllowanceIndex(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        $allowances = Allowance::where('tenant_id', $tenantId)->get();
        $branches = Branch::where('tenant_id', $tenantId)->get();
        $departments = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $userAllowances = UserAllowance::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User Earnings Index',
                'data' => [
                    'allowances' => $allowances,
                    'branches' => $branches,
                    'departments' => $departments,
                    'designations' => $designations,
                    'userAllowances' => $userAllowances
                ]
            ]);
        }

        return view(
            'tenant.payroll.payroll-items.allowance.allowanceuser',
            compact('allowances', 'branches', 'departments', 'designations', 'userAllowances')
        );
    }

    // Allowance Assign User Function
    public function userAllowanceAssign(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        $validated = $request->validate([
            'type'                 => ['required', Rule::in(['include', 'exclude'])],
            'allowance_id'         => ['required', 'integer', 'exists:allowances,id'],
            'branch_id'            => ['sometimes', 'array'],                 // optional array of branches
            'branch_id.*'          => ['integer', 'exists:branches,id'],      // if used
            'department_id'        => ['sometimes', 'array'],                 // optional
            'department_id.*'      => ['integer', 'exists:departments,id'],
            'designation_id'       => ['sometimes', 'array'],                 // optional
            'designation_id.*'     => ['integer', 'exists:designations,id'],
            'user_id'              => ['required', 'array', 'min:1'],         // must select at least one user
            'user_id.*'            => ['integer', 'exists:users,id'],         // each must exist
            'override_enabled'     => ['sometimes', 'boolean'],               // optional
            'override_amount'      => ['nullable', 'numeric', 'min:0'],
            'calculation_basis'    => ['nullable', Rule::in(['fixed', 'per_attended_day', 'per_attended_hour'])],
            'frequency'            => ['required', Rule::in(['every_payroll', 'every_other', 'one_time'])],
            'effective_start_date' => [
                Rule::requiredIf(fn() => $request->input('type') === 'include'),
                'nullable',
                'date'
            ],
            'effective_end_date'   => [
                'nullable',
                'date',
                'after_or_equal:effective_start_date'
            ],
        ]);

        $userIds = $validated['user_id'];
        $allowanceId = $validated['allowance_id'];

        $alreadyAssigned = UserAllowance::where('allowance_id', $allowanceId)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->all();

        if (!empty($alreadyAssigned)) {
            $ids = implode(', ', $alreadyAssigned);
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => [
                    'user_id' => ["Selected allowance is already assigned to a user."]
                ]
            ], 422);
        }

        $createdRecords = [];
        foreach ($userIds as $uid) {
            $userAllowance = new UserAllowance();
            $userAllowance->user_id               = $uid;
            $userAllowance->allowance_id          = $validated['allowance_id'];
            $userAllowance->type                  = $validated['type'];
            $userAllowance->override_enabled     = $validated['override_enabled'] ?? 0;
            $userAllowance->override_amount       = $validated['override_amount'] ?? null;
            $userAllowance->calculation_basis     = $validated['calculation_basis'] ?? null;
            $userAllowance->frequency             = $validated['frequency'];
            $userAllowance->effective_start_date  = $validated['effective_start_date'] ?? null;
            $userAllowance->effective_end_date    = $validated['effective_end_date'] ?? null;
            $userAllowance->status                = 'active';

            // Track “created_by” polymorphic (web vs global guard)
            if (Auth::guard('web')->check()) {
                $userAllowance->created_by_type = get_class(Auth::guard('web')->user());
                $userAllowance->created_by_id   = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $userAllowance->created_by_type = get_class(Auth::guard('global')->user());
                $userAllowance->created_by_id   = Auth::guard('global')->id();
            }

            $userAllowance->save();
            $createdRecords[] = $userAllowance;

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
                'module'         => 'User Allowances',
                'action'         => 'Assign',
                'description'    => 'Assigned allowance_id ' . $validated['allowance_id']
                    . ' to user_id ' . $uid
                    . ' (type=' . $validated['type'] . ').',
                'affected_id'    => $userAllowance->id,
                'old_data'       => null,
                'new_data'       => json_encode($userAllowance->toArray()),
            ]);
        }

        // 5) Return JSON success, with 201 Created
        return response()->json([
            'message'       => 'Allowance assignments created for ' . count($createdRecords) . ' user(s).',
            'assigned_ids'  => array_column($createdRecords, 'id'),
        ], 201);
    }

    // Edit Allowance Assigned User Function
    public function userAllowanceUpdate(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        // Validate incoming request
        $validated = $request->validate([
            'type'                 => ['required', Rule::in(['include', 'exclude'])],
            'allowance_id'         => ['required', 'integer', 'exists:allowances,id'],
            'override_enabled'     => ['sometimes', 'boolean'],
            'override_amount'      => ['nullable', 'numeric', 'min:0'],
            'calculation_basis'    => ['nullable', Rule::in(['fixed', 'per_attended_day', 'per_attended_hour'])],
            'frequency'            => ['required', Rule::in(['every_payroll', 'every_other', 'one_time'])],
            'effective_start_date' => [
                Rule::requiredIf(fn() => $request->input('type') === 'include'),
                'nullable',
                'date'
            ],
            'effective_end_date'   => ['nullable', 'date', 'after_or_equal:effective_start_date'],
            'status'               => ['sometimes', Rule::in(['active', 'inactive', 'completed', 'hold'])]
        ]);

        $userAllowance = UserAllowance::findOrFail($id);

        $oldData = $userAllowance->toArray();

        $userAllowance->type                 = $validated['type'];
        $userAllowance->allowance_id         = $validated['allowance_id'];
        $userAllowance->override_enabled     = $validated['override_enabled'] ?? 0;
        $userAllowance->override_amount      = $validated['override_amount'] ?? null;
        $userAllowance->calculation_basis    = $validated['calculation_basis'] ?? null;
        $userAllowance->frequency            = $validated['frequency'];
        $userAllowance->effective_start_date = $validated['effective_start_date'] ?? null;
        $userAllowance->effective_end_date   = $validated['effective_end_date'] ?? null;

        if (isset($validated['status'])) {
            $userAllowance->status = $validated['status'];
        }

        if (Auth::guard('web')->check()) {
            $userAllowance->updated_by_type = get_class(Auth::guard('web')->user());
            $userAllowance->updated_by_id   = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $userAllowance->updated_by_type = get_class(Auth::guard('global')->user());
            $userAllowance->updated_by_id   = Auth::guard('global')->id();
        }

        $userAllowance->save();

        $logUserId    = null;
        $logGlobalId  = null;
        if (Auth::guard('web')->check()) {
            $logUserId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $logGlobalId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'        => $logUserId,
            'global_user_id' => $logGlobalId,
            'module'         => 'User Allowances',
            'action'         => 'Update',
            'description'    => 'Updated UserAllowance ID ' . $userAllowance->id,
            'affected_id'    => $userAllowance->id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($userAllowance->toArray()),
        ]);

        return response()->json([
            'message'      => 'Assigned allowance updated successfully.',
            'user_allowance' => $userAllowance,
        ], 200);
    }

    // Delete Allowance Assigned User Function
    public function userAllowanceDelete($id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        $userAllowance = UserAllowance::findOrFail($id);
        $userAllowance->delete();

        UserLog::create([
            'user_id'        => Auth::user()->id,
            'global_user_id' => null,
            'module'         => 'User Allowances',
            'action'         => 'Delete',
            'description'    => 'Deleted UserAllowance ID ' . $userAllowance->id,
            'affected_id'    => $userAllowance->id,
            'old_data'       => json_encode($userAllowance->toArray()),
            'new_data'       => null,
        ]);

        return response()->json([
            'message' => 'Assigned allowance deleted successfully.',
        ], 200);
    }
}
