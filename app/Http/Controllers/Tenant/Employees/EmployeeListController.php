<?php

namespace App\Http\Controllers\Tenant\Employees;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\LeaveType;
use App\Models\Department;
// use Spatie\Permission\Models\Role;
use App\Models\CustomField;
use App\Models\Designation;
use Illuminate\Support\Str;
use App\Models\SalaryDetail;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use App\Jobs\ImportEmployeesJob;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use App\Models\EmploymentGovernmentId;
use App\Services\LicenseOverageService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\EmploymentPersonalInformation;
use App\Http\Controllers\DataAccessController;

class EmployeeListController extends Controller
{
    protected $licenseOverageService;

    public function __construct(LicenseOverageService $licenseOverageService)
    {
        $this->licenseOverageService = $licenseOverageService;
    }

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function employeeListIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(9);
        $leaveTypes = LeaveType::all();
        $roles = Role::where('tenant_id', $authUser->tenant_id)->get();
        $branchId = $request->has('branch_id') ? $request->input('branch_id') : null;
        $departmentId = $request->input('department_id');
        $designationId = $request->input('designation_id');
        $status = $request->input('status');
        $sort = $request->input('sort');

        $prefixes = CustomField::where('tenant_id', $authUser->tenant_id)->get();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments  = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $employees = $accessData['employees'];


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
        return view('tenant.employee.employeelist', [
            'employees' => $employees->get(),
            'permission' => $permission,
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
            'prefixes' => $prefixes,
        ]);
    }

    public function getEmployeeDetails(Request $request)
    {

        $emp_id = $request->input('emp_id');
        $employee = User::with([
            'personalInformation',
            'employmentDetail',
            'userPermission',
            'designation',
        ])->find($emp_id);


        return response()->json([
            'status' => 'success',
            'employee' => $employee,
        ]);
    }

    public function employeeListFilter(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(9);
        $branch = $request->input('branch');
        $department = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');
        $sortBy = $request->input('sort_by');

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $query = $accessData['employees']->with([
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
        if (!is_null($status)) {
            $query->whereHas('employmentDetail', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }
        if ($sortBy === 'ascending') {
            $query->whereHas('employmentDetail', function ($q) use ($status) {
                $q->orderBy('date_hired', 'ASC');
            });
        } elseif ($sortBy === 'descending') {
            $query->whereHas('employmentDetail', function ($q) use ($status) {
                $q->orderBy('date_hired', 'DESC');
            });
        } elseif ($sortBy === 'last_month') {
            $query->whereHas('employmentDetail', function ($q) use ($status) {
                $q->where('date_hired', '>=', now()->subMonth());
            });
        } elseif ($sortBy === 'last_7_days') {
            $query->whereHas('employmentDetail', function ($q) use ($status) {
                $q->where('date_hired', '>=', now()->subDays(7));
            });
        }

        $employeeList = $query->get();
        $html = view('tenant.employee.employeelist_filter', [
            'employees' => $employeeList,
            'permission' => $permission
        ])->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function branchAutoFilter(Request $request)
    {
        $authUser = $this->authUser();
        $branch_id = $request->input('branch');
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        if (!empty($branch_id)) {
            $departments = $accessData['departments']->where('branch_id', $branch_id)->get();
            $departmentIds = $departments->pluck('id');
            $designations = $accessData['designations']->whereIn('department_id', $departmentIds)->get();
        } else {
            $departments = $accessData['departments']->whereHas('branch', function ($query) use ($authUser) {
                $query->where('tenant_id', $authUser->tenant_id);
            })->get();

            $departmentIds = $departments->pluck('id');
            $designations = $accessData['designations']->whereIn('department_id', $departmentIds)->get();
        }

        return response()->json([
            'status' => 'success',
            'departments' => $departments,
            'designations' => $designations,
        ]);
    }

    public function departmentAutoFilter(Request $request)
    {
        $authUser = $this->authUser();
        $department_id = $request->input('department');
        $branch = $request->input('branch');
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);


        if (!empty($department_id)) {
            $department = $accessData['departments']->find($department_id);

            if (!$department || $department->branch->tenant_id !== $authUser->tenant_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access or department not found.'
                ], 403);
            }

            $branch_id = $department->branch_id;
            $designations = $accessData['designations']->where('department_id', $department_id)->get();
        } else {
            $branch_id = '';

            if (!empty($branch)) {
                $departments = $accessData['departments']->where('branch_id', $branch)
                    ->whereHas('branch', function ($query) use ($authUser) {
                        $query->where('tenant_id', $authUser->tenant_id);
                    })
                    ->pluck('id');

                $designations = $accessData['designations']->where('department_id', $departments)->get();
            } else {
                $departments = $accessData['departments']->whereHas('branch', function ($query) use ($authUser) {
                    $query->where('tenant_id', $authUser->tenant_id);
                })->pluck('id');

                $designations = $accessData['designations']->whereIn('department_id', $departments)->get();
            }
        }

        return response()->json([
            'status' => 'success',
            'branch_id' => $branch_id,
            'designations' => $designations,
        ]);
    }

    public function designationAutoFilter(Request $request)
    {
        $authUser = $this->authUser();
        $designation_id = $request->input('designation');
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!empty($designation_id)) {
            $designation = $accessData['designations']->with('department.branch')->find($designation_id);

            if (!$designation || $designation->department->branch->tenant_id !== $authUser->tenant_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access or designation not found.'
                ], 403);
            }

            $department_id = $designation->department_id;
            $department = $accessData['departments']->find($department_id);
            $branch_id = $department->branch_id;
        } else {
            $department_id = '';
            $branch_id = '';
        }

        return response()->json([
            'status' => 'success',
            'department_id' => $department_id,
            'branch_id' => $branch_id
        ]);
    }


    public function employeeAdd(Request $request)
    {
        $permission = PermissionHelper::get(9);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'suffix' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|same:confirm_password',
            'confirm_password' => 'required|string|min:6',
            'role_id' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|string',
            'designation_id' => 'required|string',
            'date_hired' => 'required|date',
            'employee_id' => 'required|string|unique:employment_details,employee_id',
            'employment_type' => 'required|string',
            'employment_status' => 'required|string',
            'security_license_number' => 'nullable|string',
            'security_license_expiration' => 'nullable|date',
            'reporting_to' => 'nullable|exists:users,id',
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
            $user_permission->data_access_id = $role->data_access_id;
            $user_permission->role_id = $role->id;
            $user_permission->menu_ids = $role->menu_ids;
            $user_permission->module_ids = $role->module_ids;
            $user_permission->user_permission_ids = $role->role_permission_ids;
            $user_permission->status = 1;
            $user_permission->save();
            $profileImagePath = null;

            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                $filename = time() . '_' . $image->getClientOriginalName();

                $path = storage_path('app/public/profile_images');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $savePath = $path . '/' . $filename;
                $manager = new ImageManager(new Driver());

                $imageInstance = $manager->read($image->getRealPath())
                    ->resize(300, 300)
                    ->save($savePath);

                $profileImagePath = 'profile_images/' . $filename;
            }

            EmploymentPersonalInformation::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'suffix' => $request->suffix,
                'profile_picture' => $profileImagePath,
                'phone_number' => $request->phone_number,
            ]);

            $fullEmployeeId = $request->emp_prefix . '-' . $request->month_year . '-' . $request->employee_id;

            EmploymentDetail::create([
                'user_id' => $user->id,
                'designation_id' => $request->designation_id,
                'department_id' => $request->department_id,
                'status' => 1,
                'date_hired' => $request->date_hired,
                'employee_id' => $fullEmployeeId,
                'employment_type' => $request->employment_type,
                'employment_status' => $request->employment_status,
                'branch_id' => $request->branch_id,
                'reporting_to' => $request->reporting_to,
                'security_license_number' => $request->security_license_number,
                'security_license_expiration' => $request->security_license_expiration,
            ]);

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
                : $branch->worked_days_per_year ?? null;

            SalaryDetail::create([
                'user_id' => $user->id,
                'sss_contribution' => $sss,
                'philhealth_contribution' => $philhealth,
                'pagibig_contribution' => $pagibig,
                'withholding_tax' => $withholding,
                'worked_days_per_year' => $workedDays,
            ]);

            DB::commit();

            // ✅ TRIGGER LICENSE OVERAGE CHECK - This is the key step!
            $overageInvoice = $this->licenseOverageService->handleEmployeeActivation($user->id);

            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // Log creation with overage info
            UserLog::create([
                'user_id' => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Employee',
                'action' => 'Create',
                'description' => 'Created new employee record' . ($overageInvoice ? ' (License overage invoice created)' : ''),
                'affected_id' => $user->id,
                'old_data' => null,
                'new_data' => json_encode([
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'employee_id' => $request->employee_id,
                    'overage_invoice_id' => $overageInvoice ? $overageInvoice->id : null
                ]),
            ]);

            $response = [
                'status' => 'success',
                'message' => 'Employee created successfully.',
            ];

            // Add overage warning if invoice was created
            if ($overageInvoice) {
                $response['overage_warning'] = [
                    'message' => 'License overage detected. Additional invoice created.',
                    'invoice_id' => $overageInvoice->id,
                    'overage_count' => $overageInvoice->license_overage_count,
                    'overage_amount' => $overageInvoice->amount_due
                ];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating employee', ['exception' => $e]);

            return response()->json([
                'message' => 'Error creating employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function employeeEdit(Request $request)
    {
        $permission = PermissionHelper::get(9);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $id = $request->input('editUserId');
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'suffix' => 'nullable|string',
            'username' => 'required|string',
            'email' => 'required|email',
            'role_id' => 'required|string',
            'password' => 'nullable|string|min:6|same:confirm_password',
            'confirm_password' => 'nullable|string|min:6',
            'designation_id' => 'required|string',
            'department_id' => 'required|string',
            'date_hired' => 'required|date',
            'employee_id' => 'required|string',
            'employment_type' => 'required|string',
            'employment_status' => 'required|string',
            'security_liicense_number' => 'nullable|string',
            'security_license_expiration' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }


        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $oldData = [
                'username' => $user->username,
                'email' => $user->email,
            ];

            $updateData = [
                'username' => $request->username,
                'email' => $request->email,
            ];
            $role = Role::find($request->role_id);

            $user_permission = UserPermission::where('user_id', $user->id)->first();

            if (!$user_permission) {
                $user_permission = new UserPermission();
                $user_permission->user_id = $user->id;
            }

            $user_permission->role_id = $role->id;
            $user_permission->data_access_id = $role->data_access_id;
            $user_permission->menu_ids = $role->menu_ids;
            $user_permission->module_ids = $role->module_ids;
            $user_permission->user_permission_ids = $role->role_permission_ids;
            $user_permission->status = 1;
            $user_permission->save();

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

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

            $fullEmployeeId = $request->emp_prefix . '-' . $request->month_year . '-' . $request->employee_id;

            $employmentDetail = EmploymentDetail::firstOrNew(['user_id' => $user->id]);
            $employmentDetail->fill([
                'designation_id' => $request->designation_id,
                'department_id' => $request->department_id,
                'date_hired' => $request->date_hired,
                'employee_id' => $fullEmployeeId,
                'employment_type' => $request->employment_type,
                'employment_status' => $request->employment_status,
                'branch_id' => $request->branch_id,
                'status' => 1,
                'security_license_number' => $request->security_license_number,
                'security_license_expiration' => $request->security_license_expiration,
                'reporting_to' => $request->reporting_to,
            ]);
            $employmentDetail->save();

            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

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
            return response()->json([
                'status' => 'success',
                'message' => 'Employee updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee', ['exception' => $e]);
            return response()->json(['message' => 'Error updating employee.', 'error' => $e->getMessage()], 500);
        }
    }

    public function employeeDelete(Request $request)
    {
        $permission = PermissionHelper::get(9);
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this employee.'
            ], 403);
        }

        $user_id = $request->input('delete_id');
        $user = User::with('employmentDetail', 'personalInformation')->findOrFail($user_id);

        $oldData = [
            'user' => $user->toArray(),
            'personal_info' => optional($user->personalInformation)->toArray(),
            'employment_detail' => optional($user->employmentDetails)->toArray(),
            'user_permission' => optional($user->userPermission)->toArray(),
        ];

        $user->delete();

        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Employee',
            'action'      => 'delete',
            'description' => 'Deleted user ' . ($oldData['personal_info']['last_name'] ?? '') . ', ID: ' . $user_id,
            'affected_id' => $user_id,
            'old_data'    => json_encode($oldData),
            'new_data'    => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }

    public function employeeActivate(Request $request)
    {
        $permission = PermissionHelper::get(9);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }

        $user_id = $request->input('act_id');
        $user = User::with('employmentDetail', 'personalInformation')->findOrFail($user_id);

        $oldData = [
            'user' => $user->toArray(),
            'personal_info' => optional($user->personalInformation)->toArray(),
            'employment_detail' => optional($user->employmentDetails)->toArray(),
        ];

        if ($user->employmentDetail) {
            $user->employmentDetail->status = 1;
            $user->employmentDetail->save();
        }

        $overageInvoice = $this->licenseOverageService->handleEmployeeActivation($user_id);

        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Employee',
            'action'      => 'activate',
            'description' => 'Activated user ' . ($oldData['personal_info']['last_name'] ?? '') . ', ID: ' . $user_id,
            'affected_id' => $user_id,
            'old_data'    => json_encode($oldData),
            'new_data'    => null,
        ]);


        $response = [
            'status' => 'success',
            'message' => 'User activated successfully.',
        ];

        // Add overage warning if invoice was created
        if ($overageInvoice) {
            $response['overage_warning'] = [
                'message' => 'License overage detected. Additional invoice created.',
                'invoice_id' => $overageInvoice->id,
                'overage_count' => $overageInvoice->license_overage_count,
                'overage_amount' => $overageInvoice->amount_due
            ];
        }

        return response()->json($response);
    }

    public function employeeDeactivate(Request $request)
    {
        $permission = PermissionHelper::get(9);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }

        $user_id = $request->input('deact_id');
        $user = User::with('employmentDetail', 'personalInformation')->findOrFail($user_id);

        $oldData = [
            'user' => $user->toArray(),
            'personal_info' => optional($user->personalInformation)->toArray(),
            'employment_detail' => optional($user->employmentDetails)->toArray(),
        ];

        // Update the user active_license to false
        $user->active_license = false;
        $user->save();

        if ($user->employmentDetail) {
            $user->employmentDetail->status = 0;
            $user->employmentDetail->save();
        }

        // ✅ HANDLE LICENSE DEACTIVATION
        $this->licenseOverageService->handleEmployeeDeactivation($user_id);

        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module'      => 'Employee',
            'action'      => 'deactivate',
            'description' => 'Deactivated user ' . ($oldData['personal_info']['last_name'] ?? '') . ', ID: ' . $user_id,
            'affected_id' => $user_id,
            'old_data'    => json_encode($oldData),
            'new_data'    => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User deactivated successfully.',
        ]);
    }

    public function importEmployeeCSV(Request $request)
    {
        // Validate file input
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max size: 10MB
        ]);

        // Get the file path and original name for debugging
        $file = $request->file('csv_file');
        Log::info('Original Filename: ' . $file->getClientOriginalName());
        Log::info('Mime Type: ' . $file->getMimeType());

        $path = $file->store('imports'); // This stores the file in 'storage/app/imports'
        Log::info('File uploaded to: ' . $path); // Log the stored file path
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        ImportEmployeesJob::dispatch($path, $tenantId);

        return response()->json([
            'status' => 'success',
            'message' => 'Import successfully queued. Please wait 5-10minutes and refresh the page.',
            'errors' => []
        ]);
    }

    public function exportEmployee(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Define the columns (except password, for security)
        $columns = [
            'First Name',
            'Last Name',
            'Middle Name',
            'Suffix',
            'Username',
            'Email',
            'Role',
            'Branch',
            'Department',
            'Designation',
            'Date Hired',
            'Employee ID',
            'Employment Type',
            'Employment Status',
            'Security License Number',
            'Security License Expiration',
            'Phone Number',
            'Gender',
            'Civil Status',
            'SSS',
            'Philhealth',
            'Pagibig',
            'TIN',
            'Spouse Name',
        ];

        // Set header values
        $sheet->fromArray($columns, null, 'A1');

        // Style the header
        $headerRange = 'A1:X1'; // V is the 22nd column
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0D47A1'); // Nice blue
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Freeze header row
        $sheet->freezePane('A2');

        // Build the user query to filter by branch
        $branchId = $request->get('branch_id');

        $query = User::with([
            'personalInformation',
            'employmentDetail.branch',
            'employmentDetail.department',
            'employmentDetail.designation',
            'employmentDetail',
            'governmentId'
        ]);

        $branchName = 'all';

        if ($branchId) {
            $query->whereHas('employmentDetail', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
            // Get branch name for filename
            $branch = Branch::find($branchId);
            if ($branch) {
                $branchName = Str::slug($branch->name, '_');
            }
        }

        $users = $query->get();

        $row = 2;
        foreach ($users as $user) {
            $info = $user->personalInformation;
            $detail = $user->employmentDetail;
            $gov = $user->governmentId;
            $userPermission = UserPermission::where('user_id', $user->id)->with('role')->first();
            $role = $userPermission && $userPermission->role ? $userPermission->role->role_name : '';

            $sheet->setCellValue("A{$row}", $info->first_name ?? '');
            $sheet->setCellValue("B{$row}", $info->last_name ?? '');
            $sheet->setCellValue("C{$row}", $info->middle_name ?? '');
            $sheet->setCellValue("D{$row}", $info->suffix ?? '');
            $sheet->setCellValue("E{$row}", $user->username);
            $sheet->setCellValue("F{$row}", $user->email);
            $sheet->setCellValue("G{$row}", $role);
            $sheet->setCellValue("H{$row}", $detail && $detail->branch ? $detail->branch->name : '');
            $sheet->setCellValue("I{$row}", $detail && $detail->department ? $detail->department->department_name : '');
            $sheet->setCellValue("J{$row}", $detail && $detail->designation ? $detail->designation->designation_name : '');
            $sheet->setCellValue("K{$row}", $detail->date_hired ?? '');
            $sheet->setCellValue("L{$row}", $detail->employee_id ?? '');
            $sheet->setCellValue("M{$row}", $detail->employment_type ?? '');
            $sheet->setCellValue("N{$row}", $detail->employment_status ?? '');
            $sheet->setCellValue("O{$row}", $detail->security_license_number ?? '');
            $sheet->setCellValue("P{$row}", $detail->security_license_expiration ?? '');
            $sheet->setCellValue("Q{$row}", $info->phone_number ?? '');
            $sheet->setCellValue("R{$row}", $info->gender ?? '');
            $sheet->setCellValue("S{$row}", $info->civil_status ?? '');
            $sheet->setCellValue("T{$row}", $gov->sss_number ?? '');
            $sheet->setCellValue("U{$row}", $gov->philhealth_number ?? '');
            $sheet->setCellValue("V{$row}", $gov->pagibig_number ?? '');
            $sheet->setCellValue("W{$row}", $gov->tin_number ?? '');
            $sheet->setCellValue("X{$row}", $info->spouse_name ?? '');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'X') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add autofilter
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        $fileName = $branchName === 'all'
            ? 'employees_all.xlsx'
            : "employees_{$branchName}.xlsx";

        // Save and return as before
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    public function downloadEmployeeTemplate()
    {
        $path = public_path('templates/employee_template.csv');

        if (!file_exists($path)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($path, 'employee_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // Get Next Employee ID
    public function getNextEmployeeId(Request $request)
    {
        $request->validate([
            'prefix' => 'required|string',
            'month_year' => 'required|string' // format: MM-YYYY
        ]);

        $authUser = $this->authUser() ?? null;
        $basePattern = $request->prefix . '-' . $request->month_year . '-';

        $latest = EmploymentDetail::whereHas('user', function ($query) use ($authUser) {
            $query->where('tenant_id', $authUser->tenant_id);
        })
            ->where('employee_id', 'like', $basePattern . '%')
            ->orderBy('employee_id', 'desc')
            ->first();

        if ($latest) {
            $lastPart = (int)substr($latest->employee_id, strrpos($latest->employee_id, '-') + 1);
            $nextNumber = $lastPart + 1;
        } else {
            $nextNumber = 1;
        }

        return response()->json([
            'next_employee_serial' => str_pad($nextNumber, 4, '0', STR_PAD_LEFT)
        ]);
    }

    public function checkLicenseOverage(Request $request)
    {
        $authUser = $this->authUser();

        $willCauseOverage = $this->licenseOverageService->willCauseOverage($authUser->tenant_id);

        if ($willCauseOverage) {
            $overageDetails = $this->licenseOverageService->getOverageDetails($authUser->tenant_id);

            return response()->json([
                'status' => 'overage_warning',
                'will_cause_overage' => true,
                'overage_details' => $overageDetails
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'will_cause_overage' => false
        ]);
    }
}
