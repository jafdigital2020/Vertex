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
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;

class BranchController extends Controller
{
    use ResponseTimingTrait;

    private function logBranchError(
        string $errorType,
        string $message,
        Request $request,
        ?float $startTime = null,
        ?array $responseData = null
    ): void {
        try {
            $processingTime = null;
            $timingData = null;

            if ($responseData && isset($responseData['timing'])) {
                $timingData = $responseData['timing'];
                $processingTime = $timingData['server_processing_time_ms'] ?? null;
            } elseif ($startTime) {
                $timingData = $this->getTimingData($startTime);
                $processingTime = $timingData ? $timingData['server_processing_time_ms'] : null;
            }

            $errorMessage = sprintf("[%s] %s", $errorType, $message);

            // Get authenticated user
            $authUser = $this->authUser();

            // ===== DEBUG LOG START =====
            Log::debug('logPayrollError - Auth User & Tenant Info', [
                'auth_user_id' => $authUser?->id,
                'auth_user_tenant_id' => $authUser?->tenant_id,
                'tenant_loaded' => isset($authUser->tenant),
                'tenant_name_from_relation' => $authUser->tenant?->tenant_name ?? null,
            ]);

            $clientName = $authUser->tenant?->tenant_name ?? 'Unknown Tenant';
            $clientId   = $authUser->tenant?->id ?? null;

            Log::debug('logPayrollError - Sending to ErrorLogger', [
                'client_name' => $clientName,
                'client_id' => $clientId,
                'error_message' => $errorMessage,
            ]);
            // ===== DEBUG LOG END =====

            // Log to remote system
            ErrorLogger::logToRemoteSystem(
                $errorMessage,
                $clientName,
                $clientId,
                $timingData
            );

            // Local Laravel log
            Log::error($errorType, [
                'clean_message' => $message,
                'full_error' => $responseData['full_error'] ?? null,
                'user_id' => $authUser->id ?? null,
                'client_name' => $clientName,
                'client_id' => $clientId,
                'processing_time_ms' => $processingTime,
                'url' => $request->fullUrl(),
                'request_data' => $request->except(['password', 'token', 'api_key'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log error', [
                'original_error' => $message,
                'logging_error' => $e->getMessage()
            ]);
        }
    }


    public function authUser()
    {
        $user = null;
        
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        } else {
            $user = Auth::guard('web')->user();
        }
        
        // Load tenant relationship if user exists
        if ($user) {
            $user->load('tenant');
        }
        
        return $user;
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
        $startTime = microtime(true);
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

            $cleanMessage = "An error occurred while creating the branch.";

            $this->logBranchError(
                '[ERROR_CREATING_BRANCH]',
                $cleanMessage,
                $request,
                $startTime
            );

            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
     }

    public function branchEdit(Request $request, $id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
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

            $cleanMessage = "An error occurred while updating the branch.";

            $this->logBranchError(
                '[ERROR_UPDATING_BRANCH]',
                $cleanMessage,
                $request,
                $startTime
            );

            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }

    public function branchDelete(Request $request,$id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
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

            $cleanMessage = "An error occurred while deleting the branch.";

            $this->logBranchError(
                '[ERROR_DELETING_BRANCH]',
                $cleanMessage,
                $request,
                $startTime
            );

            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }
}
