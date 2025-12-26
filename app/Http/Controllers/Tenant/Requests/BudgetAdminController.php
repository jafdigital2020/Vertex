<?php

namespace App\Http\Controllers\Tenant\Requests;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\BudgetRequest;
use Illuminate\Http\Request;
use App\Models\BudgetApproval;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class BudgetAdminController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Display all budget requests for admin
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(63) // Budget Requests (Admin);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $budgetRequests = $accessData['budgetRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->with(['user.personalInformation', 'user.employmentDetail'])
            ->get();

        $approvedCount = $budgetRequests->where('status', 'approved')->count();
        $rejectedCount = $budgetRequests->where('status', 'rejected')->count();
        $pendingCount = $budgetRequests->where('status', 'pending')->count();

        foreach ($budgetRequests as $br) {
            if ($latest = $br->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $br->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $br->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $br->last_approver = null;
                $br->last_approver_type = null;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Budget requests retrieved successfully.',
                'status' => 'success',
                'budgetRequests' => $budgetRequests,
                'approvedCount' => $approvedCount,
                'rejectedCount' => $rejectedCount,
                'pendingCount' => $pendingCount,
            ]);
        }

        return view('tenant.requests.budget.admin', [
            'budgetRequests' => $budgetRequests,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'permission' => $permission
        ]);
    }

    /**
     * Filter budget requests via AJAX
     */
    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(63) // Budget Requests (Admin);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $category = $request->input('category');

        $query = $accessData['budgetRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc');

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->where(function ($q) use ($start, $end) {
                    $q->whereDate('start_date', '<=', $end)
                        ->whereDate('end_date', '>=', $start);
                });
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

        if ($category) {
            $query->where('budget_category', $category);
        }

        $budgetRequests = $query->get();

        $approvedCount = $budgetRequests->where('status', 'approved')->count();
        $rejectedCount = $budgetRequests->where('status', 'rejected')->count();
        $pendingCount = $budgetRequests->where('status', 'pending')->count();

        foreach ($budgetRequests as $br) {
            if ($latest = $br->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $br->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $br->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $br->last_approver = null;
                $br->last_approver_type = null;
            }
        }

        $html = view('tenant.requests.budget.admin_filter', compact('budgetRequests', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve or reject a budget request
     */
    public function approve(Request $request, $budgetRequestId)
    {
        $permission = PermissionHelper::get(63) // Budget Requests (Admin);
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

            $budgetRequest = BudgetRequest::findOrFail($budgetRequestId);
            $user = $this->authUser();
            $action = $request->action;
            $comment = $request->comment;

            // Prevent self-approval
            if ($user->id === $budgetRequest->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot approve your own budget request.'
                ], 403);
            }

            // Check if already processed
            if ($budgetRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Budget request is already {$budgetRequest->status}."
                ], 400);
            }

            // Create approval record
            BudgetApproval::create([
                'budget_request_id' => $budgetRequest->id,
                'approver_id' => $user->id,
                'step' => 1,
                'action' => $action,
                'comment' => $comment,
                'acted_at' => Carbon::now(),
            ]);

            // Update budget request status
            $budgetRequest->update(['status' => $action]);

            // Send notification to requester
            $requester = User::find($budgetRequest->user_id);
            if ($requester) {
                $requester->notify(new UserNotification(
                    "Your budget request for '{$budgetRequest->project_name}' has been {$action}."
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
                'module' => 'Budget Request',
                'action' => ucfirst($action),
                'description' => "Budget request {$action}",
                'affected_id' => $budgetRequest->id,
                'old_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => $action]),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Budget request {$action} successfully!",
                'budgetRequest' => $budgetRequest,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error approving/rejecting budget request', [
                'budgetRequestId' => $budgetRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while processing budget request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a budget request
     */
    public function update(Request $request, $budgetRequestId)
    {
        $permission = PermissionHelper::get(63) // Budget Requests (Admin);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string',
            'budget_category' => 'required|string',
            'requested_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'justification' => 'nullable|string',
            'expected_outcome' => 'nullable|string',
            'file_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        try {
            $budgetRequest = BudgetRequest::findOrFail($budgetRequestId);
            $oldData = $budgetRequest->getOriginal();

            $budgetRequest->project_name = $request->project_name;
            $budgetRequest->budget_category = $request->budget_category;
            $budgetRequest->requested_amount = $request->requested_amount;
            $budgetRequest->start_date = $request->start_date;
            $budgetRequest->end_date = $request->end_date;
            $budgetRequest->justification = $request->justification;
            $budgetRequest->expected_outcome = $request->expected_outcome;

            // Handle file upload if provided
            if ($request->hasFile('file_attachment')) {
                $filePath = $request->file('file_attachment')->store('budget_requests', 'public');

                if ($budgetRequest->file_attachment) {
                    Storage::disk('public')->delete($budgetRequest->file_attachment);
                }

                $budgetRequest->file_attachment = $filePath;
            }

            $budgetRequest->save();

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
                'module' => 'Budget Request',
                'action' => 'Update',
                'description' => 'Updated budget request',
                'affected_id' => $budgetRequest->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($budgetRequest->toArray()),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Budget request updated successfully!',
                'budgetRequest' => $budgetRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating budget request', [
                'budgetRequestId' => $budgetRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while updating budget request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a budget request
     */
    public function destroy($budgetRequestId)
    {
        $permission = PermissionHelper::get(63) // Budget Requests (Admin);
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            $budgetRequest = BudgetRequest::findOrFail($budgetRequestId);
            $filePath = $budgetRequest->file_attachment;
            $oldData = $budgetRequest->toArray();

            $budgetRequest->delete();

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
                'module' => 'Budget Request',
                'action' => 'Delete',
                'description' => 'Deleted budget request',
                'affected_id' => $budgetRequestId,
                'old_data' => json_encode($oldData),
                'new_data' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Budget request deleted successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting budget request', [
                'budgetRequestId' => $budgetRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while deleting budget request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
