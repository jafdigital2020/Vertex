<?php

namespace App\Http\Controllers\Tenant\Policy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function policyIndex(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the policy index endpoint.',
                'status' => 'success',
            ]);
        }

        return view('tenant.employee.policy');
    }
}
