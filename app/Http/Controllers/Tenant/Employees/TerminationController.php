<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TerminationController extends Controller
{
    public function terminationIndex(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the termination index endpoint.',
                'status' => 'success',
            ]);
        }

        return view('tenant.employee.termination');
    }
}
