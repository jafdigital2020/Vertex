# Payslip Upload - User-Friendly Error Messages

This document lists all error messages displayed to users during the payslip upload process. All messages are written in plain English and easy for non-technical users to understand.

## Frontend Validation Messages (Before Upload)

### File Selection Errors
- **No file selected**: "Please select a CSV file before uploading."
- **File too large**: "Your file is too large. Please upload a file smaller than 10MB."
- **Wrong file type**: "Please upload a CSV file. Other file types are not supported."

## Backend Validation Messages (During Upload)

### Permission Errors
- **No permission**: "Sorry, you don't have permission to upload payslips. Please contact your administrator."

### File Validation Errors
- **No file uploaded**: "Please select a file to upload."
- **Invalid file**: "The uploaded file is invalid. Please try again."
- **Wrong file format**: "Only CSV files are allowed. Please upload a .csv file."
- **File too large**: "The file is too large. Maximum file size is 10MB."

### CSV Content Errors
- **Empty file**: "The file you uploaded is empty. Please add some data and try again."
- **Missing columns**: "Your file is missing some important columns: [column names]. Please download the template and make sure all columns are included."
- **No valid data**: "No valid data found in your file. Please make sure you have filled in the employee information correctly."

### General Upload Errors
- **Upload failed**: "Something went wrong while uploading your file. Please try again or contact support if the problem persists."

## Import Processing Messages

### Status Updates
- **Processing**: "Your payslips are being processed. Please wait a moment..."
- **Uploading**: "File uploaded. Processing [X] records..."

### Success Messages
- **All successful**: "Great! All [X] payslips have been successfully imported!"
- **Partial success**: "Import completed! [X] payslips were successfully imported, but [Y] rows had errors. Please review the errors below and correct them in your file."

### Failure Messages
- **Complete failure**: "The payslip import failed. Please check your file and try again. If the problem continues, contact support for help."
- **Status check failed**: "We could not check the status of your import. Please refresh the page to see if your payslips were imported."

## Row-Level Error Messages (During Import)

These errors appear in the error details table when specific rows fail:

### Employee ID Errors
- **Empty Employee ID**: "The Employee ID field is empty. Please provide a valid employee ID."
- **Employee not found**: "We could not find an employee with ID '[ID]' in our system. Please check the employee ID and try again."

### Date Errors
- **Invalid month or year**: "The Payroll Month or Year is not valid. Please enter a valid month (1-12 or month name) and a valid year (e.g., 2024)."

### General Row Errors
- **Unknown error**: "Something went wrong while processing this row. Please check all the data and try again."

## Error Display Format

Errors are displayed to users in two ways:

1. **Toast Notifications** - Brief popup messages for immediate feedback
2. **Modal Alerts** - Detailed error information with a table showing:
   - Row number where the error occurred
   - Employee ID (if available)
   - Clear description of what went wrong

## Best Practices Applied

✅ **Plain English** - No technical jargon or error codes
✅ **Specific** - Tells users exactly what went wrong
✅ **Actionable** - Provides clear next steps to fix the issue
✅ **Friendly Tone** - Encouraging and helpful, not intimidating
✅ **Context** - Includes relevant information (row numbers, IDs, values)
✅ **Solutions** - Suggests how to resolve the problem

## Example Error Display

When a row fails, users see:

```
Row: 5
Employee ID: EMP-001
Error: We could not find an employee with ID "EMP-001" in our system. Please check the employee ID and try again.
```

This helps users:
- Know exactly which row has the problem
- Identify the employee affected
- Understand what went wrong
- Know how to fix it
