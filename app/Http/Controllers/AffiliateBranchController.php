<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;

class AffiliateBranchController extends Controller
{
    public function createAffiliateIndex()
    {
        return view('affiliate.branch.register');
    }

    
    public function registerBranch(Request $request)
   {
    // You can split the validation into branch and user parts if needed
    $validator = \Validator::make($request->all(), [
        // Branch fields
        'name' => 'required|string|max:255',
        'branch_type' => 'required|in:main,sub',
        'location' => 'required|string|max:500',
        'tenant_id' => 'required|exists:tenants,id',
        'salary_computation_type' => 'required|in:monthly,semi-monthly,bi-weekly,weekly',
        'sss_contribution_type' => 'required|in:system,fixed,manual,none',
        'philhealth_contribution_type' => 'required|in:system,fixed,manual,none',
        'pagibig_contribution_type' => 'required|in:system,fixed,manual,none',
        'withholding_tax_type' => 'required|in:system,fixed,manual,none',
        'worked_days_per_year' => 'required',

        // User fields
        'username' => 'required|string|max:255|unique:users,username',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'role_id' => 'required|exists:roles,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Transaction to ensure both user and branch are created together
    \DB::beginTransaction();

    try {
        // Create Branch
        $branchData = $request->only([
            'name', 'contact_number', 'branch_type', 'location',
            'sss_contribution_type', 'fixed_sss_amount',
            'philhealth_contribution_type', 'fixed_philhealth_amount',
            'pagibig_contribution_type', 'fixed_pagibig_amount',
            'withholding_tax_type', 'fixed_withholding_tax_amount',
            'basic_salary', 'salary_type', 'salary_computation_type',
            'branch_tin', 'wage_order', 'sss_contribution_template',
            'worked_days_per_year', 'tenant_id'
        ]);

        if ($request->hasFile('branch_logo')) {
            $branchData['branch_logo'] = $request->file('branch_logo')->store('branch_logos', 'public');
        }

        if ($branchData['branch_type'] === 'main') {
            $mainBranchExists = Branch::where('tenant_id', $branchData['tenant_id'])
                ->where('branch_type', 'main')
                ->exists();
            if ($mainBranchExists) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['main_branch' => ['A main branch already exists.']]
                ], 422);
            }
        }

        $branch = Branch::create($branchData);

        // Create User
        $userData = [
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'tenant_id' => $request->tenant_id,
            // Optional: Link user to the branch directly if you have a `branch_id` in users table
            // 'branch_id' => $branch->id
        ];

        $user = User::create($userData);

        \DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Branch and user successfully created.',
            'data' => [
                'branch' => $branch,
                'user' => $user,
            ],
        ]);
    } catch (\Exception $e) {
        \DB::rollBack();

        \Log::error('Branch/User creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while creating the branch and user.',
        ], 500);
    }
   }

}