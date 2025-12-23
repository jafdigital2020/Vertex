<?php

namespace App\Http\Controllers\Tenant\Requests;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\AssetRequest;
use Illuminate\Http\Request;
use App\Models\AssetApproval;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class AssetAdminController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Display all asset requests for admin
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(58);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $assetRequests = $accessData['assetRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->with(['user.personalInformation', 'user.employmentDetail'])
            ->get();

        $approvedCount = $assetRequests->where('status', 'approved')->count();
        $rejectedCount = $assetRequests->where('status', 'rejected')->count();
        $pendingCount = $assetRequests->where('status', 'pending')->count();

        foreach ($assetRequests as $ar) {
            if ($latest = $ar->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $ar->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $ar->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $ar->last_approver = null;
                $ar->last_approver_type = null;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Asset requests retrieved successfully.',
                'status' => 'success',
                'assetRequests' => $assetRequests,
                'approvedCount' => $approvedCount,
                'rejectedCount' => $rejectedCount,
                'pendingCount' => $pendingCount,
            ]);
        }

        return view('tenant.requests.asset.admin', [
            'assetRequests' => $assetRequests,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'permission' => $permission
        ]);
    }

    /**
     * Filter asset requests via AJAX
     */
    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(58);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $assetType = $request->input('assetType');
        $urgencyLevel = $request->input('urgencyLevel');

        $query = $accessData['assetRequests']
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

        if ($assetType) {
            $query->where('asset_type', $assetType);
        }

        if ($urgencyLevel) {
            $query->where('urgency_level', $urgencyLevel);
        }

        $assetRequests = $query->get();

        $approvedCount = $assetRequests->where('status', 'approved')->count();
        $rejectedCount = $assetRequests->where('status', 'rejected')->count();
        $pendingCount = $assetRequests->where('status', 'pending')->count();

        foreach ($assetRequests as $ar) {
            if ($latest = $ar->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $ar->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $ar->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $ar->last_approver = null;
                $ar->last_approver_type = null;
            }
        }

        $html = view('tenant.requests.asset.admin_filter', compact('assetRequests', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve or reject an asset request
     */
    public function approve(Request $request, $assetRequestId)
    {
        $permission = PermissionHelper::get(58);
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

            $assetRequest = AssetRequest::findOrFail($assetRequestId);
            $user = $this->authUser();
            $action = $request->action;
            $comment = $request->comment;

            // Prevent self-approval
            if ($user->id === $assetRequest->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot approve your own asset request.'
                ], 403);
            }

            // Check if already processed
            if ($assetRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Asset request is already {$assetRequest->status}."
                ], 400);
            }

            // Create approval record
            AssetApproval::create([
                'asset_request_id' => $assetRequest->id,
                'approver_id' => $user->id,
                'step' => 1,
                'action' => $action,
                'comment' => $comment,
                'acted_at' => Carbon::now(),
            ]);

            // Update asset request status
            $assetRequest->update(['status' => $action]);

            // Send notification to requester
            $requester = User::find($assetRequest->user_id);
            if ($requester) {
                $requester->notify(new UserNotification(
                    "Your asset request for '{$assetRequest->asset_name}' has been {$action}."
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
                'module' => 'Asset Request',
                'action' => ucfirst($action),
                'description' => "Asset request {$action}",
                'affected_id' => $assetRequest->id,
                'old_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => $action]),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Asset request {$action} successfully!",
                'assetRequest' => $assetRequest,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error approving/rejecting asset request', [
                'assetRequestId' => $assetRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while processing asset request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an asset request
     */
    public function update(Request $request, $assetRequestId)
    {
        $permission = PermissionHelper::get(58);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'asset_type' => 'required|string',
            'asset_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'estimated_cost' => 'nullable|numeric|min:0',
            'urgency_level' => 'required|in:Low,Medium,High,Critical',
            'purpose' => 'nullable|string',
            'justification' => 'nullable|string',
            'file_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        try {
            $assetRequest = AssetRequest::findOrFail($assetRequestId);
            $oldData = $assetRequest->getOriginal();

            $assetRequest->asset_type = $request->asset_type;
            $assetRequest->asset_name = $request->asset_name;
            $assetRequest->quantity = $request->quantity;
            $assetRequest->estimated_cost = $request->estimated_cost;
            $assetRequest->urgency_level = $request->urgency_level;
            $assetRequest->purpose = $request->purpose;
            $assetRequest->justification = $request->justification;

            // Handle file upload if provided
            if ($request->hasFile('file_attachment')) {
                $filePath = $request->file('file_attachment')->store('asset_requests', 'public');

                if ($assetRequest->file_attachment) {
                    Storage::disk('public')->delete($assetRequest->file_attachment);
                }

                $assetRequest->file_attachment = $filePath;
            }

            $assetRequest->save();

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
                'module' => 'Asset Request',
                'action' => 'Update',
                'description' => 'Updated asset request',
                'affected_id' => $assetRequest->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($assetRequest->toArray()),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Asset request updated successfully!',
                'assetRequest' => $assetRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating asset request', [
                'assetRequestId' => $assetRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while updating asset request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an asset request
     */
    public function destroy($assetRequestId)
    {
        $permission = PermissionHelper::get(58);
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            $assetRequest = AssetRequest::findOrFail($assetRequestId);
            $filePath = $assetRequest->file_attachment;
            $oldData = $assetRequest->toArray();

            $assetRequest->delete();

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
                'module' => 'Asset Request',
                'action' => 'Delete',
                'description' => 'Deleted asset request',
                'affected_id' => $assetRequestId,
                'old_data' => json_encode($oldData),
                'new_data' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Asset request deleted successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting asset request', [
                'assetRequestId' => $assetRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while deleting asset request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
