<?php

namespace App\Http\Controllers\Tenant\Report;

use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class PayrollReportController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

    public function payrollReportIndex(Request $request)
    {
        $tenantId = $this->authUser()->tenant_id;

        $payrolls = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->get();

        $branches = Branch::where('tenant_id', $tenantId)->get();

        if ($request->wantsJson()) {
            return response()->json($payrolls);
        }

        return view('tenant.reports.payrollreport', compact('payrolls', 'branches'));
    }
}
