<?php

namespace App\Http\Controllers\Tenant\Requests;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\HmoRequest;
use Illuminate\Http\Request;
use App\Models\HmoApproval;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class HmoAdminController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Display all HMO requests for admin
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(59);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $hmoRequests = $accessData['hmoRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->with(['user.personalInformation', 'user.employmentDetail'])
            ->get();

        $approvedCount = $hmoRequests->where('status', 'approved')->count();
        $rejectedCount = $hmoRequests->where('status', 'rejected')->count();
        $pendingCount = $hmoRequests->where('status', 'pending')->count();

        foreach ($hmoRequests as $hr) {
            if ($latest = $hr->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $hr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $hr->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $hr->last_approver = null;
                $hr->last_approver_type = null;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'HMO requests retrieved successfully.',
                'status' => 'success',
                'hmoRequests' => $hmoRequests,
                'approvedCount' => $approvedCount,
                'rejectedCount' => $rejectedCount,
                'pendingCount' => $pendingCount,
            ]);
        }

        return view('tenant.requests.hmo.admin', [
            'hmoRequests' => $hmoRequests,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'permission' => $permission
        ]);
    }

    /**
     * Filter HMO requests via AJAX
     */
    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(59);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $hmoPlan = $request->input('hmoPlan');
        $coverageType = $request->input('coverageType');

        $query = $accessData['hmoRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc');

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

        if ($status) {
            $query->where('status', $status);
        }

        if ($hmoPlan) {
            $query->where('hmo_plan', $hmoPlan);
        }

        if ($coverageType) {
            $query->where('coverage_type', $coverageType);
        }

        $hmoRequests = $query->get();

        $approvedCount = $hmoRequests->where('status', 'approved')->count();
        $rejectedCount = $hmoRequests->where('status', 'rejected')->count();
        $pendingCount = $hmoRequests->where('status', 'pending')->count();

        foreach ($hmoRequests as $hr) {
            if ($latest = $hr->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $hr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $hr->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $hr->last_approver = null;
                $hr->last_approver_type = null;
            }
        }

        $html = view('tenant.requests.hmo.admin_filter', compact('hmoRequests', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve or reject an HMO request
     */
    public function approve(Request $request, $hmoRequestId)
    {
        $permission = PermissionHelper::get(59);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to approve/reject.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        try {
            DB::beginTransaction();

            $hmoRequest = HmoRequest::findOrFail($hmoRequestId);
            $user = $this->authUser();
            $action = $request->action;
            $comment = $request->comment;

            // Prevent self-approval
            if ($user->id === $hmoRequest->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot approve your own HMO request.'
                ], 403);
            }

            // Check if already processed
            if ($hmoRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => "HMO request is already {$hmoRequest->status}."
                ], 400);
            }

            // Create approval record
            HmoApproval::create([
                'hmo_request_id' => $hmoRequest->id,
                'approver_id' => $user->id,
                'step' => 1,
                'action' => $action,
                'comment' => $comment,
                'acted_at' => Carbon::now(),
            ]);

            // Update HMO request status
            $hmoRequest->update(['status' => $action]);

            // Send notification to requester
            $requester = User::find($hmoRequest->user_id);
            if ($requester) {
                $requester->notify(new UserNotification(
                    "Your HMO request for '{$hmoRequest->hmo_plan}' has been {$action}."
                ));
            }

            // Log the action
            $empId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $empId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id' => $empId,
                'global_user_id' => $globalUserId,
                'module' => 'HMO Request',
                'action' => ucfirst($action),
                'description' => "HMO request {$action}",
                'affected_id' => $hmoRequest->id,
                'old_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => $action]),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "HMO request {$action} successfully!",
                'hmoRequest' => $hmoRequest,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error approving/rejecting HMO request', [
                'hmoRequestId' => $hmoRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while processing HMO request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an HMO request
     */
    public function update(Request $request, $hmoRequestId)
    {
        $permission = PermissionHelper::get(59);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'hmo_plan' => 'required|string',
            'coverage_type' => 'required|string',
            'number_of_dependents' => 'required|integer|min:0',
            'dependent_details' => 'nullable|string',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string',
            'file_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        try {
            $hmoRequest = HmoRequest::findOrFail($hmoRequestId);
            $oldData = $hmoRequest->getOriginal();

            $hmoRequest->hmo_plan = $request->hmo_plan;
            $hmoRequest->coverage_type = $request->coverage_type;
            $hmoRequest->number_of_dependents = $request->number_of_dependents;
            $hmoRequest->dependent_details = $request->dependent_details;
            $hmoRequest->effective_date = $request->effective_date;
            $hmoRequest->reason = $request->reason;

            // Handle file upload if provided
            if ($request->hasFile('file_attachment')) {
                $filePath = $request->file('file_attachment')->store('hmo_requests', 'public');

                if ($hmoRequest->file_attachment) {
                    Storage::disk('public')->delete($hmoRequest->file_attachment);
                }

                $hmoRequest->file_attachment = $filePath;
            }

            $hmoRequest->save();

            // Logging
            $empId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $empId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id' => $empId,
                'global_user_id' => $globalUserId,
                'module' => 'HMO Request',
                'action' => 'Update',
                'description' => 'Updated HMO request',
                'affected_id' => $hmoRequest->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($hmoRequest->toArray()),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'HMO request updated successfully!',
                'hmoRequest' => $hmoRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating HMO request', [
                'hmoRequestId' => $hmoRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while updating HMO request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an HMO request
     */
    public function destroy($hmoRequestId)
    {
        $permission = PermissionHelper::get(59);
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            $hmoRequest = HmoRequest::findOrFail($hmoRequestId);
            $filePath = $hmoRequest->file_attachment;
            $oldData = $hmoRequest->toArray();

            $hmoRequest->delete();

            // Delete the file attachment if it exists
            if ($filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            // Logging
            $empId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $empId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id' => $empId,
                'global_user_id' => $globalUserId,
                'module' => 'HMO Request',
                'action' => 'Delete',
                'description' => 'Deleted HMO request',
                'affected_id' => $hmoRequestId,
                'old_data' => json_encode($oldData),
                'new_data' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'HMO request deleted successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting HMO request', [
                'hmoRequestId' => $hmoRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while deleting HMO request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
