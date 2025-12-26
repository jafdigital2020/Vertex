<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\EmployeeStatusApproval;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class EmployeeStatusController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

    // Employee Status Management Index
    public function employeeStatusIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62); // New submodule ID
        $tenantId = $authUser->tenant_id ?? null;

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();

        // Get employees with employment details
        $employees = $accessData['employees']
            ->with(['personalInformation', 'employmentDetail.branch', 'employmentDetail.department', 'employmentDetail.designation'])
            ->whereHas('employmentDetail')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $employees,
                'permission' => $permission
            ]);
        }

        return view('tenant.employee.employee-status-management', compact('employees', 'branches', 'departments', 'designations', 'permission'));
    }

    // Filter Employee Status
    public function employeeStatusFilter(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $query = $accessData['employees']
            ->with(['personalInformation', 'employmentDetail.branch', 'employmentDetail.department', 'employmentDetail.designation'])
            ->whereHas('employmentDetail');

        // Apply filters
        if ($request->branch) {
            $query->whereHas('employmentDetail', function ($q) use ($request) {
                $q->where('branch_id', $request->branch);
            });
        }

        if ($request->department) {
            $query->whereHas('employmentDetail', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        if ($request->designation) {
            $query->whereHas('employmentDetail', function ($q) use ($request) {
                $q->where('designation_id', $request->designation);
            });
        }

        if ($request->employment_state) {
            $query->whereHas('employmentDetail', function ($q) use ($request) {
                $q->where('employment_state', $request->employment_state);
            });
        }

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('personalInformation', function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $employees = $query->get();

        $html = view('tenant.employee.employee-status-filter', compact('employees', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    // Update Employee Status
    public function updateEmployeeStatus(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update employee status.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'employment_state' => 'required|in:Active,AWOL,Resigned,Terminated,Suspended,Floating',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $employmentDetail = EmploymentDetail::where('user_id', $request->user_id)->first();

            if (!$employmentDetail) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employment details not found.'
                ], 404);
            }

            $oldStatus = $employmentDetail->employment_state;

            // Create approval request instead of direct update
            EmployeeStatusApproval::create([
                'user_id' => $request->user_id,
                'current_status' => $oldStatus,
                'requested_status' => $request->employment_state,
                'remarks' => $request->remarks,
                'approval_status' => 'Pending',
                'requested_by' => $authUser->id,
                'tenant_id' => $authUser->tenant_id,
            ]);

            // Log the status change request
            UserLog::create([
                'user_id' => $request->user_id,
                'module' => 'Employee Status Management',
                'action' => 'Employment Status Change Requested',
                'description' => "Status change requested from {$oldStatus} to {$request->employment_state}. Remarks: {$request->remarks}",
                'performed_by' => $authUser->id,
                'tenant_id' => $authUser->tenant_id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee status change request submitted for HR approval.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update employee status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Bulk Update Employee Status
    public function bulkUpdateEmployeeStatus(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update employee status.'
            ], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'employment_state' => 'required|in:Active,AWOL,Resigned,Terminated,Suspended,Floating',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $updated = 0;
            foreach ($request->user_ids as $userId) {
                $employmentDetail = EmploymentDetail::where('user_id', $userId)->first();

                if ($employmentDetail) {
                    $oldStatus = $employmentDetail->employment_state;

                    // Create approval request
                    EmployeeStatusApproval::create([
                        'user_id' => $userId,
                        'current_status' => $oldStatus,
                        'requested_status' => $request->employment_state,
                        'remarks' => $request->remarks,
                        'approval_status' => 'Pending',
                        'requested_by' => $authUser->id,
                        'tenant_id' => $authUser->tenant_id,
                    ]);

                    // Log the status change request
                    UserLog::create([
                        'user_id' => $userId,
                        'module' => 'Employee Status Management',
                        'action' => 'Employment Status Change Requested (Bulk)',
                        'description' => "Status change requested from {$oldStatus} to {$request->employment_state}. Remarks: {$request->remarks}",
                        'performed_by' => $authUser->id,
                        'tenant_id' => $authUser->tenant_id,
                    ]);

                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "{$updated} employee status change request(s) submitted for HR approval."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update employee status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get Pending Approvals (for HR)
    public function getPendingApprovals()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        if (!in_array('Read', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view approvals.'
            ], 403);
        }

        $approvals = EmployeeStatusApproval::with(['employee.personalInformation', 'employee.employmentDetail.department', 'employee.employmentDetail.designation', 'requester.personalInformation'])
            ->where('tenant_id', $authUser->tenant_id)
            ->where('approval_status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'approvals' => $approvals
        ]);
    }

    // Approve Status Change
    public function approveStatusChange(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to approve status changes.'
            ], 403);
        }

        $request->validate([
            'approval_id' => 'required|exists:employee_status_approvals,id'
        ]);

        try {
            DB::beginTransaction();

            $approval = EmployeeStatusApproval::findOrFail($request->approval_id);

            if ($approval->approval_status !== 'Pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This approval request has already been processed.'
                ], 400);
            }

            // Update employment status
            $employmentDetail = EmploymentDetail::where('user_id', $approval->user_id)->first();
            if ($employmentDetail) {
                $employmentDetail->employment_state = $approval->requested_status;
                $employmentDetail->save();
            }

            // Update approval record
            $approval->approval_status = 'Approved';
            $approval->approved_by = $authUser->id;
            $approval->approved_at = now();
            $approval->save();

            // Log the approval
            UserLog::create([
                'user_id' => $approval->user_id,
                'module' => 'Employee Status Management',
                'action' => 'Employment Status Change Approved',
                'description' => "Status changed from {$approval->current_status} to {$approval->requested_status}. Approved by HR.",
                'performed_by' => $authUser->id,
                'tenant_id' => $authUser->tenant_id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status change approved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve status change: ' . $e->getMessage()
            ], 500);
        }
    }

    // Reject Status Change
    public function rejectStatusChange(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to reject status changes.'
            ], 403);
        }

        $request->validate([
            'approval_id' => 'required|exists:employee_status_approvals,id',
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $approval = EmployeeStatusApproval::findOrFail($request->approval_id);

            if ($approval->approval_status !== 'Pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This approval request has already been processed.'
                ], 400);
            }

            // Update approval record
            $approval->approval_status = 'Rejected';
            $approval->approved_by = $authUser->id;
            $approval->approved_at = now();
            $approval->rejection_reason = $request->rejection_reason;
            $approval->save();

            // Log the rejection
            UserLog::create([
                'user_id' => $approval->user_id,
                'module' => 'Employee Status Management',
                'action' => 'Employment Status Change Rejected',
                'description' => "Status change from {$approval->current_status} to {$approval->requested_status} was rejected. Reason: {$request->rejection_reason}",
                'performed_by' => $authUser->id,
                'tenant_id' => $authUser->tenant_id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status change rejected.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject status change: ' . $e->getMessage()
            ], 500);
        }
    }
}
