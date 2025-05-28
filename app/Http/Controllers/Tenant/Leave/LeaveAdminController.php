<?php

namespace App\Http\Controllers\Tenant\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaveAdminController extends Controller
{
    public function leaveAdminIndex(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the leave admin index endpoint.',
                'status' => 'success',
            ]);
        }

        return view('tenant.leave.adminleave');
    }
}
