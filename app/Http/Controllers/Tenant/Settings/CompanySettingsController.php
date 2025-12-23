<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CompanySettingsController extends Controller
{
    /**
     * Display the company settings page.
     */
    public function companySettingsIndex()
    {
        // Check for global user or regular user
        if (Auth::guard('global')->check()) {
            $authUser = Auth::guard('global')->user();
        } else {
            $authUser = Auth::user();
        }

        if (!$authUser || !$authUser->tenant_id) {
            abort(403, 'Unauthorized access');
        }

        $tenantId = $authUser->tenant_id;
        $permission = PermissionHelper::get(43);

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        return view('tenant.settings.company-settings', compact('tenant', 'permission'));
    }

    /**
     * Update the company/tenant code.
     */
    public function updateTenantCode(Request $request)
    {
        // Check for global user or regular user first
        if (Auth::guard('global')->check()) {
            $authUser = Auth::guard('global')->user();
        } else {
            $authUser = Auth::user();
        }

        if (!$authUser || !$authUser->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tenant_code' => 'required|string|max:50|alpha_dash|unique:tenants,tenant_code,' . $authUser->tenant_id,
        ], [
            'tenant_code.required' => 'Company code is required.',
            'tenant_code.alpha_dash' => 'Company code can only contain letters, numbers, dashes and underscores.',
            'tenant_code.unique' => 'This company code is already taken.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $tenantId = $authUser->tenant_id;

            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found.',
                ], 404);
            }

            $oldCode = $tenant->tenant_code;
            $tenant->tenant_code = strtoupper($request->tenant_code);
            $tenant->save();

            Log::info('Tenant code updated', [
                'tenant_id' => $tenantId,
                'old_code' => $oldCode,
                'new_code' => $tenant->tenant_code,
                'updated_by' => $authUser->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Company code updated successfully.',
                'data' => [
                    'tenant_code' => $tenant->tenant_code,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update tenant code', [
                'error' => $e->getMessage(),
                'tenant_id' => $authUser->tenant_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update company code. Please try again.',
            ], 500);
        }
    }

    /**
     * Update company information (name, email, address).
     */
    public function updateCompanyInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'nullable|email|max:255',
            'tenant_address' => 'nullable|string|max:500',
        ], [
            'tenant_name.required' => 'Company name is required.',
            'tenant_email.email' => 'Please provide a valid email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            // Check for global user or regular user
            if (Auth::guard('global')->check()) {
                $authUser = Auth::guard('global')->user();
            } else {
                $authUser = Auth::user();
            }

            if (!$authUser || !$authUser->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            $tenantId = $authUser->tenant_id;

            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found.',
                ], 404);
            }

            $tenant->tenant_name = $request->tenant_name;
            $tenant->tenant_email = $request->tenant_email;
            $tenant->tenant_address = $request->tenant_address;
            $tenant->save();

            Log::info('Company information updated', [
                'tenant_id' => $tenantId,
                'updated_by' => $authUser->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Company information updated successfully.',
                'data' => [
                    'tenant_name' => $tenant->tenant_name,
                    'tenant_email' => $tenant->tenant_email,
                    'tenant_address' => $tenant->tenant_address,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update company information', [
                'error' => $e->getMessage(),
                'tenant_id' => $authUser->tenant_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update company information. Please try again.',
            ], 500);
        }
    }
}
