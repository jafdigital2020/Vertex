<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResignationController extends Controller
{
    public function resignationIndex(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the resignation index endpoint.',
                'status' => 'success',
            ]);
        }

        return view('tenant.employee.resignation');
    }
}
