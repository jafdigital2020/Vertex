<?php

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
use App\Http\Controllers\Tenant\Payroll\PayrollItemsController;
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
    Route::get('/employee-dashboard/attendance-analytics', [TenantDashboardController::class, 'getAttendanceAnalytics'])->name('attendance-analytics');
    Route::get('/employee-dashboard/leave-analytics', [TenantDashboardController::class, 'getLeaveAnalytics'])->name('leave-analytics');
    Route::get('/employee-dashboard/attendance-bar-data', [TenantDashboardController::class, 'getAttendanceBarData'])->name('employee-dashboard.attendance-bar-data');
    Route::get('/employee-dashboard/user-shifts', [TenantDashboardController::class, 'getUserShiftsForWidget'])->name('employee-dashboard.user-shifts');

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
    Route::get('/shifts-management-filter', [ShiftManagementController::class, 'shiftManagementFilter'])->name('shiftmanagement.filter');
    Route::get('/shift-list', [ShiftManagementController::class, 'shiftList'])->name('shift-list');
    Route::get('/shift-list-filter', [ShiftManagementController::class, 'shiftListfilter'])->name('shiftList-filter');
    //Settings
    Route::get('/settings/attendance-settings', [AttendanceSettingsController::class, 'attendanceSettingsIndex'])->name('attendance-settings')->middleware(CheckPermission::class . ':18');
    Route::get('/settings/leave-type', [LeaveTypeSettingsController::class, 'leaveTypeSettingsIndex'])->name('leave-type')->middleware(CheckPermission::class . ':43');
    Route::get('/settings/approval-steps', [ApprovalController::class, 'approvalIndex'])->name('approval-steps')->middleware(CheckPermission::class . ':43');
    Route::get('/settings/custom-fields', [CustomfieldController::class, 'customfieldIndex'])->name('custom-fields');

    // Geofence
    Route::get('/settings/geofence', [GeofenceController::class, 'geofenceIndex'])->name('geofence-settings');
    Route::get('/settings/geofence/location', [GeofenceController::class, 'locationFilter'])->name('geofence-location-filter');
    Route::get('/settings/geofence/user', [GeofenceController::class, 'userFilter'])->name('geofence-user-filter');
    // Attendance
    Route::get('/attendance-employee', [AttendanceEmployeeController::class, 'employeeAttendanceIndex'])->name('attendance-employee')->middleware(CheckPermission::class . ':15');
    Route::get('/attendance-employee-filter', [AttendanceEmployeeController::class, 'filter'])->name('attendance-employee-filter');
    Route::get('/attendance-employee/request-attendance', [AttendanceEmployeeController::class, 'requestAttendanceIndex'])->name('attendance-request');

    Route::get('/attendance-admin', [AttendanceAdminController::class, 'adminAttendanceIndex'])->name('attendance-admin')->middleware(CheckPermission::class . ':14');
    Route::get('/attendance-admin-filter', [AttendanceAdminController::class, 'filter'])->name('attendance-admin-filter');
    Route::post('/attendance-admin/upload', [AttendanceAdminController::class, 'importAttendanceCSV'])->name('importAttendanceCSV'); // Import Attendance CSV
    Route::post('/attendance-admin/bulk-upload', [AttendanceAdminController::class, 'bulkImportAttendanceCSV'])->name('bulkImportAttendanceCSV'); // Bulk Import Attendance CSV
    Route::get('/attendance-admin/download-template', [AttendanceAdminController::class, 'downloadAttendanceTemplate'])->name('downloadAttendanceTemplate');
    Route::get('/attendance-admin/download-bulk-template', [AttendanceAdminController::class, 'downloadAttendanceBulkImportTemplate'])->name('downloadAttendanceBulkImportTemplate');
    Route::get('/attendance-admin/bulk-attendance', [AttendanceAdminController::class, 'bulkAdminAttendanceIndex'])->name('bulkAdminAttendanceIndex');
    Route::get('/attendance-admin/bulk-attendance-filter', [AttendanceAdminController::class, 'bulkAdminAttendanceFilter'])->name('bulkAdminAttendanceFilter');
    Route::get('/attendance-admin/request-attendance', [AttendanceRequestAdminController::class, 'adminRequestAttendanceIndex'])->name('adminRequestAttendance');

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
    // Bank
    Route::get('/bank', [BankController::class, 'bankIndex'])->name('bank');

    // Payroll Process
    Route::get('/payroll', [PayrollController::class, 'payrollProcessIndex'])->name('payroll-process');
    Route::get('/payroll/generated-payslips', [PayslipController::class, 'generatedPayslipIndex'])->name('generatedPayslipIndex');
    Route::get('/payroll/generated-payslips-filter', [PayslipController::class, 'filter'])->name('generatedPayslipIndex-filter');
    Route::get('/payroll/generated-payslips/{id}', [PayslipController::class, 'generatedPayslips'])->name('generatedPayslips');
    Route::get('/payslip', [PayslipController::class, 'userPayslipIndex'])->name('payslip');

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
    Route::get('/employee-assets-get', [AssetsController::class, 'employeeAssetGet'])->name('get-employee-assets');
    Route::get('/employee-assets-filter', [AssetsController::class, 'employeeAssetsFilter'])->name('employee-assets-filter');
    Route::get('/employee-assets/list', [AssetsController::class, 'list']);

    Route::get('/get-asset-info/{id}', function ($id) {
        $asset =  Assets::with('category')->find($id);

        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }
        return response()->json([
            'status' => $asset->status,
            'price' => $asset->price,
            'category' => $asset->category->name ?? 'N/A',
        ]);
    });
    Route::post('/employee-assets-create', [AssetsController::class, 'employeeAssetsStore'])->name('employee-assets-create');

    Route::get('/assets-settings', [AssetsController::class, 'assetsSettingsIndex'])->name('assets-settings')->middleware(CheckPermission::class . ':50');
    Route::get('/assets-settings-filter', [AssetsController::class, 'assetsSettingsFilter'])->name('assets-settings-filter');
    Route::post('/assets-settings/create', [AssetsController::class, 'assetsSettingsStore'])->name('assetsSettingsStore');
    Route::post('/assets-settings/update', [AssetsController::class, 'assetsSettingsUpdate'])->name('assetsSettingsUpdate');
    Route::post('/assets-settings/delete', [AssetsController::class, 'assetsSettingsDelete'])->name('assetsSettingsDelete');
    });

    Route::get('/send-test-notif', function () {
        $user = User::find(47);
        $user->notify(new UserNotification('Welcome! This is your test notification.'));
        return 'Notification Sent!';
    });



