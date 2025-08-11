<?php

namespace App\Http\Controllers\Tenant\Policy;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Policy;
use App\Models\UserLog;
use App\Models\PolicyTarget;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PolicyController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }

        return Auth::user();
    }

      public function filter(Request $request)
     {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(12);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $targetType = $request->input('targetType');

        $query  = $accessData['policy'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('effective_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($targetType) {
            $query->whereHas('targets', function ($q) use ($targetType) {
                $q->where('target_type', $targetType);
            });
        }
        $policies = $query->get();
        $html = view('tenant.employee.policy_filter', compact('policies', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function policyIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(12);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $policies = $accessData['policy']->get();
        $branches = $accessData['branches']->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the policy index endpoint.',
                'status' => 'success',
            ]);
        }

        return view('tenant.employee.policy', [
            'branches' => $branches,
            'policies' => $policies,
            'permission' => $permission
        ]);
    }

    public function policyCreate(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(12);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ],403);
        }

        $request->validate([
            'policy_title' => 'required|string|max:255',
            'policy_content' => 'nullable|string',
            'effective_date' => 'required|date',
            'target_type' => 'required|in:company-wide,branch,department,employee',
            'attachment_path' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        // Handle file upload if provided
        $attachmentPath = null;
        if ($request->hasFile('attachment_path')) {
            $attachmentPath = $request->file('attachment_path')->store('policy_attachment', 'public');
        }

        // Create the policy
        $policy = Policy::create([
            'tenant_id' => $tenantId,
            'policy_title' => $request->input('policy_title'),
            'effective_date' => $request->input('effective_date'),
            'policy_content' => $request->input('policy_content'),
            'target_type' => $request->input('target_type'),
            'attachment_path' => $attachmentPath,
            'created_by' => Auth::guard('global')->check() ? null : $authUser->id,
        ]);

        // Handle policy targets
        $targets = [];

        $targetType = $request->input('target_type');

        if ($targetType == 'branch') {
            $branch_ids = $request->input('branch_id', []);
            foreach ($branch_ids as $id) {
                if ($id) {
                    $targets[] = [
                        'policy_id' => $policy->id,
                        'target_type' => 'branch',
                        'target_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        } elseif ($targetType == 'department') {
            $department_ids = $request->input('department_id', []);
            foreach ($department_ids as $id) {
                if ($id) {
                    $targets[] = [
                        'policy_id' => $policy->id,
                        'target_type' => 'department',
                        'target_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        } elseif ($targetType == 'employee') {
            $employee_ids = $request->input('employee_id', []);
            foreach ($employee_ids as $id) {
                if ($id) {
                    $targets[] = [
                        'policy_id' => $policy->id,
                        'target_type' => 'user',
                        'target_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        } else {
            $targets[] = [
                'policy_id' => $policy->id,
                'target_type' => 'company-wide',
                'target_id' => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($targets)) {
            PolicyTarget::insert($targets);
        }

        $empId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
        $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Company Policy',
            'action' => 'Create',
            'description' => 'Created Policy: "' . $policy->policy_title . '" with Effective Date: ' . $policy->effective_date,
            'affected_id' => $policy->id,
            'old_data' => null,
            'new_data' => json_encode($policy->toArray()),
        ]);

        return response()->json([
            'message' => 'Policy created successfully.',
            'status' => 'success',
            'policy' => $policy,
        ], 201);
    }

    // Policy Delete
    public function policyDelete(Request $request, $id)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(12);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ]);
        }

        $policy = Policy::findOrFail($id);
        $policy->delete();

        $empId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
        $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Company Policy',
            'action' => 'Delete',
            'description' => 'Deleted Policy: "' . $policy->policy_title . '" with Effective Date: ' . $policy->effective_date,
            'affected_id' => $policy->id,
            'old_data' => json_encode($policy->toArray()),
            'new_data' => null,
        ]);


        return response()->json([
            'message' => 'Policy deleted successfully.',
            'status' => 'success',
        ]);
    }

    // Remove Policy Target
    public function removeTarget(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|exists:policy_targets,id',
            'policy_id' => 'required|exists:policies,id',
        ]);

        $target = PolicyTarget::where('id', $request->id)
            ->where('policy_id', $request->policy_id)
            ->first();

        if ($target) {
            $target->delete();
            return response()->json([
                'success' => true,
                'message' => 'Target removed successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The selected target id is invalid.'
        ], 404);
    }

    // Policy Edit
    public function policyUpdate(Request $request, $policyId)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(12);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ]);
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'policy_title' => 'required|string|max:255',
            'effective_date' => 'required|date',
            'policy_content' => 'nullable|string',
            'attachment_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx',
            'target_type' => 'nullable|in:company-wide,branch,department,employee',
            'branch_id' => 'nullable|array',
            'department_id' => 'nullable|array',
            'employee_id' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $policy = Policy::findOrFail($policyId);
        } catch (ModelNotFoundException $e) {
            Log::error('Policy with ID ' . $policyId . ' not found. Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Policy not found'], 404);
        }

        $policy->policy_title = $request->input('policy_title');
        $policy->effective_date = $request->input('effective_date');
        $policy->policy_content = $request->input('policy_content', '');

        if ($request->hasFile('attachment_path')) {
            $file = $request->file('attachment_path');
            $path = $file->store('policies', 'public');
            $policy->attachment_path = $path;
        }

        $policy->save();

        $targets = [];
        $targetType = $request->input('target_type');
        $tenantId = Auth::user()->tenant_id;

        if ($targetType === 'company-wide') {
            PolicyTarget::where('policy_id', $policy->id)
                ->where('target_type', '!=', 'company-wide')
                ->delete();

            $exists = PolicyTarget::where([
                'policy_id' => $policy->id,
                'target_type' => 'company-wide',
                'target_id' => $tenantId,
            ])->exists();
            if (!$exists) {
                $targets[] = [
                    'policy_id' => $policy->id,
                    'target_type' => 'company-wide',
                    'target_id' => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } else {
            PolicyTarget::where('policy_id', $policy->id)
                ->where('target_type', 'company-wide')
                ->delete();

            if ($targetType === 'branch' || $request->has('branch_ids')) {
                $branch_ids = $request->input('branch_ids', []);
                foreach ($branch_ids as $id) {
                    if ($id) {
                        $exists = PolicyTarget::where([
                            'policy_id' => $policy->id,
                            'target_type' => 'branch',
                            'target_id' => $id,
                        ])->exists();
                        if (!$exists) {
                            $targets[] = [
                                'policy_id' => $policy->id,
                                'target_type' => 'branch',
                                'target_id' => $id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
            }
            if ($targetType === 'department' || $request->has('department_ids')) {
                $department_ids = $request->input('department_ids', []);
                foreach ($department_ids as $id) {
                    if ($id) {
                        $exists = PolicyTarget::where([
                            'policy_id' => $policy->id,
                            'target_type' => 'department',
                            'target_id' => $id,
                        ])->exists();
                        if (!$exists) {
                            $targets[] = [
                                'policy_id' => $policy->id,
                                'target_type' => 'department',
                                'target_id' => $id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
            }
            if ($targetType === 'employee' || $request->has('employee_ids')) {
                $employee_ids = $request->input('employee_ids', []);
                foreach ($employee_ids as $id) {
                    if ($id) {
                        $exists = PolicyTarget::where([
                            'policy_id' => $policy->id,
                            'target_type' => 'user',
                            'target_id' => $id,
                        ])->exists();
                        if (!$exists) {
                            $targets[] = [
                                'policy_id' => $policy->id,
                                'target_type' => 'user',
                                'target_id' => $id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
            }
        }

        if (count($targets)) {
            PolicyTarget::insert($targets);
        }


        return response()->json(['message' => 'Policy updated successfully!']);
    }
}
