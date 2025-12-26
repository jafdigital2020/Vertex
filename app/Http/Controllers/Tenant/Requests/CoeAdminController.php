<?php

namespace App\Http\Controllers\Tenant\Requests;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\CoeRequest;
use Illuminate\Http\Request;
use App\Models\CoeApproval;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class CoeAdminController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Display all COE requests for admin
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(66); // COE Requests (Admin)
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $coeRequests = $accessData['coeRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->with(['user.personalInformation', 'user.employmentDetail'])
            ->get();

        $approvedCount = $coeRequests->where('status', 'approved')->count();
        $rejectedCount = $coeRequests->where('status', 'rejected')->count();
        $pendingCount = $coeRequests->where('status', 'pending')->count();

        foreach ($coeRequests as $cr) {
            if ($latest = $cr->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $cr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $cr->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $cr->last_approver = null;
                $cr->last_approver_type = null;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'COE requests retrieved successfully.',
                'status' => 'success',
                'coeRequests' => $coeRequests,
                'approvedCount' => $approvedCount,
                'rejectedCount' => $rejectedCount,
                'pendingCount' => $pendingCount,
            ]);
        }

        return view('tenant.requests.coe.admin', [
            'coeRequests' => $coeRequests,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'permission' => $permission
        ]);
    }

    /**
     * Filter COE requests via AJAX
     */
    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(66); // COE Requests (Admin)
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $purpose = $request->input('purpose');

        $query = $accessData['coeRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc');

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('request_date', [$start, $end]);
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

        if ($purpose) {
            $query->where('purpose', 'like', '%' . $purpose . '%');
        }

        $coeRequests = $query->get();

        $approvedCount = $coeRequests->where('status', 'approved')->count();
        $rejectedCount = $coeRequests->where('status', 'rejected')->count();
        $pendingCount = $coeRequests->where('status', 'pending')->count();

        foreach ($coeRequests as $cr) {
            if ($latest = $cr->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $cr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $cr->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $cr->last_approver = null;
                $cr->last_approver_type = null;
            }
        }

        $html = view('tenant.requests.coe.admin_filter', compact('coeRequests', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve or reject a COE request
     */
    public function approve(Request $request, $coeRequestId)
    {
        $permission = PermissionHelper::get(66); // COE Requests (Admin)
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

            $coeRequest = CoeRequest::findOrFail($coeRequestId);
            $user = $this->authUser();
            $action = $request->action;
            $comment = $request->comment;

            // Prevent self-approval
            if ($user->id === $coeRequest->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot approve your own COE request.'
                ], 403);
            }

            // Check if already processed
            if ($coeRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => "COE request is already {$coeRequest->status}."
                ], 400);
            }

            // Create approval record
            CoeApproval::create([
                'coe_request_id' => $coeRequest->id,
                'approver_id' => $user->id,
                'step' => 1,
                'action' => $action,
                'comment' => $comment,
                'acted_at' => Carbon::now(),
            ]);

            // Update COE request status
            $coeRequest->update(['status' => $action]);

            // Send notification to requester
            $requester = User::find($coeRequest->user_id);
            if ($requester) {
                $requester->notify(new UserNotification(
                    "Your COE request for '{$coeRequest->purpose}' has been {$action}."
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
                'module' => 'COE Request',
                'action' => ucfirst($action),
                'description' => "COE request {$action}",
                'affected_id' => $coeRequest->id,
                'old_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => $action]),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "COE request {$action} successfully!",
                'coeRequest' => $coeRequest,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error approving/rejecting COE request', [
                'coeRequestId' => $coeRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while processing COE request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a COE request
     */
    public function update(Request $request, $coeRequestId)
    {
        $permission = PermissionHelper::get(66); // COE Requests (Admin)
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'purpose' => 'required|string',
            'recipient_name' => 'nullable|string',
            'recipient_company' => 'nullable|string',
            'address_to' => 'nullable|string',
            'needed_by_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        try {
            $coeRequest = CoeRequest::findOrFail($coeRequestId);
            $oldData = $coeRequest->getOriginal();

            $coeRequest->purpose = $request->purpose;
            $coeRequest->recipient_name = $request->recipient_name;
            $coeRequest->recipient_company = $request->recipient_company;
            $coeRequest->address_to = $request->address_to;
            $coeRequest->needed_by_date = $request->needed_by_date;

            $coeRequest->save();

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
                'module' => 'COE Request',
                'action' => 'Update',
                'description' => 'Updated COE request',
                'affected_id' => $coeRequest->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($coeRequest->toArray()),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'COE request updated successfully!',
                'coeRequest' => $coeRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating COE request', [
                'coeRequestId' => $coeRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while updating COE request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a COE request
     */
    public function destroy($coeRequestId)
    {
        $permission = PermissionHelper::get(66); // COE Requests (Admin)
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            $coeRequest = CoeRequest::findOrFail($coeRequestId);
            $oldData = $coeRequest->toArray();

            $coeRequest->delete();

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
                'module' => 'COE Request',
                'action' => 'Delete',
                'description' => 'Deleted COE request',
                'affected_id' => $coeRequestId,
                'old_data' => json_encode($oldData),
                'new_data' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'COE request deleted successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting COE request', [
                'coeRequestId' => $coeRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while deleting COE request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
