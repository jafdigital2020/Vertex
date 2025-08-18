<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SalaryBondController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    // Salary Bond Index
    public function salaryBondIndex(Request $request, $userId)
      {
        $authUser = $this->authUser();
        $user = User::with('salaryRecord')->findOrFail($userId);

        // For API response
        if (request()->expectsJson()) {
            return response()->json([
                'user' => $user->only(['id', 'name']),
            ]);
        }

        // For web view
        return view('tenant.employee.salaries.salarybond', compact('user'));
    }
}
