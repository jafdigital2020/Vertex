<?php

use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Tenant\HolidayController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\Tenant\DesignationController;
use App\Http\Controllers\Tenant\Branch\BranchController;
use App\Http\Controllers\Tenant\Policy\PolicyController;
use App\Http\Controllers\Tenant\UserManagementController;
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
    Route::post('designations', [DesignationController::class, 'designationStore'])->name('api.designationStore');
    Route::post('/designations/update/{id}', [DesignationController::class, 'designationUpdate'])->name('api.designationUpdate');
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
    Route::post('/leave-entitlements/assign-users', [LeaveSettingsController::class, 'assignUsers'])->name('api.assignUsers');

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

    // ============= Branch API ================ //
    Route::get('/branches', [BranchController::class, 'branchIndex'])->name('api.branchIndex');

    // ============= Policy API ================ //
    Route::get('/policy', [PolicyController::class, 'policyIndex'])->name('api.policyIndex');

    // ============= Resignation API ================ //
    Route::get('/resignation', [ResignationController::class, 'resignationIndex'])->name('api.resignationIndex');

    // ============= Termination API ================ //
    Route::get('/termination', [TerminationController::class, 'terminationIndex'])->name('api.terminationIndex');

    // ============= Knowledge Base API ================ //
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'knowledgeBaseIndex'])->name('api.knowledgeBaseIndex');
});
