<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryDetail;
use App\Models\UserPermission;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\EmploymentGovernmentId;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\EmploymentPersonalInformation;

class ImportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path, $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct($path, $tenantId)
    {
        $this->path = $path;
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Attempting to open file at: " . storage_path('app/private/' . $this->path));
        Log::info("Running ImportEmployeesJob with tenant ID: " . $this->tenantId);

        $path = storage_path('app/private/' . $this->path);
        if (!file_exists($path)) {
            Log::error("CSV import failed: File does not exist at path: $path");
            return;
        }

        $file = fopen($path, 'r');
        if (!$file) {
            Log::error("CSV import failed: Unable to open file at path: $path");
            return;
        }

        $header = fgetcsv($file);
        if (!$header) {
            Log::error("CSV import failed: Unable to read header row from file at path: $path");
            fclose($file);
            return;
        }

        Log::info('CSV import started.', ['file' => $this->path]);

        $columnMap = [
            'First Name'    => 'first_name',
            'Last Name'     => 'last_name',
            'Middle Name'   => 'middle_name',
            'Suffix'        => 'suffix',
            'Username'      => 'username',
            'Email'         => 'email',
            'Password'      => 'password',
            'Role'          => 'role_name',
            'Branch'        => 'branch_name',
            'Department'    => 'department_name',
            'Designation'   => 'designation_name',
            'Date Hired'    => 'date_hired',
            'Employee ID'   => 'employee_id',
            'Employment Type' => 'employment_type',
            'Employment Status' => 'employment_status',
            'Security License Number' => 'security_license_number',
            'Security License Expiration' => 'security_license_expiration',
            'Phone Number'  => 'phone_number',
            'Gender'        => 'gender',
            'Civil Status'  => 'civil_status',
            'SSS'           => 'sss_number',
            'Philhealth'    => 'philhealth_number',
            'Pagibig'       => 'pagibig_number',
            'TIN'           => 'tin_number',
            'Spouse Name'   => 'spouse_name',
        ];

        $requiredFields = [
            'first_name',
            'last_name',
            'username',
            'password',
            'role_name',
            'branch_name',
            'department_name',
            'designation_name',
            'date_hired',
            'employee_id',
            'employment_type',
            'employment_status'
        ];

        $genderMap = [
            'm' => 'Male',
            'male' => 'Male',
            'f' => 'Female',
            'female' => 'Female',
            'other' => 'Other',
            'o' => 'Other'
        ];
        $validGenders = ['Male', 'Female', 'Other'];
        $validCivilStatus = ['single', 'married', 'widowed', 'divorced', 'separated'];

        $errors = [];
        $successCount = 0;
        $skippedCount = 0;
        $rowNumber = 1;

        DB::beginTransaction();
        try {
            while ($row = fgetcsv($file)) {
                $rowNumber++;
                $raw = [];

                Log::debug("Processing row $rowNumber", ['row' => $row]);

                if (count($row) !== count($header)) {
                    $errorMsg = "Row $rowNumber: Column mismatch.";
                    $errors[] = ['row' => $rowNumber, 'error' => $errorMsg];
                    Log::warning($errorMsg, ['row' => $row]);
                    continue;
                }

                foreach ($header as $index => $csvHeader) {
                    $mappedKey = $columnMap[$csvHeader] ?? null;
                    if ($mappedKey) {
                        $raw[$mappedKey] = isset($row[$index]) ? trim($row[$index]) : null;
                    }
                }

                foreach (['first_name', 'last_name', 'middle_name'] as $nameKey) {
                    if (!empty($raw[$nameKey])) {
                        $raw[$nameKey] = ucwords(strtolower($raw[$nameKey]));
                    }
                }

                // Handle N/A or empty for date fields
                foreach (['date_hired', 'security_license_expiration'] as $dateField) {
                    if (isset($raw[$dateField])) {
                        $value = trim($raw[$dateField]);
                        if ($value === '' || strtolower($value) === 'n/a') {
                            $raw[$dateField] = null;
                        }
                    }
                }

                // Handle N/A for security_license_number
                if (isset($raw['security_license_number'])) {
                    $value = trim($raw['security_license_number']);
                    if ($value === '' || strtolower($value) === 'n/a') {
                        $raw['security_license_number'] = null;
                    }
                }

                try {
                    foreach ($requiredFields as $field) {
                        if ($field === 'date_hired') {
                            if (!isset($raw[$field]) || $raw[$field] === null) {
                                Log::warning("Row $rowNumber: '$field' is required but missing or null.");
                                throw new \Exception("Row $rowNumber: '$field' is required.");
                            }
                        } else {
                            if (empty($raw[$field])) {
                                Log::warning("Row $rowNumber: '$field' is required but empty.");
                                throw new \Exception("Row $rowNumber: '$field' is required.");
                            }
                        }
                    }

                    if (!empty($raw['gender'])) {
                        $raw['gender'] = $genderMap[strtolower($raw['gender'])] ?? null;
                        if (!in_array($raw['gender'], $validGenders)) {
                            Log::warning("Row $rowNumber: Invalid gender value '{$raw['gender']}'");
                            throw new \Exception("Row $rowNumber: Invalid gender.");
                        }
                    }

                    if (!empty($raw['civil_status'])) {
                        $civilValue = strtolower($raw['civil_status']);
                        if (!in_array($civilValue, $validCivilStatus)) {
                            Log::warning("Row $rowNumber: Invalid civil status value '{$raw['civil_status']}'");
                            throw new \Exception("Row $rowNumber: Invalid civil status.");
                        }
                        $raw['civil_status'] = ucfirst($civilValue);
                    }

                    // Role lookup
                    Log::debug("Row $rowNumber: Looking up role '{$raw['role_name']}'");
                    $role = Role::where('role_name', $raw['role_name'])->first();
                    if ($role) {
                        Log::info("Row $rowNumber: Found Role '{$raw['role_name']}' (ID: {$role->id})");
                    } else {
                        Log::warning("Row $rowNumber: Role '{$raw['role_name']}' not found.");
                        throw new \Exception("Row $rowNumber: Role '{$raw['role_name']}' not found.");
                    }

                    // Branch lookup
                    Log::debug("Row $rowNumber: Looking up branch '{$raw['branch_name']}'");
                    $branch = Branch::where('name', $raw['branch_name'])->first();
                    if ($branch) {
                        Log::info("Row $rowNumber: Found Branch '{$raw['branch_name']}' (ID: {$branch->id})");
                    } else {
                        Log::warning("Row $rowNumber: Branch '{$raw['branch_name']}' not found.");
                        throw new \Exception("Row $rowNumber: Branch '{$raw['branch_name']}' not found.");
                    }

                    // Department lookup
                    Log::debug("Row $rowNumber: Looking up department '{$raw['department_name']}' for branch '{$raw['branch_name']}'");
                    $department = Department::where('department_name', $raw['department_name'])
                        ->where('branch_id', $branch->id)->first();
                    if ($department) {
                        Log::info("Row $rowNumber: Found Department '{$raw['department_name']}' (ID: {$department->id}) for Branch '{$raw['branch_name']}'");
                    } else {
                        Log::warning("Row $rowNumber: Department '{$raw['department_name']}' not found for branch '{$raw['branch_name']}'.");
                        throw new \Exception("Row $rowNumber: Department '{$raw['department_name']}' not found.");
                    }

                    // Designation lookup
                    Log::debug("Row $rowNumber: Looking up designation '{$raw['designation_name']}' for department '{$raw['department_name']}'");
                    $designation = Designation::where('designation_name', $raw['designation_name'])
                        ->where('department_id', $department->id)->first();
                    if ($designation) {
                        Log::info("Row $rowNumber: Found Designation '{$raw['designation_name']}' (ID: {$designation->id}) for Department '{$raw['department_name']}'");
                    } else {
                        Log::warning("Row $rowNumber: Designation '{$raw['designation_name']}' not found for department '{$raw['department_name']}'.");
                        throw new \Exception("Row $rowNumber: Designation '{$raw['designation_name']}' not found.");
                    }

                    // Only parse date if not null
                    if (!empty($raw['date_hired'])) {
                        $raw['date_hired'] = Carbon::parse($raw['date_hired'])->format('Y-m-d');
                    } else {
                        $raw['date_hired'] = null;
                    }

                    if (!empty($raw['security_license_expiration'])) {
                        $raw['security_license_expiration'] = Carbon::parse($raw['security_license_expiration'])->format('Y-m-d');
                    } else {
                        $raw['security_license_expiration'] = null;
                    }

                    $raw['email'] = empty($raw['email']) ? null : $raw['email'];

                    // SKIP if user exists by username only (email can be nullable/duplicate)
                    $existingUser = User::where('username', $raw['username'])->first();
                    if ($existingUser) {
                        Log::info("Row $rowNumber: Skipped existing user {$raw['username']} / {$raw['email']}");
                        $skippedCount++;
                        continue;
                    }

                    $validator = Validator::make($raw, [
                        'first_name' => 'required|string',
                        'last_name' => 'required|string',
                        'username' => 'required|string|unique:users,username',
                        'email' => 'nullable|email',
                        'password' => 'required|string|min:6',
                        'date_hired' => 'required|date',
                        'employee_id' => 'required|string|unique:employment_details,employee_id',
                        'employment_type' => 'required|string',
                        'employment_status' => 'required|string',
                    ]);

                    if ($validator->fails()) {
                        Log::warning("Row $rowNumber: Validation failed.", ['errors' => $validator->errors()->all()]);
                        throw new \Exception("Row $rowNumber: " . implode(', ', $validator->errors()->all()));
                    }

                    Log::info("Row $rowNumber: Creating user '{$raw['username']}'");

                    $user = new User();
                    $user->username = $raw['username'];
                    $user->tenant_id = $this->tenantId;
                    $user->email = $raw['email'];
                    $user->password = Hash::make($raw['password']);
                    Log::info("Saving user to the database...");
                    $user->save();
                    Log::info("User '{$raw['username']}' saved successfully.");

                    // Log user ID to ensure it's saved properly
                    Log::info("User created with ID: {$user->id}");;

                    EmploymentPersonalInformation::create([
                        'user_id' => $user->id,
                        'first_name' => $raw['first_name'],
                        'last_name' => $raw['last_name'],
                        'middle_name' => $raw['middle_name'] ?? null,
                        'suffix' => $raw['suffix'] ?? null,
                        'phone_number' => $raw['phone_number'] ?? null,
                        'profile_picture' => null,
                        'gender' => $raw['gender'] ?? null,
                        'civil_status' => $raw['civil_status'] ?? null,
                        'spouse_name' => $raw['spouse_name'] ?? null,
                    ]);

                    EmploymentDetail::create([
                        'user_id' => $user->id,
                        'designation_id' => $designation->id,
                        'department_id' => $department->id,
                        'status' => 1,
                        'date_hired' => $raw['date_hired'],
                        'employee_id' => $raw['employee_id'],
                        'employment_type' => $raw['employment_type'],
                        'employment_status' => $raw['employment_status'],
                        'branch_id' => $branch->id,
                        'security_license_number' => $raw['security_license_number'] ?? null,
                        'security_license_expiration' => $raw['security_license_expiration'] ?? null,
                    ]);

                    EmploymentGovernmentId::create([
                        'user_id' => $user->id,
                        'sss_number' => $raw['sss_number'] ?? null,
                        'tin_number' => $raw['tin_number'] ?? null,
                        'philhealth_number' => $raw['philhealth_number'] ?? null,
                        'pagibig_number' => $raw['pagibig_number'] ?? null,
                    ]);

                    $sss = $branch->sss_contribution_type === 'fixed' ? $branch->fixed_sss_amount : ($branch->sss_contribution_type === 'manual' ? 'manual' : 'system');
                    $philhealth = $branch->philhealth_contribution_type === 'fixed' ? $branch->fixed_philhealth_amount : ($branch->philhealth_contribution_type === 'manual' ? 'manual' : 'system');
                    $pagibig = $branch->pagibig_contribution_type === 'fixed' ? $branch->fixed_pagibig_amount : ($branch->pagibig_contribution_type === 'manual' ? 'manual' : 'system');
                    $withholding = $branch->withholding_tax_type === 'fixed' ? $branch->fixed_withholding_tax_amount : ($branch->withholding_tax_type === 'manual' ? 'manual' : 'system');
                    $workedDays = $branch->worked_days_per_year === 'custom' ? $branch->custom_worked_days : $branch->worked_days_per_year ?? null;

                    SalaryDetail::create([
                        'user_id' => $user->id,
                        'sss_contribution' => $sss,
                        'philhealth_contribution' => $philhealth,
                        'pagibig_contribution' => $pagibig,
                        'withholding_tax' => $withholding,
                        'worked_days_per_year' => $workedDays,
                    ]);

                    UserLog::create([
                        'user_id' => Auth::id(),
                        'global_user_id' => null,
                        'module' => 'Employee',
                        'action' => 'Import',
                        'description' => 'Imported employee via user-friendly CSV',
                        'affected_id' => $user->id,
                        'old_data' => null,
                        'new_data' => json_encode($raw),
                    ]);

                    Log::info("Row $rowNumber: Successfully imported user '{$raw['username']}'");

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = ['row' => $rowNumber, 'error' => $e->getMessage()];
                    Log::error("CSV import failed for row $rowNumber: " . $e->getMessage(), ['row' => $raw]);
                }
            }

            DB::commit();

            Log::info("CSV IMPORT COMPLETE: Imported: $successCount | Skipped: $skippedCount", ['errors' => $errors]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CSV import failed.', ['error' => $e->getMessage()]);
        } finally {
            if (isset($file) && is_resource($file)) {
                fclose($file);
                Log::info("CSV file closed: $path");
            }
        }
    }
}
