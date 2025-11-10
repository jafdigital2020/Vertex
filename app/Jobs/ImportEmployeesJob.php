<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Plan;
use App\Models\Branch;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryDetail;
use App\Models\Subscription;
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
use App\Services\LicenseOverageService;
use App\Models\EmploymentPersonalInformation;

class ImportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path, $tenantId;
    protected $licenseOverageService;

    /**
     * Create a new job instance.
     */
    public function __construct($path, $tenantId)
    {
        $this->path = $path;
        $this->tenantId = $tenantId;
        $this->licenseOverageService = app(LicenseOverageService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = storage_path('app/private/' . $this->path);
        Log::info("ImportEmployeesJob starting", [
            'tenant_id' => $this->tenantId,
            'file_path' => $this->path,
            'full_path' => $path,
            'file_exists' => file_exists($path)
        ]);

        if (!file_exists($path)) {
            Log::error("CSV import failed: File does not exist", [
                'expected_path' => $path,
                'storage_path' => storage_path('app/'),
                'relative_path' => $this->path
            ]);
            $this->logImportResult(0, 0, 0, [['error' => 'File not found at: ' . $path]], 'failed');
            return;
        }

        $file = fopen($path, 'r');
        if (!$file) {
            Log::error("CSV import failed: Unable to open file", [
                'path' => $path,
                'is_readable' => is_readable($path),
                'file_size' => file_exists($path) ? filesize($path) : 'N/A'
            ]);
            $this->logImportResult(0, 0, 0, [['error' => 'Unable to open file']], 'failed');
            return;
        }

        $header = fgetcsv($file);
        if (!$header) {
            Log::error("CSV import failed: Unable to read header row from file at path: $path");
            fclose($file);
            $this->logImportResult(0, 0, 0, [['error' => 'Invalid CSV file format']], 'failed');
            return;
        }

        // ✅ NEW: Check subscription and plan limits before processing
        $subscription = Subscription::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if (!$subscription) {
            Log::error("CSV import failed: No active subscription found for tenant {$this->tenantId}");
            fclose($file);
            $this->logImportResult(0, 0, 0, [['error' => 'No active subscription found']], 'failed');
            return;
        }

        // Count current active users
        $currentActiveUsers = User::where('tenant_id', $this->tenantId)
            ->where('active_license', true)
            ->count();

        // Count total rows in CSV to import
        $totalRowsToImport = 0;
        while (fgetcsv($file)) {
            $totalRowsToImport++;
        }
        rewind($file);
        fgetcsv($file); // Skip header again

        $planLimit = $subscription->plan->employee_limit ?? $subscription->active_license ?? 0;
        $totalAfterImport = $currentActiveUsers + $totalRowsToImport;

        Log::info("License validation check", [
            'tenant_id' => $this->tenantId,
            'current_active_users' => $currentActiveUsers,
            'rows_to_import' => $totalRowsToImport,
            'plan_limit' => $planLimit,
            'total_after_import' => $totalAfterImport,
            'plan_name' => $subscription->plan->name ?? 'Unknown'
        ]);

        // ✅ Check if import would exceed plan limits
        if ($totalAfterImport > $planLimit) {
            $overage = $totalAfterImport - $planLimit;
            $overageCost = $overage * LicenseOverageService::OVERAGE_RATE_PER_LICENSE;

            $errorMessage = "Import would exceed your plan limit. " .
                "Current users: {$currentActiveUsers}, " .
                "Trying to import: {$totalRowsToImport}, " .
                "Plan limit: {$planLimit}. " .
                "This would result in {$overage} overage users costing ₱{$overageCost}/month. " .
                "Please upgrade your plan or reduce the number of employees to import.";

            Log::warning("CSV import blocked due to license limits", [
                'tenant_id' => $this->tenantId,
                'current_users' => $currentActiveUsers,
                'import_count' => $totalRowsToImport,
                'plan_limit' => $planLimit,
                'overage_count' => $overage,
                'overage_cost' => $overageCost
            ]);

            fclose($file);
            $this->logImportResult(0, 0, $totalRowsToImport, [
                ['error' => $errorMessage, 'type' => 'license_limit_exceeded']
            ], 'blocked');
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
                foreach (['date_hired'] as $dateField) {
                    if (isset($raw[$dateField])) {
                        $value = trim($raw[$dateField]);
                        if ($value === '' || strtolower($value) === 'n/a') {
                            $raw[$dateField] = null;
                        }
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
                    // Convert technical errors to user-friendly messages
                    $userFriendlyError = $this->translateError($e->getMessage(), $raw);
                    $errors[] = ['row' => $rowNumber, 'error' => $userFriendlyError];
                    Log::error("CSV import failed for row $rowNumber: " . $e->getMessage(), ['row' => $raw]);
                }
            }

            DB::commit();

            Log::info("CSV IMPORT COMPLETE: Imported: $successCount | Skipped: $skippedCount", ['errors' => $errors]);

            // ✅ NEW: Log final import results for frontend feedback
            $this->logImportResult($successCount, $skippedCount, $rowNumber - 1, $errors, 'completed');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CSV import failed.', ['error' => $e->getMessage()]);
            $this->logImportResult(0, 0, 0, [['error' => 'Import failed: ' . $e->getMessage()]], 'failed');
        } finally {
            if (isset($file) && is_resource($file)) {
                fclose($file);
                Log::info("CSV file closed: $path");
            }

            // Clean up the uploaded CSV file after processing
            if (file_exists($path)) {
                unlink($path);
                Log::info("CSV file deleted after processing: $path");
            }
        }
    }

    /**
     * ✅ NEW: Log import results to a file that can be read by the frontend
     */
    private function logImportResult($successCount, $skippedCount, $totalProcessed, $errors, $status)
    {
        $result = [
            'tenant_id' => $this->tenantId,
            'status' => $status, // completed, failed, blocked
            'timestamp' => now()->toISOString(),
            'summary' => [
                'total_processed' => $totalProcessed,
                'successful_imports' => $successCount,
                'skipped_records' => $skippedCount,
                'errors_count' => count($errors),
                'has_license_limit_error' => collect($errors)->contains(function($error) {
                    return isset($error['type']) && $error['type'] === 'license_limit_exceeded';
                })
            ],
            'errors' => $errors,
            'file_path' => $this->path
        ];

        // Store results in a temporary file that frontend can access
        $resultsPath = storage_path('app/import_results/import_result_' . $this->tenantId . '_' . time() . '.json');

        // Ensure directory exists
        $dir = dirname($resultsPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($resultsPath, json_encode($result, JSON_PRETTY_PRINT));

        Log::info('Import result logged', [
            'tenant_id' => $this->tenantId,
            'status' => $status,
            'results_file' => $resultsPath,
            'summary' => $result['summary']
        ]);
    }

    /**
     * Convert technical database/validation errors to user-friendly messages
     */
    private function translateError($errorMessage, $rowData = [])
    {
        // Database constraint errors
        if (str_contains($errorMessage, 'SQLSTATE[01000]') && str_contains($errorMessage, 'Data truncated for column')) {
            if (str_contains($errorMessage, "'gender'")) {
                return 'Invalid gender value. Please use: Male, Female, or Other.';
            }
            if (str_contains($errorMessage, "'civil_status'")) {
                return 'Invalid civil status. Please use: Single, Married, Divorced, Widowed, or Separated.';
            }
            if (str_contains($errorMessage, "'employment_type'")) {
                return 'Invalid employment type. Please check the correct format.';
            }
            return 'Invalid data format. Please check your values match the expected format.';
        }

        // Unique constraint violations
        if (str_contains($errorMessage, 'SQLSTATE[23000]') && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, "'username'")) {
                return 'Username already exists. Please use a different username.';
            }
            if (str_contains($errorMessage, "'email'")) {
                return 'Email address already exists. Please use a different email.';
            }
            if (str_contains($errorMessage, "'employee_id'")) {
                return 'Employee ID already exists. Please use a different employee ID.';
            }
            return 'Duplicate value found. Please check for existing records.';
        }

        // Foreign key constraint errors
        if (str_contains($errorMessage, 'SQLSTATE[23000]') && str_contains($errorMessage, 'foreign key constraint')) {
            return 'Referenced data not found. Please check branch, department, or role names.';
        }

        // Validation errors (Laravel)
        if (str_contains($errorMessage, 'validation')) {
            return 'Required fields are missing or invalid. Please check all required columns.';
        }

        // Date format errors
        if (str_contains($errorMessage, 'date') && str_contains($errorMessage, 'format')) {
            return 'Invalid date format. Please use YYYY-MM-DD format.';
        }

        // String too long errors
        if (str_contains($errorMessage, 'Data too long for column')) {
            return 'Text too long. Please shorten the value and try again.';
        }

        // Custom validation errors that are already user-friendly
        if (!str_contains($errorMessage, 'SQLSTATE') && !str_contains($errorMessage, 'SQL:')) {
            return $errorMessage;
        }

        // Fallback for any other database errors
        return 'Data import error. Please check your data format and try again.';
    }
}
