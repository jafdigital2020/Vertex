<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EarningType;
use App\Models\UserEarning;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EarningsController extends Controller
{
    public function earningIndex(Request $request)
    {
        $earningTypes = EarningType::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Earnings Index',
                'data' => [
                    'earning_types' => $earningTypes
                ]
            ]);
        }

        return view('tenant.payroll.payroll-items.earning.earnings', compact('earningTypes'));
    }

    // Earning Store Method
    public function earningStore(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:100', 'unique:earning_types,name'],
            'calculation_method'      => ['required', Rule::in(['fixed', 'percentage'])],
            'default_amount'          => ['required', 'numeric', 'min:0'],
            'is_taxable'              => ['required', 'boolean'],
            'apply_to_all_employees'  => ['required', 'boolean'],
            'description'           => ['nullable', 'string'],
        ]);

        $earningType = new EarningType();
        $earningType->name                   = $validated['name'];
        $earningType->calculation_method     = $validated['calculation_method'];
        $earningType->default_amount         = $validated['default_amount'];
        $earningType->is_taxable             = $validated['is_taxable'];
        $earningType->apply_to_all_employees = $validated['apply_to_all_employees'];
        $earningType->description = $validated['description'] ?? null;
        $earningType->created_by_id = Auth::user()->id;
        $earningType->created_by_type = get_class(Auth::user());

        $earningType->save();

        // Log Starts
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
            'module'         => 'Earning Types',
            'action'         => 'Create',
            'description'    => 'Created Earning Type (ID: ' . $earningType->id . ', Name: ' . $earningType->name . ').',
            'affected_id'    => $earningType->id,
            'old_data'       => null,
            'new_data'       => json_encode($earningType->toArray()),
        ]);

        // 5. Return JSON response with 201 status
        return response()->json([
            'message'      => 'Earning type created successfully.',
            'earning_type' => $earningType,
        ], 201);
    }

    // Earning Update Method
    public function earningUpdate(Request $request, $id)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:100', Rule::unique('earning_types')->ignore($id)],
            'calculation_method'      => ['required', Rule::in(['fixed', 'percentage'])],
            'default_amount'          => ['required', 'numeric', 'min:0'],
            'is_taxable'              => ['required', 'boolean'],
            'apply_to_all_employees'  => ['required', 'boolean'],
            'description'           => ['nullable', 'string'],
        ]);

        $earningType = EarningType::findOrFail($id);
        $oldData = $earningType->toArray();

        $earningType->name                   = $validated['name'];
        $earningType->calculation_method     = $validated['calculation_method'];
        $earningType->default_amount         = $validated['default_amount'];
        $earningType->is_taxable             = $validated['is_taxable'];
        $earningType->apply_to_all_employees = $validated['apply_to_all_employees'];
        $earningType->description = $validated['description'] ?? null;
        $earningType->updated_by_id = Auth::user()->id;
        $earningType->updated_by_type = get_class(Auth::user());

        $earningType->save();

        // Log Starts
        UserLog::create([
            'user_id'        => Auth::user()->id,
            'global_user_id' => null,
            'module'         => 'Earning Types',
            'action'         => 'Update',
            'description'    => 'Updated Earning Type (ID: ' . $earningType->id . ', Name: ' . $earningType->name . ').',
            'affected_id'    => $earningType->id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($earningType->toArray()),
        ]);

        // Return JSON response with 200 status
        return response()->json([
            'message'      => 'Earning type updated successfully.',
            'earning_type' => $earningType,
        ], 200);
    }

    // Earning Delete Method
    public function earningDelete($id)
    {
        $earningType = EarningType::findOrFail($id);
        $earningType->delete();

        // Log Starts
        UserLog::create([
            'user_id'        => Auth::user()->id,
            'global_user_id' => null,
            'module'         => 'Earning Types',
            'action'         => 'Delete',
            'description'    => 'Deleted Earning Type (ID: ' . $earningType->id . ', Name: ' . $earningType->name . ').',
            'affected_id'    => $earningType->id,
            'old_data'       => json_encode($earningType->toArray()),
            'new_data'       => null,
        ]);

        // Return JSON response with 200 status
        return response()->json([
            'message' => 'Earning type deleted successfully.',
        ], 200);
    }

    // User Earning Index
    public function userEarningIndex(Request $request)
    {
        $earningTypes = EarningType::all();
        $branches = Branch::all();
        $departments = Department::all();
        $designations = Designation::all();
        $userEarnings = UserEarning::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User Earnings Index',
                'data' => [
                    'earning_types' => $earningTypes,
                    'branches' => $branches,
                    'departments' => $departments,
                    'designations' => $designations,
                    'user_earnings' => $userEarnings
                ]
            ]);
        }

        return view(
            'tenant.payroll.payroll-items.earning.earninguser',
            compact('earningTypes', 'branches', 'departments', 'designations', 'userEarnings')
        );
    }

    // User Earning Store/Assigning Method
    public function userEarningAssign(Request $request)
    {
        $validated = $request->validate([
            'type'                 => ['required', Rule::in(['include', 'exclude'])],
            'earning_type_id'      => ['required', 'integer', 'exists:earning_types,id'],
            'branch_id'            => ['sometimes', 'array'],                 // optional array of branches
            'branch_id.*'          => ['integer', 'exists:branches,id'],      // if used
            'department_id'        => ['sometimes', 'array'],                 // optional
            'department_id.*'      => ['integer', 'exists:departments,id'],
            'designation_id'       => ['sometimes', 'array'],                 // optional
            'designation_id.*'     => ['integer', 'exists:designations,id'],
            'user_id'              => ['required', 'array', 'min:1'],         // must select at least one user
            'user_id.*'            => ['integer', 'exists:users,id'],         // each must exist
            'amount'               => ['nullable', 'numeric', 'min:0'],
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
        $earningTypeId = $validated['earning_type_id'];

        $alreadyAssigned = UserEarning::where('earning_type_id', $earningTypeId)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->all();

        if (!empty($alreadyAssigned)) {
            $ids = implode(', ', $alreadyAssigned);
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => [
                    'user_id' => ["Selected earning type is already assigned to a user."]
                ]
            ], 422);
        }

        $createdRecords = [];
        foreach ($userIds as $uid) {
            $userEarning = new UserEarning();
            $userEarning->user_id               = $uid;
            $userEarning->earning_type_id       = $validated['earning_type_id'];
            $userEarning->type                  = $validated['type'];
            $userEarning->amount                = $validated['amount'] ?? null;
            $userEarning->frequency             = $validated['frequency'];
            $userEarning->effective_start_date  = $validated['effective_start_date'] ?? null;
            $userEarning->effective_end_date    = $validated['effective_end_date'] ?? null;
            $userEarning->status                = 'active';

            // Track “created_by” polymorphic (web vs global guard)
            if (Auth::guard('web')->check()) {
                $userEarning->created_by_type = get_class(Auth::guard('web')->user());
                $userEarning->created_by_id   = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $userEarning->created_by_type = get_class(Auth::guard('global')->user());
                $userEarning->created_by_id   = Auth::guard('global')->id();
            }

            $userEarning->save();
            $createdRecords[] = $userEarning;

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
                'module'         => 'User Earnings',
                'action'         => 'Assign',
                'description'    => 'Assigned earning_type_id ' . $validated['earning_type_id']
                    . ' to user_id ' . $uid
                    . ' (type=' . $validated['type'] . ').',
                'affected_id'    => $userEarning->id,
                'old_data'       => null,
                'new_data'       => json_encode($userEarning->toArray()),
            ]);
        }

        // 5) Return JSON success, with 201 Created
        return response()->json([
            'message'       => 'Earning assignments created for ' . count($createdRecords) . ' user(s).',
            'assigned_ids'  => array_column($createdRecords, 'id'),
        ], 201);
    }

    // User Earning Update Method
    public function userEarningUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'type'                 => ['required', Rule::in(['include', 'exclude'])],
            'earning_type_id'      => ['required', 'integer', 'exists:earning_types,id'],
            'amount'               => ['nullable', 'numeric', 'min:0'],
            'frequency'            => ['required', Rule::in(['every_payroll', 'every_other', 'one_time'])],
            'effective_start_date' => [
                Rule::requiredIf(fn() => $request->input('type') === 'include'),
                'nullable',
                'date'
            ],
            'effective_end_date'   => ['nullable', 'date', 'after_or_equal:effective_start_date'],
            'status'               => ['sometimes', Rule::in(['active', 'inactive', 'completed', 'hold'])]
        ]);

        $userEarning = UserEarning::findOrFail($id);

        $oldData = $userEarning->toArray();

        $userEarning->type                 = $validated['type'];
        $userEarning->earning_type_id      = $validated['earning_type_id'];
        $userEarning->amount               = $validated['amount'] ?? null;
        $userEarning->frequency            = $validated['frequency'];
        $userEarning->effective_start_date = $validated['effective_start_date'] ?? null;
        $userEarning->effective_end_date   = $validated['effective_end_date'] ?? null;
        if (isset($validated['status'])) {
            $userEarning->status = $validated['status'];
        }

        if (Auth::guard('web')->check()) {
            $userEarning->updated_by_type = get_class(Auth::guard('web')->user());
            $userEarning->updated_by_id   = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $userEarning->updated_by_type = get_class(Auth::guard('global')->user());
            $userEarning->updated_by_id   = Auth::guard('global')->id();
        }

        $userEarning->save();

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
            'module'         => 'User Earnings',
            'action'         => 'Update',
            'description'    => 'Updated UserEarning ID ' . $userEarning->id,
            'affected_id'    => $userEarning->id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($userEarning->toArray()),
        ]);

        return response()->json([
            'message'      => 'Assigned earning updated successfully.',
            'user_earning' => $userEarning,
        ], 200);
    }

    // User Earning Delete Method
    public function userEarningDelete($id)
    {
        $userEarning = UserEarning::findOrFail($id);
        $userEarning->delete();

        // Log Starts
        UserLog::create([
            'user_id'        => Auth::user()->id,
            'global_user_id' => null,
            'module'         => 'User Earnings',
            'action'         => 'Delete',
            'description'    => 'Deleted UserEarning ID ' . $userEarning->id,
            'affected_id'    => $userEarning->id,
            'old_data'       => json_encode($userEarning->toArray()),
            'new_data'       => null,
        ]);

        return response()->json([
            'message' => 'Assigned earning deleted successfully.',
        ], 200);
    }
}
