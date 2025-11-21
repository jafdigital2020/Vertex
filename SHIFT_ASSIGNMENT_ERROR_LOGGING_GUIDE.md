# Shift Assignment Error Logging Guide

## Overview
This document explains how to identify and debug errors when deleting shift assignments in the Timora system.

## Updated Files
1. **Backend Controller:** `app/Http/Controllers/Tenant/Attendance/ShiftManagementController.php`
2. **Frontend View:** `resources/views/tenant/attendance/shiftmanagement/shiftassignment.blade.php`

---

## How to Identify Errors

### 1. Check Browser Console

When a delete operation fails, open the browser console (F12) and look for:

```javascript
Delete Shift Assignment Error: {xhr object}
Error Details: {detailed response}
Error Location: {file: "ShiftManagementController.php", line: 123}
File: ShiftManagementController.php, Line: 123
Full Error Message Shown to User: "Actual error message"
```

**Steps:**
1. Press `F12` to open Developer Tools
2. Go to the **Console** tab
3. Try to delete a shift assignment
4. Look for the error messages above

---

### 2. Check Laravel Logs

The backend logs detailed information to `storage/logs/laravel.log`.

**Search for these log entries:**

#### a) Function Called
```
deleteAssignShift called
user_id: 123
```

#### b) Permission Check
```
Permission denied for deleteAssignShift
user_id: 123
```

#### c) Assignments Found
```
Found assignments
user_id: 123
count: 2
```

#### d) Processing Each Assignment
```
Processing assignment for deletion
assignment_id: 45
user_id: 123
```

#### e) Auth User Info
```
Auth user info
auth_user_id: 10
global_user_id: null
```

#### f) Assignment Deleted Successfully
```
Assignment deleted
assignment_id: 45
```

#### g) Completion Success
```
deleteAssignShift completed successfully
user_id: 123
deleted_count: 2
```

#### h) Error Occurred
```
[ERROR] Shift assignment deletion failed
user_id: 123
error: "Actual error message"
file: "/path/to/ShiftManagementController.php"
line: 1349
trace: "Full stack trace..."
```

---

## Common Error Scenarios

### Scenario 1: Permission Denied

**Console Output:**
```
Error Details: {status: "error", message: "You do not have the permission to delete."}
Full Error Message: "You do not have permission to delete."
```

**Laravel Log:**
```
Permission denied for deleteAssignShift
user_id: 123
```

**Solution:**
- User needs "Delete" permission for Module 16 (Shift & Schedule)
- Check user's permissions in User Management → Roles & Permissions
- Ensure permission includes operation "4" (Delete) for module "16"

---

### Scenario 2: No Shift Assignments Found

**Console Output:**
```
Error Details: {message: "No shift assignments found for this user."}
```

**Laravel Log:**
```
Found assignments
user_id: 123
count: 0

No assignments found for user
user_id: 123
```

**Solution:**
- The user doesn't have any shift assignments to delete
- This is not an error, just informational
- Return status: 404

---

### Scenario 3: Database Constraint Error

**Console Output:**
```
Error Details: {
  error: "SQLSTATE[23000]: Integrity constraint violation...",
  debug: {file: "ShiftManagementController.php", line: 1349}
}
```

**Laravel Log:**
```
[ERROR] Shift assignment deletion failed
error: "SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails..."
file: "/path/to/ShiftManagementController.php"
line: 1349
```

**Solution:**
- Check for foreign key constraints in database
- Look for related records in other tables referencing this shift assignment
- May need to delete related records first or use soft deletes

---

### Scenario 4: UserLog Creation Failed

**Console Output:**
```
Error Details: {
  error: "SQLSTATE[HY000]: General error: 1364 Field 'column_name' doesn't have a default value",
  debug: {file: "ShiftManagementController.php", line: 1338}
}
```

**Laravel Log:**
```
[ERROR] Shift assignment deletion failed
error: "Field 'user_id' doesn't have a default value"
line: 1338
```

**Solution:**
- UserLog table has required fields that aren't being populated
- Check if user is authenticated (both web and global guards)
- Verify UserLog::create() has all required fields

---

### Scenario 5: Assignment Not Found After Query

**Console Output:**
```
Error Details: {
  error: "Call to a member function delete() on null",
  debug: {file: "ShiftManagementController.php", line: 1349}
}
```

**Solution:**
- Assignment was deleted between query and delete operation
- Possible race condition
- Add null check before deleting

---

## Debugging Steps

### Step 1: Reproduce the Error
1. Navigate to Shift Management → Shift Assignment
2. Try to delete a shift assignment
3. Note the exact error message shown

### Step 2: Check Browser Console
1. Open Developer Tools (F12)
2. Go to Console tab
3. Look for error details including:
   - Error message
   - File name
   - Line number
   - Full response object

### Step 3: Check Laravel Logs
1. Open `storage/logs/laravel.log`
2. Search for "deleteAssignShift" or "Shift assignment deletion failed"
3. Look at the timestamp matching when you tried to delete
4. Read the full error message and stack trace

### Step 4: Analyze the Error

**Questions to ask:**
- What is the error message?
- At which line did it fail?
- What was the user_id?
- Was permission check successful?
- Were assignments found?
- Which assignment was being processed when it failed?

---

## Error Response Format

### Success Response (200)
```json
{
  "message": "Successfully deleted 2 shift assignment(s) for user ID: 123."
}
```

### Permission Error (403)
```json
{
  "status": "error",
  "message": "You do not have the permission to delete."
}
```

### Not Found (404)
```json
{
  "message": "No shift assignments found for this user."
}
```

### Server Error (500)
```json
{
  "message": "Failed to delete shift assignments.",
  "error": "Detailed error message from exception",
  "debug": {
    "file": "ShiftManagementController.php",
    "line": 1349
  }
}
```

---

## Frontend Error Handling

The JavaScript error handler now:

1. **Logs full XHR object** to console
2. **Extracts error message** from responseJSON
3. **Displays debug info** (file and line number) in console
4. **Shows user-friendly message** via toastr notification
5. **Logs final message** shown to user

---

## Backend Logging Points

The controller logs at these points:

1. **Function entry** - User ID
2. **Permission check** - Pass/fail
3. **Query result** - Number of assignments found
4. **Each iteration** - Assignment ID being processed
5. **Auth check** - Which guard is authenticated
6. **Deletion** - Confirmation of each delete
7. **Success** - Total count deleted
8. **Error** - Full exception details with trace

---

## Tips for Production

### Enable Detailed Logs
In `.env`:
```env
LOG_LEVEL=debug
APP_DEBUG=true  # Only in development!
```

### Monitor Log File
```bash
# Watch logs in real-time
php artisan pail --timeout=0

# Or use tail
tail -f storage/logs/laravel.log
```

### Query Log
Add to your controller temporarily for detailed SQL logging:
```php
\DB::enableQueryLog();
// ... your code ...
dd(\DB::getQueryLog());
```

---

## Testing Checklist

Test these scenarios to ensure logging works:

- [ ] Delete with valid permissions → Success logged
- [ ] Delete without permissions → Permission error logged
- [ ] Delete non-existent assignment → Not found logged
- [ ] Delete with database error → Exception logged with trace
- [ ] Delete with multiple assignments → Each iteration logged
- [ ] Check console shows file and line number on error
- [ ] Check toastr shows actual error message

---

## Related Files

- **Controller:** `app/Http/Controllers/Tenant/Attendance/ShiftManagementController.php`
  - Function: `deleteAssignShift($userId)` (Line 1287)

- **Frontend:** `resources/views/tenant/attendance/shiftmanagement/shiftassignment.blade.php`
  - Delete AJAX: Lines 697-738

- **API Route:** `routes/api.php` (Line 193)
  - Route: `DELETE /api/shift-management/shift-assignment/user/{userId}`

- **Model:** `app/Models/ShiftAssignment.php`

- **Logs:** `storage/logs/laravel.log`

---

## Example Log Analysis

### Successful Deletion
```
[2025-11-21 10:30:15] local.INFO: deleteAssignShift called {"user_id":123}
[2025-11-21 10:30:15] local.INFO: Found assignments {"user_id":123,"count":2}
[2025-11-21 10:30:15] local.INFO: Processing assignment for deletion {"assignment_id":45,"user_id":123}
[2025-11-21 10:30:15] local.INFO: Auth user info {"auth_user_id":10,"global_user_id":null}
[2025-11-21 10:30:15] local.INFO: Assignment deleted {"assignment_id":45}
[2025-11-21 10:30:15] local.INFO: Processing assignment for deletion {"assignment_id":46,"user_id":123}
[2025-11-21 10:30:15] local.INFO: Auth user info {"auth_user_id":10,"global_user_id":null}
[2025-11-21 10:30:15] local.INFO: Assignment deleted {"assignment_id":46}
[2025-11-21 10:30:15] local.INFO: deleteAssignShift completed successfully {"user_id":123,"deleted_count":2}
```

### Failed Deletion
```
[2025-11-21 10:35:20] local.INFO: deleteAssignShift called {"user_id":123}
[2025-11-21 10:35:20] local.INFO: Found assignments {"user_id":123,"count":1}
[2025-11-21 10:35:20] local.INFO: Processing assignment for deletion {"assignment_id":47,"user_id":123}
[2025-11-21 10:35:20] local.INFO: Auth user info {"auth_user_id":10,"global_user_id":null}
[2025-11-21 10:35:20] local.ERROR: Shift assignment deletion failed {
  "user_id":123,
  "error":"SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row",
  "file":"/var/www/html/app/Http/Controllers/Tenant/Attendance/ShiftManagementController.php",
  "line":1349,
  "trace":"#0 /var/www/html/app/Http/Controllers/..."
}
```

---

## Quick Reference

| What | Where to Look | What to Search |
|------|---------------|----------------|
| Permission errors | Browser console & logs | "Permission denied" |
| Database errors | Laravel logs | "SQLSTATE" or "Integrity" |
| Not found errors | Browser console | "No shift assignments found" |
| Exception details | Laravel logs | "Shift assignment deletion failed" |
| Line numbers | Browser console | "Error Location" or "debug" |
| User actions | Laravel logs | "deleteAssignShift called" |
| Success confirmations | Laravel logs | "completed successfully" |

---

**Last Updated:** November 21, 2025
**Version:** 1.0.0
