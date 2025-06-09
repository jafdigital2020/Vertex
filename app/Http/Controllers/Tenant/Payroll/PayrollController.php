<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayrollController extends Controller
{
    // Process Index
    public function payrollProcessIndex(Request $request)
    {
        $branches = Branch::all();
        $departments = Department::all();
        $designations = Designation::all();

        if($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll Process Index',
                'data' => []
            ]);
        }

        return view('tenant.payroll.process', compact('branches', 'departments', 'designations'));
    }
}
