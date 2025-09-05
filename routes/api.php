<?php

use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\EmploymentDetail;
use Illuminate\Types\Relations\Part;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataAccessController;
use App\Http\Controllers\AffiliatePredefinedRoles;
use App\Http\Controllers\Tenant\HolidayController;
use App\Http\Controllers\Tenant\Bank\BankController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\Tenant\DesignationController;
use App\Http\Controllers\Tenant\Branch\BranchController;
use App\Http\Controllers\Tenant\Policy\PolicyController;
use App\Http\Controllers\Tenant\UserManagementController;
use App\Http\Controllers\Tenant\Billing\BillingController;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Http\Controllers\Tenant\Payroll\PayslipController;
use App\Http\Controllers\Tenant\Profile\ProfileController;
use App\Http\Controllers\Tenant\Employees\SalaryController;
use App\Http\Controllers\Tenant\Leave\LeaveAdminController;
use App\Http\Controllers\Tenant\Payroll\EarningsController;
use App\Http\Controllers\Tenant\Report\SssReportController;
use App\Http\Controllers\Tenant\Overtime\OvertimeController;
use App\Http\Controllers\Tenant\Payroll\AllowanceController;
use App\Http\Controllers\Tenant\Settings\ApprovalController;
use App\Http\Controllers\Tenant\Settings\GeofenceController;
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
use App\Http\Controllers\Tenant\Payroll\PayrollDispatcherController;
use App\Http\Controllers\Tenant\Attendance\AttendanceAdminController;
use App\Http\Controllers\Tenant\Attendance\ShiftManagementController;
use App\Http\Controllers\Tenant\Settings\LeaveTypeSettingsController;
use App\Http\Controllers\Tenant\Settings\AttendanceSettingsController;
use App\Http\Controllers\Tenant\Attendance\AttendanceEmployeeController;
use App\Http\Controllers\Tenant\Attendance\AttendanceRequestAdminController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\MicroBusinessController;
use App\Http\Controllers\AffiliateAccountController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.login');

// Micro Business | Affiliate invited users register branch
Route::post('/affiliate/branch/register', [MicroBusinessController::class, 'registerBranchWithVat'])
    ->name('affiliate-register-post');

Route::get('/affiliate/branch/subscriptions', action: [MicroBusinessController::class, 'branchSubscriptions'])
    ->name('api.affiliate-branch-subscriptions');

// Affiliate Account tenant
Route::post('/affiliate/account/upload', [AffiliateAccountController::class, 'upload'])->name('api.affiliate-account-upload-post');

Route::post('/affiliate/account/register', [AffiliateAccountController::class, 'registerAffiliateAccount'])
    ->name('api.affiliate-account-register-post');

// For Predefined Affiliate Roles
Route::post('/roles/predefined/{tenant_id}', [AffiliatePredefinedRoles::class, 'store'])->name('roles.predefined');

// Add-on Features API
Route::get('/affiliate/branch/addons', [MicroBusinessController::class, 'addOnFeatures'])->name('api.affiliate-addons');

Route::middleware('auth:sanctum')->group(function () {

    // ================== Authentication ================ //
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // ================= Users API ========================= //
    Route::get('users', [UserManagementController::class, 'userIndex'])->name('api.userIndex');

    // ================ Roles and Permission API =============== //
    Route::post('roles-permission', [UserManagementController::class, 'roleStore'])->name('api.roleStore');

    // ============ Department and Designation API ================= //
    Route::post('departments', [DepartmentController::class, 'departmentStore'])->name('api.departmentStore');
    Route::put('/departments/update/{id}', [DepartmentController::class, 'departmentUpdate'])->name('api.departmentUpdate');
    Route::delete('/departments/delete/{id}', [DepartmentController::class, 'departmentDelete'])->name('api.departmentDelete');
    Route::post('/designations/create', [DesignationController::class, 'designationStore'])->name('api.designationStore');
    Route::put('/designations/update/{id}', [DesignationController::class, 'designationUpdate'])->name('api.designationUpdate');
    Route::delete('/designations/delete/{id}', [DesignationController::class, 'designationDelete'])->name('api.designationDelete');

    // ===========  Employee API ================ //
    Route::get('/employees', [EmployeeListController::class, 'employeeListIndex'])->name('api.employees');
    Route::post('employees', [EmployeeListController::class, 'employeeStore'])->name('api.employeeStore');
    Route::get('/get-designations/{department}', [EmployeeListController::class, 'getByDepartment']);
    Route::get('/get-branch-data/{branchId}', [EmployeeListController::class, 'getDepartmentsAndEmployeesByBranch']); // and employee
    Route::put('/employees/update/{id}', [EmployeeListController::class, 'employeeUpdate'])->name('api.employeeUpdate');
    Route::delete('/employees/delete/{id}', [EmployeeListController::class, 'employeeDelete'])->name('api.employeeDelete');
    Route::put('/employees/deactivate/{id}', [EmployeeListController::class, 'employeeDeactivate'])->name('api.employeeDeactivate');
    Route::put('/employees/activate/{id}', [EmployeeListController::class, 'employeeActivate'])->name('api.employeeActivate');
    Route::put('/employees/employee-details/{id}/government-id', [EmployeeDetailsController::class, 'employeeGovernmentId'])->name('api.employeeGovernmentId');
    Route::put('/employees/employee-details/{id}/bank-details', [EmployeeDetailsController::class, 'employeeBankDetail'])->name('api.employeeBankDetail');
    Route::put('/employees/employee-details/{id}/family-informations', [EmployeeDetailsController::class, 'employeeFamilyInformation'])->name('api.employeeFamilyInformation');
    Route::put('/employees/employee-details/{user}/family-informations/update/{family}', [EmployeeDetailsController::class, 'employeeFamilyInformationUpdate'])->name('api.employeeFamilyInformationUpdate');
    Route::delete('/employees/employee-details/{user}/family-informations/delete/{family}', [EmployeeDetailsController::class, 'employeeFamilyInformationDelete'])->name('api.employeeFamilyInformationDelete');
    Route::post('/employees/employee-details/{id}/education-details', [EmployeeDetailsController::class, 'employeeEducation'])->name('api.employeeEducation');
    Route::put('/employees/employee-details/{user}/education-details/update/{education}', [EmployeeDetailsController::class, 'employeeEducationUpdate'])->name('api.employeeEducationUpdate');
    Route::delete('/employees/employee-details/{user}/education-details/delete/{education}', [EmployeeDetailsController::class, 'employeeEducationDelete'])->name('api.employeeEducationDelete');
    Route::post('/employees/employee-details/{id}/experience-details', [EmployeeDetailsController::class, 'employeeExperience'])->name('api.employeeExperience');
    Route::put('/employees/employee-details/{user}/experience-details/update/{experience}', [EmployeeDetailsController::class, 'employeeExperienceUpdate'])->name('api.employeeExperienceUpdate');
    Route::delete('/employees/employee-details/{user}/experience-details/delete/{experience}', [EmployeeDetailsController::class, 'employeeExperienceDelete'])->name('api.employeeExperienceDelete');
    Route::put('/employees/employee-details/{id}/emergency-contacts', [EmployeeDetailsController::class, 'employeeEmergencyContact'])->name('api.employeeEmergencyContact');
    Route::put('/employees/employee-details/{id}/personal-informations', [EmployeeDetailsController::class, 'employeePersonalInformation'])->name('api.employeePersonalInformation');
    Route::put('/employees/employee-details/{id}/basic-informations', [EmployeeDetailsController::class, 'employeeBasicInformation'])->name('api.employeeBasicInformation');
    Route::put('/employees/employee-details/{id}/detail-informations', [EmployeeDetailsController::class, 'employeeDetailsPersonalUpdate'])->name('api.employeeDetailsPersonalUpdate');
    Route::put('/employees/employee-details/{id}/salary-contributions', [EmployeeDetailsController::class, 'employeeSalaryContribution'])->name('api.employeeSalaryContribution');
    Route::post('/employees/employee-details/{id}/attachments', [EmployeeDetailsController::class, 'employeeAttachmentsStore'])->name('api.employeeAttachmentsStore');

    // ============ Salary Record ================== //
    Route::get('/employees/employee-details/{id}/salary-records', [SalaryController::class, 'salaryRecordIndex'])->name('api.salaryRecordIndex');
    Route::post('/employees/employee-details/{id}/salary-records/create', [SalaryController::class, 'salaryRecord'])->name('api.salaryRecord');
    Route::put('/employees/employee-details/{userId}/salary-records/update/{salaryId}', [SalaryController::class, 'salaryRecordUpdate'])->name('api.salaryRecordUpdate');
    Route::delete('/employees/employee-details/{userId}/salary-records/delete/{salaryId}', [SalaryController::class, 'salaryRecordDelete'])->name('api.salaryRecordDelete');

    // ============ Shift Management ================== //
    Route::get('/shift-management/shift-list', [ShiftManagementController::class, 'shiftList'])->name('api.shiftList');
    Route::post('/shift-management/shift-list/create', [ShiftManagementController::class, 'shiftListCreate'])->name('api.shiftListCreate');
    Route::put('/shift-management/shift-list/update/{id}', [ShiftManagementController::class, 'shiftListUpdate'])->name('api.shiftListUpdate');
    Route::delete('/shift-management/shift-list/delete/{id}', [ShiftManagementController::class, 'shiftListDelete'])->name('api.shiftListDelete');
    Route::get('/shift-management', [ShiftManagementController::class, 'shiftIndex'])->name('api.shiftIndex');
    Route::post('/shift-management/shift-assignment', [ShiftManagementController::class, 'shiftAssignmentCreate'])->name('api.shiftAssignmentCreate');
    // Shift Assignment Branch Data Gathering
    Route::get('/shift-management/get-designations', [ShiftManagementController::class, 'getDesignationsByDepartments'])->name('api.getDesignationsByDepartments');
    Route::get('/shift-management/get-branch-data', [ShiftManagementController::class, 'getDepartmentsAndEmployeesByBranches'])->name('api.getDepartmentsAndEmployeesByBranches');

    // ============ Attendance Settings ================== //
    Route::get('/settings/attendance-settings', [AttendanceSettingsController::class, 'attendanceSettingsIndex'])->name('api.attendance-settings');
    Route::post('/settings/attendance-settings/update', [AttendanceSettingsController::class, 'attendanceSettingsCreate'])->name('api.attendanceSettingsCreate');

    // ============ Geofence Settings ================== //
    Route::get('/settings/geofence', [GeofenceController::class, 'geofenceIndex'])->name('api.geofence-settings');
    Route::post('/settings/geofence/create', [GeofenceController::class, 'geofenceStore'])->name('api.geofenceStore');
    Route::put('/settings/geofence/update/{id}', [GeofenceController::class, 'geofenceUpdate'])->name('api.geofenceUpdate');
    Route::delete('/settings/geofence/delete/{id}', [GeofenceController::class, 'geofenceDelete'])->name('api.geofenceDelete');
    // Geofence User Assignment
    Route::post('/settings/geofence/assignment', [GeofenceController::class, 'geofenceUserAssign'])->name('api.geofenceUserAssign');
    Route::put('/settings/geofence/assignment/update/{id}', [GeofenceController::class, 'geofenceUserAssignEdit'])->name('api.geofenceUserAssignEdit');
    Route::delete('/settings/geofence/assignment/delete/{id}', [GeofenceController::class, 'geofenceUserDelete'])->name('api.geofenceUserDelete');

    // ============ Attendance API ================== //
    Route::get('/attendance-employee', [AttendanceEmployeeController::class, 'employeeAttendanceIndex'])->name('api.attendance-employee');
    Route::post('/attendance/clock-in', [AttendanceEmployeeController::class, 'employeeAttendanceClockIn'])->name('api.attendance-clock-in');
    Route::post('/attendance/clock-out', [AttendanceEmployeeController::class, 'employeeAttendanceClockOut'])->name('api.attendance-clock-out');
    Route::get('/attendance-admin', [AttendanceAdminController::class, 'adminAttendanceIndex'])->name('api.attendance-admin');
    Route::put('/attendance-admin/update/{id}', [AttendanceAdminController::class, 'adminAttendanceEdit'])->name('api.adminAttendanceEdit');
    Route::delete('/attendance-admin/delete/{id}', [AttendanceAdminController::class, 'adminAttendanceDelete'])->name('api.adminAttendanceDelete');
    Route::get('/attendance-admin/bulk-attendance', [AttendanceAdminController::class, 'bulkAdminAttendanceIndex'])->name('api.bulkAdminAttendanceIndex');
    Route::put('/attendance-admin/bulk-attendance/update/{id}', [AttendanceAdminController::class, 'bulkAttendanceEdit'])->name('api.bulkAttendanceEdit');
    Route::delete('/attendance-admin/bulk-attendance/delete/{id}', [AttendanceAdminController::class, 'bulkAttendanceDelete'])->name('api.bulkAttendanceDelete');
    Route::get('/attendance-employee/request-attendance', [AttendanceEmployeeController::class, 'requestAttendanceIndex'])->name('api.attendance-request');
    Route::post('/attendance-employee/request', [AttendanceEmployeeController::class, 'requestAttendance'])->name('api.requestAttendance');
    Route::post('/attendance-employee/request/edit/{id}', [AttendanceEmployeeController::class, 'requestAttendanceEdit'])->name('api.requestAttendanceEdit');
    Route::delete('/attendance-employee/request/delete/{id}', [AttendanceEmployeeController::class, 'requestAttendanceDelete'])->name('api.requestAttendanceDelete');
    Route::get('/attendance-admin/request-attendance', [AttendanceRequestAdminController::class, 'adminRequestAttendanceIndex'])->name('api.adminRequestAttendance');
    Route::post('/attendance-admin/request-attendance/{req}/approve', [AttendanceRequestAdminController::class, 'requestAttendanceApproval'])->name('api.requestAttendanceApproval');
    Route::post('/attendance-admin/request-attendance/{req}/reject', [AttendanceRequestAdminController::class, 'requestAttendanceReject'])->name('api.requestAttendanceReject');
    Route::post('/attendance-admin/create', [AttendanceAdminController::class, 'adminAttendanceCreate'])->name('api.adminAttendanceCreate');

    // ============ Admin Dashboard API ================== //
    Route::get('/admin-dashboard', [TenantDashboardController::class, 'adminDashboard'])->name('api.admin-dashboard');
    Route::get('/admin-dashboard/attendance-overview', [TenantDashboardController::class, 'attendanceSummaryToday'])->name('api.admin-attendance-summary');

    // ============= Leave Type Settings ============= //
    Route::get('/settings/leave-type', [LeaveTypeSettingsController::class, 'leaveTypeSettingsIndex'])->name('api.leave-type');
    Route::post('/settings/leave-type/create', [LeaveTypeSettingsController::class, 'leaveTypeSettingsStore'])->name('api.leaveTypeSettingsStore');
    Route::put('/settings/leave-type/update/{id}', [LeaveTypeSettingsController::class, 'leaveTypeSettingsUpdate'])->name('api.leaveTypeSettingsUpdate');
    Route::delete('/settings/leave-type/delete/{id}', [LeaveTypeSettingsController::class, 'leaveTypeSettingsDelete'])->name('api.leaveTypeSettingsDelete');

    // ============= Leave Module ================== //
    Route::get('/leave/leave-settings', [LeaveSettingsController::class, 'LeaveSettingsIndex'])->name('api.leave-settings');
    Route::patch('leave/leave-settings/status/{leaveType}', [LeaveSettingsController::class, 'statusToggle'])->name('api.statusToggle');
    Route::get('/leave/leave-settings/{leaveTypeId}', [LeaveSettingsController::class, 'leaveSettingShow'])->name('api.leaveSettingShow');
    Route::post('/leave/leave-settings/create', [LeaveSettingsController::class, 'leaveSettingsCreate'])->name('api.leaveSettingsCreate');
    Route::get('/leave/leave-employee', [LeaveEmployeeController::class, 'leaveEmployeeIndex'])->name('api.leaveEmployeeIndex');
    Route::get('/leave/leave-admin', [LeaveAdminController::class, 'leaveAdminIndex'])->name('api.leaveAdminIndex');
    Route::post('/leave/leave-admin/update/{id}', [LeaveAdminController::class, 'editLeaveRequest'])->name('api.editLeaveRequest');
    Route::delete('/leave/leave-admin/delete/{id}', [LeaveAdminController::class, 'deleteLeaveRequest'])->name('api.deleteLeaveRequest');
    Route::post('/leave-entitlements/assign-users', [LeaveSettingsController::class, 'assignUsers'])->name('api.assignUsers');
    Route::post('/leave/leave-request', [LeaveEmployeeController::class, 'leaveEmployeeRequest'])->name('api.leaveEmployeeRequest');
    Route::post('/leave/leave-request/{id}', [LeaveEmployeeController::class, 'leaveEmployeeRequestEdit'])->name('api.leaveEmployeeRequestEdit');
    Route::delete('/leave/leave-request/delete/{id}', [LeaveEmployeeController::class, 'leaveEmployeeRequestDelete'])->name('api.leaveEmployeeRequestDelete');
    Route::get('/leave/leave-settings/{id}/assigned-users', [LeaveSettingsController::class, 'assignedUsersIndex'])->name('api.leave-assigned-users');
    Route::put('/leave/leave-settings/assigned-users/{id}', [LeaveSettingsController::class, 'assignedUsersUpdate'])->name('api.assignedUsersUpdate');
    Route::delete('/leave/leave-settings/assigned-users/delete/{id}', [LeaveSettingsController::class, 'assignedUsersDelete'])->name('api.assignedUsersDelete');
    // Leave Request Approval
    Route::post('/leave/leave-request/{leave}/approve', [LeaveAdminController::class, 'leaveApproval'])->name('api.leaveApproval');
    Route::post('/leave/leave-request/{leave}/reject', [LeaveAdminController::class, 'leaveReject'])->name('api.leaveReject');

    // ====================== Holiday ======================= //
    Route::get('/holidays', [HolidayController::class, 'holidayIndex'])->name('api.holidays');
    Route::post('/holidays/create', [HolidayController::class, 'holidayStore'])->name('api.holidayStore');
    Route::put('/holidays/update/{id}', [HolidayController::class, 'holidayUpdate'])->name('api.holidayUpdate');
    Route::delete('/holidays/delete/{id}', [HolidayController::class, 'holidayDelete'])->name('api.holidayDelete');
    // Holiday Exception
    Route::get('/holidays/holiday-exception', [HolidayController::class, 'holidayExceptionIndex'])->name('api.holidayException');
    Route::post('/holidays/holiday-exception/create', [HolidayController::class, 'holidayExceptionUserStore'])->name('api.holidayExceptionUserStore');
    Route::put('/holidays/holiday-exception/deactivate/{id}', [HolidayController::class, 'holidayExceptionDeactivate'])->name('api.holidayExceptionDeactivate');
    Route::put('/holidays/holiday-exception/activate/{id}', [HolidayController::class, 'holidayExceptionActivate'])->name('api.holidayExceptionActivate');
    Route::delete('/holidays/holiday-exception/delete/{id}', [HolidayController::class, 'holidayExceptionDelete'])->name('api.holidayExceptionDelete');

    // =============== Approval Steps Settings ================ //
    Route::get('/settings/approval-steps', [ApprovalController::class, 'approvalIndex'])->name('api.approval-steps');
    Route::get('/settings/approval-steps/users', [ApprovalController::class, 'getUsers'])->name('api.getUsers');
    Route::get('/settings/approval-steps/steps', [ApprovalController::class, 'getSteps'])->name('api.getSteps');
    Route::post('/settings/approval-steps/create', [ApprovalController::class, 'approvalStepStore'])->name('api.approvalStepStore');
    Route::get('/settings/custom-fields', [CustomfieldController::class, 'customfieldIndex'])->name('api.custom-fields');
    Route::post('/settings/custom-fields/create-prefix', [CustomfieldController::class, 'customfieldCreate'])->name('api.customfieldCreate');
    Route::put('/settings/custom-fields/update-prefix/{id}', [CustomfieldController::class, 'customfieldUpdate'])->name('api.customfieldUpdate');
    Route::delete('/settings/custom-fields/delete-prefix/{id}', [CustomfieldController::class, 'customfieldDelete'])->name('api.customfieldDelete');

    // ================= Overtime API ================== //
    Route::get('/overtime', [OvertimeController::class, 'overtimeIndex'])->name('api.overtimeIndex');
    Route::post('/overtime/update/{id}', [OvertimeController::class, 'overtimeAdminUpdate'])->name('api.overtimeAdminUpdate');
    Route::delete('/overtime/delete/{id}', [OvertimeController::class, 'overtimeAdminDelete'])->name('api.overtimeAdminDelete');
    Route::get('/overtime-employee', [EmployeeOvertimeController::class, 'overtimeEmployeeIndex'])->name('api.overtimeEmployeeIndex');
    Route::post('/overtime-employee/create/manual', [EmployeeOvertimeController::class, 'overtimeEmployeeManualCreate'])->name('api.overtimeEmployeeManualCreate');
    Route::post('/overtime-employee/update/{id}', [EmployeeOvertimeController::class, 'overtimeEmployeeManualUpdate'])->name('api.overtimeEmployeeManualUpdate');
    Route::delete('/overtime-employee/delete/{id}', [EmployeeOvertimeController::class, 'overtimeEmployeeManualDelete'])->name('api.overtimeEmployeeManualDelete');
    Route::post('/overtime-employee/clock-in', [EmployeeOvertimeController::class, 'overtimeEmployeeClockIn'])->name('api.overtimeEmployeeClockIn');
    Route::post('/overtime-employee/clock-out', [EmployeeOvertimeController::class, 'overtimeEmployeeClockOut'])->name('api.overtimeEmployeeClockOut');
    //OT Approval
    Route::post('/overtime/{overtime}/approve', [OvertimeController::class, 'overtimeApproval'])->name('api.overtimeApproval');
    Route::post('/overtime/{overtime}/reject', [OvertimeController::class, 'overtimeReject'])->name('api.overtimeReject');

    // ============= Branch API ================ //
    Route::get('/branches', [BranchController::class, 'branchIndex'])->name('api.branchIndex');
    Route::post('/branches/create', [BranchController::class, 'branchCreate'])->name('api.branchCreate');
    Route::post('/branches/update/{id}', [BranchController::class, 'branchEdit'])->name('api.branchEdit');
    Route::delete('/branches/delete/{id}', [BranchController::class, 'branchDelete'])->name('api.branchDelete');

    // ============= Policy API ================ //
    Route::get('/policy', [PolicyController::class, 'policyIndex'])->name('api.policyIndex');
    Route::post('/policy/create', [PolicyController::class, 'policyCreate'])->name('api.policyCreate');
    Route::delete('/policy/delete/{id}', [PolicyController::class, 'policyDelete'])->name('api.policyDelete');
    Route::post('/policy/remove-target', [PolicyController::class, 'removeTarget'])->name('api.policyRemoveTarget');
    Route::put('/policy/update/{id}', [PolicyController::class, 'policyUpdate'])->name('api.policyUpdate');

    // ============= Resignation API ================ //
    Route::get('/resignation', [ResignationController::class, 'resignationIndex'])->name('api.resignationIndex');

    // ============= Termination API ================ //
    Route::get('/termination', [TerminationController::class, 'terminationIndex'])->name('api.terminationIndex');

    // ============= Knowledge Base API ================ //
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'knowledgeBaseIndex'])->name('api.knowledgeBaseIndex');

    // ============ Payroll Items API ================ //
    Route::get('/payroll/payroll-items/sss-contribution', [PayrollItemsController::class, 'payrollItemsSSSContribution'])->name('api.payrollItemsSSSContribution');
    Route::get('/payroll/payroll-items/philhealth-contribution', [PayrollItemsController::class, 'payrollItemsPhilHealthContribution'])->name('api.payrollItemsPhilHealthContribution');
    Route::get('/payroll/payroll-items/withholding-tax', [PayrollItemsController::class, 'payrollItemsWithholdingTax'])->name('api.withholding-taxTable');
    Route::get('/payroll/payroll-items/overtime-table', [PayrollItemsController::class, 'payrollItemsOTtable'])->name('api.ot-table');
    Route::get('/payroll/payroll-items/de-minimis-table', [PayrollItemsController::class, 'payrollItemsDeMinimisTable'])->name('api.de-minimis-benefits');
    Route::get('/payroll/payroll-items/de-minimis-user', [PayrollItemsController::class, 'userDeminimisIndex'])->name('api.de-minimis-user');
    Route::post('/payroll/payroll-items/de-minimis-user/assign', [PayrollItemsController::class, 'userDeminimisAssign'])->name('api.userDeminimisAssign');
    Route::put('/payroll/payroll-items/de-minimis-user/update/{id}', [PayrollItemsController::class, 'userDeminimisUpdate'])->name('api.userDeminimisUpdate');
    Route::delete('/payroll/payroll-items/de-minimis-user/delete/{id}', [PayrollItemsController::class, 'userDeminimisDelete'])->name('api.userDeminimisDelete');
    // Earnings
    Route::get('/payroll/payroll-items/earnings', [EarningsController::class, 'earningIndex'])->name('api.earnings');
    Route::post('/payroll/payroll-items/earnings/store', [EarningsController::class, 'earningStore'])->name('api.earningStore');
    Route::put('/payroll/payroll-items/earnings/update/{id}', [EarningsController::class, 'earningUpdate'])->name('api.earningUpdate');
    Route::delete('/payroll/payroll-items/earnings/delete/{id}', [EarningsController::class, 'earningDelete'])->name('api.earningDelete');
    Route::get('/payroll/payroll-items/earnings/user', [EarningsController::class, 'userEarningIndex'])->name('api.user-earnings');
    Route::post('/payroll/payroll-items/earnings/user/assign', [EarningsController::class, 'userEarningAssign'])->name('api.userEarningAssign');
    Route::put('/payroll/payroll-items/earnings/user/update/{id}', [EarningsController::class, 'userEarningUpdate'])->name('api.userEarningUpdate');
    Route::delete('/payroll/payroll-items/earnings/user/delete/{id}', [EarningsController::class, 'userEarningDelete'])->name('api.userEarningDelete');
    // Deductions
    Route::get('/payroll/payroll-items/deductions', [DeductionsController::class, 'deductionIndex'])->name('api.deductions');
    Route::post('/payroll/payroll-items/deductions/store', [DeductionsController::class, 'deductionStore'])->name('api.deductionStore');
    Route::put('/payroll/payroll-items/deductions/update/{id}', [DeductionsController::class, 'deductionUpdate'])->name('api.deductionUpdate');
    Route::delete('/payroll/payroll-items/deductions/delete/{id}', [DeductionsController::class, 'deductionDelete'])->name('api.deductionDelete');
    Route::get('/payroll/payroll-items/deductions/user', [DeductionsController::class, 'userDeductionIndex'])->name('api.user-deductions');
    Route::post('/payroll/payroll-items/deductions/user/assign', [DeductionsController::class, 'userDeductionAssign'])->name('api.userDeductionAssign');
    Route::put('/payroll/payroll-items/deductions/user/update/{id}', [DeductionsController::class, 'userDeductionUpdate'])->name('api.userDeductionUpdate');
    Route::delete('/payroll/payroll-items/deductions/user/delete/{id}', [DeductionsController::class, 'userDeductionDelete'])->name('api.userDeductionDelete');

    // ======================== Allowances ======================== //
    Route::get('/payroll/payroll-items/allowance', [AllowanceController::class, 'payrollItemsAllowance'])->name('api.allowance');
    Route::post('/payroll/payroll-items/allowance/create', [AllowanceController::class, 'allowanceStore'])->name('api.allowanceStore');
    Route::put('/payroll/payroll-items/allowance/update/{id}', [AllowanceController::class, 'allowanceUpdate'])->name('api.allowanceUpdate');
    Route::delete('/payroll/payroll-items/allowance/delete/{id}', [AllowanceController::class, 'allowanceDelete'])->name('api.allowanceDelete');

    // ======================= User Allowances ======================== //
    Route::get('/payroll/payroll-items/allowance/user', [AllowanceController::class, 'userAllowanceIndex'])->name('api.userAllowanceIndex');


    // ============ Branch API ================== //
    Route::get('/bank', [BankController::class, 'bankIndex'])->name('api.bankIndex');
    Route::post('/bank/create', [BankController::class, 'bankCreate'])->name('api.bankCreate');
    Route::put('/bank/update/{id}', [BankController::class, 'bankUpdate'])->name('api.bankUpdate');
    Route::delete('bank/delete/{id}', [BankController::class, 'bankDelete'])->name('api.bankDelete');

    // ============ Payroll Process ================== //
    Route::get('/payroll', [PayrollController::class, 'payrollProcessIndex'])->name('api.payroll-process');
    Route::post('/payroll/process', [PayrollDispatcherController::class, 'handlePayroll'])->name('api.payrollProcessStore');
    Route::delete('/payroll/delete/{id}', [PayrollController::class, 'deletePayroll'])->name('api.delete-payroll');
    Route::post('/payroll/update/{id}', [PayrollController::class, 'updatePayroll'])->name('api.update-payroll');
    Route::post('/payroll/bulk-delete', [PayrollController::class, 'bulkDeletePayroll'])->name('api.bulkDeletePayroll');
    Route::post('/payroll/bulk-generate-payslip', [PayrollController::class, 'bulkGeneratePayslips'])->name('api.bulkGeneratePayslips');
    Route::post('/payroll/bulk-generate-bank-reports', [PayrollController::class, 'bulkGenerateBankReports'])->name('api.bulkGenerateBankReports');

    // ============ Payslip API ================== //
    Route::get('/payroll/generated-payslips', [PayslipController::class, 'generatedPayslipIndex'])->name('api.generatedPayslipIndex');
    Route::post('payroll/generated-payslips/revert/{id}', [PayslipController::class, 'revertGeneratedPayslip'])->name('api.revertGeneratedPayslip');
    Route::delete('/payroll/generated-payslips/delete/{id}', [PayslipController::class, 'deleteGeneratedPayslip'])->name('api.deleteGeneratedPayslip');
    Route::get('/payroll/generated-payslips/payroll-chart', [PayslipController::class, 'dashboardChartData'])->name('api.dashboardChartData');
    Route::get('/payroll/generated-payslips/payroll-summary', [PayslipController::class, 'payrollSummary'])->name('api.payrollSummary');
    Route::post('/payroll/generated-payslips/bulk-delete', [PayslipController::class, 'bulkDeletePayslip'])->name('api.bulkDeletePayslip');
    Route::post('/payroll/generated-payslips/bulk-revert', [PayslipController::class, 'bulkRevertPayslip'])->name('api.bulkRevertPayslip');

    // User Payslip
    Route::get('/payslip', [PayslipController::class, 'userPayslipIndex'])->name('api.user-payslip');
    Route::get('/payslip/payroll-chart', [PayslipController::class, 'userDashboardChartData'])->name('api.userDashboardChartData');
    Route::get('/payslip/payroll-summary', [PayslipController::class, 'userPayrollSummary'])->name('api.userPayrollSummary');
    Route::get('/payslip/view/{id}', [PayslipController::class, 'userGeneratedPayslip'])->name('api.user-generated-payslips');

    Route::prefix('holiday-exception')->group(function () {
        Route::get('/departments', [HolidayController::class, 'getDepartments']);
        Route::get('/designations', [HolidayController::class, 'getDesignations']);
        Route::get('/employees', [HolidayController::class, 'getEmployees']);
    });

    Route::get('/branches/{id}/departments', [HolidayController::class, 'getDepartmentsByBranch']);
    Route::get('/departments/{id}/branch', [HolidayController::class, 'getBranchByDepartment']);

    // Filtering Routes
    Route::get('/filter-from-branch', [DataAccessController::class, 'fromBranch']);
    Route::get('/filter-from-department', [DataAccessController::class, 'fromDepartment']);
    Route::get('/filter-from-designation', [DataAccessController::class, 'fromDesignation']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'profileIndex'])->name('api.profileIndex');
    Route::post('/profile/update/profile-picture', [ProfileController::class, 'updateProfilePicture'])->name('api.updateProfilePicture');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('api.changePassword');
    Route::post('/profile/update/basic-information', [ProfileController::class, 'updateUserBasicInfo'])->name('api.updateUserBasicInfo');
    Route::post('/profile/update/personal-information', [ProfileController::class, 'updateUserPersonalInfo'])->name('api.updateUserPersonalInfo');
    Route::post('/profile/update/emergency-contact', [ProfileController::class, 'updateUserEmergencyContact'])->name('api.updateUserEmergencyContact');
    Route::post('/profile/add/family-informations', [ProfileController::class, 'addFamilyInformation'])->name('api.addFamilyInformation');
    Route::put('/profile/update/family-informations/{id}', [ProfileController::class, 'updateFamilyInformation'])->name('api.updateFamilyInformation');

    // Official Business
    Route::get('/official-business/employee', [OfficialBusinessController::class, 'employeeOBIndex'])->name('api.ob-employee');
    Route::post('/official-business/employee/request', [OfficialBusinessController::class, 'employeeRequestOB'])->name('api.employeeRequestOB');
    Route::post('/official-business/employee/update/{id}', [OfficialBusinessController::class, 'employeeUpdateOB'])->name('api.employeeUpdateOB');
    Route::delete('/official-business/employee/delete/{id}', [OfficialBusinessController::class, 'employeeDeleteOB'])->name('api.employeeDeleteOB');
    //Admin Official Business
    Route::get('/official-business/admin', [AdminOfficialBusinessController::class, 'adminOBIndex'])->name('api.ob-admin');
    Route::post('/official-business/admin/{ob}/approve', [AdminOfficialBusinessController::class, 'obApproval'])->name('api.obApproval');
    Route::post('/official-business/admin/{ob}/reject', [AdminOfficialBusinessController::class, 'obReject'])->name('api.obReject');
    Route::post('/official-business/admin/update/{id}', [AdminOfficialBusinessController::class, 'adminUpdateOB'])->name('api.adminUpdateOB');
    Route::delete('/official-business/admin/delete/{id}', [AdminOfficialBusinessController::class, 'adminDeleteOB'])->name('api.adminDeleteOB');

    // ==================== Reports ==================== //
    Route::get('/reports/payroll', [PayrollReportController::class, 'payrollReportIndex'])->name('api.payroll-report');
    Route::get('/reports/alphalist', [AlphalistReportController::class, 'alphalistReportIndex'])->name('api.alphalist-report');
    Route::get('/reports/sss', [SssReportController::class, 'sssReportIndex'])->name('api.sss-report');

    // =================== Billing ================ //
    Route::get('/billing', [BillingController::class, 'billingIndex'])->name('api.billing');


});
 