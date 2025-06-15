<?php

namespace App\Http\Controllers\Tenant\Branch;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function branchIndex(Request $request)
    {
        $userTenantId = Auth::user()->tenant_id;

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
        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255',
            'contact_number'               => 'nullable|string|max:20',
            'branch_type'                  => 'required|in:main,sub',
            'address'                      => 'nullable|string|max:500',

            'sss_contribution_type'        => 'required|in:system,fixed,manual,none',
            'fixed_sss_amount'             => 'nullable|required_if:sss_contribution_type,fixed|numeric',

            'philhealth_contribution_type' => 'required|in:system,fixed,manual,none',
            'fixed_philhealth_amount'      => 'nullable|required_if:philhealth_contribution_type,fixed|numeric',

            'pagibig_contribution_type'    => 'required|in:system,fixed,manual,none',
            'fixed_pagibig_amount'         => 'nullable|required_if:pagibig_contribution_type,fixed|numeric',

            'withholding_tax_type'         => 'required|in:system,fixed,manual,none',
            'fixed_withholding_tax_amount' => 'nullable|required_if:withholding_tax_type,fixed|numeric',

            'branch_logo'                  => 'nullable|image|mimes:jpg,jpeg,png|max:4096'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Prepare data for DB save
        $data = $request->except('branch_logo');

        if ($request->hasFile('branch_logo')) {
            $logoPath = $request->file('branch_logo')->store('branch_logos', 'public');
            $data['branch_logo'] = $logoPath;
        }

        $branch = Branch::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Branch successfully created.',
            'data'    => $branch,
        ]);
    }
}
