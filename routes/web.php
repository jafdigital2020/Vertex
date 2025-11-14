<?php

use App\Http\Controllers\Tenant\Employees\SuspensionController;
use App\Http\Controllers\Tenant\Settings\BioController;
use App\Http\Controllers\Tenant\Zkteco\BiometricsController;
use App\Models\User;
use App\Models\Assets;
use App\Models\Department;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Notifications\UserNotification;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckPermission;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayrollBatchController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Controllers\Tenant\HolidayController;
use App\Http\Middleware\EnsureUserIsAuthenticated;
use Illuminate\Notifications\DatabaseNotification;
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
use App\Http\Controllers\Tenant\Payroll\PayslipController;
use App\Http\Controllers\Tenant\Profile\ProfileController;
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
use App\Http\Controllers\Tenant\OB\OfficialBusinessController;
use App\Http\Controllers\Tenant\Employees\SalaryBondController;
use App\Http\Controllers\Tenant\Payroll\PayrollItemsController;
use App\Http\Controllers\Tenant\Report\PayrollReportController;
use App\Http\Controllers\Tenant\Settings\CustomfieldController;
use App\Http\Controllers\Tenant\Employees\ResignationController;
use App\Http\Controllers\Tenant\Employees\TerminationController;
use App\Http\Controllers\Tenant\Support\KnowledgeBaseController;
use App\Http\Controllers\Tenant\Employees\EmployeeListController;
use App\Http\Controllers\Tenant\OB\AdminOfficialBusinessController;
use App\Http\Controllers\Tenant\Employees\EmployeeDetailsController;
use App\Http\Controllers\Tenant\Overtime\EmployeeOvertimeController;
use App\Http\Controllers\Tenant\Attendance\AttendanceAdminController;
use App\Http\Controllers\Tenant\Attendance\ShiftManagementController;
use App\Http\Controllers\Tenant\Settings\LeaveTypeSettingsController;
use App\Http\Controllers\Tenant\Settings\AttendanceSettingsController;
use App\Http\Controllers\Tenant\Attendance\AttendanceEmployeeController;
use App\Http\Controllers\Tenant\Attendance\AttendanceRequestAdminController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\Employees\InactiveListController;
use App\Http\Controllers\Tenant\Payroll\AllowanceController;

Route::get('/', function () {
    return redirect('login');
});

Route::match(['get', 'post'], '/iclock/cdata',      [BiometricsController::class, 'cdata']);
Route::get('/iclock/getrequest',                    [BiometricsController::class, 'getRequest']);
Route::post('/iclock/devicecmd',                    [BiometricsController::class, 'deviceCommand']);

Route::match(['get', 'post'], '/iclock/cdata.aspx',  [BiometricsController::class, 'cdata']);
Route::get('/iclock/getrequest.aspx',               [BiometricsController::class, 'getRequest']);
Route::post('/iclock/devicecmd.aspx',               [BiometricsController::class, 'deviceCommand']);

Route::match(['get', 'post'], '/cdata',              [BiometricsController::class, 'cdata']);
Route::match(['get', 'post'], '/cdata.aspx',         [BiometricsController::class, 'cdata']);

// Add BioTime routes
Route::prefix('biotime')->group(function () {
    Route::get('/test-connection', [BiometricsController::class, 'testBioTimeConnection']);
    Route::get('/fetch-attendance', [BiometricsController::class, 'fetchAttendanceFromBioTime']);
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
    Route::get('/employee-dashboard/attendance-analytics', [TenantDashboardController::class, 'getAttendanceAnalytics'])->name('attendance-analytics');
    Route::get('/employee-dashboard/leave-analytics', [TenantDashboardController::class, 'getLeaveAnalytics'])->name('leave-analytics');
    Route::get('/employee-dashboard/attendance-bar-data', [TenantDashboardController::class, 'getAttendanceBarData'])->name('employee-dashboard.attendance-bar-data');
    Route::get('/employee-dashboard/user-shifts', [TenantDashboardController::class, 'getUserShiftsForWidget'])->name('employee-dashboard.user-shifts');
    Route::get('/admin-dashboard/attendance-overview', [TenantDashboardController::class, 'attendanceSummaryToday'])->name('attendance-overview');
    Route::get('/admin-dashboard/payroll-overview', [TenantDashboardController::class, 'payrollOverview'])->name('payroll-overview');
    Route::get('/admin-dashboard/overtime-overview', [TenantDashboardController::class, 'overtimeOverview'])->name('overtime-overview');

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

    // SG LIST
    Route::get('/employees/security-guards', [EmployeeListController::class, 'sgListIndex'])->name('security-guards');
    Route::get('/employees/security-guards-filter', [EmployeeListController::class, 'sgListFilter'])->name('sgList-filter');
    // Inactive List
    Route::get('employees/inactive', [InactiveListController::class, 'hoInactiveIndex'])->name('inactive-employees');
    Route::get('employees/inactive-filter', [InactiveListController::class, 'hoInactiveIndexFilter'])->name('inactive-employees-filter');
    Route::get('employees/security-guards/inactive', [InactiveListController::class, 'sgInactiveIndex'])->name('inactive-security-guards');
    Route::get('employees/security-guards/inactive-filter', [InactiveListController::class, 'sgInactiveIndexFilter'])->name('inactive-security-guards-filter');

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

    //Salary Record
    Route::get('/employees/employee-details/{id}/salary-records', [SalaryController::class, 'salaryRecordIndex'])->name('salaryRecord');
    Route::get('/employees/employee-details/salary-records/filter', [SalaryController::class, 'salaryRecordFilter'])->name('salaryRecordFilter');

    //Salary Bond
    Route::get('/employees/employee-details/{id}/salary-bond', [SalaryBondController::class, 'salaryBondIndex'])->name('salaryBond');

    // Shift Management
    Route::get('/shift-management', [ShiftManagementController::class, 'shiftIndex'])->name('shift-management')->middleware(CheckPermission::class . ':16');
    Route::get('/shifts-management-filter', [ShiftManagementController::class, 'shiftManagementFilter'])->name('shiftmanagement.filter');
    Route::get('/shift-list', [ShiftManagementController::class, 'shiftList'])->name('shift-list');
    Route::get('/shift-list-filter', [ShiftManagementController::class, 'shiftListfilter'])->name('shiftList-filter');
    //Settings
    Route::get('/settings/attendance-settings', [AttendanceSettingsController::class, 'attendanceSettingsIndex'])->name('attendance-settings')->middleware(CheckPermission::class . ':18');
    Route::get('/settings/leave-type', [LeaveTypeSettingsController::class, 'leaveTypeSettingsIndex'])->name('leave-type')->middleware(CheckPermission::class . ':43');
    Route::get('/settings/approval-steps', [ApprovalController::class, 'approvalIndex'])->name('approval-steps')->middleware(CheckPermission::class . ':43');
    Route::get('/settings/custom-fields', [CustomfieldController::class, 'customfieldIndex'])->name('custom-fields');
    Route::get('/settings/biometrics', [BioController::class, 'biometricsIndex'])->name('biometrics')->middleware(CheckPermission::class . ':43');

    // Geofence
    Route::get('/settings/geofence', [GeofenceController::class, 'geofenceIndex'])->name('geofence-settings');
    Route::get('/settings/geofence/location', [GeofenceController::class, 'locationFilter'])->name('geofence-location-filter');
    Route::get('/settings/geofence/user', [GeofenceController::class, 'userFilter'])->name('geofence-user-filter');
    // Attendance
    Route::get('/attendance-employee', [AttendanceEmployeeController::class, 'employeeAttendanceIndex'])->name('attendance-employee')->middleware(CheckPermission::class . ':15');
    Route::get('/attendance-employee-filter', [AttendanceEmployeeController::class, 'filter'])->name('attendance-employee-filter');
    Route::get('/attendance-employee/request-attendance', [AttendanceEmployeeController::class, 'requestAttendanceIndex'])->name('attendance-request');
    Route::get('/attendance-employee/request-attendance-filter', [AttendanceEmployeeController::class, 'requestAttendanceFilter'])->name('attendance-request-filter');

    Route::get('/attendance-admin', [AttendanceAdminController::class, 'adminAttendanceIndex'])->name('attendance-admin')->middleware(CheckPermission::class . ':14');
    Route::get('/attendance-admin-filter', [AttendanceAdminController::class, 'filter'])->name('attendance-admin-filter');
    Route::post('/attendance-admin/upload', [AttendanceAdminController::class, 'importAttendanceCSV'])->name('importAttendanceCSV'); // Import Attendance CSV
    Route::post('/attendance-admin/bulk-upload', [AttendanceAdminController::class, 'bulkImportAttendanceCSV'])->name('bulkImportAttendanceCSV'); // Bulk Import Attendance CSV
    Route::get('/attendance-admin/download-template', [AttendanceAdminController::class, 'downloadAttendanceTemplate'])->name('downloadAttendanceTemplate');
    Route::get('/attendance-admin/download-bulk-template', [AttendanceAdminController::class, 'downloadAttendanceBulkImportTemplate'])->name('downloadAttendanceBulkImportTemplate');
    Route::get('/attendance-admin/bulk-attendance', [AttendanceAdminController::class, 'bulkAdminAttendanceIndex'])->name('bulkAdminAttendanceIndex');
    Route::get('/attendance-admin/bulk-attendance-filter', [AttendanceAdminController::class, 'bulkAdminAttendanceFilter'])->name('bulkAdminAttendanceFilter');
    Route::get('/attendance-admin/request-attendance', [AttendanceRequestAdminController::class, 'adminRequestAttendanceIndex'])->name('adminRequestAttendance');
    Route::get('/attendance-admin/request-attendance-filter', [AttendanceRequestAdminController::class, 'filter'])->name('adminRequestAttendanceFilter');
    //Leave UI
    Route::get('/leave/leave-settings', [LeaveSettingsController::class, 'LeaveSettingsIndex'])->name('leave-settings')->middleware(CheckPermission::class . ':21');
    Route::get('/leave/leave-employee', [LeaveEmployeeController::class, 'leaveEmployeeIndex'])->name('leave-employees')->middleware(CheckPermission::class . ':20');
    Route::get('/leave/leave-employee-filter', [LeaveEmployeeController::class, 'filter'])->name('leave-employees-filter');
    Route::get('/leave/leave-admin', [LeaveAdminController::class, 'leaveAdminIndex'])->name('leave-admin')->middleware(CheckPermission::class . ':19');
    Route::get('/leave/leave-admin-filter', [LeaveAdminController::class, 'filter'])->name('leave-admin-filter');
    Route::get('/leave/leave-settings/{id}/assigned-users', [LeaveSettingsController::class, 'assignedUsersIndex'])->name('assignedUsersIndex');
    // Holiday
    Route::get('/holidays', [HolidayController::class, 'holidayIndex'])->name('holidays')->middleware(CheckPermission::class . ':13');
    Route::get('/holiday-filter', [HolidayController::class, 'holidayFilter'])->name('holiday_filter');
    Route::get('/holidays/holiday-exception', [HolidayController::class, 'holidayExceptionIndex'])->name('holiday-exception');
    Route::get('/holidayEx-filter', [HolidayController::class, 'holidayExFilter'])->name('holidayEx_filter');

    // Branch
    Route::get('/branches', [BranchController::class, 'branchIndex'])->name('branch-grid')->middleware(CheckPermission::class . ':8');;
    Route::get('/branches/filter', [BranchController::class, 'filter'])->name('branches.filter');
    Route::get('/branches/by-group', [BranchController::class, 'getByGroup'])
        ->name('branches.getByGroup');

    // Policy
    Route::get('/policy', [PolicyController::class, 'policyIndex'])->name('policy')->middleware(CheckPermission::class . ':12');
    Route::get('/policy-filter', [PolicyController::class, 'filter'])->name('policy_filter');

    // Resignation
    Route::get('/resignation-admin-filter', [ResignationController::class, 'filter'])->name('resignation-admin-filter');
    Route::get('/resignation/admin', [ResignationController::class, 'resignationAdminIndex'])->name('resignation-admin')->middleware(CheckPermission::class . ':22');
    Route::get('/resignation/employee', [ResignationController::class, 'resignationEmployeeIndex'])->name('resignation-employee')->middleware(CheckPermission::class . ':58');
    Route::get('/resignation/hr', [ResignationController::class, 'resignationHRIndex'])->name('resignation-hr')->middleware(CheckPermission::class . ':59');
    Route::get('/resignation-hr-filter', [ResignationController::class, 'HRfilter'])->name('resignation-hr-filter');

    // Termination
    Route::get('/termination', [TerminationController::class, 'terminationIndex'])->name('termination')->middleware(CheckPermission::class . ':23');


    //Suspension
    Route::get('/suspension/admin', [SuspensionController::class, 'adminSuspensionEmployeeListIndex'])->name('suspension-admin')->middleware(CheckPermission::class . ':60');
    Route::get('/suspension/admin-filter', [SuspensionController::class, 'filter'])->name('suspension-admin-filter');
    // Route::get('/suspension/employees', [SuspensionController::class, 'suspensionEmployeeListIndex'])->name('suspension-employee-list')->middleware(CheckPermission::class . ':60');
    Route::get('/suspension/employee', [SuspensionController::class, 'suspensionEmployeeListIndex'])->name('suspension-employee-list')->middleware(CheckPermission::class . ':61');
    Route::get('/suspension/employee-filter', [SuspensionController::class, 'empfilter'])->name('suspension-employee-filter');

    Route::get('/suspension/employees-by-branch', [SuspensionController::class, 'getEmployeesByBranch'])
        ->name('suspension.employees-by-branch');


    // Knowledge Base
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'knowledgeBaseIndex'])->name('knowledgebase')->middleware(CheckPermission::class . ':28');

    //Overtime
    Route::get('/overtime', [OvertimeController::class, 'overtimeIndex'])->name('overtime')->middleware(CheckPermission::class . ':17');
    Route::get('/overtime-admin-filter', [OvertimeController::class, 'filter'])->name('overtime-admin-filter');

    Route::get('/overtime-employee', [EmployeeOvertimeController::class, 'overtimeEmployeeIndex'])->name('overtime-employee')->middleware(CheckPermission::class . ':45');
    Route::get('/overtime-employee-filter', [EmployeeOvertimeController::class, 'filter'])->name('overtime-employee-filter');
    Route::post('/overtime/upload', [OvertimeController::class, 'importOvertimeCSV'])->name('importOvertimeCSV'); // Import Overtime CSV
    Route::get('/overtime/download-template', [OvertimeController::class, 'downloadOvertimeTemplate'])->name('downloadOvertimeTemplate');

    // Payroll Items
    Route::get('/payroll/payroll-items/sss-contribution', [PayrollItemsController::class, 'payrollItemsSSSContribution'])->name('sss-contributionTable')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/sss-contribution-filter', [PayrollItemsController::class, 'payrollItemsSSSContributionFilter'])->name('sss-contributionTable-filter');
    Route::get('/payroll/payroll-items/philhealth-contribution', [PayrollItemsController::class, 'payrollItemsPhilHealthContribution'])->name('philhealth')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/philhealth-contribution-filter', [PayrollItemsController::class, 'payrollItemsPhilHealthContributionFilter'])->name('philhealth-filter');
    Route::get('/payroll/payroll-items/withholding-tax', [PayrollItemsController::class, 'payrollItemsWithholdingTax'])->name('withholding-taxTable')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/withholding-tax-filter', [PayrollItemsController::class, 'payrollItemsWithholdingTaxFilter'])->name('withholding-taxTable-filter');
    Route::get('/payroll/payroll-items/overtime-table', [PayrollItemsController::class, 'payrollItemsOTtable'])->name('ot-table')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/overtime-table-filter', [PayrollItemsController::class, 'payrollItemsOTtableFilter'])->name('ot-table-filter');
    Route::get('/payroll/payroll-items/de-minimis-table', [PayrollItemsController::class, 'payrollItemsDeMinimisTable'])->name('de-minimis-benefits')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/de-minimis-table-filter', [PayrollItemsController::class, 'payrollItemsDeMinimisTableFilter'])->name('de-minimis-benefits-filter');
    Route::get('/payroll/payroll-items/de-minimis-user', [PayrollItemsController::class, 'userDeminimisIndex'])->name('de-minimis-user')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/de-minimis-user-filter', [PayrollItemsController::class, 'userDeminimisFilter'])->name('de-minimis-user-filter');
    Route::get('/payroll/payroll-items/earnings', [EarningsController::class, 'earningIndex'])->name('earnings')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/earnings-filter', [EarningsController::class, 'earningFilter'])->name('earnings-filter');
    Route::get('/payroll/payroll-items/earnings/user', [EarningsController::class, 'userEarningIndex'])->name('user-earnings')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/earnings/user-filter', [EarningsController::class, 'userEarningsFilter'])->name('user-earnings-filter');
    Route::get('/payroll/payroll-items/deductions', [DeductionsController::class, 'deductionIndex'])->name('deductions')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/deductions-filter', [DeductionsController::class, 'deductionsFilter'])->name('deductions-filter');
    Route::get('/payroll/payroll-items/deductions/user', [DeductionsController::class, 'userDeductionIndex'])->name('user-deductions')->middleware(CheckPermission::class . ':26');
    Route::get('/payroll/payroll-items/deductions/user-filter', [DeductionsController::class, 'userDeductionFilter'])->name('user-deductions-filter');

    // Allowance
    Route::get('/payroll/payroll-items/allowance', [AllowanceController::class, 'payrollItemsAllowance'])->name('allowance');

    // User Allowance
    Route::get('/employees/employee-details/{id}/allowance', [AllowanceController::class, 'userAllowanceIndex'])->name('userAllowanceIndex');

    // Bank
    Route::get('/bank', [BankController::class, 'bankIndex'])->name('bank');

    // Payroll Process
    Route::get('/payroll', [PayrollController::class, 'payrollProcessIndex'])->name('payroll-process');
    Route::get('/payroll-filter', [PayrollController::class, 'payrollProcessIndexFilter'])->name('payroll-process-filter');
    Route::get('/payroll/generated-payslips', [PayslipController::class, 'generatedPayslipIndex'])->name('generatedPayslipIndex');
    Route::get('/payroll/generated-payslips-filter', [PayslipController::class, 'filter'])->name('generatedPayslipIndex-filter');
    Route::get('/payroll/generated-payslips/{id}', [PayslipController::class, 'generatedPayslips'])->name('generatedPayslips');
    Route::get('/payslip', [PayslipController::class, 'userPayslipIndex'])->name('payslip');

    // Payroll Batch
    Route::get('/payroll/batch/users', [PayrollBatchController::class, 'payrollBatchUsersIndex'])->name('payroll-batch-users');
    Route::get('/payroll/batch/users_filter', [PayrollBatchController::class, 'payrollBatchUsersFilter'])->name('payroll-batch-users-filter');
    Route::post('/payroll/batch/users/update', [PayrollBatchController::class, 'payrollBatchUsersUpdate'])->name('payroll-batch-users-update');
    Route::post('/payroll/batch/users/bulk-assign', [PayrollBatchController::class, 'payrollBatchBulkAssign'])->name('payroll-batch-bulk-assign');
    Route::post('/fetch-departments', [PayrollBatchController::class, 'fetchDepartments'])->name('fetch.departments');
    Route::post('/fetch-designations', [PayrollBatchController::class, 'fetchDesignations'])->name('fetch.designations');
    Route::post('/fetch-employees', [PayrollBatchController::class, 'fetchEmployees'])->name('fetch.employees');
    Route::post('/payroll-batch/check-duplicate', [PayrollBatchController::class, 'checkDuplicatePayroll'])
        ->name('payroll-batch.check-duplicate');


    Route::get('/payroll/batch/settings', [PayrollBatchController::class, 'payrollBatchSettingsIndex'])->name('payroll-batch-settings');
    Route::post('/payroll/batch/settings/store', [PayrollBatchController::class, 'payrollBatchSettingsStore'])->name('payroll-batch-settings-store');
    Route::post('/payroll/batch/settings/update', [PayrollBatchController::class, 'payrollBatchSettingsUpdate'])->name('payroll-batch-settings-update');
    Route::post('/payroll/batch/settings/delete', [PayrollBatchController::class, 'payrollBatchSettingsDelete'])->name('payroll-batch-settings-delete');

    //User Payslip
    Route::get('/payslip', [PayslipController::class, 'userPayslipIndex'])->name('user-payslip');
    Route::get('/payslip/view/{id}', [PayslipController::class, 'userGeneratedPayslip'])->name('userGeneratedPayslip');

    // Notifications
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.ajaxMarkAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.ajaxMarkAllAsRead');

    // Auth User Profile
    Route::get('/profile', [ProfileController::class, 'profileIndex'])->name('profile');

    // Official Business
    Route::get('/official-business/admin', [AdminOfficialBusinessController::class, 'adminOBIndex'])->name('ob-admin')->middleware(CheckPermission::class . ':47');
    Route::get('/official-business/admin-filter', [AdminOfficialBusinessController::class, 'filter'])->name('ob-admin-filter');
    Route::get('/official-business/employee', [OfficialBusinessController::class, 'employeeOBIndex'])->name('ob-employee')->middleware(CheckPermission::class . ':48');
    Route::get('/official-business/employee-filter', [OfficialBusinessController::class, 'filter'])->name('ob-employee-filter');


    Route::get('/employee-assets', [AssetsController::class, 'employeeAssetsIndex'])->name('employee-assets')->middleware(CheckPermission::class . ':49');
    Route::get('/employee-assets-filter', [AssetsController::class, 'employeeAssetsFilter'])->name('employee-assets-filter');
    Route::get('/employee-assets/by-category/{id}', [AssetsController::class, 'getAssetsByCategory'])->name('assets-by-category');
    Route::get('/employee-assets/{id}', [AssetsController::class, 'getEmployeeAssets'])->name('employee.assets');
    Route::get('/employee-assets/{id}/remarks', [AssetsController::class, 'getLatestRemarks']);

    Route::post('/employee-assets-create', [AssetsController::class, 'employeeAssetsStore'])->name('employee-assets-create');
    Route::get('/employee-assets-history', [AssetsController::class, 'employeeAssetsHistoryIndex'])->name('employee-assets-history')->middleware(CheckPermission::class . ':49');
    Route::get('/employee-assets-history-filter', [AssetsController::class, 'employeeAssetsHistoryFilter'])->name('employee-assets-history-filter');
    Route::get('/assets-settings', [AssetsController::class, 'assetsSettingsIndex'])->name('assets-settings')->middleware(CheckPermission::class . ':50');
    Route::get('/assets-settings-filter', [AssetsController::class, 'assetsSettingsFilter'])->name('assets-settings-filter');
    Route::get('/assets-settings-details', [AssetsController::class, 'assetsSettingsDetails'])->name('assets-settings-details');
    Route::post('/assets-settings-details/update', [AssetsController::class, 'assetsSettingsDetailsUpdate'])->name('assetsSettingsDetailsUpdate');
    Route::post('/assets-settings/create', [AssetsController::class, 'assetsSettingsStore'])->name('assetsSettingsStore');
    Route::post('/assets-settings/update', [AssetsController::class, 'assetsSettingsUpdate'])->name('assetsSettingsUpdate');
    Route::post('/assets-settings/delete', [AssetsController::class, 'assetsSettingsDelete'])->name('assetsSettingsDelete');
    Route::get('/assets-settings-history', [AssetsController::class, 'assetsSettingsHistoryIndex'])->name('assets-settings-history')->middleware(CheckPermission::class . ':50');
    Route::get('/assets-settings-history-filter', [AssetsController::class, 'assetsSettingsHistoryFilter'])->name('assets-history-filter');

    // export assets 
    Route::get('/export-asset-pdf/{assetDetailId}/{userId}', [AssetsController::class, 'exportAssetPDF'])->name('export.asset.pdf');
});

Route::get('/send-test-notif', function () {
    $user = User::find(47);
    $user->notify(new UserNotification('Welcome! This is your test notification.'));
    return 'Notification Sent!';
});

// Payroll Report
Route::get('/reports/payroll', [PayrollReportController::class, 'payrollReportIndex'])->name('payroll-report');
