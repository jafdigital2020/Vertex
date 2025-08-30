<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

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
        $permission = PermissionHelper::get(57);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $employees = $accessData['inactive_ho_employees']->get(); 
        $branches = $accessData['branches']->where('id', 7)->get(); 
        $departments = $accessData['departments']->where('branch_id', 7)->get(); 
        $departmentIds = $departments->pluck('id')->toArray(); 
        $designations = $accessData['designations']->whereIn('department_id', $departmentIds)->get();
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $employees, 
                'departments' => $departments,
                'designations' => $designations,
                'branches' => $branches,
            ]);
        }

        return view('tenant.employee.inactive.head_office', [
            'employees' => $employees,
            'permission' => $permission, 
            'departments' => $departments,
            'designations' => $designations,
            'branches' => $branches,
        ]);
    }

        public function hoInactiveIndexFilter(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(57);
        $branch = $request->input('branch');
        $department = $request->input('department');
        $designation = $request->input('designation'); 
        $sortBy = $request->input('sortBy');

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $query = $accessData['inactive_ho_employees']->with([
            'personalInformation',
            'employmentDetail',
            'employmentDetail.department',
            'employmentDetail.designation'
        ]);

        if ($branch) {
            $query->whereHas('employmentDetail', function ($query) use ($branch) {
                $query->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('employmentDetail', function ($query) use ($department) {
                $query->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('employmentDetail', function ($query) use ($designation) {
                $query->where('designation_id', $designation);
            });
        } 
        if ($sortBy === 'ascending') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->orderBy('date_hired', 'ASC');
            });
        } elseif ($sortBy === 'descending') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->orderBy('date_hired', 'DESC');
            });
        } elseif ($sortBy === 'last_month') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->where('date_hired', '>=', now()->subMonth());
            });
        } elseif ($sortBy === 'last_7_days') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->where('date_hired', '>=', now()->subDays(7));
            });
        }

        $employees = $query->get();
        $html = view('tenant.employee.inactive.head_office_filter', [
            'employees' => $employees,
            'permission' => $permission,  
        ])->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function sgInactiveIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(57);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $employees = $accessData['inactive_sg_employees']->get();
        $branches = $accessData['branches']->where('id','!=', 7)->get(); 
        $departments = $accessData['departments']->where('branch_id','!=', 7)->get(); 
        $departmentIds = $departments->pluck('id')->toArray(); 
        $designations = $accessData['designations']->whereIn('department_id', $departmentIds)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $employees,
                'departments' => $departments,
                'designations' => $designations,
                'branches' => $branches,
            ]);
        }

        return view('tenant.employee.inactive.security_guard', [
            'employees' => $employees,
            'permission' => $permission, 
            'departments' => $departments,
            'designations' => $designations,
            'branches' => $branches,
        ]);
    }
         public function sgInactiveIndexFilter(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(57);
        $branch = $request->input('branch');
        $department = $request->input('department');
        $designation = $request->input('designation'); 
        $sortBy = $request->input('sortBy');

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $query = $accessData['inactive_sg_employees']->with([
            'personalInformation',
            'employmentDetail',
            'employmentDetail.department',
            'employmentDetail.designation'
        ]);

        if ($branch) {
            $query->whereHas('employmentDetail', function ($query) use ($branch) {
                $query->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('employmentDetail', function ($query) use ($department) {
                $query->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('employmentDetail', function ($query) use ($designation) {
                $query->where('designation_id', $designation);
            });
        } 
        if ($sortBy === 'ascending') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->orderBy('date_hired', 'ASC');
            });
        } elseif ($sortBy === 'descending') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->orderBy('date_hired', 'DESC');
            });
        } elseif ($sortBy === 'last_month') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->where('date_hired', '>=', now()->subMonth());
            });
        } elseif ($sortBy === 'last_7_days') {
            $query->whereHas('employmentDetail', function ($q) {
                $q->where('date_hired', '>=', now()->subDays(7));
            });
        }

        $employees = $query->get();
        $html = view('tenant.employee.inactive.security_guard_filter', [
            'employees' => $employees,
            'permission' => $permission,  
        ])->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

}
