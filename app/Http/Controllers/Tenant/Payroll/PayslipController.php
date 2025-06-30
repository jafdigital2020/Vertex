<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    // Generated Payslip Index
    public function generatedPayslipIndex(Request $request)
    {
        // Get the tenant ID from the request
        $tenantId = Auth::user()->tenant_id ?? null;

        $payslips = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->orderBy('payment_date', 'desc')
            ->latest('id')
            ->get();


        if ($request->wantsJson()) {
            // Return JSON response if the request expects JSON
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        // Return the view with the tenant ID
        return view('tenant.payroll.payroll-items.payslip.generated-payslip', compact('tenantId', 'payslips'));
    }

    // Payroll Chart Data for Index
    public function dashboardChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $authUserTenantId = Auth::user()->tenant_id ?? null;

        $netSalaries = Payroll::selectRaw('MONTH(payment_date) as month, SUM(net_salary) as total')
            ->where('tenant_id', $authUserTenantId)
            ->whereYear('payment_date', $year)
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

        $authUserTenantId = Auth::user()->tenant_id ?? null;

        $totalEarnings = Payroll::where('tenant_id', $authUserTenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_earnings');

        $totalDeductions = Payroll::where('tenant_id', $authUserTenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_deductions');

        $totalNetSalary = Payroll::where('tenant_id', $authUserTenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('net_salary');

        $totalPayrollCount = Payroll::where('tenant_id', $authUserTenantId)
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
    public function generatedPayslips($id)
    {
        // Get the tenant ID from the request
        $tenantId = Auth::user()->tenant_id ?? null;
        $user = Auth::user();

        $payslips = Payroll::findOrFail($id);

        // Return the view with the tenant ID
        return view('tenant.payroll.payroll-items.payslip.payslip-view', compact('tenantId', 'payslips'));
    }


    //  User Payslip Index
    public function userPayslipIndex(Request $request)
    {
        // Get the tenant ID from the request
        $tenantId = Auth::user()->tenant_id ?? null;

        // Get the authenticated user's ID
        $userId = Auth::id();

        // Fetch payslips for the authenticated user
        $payslips = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderBy('payment_date', 'desc')
            ->latest('id')
            ->get();

        if ($request->wantsJson()) {
            // Return JSON response if the request expects JSON
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        // Return the view with the tenant ID and user ID
        return view('tenant.payroll.payroll-items.payslip.payslip', compact('tenantId', 'userId', 'payslips'));
    }
}
