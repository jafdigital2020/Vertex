<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\OtTable;
use Illuminate\Http\Request;
use App\Models\DeminimisBenefits;
use App\Models\WithholdingTaxTable;
use App\Http\Controllers\Controller;
use App\Models\SssContributionTable;

class PayrollItemsController extends Controller
{
    // SSS Contribution
    public function payrollItemsSSSContribution(Request $request)
    {
        $sssContributions = SssContributionTable::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items SSS contribution',
                'data' => $sssContributions
            ]);
        }

        return view('tenant.payroll.payroll-items.sss-contribution', compact('sssContributions'));
    }

    // Withholding Tax
    public function payrollItemsWithholdingTax(Request $request)
    {
        $withholdingTaxes = WithholdingTaxTable::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items withholding tax',
                'data' => $withholdingTaxes
            ]);
        }

        return view('tenant.payroll.payroll-items.withholdingtax', compact('withholdingTaxes'));
    }

    // Overtime Table
    public function payrollItemsOTtable(Request $request)
    {
        $ots = OtTable::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items overtime table',
                'data' => $ots
            ]);
        }

        return view('tenant.payroll.payroll-items.ot-table', compact('ots'));
    }

    // De minimis Table (Benefits)
    public function payrollItemsDeMinimisTable(Request $request)
    {
        $deMinimis = DeminimisBenefits::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items de minimis table',
                'data' => $deMinimis
            ]);
        }

        return view('tenant.payroll.payroll-items.deminimis.benefits', compact('deMinimis'));
    }

    // User Deminimis
    public function userDeminimisIndex(Request $request)
    {
        $deMinimis = DeminimisBenefits::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User de minimis benefits',
                'data' => $deMinimis
            ]);
        }

        return view('tenant.payroll.payroll-items.deminimis.deminimisuser', compact('deMinimis'));
    }
}
