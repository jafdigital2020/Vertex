<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\DeductionType;
use App\Models\UserDeduction;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DeductionsController extends Controller
{
    public function deductionIndex(Request $request)
    {
        $deductionTypes = DeductionType::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Deductions Index',
                'data' => [
                    'deduction_types' => $deductionTypes
                ]
            ]);
        }

        return view('tenant.payroll.payroll-items.deduction.deductions', compact('deductionTypes'));
    }

    // Deduction Store Method
    public function deductionStore(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:100', 'unique:deduction_types,name'],
            'calculation_method'      => ['required', Rule::in(['fixed', 'percentage'])],
            'default_amount'          => ['required', 'numeric', 'min:0'],
            'is_taxable'              => ['required', 'boolean'],
            'apply_to_all_employees'  => ['required', 'boolean'],
            'description'           => ['nullable', 'string'],
        ]);

        $deductionType = new DeductionType();
        $deductionType->name                   = $validated['name'];
        $deductionType->calculation_method     = $validated['calculation_method'];
        $deductionType->default_amount         = $validated['default_amount'];
        $deductionType->is_taxable             = $validated['is_taxable'];
        $deductionType->apply_to_all_employees = $validated['apply_to_all_employees'];
        $deductionType->description = $validated['description'] ?? null;
        $deductionType->created_by_id = Auth::user()->id;
        $deductionType->created_by_type = get_class(Auth::user());

        $deductionType->save();

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
            'module'         => 'Deduction Types',
            'action'         => 'Create',
            'description'    => 'Created Deduction Type (ID: ' . $deductionType->id . ', Name: ' . $deductionType->name . ').',
            'affected_id'    => $deductionType->id,
            'old_data'       => null,
            'new_data'       => json_encode($deductionType->toArray()),
        ]);

        // 5. Return JSON response with 201 status
        return response()->json([
            'message'      => 'Deduction type created successfully.',
            'deduction_type' => $deductionType,
        ], 201);
    }

    // Deduction Update Method
    public function deductionUpdate(Request $request, $id)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:100', Rule::unique('deduction_types')->ignore($id)],
            'calculation_method'      => ['required', Rule::in(['fixed', 'percentage'])],
            'default_amount'          => ['required', 'numeric', 'min:0'],
            'is_taxable'              => ['required', 'boolean'],
            'apply_to_all_employees'  => ['required', 'boolean'],
            'description'           => ['nullable', 'string'],
        ]);

        $deductionType = DeductionType::findOrFail($id);
        $oldData = $deductionType->toArray();

        $deductionType->name                   = $validated['name'];
        $deductionType->calculation_method     = $validated['calculation_method'];
        $deductionType->default_amount         = $validated['default_amount'];
        $deductionType->is_taxable             = $validated['is_taxable'];
        $deductionType->apply_to_all_employees = $validated['apply_to_all_employees'];
        $deductionType->description = $validated['description'] ?? null;
        $deductionType->updated_by_id = Auth::user()->id;
        $deductionType->updated_by_type = get_class(Auth::user());

        $deductionType->save();

        // Log Starts
        UserLog::create([
            'user_id'        => Auth::user()->id,
            'global_user_id' => null,
            'module'         => 'Deduction Types',
            'action'         => 'Update',
            'description'    => 'Updated Deduction Type (ID: ' . $deductionType->id . ', Name: ' . $deductionType->name . ').',
            'affected_id'    => $deductionType->id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($deductionType->toArray()),
        ]);

        // Return JSON response with 200 status
        return response()->json([
            'message'      => 'Deduction type updated successfully.',
            'deduction_type' => $deductionType,
        ], 200);
    }

    // Deduction Delete Method
    public function deductionDelete($id)
    {
        $deductionType = DeductionType::findOrFail($id);

        // Log Starts
        UserLog::create([
            'user_id'        => Auth::user()->id,
            'global_user_id' => null,
            'module'         => 'Deduction Types',
            'action'         => 'Delete',
            'description'    => 'Deleted Deduction Type (ID: ' . $deductionType->id . ', Name: ' . $deductionType->name . ').',
            'affected_id'    => $deductionType->id,
            'old_data'       => json_encode($deductionType->toArray()),
            'new_data'       => null,
        ]);

        // Delete the deduction type
        $deductionType->delete();

        // Return JSON response with 200 status
        return response()->json([
            'message' => 'Deduction type deleted successfully.',
        ], 200);
    }

    // Deduction User Index
    public function userDeductionIndex(Request $request)
    {
        $deductionTypes = DeductionType::all();
        $branches = Branch::all();
        $departments = Department::all();
        $designations = Designation::all();
        $userDeductions = UserDeduction::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User Deductions Index',
                'data' => [
                    'deduction_types' => $deductionTypes,
                    'branches' => $branches,
                    'departments' => $departments,
                    'designations' => $designations,
                    'user_deductions' => $userDeductions
                ]
            ]);
        }

        return view(
            'tenant.payroll.payroll-items.deduction.deductionuser',
            compact('deductionTypes', 'branches', 'departments', 'designations', 'userDeductions')
        );
    }

    // User Deduction Store/Assigning Method
    public function userDeductionAssign(Request $request)
    {
        $validated = $request->validate([
            'type'                 => ['required', Rule::in(['include', 'exclude'])],
            'deduction_type_id'    => ['required', 'integer', 'exists:deduction_types,id'],
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
                'date',
            ],
            'effective_end_date'   => [
                'nullable',
                'date',
                'after_or_equal:effective_start_date'
            ],
        ]);

        $userIds = $validated['user_id'];
        $deductionTypeId = $validated['deduction_type_id'];

        $alreadyAssigned = UserDeduction::where('deduction_type_id', $deductionTypeId)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->all();

        if (!empty($alreadyAssigned)) {
            $ids = implode(', ', $alreadyAssigned);
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => [
                    'user_id' => ["Selected deduction type is already assigned to a user."]
                ]
            ], 422);
        }

        $createdRecords = [];
        foreach ($userIds as $uid) {
            $userDeduction = new UserDeduction();
            $userDeduction->user_id               = $uid;
            $userDeduction->deduction_type_id     = $validated['deduction_type_id'];
            $userDeduction->type                  = $validated['type'];
            $userDeduction->amount                = $validated['amount'] ?? null;
            $userDeduction->frequency             = $validated['frequency'];
            $userDeduction->effective_start_date  = $validated['effective_start_date'] ?? null;
            $userDeduction->effective_end_date    = $validated['effective_end_date'] ?? null;
            $userDeduction->status                = 'active';

            // Track “created_by” polymorphic (web vs global guard)
            if (Auth::guard('web')->check()) {
                $userDeduction->created_by_type = get_class(Auth::guard('web')->user());
                $userDeduction->created_by_id   = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $userDeduction->created_by_type = get_class(Auth::guard('global')->user());
                $userDeduction->created_by_id   = Auth::guard('global')->id();
            }

            $userDeduction->save();
            $createdRecords[] = $userDeduction;

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
                'module'         => 'User Deductions',
                'action'         => 'Assign',
                'description'    => 'Assigned deduction_type_id ' . $validated['deduction_type_id']
                    . ' to user_id ' . $uid
                    . ' (type=' . $validated['type'] . ').',
                'affected_id'    => $userDeduction->id,
                'old_data'       => null,
                'new_data'       => json_encode($userDeduction->toArray()),
            ]);
        }

        // 5) Return JSON success, with 201 Created
        return response()->json([
            'message'       => 'Deduction assignments created for ' . count($createdRecords) . ' user(s).',
            'assigned_ids'  => array_column($createdRecords, 'id'),
        ], 201);
    }

    // User Deduction Update Method
    public function userDeductionUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'type'                 => ['required', Rule::in(['include', 'exclude'])],
            'deduction_type_id'    => ['required', 'integer', 'exists:deduction_types,id'],
            'amount'               => ['nullable', 'numeric', 'min:0'],
            'frequency'            => ['required', Rule::in(['every_payroll', 'every_other', 'one_time'])],
            'effective_start_date' => [
                Rule::requiredIf(fn() => $request->input('type') === 'include'),
                'nullable',
                'date',
            ],
            'effective_end_date'   => ['nullable', 'date', 'after_or_equal:effective_start_date'],
            'status'               => ['sometimes', Rule::in(['active', 'inactive', 'completed', 'hold'])]
        ]);

        $userDeduction = UserDeduction::findOrFail($id);

        $oldData = $userDeduction->toArray();

        $userDeduction->type                 = $validated['type'];
        $userDeduction->deduction_type_id    = $validated['deduction_type_id'];
        $userDeduction->amount                = $validated['amount'] ?? null;
        $userDeduction->frequency             = $validated['frequency'];
        $userDeduction->effective_start_date  = $validated['effective_start_date'] ?? null;
        $userDeduction->effective_end_date    = $validated['effective_end_date'] ?? null;
        if (isset($validated['status'])) {
            $userDeduction->status = $validated['status'];
        }

        if (Auth::guard('web')->check()) {
            $userDeduction->updated_by_type = get_class(Auth::guard('web')->user());
            $userDeduction->updated_by_id   = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $userDeduction->updated_by_type = get_class(Auth::guard('global')->user());
            $userDeduction->updated_by_id   = Auth::guard('global')->id();
        }

        $userDeduction->save();

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
            'module'         => 'User Deductions',
            'action'         => 'Update',
            'description'    => 'Updated UserDeduction ID ' . $userDeduction->id,
            'affected_id'    => $userDeduction->id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($userDeduction->toArray()),
        ]);

        return response()->json([
            'message'      => 'Assigned deduction updated successfully.',
            'user_deduction' => $userDeduction,
        ], 200);
    }

    // User Deduction Delete Method
    public function userDeductionDelete($id)
    {
        $userDeduction = UserDeduction::findOrFail($id);

        // Log Starts
        UserLog::create([
            'user_id'        => Auth::user()->id,
            'global_user_id' => null,
            'module'         => 'User Deductions',
            'action'         => 'Delete',
            'description'    => 'Deleted User Deduction ID: ' . $userDeduction->id,
            'affected_id'    => $userDeduction->id,
            'old_data'       => json_encode($userDeduction->toArray()),
            'new_data'       => null,
        ]);

        // Delete the user deduction
        $userDeduction->delete();

        // Return JSON response with 200 status
        return response()->json([
            'message' => 'User deduction deleted successfully.',
        ], 200);
    }
}
