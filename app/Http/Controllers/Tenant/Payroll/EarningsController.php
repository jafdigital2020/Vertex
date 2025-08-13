<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EarningType;
use App\Models\UserEarning;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class EarningsController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }

        return Auth::user();
    }

    public function earningFilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $sort_by = $request->input('sort_by');

        $query = $accessData['earningType'];

        if ($sort_by === 'recent') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort_by === 'asc') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort_by === 'desc') {
            $query->orderBy('created_at', 'desc');
        }

        $earningTypes = $query->get();

        $html = view('tenant.payroll.payroll-items.earning.earnings_filter', compact('earningTypes', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function earningIndex(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $earningTypes = $accessData['earningType']->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Earnings Index',
                'data' => [
                    'earning_types' => $earningTypes
                ]
            ]);
        }

        return view('tenant.payroll.payroll-items.earning.earnings', compact('earningTypes', 'permission'));
    }

    // Earning Store Method
    public function earningStore(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

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
        $earningType->tenant_id = $tenantId;
        $earningType->name                   = $validated['name'];
        $earningType->calculation_method     = $validated['calculation_method'];
        $earningType->default_amount         = $validated['default_amount'];
        $earningType->is_taxable             = $validated['is_taxable'];
        $earningType->apply_to_all_employees = $validated['apply_to_all_employees'];
        $earningType->description = $validated['description'] ?? null;
        $earningType->created_by_id = $authUserId;
        $earningType->created_by_type = get_class($authUser);

        $earningType->save();

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
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }


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
        $earningType->updated_by_id =  $authUserId;
        $earningType->updated_by_type =  get_class($authUser);

        $earningType->save();

        // Log Starts
        UserLog::create([
            'user_id'        => $authUserId,
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

        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $earningType = EarningType::findOrFail($id);
        $earningType->delete();

        // Log Starts
        UserLog::create([
            'user_id'        =>  $authUserId,
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
    public function userEarningsFilter(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  =  $accessData['userEarnings'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->where(function ($q) use ($start, $end) {
                    $q->whereDate('effective_start_date', '<=', $end)
                        ->whereDate('effective_end_date', '>=', $start);
                });
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }

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
        if ($status) {
            $query->where('status', $status);
        }

        $userEarnings = $query->get();

        $html = view('tenant.payroll.payroll-items.earning.earninguser_filter', compact('userEarnings', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function userEarningIndex(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        $earningTypes = $accessData['earningType']->get();
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $userEarnings = $accessData['userEarnings']->get();

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
            compact('earningTypes', 'branches', 'departments', 'designations', 'userEarnings', 'permission')
        );
    }

    // User Earning Store/Assigning Method
    public function userEarningAssign(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

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

        $alreadyAssigned = $accessData['userEarnings']->where('earning_type_id', $earningTypeId)
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
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

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
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $userEarning = UserEarning::findOrFail($id);
        $userEarning->delete();

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
