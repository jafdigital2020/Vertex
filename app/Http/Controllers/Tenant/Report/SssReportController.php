<?php

namespace App\Http\Controllers\Tenant\Report;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class SssReportController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function sssReportIndex(Request $request)
    {
        $user = $this->authUser();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant not found.');
        }

        $tenantId = $tenant->id;
        $dateRange = $request->input('date_range');
        $payrollsQuery = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->with('user.employmentDetail.branch');

        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d');
            $payrollsQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('payroll_period_start', [$startDate, $endDate])
                  ->orWhereBetween('payroll_period_end', [$startDate, $endDate]);
            });
        }

        if ($request->filled('branch_filter')) {
            $payrollsQuery->whereHas('user.employmentDetail.branch', function ($query) use ($request) {
                $query->where('id', $request->branch_filter);
            });
        }

        if ($request->filled('sortby_filter')) {
            switch ($request->sortby_filter) {
                case 'ascending':
                    $payrollsQuery->orderBy('payroll_period_start', 'asc');
                    break;
                case 'descending':
                    $payrollsQuery->orderBy('payroll_period_start', 'desc');
                    break;
                case 'last_month':
                    $payrollsQuery->whereBetween('payroll_period_start', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'last_7_days':
                    $payrollsQuery->whereBetween('payroll_period_start', [
                        now()->subDays(7)->startOfDay(),
                        now()->endOfDay()
                    ]);
                    break;
            }
        }

        $selectedBranch = null;
        if ($request->filled('branch_filter')) {
            $selectedBranch = Branch::where('tenant_id', $tenantId)
                ->where('id', $request->branch_filter)
                ->first();
        }

        $payrollsGrouped = $payrollsQuery->get()
            ->groupBy('user_id')->map(function ($group) {
                return [
                    'user' => $group->first()->user,
                    'pay_period_start' => $group->min('payroll_period_start'),
                    'pay_period_end' => $group->max('payroll_period_end'),
                    'processor_name' => $group->first()->processor_name ?? 'Unknown Processor',
                    'sss_contribution' => $group->sum('sss_contribution'),
                    'sss_contribution_employer' => $group->sum('sss_contribution_employer'),
                    'philhealth_contribution' => $group->sum('philhealth_contribution'),
                    'philhealth_contribution_employer' => $group->sum('philhealth_contribution_employer'),
                    'pagibig_contribution' => $group->sum('pagibig_contribution'),
                    'pagibig_contribution_employer' => $group->sum('pagibig_contribution_employer'),
                    'gross_pay' => $group->sum('gross_pay'),
                    'net_salary' => $group->sum('net_salary'),
                ];
            });

        $branches = Branch::where('tenant_id', $tenantId)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'tenant' => $tenant,
                'payrollsGrouped' => $payrollsGrouped,
                'branches' => $branches,
                'selectedBranch' => $selectedBranch,
                'message' => 'SSS Report data retrieved successfully.'
            ]);
        }

        return view('tenant.reports.sssreport', compact('tenant', 'payrollsGrouped', 'branches', 'selectedBranch'));
    }
}
