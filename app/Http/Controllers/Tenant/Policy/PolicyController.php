<?php

namespace App\Http\Controllers\Tenant\Policy;

use App\Models\Branch;
use App\Models\Policy;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\PolicyTarget;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PolicyController extends Controller
{   

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    } 
    public function policyIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;
        $permission = PermissionHelper::get(12);  

        $policies = Policy::where('tenant_id', $tenantId)
            ->orderBy('effective_date', 'desc')
            ->get();

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('status', '1')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the policy index endpoint.',
                'status' => 'success',
            ]);
        }

        return view('tenant.employee.policy', [
            'branches' => $branches,
            'policies' => $policies,
            'permission' => $permission,
        ]);
    }

    // Policy Create
    public function policyCreate(Request $request)
    {
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
        $authUser = $this->authUser();
        //Get the tenant ID from the authenticated user
        $tenantId = $authUser->tenant_id;
         
        $policy = new Policy();
        $policy->tenant_id = $tenantId;
        $policy->policy_title = $request->input('policy_title');
        $policy->policy_content = $request->input('policy_content'); 
        $policy->attachment_path = $attachmentPath;
        $policy->created_by = $authUser->id;
        $policy->effective_date = $request->input('effective_date');
        $policy->save();

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

        // Logging
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
        $policy = Policy::findOrFail($id);
        $policy->delete();

        // Logging
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
        // Validate the input
        $validator = Validator::make($request->all(), [
            'policy_title' => 'required|string|max:255',
            'effective_date' => 'required|date',
            'policy_content' => 'nullable|string',
            'attachment_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx',
            'target_type' => 'required|in:company-wide,branch,department,employee',
            'branch_ids' => 'nullable|array',
            'department_ids' => 'nullable|array',
            'employee_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the policy
        $policy = Policy::findOrFail($policyId);

        // Update the policy fields
        $policy->policy_title = $request->input('policy_title');
        $policy->effective_date = $request->input('effective_date');
        $policy->policy_content = $request->input('policy_content', '');

        // Handle file upload if there's an attachment
        if ($request->hasFile('attachment_path')) {
            $file = $request->file('attachment_path');
            $path = $file->store('policies', 'public');
            $policy->attachment_path = $path;
        }

        // Save the policy
        $policy->save();

        // Handle the policy targets based on the target type
        $targets = [];
        $targetType = $request->input('target_type');
        $tenantId = Auth::user()->tenant_id; // Assuming tenant_id is available via the authenticated user

        // Only add new targets, do not update existing ones
       $targets = []; // initialize

        if ($targetType == 'branch') {
            $branch_ids = $request->input('branch_ids', []);

            // 🧹 Delete old ones not in new list
            PolicyTarget::where('policy_id', $policy->id)
                ->where('target_type', 'branch')
                ->whereNotIn('target_id', $branch_ids)
                ->delete();

            foreach ($branch_ids as $id) {
                if ($id && !PolicyTarget::where('policy_id', $policy->id)->where('target_type', 'branch')->where('target_id', $id)->exists()) {
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
            $department_ids = $request->input('department_ids', []);

            PolicyTarget::where('policy_id', $policy->id)
                ->where('target_type', 'department')
                ->whereNotIn('target_id', $department_ids)
                ->delete();

            foreach ($department_ids as $id) {
                if ($id && !PolicyTarget::where('policy_id', $policy->id)->where('target_type', 'department')->where('target_id', $id)->exists()) {
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
            $employee_ids = $request->input('employee_ids', []);

            PolicyTarget::where('policy_id', $policy->id)
                ->where('target_type', 'user')
                ->whereNotIn('target_id', $employee_ids)
                ->delete();

            foreach ($employee_ids as $id) {
                if ($id && !PolicyTarget::where('policy_id', $policy->id)->where('target_type', 'user')->where('target_id', $id)->exists()) {
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
            // Company-wide: delete all if not selected
            PolicyTarget::where('policy_id', $policy->id)
                ->where('target_type', 'company-wide') 
                ->delete();

            if (!PolicyTarget::where('policy_id', $policy->id)->where('target_type', 'company-wide')->exists()) {
                $targets[] = [
                    'policy_id' => $policy->id,
                    'target_type' => 'company-wide',
                    'target_id' => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }


        // Insert the new targets into the database
        if (count($targets)) {
            PolicyTarget::insert($targets);
        }

        return response()->json(['message' => 'Policy updated successfully!']);
    }
}
