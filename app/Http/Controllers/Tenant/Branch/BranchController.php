<?php

namespace App\Http\Controllers\Tenant\Branch;

use App\Models\Branch;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function branchIndex(Request $request)
    {
        $user = Auth::user();
        $userTenantId = $user ? $user->tenant_id : null;


        $branches = Branch::where('tenant_id', $userTenantId)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the branch index endpoint.',
                'status' => 'success',
                'branches' => $branches,
            ]);
        }

        return view('tenant.branch.branch-grid', [
            'branches' => $branches,
        ]);
    }

    public function branchCreate(Request $request)
    {
        $authUserTenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255',
            'contact_number'               => 'nullable|string|max:20',
            'branch_type'                  => 'required|in:main,sub',
            'location'                      => 'nullable|string|max:500',

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

            'salary_computation_type' => 'nullable|in:monthly,semi-monthly,bi-weekly,weekly',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Check if a 'main' branch already exists for this tenant
        if ($request->branch_type === 'main') {
            $mainBranchExists = Branch::where('tenant_id', $authUserTenantId)
                ->where('branch_type', 'main')
                ->exists();

            if ($mainBranchExists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'A main branch already exists for this tenant.',
                ], 422);
            }
        }

        // Prepare data for DB save
        $data = $request->except('branch_logo');

        if ($request->hasFile('branch_logo')) {
            $logoPath = $request->file('branch_logo')->store('branch_logos', 'public');
            $data['branch_logo'] = $logoPath;
        }

        $data['tenant_id'] = $authUserTenantId;
        $branch = Branch::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Branch successfully created.',
            'data'    => $branch,
        ]);
    }

    // Edit branch
    public function branchEdit(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $oldData = $branch->toArray();

        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255',
            'contact_number'               => 'nullable|string|max:20',
            'branch_type'                  => 'required|in:main,sub',
            'location'                     => 'nullable|string|max:500',

            'sss_contribution_type'        => 'required|in:system,fixed,manual,none',
            'fixed_sss_amount'             => 'nullable|required_if:sss_contribution_type,fixed|numeric',

            'philhealth_contribution_type' => 'required|in:system,fixed,manual,none',
            'fixed_philhealth_amount'      => 'nullable|required_if:philhealth_contribution_type,fixed|numeric',

            'pagibig_contribution_type'    => 'required|in:system,fixed,manual,none',
            'fixed_pagibig_amount'         => 'nullable|required_if:pagibig_contribution_type,fixed|numeric',

            'withholding_tax_type'         => 'required|in:system,fixed,manual,none',
            'fixed_withholding_tax_amount' => 'nullable|required_if:withholding_tax_type,fixed|numeric',

            'worked_days_per_year'         => 'required|in:313,261,300,365,custom',
            'custom_worked_days'           => 'nullable|required_if:worked_days_per_year,custom|numeric',

            'basic_salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|in:hourly_rate,monthly_fixed,daily_rate',
            'branch_logo'                  => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'salary_computation_type' => 'nullable|in:monthly,semi-monthly,bi-weekly,weekly',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('branch_logo');

        if ($request->hasFile('branch_logo')) {
            $logoPath = $request->file('branch_logo')->store('branch_logos', 'public');
            $data['branch_logo'] = $logoPath;
        }

        $branch->update($data);

        // Logging
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

        return response()->json([
            'status' => 'success',
            'message' => 'Branch successfully updated.',
            'data' => $branch
        ]);
    }

    // Branch delete
    public function branchDelete($id)
    {
        $branch = Branch::findOrFail($id);

        // Check if the branch has any associated employment details
        if ($branch->employmentDetail()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete branch with existing employment details.'
            ], 422);
        }

        // Logging
        $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
        $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Branch',
            'action'         => 'Delete',
            'description'    => 'Deleted Branch "' . $branch->name . '"',
            'affected_id'    => $branch->id,
            'old_data'       => json_encode($branch->toArray()),
            'new_data'       => null,
        ]);

        $branch->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Branch successfully deleted.'
        ]);
    }
}
