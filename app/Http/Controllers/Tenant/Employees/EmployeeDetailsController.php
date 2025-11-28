<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\Bank;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\Policy;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryDetail;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeExperience;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use App\Models\EmploymentGovernmentId;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeeEducationDetails;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeDetailsAttachment;
use App\Models\EmployeeFamilyInformation;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\EmploymentPersonalInformation;

class EmployeeDetailsController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function employeeDetails($id)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(9);
        $users = User::with('employmentDetail', 'personalInformation', 'governmentId', 'employeeBank', 'family', 'education', 'experience', 'emergency', 'branch')->findOrFail($id);
        $banks = Bank::where('tenant_id', $authUser->tenant_id)->get();
        $branches = Branch::where('tenant_id', $authUser->tenant_id)->get();
        $departments = Department::whereHas('branch', function ($query) use ($authUser) {
            $query->where('tenant_id', $authUser->tenant_id);
        })->get();
        $departmentIds = $departments->pluck('id');
        $designations = Designation::whereIn('department_id', $departmentIds)->get();
        $roles = Role::where('tenant_id', $authUser->tenant_id)->get();

        $employees = User::with([
            'personalInformation',
            'employmentDetail.branch',
            'role',
            'designation',
        ]);

        // Fetch policies relevant to this employee
        $policies = Policy::where('tenant_id', $authUser->tenant_id)
            ->whereHas('targets', function ($query) use ($users, $authUser) {
                $query->where(function ($q) use ($users, $authUser) {
                    // Company-wide policies
                    $q->where('target_type', 'company-wide')
                      ->where('target_id', $authUser->tenant_id);
                })
                ->orWhere(function ($q) use ($users) {
                    // Branch-specific policies
                    if ($users->employmentDetail->branch_id) {
                        $q->where('target_type', 'branch')
                          ->where('target_id', $users->employmentDetail->branch_id);
                    }
                })
                ->orWhere(function ($q) use ($users) {
                    // Department-specific policies
                    if ($users->employmentDetail->department_id) {
                        $q->where('target_type', 'department')
                          ->where('target_id', $users->employmentDetail->department_id);
                    }
                })
                ->orWhere(function ($q) use ($users) {
                    // User-specific policies
                    $q->where('target_type', 'user')
                      ->where('target_id', $users->id);
                });
            })
            ->with('targets')
            ->orderBy('effective_date', 'desc')
            ->get();

        return view('tenant.employee.employeedetails', compact('users', 'banks', 'departments', 'designations', 'roles', 'branches', 'employees', 'permission', 'policies'));
    }

    // Government ID's
    public function employeeGovernmentId(Request $request, $id)
    {
        $user = User::with('governmentId')->findOrFail($id);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $governmentId = EmploymentGovernmentId::updateOrCreate(
            ['user_id' => $user->id],
            [
                'sss_number' => $request->input('sss_number'),
                'philhealth_number' => $request->input('philhealth_number'),
                'pagibig_number' => $request->input('pagibig_number'),
                'tin_number' => $request->input('tin_number')
            ]
        );

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Government ID)',
            'action' => $governmentId->wasRecentlyCreated ? 'Create' : 'Update',
            'description' => ($governmentId->wasRecentlyCreated ? 'Created' : 'Updated') .
                ' government IDs: SSS "' . $governmentId->sss_number .
                '", Philhealth "' . $governmentId->philhealth_number .
                '", HDMF "' . $governmentId->pagibig_number .
                '", TIN "' . $governmentId->tin_number . '"',
            'affected_id' => $governmentId->id,
            'old_data' => json_encode($governmentId->getOriginal()),
            'new_data' => json_encode($governmentId->getChanges()),
        ]);

        return response()->json([
            'message' => $governmentId->wasRecentlyCreated ? 'Government ID created successfully' : 'Government ID updated successfully',
            'data' => $governmentId
        ], 200);
    }

    //Employee Bank Details
    public function employeeBankDetail(Request $request, $id)
    {
        $user = User::with('employeeBank')->findOrFail($id);

        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'account_name' => 'required|string|max:255',
            'account_number' => "required|string|max:50|unique:employee_bank_details,account_number,{$user->id},user_id",
        ]);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $bank = EmployeeBankDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'bank_id' => $request->input('bank_id'),
                'account_name' => $request->input('account_name'),
                'account_number' => $request->input('account_number'),
            ]
        );

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Government ID)',
            'action' => $bank->wasRecentlyCreated ? 'Create' : 'Update',
            'description' => ($bank->wasRecentlyCreated ? 'Created' : 'Updated') .
                ' Bank Details : Bank Name "' . $bank->bank->bank_name .
                '", Account Name "' . $bank->account_name .
                '", Account Number "' . $bank->account_number,
            'affected_id' => $bank->id,
            'old_data' => json_encode($bank->getOriginal()),
            'new_data' => json_encode($bank->getChanges()),
        ]);

        return response()->json([
            'message' => $bank->wasRecentlyCreated ? 'Bank details created successfully' : 'Bank details updated successfully',
            'data' => $bank
        ], 200);
    }

    // Employee Family Information
    public function employeeFamilyInformation(Request $request, $id)
    {
        $user = User::with('family')->findOrFail($id);

        $request->validate(
            [
                'user_id' => 'required|exists:users,id',
                'name' => 'required|array',
                'name.*' => 'required|string|max:255',
                'relationship' => 'required|array',
                'relationship.*' => 'required|string|max:255',
                'birthdate' => 'required|array',
                'birthdate.*' => 'required|date',
                'phone_number' => 'required|array',
                'phone_number.*' => 'required|string|max:20',
            ],
            [
                'name.*' => 'The name field is required for all entries.',
                'relationship.*' => 'The relationship field is required for all entries.',
                'birthdate.*' => 'The birthdate field is required for all entries.',
                'phone_number.*' => 'The phone number field is required for all entries.',
            ]
        );


        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $authUser = $request->input('user_id');
        $names = $request->input('name');
        $relationships = $request->input('relationship');
        $birthdates = $request->input('birthdate');
        $phones = $request->input('phone_number');

        foreach ($names as $index => $name) {
            $family = EmployeeFamilyInformation::updateOrCreate(
                [
                    'user_id' => $authUser,
                    'name' => $name,
                    'relationship' => $relationships[$index],
                    'birthdate' => $birthdates[$index],
                ],
                [
                    'phone_number' => $phones[$index] ?? null
                ]
            );

            // Log create for each family member
            UserLog::create([
                'user_id' => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Employee Details (Family)',
                'action' => 'Create',
                'description' => 'Created Family Information: "' . $family->name . '", Relationship: "' . $family->relationship . '", Phone: "' . $family->phone_number . '"',
                'affected_id' => $family->id,
                'old_data' => json_encode($family->getOriginal()),
                'new_data' => json_encode($family->getChanges()),
            ]);
        }

        return response()->json([
            'message' => 'Family information processed successfully',
            'data' => $family
        ], 200);
    }


    public function employeeFamilyInformationUpdate(Request $request, $userId, $familyId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
        ]);

        $family = EmployeeFamilyInformation::where('user_id', $userId)
            ->where('id', $familyId)
            ->first();

        $oldData = $family->toArray();

        $family->update($validated);

        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Family)',
            'action' => 'Update',
            'description' => 'Updated Family Info: Name "' . $family->name . '", Relationship "' . $family->relationship . '"',
            'affected_id' => $family->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($family->getChanges()),
        ]);

        return response()->json([
            'message' => 'Family information updated successfully.',
            'data' => $family,
        ]);
    }

    public function employeeFamilyInformationDelete(Request $request, $userId, $familyId)
    {
        $family = EmployeeFamilyInformation::where('user_id', $userId)
            ->where('id', $familyId)
            ->first();

        if (!$family) {
            return response()->json(['message' => 'Family member not found.'], 404);
        }

        $oldData = $family->toArray();

        $family->delete();

        // Logging Start
        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Family)',
            'action' => 'Delete',
            'description' => 'Deleted Family Info: Name "' . $family->name . '", Relationship "' . $family->relationship . '"',
            'affected_id' => $family->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode([]),
        ]);

        return response()->json([
            'message' => 'Family information deleted successfully.',
        ]);
    }

    //Employee Education Details
    public function employeeEducation(Request $request, $id)
    {
        $user = User::with('education')->findOrFail($id);

        $request->validate([
            'institution_name' => 'required|string|max:255',
            'course_or_level' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $education = EmployeeEducationDetails::create([
            'user_id' => $user->id,
            'institution_name' => $request->input('institution_name'),
            'course_or_level' => $request->input('course_or_level'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Education Details)',
            'action' => $education->wasRecentlyCreated ? 'Create' : 'Update',
            'description' => ($education->wasRecentlyCreated ? 'Created' : 'Updated') .
                ' Education Details : Institution Name "' . $education->institution_name .
                '", Course/Level "' . $education->course_or_level .
                '", Date From and To "' . $education->date_from . '"to"' . $education->date_to,
            'affected_id' => $education->id,
            'old_data' => json_encode($education->getOriginal()),
            'new_data' => json_encode($education->getChanges()),
        ]);

        return response()->json([
            'message' => $education->wasRecentlyCreated ? 'Education details created successfully' : 'Education details updated successfully',
            'data' => $education
        ], 200);
    }

    // Edit Education Details
    public function employeeEducationUpdate(Request $request, $userId, $educationId)
    {
        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'course_or_level' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $education = EmployeeEducationDetails::where('user_id', $userId)
            ->where('id', $educationId)
            ->first();

        $oldData = $education->toArray();

        $education->update($validated);

        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Education)',
            'action' => 'Update',
            'description' => 'Updated Education Details: Name "' . $education->institution_name . '", Course or Level "' . $education->course_or_level . '"',
            'affected_id' => $education->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($education->getChanges()),
        ]);

        return response()->json([
            'message' => 'Education details updated successfully.',
            'data' => $education,
        ]);
    }

    // Delete Education
    public function employeeEducationDelete(Request $request, $userId, $educationId)
    {
        $education = EmployeeEducationDetails::where('user_id', $userId)
            ->where('id', $educationId)
            ->first();

        if (!$education) {
            return response()->json(['message' => 'Education detail not found.'], 404);
        }

        $oldData = $education->toArray();

        $education->delete();

        // Logging Start
        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Education)',
            'action' => 'Delete',
            'description' => 'Deleted education detail: Name "' . $education->institution_name,
            'affected_id' => $education->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode([]),
        ]);

        return response()->json([
            'message' => 'Education details deleted successfully.',
        ]);
    }

    // Employee Experience
    public function employeeExperience(Request $request, $id)
    {
        $user = User::with('experience')->findOrFail($id);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $request->validate([
            'previous_company' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'is_present' => 'required|boolean',
        ], [
            'date_from.required' => 'The start date is required.'
        ]
        );

        $isPresent = $request->input('is_present', 0);

        $experience = EmployeeExperience::create([
            'user_id' => $user->id,
            'previous_company' => $request->input('previous_company'),
            'designation' => $request->input('designation'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'is_present' => $isPresent,
        ]);

        // Log activity
        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Experience Details)',
            'action' => 'Create',
            'description' => 'Created Experience Details: Company "' . $experience->previous_company .
                '", Designation "' . $experience->designation .
                '", Date From "' . $experience->date_from .
                '" to "' . ($experience->is_present ? 'Present' : $experience->date_to) . '"',
            'affected_id' => $experience->id,
            'old_data' => null,
            'new_data' => json_encode($experience),
        ]);

        return response()->json([
            'message' => 'Experience details created successfully',
            'data' => $experience
        ], 200);
    }

    // Edit Experience
    public function employeeExperienceUpdate(Request $request, $userId, $experienceId)
    {
        $validated = $request->validate([
            'previous_company' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'is_present' => 'required|boolean',
        ]);

        // If currently working, we nullify the date_to value
        if ($validated['is_present']) {
            $validated['date_to'] = null;
        }

        $experience = EmployeeExperience::where('user_id', $userId)
            ->where('id', $experienceId)
            ->firstOrFail();

        $oldData = $experience->toArray();

        $experience->update($validated);

        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Experience)',
            'action' => 'Update',
            'description' => 'Updated Experience Details: Company "' . $experience->previous_company . '", Designation "' . $experience->designation . '"',
            'affected_id' => $experience->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($experience->getChanges()),
        ]);

        return response()->json([
            'message' => 'Experience details updated successfully.',
            'data' => $experience,
        ]);
    }

    // Delete Experience
    public function employeeExperienceDelete(Request $request, $userId, $experienceId)
    {
        $experience = EmployeeExperience::where('user_id', $userId)
            ->where('id', $experienceId)
            ->first();

        if (!$experience) {
            return response()->json(['message' => 'Experience detail not found.'], 404);
        }

        $oldData = $experience->toArray();

        $experience->delete();

        // Logging Start
        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Experience)',
            'action' => 'Delete',
            'description' => 'Deleted experience detail: Company Name "' . $experience->previous_company,
            'affected_id' => $experience->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode([]),
        ]);

        return response()->json([
            'message' => 'Experience details deleted successfully.',
        ]);
    }

    //Employee Emergency Contact
    public function employeeEmergencyContact(Request $request, $id)
    {
        $user = User::with('emergency')->findOrFail($id);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }
        $request->validate([
            'primary_name' => 'required|string|max:255',
            'primary_phone_one' => 'required',
            'primary_relationship' => 'required'
        ]);

        $emergency = EmployeeEmergencyContact::updateOrCreate(
            ['user_id' => $user->id],
            [
                'primary_name' => $request->input('primary_name'),
                'primary_relationship' => $request->input('primary_relationship'),
                'primary_phone_one' => $request->input('primary_phone_one'),
                'primary_phone_two' => $request->input('primary_phone_two') ?? null,
                'secondary_name' => $request->input('secondary_name') ?? null,
                'secondary_relationship' => $request->input('secondary_relationship') ?? null,
                'secondary_phone_one' => $request->input('secondary_phone_one') ?? null,
                'secondary_phone_two' => $request->input('secondary_phone_two') ?? null,
            ]
        );

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Emergency Contact)',
            'action' => $emergency->wasRecentlyCreated ? 'Create' : 'Update',
            'description' => ($emergency->wasRecentlyCreated ? 'Created' : 'Updated') .
                ' Contact Emergency s: Primary Name "' . $emergency->primary_name .
                '", Primary Relationship "' . $emergency->primary_relationship .
                '", Primary Phone 1 "' . $emergency->primary_phone_one .
                '", Primary Phone 2 "' . $emergency->primary_phone_two .
                '", Secondary Name  "' . $emergency->secondary_name .
                '", Seconday Relationship"' . $emergency->secondary_relationship .
                '", Secondary Phone 1"' . $emergency->secondary_phone_one .
                '", Secondary Phone 2"' . $emergency->secondary_phone_two . '"',
            'affected_id' => $emergency->id,
            'old_data' => json_encode($emergency->getOriginal()),
            'new_data' => json_encode($emergency->getChanges()),
        ]);

        return response()->json([
            'message' => $emergency->wasRecentlyCreated ? 'Emergency Contact created successfully' : 'Emergency Contact updated successfully',
            'data' => $emergency
        ], 200);
    }

    // Employee Personal Information
    public function employeePersonalInformation(Request $request, $id)
    {
        $user = User::with('personalInformation')->findOrFail($id);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $personalInfo = EmploymentPersonalInformation::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nationality' => $request->input('nationality'),
                'religion' => $request->input('religion'),
                'civil_status' => $request->input('civil_status'),
                'no_of_children' => $request->input('no_of_children'),
                'spouse_name' => $request->input('spouse_name'),
            ]
        );

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Personal Information)',
            'action' => $personalInfo->wasRecentlyCreated ? 'Create' : 'Update',
            'description' => ($personalInfo->wasRecentlyCreated ? 'Created' : 'Updated') .
                ' Personal Information: Nationality "' . $personalInfo->nationality .
                '", Religion "' . $personalInfo->religion .
                '", Civil Status "' . $personalInfo->civil_status .
                '", Number of children  "' . $personalInfo->no_of_children .
                '", Spouse Name "' . $personalInfo->secondary_phone_two . '"',
            'affected_id' => $personalInfo->id,
            'old_data' => json_encode($personalInfo->getOriginal()),
            'new_data' => json_encode($personalInfo->getChanges()),
        ]);

        return response()->json([
            'message' => $personalInfo->wasRecentlyCreated ? 'Personal information created successfully' : 'Personal information updated successfully',
            'data' => $personalInfo
        ], 200);
    }

    // Employee Basic Information (Personal Information)
    public function employeeBasicInformation(Request $request, $id)
    {
        $user = User::with('personalInformation')->findOrFail($id);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $personalInfo = EmploymentPersonalInformation::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $request->input('phone_number'),
                'gender' => $request->input('gender'),
                'birth_date' => $request->input('birth_date'),
                'birth_place' => $request->input('birth_place'),
                'complete_address' => $request->input('complete_address'),
            ]
        );

        UserLog::create([
            'user_id' => $userId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Emergency Contact)',
            'action' => $personalInfo->wasRecentlyCreated ? 'Create' : 'Update',
            'description' => ($personalInfo->wasRecentlyCreated ? 'Created' : 'Updated') .
                ' Personal Information: Phone Number "' . $personalInfo->phone_number .
                '", gender "' . $personalInfo->gender .
                '", Birthdate "' . $personalInfo->birth_date .
                '", Birthplace  "' . $personalInfo->birth_place .
                '", Complete Address "' . $personalInfo->complete_address . '"',
            'affected_id' => $personalInfo->id,
            'old_data' => json_encode($personalInfo->getOriginal()),
            'new_data' => json_encode($personalInfo->getChanges()),
        ]);

        return response()->json([
            'message' => $personalInfo->wasRecentlyCreated ? 'Personal information created successfully' : 'Personal information updated successfully',
            'data' => $personalInfo
        ], 200);
    }

    public function employeeDetailsPersonalUpdate(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            // Personal Info
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'suffix' => 'nullable|string',
            'username' => 'required|string|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|string',
            'password' => 'nullable|string|min:6|same:confirm_password',
            'confirm_password' => 'nullable|string|min:6',
            'designation_id' => 'required|string',
            'department_id' => 'required|string',
            'date_hired' => 'required|date',
            'employee_id' => 'required|string|unique:employment_details,employee_id,' . $id . ',user_id',
            'employment_type' => 'required|string',
            'employment_status' => 'required|string',
            'security_license_number' => 'nullable|string',
            'security_license_expiration' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::with('personalInformation', 'employmentDetail')->findOrFail($id);

            // Old data for logs
            $oldData = [
                'username' => $user->username,
                'email' => $user->email,
            ];
            $role = Role::find($request->role_id);
            $user_permission = UserPermission::where('user_id', $user->id)->first();
            $user_permission->role_id = $role->id;
            $user_permission->menu_ids = $role->menu_ids;
            $user_permission->module_ids = $role->module_ids;
            $user_permission->user_permission_ids = $role->role_permission_ids;
            $user_permission->status = 1;
            $user_permission->save();
            // Update User
            $updateData = [
                'username' => $request->username,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update Personal Info
            $personalInfo = EmploymentPersonalInformation::firstOrNew(['user_id' => $user->id]);
            $personalInfo->fill($request->only(['first_name', 'last_name', 'middle_name', 'suffix']));

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

            // Update Employment Details
            $employmentDetail = EmploymentDetail::firstOrNew(['user_id' => $user->id]);
            $employmentDetail->fill([
                'designation_id' => $request->designation_id,
                'department_id' => $request->department_id,
                'date_hired' => $request->date_hired,
                'employee_id' => $request->employee_id,
                'employment_type' => $request->employment_type,
                'employment_status' => $request->employment_status,
                'branch_id' => $request->branch_id,
                'security_license_number' => $request->security_license_number,
                'security_license_expiration' => $request->security_license_expiration,
                'status' => 1,
            ]);
            $employmentDetail->save();


            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // ✨ Log the action
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
            return response()->json(['message' => 'Employee updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee', ['exception' => $e]);
            return response()->json(['message' => 'Error updating employee.', 'error' => $e->getMessage()], 500);
        }
    }

    // Salary and Contribution Computation
    public function employeeSalaryContribution(Request $request, $id)
    {
        $user = User::with('salaryDetail')->findOrFail($id);

        $request->validate([
            'sss_contribution' => 'required|in:system,manual',
            'philhealth_contribution' => 'required|in:system,manual',
            'pagibig_contribution' => 'required|in:system,manual',
            'withholding_tax' => 'required|in:system,manual',

            'sss_contribution_override' => 'required_if:sss_contribution,manual|nullable|numeric',
            'philhealth_contribution_override' => 'required_if:philhealth_contribution,manual|nullable|numeric',
            'pagibig_contribution_override' => 'required_if:pagibig_contribution,manual|nullable|numeric',
            'withholding_tax_override' => 'required_if:withholding_tax,manual|nullable|numeric',

            'worked_days_per_year' => 'nullable|numeric|min:0',
        ]);

        // Determine override values based on contribution type
        $sssContribution = $request->input('sss_contribution');
        $sssOverride = $sssContribution === 'system' ? null : $request->input('sss_contribution_override');

        $philhealthContribution = $request->input('philhealth_contribution');
        $philhealthOverride = $philhealthContribution === 'system' ? null : $request->input('philhealth_contribution_override');

        $pagibigContribution = $request->input('pagibig_contribution');
        $pagibigOverride = $pagibigContribution === 'system' ? null : $request->input('pagibig_contribution_override');

        $withholdingTax = $request->input('withholding_tax');
        $withholdingOverride = $withholdingTax === 'system' ? null : $request->input('withholding_tax_override');

        $salary = SalaryDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'sss_contribution' => $sssContribution,
                'philhealth_contribution' => $philhealthContribution,
                'pagibig_contribution' => $pagibigContribution,
                'withholding_tax' => $withholdingTax,
                'sss_contribution_override' => $sssOverride,
                'philhealth_contribution_override' => $philhealthOverride,
                'pagibig_contribution_override' => $pagibigOverride,
                'withholding_tax_override' => $withholdingOverride,
                'worked_days_per_year' => $request->input('worked_days_per_year'),
            ]
        );

        // Logging Start
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
            'module' => 'Employee Details (Salary & Contribution)',
            'action' => $salary->wasRecentlyCreated ? 'Create' : 'Update',
            'description' => ($salary->wasRecentlyCreated ? 'Created' : 'Updated') . ' contribution setup.',
            'affected_id' => $salary->id,
            'old_data' => json_encode($salary->getOriginal()),
            'new_data' => json_encode($salary->getChanges()),
        ]);

        return response()->json([
            'message' => $salary->wasRecentlyCreated ? 'Contribution computation created successfully' : 'Contribution computation updated successfully',
            'data' => $salary
        ], 200);
    }

    // Employee Attachments
    public function employeeAttachmentsStore(Request $request, $id)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id ?? null;

        $user = User::with('attachments')->findOrFail($id);

        $request->validate([
            'attachment_name' => 'required|string|max:255',
            'attachment_path' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $attachment = new EmployeeDetailsAttachment();
        $attachment->user_id = $user->id;
        $attachment->attachment_name = $request->input('attachment_name');
        $attachment->attachment_path = $request->file('attachment_path')->store('employee_attachments', 'public');
        $attachment->upload_by_id = $authUserId;
        $attachment->upload_by_type = get_class($authUser);
        $attachment->save();


        // Log activity
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
            'module' => 'Employee Details (Attachments)',
            'action' => 'Create',
            'description' => 'Created attachment: "' . $attachment->attachment_name . '" for user ID: ' . $user->id,
            'affected_id' => $attachment->id,
            'old_data' => null,
            'new_data' => json_encode($attachment),
        ]);

        return response()->json([
            'message' => 'Attachment created successfully',
            'data' => $attachment
        ], 200);
    }

    public function employeeAttachmentDelete($userId, $attachmentId)
    {
        $attachment = EmployeeDetailsAttachment::where('user_id', $userId)
            ->where('id', $attachmentId)
            ->first();

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found.'], 404);
        }

        $oldData = $attachment->toArray();

        // Delete the file from storage
        if (Storage::disk('public')->exists($attachment->attachment_path)) {
            Storage::disk('public')->delete($attachment->attachment_path);
        }

        $attachment->delete();

        // Logging Start
        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Attachments)',
            'action' => 'Delete',
            'description' => 'Deleted attachment: "' . $attachment->attachment_name . '"',
            'affected_id' => $attachment->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode([]),
        ]);

        return response()->json([
            'message' => 'Attachment deleted successfully.',
        ]);
    }
}
