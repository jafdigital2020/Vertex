<?php

namespace App\Http\Controllers\Tenant\Employees;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Plan;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Invoice;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\Subscription;
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

    /**
     * Get all employees
     *
     * Retrieves a paginated list of employees with their personal information, employment details, and role assignments.
     * Supports filtering by branch, department, designation, status, and sorting options.
     *
     * @group Employees
     * @authenticated
     *
     * @queryParam branch_id integer Optional. Filter employees by branch ID. Example: 1
     * @queryParam department_id integer Optional. Filter employees by department ID. Example: 2
     * @queryParam designation_id integer Optional. Filter employees by designation ID. Example: 3
     * @queryParam status string Optional. Filter by employment status (0=Inactive, 1=Active). Example: 1
     * @queryParam sort string Optional. Sort order: "asc" (oldest first), "desc" (newest first), "last_month" (last 30 days), "last_7_days" (last week). Example: desc
     *
     * @response 200 scenario="Success" {
     *   "employees": [
     *     {
     *       "user": {
     *         "id": 1,
     *         "username": "juan.delacruz",
     *         "email": "juan@company.com",
     *         "role": "Employee"
     *       },
     *       "employment_detail": {
     *         "id": 1,
     *         "employee_id": "EMP-0001",
     *         "date_hired": "2024-01-15",
     *         "employment_type": "Regular",
     *         "employment_status": "Active",
     *         "branch_id": 1,
     *         "department_id": 2,
     *         "designation_id": 3,
     *         "status": 1
     *       },
     *       "personal_information": {
     *         "first_name": "Juan",
     *         "last_name": "Dela Cruz",
     *         "middle_name": "Santos",
     *         "suffix": "Jr.",
     *         "phone_number": "09171234567",
     *         "profile_picture": "profile_images/1234567890_photo.jpg"
     *       }
     *     }
     *   ]
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     */
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
                'employees' => $employees->get()->map(function ($user) {
                    $employmentDetail = $user->employmentDetail;
                    return [
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'role' => $user->userPermission->role->role_name ?? null,
                        ],
                        'employment_detail' => $employmentDetail ? [
                            'id' => $employmentDetail->id,
                            'employee_id' => $employmentDetail->employee_id,
                            'date_hired' => $employmentDetail->date_hired,
                            'employment_type' => $employmentDetail->employment_type,
                            'employment_status' => $employmentDetail->employment_status,
                            'branch_id' => $employmentDetail->branch_id,
                            'department_id' => $employmentDetail->department_id,
                            'designation_id' => $employmentDetail->designation_id,
                            'status' => $employmentDetail->status,
                            'department' => $employmentDetail->department ? [
                                'id' => $employmentDetail->department->id,
                                'name' => $employmentDetail->department->department_name
                            ] : null,
                            'designation' => $employmentDetail->designation ? [
                                'id' => $employmentDetail->designation->id,
                                'name' => $employmentDetail->designation->designation_name
                            ] : null,
                             'branch' => $employmentDetail->branch ? [
                                'id' => $employmentDetail->branch->id,
                                'name' => $employmentDetail->branch->name
                            ] : null,
                        ] : null,
                        'personal_information' => $user->personalInformation,
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

    /**
     * Create a new employee
     *
     * Creates a new employee record with personal information, employment details, and salary configuration.
     * This endpoint performs license validation and may generate overage invoices if license limits are exceeded.
     *
     * @group Employees
     * @authenticated
     *
     * @bodyParam first_name string required Employee's first name. Example: Juan
     * @bodyParam last_name string required Employee's last name. Example: Dela Cruz
     * @bodyParam middle_name string Optional. Employee's middle name. Example: Santos
     * @bodyParam suffix string Optional. Name suffix (e.g., Jr., Sr., III). Example: Jr.
     * @bodyParam profile_picture file Optional. Profile photo (jpeg, jpg, png format, max 2MB).
     * @bodyParam username string required Unique username for login. Must be unique across the system. Example: juan.delacruz
     * @bodyParam email string required Unique email address. Must be a valid email format. Example: juan.delacruz@company.com
     * @bodyParam password string required Password for account (minimum 6 characters). Example: SecurePass123
     * @bodyParam confirm_password string required Password confirmation. Must match the password field. Example: SecurePass123
     * @bodyParam role_id integer required Role/Permission template ID. Example: 2
     * @bodyParam branch_id integer required Branch where employee will be assigned. Must be a valid branch ID. Example: 1
     * @bodyParam department_id integer required Department assignment. Must be a valid department ID. Example: 2
     * @bodyParam designation_id integer required Job position/designation. Must be a valid designation ID. Example: 3
     * @bodyParam date_hired date required Date when employee was hired (format: YYYY-MM-DD). Example: 2024-01-15
     * @bodyParam emp_prefix string required Employee ID prefix. Example: EMP
     * @bodyParam employee_id string required Unique employee ID number (will be combined with prefix). Must be unique. Example: 0001
     * @bodyParam employment_type string required Type of employment (e.g., Regular, Contractual, Probationary). Example: Regular
     * @bodyParam employment_status string required Current employment status (e.g., Active, On Leave). Example: Active
     * @bodyParam phone_number string Optional. Contact phone number. Example: 09171234567
     * @bodyParam reporting_to integer Optional. User ID of the direct supervisor/manager. Example: 5
     * @bodyParam biometrics_id string Optional. Unique biometrics device ID for attendance tracking. Example: BIO123
     *
     * @response 200 scenario="Success" {
     *   "status": "success",
     *   "message": "Employee created successfully."
     * }
     *
     * @response 200 scenario="Success with License Overage" {
     *   "status": "success",
     *   "message": "Employee created successfully.",
     *   "overage_warning": {
     *     "message": "License overage detected. Additional invoice created.",
     *     "invoice_id": 123,
     *     "overage_count": 5,
     *     "overage_amount": 2500.00
     *   }
     * }
     *
     * @response 402 scenario="Implementation Fee Required" {
     *   "status": "implementation_fee_required",
     *   "message": "Implementation fee payment required before adding employees.",
     *   "data": {
     *     "implementation_fee": 5000.00,
     *     "plan_name": "Enterprise Plan"
     *   }
     * }
     *
     * @response 403 scenario="Plan Upgrade Required" {
     *   "status": "upgrade_required",
     *   "message": "Your plan has reached its employee limit. Please upgrade your plan.",
     *   "data": {
     *     "current_plan": "Starter",
     *     "current_limit": 10,
     *     "active_employees": 10
     *   }
     * }
     *
     * @response 403 scenario="Insufficient Permissions" {
     *   "status": "error",
     *   "message": "You do not have the permission to create."
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "message": "The email has already been taken.",
     *   "errors": {
     *     "email": [
     *       "The email has already been taken."
     *     ],
     *     "username": [
     *       "The username has already been taken."
     *     ]
     *   }
     * }
     *
     * @response 500 scenario="Server Error" {
     *   "message": "Error creating employee.",
     *   "error": "Database connection failed"
     * }
     */
    public function employeeAdd(Request $request)
    {
        $permission = PermissionHelper::get(9);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        // ✅ NEW: Check license requirements before validation
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        $requirementCheck = $this->licenseOverageService->checkUserAdditionRequirements($tenantId);

        if ($requirementCheck['status'] === 'implementation_fee') {
            return response()->json([
                'status' => 'implementation_fee_required',
                'message' => $requirementCheck['message'],
                'data' => $requirementCheck['data']
            ], 402); // 402 Payment Required
        }

        if ($requirementCheck['status'] === 'upgrade_required') {
            return response()->json([
                'status' => 'upgrade_required',
                'message' => $requirementCheck['message'],
                'data' => $requirementCheck['data']
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
            'biometrics_id' => 'nullable|string|unique:employment_details,biometrics_id',
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

            $fullEmployeeId = $request->emp_prefix . '-' . $request->employee_id;

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
                'biometrics_id' => $request->biometrics_id,
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

            // TRIGGER LICENSE OVERAGE CHECK
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

    /**
     * Update employee details
     *
     * Updates an existing employee's personal information, employment details, and role assignments.
     * All fields except password are required. Password fields are optional and only needed when changing the password.
     *
     * @group Employees
     * @authenticated
     *
     * @bodyParam editUserId integer required The ID of the employee to update. Example: 1
     * @bodyParam first_name string required Employee's first name. Example: Juan
     * @bodyParam last_name string required Employee's last name. Example: Dela Cruz
     * @bodyParam middle_name string Optional. Employee's middle name. Example: Santos
     * @bodyParam suffix string Optional. Name suffix. Example: Jr.
     * @bodyParam profile_picture file Optional. New profile photo (jpeg, jpg, png format, max 2MB).
     * @bodyParam username string required Username for login. Example: juan.delacruz
     * @bodyParam email string required Email address. Example: juan.delacruz@company.com
     * @bodyParam role_id integer required Role/Permission template ID. Example: 2
     * @bodyParam password string Optional. New password (minimum 6 characters). Only required when changing password. Example: NewSecurePass123
     * @bodyParam confirm_password string Optional. Password confirmation. Required when password is provided. Example: NewSecurePass123
     * @bodyParam designation_id integer required Job position/designation ID. Example: 3
     * @bodyParam department_id integer required Department ID. Example: 2
     * @bodyParam branch_id integer required Branch ID. Example: 1
     * @bodyParam date_hired date required Date when employee was hired (format: YYYY-MM-DD). Example: 2024-01-15
     * @bodyParam emp_prefix string required Employee ID prefix. Example: EMP
     * @bodyParam employee_id string required Unique employee ID number. Example: 0001
     * @bodyParam employment_type string required Type of employment. Example: Regular
     * @bodyParam employment_status string required Current employment status. Example: Active
     * @bodyParam phone_number string Optional. Contact phone number. Example: 09171234567
     * @bodyParam reporting_to integer Optional. User ID of the direct supervisor/manager. Example: 5
     * @bodyParam biometrics_id string Optional. Unique biometrics device ID. Example: BIO123
     *
     * @response 200 scenario="Success" {
     *   "status": "success",
     *   "message": "Employee updated successfully."
     * }
     *
     * @response 403 scenario="Insufficient Permissions" {
     *   "status": "error",
     *   "message": "You do not have the permission to update."
     * }
     *
     * @response 404 scenario="Employee Not Found" {
     *   "message": "No query results for model [App\\Models\\User]."
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "message": "The email has already been taken."
     * }
     *
     * @response 500 scenario="Server Error" {
     *   "message": "Error updating employee.",
     *   "error": "Database error"
     * }
     */
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
            'biometrics_id' => 'nullable|string|unique:employment_details,biometrics_id,' . $id . ',user_id',
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

            $fullEmployeeId = $request->emp_prefix . '-' . $request->employee_id;

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
                'biometrics_id' => $request->biometrics_id,
                'reporting_to' => $request->reporting_to,
            ]);
            $employmentDetail->save();

            $user->refresh();
            $user->load(['employmentDetail', 'personalInformation']);

            if (method_exists($user, 'flushCache')) {
                $user->flushCache();
            }

            $user->unsetRelation('employmentDetail');
            $user->load('employmentDetail');

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

    /**
     * Delete an employee
     *
     * Permanently removes an employee record from the system along with all associated data.
     * This action cannot be undone. All related records (personal info, employment details, permissions) will be deleted.
     *
     * @group Employees
     * @authenticated
     *
     * @bodyParam delete_id integer required The ID of the employee to delete. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "status": "success",
     *   "message": "User deleted successfully."
     * }
     *
     * @response 403 scenario="Insufficient Permissions" {
     *   "status": "error",
     *   "message": "You do not have permission to delete this employee."
     * }
     *
     * @response 404 scenario="Employee Not Found" {
     *   "message": "No query results for model [App\\Models\\User]."
     * }
     */
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

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        if (!$tenantId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid tenant information.',
                'errors' => []
            ], 400);
        }

        // ✅ NEW: Check subscription and license limits before processing
        $subscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active subscription found. Please contact support.',
                'errors' => []
            ], 400);
        }

        // Get the file and store it for processing
        $file = $request->file('csv_file');
        $filePath = $file->store('imports');
        $fullPath = storage_path('app/private/' . $filePath);

        // Count rows in CSV (excluding header)
        $rowCount = 0;
        if (($handle = fopen($fullPath, 'r')) !== false) {
            fgetcsv($handle); // Skip header
            while (fgetcsv($handle) !== false) {
                $rowCount++;
            }
            fclose($handle);
        }

        // Get current active users
        $currentActiveUsers = User::where('tenant_id', $tenantId)
            ->where('active_license', true)
            ->count();

        $planLimit = $subscription->plan->employee_limit ?? $subscription->active_license ?? 0;
        $totalAfterImport = $currentActiveUsers + $rowCount;

        // ✅ Check if import would exceed plan limits
        if ($totalAfterImport > $planLimit) {
            // Clean up uploaded file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $overage = $totalAfterImport - $planLimit;
            $overageCost = $overage * LicenseOverageService::OVERAGE_RATE_PER_LICENSE;
            $planName = $subscription->plan->name ?? 'your current plan';

            return response()->json([
                'status' => 'error',
                'message' => 'Import blocked due to license limits',
                'errors' => [
                    'license_limit' => [
                        'message' => "Cannot import {$rowCount} employees. This would exceed your {$planName} limit.",
                        'details' => [
                            'current_users' => $currentActiveUsers,
                            'plan_limit' => $planLimit,
                            'trying_to_import' => $rowCount,
                            'would_exceed_by' => $overage,
                            'overage_cost_per_month' => "₱" . number_format($overageCost, 2),
                            'plan_name' => $planName
                        ],
                        'suggestions' => [
                            'Reduce the number of employees in your CSV file to ' . ($planLimit - $currentActiveUsers) . ' or fewer',
                            'Upgrade your subscription plan to accommodate more users',
                            'Contact support for assistance with bulk imports'
                        ]
                    ]
                ]
            ], 422);
        }

        Log::info('CSV import queued', [
            'tenant_id' => $tenantId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'rows_to_import' => $rowCount
        ]);

        // Dispatch the import job
        ImportEmployeesJob::dispatch($filePath, $tenantId);

        return response()->json([
            'status' => 'success',
            'message' => "Import queued successfully! Processing {$rowCount} employees. This will take 5-10 minutes.",
            'details' => [
                'rows_to_import' => $rowCount,
                'current_users' => $currentActiveUsers,
                'total_after_import' => $totalAfterImport,
                'plan_limit' => $planLimit
            ],
            'next_steps' => [
                'The import is running in the background',
                'Refresh this page in 5-10 minutes to see the results',
                'You will see the imported employees in the employee list'
            ]
        ]);
    }

    /**
     * ✅ NEW: Check import results
     */
    public function checkImportStatus(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        // Find the most recent import result file for this tenant
        $resultsDir = storage_path('app/import_results');
        if (!is_dir($resultsDir)) {
            return response()->json([
                'status' => 'no_results',
                'message' => 'No import results found'
            ]);
        }

        $pattern = $resultsDir . '/import_result_' . $tenantId . '_*.json';
        $files = glob($pattern);

        if (empty($files)) {
            return response()->json([
                'status' => 'no_results',
                'message' => 'No import results found for your account'
            ]);
        }

        // Get the most recent file
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latestFile = $files[0];
        $results = json_decode(file_get_contents($latestFile), true);

        // Clean up old result files (keep only latest 3)
        if (count($files) > 3) {
            $filesToDelete = array_slice($files, 3);
            foreach ($filesToDelete as $fileToDelete) {
                unlink($fileToDelete);
            }
        }

        return response()->json([
            'status' => 'success',
            'results' => $results
        ]);
    }

    public function exportEmployee(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Define the columns (except password and security license fields)
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
        $headerRange = 'A1:V1'; // V is the 22nd column
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
            // Security license fields removed - shift following columns left
            $sheet->setCellValue("O{$row}", $info->phone_number ?? '');
            $sheet->setCellValue("P{$row}", $info->gender ?? '');
            $sheet->setCellValue("Q{$row}", $info->civil_status ?? '');
            $sheet->setCellValue("R{$row}", $gov->sss_number ?? '');
            $sheet->setCellValue("S{$row}", $gov->philhealth_number ?? '');
            $sheet->setCellValue("T{$row}", $gov->pagibig_number ?? '');
            $sheet->setCellValue("U{$row}", $gov->tin_number ?? '');
            $sheet->setCellValue("V{$row}", $info->spouse_name ?? '');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'V') as $col) {
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
        $path = public_path('templates/bulk_template.csv');

        if (!file_exists($path)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($path, 'bulk_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // Get Next Employee ID
    public function getNextEmployeeId(Request $request)
    {
        $request->validate([
            'prefix' => 'required|string',
        ]);

        $authUser = $this->authUser();

        if (!$authUser || !$authUser->tenant_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to determine tenant'
            ], 400);
        }

        $tenantId = $authUser->tenant_id;
        $basePattern = $request->prefix . '-';

        // Get all employee IDs with this prefix for this tenant
        $employeeIds = EmploymentDetail::whereHas('user', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->where('employee_id', 'like', $basePattern . '%')
            ->pluck('employee_id')
            ->map(function ($id) use ($basePattern) {
                // Extract numeric part after the prefix
                $numericPart = substr($id, strlen($basePattern));
                return (int) $numericPart;
            })
            ->filter(function ($num) {
                return $num > 0; // Only valid numbers
            });

        // Get the highest number
        $nextNumber = $employeeIds->isEmpty() ? 1 : $employeeIds->max() + 1;

        return response()->json([
            'next_employee_serial' => str_pad($nextNumber, 4, '0', STR_PAD_LEFT)
        ]);
    }

    public function checkLicenseOverage(Request $request)
    {
        $authUser = $this->authUser();

        // ✅ NEW: Check for implementation fee and upgrade requirements
        $requirementCheck = $this->licenseOverageService->checkUserAdditionRequirements($authUser->tenant_id);

        if ($requirementCheck['status'] === 'implementation_fee') {
            return response()->json([
                'status' => 'implementation_fee_required',
                'message' => $requirementCheck['message'],
                'data' => $requirementCheck['data']
            ]);
        }

        if ($requirementCheck['status'] === 'upgrade_required') {
            return response()->json([
                'status' => 'upgrade_required',
                'message' => $requirementCheck['message'],
                'data' => $requirementCheck['data']
            ]);
        }

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

    /**
     * ✅ NEW: Generate implementation fee invoice
     */
    public function generateImplementationFeeInvoice(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        try {
            $subscription = Subscription::with('plan')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active subscription found'
                ], 404);
            }

            // Verify this tenant actually needs implementation fee
            $requirementCheck = $this->licenseOverageService->checkUserAdditionRequirements($tenantId);

            if ($requirementCheck['status'] !== 'implementation_fee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Implementation fee not required at this time'
                ], 400);
            }

            // Check if implementation fee invoice already exists
            $existingInvoice = Invoice::where('tenant_id', $tenantId)
                ->where('subscription_id', $subscription->id)
                ->where('invoice_type', 'implementation_fee')
                ->whereIn('status', ['paid', 'pending'])
                ->first();

            if ($existingInvoice) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Implementation fee invoice already exists',
                    'invoice' => $existingInvoice
                ]);
            }

            // Generate the invoice
            $implementationFee = $subscription->plan->implementation_fee ?? 0;

            if ($implementationFee <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Implementation fee not configured for this plan'
                ], 400);
            }

            $invoice = $this->licenseOverageService->createImplementationFeeInvoice($subscription, $implementationFee);

            Log::info('Implementation fee invoice generated via user action', [
                'tenant_id' => $tenantId,
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'amount' => $implementationFee
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Implementation fee invoice generated successfully',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate implementation fee invoice', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Generate plan upgrade invoice and upgrade the plan
     */
    public function generatePlanUpgradeInvoice(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        $request->validate([
            'new_plan_id' => 'required|exists:plans,id',
            'new_billing_cycle' => 'nullable|in:monthly,yearly' // Optional billing cycle change
        ]);

        try {
            $subscription = Subscription::with('plan')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active subscription found'
                ], 404);
            }

            $newPlan = Plan::find($request->new_plan_id);

            if (!$newPlan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected plan not found'
                ], 404);
            }

            // Verify the new plan is actually an upgrade
            if ($newPlan->employee_limit <= $subscription->plan->employee_limit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected plan is not an upgrade from your current plan'
                ], 400);
            }

            $existingInvoice = Invoice::where('tenant_id', $tenantId)
                ->where('subscription_id', $subscription->id)
                ->where('invoice_type', 'plan_upgrade')
                ->where('status', 'pending') // Only check for pending invoices
                ->where('created_at', '>=', now()->subDays(7)) // Last 7 days
                ->first();

            if ($existingInvoice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a pending plan upgrade invoice. Please complete or cancel it before creating a new one.',
                    'invoice' => $existingInvoice
                ], 400);
            }

            // Calculate prorated amount (optional - can be 0 for now)
            $proratedAmount = 0; // TODO: Implement proration logic if needed

            // Generate the plan upgrade invoice
            $invoice = $this->licenseOverageService->createPlanUpgradeInvoice($subscription, $newPlan, $proratedAmount);

            Log::info('Plan upgrade invoice generated via user action', [
                'tenant_id' => $tenantId,
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'old_plan' => $subscription->plan->name,
                'new_plan' => $newPlan->name,
                'amount' => $invoice->amount_due
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Plan upgrade invoice generated successfully',
                'invoice' => $invoice,
                'new_plan' => [
                    'id' => $newPlan->id,
                    'name' => $newPlan->name,
                    'employee_limit' => $newPlan->employee_limit
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate plan upgrade invoice', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     *
     * This should be called when the plan_upgrade invoice is marked as paid
     */
    public function processPlanUpgrade($invoiceId)
    {
        try {
            $invoice = Invoice::with('subscription')->find($invoiceId);

            if (!$invoice || $invoice->invoice_type !== 'plan_upgrade') {
                Log::error('Invalid invoice for plan upgrade', ['invoice_id' => $invoiceId]);
                return false;
            }

            $subscription = $invoice->subscription;
            $newPlanId = $invoice->upgrade_plan_id; // Get the upgrade plan ID from the dedicated column

            if (!$newPlanId) {
                Log::error('New plan ID not found in invoice', ['invoice_id' => $invoiceId]);
                return false;
            }

            $newPlan = Plan::find($newPlanId);

            if (!$newPlan) {
                Log::error('New plan not found', ['plan_id' => $newPlanId]);
                return false;
            }

            // Update subscription to new plan
            $oldPlanId = $subscription->plan_id;
            $oldBillingCycle = $subscription->billing_cycle;
            $subscription->plan_id = $newPlan->id;
            $subscription->billing_cycle = $newPlan->billing_cycle; // ✅ Update billing cycle from new plan
            $subscription->implementation_fee_paid = $newPlan->implementation_fee ?? 0;
            $subscription->save();

            Log::info('Plan upgraded successfully', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'old_plan_id' => $oldPlanId,
                'new_plan_id' => $newPlan->id,
                'old_billing_cycle' => $oldBillingCycle,
                'new_billing_cycle' => $newPlan->billing_cycle,
                'invoice_id' => $invoiceId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to process plan upgrade', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ✅ NEW: Clear import status
     */
    public function clearImportStatus(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        // Find and delete import result files for this tenant
        $resultsDir = storage_path('app/import_results');
        if (is_dir($resultsDir)) {
            $pattern = $resultsDir . '/import_result_' . $tenantId . '_*.json';
            $files = glob($pattern);

            foreach ($files as $file) {
                unlink($file);
            }

            Log::info('Import status cleared', [
                'tenant_id' => $tenantId,
                'files_deleted' => count($files)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Import status cleared',
                'files_deleted' => count($files)
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'No import status found to clear'
        ]);
    }
}
