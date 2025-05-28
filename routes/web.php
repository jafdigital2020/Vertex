<?php

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Tenant\HolidayController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\SuperAdmin\PackageController;
use App\Http\Controllers\SuperAdmin\PaymentController;
use App\Http\Controllers\Tenant\DesignationController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\Tenant\Branch\BranchController;
use App\Http\Controllers\Tenant\Policy\PolicyController;
use App\Http\Controllers\Tenant\UserManagementController;
use App\Http\Controllers\SuperAdmin\OrganizationController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\Tenant\Employees\SalaryController;
use App\Http\Controllers\Tenant\Leave\LeaveAdminController;
use App\Http\Controllers\Tenant\Settings\ApprovalController;
use App\Http\Controllers\Tenant\Settings\GeofenceController;
use App\Http\Controllers\Tenant\Leave\LeaveEmployeeController;
use App\Http\Controllers\Tenant\Leave\LeaveSettingsController;
use App\Http\Controllers\Tenant\Employees\ResignationController;
use App\Http\Controllers\Tenant\Employees\TerminationController;
use App\Http\Controllers\Tenant\Support\KnowledgeBaseController;
use App\Http\Controllers\Tenant\Employees\EmployeeListController;
use App\Http\Controllers\Tenant\Employees\EmployeeDetailsController;
use App\Http\Controllers\Tenant\Attendance\AttendanceAdminController;
use App\Http\Controllers\Tenant\Attendance\ShiftManagementController;
use App\Http\Controllers\Tenant\Settings\LeaveTypeSettingsController;
use App\Http\Controllers\Tenant\Settings\AttendanceSettingsController;
use App\Http\Controllers\Tenant\Attendance\AttendanceEmployeeController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [AuthController::class, 'loginIndex'])->name('login');

Route::middleware(['auth:global', 'isSuperAdmin'])->group(function () {
    Route::get('/superadmin-dashboard', [DashboardController::class, 'dashboardIndex'])->name('superadmin-dashboard');
    Route::get('/tenant', [OrganizationController::class, 'organizationIndex'])->name('superadmin-tenants');
    Route::get('/subscription', [SubscriptionController::class, 'subscriptionIndex'])->name('superadmin-subscription');
    Route::get('/packages', [PackageController::class, 'packageTable'])->name('superadmin-packagetable');
    Route::get('/packages-grid', [PackageController::class, 'packageGrid'])->name('superadmin-packageGrid');
    Route::get('/payment', [PaymentController::class, 'paymentIndex'])->name('superadmin-payment');
});

Route::middleware(['auth:global,web'])->group(function () {

    // Dashboard
    Route::get('/admin-dashboard', [TenantDashboardController::class, 'adminDashboard'])->name('admin-dashboard');
    Route::get('/employee-dashboard', [TenantDashboardController::class, 'employeeDashboard'])->name('employee-dashboard');

    //User Management
    Route::get('/users', [UserManagementController::class, 'userIndex'])->name('users');
    Route::get('/roles-permission', [UserManagementController::class, 'roleIndex'])->name('roles-permissions');

    // Employees
    Route::get('/employees', [EmployeeListController::class, 'employeeListIndex'])->name('employees');
    Route::get('/get-designations/{department}', [EmployeeListController::class, 'getByDepartment']);
    Route::delete('/employees/delete/{id}', [EmployeeListController::class, 'employeeDelete'])->name('employeeDelete');

    // == Details == //
    Route::get('/employees/employee-details/{id}', [EmployeeDetailsController::class, 'employeeDetails'])->name('employee-details');

    // Department and Designation
    Route::get('/departments', [DepartmentController::class, 'departmentIndex'])->name('departments');
    Route::post('/departments/update/{id}', [DepartmentController::class, 'departmentUpdate'])->name('departmentUpdate');
    Route::get('/designations', [DesignationController::class, 'designationIndex'])->name('designations');
    Route::get('/designations/departments/{branchId}', [DesignationController::class, 'getDepartmentsByBranch']);
    Route::post('/designations/update/{id}', [DesignationController::class, 'designationUpdate'])->name('designationUpdate');

    //Salary Record Index
    Route::get('/employees/employee-details/{id}/salary-records', [SalaryController::class, 'salaryRecordIndex'])->name('salaryRecord');

    // Shift Management
    Route::get('/shift-management', [ShiftManagementController::class, 'shiftIndex'])->name('shift-management');
    Route::get('/shift-list', [ShiftManagementController::class, 'shiftList'])->name('shift-list');

    //Settings
    Route::get('/settings/attendance-settings', [AttendanceSettingsController::class, 'attendanceSettingsIndex'])->name('attendance-settings');
    Route::get('/settings/leave-type', [LeaveTypeSettingsController::class, 'leaveTypeSettingsIndex'])->name('leave-type');
    Route::get('/settings/approval-steps', [ApprovalController::class, 'approvalIndex'])->name('approval-steps');

    // Geofence
    Route::get('/settings/geofence', [GeofenceController::class, 'geofenceIndex'])->name('geofence-settings');

    // Attendance
    Route::get('/attendance-employee', [AttendanceEmployeeController::class, 'employeeAttendanceIndex'])->name('attendance-employee');
    Route::get('/attendance-admin', [AttendanceAdminController::class, 'adminAttendanceIndex'])->name('attendance-admin');

    //Leave UI
    Route::get('/leave/leave-settings', [LeaveSettingsController::class, 'LeaveSettingsIndex'])->name('leave-settings');
    Route::get('/leave/leave-employee', [LeaveEmployeeController::class, 'leaveEmployeeIndex'])->name('leave-employees');
    Route::get('/leave/leave-admin', [LeaveAdminController::class, 'leaveAdminIndex'])->name('leave-admin');

    // Holiday
    Route::get('/holidays', [HolidayController::class, 'holidayIndex'])->name('holidays');
    Route::get('/holidays/holiday-exception', [HolidayController::class, 'holidayExceptionIndex'])->name('holiday-exception');

    // Branch
    Route::get('/branches', [BranchController::class, 'branchIndex'])->name('branch-grid');

    // Policy
    Route::get('/policy', [PolicyController::class, 'policyIndex'])->name('policy');

    // Resignation
    Route::get('/resignation', [ResignationController::class, 'resignationIndex'])->name('resignation');

    // Termination
    Route::get('/termination', [TerminationController::class, 'terminationIndex'])->name('termination');

    // Knowledge Base
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'knowledgeBaseIndex'])->name('knowledgebase');
});
