<?php

namespace App\Http\Controllers\Tenant\Requests;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use App\Models\LoanApproval;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class LoanAdminController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Display all loan requests for admin
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(62); // Loan Requests (Admin)
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $loanRequests = $accessData['loanRequests']
            ->where('tenant_id', $tenantId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->with(['user.personalInformation', 'user.employmentDetail'])
            ->get();

        $approvedCount = $loanRequests->where('status', 'approved')->count();
        $rejectedCount = $loanRequests->where('status', 'rejected')->count();
        $pendingCount = $loanRequests->where('status', 'pending')->count();

        foreach ($loanRequests as $lr) {
            if ($latest = $lr->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $lr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $lr->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $lr->last_approver = null;
                $lr->last_approver_type = null;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Loan requests retrieved successfully.',
                'status' => 'success',
                'loanRequests' => $loanRequests,
                'approvedCount' => $approvedCount,
                'rejectedCount' => $rejectedCount,
                'pendingCount' => $pendingCount,
            ]);
        }

        return view('tenant.requests.loan.admin', [
            'loanRequests' => $loanRequests,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'permission' => $permission
        ]);
    }

    /**
     * Filter loan requests via AJAX
     */
    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(62); // Loan Requests (Admin)
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $loanType = $request->input('loanType');

        $query = $accessData['loanRequests']
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

        if ($loanType) {
            $query->where('loan_type', $loanType);
        }

        $loanRequests = $query->get();

        $approvedCount = $loanRequests->where('status', 'approved')->count();
        $rejectedCount = $loanRequests->where('status', 'rejected')->count();
        $pendingCount = $loanRequests->where('status', 'pending')->count();

        foreach ($loanRequests as $lr) {
            if ($latest = $lr->latestApproval) {
                $approver = $latest->approver;
                $pi = optional($approver->personalInformation);
                $lr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $lr->last_approver_type = optional(optional($approver->employmentDetail)->branch)->name ?? 'Global';
            } else {
                $lr->last_approver = null;
                $lr->last_approver_type = null;
            }
        }

        $html = view('tenant.requests.loan.admin_filter', compact('loanRequests', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve or reject a loan request
     */
    public function approve(Request $request, $loanRequestId)
    {
        $permission = PermissionHelper::get(62); // Loan Requests (Admin)
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

            $loanRequest = LoanRequest::findOrFail($loanRequestId);
            $user = $this->authUser();
            $action = $request->action;
            $comment = $request->comment;

            // Prevent self-approval
            if ($user->id === $loanRequest->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot approve your own loan request.'
                ], 403);
            }

            // Check if already processed
            if ($loanRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Loan request is already {$loanRequest->status}."
                ], 400);
            }

            // Create approval record
            LoanApproval::create([
                'loan_request_id' => $loanRequest->id,
                'approver_id' => $user->id,
                'step' => 1,
                'action' => $action,
                'comment' => $comment,
                'acted_at' => Carbon::now(),
            ]);

            // Update loan request status
            $loanRequest->update(['status' => $action]);

            // Send notification to requester
            $requester = User::find($loanRequest->user_id);
            if ($requester) {
                $requester->notify(new UserNotification(
                    "Your loan request for " . number_format($loanRequest->loan_amount, 2) . " has been {$action}."
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
                'module' => 'Loan Request',
                'action' => ucfirst($action),
                'description' => "Loan request {$action}",
                'affected_id' => $loanRequest->id,
                'old_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => $action]),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Loan request {$action} successfully!",
                'loanRequest' => $loanRequest,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error approving/rejecting loan request', [
                'loanRequestId' => $loanRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while processing loan request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a loan request
     */
    public function update(Request $request, $loanRequestId)
    {
        $permission = PermissionHelper::get(62); // Loan Requests (Admin)
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'loan_type' => 'required|string',
            'loan_amount' => 'required|numeric|min:0',
            'repayment_period' => 'required|integer|min:1',
            'interest_rate' => 'nullable|numeric|min:0',
            'purpose' => 'nullable|string',
            'collateral' => 'nullable|string',
            'file_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        try {
            $loanRequest = LoanRequest::findOrFail($loanRequestId);
            $oldData = $loanRequest->getOriginal();

            $loanRequest->loan_type = $request->loan_type;
            $loanRequest->loan_amount = $request->loan_amount;
            $loanRequest->repayment_period = $request->repayment_period;
            $loanRequest->interest_rate = $request->interest_rate;
            $loanRequest->purpose = $request->purpose;
            $loanRequest->collateral = $request->collateral;

            // Handle file upload if provided
            if ($request->hasFile('file_attachment')) {
                $filePath = $request->file('file_attachment')->store('loan_requests', 'public');

                if ($loanRequest->file_attachment) {
                    Storage::disk('public')->delete($loanRequest->file_attachment);
                }

                $loanRequest->file_attachment = $filePath;
            }

            $loanRequest->save();

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
                'module' => 'Loan Request',
                'action' => 'Update',
                'description' => 'Updated loan request',
                'affected_id' => $loanRequest->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($loanRequest->toArray()),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Loan request updated successfully!',
                'loanRequest' => $loanRequest,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating loan request', [
                'loanRequestId' => $loanRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while updating loan request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a loan request
     */
    public function destroy($loanRequestId)
    {
        $permission = PermissionHelper::get(62); // Loan Requests (Admin)
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            $loanRequest = LoanRequest::findOrFail($loanRequestId);
            $filePath = $loanRequest->file_attachment;
            $oldData = $loanRequest->toArray();

            $loanRequest->delete();

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
                'module' => 'Loan Request',
                'action' => 'Delete',
                'description' => 'Deleted loan request',
                'affected_id' => $loanRequestId,
                'old_data' => json_encode($oldData),
                'new_data' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Loan request deleted successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting loan request', [
                'loanRequestId' => $loanRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while deleting loan request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
