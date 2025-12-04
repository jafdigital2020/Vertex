<?php

use App\Models\User;
use App\Models\Assets;
use App\Models\Department;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
use App\Http\Controllers\Tenant\Settings\BioController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\Tenant\Branch\BranchController;
use App\Http\Controllers\Tenant\Policy\PolicyController;
use App\Http\Controllers\Tenant\UserManagementController;
use App\Http\Controllers\Tenant\Billing\BillingController;
use App\Http\Controllers\Tenant\Billing\InvoiceController;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Http\Controllers\Tenant\Payroll\PayslipController;
use App\Http\Controllers\Tenant\Profile\ProfileController;
use App\Http\Controllers\SuperAdmin\OrganizationController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\Tenant\Employees\SalaryController;
use App\Http\Controllers\Tenant\Leave\LeaveAdminController;
use App\Http\Controllers\Tenant\Payroll\EarningsController;
use App\Http\Controllers\Tenant\Report\SssReportController;
use App\Http\Controllers\Tenant\Overtime\OvertimeController;
use App\Http\Controllers\Tenant\Payroll\AllowanceController;
use App\Http\Controllers\Tenant\Settings\ApprovalController;
use App\Http\Controllers\Tenant\Settings\GeofenceController;
use App\Http\Controllers\Tenant\Zkteco\BiometricsController;
use App\Http\Controllers\Tenant\Payroll\DeductionsController;
use App\Http\Controllers\Tenant\Leave\LeaveEmployeeController;
use App\Http\Controllers\Tenant\Leave\LeaveSettingsController;
use App\Http\Controllers\Tenant\OB\OfficialBusinessController;
use App\Http\Controllers\Tenant\Payroll\PayrollItemsController;
use App\Http\Controllers\Tenant\Report\PayrollReportController;
use App\Http\Controllers\Tenant\Settings\CustomfieldController;
use App\Http\Controllers\Tenant\Employees\ResignationController;
use App\Http\Controllers\Tenant\Employees\TerminationController;
use App\Http\Controllers\Tenant\Support\KnowledgeBaseController;
use App\Http\Controllers\Tenant\Employees\EmployeeListController;
use App\Http\Controllers\Tenant\Report\AlphalistReportController;
use App\Http\Controllers\Tenant\OB\AdminOfficialBusinessController;
use App\Http\Controllers\Tenant\Employees\EmployeeDetailsController;
use App\Http\Controllers\Tenant\Overtime\EmployeeOvertimeController;
use App\Http\Controllers\Tenant\Attendance\AttendanceAdminController;
use App\Http\Controllers\Tenant\Attendance\ShiftManagementController;
use App\Http\Controllers\Tenant\Settings\LeaveTypeSettingsController;
use App\Http\Controllers\Tenant\Settings\AttendanceSettingsController;
use App\Http\Controllers\Tenant\Attendance\AttendanceEmployeeController;
use App\Http\Controllers\Tenant\Payroll\ThirteenthMonthPayslipController;
use App\Http\Controllers\Tenant\Attendance\AttendanceRequestAdminController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\Billing\PaymentController as TenantPaymentController;
use App\Http\Controllers\Tenant\Billing\SubscriptionController as TenantSubscriptionController;

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
    Route::get('/employee/import-status', [EmployeeListController::class, 'checkImportStatus'])->name('emp.checkImportStatus'); // Check Import Status
    Route::post('/employee/clear-import-status', [EmployeeListController::class, 'clearImportStatus'])->name('emp.clearImportStatus'); // Clear Import Status
    Route::get('/employee/download-template', [EmployeeListController::class, 'downloadEmployeeTemplate'])->name('downloadEmployeeTemplate');
    Route::get('/employee/get-next-employee-id', [EmployeeListController::class, 'getNextEmployeeId'])->name('getNextEmployeeId');
    Route::get('/employee/export', [EmployeeListController::class, 'exportEmployee'])->name('exportEmployee');
    Route::post('/employees/check-license-overage', [EmployeeListController::class, 'checkLicenseOverage'])->name('checkLicenseOverage');
    Route::post('/employees/generate-implementation-fee-invoice', [EmployeeListController::class, 'generateImplementationFeeInvoice'])->name('generateImplementationFeeInvoice');
    Route::post('/employees/generate-plan-upgrade-invoice', [EmployeeListController::class, 'generatePlanUpgradeInvoice'])->name('generatePlanUpgradeInvoice');

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
    Route::get('/leave/assigned-users-filter', [LeaveSettingsController::class, 'filter'])->name('assigned-users-filter');
    // Holiday
    Route::get('/holidays', [HolidayController::class, 'holidayIndex'])->name('holidays')->middleware(CheckPermission::class . ':13');
    Route::get('/holiday-filter', [HolidayController::class, 'holidayFilter'])->name('holiday_filter');
    Route::get('/holidays/holiday-exception', [HolidayController::class, 'holidayExceptionIndex'])->name('holiday-exception');
    Route::get('/holidayEx-filter', [HolidayController::class, 'holidayExFilter'])->name('holidayEx_filter');

    // Branch
    Route::get('/branches', [BranchController::class, 'branchIndex'])->name('branch-grid')->middleware(CheckPermission::class . ':8');;

    // Policy
    Route::get('/policy', [PolicyController::class, 'policyIndex'])->name('policy')->middleware(CheckPermission::class . ':12');
    Route::get('/policy-filter', [PolicyController::class, 'filter'])->name('policy_filter');
    // Resignation
    Route::get('/resignation', [ResignationController::class, 'resignationIndex'])->name('resignation')->middleware(CheckPermission::class . ':22');

    // Termination
    Route::get('/termination', [TerminationController::class, 'terminationIndex'])->name('termination')->middleware(CheckPermission::class . ':23');

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
    Route::get('/payroll/payroll-items/allowance', [AllowanceController::class, 'payrollItemsAllowance'])->name('allowance');
    Route::get('/employees/employee-details/{id}/allowance', [AllowanceController::class, 'userAllowanceIndex'])->name('userAllowanceIndex');

    // Bank
    Route::get('/bank', [BankController::class, 'bankIndex'])->name('bank');

    // Payroll Process
    Route::get('/payroll', [PayrollController::class, 'payrollProcessIndex'])->name('payroll-process');
    Route::get('/payroll-filter', [PayrollController::class, 'payrollProcessIndexFilter'])->name('payroll-process-filter');
    Route::get('/payroll/generated-payslips', [PayslipController::class, 'generatedPayslipIndex'])->name('generatedPayslipIndex');
    Route::get('/payroll/generated-payslips-filter', [PayslipController::class, 'filter'])->name('generatedPayslipIndex-filter');
    Route::get('/payroll/generated-payslips/{id}', [PayslipController::class, 'generatedPayslips'])->name('generatedPayslips');

    // Thirteenth Month Payslip (Admin)
    Route::get('/thirteenth-month-payslip', [ThirteenthMonthPayslipController::class, 'thirteenthMonthPayslipadminIndex'])->name('thirteenthMonthPayslipadminIndex');
    Route::get('/thirteenth-month-payslip/{id}', [ThirteenthMonthPayslipController::class, 'generatedPayslips'])->name('thirteenthMonthPayslipView');

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

    // Payroll Export
    Route::get('/payroll/export-pdf', [PayrollController::class, 'exportPDF'])->name('payroll.export.pdf');
    Route::get('/payroll/export-excel', [PayrollController::class, 'exportExcel'])->name('payroll.export.excel');

    // Payroll Upload
    Route::post('/payroll/upload-payslips', [PayslipController::class, 'uploadPayslips'])->name('uploadPayslips');
    Route::get('/payroll/download-template', [PayslipController::class, 'downloadTemplate'])->name('downloadPayslipTemplate');
    Route::get('/payroll/check-import-status', [PayslipController::class, 'checkImportStatus'])->name('checkImportStatus');

    //User Payslip
    Route::get('/payslip', [PayslipController::class, 'userPayslipIndex'])->name('user-payslip');
    Route::get('/payslip/view/{id}', [PayslipController::class, 'userGeneratedPayslip'])->name('userGeneratedPayslip');

    //User thirteenth month payslip
    Route::get('/thirteenth-month-payslip-user', [ThirteenthMonthPayslipController::class, 'thirteenthMonthPayslipIndex'])->name('thirteenthMonthPayslipIndex');
    Route::get('/thirteenth-month-payslip/view/{id}', [ThirteenthMonthPayslipController::class, 'userGeneratedPayslip'])->name('userThirteenthMonthPayslipView');

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

    // Recruitment Module Routes
    Route::prefix('recruitment')->group(function () {
        // Manpower Requests
        Route::get('/manpower-requests', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'index'])->name('recruitment.manpower-requests.index')->middleware(CheckPermission::class . ':65');
        Route::get('/manpower-requests/create', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'create'])->name('recruitment.manpower-requests.create')->middleware(CheckPermission::class . ':65');
        Route::post('/manpower-requests', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'store'])->name('recruitment.manpower-requests.store')->middleware(CheckPermission::class . ':65');
        Route::get('/manpower-requests/{manpowerRequest}', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'show'])->name('recruitment.manpower-requests.show')->middleware(CheckPermission::class . ':65');
        Route::get('/manpower-requests/{manpowerRequest}/edit', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'edit'])->name('recruitment.manpower-requests.edit')->middleware(CheckPermission::class . ':65');
        Route::put('/manpower-requests/{manpowerRequest}', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'update'])->name('recruitment.manpower-requests.update')->middleware(CheckPermission::class . ':65');
        Route::delete('/manpower-requests/{manpowerRequest}', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'destroy'])->name('recruitment.manpower-requests.destroy')->middleware(CheckPermission::class . ':65');
        Route::post('/manpower-requests/{manpowerRequest}/approve', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'approve'])->name('recruitment.manpower-requests.approve')->middleware(CheckPermission::class . ':65');
        Route::post('/manpower-requests/{manpowerRequest}/reject', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'reject'])->name('recruitment.manpower-requests.reject')->middleware(CheckPermission::class . ':65');
        Route::post('/manpower-requests/{manpowerRequest}/post-job', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'postJob'])->name('recruitment.manpower-requests.post-job')->middleware(CheckPermission::class . ':65');
        Route::post('/manpower-requests/{manpowerRequest}/submit-review', [App\Http\Controllers\Tenant\Recruitment\ManpowerRequestController::class, 'submitForReview'])->name('recruitment.manpower-requests.submit-review')->middleware(CheckPermission::class . ':65');

        // Job Postings
        Route::get('/job-postings', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'index'])->name('recruitment.job-postings.index')->middleware(CheckPermission::class . ':60');
        Route::get('/job-postings/create', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'create'])->name('recruitment.job-postings.create')->middleware(CheckPermission::class . ':60');
        Route::post('/job-postings', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'store'])->name('recruitment.job-postings.store')->middleware(CheckPermission::class . ':60');
        Route::get('/job-postings/{jobPosting}', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'show'])->name('recruitment.job-postings.show')->middleware(CheckPermission::class . ':60');
        Route::get('/job-postings/{jobPosting}/edit', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'edit'])->name('recruitment.job-postings.edit')->middleware(CheckPermission::class . ':60');
        Route::put('/job-postings/{jobPosting}', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'update'])->name('recruitment.job-postings.update')->middleware(CheckPermission::class . ':60');
        Route::delete('/job-postings/{jobPosting}', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'destroy'])->name('recruitment.job-postings.destroy')->middleware(CheckPermission::class . ':60');
        Route::post('/job-postings/{jobPosting}/publish', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'publish'])->name('recruitment.job-postings.publish')->middleware(CheckPermission::class . ':60');
        Route::post('/job-postings/{jobPosting}/close', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'close'])->name('recruitment.job-postings.close')->middleware(CheckPermission::class . ':60');
        Route::post('/job-postings/{jobPosting}/clone', [App\Http\Controllers\Tenant\Recruitment\JobPostingController::class, 'clone'])->name('recruitment.job-postings.clone')->middleware(CheckPermission::class . ':60');

        // Candidates
        Route::get('/candidates', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'index'])->name('recruitment.candidates.index')->middleware(CheckPermission::class . ':61');
        Route::get('/candidates/create', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'create'])->name('recruitment.candidates.create')->middleware(CheckPermission::class . ':61');
        Route::post('/candidates', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'store'])->name('recruitment.candidates.store')->middleware(CheckPermission::class . ':61');
        Route::get('/candidates/{candidate}', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'show'])->name('recruitment.candidates.show')->middleware(CheckPermission::class . ':61');
        Route::get('/candidates/{candidate}/edit', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'edit'])->name('recruitment.candidates.edit')->middleware(CheckPermission::class . ':61');
        Route::put('/candidates/{candidate}', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'update'])->name('recruitment.candidates.update')->middleware(CheckPermission::class . ':61');
        Route::delete('/candidates/{candidate}', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'destroy'])->name('recruitment.candidates.destroy')->middleware(CheckPermission::class . ':61');
        Route::post('/candidates/{candidate}/status', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'updateStatus'])->name('recruitment.candidates.update-status')->middleware(CheckPermission::class . ':61');
        Route::get('/candidates/export', [App\Http\Controllers\Tenant\Recruitment\CandidateController::class, 'export'])->name('recruitment.candidates.export')->middleware(CheckPermission::class . ':61');

        // Job Applications
        Route::get('/applications', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'index'])->name('recruitment.applications.index')->middleware(CheckPermission::class . ':62');
        Route::get('/applications/{application}', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'show'])->name('recruitment.applications.show')->middleware(CheckPermission::class . ':62');
        Route::post('/applications/{application}/status', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'updateStatus'])->name('recruitment.applications.update-status')->middleware(CheckPermission::class . ':62');
        Route::post('/applications/{application}/recruiter', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'assignRecruiter'])->name('recruitment.applications.assign-recruiter')->middleware(CheckPermission::class . ':62');
        Route::post('/applications/{application}/score', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'updateScore'])->name('recruitment.applications.update-score')->middleware(CheckPermission::class . ':62');
        Route::post('/applications/bulk-status', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'bulkUpdateStatus'])->name('recruitment.applications.bulk-status')->middleware(CheckPermission::class . ':62');
        Route::get('/applications/kanban', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'kanbanView'])->name('recruitment.applications.kanban')->middleware(CheckPermission::class . ':62');
        Route::get('/applications/statistics', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'statistics'])->name('recruitment.applications.statistics')->middleware(CheckPermission::class . ':62');
        Route::get('/applications/{application}/workflow', [App\Http\Controllers\Tenant\Recruitment\JobApplicationController::class, 'getWorkflowHistory'])->name('recruitment.applications.workflow')->middleware(CheckPermission::class . ':62');

        // Interviews
        Route::get('/interviews', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'index'])->name('recruitment.interviews.index')->middleware(CheckPermission::class . ':63');
        Route::get('/interviews/create', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'create'])->name('recruitment.interviews.create')->middleware(CheckPermission::class . ':63');
        Route::post('/interviews', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'store'])->name('recruitment.interviews.store')->middleware(CheckPermission::class . ':63');
        Route::get('/interviews/{interview}', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'show'])->name('recruitment.interviews.show')->middleware(CheckPermission::class . ':63');
        Route::get('/interviews/{interview}/edit', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'edit'])->name('recruitment.interviews.edit')->middleware(CheckPermission::class . ':63');
        Route::put('/interviews/{interview}', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'update'])->name('recruitment.interviews.update')->middleware(CheckPermission::class . ':63');
        Route::post('/interviews/{interview}/status', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'updateStatus'])->name('recruitment.interviews.update-status')->middleware(CheckPermission::class . ':63');
        Route::post('/interviews/{interview}/feedback', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'addFeedback'])->name('recruitment.interviews.feedback')->middleware(CheckPermission::class . ':63');
        Route::post('/interviews/{interview}/reschedule', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'reschedule'])->name('recruitment.interviews.reschedule')->middleware(CheckPermission::class . ':63');
        Route::get('/interviews/calendar', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'calendar'])->name('recruitment.interviews.calendar')->middleware(CheckPermission::class . ':63');
        Route::get('/interviews/today', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'todaysInterviews'])->name('recruitment.interviews.today')->middleware(CheckPermission::class . ':63');
        Route::get('/interviews/upcoming', [App\Http\Controllers\Tenant\Recruitment\InterviewController::class, 'upcomingInterviews'])->name('recruitment.interviews.upcoming')->middleware(CheckPermission::class . ':63');

        // Job Offers
        Route::get('/offers', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'index'])->name('recruitment.offers.index')->middleware(CheckPermission::class . ':64');
        Route::get('/offers/create', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'create'])->name('recruitment.offers.create')->middleware(CheckPermission::class . ':64');
        Route::post('/offers', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'store'])->name('recruitment.offers.store')->middleware(CheckPermission::class . ':64');
        Route::get('/offers/{offer}', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'show'])->name('recruitment.offers.show')->middleware(CheckPermission::class . ':64');
        Route::get('/offers/{offer}/edit', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'edit'])->name('recruitment.offers.edit')->middleware(CheckPermission::class . ':64');
        Route::put('/offers/{offer}', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'update'])->name('recruitment.offers.update')->middleware(CheckPermission::class . ':64');
        Route::post('/offers/{offer}/send', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'send'])->name('recruitment.offers.send')->middleware(CheckPermission::class . ':64');
        Route::post('/offers/{offer}/accept', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'accept'])->name('recruitment.offers.accept')->middleware(CheckPermission::class . ':64');
        Route::post('/offers/{offer}/reject', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'reject'])->name('recruitment.offers.reject')->middleware(CheckPermission::class . ':64');
        Route::post('/offers/{offer}/withdraw', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'withdraw'])->name('recruitment.offers.withdraw')->middleware(CheckPermission::class . ':64');
        Route::post('/offers/{offer}/approve', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'approve'])->name('recruitment.offers.approve')->middleware(CheckPermission::class . ':64');
        Route::post('/offers/{offer}/generate-letter', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'generateOfferLetter'])->name('recruitment.offers.generate-letter')->middleware(CheckPermission::class . ':64');
        Route::get('/offers/{offer}/download-letter', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'downloadOfferLetter'])->name('recruitment.offers.download-letter')->middleware(CheckPermission::class . ':64');
        Route::get('/offers/statistics', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'statistics'])->name('recruitment.offers.statistics')->middleware(CheckPermission::class . ':64');
        Route::get('/offers/expired', [App\Http\Controllers\Tenant\Recruitment\JobOfferController::class, 'expiredOffers'])->name('recruitment.offers.expired')->middleware(CheckPermission::class . ':64');
        
        // Candidate Role Management Routes
        Route::prefix('candidate-roles')->group(function () {
            Route::get('/roles', [App\Http\Controllers\Tenant\Recruitment\CandidateRoleController::class, 'getRoles'])->name('recruitment.candidate-roles.get-roles')->middleware(CheckPermission::class . ':61');
            Route::post('/candidates/{candidate}/assign-role', [App\Http\Controllers\Tenant\Recruitment\CandidateRoleController::class, 'assignRole'])->name('recruitment.candidate-roles.assign')->middleware(CheckPermission::class . ':61');
            Route::delete('/candidates/{candidate}/remove-role', [App\Http\Controllers\Tenant\Recruitment\CandidateRoleController::class, 'removeRole'])->name('recruitment.candidate-roles.remove')->middleware(CheckPermission::class . ':61');
            Route::get('/candidates/{candidate}/role', [App\Http\Controllers\Tenant\Recruitment\CandidateRoleController::class, 'getCandidateRole'])->name('recruitment.candidate-roles.get')->middleware(CheckPermission::class . ':61');
            Route::put('/candidates/{candidate}/permissions', [App\Http\Controllers\Tenant\Recruitment\CandidateRoleController::class, 'updatePermissions'])->name('recruitment.candidate-roles.update-permissions')->middleware(CheckPermission::class . ':61');
        });
    });
});

Route::get('/send-test-notif', function () {
    $user = User::find(47);
    $user->notify(new UserNotification('Welcome! This is your test notification.'));
    return 'Notification Sent!';
});


// Payroll Report
Route::get('/reports/payroll', [PayrollReportController::class, 'payrollReportIndex'])->name('payroll-report');
Route::get('/reports/alphalist', [AlphalistReportController::class, 'alphalistReportIndex'])->name('alphalist-report')->middleware(CheckAddon::class . ':alphalist_report');
Route::get('/reports/sss', [SssReportController::class, 'sssReportIndex'])->name('sss-report')->middleware(CheckAddon::class . ':sss_reports');
Route::get('/generate-pdf', [SssReportController::class, 'generatePdf'])->middleware(CheckAddon::class . ':sss_reports');


// Billing
Route::get('/billing', [BillingController::class, 'billingIndex'])->name('billing');
Route::get('/payment', [PaymentHistoryController::class, 'paymentIndex'])->name('payment');

Route::get('/invoice/{id}/download', [App\Http\Controllers\Tenant\Billing\BillingController::class, 'downloadInvoice'])->name('invoice.download');
Route::get('/invoices/download-all', [App\Http\Controllers\Tenant\Billing\BillingController::class, 'downloadAllInvoices'])->name('invoices.download-all');

// Branch Add-ons
Route::get('/addons', [BranchAddonController::class, 'index'])->name('addons.purchase');
Route::post('/addons/purchase', [BranchAddonController::class, 'purchase'])->name('addon.purchase');
Route::post('/addons/cancel', [BranchAddonController::class, 'cancel'])->name('addon.cancel');
Route::get('/addons/payment/callback/{invoice}', [BranchAddonController::class, 'paymentCallback'])->name('addon.payment.callback');
Route::get('/addons/payment-status', [BranchAddonController::class, 'showPaymentStatus'])->name('addon.payment.status');

// Addon Access Denied Pages
Route::get('/addonrequired', function () {
    session()->forget('addon_redirect'); // Clear session after use
    return response()->view('errors.addonrequired', [], 200);
})->name('addon.required');

Route::get('/featurerequired', function () {
    session()->forget('addon_redirect'); // Clear session after use
    return response()->view('errors.featurerequired', [], 200);
})->name('feature.required');

Route::get('/employees/topup', [EmployeePaymentController::class, 'showPaymentStatus'])->name('employee.paymentstatus');

// Career Page Routes (Public)
Route::prefix('career')->group(function () {
    Route::get('/', [App\Http\Controllers\Tenant\Recruitment\CareerPageController::class, 'index'])->name('career.index');
    Route::get('/jobs/{job}', [App\Http\Controllers\Tenant\Recruitment\CareerPageController::class, 'show'])->name('career.show');
    Route::get('/search', [App\Http\Controllers\Tenant\Recruitment\CareerPageController::class, 'searchJobs'])->name('career.search');
    
    // Authentication routes
    Route::get('/register', [App\Http\Controllers\Career\CandidateAuthController::class, 'showRegisterForm'])->name('career.register');
    Route::post('/register', [App\Http\Controllers\Career\CandidateAuthController::class, 'register'])->name('career.register.submit');
    Route::get('/login', [App\Http\Controllers\Career\CandidateAuthController::class, 'showLoginForm'])->name('career.login');
    Route::post('/login', [App\Http\Controllers\Career\CandidateAuthController::class, 'login'])->name('career.login.submit');
    Route::post('/logout', [App\Http\Controllers\Career\CandidateAuthController::class, 'logout'])->name('career.logout');
    
    // Protected routes (require candidate authentication)
    Route::middleware('auth:candidate')->group(function () {
        Route::get('/jobs/{job}/apply', [App\Http\Controllers\Tenant\Recruitment\CareerPageController::class, 'showApplicationForm'])->name('career.apply');
        Route::post('/jobs/{job}/apply', [App\Http\Controllers\Tenant\Recruitment\CareerPageController::class, 'apply'])->name('career.apply.submit');
        Route::post('/application-status', [App\Http\Controllers\Tenant\Recruitment\CareerPageController::class, 'applicationStatus'])->name('career.application-status');
        Route::post('/withdraw-application', [App\Http\Controllers\Tenant\Recruitment\CareerPageController::class, 'withdrawApplication'])->name('career.withdraw-application');
    });
});
