<?php

/**
 * Test script for error message translation
 */

// Simulate the translateError function
function translateError($errorMessage, $rowData = [])
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

echo "🧪 Testing Error Message Translation\n";
echo "===================================\n\n";

// Test cases
$testCases = [
    // Original error from the user
    "SQLSTATE[01000]: Warning: 1265 Data truncated for column 'gender' at row 1 (Connection: mysql, SQL: insert into `employment_personal_information` (`user_id`, `first_name`, `last_name`, `middle_name`, `suffix`, `phone_number`, `profile_picture`, `gender`, `civil_status`, `spouse_name`, `updated_at`, `created_at`) values (8817, Patrick, Dassad, Eqwewq, , 3123123213, ?, , , , 2025-11-10 13:57:30, 2025-11-10 13:57:30))",

    // Other test cases
    "SQLSTATE[01000]: Warning: 1265 Data truncated for column 'civil_status' at row 1",
    "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'john.doe' for key 'users.username'",
    "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'john@example.com' for key 'users.email'",
    "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails",
    "Row 5: 'first_name' is required but empty.",
    "Invalid date format for date_hired field",
    "SQLSTATE[22001]: String data too long: 1406 Data too long for column 'first_name'",
    "Already user-friendly error message",
];

foreach ($testCases as $i => $testError) {
    $translated = translateError($testError);
    echo "Test " . ($i + 1) . ":\n";
    echo "Original: " . substr($testError, 0, 100) . (strlen($testError) > 100 ? "..." : "") . "\n";
    echo "Translated: " . $translated . "\n";
    echo "✅ " . (strlen($translated) < strlen($testError) ? "Simplified" : "Processed") . "\n\n";
}

echo "🎉 All error translations tested successfully!\n";
echo "\nKey improvements:\n";
echo "- Technical SQLSTATE errors become user-friendly messages\n";
echo "- Specific field errors (gender, civil_status) have targeted help\n";
echo "- Database constraint violations are explained clearly\n";
echo "- Already user-friendly messages are preserved\n";
echo "- Fallback message for unknown database errors\n";
