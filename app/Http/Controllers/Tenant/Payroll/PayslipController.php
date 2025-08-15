<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Exception;
use Carbon\Carbon;
use App\Models\Payroll;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class PayslipController extends Controller
{
    // Generated Payslip Index
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(14);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  = $accessData['payslips'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('payment_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($branch) {
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }

        $payslips = $query->get();

        $html = view('tenant.payroll.payslip.generated-payslip_filter', compact('tenantId', 'payslips', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function generatedPayslipIndex(Request $request)
    {
        // Get the tenant ID from the request
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(25);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $payslips = $accessData['payslips']->get();
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }
        return view('tenant.payroll.payslip.generated-payslip', compact('tenantId', 'payslips', 'permission', 'branches', 'departments', 'designations'));
    }

    // Payroll Chart Data for Index
    public function dashboardChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;


        $netSalaries = Payroll::selectRaw('MONTH(payment_date) as month, SUM(net_salary) as total')
            ->where('tenant_id', $tenantId)
            ->whereYear('payment_date', $year)
            ->where('status', 'Paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare data for all 12 months (fill missing months with 0)
        $months = range(1, 12);
        $totals = [];
        foreach ($months as $m) {
            $row = $netSalaries->firstWhere('month', $m);
            $totals[] = $row ? floatval($row->total) : 0;
        }

        return response()->json([
            'months' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            'totals' => $totals,
        ]);
    }

    // Payroll Summary for Index
    public function payrollSummary(Request $request)
    {
        $range = $request->input('range', 'monthly');
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        if ($range == 'yearly') {
            $start = Carbon::createFromDate($year)->startOfYear()->format('Y-m-d');
            $end = Carbon::createFromDate($year)->endOfYear()->format('Y-m-d');
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        }
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;


        $totalEarnings = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_earnings');

        $totalDeductions = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_deductions');

        $totalNetSalary = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('net_salary');

        $totalPayrollCount = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->count();


        return response()->json([
            'totalEarnings' => $totalEarnings,
            'totalDeductions' => $totalDeductions,
            'totalNetSalary' => $totalNetSalary,
            'totalPayrollCount' => $totalPayrollCount,
            'range' => $range,
            'year' => $year,
            'month' => $month,
            'start' => $start,
            'end' => $end
        ]);
    }

    // Generated Payslips
    public function generatedPayslips(Request $request, $id)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $payslips = Payroll::findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        return view('tenant.payroll.payslip.payslip-view', compact('tenantId', 'payslips'));
    }

    // Revert Generated Payslip
    public function revertGeneratedPayslip($id)
    {
        try {
            $payslip = Payroll::findOrFail($id);
            $payslip->status = 'Pending';
            $payslip->save();

            $oldData = $payslip->getOriginal();
            $newData = $payslip->toArray();

            //Logging the revert action
            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Payslip',
                'action'          => 'Revert',
                'description'     => 'Reverted Payslip with ID "' . $payslip->id . '" to Draft status',
                'affected_id'     => $payslip->id,
                'old_data'        => json_encode($oldData),
                'new_data'        => json_encode($newData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payslip reverted to Draft status successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error reverting payslip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reverting payslip.'
            ], 500);
        }
    }

    public function deleteGeneratedPayslip($id)
    {
        try {
            $payslip = Payroll::findOrFail($id);
            $payslip->delete();

            $oldData = $payslip->toArray();

            //Logging the deletion
            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Payslip',
                'action'          => 'Delete',
                'description'     => 'Deleted Payslip with ID "' . $payslip->id . '"',
                'affected_id'     => $payslip->id,
                'old_data'        => json_encode($oldData),
                'new_data'        => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payslip deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting payslip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payslip.'
            ], 500);
        }
    }


    //  User Payslip Index
    public function userPayslipIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $userId = $authUser->id;

        $payslips = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->orderBy('payment_date', 'desc')
            ->latest('id')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        return view('tenant.payroll.payslip.userpayslip.payslip', compact('tenantId', 'userId', 'payslips'));
    }

    // User Payslip Dashboard Chart
    public function userDashboardChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $authUser = Auth::user();
        $tenantId = $authUser->tenant_id ?? null;
        $userId = $authUser->id;

        $netSalaries = Payroll::selectRaw('MONTH(payment_date) as month, SUM(net_salary) as total')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->whereYear('payment_date', $year)
            ->where('status', 'Paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare data for all 12 months (fill missing months with 0)
        $months = range(1, 12);
        $totals = [];
        foreach ($months as $m) {
            $row = $netSalaries->firstWhere('month', $m);
            $totals[] = $row ? floatval($row->total) : 0;
        }

        return response()->json([
            'months' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            'totals' => $totals,
        ]);
    }

    // User Payslip Summary
    public function userPayrollSummary(Request $request)
    {
        $range = $request->input('range', 'monthly');
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        if ($range == 'yearly') {
            $start = Carbon::createFromDate($year)->startOfYear()->format('Y-m-d');
            $end = Carbon::createFromDate($year)->endOfYear()->format('Y-m-d');
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        }

        $authUser = Auth::user();
        $tenantId = $authUser->tenant_id ?? null;
        $userId = $authUser->id;

        $totalEarnings = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_earnings');

        $totalDeductions = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_deductions');

        $totalNetSalary = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('net_salary');

        $totalPayrollCount = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->count();

        return response()->json([
            'totalEarnings' => $totalEarnings,
            'totalDeductions' => $totalDeductions,
            'totalNetSalary' => $totalNetSalary,
            'totalPayrollCount' => $totalPayrollCount,
            'range' => $range,
            'year' => $year,
            'month' => $month,
            'start' => $start,
            'end' => $end
        ]);
    }

    // Admin Generated Payslip Bulk Delete
    public function bulkDeletePayslip(Request $request)
    {
        $payslipIds = $request->input('payroll_ids', []);
        if (empty($payslipIds)) {
            return response()->json(['message' => 'Please select at least 1 payslip'], 400);
        }

        // Proceed with bulk deletion
        Payroll::whereIn('id', $payslipIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payslip records deleted successfully!',
        ]);
    }

    // Admin Generated Payslip Revert to Pending
    public function bulkRevertPayslip(Request $request)
    {
        $payslipIds = $request->input('payroll_ids', []);

        if (empty($payslipIds)) {
            return response()->json(['message' => 'Please select at least 1 payslip'], 400);
        }

        // Proceed with bulk revert
        $payslips = Payroll::whereIn('id', $payslipIds)->get();
        foreach ($payslips as $payslip) {
            $payslip->status = 'Pending';
            $payslip->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Payslip records reverted to Pending status successfully!',
        ]);
    }

    // User Generated Payslip
    public function userGeneratedPayslip(Request $request, $id)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $payslips = Payroll::findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        return view('tenant.payroll.payslip.userpayslip.payslipview', compact('tenantId', 'payslips'));
    }
}
