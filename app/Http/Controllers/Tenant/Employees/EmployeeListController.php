<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryDetail;
// use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\EmploymentPersonalInformation;
use App\Models\UserPermission;

class EmployeeListController extends Controller
{
    // Employee Index
    public function authUser() {
      if (Auth::guard('global')->check()) {
         return Auth::guard('global')->user();
      } 
      return Auth::guard('web')->user();
   }


    public function employeeListIndex(Request $request)
    {
        $departments = Department::all();
        $designations = Designation::all();
        $roles = Role::all();
        $branches = Branch::all();
        $leaveTypes = LeaveType::all();
       
        // Get default 'main' branch
        // $mainBranch = Branch::where('branch_type', 'main')->first();

        $branchId = $request->has('branch_id') ? $request->input('branch_id') : null;
        $departmentId = $request->input('department_id');
        $designationId = $request->input('designation_id');
        $status = $request->input('status');
        $sort = $request->input('sort');

        $employees = User::with([
            'personalInformation',
            'employmentDetail.branch',
            'role',
            'userPermission',
            'designation',
        ]);
       
        if ($branchId) {
            $employees->whereHas('employmentDetail', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        if ($departmentId) {
            $employees->whereHas('employmentDetail', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        }

        if ($designationId) {
            $employees->whereHas('employmentDetail', function ($query) use ($designationId) {
                $query->where('designation_id', $designationId);
            });
        }

        if ($status) {
            $employees->whereHas('employmentDetail', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        if ($sort === 'asc') {
            $employees->orderBy('created_at', 'asc');
        } elseif ($sort === 'desc') {
            $employees->orderBy('created_at', 'desc');
        } elseif ($sort === 'last_month') {
            $employees->where('created_at', '>=', now()->subMonth());
        } elseif ($sort === 'last_7_days') {
            $employees->where('created_at', '>=', now()->subDays(7));
        }

        //API JSON Response
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'employees' => $employees->map(function ($user) {
                    return [
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'role' => $user->userPermission->role->role_name ?? null,
                        ],
                        'employment_detail' => $user->employmentDetail,
                        'personal_information' => $user->employmentPersonalInformation,

                    ];
                }),
            ]);
        }
       
        // For web access (Blade view)
        return view('tenant.employee.employeelist', [
            'employees' => $employees->get(),
            'departments' => $departments,
            'designations' => $designations,
            'branches' => $branches,
            'roles' => $roles,
            'selectedBranchId' => $branchId,
            'selectedDepartmentId' => $departmentId,
            'selectedDesignationId' => $designationId,
            'selectedStatus' => $status,
            'selectedSort' => $sort,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    // Adding Employee API
    public function employeeStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Personal Info
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'suffix' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // User
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|same:confirm_password',
            'confirm_password' => 'required|string|min:6',
            'role_id' => 'required|string',

            // Employment Details
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|string',
            'designation_id' => 'required|string',
            'date_hired' => 'required|date',
            'employee_id' => 'required|string|unique:employment_details,employee_id',
            'employment_type' => 'required|string',
            'employment_status' => 'required|string',

        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();

            Log::error('Employee store validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'message' => $firstError,
                'errors' => $validator->errors(),
            ], 422);
        }
         
        DB::beginTransaction();
        try {
           
            $user = new User();
            $user->username = $request->username;
            $user->tenant_id = $this->authUser()->tenant_id;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->save();

            $role = Role::find($request->role_id);

            $user_permission = new UserPermission();
            $user_permission->user_id = $user->id;
            $user_permission->role_id = $role->id;
            $user_permission->menu_ids = $role->menu_id;
            $user_permission->module_ids = $role->module_ids;
            $user_permission->user_permission_ids = $role->role_permission_ids;
            $user_permission->status = 1;
            $user_permission->save();
            // 2. Handle image upload
            $profileImagePath = null;

            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                $filename = time() . '_' . $image->getClientOriginalName();

                // Make sure the directory exists
                $path = storage_path('app/public/profile_images');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $savePath = $path . '/' . $filename;

                // Initialize manager with GD
                $manager = new ImageManager(new Driver());

                // Read, resize and save the image
                $imageInstance = $manager->read($image->getRealPath())
                    ->resize(300, 300)
                    ->save($savePath);

                // Save relative path to DB
                $profileImagePath = 'profile_images/' . $filename;
            }

            // 3. Save Personal Info
            EmploymentPersonalInformation::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'suffix' => $request->suffix,
                'profile_picture' => $profileImagePath,
                'phone_number' => $request->phone_number,
            ]);

            // 4. Save Employment Details
            EmploymentDetail::create([
                'user_id' => $user->id,
                'designation_id' => $request->designation_id,
                'department_id' => $request->department_id,
                'status' => 1,
                'date_hired' => $request->date_hired,
                'employee_id' => $request->employee_id,
                'employment_type' => $request->employment_type,
                'employment_status' => $request->employment_status,
                'branch_id' => $request->branch_id,
                'reporting_to' => $request->reporting_to,
            ]);

            // Retrieve Branch Contribution Settings
            $branch = Branch::find($request->branch_id);

            if (!$branch || !$branch->sss_contribution_type) {
                return response()->json([
                    'message' => 'Unable to create employee. Please ensure that the contributions type are configured for this branch.'
                ], 422);
            }

            $sss = $branch->sss_contribution_type === 'fixed'
                ? $branch->fixed_sss_amount
                : ($branch->sss_contribution_type === 'manual' ? 'manual' : 'system');

            $philhealth = $branch->philhealth_contribution_type === 'fixed'
                ? $branch->fixed_philhealth_amount
                : ($branch->philhealth_contribution_type === 'manual' ? 'manual' : 'system');

            $pagibig = $branch->pagibig_contribution_type === 'fixed'
                ? $branch->fixed_pagibig_amount
                : ($branch->pagibig_contribution_type === 'manual' ? 'manual' : 'system');

            $withholding = $branch->withholding_tax_type === 'fixed'
                ? $branch->fixed_withholding_tax_amount
                : ($branch->withholding_tax_type === 'manual' ? 'manual' : 'system');

            $workedDays = $branch->worked_days_per_year === 'custom'
                ? $branch->custom_worked_days
                : $branch->worked_days_per_year;

            // Save Salary Details
            SalaryDetail::create([
                'user_id' => $user->id,
                'sss_contribution' => $sss,
                'philhealth_contribution' => $philhealth,
                'pagibig_contribution' => $pagibig,
                'withholding_tax' => $withholding,
                'worked_days_per_year' => $workedDays,
            ]);

            DB::commit();
            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // ✨ Log the action
            UserLog::create([
                'user_id' => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Employee',
                'action' => 'Create',
                'description' => 'Created new employee record',
                'affected_id' => $user->id,
                'old_data' => null,
                'new_data' => json_encode([
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'employee_id' => $request->employee_id,
                ]),
            ]);

            return response()->json([
                'message' => 'Employee created successfully.',
                'data' => [
                    'user' => $user,
                    'personal_info' => $user->personalInformation,
                    'employment_details' => $user->employmentDetail,
                    'employee_name' => $user->personalInformation->last_name . ', ' . $user->personalInformation->first_name,
                    'employee_id' => $user->employmentDetail->employee_id,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating employee', ['exception' => $e]);

            return response()->json([
                'message' => 'Error creating employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Designations filter by department in Employee Add
    public function getByDepartment($id)
    {
        $designations = Designation::where('department_id', $id)->get();

        return response()->json($designations);
    }

    // Get Department By Branch
    public function getDepartmentsAndEmployeesByBranch($branchId)
    {
        $departments = Department::where('branch_id', $branchId)->get();
        $employees = EmploymentDetail::where('branch_id', $branchId)
            ->with('user.personalInformation')
            ->get();

        return response()->json([
            'departments' => $departments,
            'employees' => $employees
        ]);
    }

    //Update Employee
    public function employeeUpdate(Request $request, $id)
    {
        Log::info('Employee Update Request Data:', $request->all());

        $validator = Validator::make($request->all(), [
            // Personal Info
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'suffix' => 'nullable|string',

            // User
            'username' => 'required|string|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|string',

            'password' => 'nullable|string|min:6|same:confirm_password',
            'confirm_password' => 'nullable|string|min:6',

            // Employment Details
            'designation_id' => 'required|string',
            'department_id' => 'required|string',
            'date_hired' => 'required|date',
            'employee_id' => 'required|string|unique:employment_details,employee_id,' . $id . ',user_id',
            'employment_type' => 'required|string',
            'employment_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // Old data for logs
            $oldData = [
                'username' => $user->username,
                'email' => $user->email,
            ];

            // Update User
            $updateData = [
                'username' => $request->username,
                'email' => $request->email, 
            ];
            $role = Role::find($request->role_id);

            $user_permission = UserPermission::where('user_id',$user->id)->first();
            $user_permission->role_id = $role->id;
            $user_permission->menu_ids = $role->menu_id;
            $user_permission->module_ids = $role->module_ids;
            $user_permission->user_permission_ids = $role->role_permission_ids;
            $user_permission->status = 1;
            $user_permission->save();

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update Personal Info
            $personalInfo = EmploymentPersonalInformation::firstOrNew(['user_id' => $user->id]);
            $personalInfo->fill($request->only(['first_name', 'last_name', 'middle_name', 'suffix', 'phone_number']));

            if ($request->hasFile('profile_picture')) {

                $image = $request->file('profile_picture');
                $filename = time() . '_' . $image->getClientOriginalName();

                $path = storage_path('app/public/profile_images');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $savePath = $path . '/' . $filename;

                $manager = new ImageManager(new Driver());

                $manager->read($image->getRealPath())
                    ->resize(300, 300)
                    ->save($savePath);

                $personalInfo->profile_picture = 'profile_images/' . $filename;
            }

            $personalInfo->save();

            // Update Employment Details
            $employmentDetail = EmploymentDetail::firstOrNew(['user_id' => $user->id]);
            $employmentDetail->fill([
                'designation_id' => $request->designation_id,
                'department_id' => $request->department_id,
                'date_hired' => $request->date_hired,
                'employee_id' => $request->employee_id,
                'employment_type' => $request->employment_type,
                'employment_status' => $request->employment_status,
                'branch_id' => $request->branch_id,
                'reporting_to' => $request->reporting_to,
                'status' => 1,
            ]);
            $employmentDetail->save();


            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // ✨ Log the action
            UserLog::create([
                'user_id' => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Employee',
                'action' => 'Update',
                'description' => 'Updated employee record',
                'affected_id' => $user->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($user->toArray()),
            ]);

            DB::commit();
            return response()->json(['message' => 'Employee updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee', ['exception' => $e]);
            return response()->json(['message' => 'Error updating employee.', 'error' => $e->getMessage()], 500);
        }
    }

    // Deleting Employee
    public function employeeDelete(Request $request, $id)
    {
        $user = User::with('employmentDetail', 'personalInformation')->findOrFail($id);

        $oldData = [
            'user' => $user->toArray(),
            'personal_info' => optional($user->personalInformation)->toArray(),
            'employment_detail' => optional($user->employmentDetails)->toArray(),
        ];

        $user->delete();

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        // ✨ Log the action
        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Employee',
            'action'      => 'delete',
            'description' => 'Deleted user ' . ($oldData['personal_info']['last_name'] ?? '') . ', ID: ' . $id,
            'affected_id' => $id,
            'old_data'    => json_encode($oldData),
            'new_data'    => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User deleted successfully.',
            ], 200);
        }
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    // Deactivate Employee
    public function employeeDeactivate(Request $request, $id)
    {
        $user = User::with('employmentDetail', 'personalInformation')->findOrFail($id);

        $oldData = [
            'user' => $user->toArray(),
            'personal_info' => optional($user->personalInformation)->toArray(),
            'employment_detail' => optional($user->employmentDetails)->toArray(),
        ];

        if ($user->employmentDetail) {
            $user->employmentDetail->status = 'inactive';
            $user->employmentDetail->save();
        }

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        // ✨ Log the action
        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Employee',
            'action'      => 'deactivate',
            'description' => 'Deactivate user ' . ($oldData['personal_info']['last_name'] ?? '') . ', ID: ' . $id,
            'affected_id' => $id,
            'old_data'    => json_encode($oldData),
            'new_data'    => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User deactivated successfully.',
            ], 200);
        }
        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    //Activate Employee
    public function employeeActivate(Request $request, $id)
    {
        $user = User::with('employmentDetail', 'personalInformation')->findOrFail($id);

        $oldData = [
            'user' => $user->toArray(),
            'personal_info' => optional($user->personalInformation)->toArray(),
            'employment_detail' => optional($user->employmentDetails)->toArray(),
        ];

        if ($user->employmentDetail) {
            $user->employmentDetail->status = 'active';
            $user->employmentDetail->save();
        }

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        // ✨ Log the action
        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Employee',
            'action'      => 'activate',
            'description' => 'Activate user ' . ($oldData['personal_info']['last_name'] ?? '') . ', ID: ' . $id,
            'affected_id' => $id,
            'old_data'    => json_encode($oldData),
            'new_data'    => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User activated successfully.',
            ], 200);
        }
        return redirect()->back()->with('success', 'User activated successfully.');
    }


}
