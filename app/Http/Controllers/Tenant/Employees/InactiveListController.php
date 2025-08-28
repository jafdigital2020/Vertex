<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InactiveListController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }

        return Auth::user();
    }

    // HO Inactive Index
    public function hoInactiveIndex(Request $request)
    {
        $authUser = $this->authUser();
        $employees = User::whereHas('employmentDetail', function ($query) use ($authUser) {
            $query->whereHas('user', function ($userQuery) use ($authUser) {
                $userQuery->where('tenant_id', $authUser->tenant_id);
            })
                ->whereHas('branch', function ($branchQuery) {
                    $branchQuery->where('id', 7)
                        ->where('name', 'Theos Helios Security Agency Corp');
                })
                ->where('status', 0);
        })->with(['personalInformation', 'employmentDetail'])->get();


        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $employees
            ]);
        }

        return view('tenant.employee.inactive.head_office', [
            'employees' => $employees
        ]);
    }

    public function sgInactiveIndex(Request $request)
    {
        $authUser = $this->authUser();
        $employees = User::whereHas('employmentDetail', function ($query) use ($authUser) {
            $query->whereHas('user', function ($userQuery) use ($authUser) {
                $userQuery->where('tenant_id', $authUser->tenant_id);
            })
                ->whereHas('branch', function ($branchQuery) {
                    $branchQuery->where('id', '!=', 7)
                        ->where('name', '!=', 'Theos Helios Security Agency Corp');
                })
                ->where('status', 0);
        })->with(['personalInformation', 'employmentDetail'])->get();


        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $employees
            ]);
        }

        return view('tenant.employee.inactive.security_guard', [
            'employees' => $employees
        ]);
    }
}
