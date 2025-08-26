<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\UserLog;
use App\Models\Allowance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AllowanceController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    // Allowance
    public function payrollItemsAllowance(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $allowances = Allowance::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items allowances',
                'data' => $allowances
            ]);
        }

        return view('tenant.payroll.payroll-items.allowance.allowance', compact('allowances'));
    }

    // Create Allowance
    public function allowanceStore(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;
        $tenantId = $authUser->tenant_id ?? null;

        // Validate incoming request
        $validated = $request->validate([
            'allowance_name'                    => ['required', 'string', 'max:100', 'unique:allowances,allowance_name'],
            'calculation_basis'      => ['required', Rule::in(['fixed', 'per_attended_day', 'per_attended_hour'])],
            'amount'          => ['required', 'numeric', 'min:0'],
            'is_taxable'              => ['required', 'boolean'],
            'apply_to_all_employees'  => ['required', 'boolean'],
            'description'           => ['nullable', 'string'],
        ]);

        $allowance = new Allowance();
        $allowance->tenant_id              = $tenantId;
        $allowance->allowance_name         = $validated['allowance_name'];
        $allowance->calculation_basis      = $validated['calculation_basis'];
        $allowance->amount                 = $validated['amount'];
        $allowance->is_taxable             = $validated['is_taxable'];
        $allowance->apply_to_all_employees = $validated['apply_to_all_employees'];
        $allowance->description            = $validated['description'] ?? null;
        $allowance->created_by_id          = $authUserId;
        $allowance->created_by_type        = get_class($authUser);

        $allowance->save();

        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Allowances',
            'action'         => 'Create',
            'description'    => 'Created Allowance (ID: ' . $allowance->id . ', Name: ' . $allowance->name . ').',
            'affected_id'    => $allowance->id,
            'old_data'       => null,
            'new_data'       => json_encode($allowance->toArray()),
        ]);

        // 5. Return JSON response with 201 status
        return response()->json([
            'message'      => 'Allowance created successfully.',
            'allowance' => $allowance,
        ], 201);
    }
}
