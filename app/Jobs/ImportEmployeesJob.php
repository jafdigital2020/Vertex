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

        // Acceptable values for gender and civil status
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
        $rowNumber = 1; // For user-friendly error reporting

        DB::beginTransaction();
        try {
            while ($row = fgetcsv($file)) {
                $rowNumber++;
                $raw = [];
                // Check if row columns align with header
                if (count($row) !== count($header)) {
                    $errorMsg = "Row $rowNumber: The number of columns does not match the template. Please check if you have missing or extra columns.";
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => $errorMsg
                    ];
                    Log::warning("CSV import row error: $errorMsg", ['row' => $row]);
                    continue;
                }
                foreach ($header as $index => $csvHeader) {
                    $mappedKey = $columnMap[$csvHeader] ?? null;
                    if ($mappedKey) {
                        $raw[$mappedKey] = isset($row[$index]) ? trim($row[$index]) : null;
                    }
                }

                // Adjust name casing for first_name, last_name, middle_name
                foreach (['first_name', 'last_name', 'middle_name'] as $nameKey) {
                    if (!empty($raw[$nameKey])) {
                        $raw[$nameKey] = ucwords(strtolower($raw[$nameKey]));
                    }
                }

                try {
                    // Check required fields
                    foreach ($requiredFields as $field) {
                        if (empty($raw[$field])) {
                            $errorMsg = "Row $rowNumber: The field \"$field\" is required. Please fill in all required fields.";
                            Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                            throw new \Exception($errorMsg);
                        }
                    }

                    // Validate and normalize gender if present
                    if (!empty($raw['gender'])) {
                        $genderValue = strtolower(trim($raw['gender']));
                        $raw['gender'] = $genderMap[$genderValue] ?? null;
                        if (!$raw['gender'] || !in_array($raw['gender'], $validGenders)) {
                            $errorMsg = "Row $rowNumber: The gender value \"{$row['gender']}\" is invalid. Please use 'Male', 'Female', or 'Other'.";
                            Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                            throw new \Exception($errorMsg);
                        }
                    }

                    // Validate civil status if present
                    if (!empty($raw['civil_status'])) {
                        $civilValue = strtolower($raw['civil_status']);
                        if (!in_array($civilValue, $validCivilStatus)) {
                            $errorMsg = "Row $rowNumber: The civil status value \"{$raw['civil_status']}\" is invalid. Please use 'Single', 'Married', 'Widowed', 'Divorced', or 'Separated'.";
                            Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                            throw new \Exception($errorMsg);
                        }
                        $raw['civil_status'] = ucfirst($civilValue);
                    }

                    // Lookups
                    $role = Role::where('role_name', $raw['role_name'])->first();
                    if (!$role) {
                        $errorMsg = "Row $rowNumber: The role \"{$raw['role_name']}\" does not exist. Please check Roles.";
                        Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                        throw new \Exception($errorMsg);
                    }

                    $branch = Branch::where('name', $raw['branch_name'])->first();
                    if (!$branch) {
                        $errorMsg = "Row $rowNumber: The branch \"{$raw['branch_name']}\" is not found.";
                        Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                        throw new \Exception($errorMsg);
                    }

                    $department = Department::where('department_name', $raw['department_name'])
                        ->where('branch_id', $branch->id)
                        ->first();
                    if (!$department) {
                        $errorMsg = "Row $rowNumber: The department \"{$raw['department_name']}\" under branch \"{$raw['branch_name']}\" does not exist.";
                        Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                        throw new \Exception($errorMsg);
                    }

                    $designation = Designation::where('designation_name', $raw['designation_name'])
                        ->where('department_id', $department->id)
                        ->first();
                    if (!$designation) {
                        $errorMsg = "Row $rowNumber: The designation \"{$raw['designation_name']}\" under department \"{$raw['department_name']}\" does not exist.";
                        Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                        throw new \Exception($errorMsg);
                    }

                    // Format Date
                    try {
                        $parsedDate = Carbon::parse($raw['date_hired']);
                        $raw['date_hired'] = $parsedDate->format('Y-m-d');
                    } catch (\Exception $e) {
                        $errorMsg = "Row $rowNumber: Invalid date format for 'Date Hired': '{$raw['date_hired']}'. Please use YYYY-MM-DD format.";
                        Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                        throw new \Exception($errorMsg);
                    }

                    // Format Security License Expiration if present
                    if (!empty($raw['security_license_expiration'])) {
                        try {
                            $raw['security_license_expiration'] = Carbon::parse($raw['security_license_expiration'])->format('Y-m-d');
                        } catch (\Exception $e) {
                            $errorMsg = "Row $rowNumber: Invalid date format for 'Security License Expiration': '{$raw['security_license_expiration']}'. Please use YYYY-MM-DD format.";
                            Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                            throw new \Exception($errorMsg);
                        }
                    }

                    if (empty($raw['email'])) {
                        $raw['email'] = null;  // Allow null email
                    }

                    // Validate unique fields
                    $validator = Validator::make($raw, [
                        'first_name' => 'required|string',
                        'last_name' => 'required|string',
                        'username' => 'required|string|unique:users,username',
                        'email' => 'nullable|email|unique:users,email,' . $raw['email'],
                        'password' => 'required|string|min:6',
                        'date_hired' => 'required|date',
                        'employee_id' => 'required|string|unique:employment_details,employee_id',
                        'employment_type' => 'required|string',
                        'employment_status' => 'required|string',
                    ]);

                    if ($validator->fails()) {
                        $errorMsg = "Row $rowNumber: " . implode(', ', $validator->errors()->all());
                        Log::warning("CSV import row error: $errorMsg", ['row' => $raw]);
                        throw new Exception($errorMsg);
                    }

                    // Create User
                    $user = new User();
                    $user->username = $raw['username'];
                    $user->tenant_id = $this->tenantId;
                    $user->email = $raw['email'];
                    $user->password = Hash::make($raw['password']);
                    $user->save();

                    UserPermission::create([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'menu_ids' => $role->menu_ids,
                        'module_ids' => $role->module_ids,
                        'user_permission_ids' => $role->role_permission_ids,
                        'status' => 1,
                    ]);

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
                        'security_license_number' => !empty($raw['security_license_number']) ? $raw['security_license_number'] : null,
                        'security_license_expiration' => !empty($raw['security_license_expiration']) ? $raw['security_license_expiration'] : null,
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

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage()
                    ];
                    Log::error("CSV import failed for row $rowNumber: " . $e->getMessage(), ['row' => $raw]);
                }
            }

            DB::commit();

            // Instead of return, just log
            Log::info("CSV IMPORT: $successCount employees imported successfully.", ['errors' => $errors]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CSV import failed.', ['error' => $e->getMessage()]);
        } finally {
            if (isset($file) && is_resource($file)) {
                fclose($file);
            }
        }
    }
}
