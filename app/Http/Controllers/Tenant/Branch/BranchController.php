<?php

namespace App\Http\Controllers\Tenant\Branch;

use App\Models\Branch;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\SssContributionTable;
use App\Models\UserPermissionAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class BranchController extends Controller
{

     public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function branchIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(8);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();

        // Get unique years from the "year" column in SssContributionTable
        $sssYears = SssContributionTable::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the branch index endpoint.',
                'status' => 'success',
                'branches' => $branches,
                'sssYears' => $sssYears,
            ]);
        }

        return view('tenant.branch.branch-grid', [
            'branches' => $branches,
            'permission'=> $permission,
            'sssYears' => $sssYears,
        ]);
    }

    public function branchCreate(Request $request)
    {
        $authUser = $this->authUser();
        $authUserTenantId = $authUser->tenant_id;
        $permission = PermissionHelper::get(8);

        if (!in_array('Create', $permission)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have the permission to create.'
                ] );
        }

        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255',
            'contact_number'               => 'nullable|string|max:20',
            'branch_type'                  => 'required|in:main,sub',
            'location'                      => 'required|string|max:500',

            'sss_contribution_type'        => 'required|in:system,fixed,manual,none',
            'fixed_sss_amount'             => 'nullable|required_if:sss_contribution_type,fixed|numeric',

            'philhealth_contribution_type' => 'required|in:system,fixed,manual,none',
            'fixed_philhealth_amount'      => 'nullable|required_if:philhealth_contribution_type,fixed|numeric',

            'pagibig_contribution_type'    => 'required|in:system,fixed,manual,none',
            'fixed_pagibig_amount'         => 'nullable|required_if:pagibig_contribution_type,fixed|numeric',

            'withholding_tax_type'         => 'required|in:system,fixed,manual,none',
            'fixed_withholding_tax_amount' => 'nullable|required_if:withholding_tax_type,fixed|numeric',

            'basic_salary'                  => 'nullable|numeric|min:0',
            'salary_type'                   => 'nullable|in:hourly_rate,monthly_fixed,daily_rate',
            'branch_logo'                  => 'nullable|image|mimes:jpg,jpeg,png|max:4096',

            'salary_computation_type'      => 'required|in:monthly,semi-monthly,bi-weekly,weekly',
            'branch_tin'                  => 'nullable|string|max:30',
            'wage_order'                  => 'nullable|string|max:255',
            'sss_contribution_template'  => 'required|string|max:4',
            'worked_days_per_year' => 'required'
        ],[
            'location.required' => 'The address field is required.',
            'worked_days_per_year' => 'The work days per year field is required.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($request->branch_type === 'main') {
            $mainBranchExists = Branch::where('tenant_id', $authUserTenantId)
                ->where('branch_type', 'main')
                ->exists();

            if ($mainBranchExists) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['main_branch' => ['A main branch already exists.']]
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $data = $request->except('branch_logo');

            if ($request->hasFile('branch_logo')) {
                $logoPath = $request->file('branch_logo')->store('branch_logos', 'public');
                $data['branch_logo'] = $logoPath;
            }

            $data['tenant_id'] = $authUserTenantId;
            $branch = Branch::create($data);
            $branchId = $branch->id;

            $user_data_access = $authUser->userPermission->data_access_id ?? null;
            if ($user_data_access == 1) {
                $user_permission_data_access = UserPermissionAccess::where( 'user_permission_id',$authUser->userPermission->id)->first();
                $accessIds = explode(',', $user_permission_data_access->access_ids ?? '');
                $accessIds = array_map('intval', $accessIds);
                if (!in_array($branchId, $accessIds)) {
                    $accessIds[] = $branchId;
                }
                $user_permission_data_access->access_ids = implode(',', $accessIds);
                $user_permission_data_access->save();
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Branch successfully created.',
                'data'    => $branch,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Branch creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while creating the branch.',
            ], 500);
        }
     }

    public function branchEdit(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $oldData = $branch->toArray();
        $permission = PermissionHelper::get(8);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255',
            'contact_number'                => 'nullable|string|max:20',
            'branch_type'                   => 'required|in:main,sub',
            'location'                      => 'nullable|string|max:500',

            'sss_contribution_type'         => 'required|in:system,fixed,manual,none',
            'fixed_sss_amount'              => 'nullable|required_if:sss_contribution_type,fixed|numeric',

            'philhealth_contribution_type'  => 'required|in:system,fixed,manual,none',
            'fixed_philhealth_amount'       => 'nullable|required_if:philhealth_contribution_type,fixed|numeric',

            'pagibig_contribution_type'     => 'required|in:system,fixed,manual,none',
            'fixed_pagibig_amount'          => 'nullable|required_if:pagibig_contribution_type,fixed|numeric',

            'withholding_tax_type'          => 'required|in:system,fixed,manual,none',
            'fixed_withholding_tax_amount'  => 'nullable|required_if:withholding_tax_type,fixed|numeric',

            'worked_days_per_year'          => 'required|in:313,261,300,365,custom',
            'custom_worked_days'            => 'nullable|required_if:worked_days_per_year,custom|numeric',

            'basic_salary'                  => 'nullable|numeric|min:0',
            'salary_type'                   => 'nullable|in:hourly_rate,monthly_fixed,daily_rate',
            'branch_logo'                   => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'salary_computation_type'       => 'required|in:monthly,semi-monthly,bi-weekly,weekly',
            'wage_order'                    => 'nullable|string|max:255',
            'branch_tin'                    => 'nullable|string|max:30',
            'sss_contribution_template'     => 'nullable|string|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent multiple main branches per tenant
        if ($request->branch_type === 'main') {
            $mainBranchExists = Branch::where('tenant_id', $branch->tenant_id)
                ->where('branch_type', 'main')
                ->where('id', '!=', $branch->id)
                ->exists();

            if ($mainBranchExists) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['main_branch' => ['A main branch already exists.']]
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $data = $request->except('branch_logo');

            if ($request->hasFile('branch_logo')) {
                $logoPath = $request->file('branch_logo')->store('branch_logos', 'public');
                $data['branch_logo'] = $logoPath;
            }

            $branch->update($data);

            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Branch',
                'action'         => 'Update',
                'description'    => 'Updated Branch "' . $branch->name . '"',
                'affected_id'    => $branch->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => json_encode($branch->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Branch successfully updated.',
                'data' => $branch
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Branch update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'branch_id' => $id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the branch.'
            ], 500);
        }
    }

    public function branchDelete($id)
    {
        $branch = Branch::findOrFail($id);
        $permission = PermissionHelper::get(8);

        if (!in_array('Delete', $permission)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have the permission to delete.'
                ] );
        }
        if ($branch->employmentDetail()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete branch with existing employment details.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $oldData = $branch->toArray();

            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Branch',
                'action'         => 'Delete',
                'description'    => 'Deleted Branch "' . $branch->name . '"',
                'affected_id'    => $branch->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => null,
            ]);

            $branch->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Branch successfully deleted.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Branch deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'branch_id' => $id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the branch.'
            ], 500);
        }
    }
}
