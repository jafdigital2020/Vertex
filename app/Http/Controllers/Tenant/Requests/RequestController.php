<?php

namespace App\Http\Controllers\Tenant\Requests;

use Carbon\Carbon;
use App\Models\LoanRequest;
use App\Models\BudgetRequest;
use App\Models\AssetRequest;
use App\Models\HmoRequest;
use App\Models\CoeRequest;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class RequestController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function loanFilter(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(51);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        // Build query
        $query = LoanRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId);

        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $loanRequests = $query->orderBy('created_at', 'desc')->get();

        $html = view('tenant.requests.loan.loan-employee_filter', compact('loanRequests', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function budgetFilter(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(52);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        // Build query
        $query = BudgetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId);

        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $budgetRequests = $query->orderBy('created_at', 'desc')->get();

        $html = view('tenant.requests.budget.budget-employee_filter', compact('budgetRequests', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function assetFilter(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(53);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        // Build query
        $query = AssetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId);

        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $assetRequests = $query->orderBy('created_at', 'desc')->get();

        $html = view('tenant.requests.asset.asset-employee_filter', compact('assetRequests', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function hmoFilter(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(54);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        // Build query
        $query = HmoRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId);

        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $hmoRequests = $query->orderBy('created_at', 'desc')->get();

        $html = view('tenant.requests.hmo.hmo-employee_filter', compact('hmoRequests', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function coeFilter(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(55);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        // Build query
        $query = CoeRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId);

        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $coeRequests = $query->orderBy('created_at', 'desc')->get();

        $html = view('tenant.requests.coe.coe-employee_filter', compact('coeRequests', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    /**
     * Show the loan request page
     */
    public function loanIndex()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(51); // Module ID for loan requests
        $authUserId = $authUser->id ?? null;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Load loan requests for current user
        $loanRequests = LoanRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get counts by status
        $totalApprovedLoans = LoanRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'approved')
            ->count();

        $totalPendingLoans = LoanRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'pending')
            ->count();

        $totalRejectedLoans = LoanRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'rejected')
            ->count();

        return view('tenant.requests.loan.index', [
            'loanRequests' => $loanRequests,
            'totalApprovedLoans' => $totalApprovedLoans,
            'totalPendingLoans' => $totalPendingLoans,
            'totalRejectedLoans' => $totalRejectedLoans,
            'permission' => $permission
        ]);
    }

    /**
     * Show the budget request page
     */
    public function budgetIndex()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(52); // Module ID for budget requests
        $authUserId = $authUser->id ?? null;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Load budget requests for current user
        $budgetRequests = BudgetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get counts by status
        $totalApprovedBudgets = BudgetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'approved')
            ->count();

        $totalPendingBudgets = BudgetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'pending')
            ->count();

        $totalRejectedBudgets = BudgetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'rejected')
            ->count();

        return view('tenant.requests.budget.index', [
            'budgetRequests' => $budgetRequests,
            'totalApprovedBudgets' => $totalApprovedBudgets,
            'totalPendingBudgets' => $totalPendingBudgets,
            'totalRejectedBudgets' => $totalRejectedBudgets,
            'permission' => $permission
        ]);
    }

    /**
     * Show the asset request page
     */
    public function assetIndex()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(53); // Module ID for asset requests
        $authUserId = $authUser->id ?? null;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Load asset requests for current user
        $assetRequests = AssetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get counts by status
        $totalApprovedAssets = AssetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'approved')
            ->count();

        $totalPendingAssets = AssetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'pending')
            ->count();

        $totalRejectedAssets = AssetRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'rejected')
            ->count();

        return view('tenant.requests.asset.index', [
            'assetRequests' => $assetRequests,
            'totalApprovedAssets' => $totalApprovedAssets,
            'totalPendingAssets' => $totalPendingAssets,
            'totalRejectedAssets' => $totalRejectedAssets,
            'permission' => $permission
        ]);
    }

    /**
     * Show the HMO request page
     */
    public function hmoIndex()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(54); // Module ID for HMO requests
        $authUserId = $authUser->id ?? null;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Load HMO requests for current user
        $hmoRequests = HmoRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get counts by status
        $totalApprovedHMO = HmoRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'approved')
            ->count();

        $totalPendingHMO = HmoRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'pending')
            ->count();

        $totalRejectedHMO = HmoRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'rejected')
            ->count();

        return view('tenant.requests.hmo.index', [
            'hmoRequests' => $hmoRequests,
            'totalApprovedHMO' => $totalApprovedHMO,
            'totalPendingHMO' => $totalPendingHMO,
            'totalRejectedHMO' => $totalRejectedHMO,
            'permission' => $permission
        ]);
    }

    /**
     * Show the COE request page
     */
    public function coeIndex()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(55); // Module ID for COE requests
        $authUserId = $authUser->id ?? null;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Load COE requests for current user
        $coeRequests = CoeRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get counts by status
        $totalApprovedCOE = CoeRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'approved')
            ->count();

        $totalPendingCOE = CoeRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'pending')
            ->count();

        $totalRejectedCOE = CoeRequest::where('user_id', $authUserId)
            ->where('tenant_id', $authUserTenantId)
            ->where('status', 'rejected')
            ->count();

        return view('tenant.requests.coe.index', [
            'coeRequests' => $coeRequests,
            'totalApprovedCOE' => $totalApprovedCOE,
            'totalPendingCOE' => $totalPendingCOE,
            'totalRejectedCOE' => $totalRejectedCOE,
            'permission' => $permission
        ]);
    }

    // =================== LOAN REQUEST CRUD METHODS =================== //
    public function storeLoanRequest(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Validate input
        $request->validate([
            'loan_type' => 'required|string|in:Emergency Loan,Salary Loan,Personal Loan,Educational Loan,Housing Loan,Other',
            'loan_amount' => 'required|numeric|min:0',
            'repayment_period' => 'required|integer|min:1',
            'interest_rate' => 'nullable|numeric|min:0',
            'purpose' => 'required|string',
            'collateral' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        // File upload
        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('loan_attachments', 'public');
        }

        try {
            $loan = LoanRequest::create([
                'tenant_id' => $authUserTenantId,
                'user_id' => $authUserId,
                'loan_type' => $request->loan_type,
                'loan_amount' => $request->loan_amount,
                'repayment_period' => $request->repayment_period,
                'interest_rate' => $request->interest_rate ?? 0,
                'purpose' => $request->purpose,
                'collateral' => $request->collateral,
                'request_date' => now()->toDateString(),
                'file_attachment' => $filePath,
                'status' => 'pending',
                'current_step' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loan request submitted successfully.',
                'data' => $loan
            ]);
        } catch (QueryException $e) {
            Log::error('Loan Request Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function updateLoanRequest(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;

        $loan = LoanRequest::where('id', $id)->where('user_id', $authUserId)->first();

        if (!$loan) {
            return response()->json(['success' => false, 'message' => 'Loan request not found.'], 404);
        }

        if ($loan->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be edited.'], 403);
        }

        $request->validate([
            'loan_type' => 'required|string|in:Emergency Loan,Salary Loan,Personal Loan,Educational Loan,Housing Loan,Other',
            'loan_amount' => 'required|numeric|min:0',
            'repayment_period' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'collateral' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        // File upload
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('loan_attachments', 'public');
            $loan->file_attachment = $filePath;
        }

        $loan->update([
            'loan_type' => $request->loan_type,
            'loan_amount' => $request->loan_amount,
            'repayment_period' => $request->repayment_period,
            'purpose' => $request->purpose,
            'collateral' => $request->collateral,
        ]);

        return response()->json(['success' => true, 'message' => 'Loan request updated successfully.']);
    }

    public function deleteLoanRequest($id)
    {
        $authUser = $this->authUser();
        $loan = LoanRequest::where('id', $id)->where('user_id', $authUser->id)->first();

        if (!$loan) {
            return response()->json(['success' => false, 'message' => 'Loan request not found.'], 404);
        }

        if ($loan->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be deleted.'], 403);
        }

        $loan->delete();
        return response()->json(['success' => true, 'message' => 'Loan request deleted successfully.']);
    }

    // =================== BUDGET REQUEST CRUD METHODS =================== //
    public function storeBudgetRequest(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Validate input
        $request->validate([
            'project_name' => 'required|string|max:255',
            'budget_category' => 'required|string|in:Marketing,Operations,IT,HR,Training,Infrastructure,Other',
            'requested_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'justification' => 'required|string',
            'expected_outcome' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120',
        ]);

        // File upload
        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('budget_attachments', 'public');
        }

        try {
            $budget = BudgetRequest::create([
                'tenant_id' => $authUserTenantId,
                'user_id' => $authUserId,
                'project_name' => $request->project_name,
                'budget_category' => $request->budget_category,
                'requested_amount' => $request->requested_amount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'justification' => $request->justification,
                'expected_outcome' => $request->expected_outcome,
                'file_attachment' => $filePath,
                'status' => 'pending',
                'current_step' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Budget request submitted successfully.',
                'data' => $budget
            ]);
        } catch (QueryException $e) {
            Log::error('Budget Request Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function updateBudgetRequest(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;

        $budget = BudgetRequest::where('id', $id)->where('user_id', $authUserId)->first();

        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Budget request not found.'], 404);
        }

        if ($budget->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be edited.'], 403);
        }

        $request->validate([
            'project_name' => 'required|string|max:255',
            'budget_category' => 'required|string|in:Marketing,Operations,IT,HR,Training,Infrastructure,Other',
            'requested_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'justification' => 'required|string',
            'expected_outcome' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120',
        ]);

        // File upload
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('budget_attachments', 'public');
            $budget->file_attachment = $filePath;
        }

        $budget->update([
            'project_name' => $request->project_name,
            'budget_category' => $request->budget_category,
            'requested_amount' => $request->requested_amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'justification' => $request->justification,
            'expected_outcome' => $request->expected_outcome,
        ]);

        return response()->json(['success' => true, 'message' => 'Budget request updated successfully.']);
    }

    public function deleteBudgetRequest($id)
    {
        $authUser = $this->authUser();
        $budget = BudgetRequest::where('id', $id)->where('user_id', $authUser->id)->first();

        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Budget request not found.'], 404);
        }

        if ($budget->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be deleted.'], 403);
        }

        $budget->delete();
        return response()->json(['success' => true, 'message' => 'Budget request deleted successfully.']);
    }

    // =================== ASSET REQUEST CRUD METHODS =================== //
    public function storeAssetRequest(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Validate input
        $request->validate([
            'asset_type' => 'required|string|in:Equipment,Furniture,Vehicle,Technology,Office Supplies,Other',
            'asset_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'estimated_cost' => 'required|numeric|min:0',
            'urgency_level' => 'required|string|in:Low,Medium,High,Critical',
            'purpose' => 'required|string',
            'justification' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        // File upload
        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('asset_attachments', 'public');
        }

        try {
            $asset = AssetRequest::create([
                'tenant_id' => $authUserTenantId,
                'user_id' => $authUserId,
                'asset_type' => $request->asset_type,
                'asset_name' => $request->asset_name,
                'quantity' => $request->quantity,
                'estimated_cost' => $request->estimated_cost,
                'urgency_level' => $request->urgency_level,
                'purpose' => $request->purpose,
                'justification' => $request->justification,
                'request_date' => now()->toDateString(),
                'file_attachment' => $filePath,
                'status' => 'pending',
                'current_step' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asset request submitted successfully.',
                'data' => $asset
            ]);
        } catch (QueryException $e) {
            Log::error('Asset Request Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function updateAssetRequest(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;

        $asset = AssetRequest::where('id', $id)->where('user_id', $authUserId)->first();

        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Asset request not found.'], 404);
        }

        if ($asset->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be edited.'], 403);
        }

        $request->validate([
            'asset_type' => 'required|string|in:Equipment,Furniture,Vehicle,Technology,Office Supplies,Other',
            'asset_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'estimated_cost' => 'required|numeric|min:0',
            'urgency_level' => 'required|string|in:Low,Medium,High,Critical',
            'purpose' => 'required|string',
            'justification' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        // File upload
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('asset_attachments', 'public');
            $asset->file_attachment = $filePath;
        }

        $asset->update([
            'asset_type' => $request->asset_type,
            'asset_name' => $request->asset_name,
            'quantity' => $request->quantity,
            'estimated_cost' => $request->estimated_cost,
            'urgency_level' => $request->urgency_level,
            'purpose' => $request->purpose,
            'justification' => $request->justification,
        ]);

        return response()->json(['success' => true, 'message' => 'Asset request updated successfully.']);
    }

    public function deleteAssetRequest($id)
    {
        $authUser = $this->authUser();
        $asset = AssetRequest::where('id', $id)->where('user_id', $authUser->id)->first();

        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Asset request not found.'], 404);
        }

        if ($asset->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be deleted.'], 403);
        }

        $asset->delete();
        return response()->json(['success' => true, 'message' => 'Asset request deleted successfully.']);
    }

    // =================== HMO REQUEST CRUD METHODS =================== //
    public function storeHMORequest(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Validate input
        $request->validate([
            'hmo_plan' => 'required|string|in:Basic,Standard,Premium,Family,Individual',
            'coverage_type' => 'required|string|in:Individual,Family,Dependent',
            'number_of_dependents' => 'nullable|integer|min:0',
            'dependent_details' => 'nullable|string',
            'effective_date' => 'required|date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        // File upload
        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('hmo_attachments', 'public');
        }

        try {
            $hmo = HmoRequest::create([
                'tenant_id' => $authUserTenantId,
                'user_id' => $authUserId,
                'hmo_plan' => $request->hmo_plan,
                'coverage_type' => $request->coverage_type,
                'number_of_dependents' => $request->number_of_dependents ?? 0,
                'dependent_details' => $request->dependent_details,
                'effective_date' => $request->effective_date,
                'reason' => $request->reason,
                'file_attachment' => $filePath,
                'status' => 'pending',
                'current_step' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'HMO request submitted successfully.',
                'data' => $hmo
            ]);
        } catch (QueryException $e) {
            Log::error('HMO Request Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function updateHMORequest(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;

        $hmo = HmoRequest::where('id', $id)->where('user_id', $authUserId)->first();

        if (!$hmo) {
            return response()->json(['success' => false, 'message' => 'HMO request not found.'], 404);
        }

        if ($hmo->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be edited.'], 403);
        }

        $request->validate([
            'hmo_plan' => 'required|string|in:Basic,Standard,Premium,Family,Individual',
            'coverage_type' => 'required|string|in:Individual,Family,Dependent',
            'number_of_dependents' => 'nullable|integer|min:0',
            'dependent_details' => 'nullable|string',
            'effective_date' => 'required|date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        // File upload
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('hmo_attachments', 'public');
            $hmo->file_attachment = $filePath;
        }

        $hmo->update([
            'hmo_plan' => $request->hmo_plan,
            'coverage_type' => $request->coverage_type,
            'number_of_dependents' => $request->number_of_dependents ?? 0,
            'dependent_details' => $request->dependent_details,
            'effective_date' => $request->effective_date,
            'reason' => $request->reason,
        ]);

        return response()->json(['success' => true, 'message' => 'HMO request updated successfully.']);
    }

    public function deleteHMORequest($id)
    {
        $authUser = $this->authUser();
        $hmo = HmoRequest::where('id', $id)->where('user_id', $authUser->id)->first();

        if (!$hmo) {
            return response()->json(['success' => false, 'message' => 'HMO request not found.'], 404);
        }

        if ($hmo->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be deleted.'], 403);
        }

        $hmo->delete();
        return response()->json(['success' => true, 'message' => 'HMO request deleted successfully.']);
    }

    // =================== COE REQUEST CRUD METHODS =================== //
    public function storeCOERequest(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $authUserTenantId = $authUser->tenant_id ?? null;

        // Validate input
        $request->validate([
            'purpose' => 'required|string',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_company' => 'nullable|string|max:255',
            'address_to' => 'nullable|string',
            'needed_by_date' => 'required|date',
        ]);

        try {
            $coe = CoeRequest::create([
                'tenant_id' => $authUserTenantId,
                'user_id' => $authUserId,
                'purpose' => $request->purpose,
                'recipient_name' => $request->recipient_name,
                'recipient_company' => $request->recipient_company,
                'address_to' => $request->address_to,
                'request_date' => now()->toDateString(),
                'needed_by_date' => $request->needed_by_date,
                'status' => 'pending',
                'current_step' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'COE request submitted successfully.',
                'data' => $coe
            ]);
        } catch (QueryException $e) {
            Log::error('COE Request Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function updateCOERequest(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;

        $coe = CoeRequest::where('id', $id)->where('user_id', $authUserId)->first();

        if (!$coe) {
            return response()->json(['success' => false, 'message' => 'COE request not found.'], 404);
        }

        if ($coe->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be edited.'], 403);
        }

        $request->validate([
            'purpose' => 'required|string',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_company' => 'nullable|string|max:255',
            'address_to' => 'nullable|string',
            'needed_by_date' => 'required|date',
        ]);

        $coe->update([
            'purpose' => $request->purpose,
            'recipient_name' => $request->recipient_name,
            'recipient_company' => $request->recipient_company,
            'address_to' => $request->address_to,
            'needed_by_date' => $request->needed_by_date,
        ]);

        return response()->json(['success' => true, 'message' => 'COE request updated successfully.']);
    }

    public function deleteCOERequest($id)
    {
        $authUser = $this->authUser();
        $coe = CoeRequest::where('id', $id)->where('user_id', $authUser->id)->first();

        if (!$coe) {
            return response()->json(['success' => false, 'message' => 'COE request not found.'], 404);
        }

        if ($coe->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Approved requests cannot be deleted.'], 403);
        }

        $coe->delete();
        return response()->json(['success' => true, 'message' => 'COE request deleted successfully.']);
    }
}