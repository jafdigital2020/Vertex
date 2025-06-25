<?php

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Controllers\Tenant\HolidayController;
use App\Http\Middleware\EnsureUserIsAuthenticated;
use App\Http\Controllers\Tenant\Bank\BankController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\SuperAdmin\PackageController;
use App\Http\Controllers\SuperAdmin\PaymentController;
use App\Http\Controllers\Tenant\DesignationController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\Tenant\Branch\BranchController;
use App\Http\Controllers\Tenant\Policy\PolicyController;
use App\Http\Controllers\Tenant\UserManagementController;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
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
use App\Http\Controllers\Tenant\Settings\CustomfieldController;
use App\Http\Controllers\Tenant\Employees\ResignationController;
use App\Http\Controllers\Tenant\Employees\TerminationController;
use App\Http\Controllers\Tenant\Support\KnowledgeBaseController;
use App\Http\Controllers\Tenant\Employees\EmployeeListController;
use App\Http\Controllers\Tenant\Employees\EmployeeDetailsController;
use App\Http\Controllers\Tenant\Overtime\EmployeeOvertimeController;
use App\Http\Controllers\Tenant\Attendance\AttendanceAdminController;
use App\Http\Controllers\Tenant\Attendance\ShiftManagementController;
use App\Http\Controllers\Tenant\Settings\LeaveTypeSettingsController;
use App\Http\Controllers\Tenant\Settings\AttendanceSettingsController;
use App\Http\Controllers\Tenant\Attendance\AttendanceEmployeeController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;

Route::get('/', function () {
    return redirect('login');
});

Route::get('/login', [AuthController::class, 'loginIndex'])->name('login')->middleware([RedirectIfAuthenticated::class]);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/no-permission', function () {
    return view('errors.permission');
})->name('no-permission');

Route::middleware([EnsureUserIsAuthenticated::class])->group(function () {

    Route::middleware(['isSuperAdmin'])->group(function () {
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
    Route::get('/admin-dashboard', [TenantDashboardController::class, 'adminDashboard'])->name('admin-dashboard')->middleware(CheckPermission::class . ':1');
    Route::get('/employee-dashboard', [TenantDashboardController::class, 'employeeDashboard'])->name('employee-dashboard')->middleware(CheckPermission::class . ':2');

    //User Management
    //   User
    Route::get('/users', [UserManagementController::class, 'userIndex'])->name('users')->middleware(CheckPermission::class . ':30');
    Route::get('/users-filter', [UserManagementController::class, 'userFilter'])->name('user-filter');
    Route::get('/get-user-permission-details', [UserManagementController::class, 'getUserPermissionDetails'])->name('get-user-permission-details');
    Route::post('/edit-user-permission', [UserManagementController::class, 'editUserPermission'])->name('edit-user-permission');
    Route::post('/edit-user-data-access-level', [UserManagementController::class, 'editUserDataAccessLevel'])->name('edit-user-data-access-level');
    //  Roles
    Route::get('/roles-permission', [UserManagementController::class, 'roleIndex'])->name('roles-permissions')->middleware(CheckPermission::class . ':31');
    Route::get('/role-filter', [UserManagementController::class, 'roleFilter'])->name('role-filter');
    Route::get('/get-role-details', [UserManagementController::class, 'getRoleDetails'])->name('get-role-details');
    Route::post('/add-role', [UserManagementController::class, 'addRole'])->name('add-role');
    Route::post('/edit-role', [UserManagementController::class, 'editRole'])->name('edit-role');
    Route::get('/get-role-permission-details', [UserManagementController::class, 'getRolePermissionDetails'])->name('get-role-permission-details');
    Route::post('/edit-role-permission', [UserManagementController::class, 'editRolePermission'])->name('edit-role-permission');


    // Employees
    Route::get('/employees', [EmployeeListController::class, 'employeeListIndex'])->name('employees')->middleware(CheckPermission::class . ':9');
    Route::get('/employee-list-filter', [EmployeeListController::class, 'employeeListFilter'])->name('empList-filter');
    Route::get('/employee-branch-auto-filter', [EmployeeListController::class, 'branchAutoFilter'])->name('branchAuto-filter');
    Route::get('/employee-department-auto-filter', [EmployeeListController::class, 'departmentAutoFilter'])->name('departmentAuto-filter');
    Route::get('/employee-designation-auto-filter', [EmployeeListController::class, 'designationAutoFilter'])->name('designationAuto-filter');
    Route::get('/employee-get-details', [EmployeeListController::class, 'getEmployeeDetails'])->name('getEmployeeDetails');
    Route::post('/employee-add', [EmployeeListController::class, 'employeeAdd'])->name('employeeAdd');
    Route::post('/employee-edit', [EmployeeListController::class, 'employeeEdit'])->name('employeeEdit');
    Route::post('/employee-delete', [EmployeeListController::class, 'employeeDelete'])->name('employeeDelete');
    Route::post('/employee-deactivate', [EmployeeListController::class, 'employeeDeactivate'])->name('employeeDeactivate');
    Route::post('/employee-activate', [EmployeeListController::class, 'employeeActivate'])->name('employeeActivate');
    Route::get('/get-designations/{department}', [EmployeeListController::class, 'getByDepartment']);
    Route::post('/employee/import', [EmployeeListController::class, 'importEmployeeCSV'])->name('importEmployeeCSV'); // Import Employee CSV
    Route::get('/employee/download-template', [EmployeeListController::class, 'downloadEmployeeTemplate'])->name('downloadEmployeeTemplate');
    Route::get('/employee/get-next-employee-id', [EmployeeListController::class, 'getNextEmployeeId'])->name('getNextEmployeeId');
    Route::get('/employee/export', [EmployeeListController::class, 'exportEmployee'])->name('exportEmployee');

    // == Details == //
    Route::get('/employees/employee-details/{id}', [EmployeeDetailsController::class, 'employeeDetails'])->name('employee-details');

    // Department and Designation
    Route::get('/departments', [DepartmentController::class, 'departmentIndex'])->name('departments')->middleware(CheckPermission::class . ':10');
    Route::get('/department-list-filter', [DepartmentController::class, 'departmentListFilter'])->name('deptList-filter');
    Route::post('/departments/update/{id}', [DepartmentController::class, 'departmentUpdate'])->name('departmentUpdate');
    Route::get('/designations', [DesignationController::class, 'designationIndex'])->name('designations')->middleware(CheckPermission::class . ':11');
    Route::get('/designation-filter', [DesignationController::class, 'designationFilter'])->name('designation-filter');
    Route::get('/designation-branch-filter', [DesignationController::class, 'designation_branchFilter'])->name('designationBranch-filter');
    Route::get('/designation-department-filter', [DesignationController::class, 'designation_departmentFilter'])->name('designationDepartment-filter');
    Route::get('/designations/departments/{branchId}', [DesignationController::class, 'getDepartmentsByBranch']);
    Route::post('/designations/update/{id}', [DesignationController::class, 'designationUpdate'])->name('designationUpdate');

    //Salary Record Index
    Route::get('/employees/employee-details/{id}/salary-records', [SalaryController::class, 'salaryRecordIndex'])->name('salaryRecord');

    // Shift Management
    Route::get('/shift-management', [ShiftManagementController::class, 'shiftIndex'])->name('shift-management')->middleware(CheckPermission::class . ':16');
    Route::get('/shift-list', [ShiftManagementController::class, 'shiftList'])->name('shift-list');

    //Settings
    Route::get('/settings/attendance-settings', [AttendanceSettingsController::class, 'attendanceSettingsIndex'])->name('attendance-settings')->middleware(CheckPermission::class . ':18');
    Route::get('/settings/leave-type', [LeaveTypeSettingsController::class, 'leaveTypeSettingsIndex'])->name('leave-type')->middleware(CheckPermission::class . ':43');
    Route::get('/settings/approval-steps', [ApprovalController::class, 'approvalIndex'])->name('approval-steps')->middleware(CheckPermission::class . ':43');
    Route::get('/settings/custom-fields', [CustomfieldController::class, 'customfieldIndex'])->name('custom-fields');

    // Geofence
    Route::get('/settings/geofence', [GeofenceController::class, 'geofenceIndex'])->name('geofence-settings');

    // Attendance
    Route::get('/attendance-employee', [AttendanceEmployeeController::class, 'employeeAttendanceIndex'])->name('attendance-employee')->middleware(CheckPermission::class . ':15');
    Route::get('/attendance-admin', [AttendanceAdminController::class, 'adminAttendanceIndex'])->name('attendance-admin')->middleware(CheckPermission::class . ':14');
    Route::post('/attendance-admin/upload', [AttendanceAdminController::class, 'importAttendanceCSV'])->name('importAttendanceCSV'); // Import Attendance CSV
    Route::post('/attendance-admin/bulk-upload', [AttendanceAdminController::class, 'bulkImportAttendanceCSV'])->name('bulkImportAttendanceCSV'); // Bulk Import Attendance CSV
    Route::get('/attendance-admin/download-template', [AttendanceAdminController::class, 'downloadAttendanceTemplate'])->name('downloadAttendanceTemplate');
    Route::get('/attendance-admin/download-bulk-template', [AttendanceAdminController::class, 'downloadAttendanceBulkImportTemplate'])->name('downloadAttendanceBulkImportTemplate');

    //Leave UI
    Route::get('/leave/leave-settings', [LeaveSettingsController::class, 'LeaveSettingsIndex'])->name('leave-settings')->middleware(CheckPermission::class . ':21');
    Route::get('/leave/leave-employee', [LeaveEmployeeController::class, 'leaveEmployeeIndex'])->name('leave-employees')->middleware(CheckPermission::class . ':20');
    Route::get('/leave/leave-admin', [LeaveAdminController::class, 'leaveAdminIndex'])->name('leave-admin')->middleware(CheckPermission::class . ':19');

    // Holiday
    Route::get('/holidays', [HolidayController::class, 'holidayIndex'])->name('holidays')->middleware(CheckPermission::class . ':13');
    Route::get('/holidays/holiday-exception', [HolidayController::class, 'holidayExceptionIndex'])->name('holiday-exception');

    // Branch
    Route::get('/branches', [BranchController::class, 'branchIndex'])->name('branch-grid')->middleware(CheckPermission::class . ':8');;

    // Policy
    Route::get('/policy', [PolicyController::class, 'policyIndex'])->name('policy')->middleware(CheckPermission::class . ':12');

    // Resignation
    Route::get('/resignation', [ResignationController::class, 'resignationIndex'])->name('resignation')->middleware(CheckPermission::class . ':22');

    // Termination
    Route::get('/termination', [TerminationController::class, 'terminationIndex'])->name('termination')->middleware(CheckPermission::class . ':23');

    // Knowledge Base
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'knowledgeBaseIndex'])->name('knowledgebase')->middleware(CheckPermission::class . ':28');

    //Overtime
    Route::get('/overtime', [OvertimeController::class, 'overtimeIndex'])->name('overtime')->middleware(CheckPermission::class . ':17');
    Route::get('/overtime-employee', [EmployeeOvertimeController::class, 'overtimeEmployeeIndex'])->name('overtime-employee')->middleware(CheckPermission::class . ':45');
    Route::post('/overtime/upload', [OvertimeController::class, 'importOvertimeCSV'])->name('importOvertimeCSV'); // Import Overtime CSV
    Route::get('/overtime/download-template', [OvertimeController::class, 'downloadOvertimeTemplate'])->name('downloadOvertimeTemplate');

    // Payroll Items
    Route::get('/payroll/payroll-items/sss-contribution', [PayrollItemsController::class, 'payrollItemsSSSContribution'])->name('sss-contributionTable');
    Route::get('/payroll/payroll-items/philhealth-contribution', [PayrollItemsController::class, 'payrollItemsPhilHealthContribution'])->name('philhealth');
    Route::get('/payroll/payroll-items/withholding-tax', [PayrollItemsController::class, 'payrollItemsWithholdingTax'])->name('withholding-taxTable');
    Route::get('/payroll/payroll-items/overtime-table', [PayrollItemsController::class, 'payrollItemsOTtable'])->name('ot-table');
    Route::get('/payroll/payroll-items/de-minimis-table', [PayrollItemsController::class, 'payrollItemsDeMinimisTable'])->name('de-minimis-benefits');
    Route::get('/payroll/payroll-items/de-minimis-user', [PayrollItemsController::class, 'userDeminimisIndex'])->name('de-minimis-user');
    Route::get('/payroll/payroll-items/earnings', [EarningsController::class, 'earningIndex'])->name('earnings');
    Route::get('/payroll/payroll-items/earnings/user', [EarningsController::class, 'userEarningIndex'])->name('user-earnings');
    Route::get('/payroll/payroll-items/deductions', [DeductionsController::class, 'deductionIndex'])->name('deductions');
    Route::get('/payroll/payroll-items/deductions/user', [DeductionsController::class, 'userDeductionIndex'])->name('user-deductions');

    // Bank
    Route::get('/bank', [BankController::class, 'bankIndex'])->name('bank');

    // Payroll Process
    Route::get('/payroll', [PayrollController::class, 'payrollProcessIndex'])->name('payroll-process');
});
