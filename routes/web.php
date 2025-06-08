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
use App\Http\Controllers\Tenant\Payroll\EarningsController;
use App\Http\Controllers\Tenant\Overtime\OvertimeController;
use App\Http\Controllers\Tenant\Settings\ApprovalController;
use App\Http\Controllers\Tenant\Settings\GeofenceController;
use App\Http\Controllers\Tenant\Payroll\DeductionsController;
use App\Http\Controllers\Tenant\Leave\LeaveEmployeeController;
use App\Http\Controllers\Tenant\Leave\LeaveSettingsController;
use App\Http\Controllers\Tenant\Payroll\PayrollItemsController;
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
use App\Http\Middleware\EnsureUserIsAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;

Route::get('/', function () {
    return redirect('login');
});

Route::get('/login', [AuthController::class, 'loginIndex'])->name('login')->middleware([RedirectIfAuthenticated::class]);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/no-permission', function () {
    return view('errors.permission');
})->name('no-permission');
 
Route::middleware([EnsureUserIsAuthenticated::class])->group(function () {
   
    Route::middleware(['isSuperAdmin']  )->group(function () {
        Route::get('/superadmin-dashboard', [DashboardController::class, 'dashboardIndex'])->name('superadmin-dashboard');
        Route::get('/tenant', [OrganizationController::class, 'organizationIndex'])->name('superadmin-tenants');
        Route::get('/subscription', [SubscriptionController::class, 'subscriptionIndex'])->name('superadmin-subscription');
        Route::get('/packages', [PackageController::class, 'packageTable'])->name('superadmin-packagetable');
        Route::get('/packages-grid', [PackageController::class, 'packageGrid'])->name('superadmin-packageGrid');
        Route::get('/payment', [PaymentController::class, 'paymentIndex'])->name('superadmin-payment');
    
        // {Packages}
        Route::get('/get-packages-details', [PackageController::class, 'getPackageDetails'])->name('superadmin-getpackageDetails');
        Route::post('/edit-package', [PackageController::class, 'editPackage'])->name('superadmin-editPackage');
    });
    // Dashboard
    Route::get('/admin-dashboard', [TenantDashboardController::class, 'adminDashboard'])->name('admin-dashboard');
    Route::get('/employee-dashboard', [TenantDashboardController::class, 'employeeDashboard'])->name('employee-dashboard');

    //User Management
        //   User
    Route::get('/users', [UserManagementController::class, 'userIndex'])->name('users');
    Route::get('/users-filter', [UserManagementController::class, 'userFilter'])->name('user-filter');
    Route::get('/get-user-permission-details', [UserManagementController::class, 'getUserPermissionDetails'])->name('get-user-permission-details');
    Route::post('/edit-user-permission', [UserManagementController::class, 'editUserPermission'])->name('edit-user-permission');
       //  Roles
    Route::get('/roles-permission', [UserManagementController::class, 'roleIndex'])->name('roles-permissions');
    Route::get('/get-role-details', [UserManagementController::class, 'getRoleDetails'])->name('get-role-details');
    Route::post('/edit-role', [UserManagementController::class, 'editRole'])->name('edit-role');
    Route::get('/get-role-permission-details', [UserManagementController::class, 'getRolePermissionDetails'])->name('get-role-permission-details');
    Route::post('/edit-role-permission', [UserManagementController::class, 'editRolePermission'])->name('edit-role-permission');
   
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

    //Overtime
    Route::get('/overtime', [OvertimeController::class, 'overtimeIndex'])->name('overtime');

    // Payroll Items
    Route::get('/payroll/payroll-items/sss-contribution', [PayrollItemsController::class, 'payrollItemsSSSContribution'])->name('sss-contributionTable');
    Route::get('/payroll/payroll-items/withholding-tax', [PayrollItemsController::class, 'payrollItemsWithholdingTax'])->name('withholding-taxTable');
    Route::get('/payroll/payroll-items/overtime-table', [PayrollItemsController::class, 'payrollItemsOTtable'])->name('ot-table');
    Route::get('/payroll/payroll-items/de-minimis-table', [PayrollItemsController::class, 'payrollItemsDeMinimisTable'])->name('de-minimis-benefits');
    Route::get('/payroll/payroll-items/de-minimis-user', [PayrollItemsController::class, 'userDeminimisIndex'])->name('de-minimis-user');
    Route::get('/payroll/payroll-items/earnings', [EarningsController::class, 'earningIndex'])->name('earnings');
    Route::get('/payroll/payroll-items/earnings/user', [EarningsController::class, 'userEarningIndex'])->name('user-earnings');
    Route::get('/payroll/payroll-items/deductions', [DeductionsController::class, 'deductionIndex'])->name('deductions');
    Route::get('/payroll/payroll-items/deductions/user', [DeductionsController::class, 'userDeductionIndex'])->name('user-deductions');
});
