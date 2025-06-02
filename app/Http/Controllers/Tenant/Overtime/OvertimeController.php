<?php

namespace App\Http\Controllers\Tenant\Overtime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function overtimeIndex(Request $request)
    {
        if($request->wantsJson()) {
            return response()->json([
                'message' => 'Overtime index page',
                'data' => [
                    // Add any data you want to return
                ]
            ]);
        }

        return view('tenant.overtime.overtime');
    }
}
