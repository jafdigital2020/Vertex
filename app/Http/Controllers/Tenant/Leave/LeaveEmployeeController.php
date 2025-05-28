<?php

namespace App\Http\Controllers\Tenant\Leave;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LeaveEmployeeController extends Controller
{
    public function leaveEmployeeIndex(Request $request)
    {
        $leaveTypes = LeaveType::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the leave employee index endpoint.',
                'status' => 'success',
                'leaveTypes' => $leaveTypes,
            ]);
        }

        return view('tenant.leave.employeeleave', [
            'leaveTypes' => $leaveTypes,
        ]);
    }


}
