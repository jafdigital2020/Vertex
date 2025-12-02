# Timora HRIS Public API Documentation

**Version:** 1.0  
**Base URL:** `https://your-domain.com/api`  
**Last Updated:** November 29, 2025

---

## 📋 Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Employee Self-Service APIs](#employee-self-service-apis)
- [Leave Management](#leave-management)
- [Official Business Management](#official-business-management)
- [Request Attendance](#request-attendance)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Data Formats](#data-formats)
- [Best Practices](#best-practices)
- [Support](#support)
- [Changelog](#changelog)

---

## Overview

This documentation covers the public-facing APIs of Timora HRIS system. These endpoints are designed for:
- Mobile application development
- Employee self-service portals
- Third-party integrations

**Note:** All authenticated endpoints require a valid Bearer token obtained from the login endpoint.

---

## Authentication

### Login

Authenticate a user and receive an access token for subsequent API calls. Supports Super Admin, Tenant Admin, and Tenant User authentication.

**Endpoint:** `POST /api/login`  
**Authentication:** None  
**Rate Limit:** Throttled to prevent brute force attacks

**Request Body:**
```json
{
  "login": "juan@example.com",
  "password": "your_password",
  "companyCode": "COMP001",
  "remember": true
}
```

**Field Descriptions:**
- `login`: **Required**. Email address or username
- `password`: **Required**. User password
- `companyCode`: **Required for Tenant Users and Tenant Admins**. Optional for Super Admin. The company/tenant code
- `remember`: Optional. Remember me option (true/false)

**Success Response (200) - Tenant User:**
```json
{
  "message": "Tenant User login successful",
  "token": "1|abcdefghijklmnopqrstuvwxyz123456789",
  "user": {
    "id": 1,
    "username": "juan.delacruz",
    "email": "juan@example.com",
    "tenant_id": 5
  },
  "tenant": {
    "id": 5,
    "tenant_code": "COMP001",
    "tenant_name": "ABC Corporation"
  },
  "role": "tenant_user"
}
```

**Success Response (200) - Tenant Admin:**
```json
{
  "message": "Tenant Admin login successful",
  "token": "2|abcdefghijklmnopqrstuvwxyz987654321",
  "user": {
    "id": 10,
    "username": "admin.user",
    "email": "admin@example.com"
  },
  "tenant": {
    "id": 5,
    "tenant_code": "COMP001",
    "tenant_name": "ABC Corporation"
  },
  "role": "tenant_admin"
}
```

**Success Response (200) - Super Admin:**
```json
{
  "message": "Super Admin login successful",
  "token": "3|superadmintoken123456789",
  "user": {
    "id": 1,
    "username": "superadmin",
    "email": "superadmin@timora.com"
  },
  "role": "super_admin"
}
```

**Error Responses:**

**400 Bad Request** - Missing company code:
```json
{
  "message": "Company code is required for Tenant Admins"
}
```
or
```json
{
  "message": "Company code is required"
}
```

**401 Unauthorized** - Invalid username/email:
```json
{
  "message": "Invalid username or email.",
  "type": "login"
}
```

**401 Unauthorized** - Invalid password:
```json
{
  "message": "Invalid password.",
  "type": "password"
}
```

**403 Forbidden** - Wrong organization:
```json
{
  "message": "Unauthorized: Tenant Admin does not belong to this organization"
}
```

**404 Not Found** - Invalid company code:
```json
{
  "message": "Invalid company code"
}
```

---

### Verify Token

Verify if the current authentication token is still valid.

**Endpoint:** `GET /api/verify-token`  
**Authentication:** Required (Bearer Token)

**Headers:**
```
Authorization: Bearer {your_token}
```

**Success Response (200):**
```json
{
  "valid": true,
  "user": {
    "id": 1,
    "name": "Juan Dela Cruz",
    "email": "juan@example.com"
  }
}
```

---

### Logout

Invalidate the current authentication token and clear session data. Revokes all tokens for the authenticated user.

**Endpoint:** `POST /api/logout`  
**Authentication:** Required

**Headers:**
```
Authorization: Bearer {your_token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**Note:** This endpoint will:
- Revoke ALL tokens for the authenticated user
- Clear the user's session
- Invalidate the remember token
- Clear both web and global authentication guards

---

## Employee Self-Service APIs

These endpoints are designed for employee mobile apps and self-service portals.

### Get Profile

Retrieve current user's profile information.

**Endpoint:** `GET /api/profile`  
**Authentication:** Required

**Success Response (200):**
```json
{
  "id": 1,
  "employee_id": "EMP-001",
  "first_name": "Juan",
  "last_name": "Dela Cruz",
  "email": "juan@example.com",
  "position": "Software Engineer",
  "department": "IT Department",
  "branch": "Main Office",
  "profile_picture": "https://example.com/storage/profiles/juan.jpg"
}
```

---

### Clock In

Record employee clock-in time for the current shift or rest day. Supports geotagging, geofencing, photo capture, and late reason.

**Endpoint:** `POST /api/attendance/clock-in`  
**Authentication:** Required

**Request Body (multipart/form-data):**
```json
{
  "clock_in_method": "Timora Mobile App",
  "time_in_photo": "[File Upload - Optional]",
  "time_in_latitude": 14.5995,
  "time_in_longitude": 120.9842,
  "time_in_accuracy": 5.0,
  "late_status_reason": "Traffic on EDSA"
}
```

**Field Descriptions:**
- `clock_in_method`: Optional. Device or method used for clock-in (e.g., "Timora Mobile App", "Web", "Biometric")
- `time_in_photo`: Optional. Photo file (required if photo capture is enabled in settings)
- `time_in_latitude`: Optional. Latitude for geotagging (required if geotagging/geofencing is enabled)
- `time_in_longitude`: Optional. Longitude for geotagging (required if geotagging/geofencing is enabled)
- `time_in_accuracy`: Optional. Location accuracy in meters
- `late_status_reason`: Optional. Reason for being late (required if late status box is enabled and user is late)

**Success Response (200):**
```json
{
  "message": "Clock-In successful for Morning Shift",
  "data": {
    "id": 123,
    "user_id": 1,
    "shift_id": 5,
    "attendance_date": "2025-11-29",
    "date_time_in": "2025-11-29 08:00:00",
    "status": "present",
    "total_late_minutes": 0,
    "clock_in_method": "Timora Mobile App",
    "time_in_latitude": 14.5995,
    "time_in_longitude": 120.9842,
    "is_rest_day": false,
    "is_holiday": false
  }
}
```

**Success Response (200) - Rest Day:**
```json
{
  "message": "Rest Day Clock-In successful. Your hours will be calculated based on your actual work time.",
  "data": {
    "id": 124,
    "user_id": 1,
    "shift_id": null,
    "attendance_date": "2025-11-30",
    "date_time_in": "2025-11-30 08:00:00",
    "status": "present",
    "is_rest_day": true
  }
}
```

**Error Responses:**

**403 Forbidden** - Subscription expired:

```json
{
  "message": "Your subscription has expired or is inactive. Please contact your administrator to renew."
}
```

**403 Forbidden** - No active shift:
```json
{
  "message": "No active shift today."
}
```

**403 Forbidden** - Rest day not allowed:
```json
{
  "message": "Clock-in on rest days is not allowed. Please contact your administrator."
}
```

**403 Forbidden** - Already clocked in:
```json
{
  "message": "You already clocked in your current shift and haven't clocked out."
}
```

**403 Forbidden** - All shifts clocked in:
```json
{
  "message": "All shifts already clocked in today."
}
```

**403 Forbidden** - Too early to clock in:
```json
{
  "message": "You can only clock in starting at 7:30 AM.",
  "earliest_allowed_time": "7:30 AM",
  "minutes_until_allowed": 15
}
```

**422 Unprocessable Entity** - Photo required:
```json
{
  "message": "Photo is required before clock-in."
}
```

**422 Unprocessable Entity** - Location required:
```json
{
  "message": "Location is required. Please enable GPS/location services."
}
```

**422 Unprocessable Entity** - Late reason required:
```json
{
  "message": "Please provide a reason for being late."
}
```

**403 Forbidden** - Outside geofence:
```json
{
  "message": "You are outside the allowed work location. Please clock in from an authorized location."
}
```

---

### Clock Out

Record employee clock-out time for the current shift.

**Endpoint:** `POST /api/attendance/clock-out`  
**Authentication:** Required

**Request Body (multipart/form-data):**
```json
{
  "clock_out_method": "Timora Mobile App",
  "time_out_photo": "[File Upload - Optional]",
  "time_out_latitude": 14.5995,
  "time_out_longitude": 120.9842,
  "time_out_accuracy": 5.0
}
```

**Field Descriptions:**
- `clock_out_method`: Optional. Device or method used for clock-out
- `time_out_photo`: Optional. Photo file (required if photo capture is enabled)
- `time_out_latitude`: Optional. Latitude for geotagging (required if geotagging/geofencing is enabled)
- `time_out_longitude`: Optional. Longitude for geotagging (required if geotagging/geofencing is enabled)
- `time_out_accuracy`: Optional. Location accuracy in meters

**Success Response (200):**
```json
{
  "message": "You have successfully clocked out.",
  "data": {
    "id": 123,
    "user_id": 1,
    "attendance_date": "2025-11-29",
    "date_time_in": "2025-11-29 08:00:00",
    "date_time_out": "2025-11-29 17:00:00",
    "total_work_minutes": 480,
    "total_night_diff_minutes": 0,
    "total_undertime_minutes": 0,
    "status": "present",
    "clock_out_method": "Timora Mobile App"
  },
  "overtime_created": null
}
```

**Success Response (200) - With Automatic Overtime:**
```json
{
  "message": "You have successfully clocked out. Extra hours (2 hr 30 min) have been automatically submitted as overtime and are pending approval.",
  "data": {
    "id": 123,
    "date_time_out": "2025-11-29 19:30:00",
    "total_work_minutes": 600
  },
  "overtime_created": {
    "overtime_id": 56,
    "total_ot_minutes": 150,
    "total_ot_formatted": "2 hr 30 min",
    "status": "pending",
    "is_rest_day": false,
    "is_holiday": false
  }
}
```

**Error Responses:**

**403 Forbidden** - Not clocked in:
```json
{
  "message": "You are not currently clocked in."
}
```

**422 Unprocessable Entity** - Photo required:
```json
{
  "message": "Photo is required before clock-out."
}
```

**422 Unprocessable Entity** - Location required:
```json
{
  "message": "Location is required. Please enable GPS/location services."
}
```

---

### Start Break

Start a break during the current shift.

**Endpoint:** `POST /api/attendance/break-in`  
**Authentication:** Required

**Request Body:**
```json
{
  "break_type": "lunch"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Break started successfully for Morning Shift.",
  "data": {
    "attendance_id": 123,
    "shift_id": 1,
    "shift_name": "Morning Shift",
    "break_type": "lunch",
    "break_in": "12:00:00",
    "max_break_minutes": 60
  }
}
```

**Error Responses:**

**403 Forbidden** - Not clocked in:
```json
{
  "success": false,
  "message": "You must be clocked in to start a break."
}
```

**403 Forbidden** - Break already completed:
```json
{
  "success": false,
  "message": "You have already completed your break for this shift. Only one break is allowed per shift."
}
```

**403 Forbidden** - Break already active:
```json
{
  "success": false,
  "message": "You already have an active break for this shift. Please end your current break first."
}
```

**403 Forbidden** - Break not allowed:
```json
{
  "success": false,
  "message": "Break time is not allowed for this shift."
}
```

---

### End Break

End the current break during the shift.

**Endpoint:** `POST /api/attendance/break-out`  
**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "message": "Break ended successfully for Morning Shift.",
  "data": {
    "attendance_id": 123,
    "shift_id": 1,
    "shift_name": "Morning Shift",
    "break_in": "12:00:00",
    "break_out": "12:45:00",
    "duration_minutes": 45,
    "max_break_minutes": 60,
    "exceeded": false
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "You must start a break before ending it."
}
```

---

### Check Break Status

Check the current break status for the active shift.

**Endpoint:** `GET /api/attendance/break-status`  
**Authentication:** Required

**Success Response (200) - Active Break:**
```json
{
  "success": true,
  "has_active_break": true,
  "break_completed": false,
  "message": "You have an active break.",
  "data": {
    "attendance_id": 123,
    "shift_id": 1,
    "shift_name": "Morning Shift",
    "break_in": "12:00:00",
    "elapsed_minutes": 15,
    "max_break_minutes": 60
  }
}
```

**Success Response (200) - No Break:**
```json
{
  "success": true,
  "has_active_break": false,
  "break_completed": false,
  "message": "No break taken yet for this shift.",
  "data": {
    "attendance_id": 123,
    "shift_id": 1,
    "shift_name": "Morning Shift",
    "max_break_minutes": 60,
    "break_available": true
  }
}
```

---

### Get My Attendance

Retrieve employee's own attendance records.

**Endpoint:** `GET /api/attendance-employee`  
**Authentication:** Required

**Query Parameters:**
```
?start_date=2025-11-01&end_date=2025-11-30&page=1
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "date": "2025-11-29",
      "clock_in": "08:00:00",
      "clock_out": "17:00:00",
      "status": "present",
      "total_hours": 9.0
    },
    {
      "id": 122,
      "date": "2025-11-28",
      "clock_in": "08:05:00",
      "clock_out": "17:00:00",
      "status": "late",
      "total_hours": 8.92
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 30,
    "per_page": 15
  }
}
```

---

### Get My Leave Requests

Retrieve employee's leave requests.

**Endpoint:** `GET /api/leave/leave-employee`  
**Authentication:** Required

**Query Parameters:**
```
?status=pending&page=1
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "leave_type": "Sick Leave",
      "start_date": "2025-12-01",
      "end_date": "2025-12-03",
      "total_days": 3,
      "reason": "Medical checkup",
      "status": "pending",
      "created_at": "2025-11-25"
    }
  ]
}
```

---

### Submit Leave Request

Submit a new leave request.

**Endpoint:** `POST /api/leave/leave-request`  
**Authentication:** Required

**Request Body:**
```json
{
  "leave_type_id": 1,
  "start_date": "2025-12-01",
  "end_date": "2025-12-03",
  "reason": "Medical checkup",
  "half_day": false
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Leave request submitted successfully",
  "leave": {
    "id": 46,
    "leave_type": "Sick Leave",
    "start_date": "2025-12-01",
    "end_date": "2025-12-03",
    "status": "pending"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Insufficient leave balance",
  "errors": {
    "leave_balance": ["You only have 2 days remaining"]
  }
}
```

---

### Get My Payslips

Retrieve employee's payslips.

**Endpoint:** `GET /api/payslip`  
**Authentication:** Required

**Query Parameters:**
```
?year=2025&page=1
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "period": "November 2025",
      "period_start": "2025-11-01",
      "period_end": "2025-11-30",
      "gross_pay": 50000.00,
      "total_deductions": 8000.00,
      "net_pay": 42000.00,
      "status": "paid",
      "pay_date": "2025-11-30"
    }
  ]
}
```

---

### View Payslip Details

View detailed breakdown of a specific payslip.

**Endpoint:** `GET /api/payslip/view/{id}`  
**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "payslip": {
    "id": 789,
    "period": "November 2025",
    "employee": {
      "name": "Juan Dela Cruz",
      "employee_id": "EMP-001",
      "position": "Software Engineer"
    },
    "earnings": {
      "basic_salary": 40000.00,
      "allowances": 8000.00,
      "overtime": 2000.00,
      "total": 50000.00
    },
    "deductions": {
      "sss": 1800.00,
      "philhealth": 1000.00,
      "pagibig": 200.00,
      "withholding_tax": 5000.00,
      "total": 8000.00
    },
    "net_pay": 42000.00
  }
}
```

---

### Get My Overtime Requests

Retrieve employee's overtime requests for the last 30 days.

**Endpoint:** `GET /api/overtime-employee`  
**Authentication:** Required

**Query Parameters:**
- `dateRange` (optional): Date range filter in format "mm/dd/yyyy - mm/dd/yyyy"
- `status` (optional): Filter by status (`pending`, `approved`, `rejected`)

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "overtimes": [
      {
        "id": 1,
        "overtime_date": "2025-11-01",
        "date_ot_in": "2025-11-01 18:00:00",
        "date_ot_out": "2025-11-01 20:00:00",
        "total_ot_minutes": 120,
        "total_night_diff_minutes": 30,
        "reason": "Project deadline",
        "status": "pending",
        "ot_login_type": "manual",
        "is_rest_day": false,
        "is_holiday": false,
        "file_attachment": "overtime_attachments/proof.pdf",
        "lastApproverName": "Juan Dela Cruz",
        "lastApproverDept": "HR"
      }
    ],
    "pendingRequests": 2,
    "approvedRequests": 5,
    "rejectedRequests": 1,
    "permission": ["Create", "Update", "Delete"]
  },
  "allData": [],
  "summary": {
    "pendingRequests": 2,
    "approvedRequests": 5,
    "rejectedRequests": 1
  }
}
```

---

### Submit Overtime (Manual Entry)

Submit a manual overtime request for a specific date.

**Endpoint:** `POST /api/overtime-employee/create/manual`  
**Authentication:** Required

**Request Body (multipart/form-data):**
```json
{
  "overtime_date": "2025-11-01",
  "date_ot_in": "2025-11-01 18:00:00",
  "date_ot_out": "2025-11-01 20:00:00",
  "total_ot_minutes": 120,
  "total_night_diff_minutes": 30,
  "offset_date": "2025-11-02",
  "reason": "Project deadline",
  "file_attachment": "[File Upload - Optional]"
}
```

**Field Descriptions:**
- `overtime_date`: **Required**. The date for the overtime request (format: YYYY-MM-DD)
- `date_ot_in`: **Required**. Overtime clock-in date and time (format: YYYY-MM-DD HH:MM:SS)
- `date_ot_out`: **Required**. Overtime clock-out date and time (must be after clock-in)
- `total_ot_minutes`: Optional. Total overtime minutes
- `total_night_diff_minutes`: Optional. Night differential minutes
- `offset_date`: Optional. Offset date for overtime (format: YYYY-MM-DD)
- `reason`: **Required**. Reason for the overtime request (max 255 characters)
- `file_attachment`: Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB)

**Validation Rules:**
- `date_ot_out` must be after `date_ot_in`
- Only one overtime request per date is allowed

**Success Response (201):**
```json
{
  "success": true,
  "message": "Overtime added successfully.",
  "data": {
    "id": 1,
    "overtime_date": "2025-11-01",
    "date_ot_in": "2025-11-01 18:00:00",
    "date_ot_out": "2025-11-01 20:00:00",
    "total_ot_minutes": 120,
    "total_night_diff_minutes": 30,
    "file_attachment": "overtime_attachments/proof.pdf",
    "offset_date": "2025-11-02",
    "reason": "Project deadline",
    "status": "pending",
    "ot_login_type": "manual"
  }
}
```

**Error Responses:**

**403 Forbidden** - No permission:
```json
{
  "status": "error",
  "message": "You do not have the permission to create."
}
```

**422 Unprocessable Entity** - Duplicate entry:
```json
{
  "success": false,
  "message": "You already have an overtime entry for this date."
}
```

---

### Update Overtime Request

Update an existing overtime request (only pending requests can be updated).

**Endpoint:** `POST /api/overtime-employee/update/manual/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the overtime request to update

**Request Body (multipart/form-data):**
```json
{
  "overtime_date": "2025-11-01",
  "date_ot_in": "2025-11-01 18:00:00",
  "date_ot_out": "2025-11-01 21:00:00",
  "total_ot_minutes": 180,
  "total_night_diff_minutes": 60,
  "offset_date": "2025-11-02",
  "reason": "Updated: Extended project deadline",
  "file_attachment": "[File Upload - Optional]"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Overtime request updated successfully."
}
```

---

### Delete Overtime Request

Delete an overtime request (only pending requests can be deleted).

**Endpoint:** `DELETE /api/overtime-employee/delete/manual/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the overtime request to delete

**Success Response (200):**
```json
{
  "success": true,
  "message": "Overtime request deleted successfully."
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Overtime request not found."
}
```

---

## Official Business Management

### List Official Business Requests

Retrieve the authenticated employee's official business (OB) requests for the current year, including summary statistics.

**Endpoint:** `GET /api/official-business/employee`  
**Authentication:** Required

**Query Parameters:**
- `dateRange` (optional): Date range filter in format "mm/dd/yyyy - mm/dd/yyyy"
- `status` (optional): Filter by status (`pending`, `approved`, `rejected`)

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "ob_date": "2025-11-01",
      "date_ob_in": "2025-11-01 08:00:00",
      "date_ob_out": "2025-11-01 17:00:00",
      "ob_break_minutes": 60,
      "total_ob_minutes": 480,
      "purpose": "Client meeting at BGC office",
      "status": "pending",
      "file_attachment": "ob_attachments/document.pdf",
      "created_at": "2025-10-30 14:00:00"
    }
  ],
  "totalApprovedOB": 2,
  "totalPendingOB": 1,
  "totalRejectedOB": 0,
  "allData": []
}
```

---

### Submit Official Business Request

Submit a new official business request for a specific date. Prevents duplicate entries for the same date.

**Endpoint:** `POST /api/official-business/employee/request`  
**Authentication:** Required

**Request Body (multipart/form-data):**
```json
{
  "ob_date": "2025-11-01",
  "date_ob_in": "2025-11-01 08:00:00",
  "date_ob_out": "2025-11-01 17:00:00",
  "ob_break_minutes": 60,
  "total_ob_minutes": 480,
  "purpose": "Client meeting at BGC office",
  "file_attachment": "[File Upload - Optional]"
}
```

**Field Descriptions:**
- `ob_date`: **Required**. The date for the official business (format: YYYY-MM-DD)
- `date_ob_in`: **Required**. Clock-in date and time (format: YYYY-MM-DD HH:MM:SS)
- `date_ob_out`: **Required**. Clock-out date and time (must be after clock-in)
- `ob_break_minutes`: Optional. Break time in minutes (default: 0)
- `total_ob_minutes`: Optional. Total OB duration in minutes
- `purpose`: **Required**. Purpose/reason for the official business (max 255 characters)
- `file_attachment`: Optional. Supporting document (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB)

**Validation Rules:**
- The `ob_date` must match the date portion of `date_ob_in`
- Only one OB request per date is allowed
- `date_ob_out` must be after or equal to `date_ob_in`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Official business request submitted successfully."
}
```

**Error Responses:**

**403 Forbidden** - No permission:
```json
{
  "status": "error",
  "message": "You do not have the permission to create."
}
```

**422 Unprocessable Entity** - Duplicate entry:
```json
{
  "success": false,
  "message": "You already have an official business entry for this date."
}
```

**422 Unprocessable Entity** - Date mismatch:
```json
{
  "success": false,
  "message": "The OB date must be the same as the date of your official business start time."
}
```

**500 Internal Server Error**:
```json
{
  "success": false,
  "message": "An unexpected error occurred. Please try again later."
}
```

---

### Update Official Business Request

Update an existing official business request (only pending requests can be updated).

**Endpoint:** `POST /api/official-business/employee/update/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the official business request to update

**Request Body (multipart/form-data):**
```json
{
  "ob_date": "2025-11-01",
  "date_ob_in": "2025-11-01 09:00:00",
  "date_ob_out": "2025-11-01 18:00:00",
  "ob_break_minutes": 60,
  "total_ob_minutes": 480,
  "purpose": "Updated: Client meeting extended",
  "file_attachment": "[File Upload - Optional]"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Official business request updated successfully."
}
```

---

### Delete Official Business Request

Delete an official business request (only pending requests can be deleted).

**Endpoint:** `DELETE /api/official-business/employee/delete/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the official business request to delete

**Success Response (200):**
```json
{
  "success": true,
  "message": "Official business request deleted successfully."
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Official business request not found."
}
```

---

## Request Attendance

### List Attendance Requests

Retrieve the authenticated employee's attendance requests for the last 30 days, including summary statistics.

**Endpoint:** `GET /api/attendance-employee/request-attendance`  
**Authentication:** Required

**Query Parameters:**
- `dateRange` (optional): Date range filter in format "mm/dd/yyyy - mm/dd/yyyy"
- `status` (optional): Filter by status (`pending`, `approved`, `rejected`)

**Success Response (200):**
```json
{
  "status": true,
  "message": "Request Attendance Employee Index",
  "data": [
    {
      "id": 1,
      "request_date": "2025-11-01",
      "request_date_in": "2025-11-01 08:00:00",
      "request_date_out": "2025-11-01 17:00:00",
      "total_break_minutes": 60,
      "total_request_minutes": 480,
      "total_request_nd_minutes": 60,
      "reason": "Forgot to clock in due to system error",
      "status": "pending",
      "file_attachment": "attendance_attachments/proof.pdf",
      "lastApproverName": "Maria Santos",
      "lastApproverDept": "Human Resources",
      "created_at": "2025-11-01 18:00:00"
    }
  ]
}
```

---

### Submit Attendance Request

Submit a new attendance request for a specific date. Useful when employees forget to clock in/out or need to request attendance adjustments.

**Endpoint:** `POST /api/attendance-employee/request`  
**Authentication:** Required

**Request Body (multipart/form-data):**
```json
{
  "request_date": "2025-11-01",
  "request_date_in": "2025-11-01 08:00:00",
  "request_date_out": "2025-11-01 17:00:00",
  "total_break_minutes": 60,
  "total_request_minutes": 480,
  "total_request_nd_minutes": 60,
  "reason": "Forgot to clock in due to system error",
  "file_attachment": "[File Upload - Optional]"
}
```

**Field Descriptions:**
- `request_date`: **Required**. Date for the attendance request (cannot be future date)
- `request_date_in`: **Required**. Clock-in date and time
- `request_date_out`: **Required**. Clock-out date and time (must be after clock-in)
- `total_break_minutes`: Optional. Total break time in minutes (default: 0)
- `total_request_minutes`: **Required**. Total work minutes for this request
- `total_request_nd_minutes`: Optional. Night differential minutes (default: 0)
- `reason`: Optional. Reason for the attendance request (max 255 characters)
- `file_attachment`: Optional. Supporting document (max 2MB)

**Validation Rules:**
- `request_date` cannot be a future date
- `request_date_out` must be after `request_date_in`
- All minute values must be non-negative integers

**Success Response (201):**
```json
{
  "success": true,
  "message": "Attendance request submitted successfully.",
  "data": {
    "id": 1,
    "request_date": "2025-11-01",
    "request_date_in": "2025-11-01 08:00:00",
    "request_date_out": "2025-11-01 17:00:00",
    "total_break_minutes": 60,
    "total_request_minutes": 480,
    "total_request_nd_minutes": 60,
    "reason": "Forgot to clock in due to system error",
    "file_attachment": "attendance_attachments/proof.pdf",
    "status": "pending"
  }
}
```

**Error Responses:**

**403 Forbidden** - Global admin restriction:
```json
{
  "success": false,
  "message": "Global administrators are not authorized to submit attendance requests."
}
```

**401 Unauthorized** - Invalid session:
```json
{
  "success": false,
  "message": "Invalid user session. Please log in again."
}
```

**422 Unprocessable Entity** - Future date:
```json
{
  "success": false,
  "message": "Sorry, you cannot request attendance for future dates. Please select today's date or any previous date."
}
```

**422 Unprocessable Entity** - Validation errors:
```json
{
  "success": false,
  "message": "Please select a date for your attendance request.",
  "errors": {
    "request_date": ["Please select a date for your attendance request."],
    "request_date_out": ["Clock-out time must be after clock-in time."]
  }
}
```

---

### Edit Attendance Request

Update an existing attendance request. Only pending requests can be edited.

**Endpoint:** `POST /api/attendance-employee/request/edit/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the attendance request to edit

**Request Body (multipart/form-data):**
```json
{
  "request_date": "2025-11-01",
  "request_date_in": "2025-11-01 08:30:00",
  "request_date_out": "2025-11-01 17:30:00",
  "total_break_minutes": 60,
  "total_request_minutes": 480,
  "total_request_nd_minutes": 60,
  "reason": "Updated: Corrected clock-in time",
  "file_attachment": "[File Upload - Optional]"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance request updated successfully.",
  "data": {
    "id": 1,
    "request_date": "2025-11-01",
    "request_date_in": "2025-11-01 08:30:00",
    "request_date_out": "2025-11-01 17:30:00",
    "status": "pending"
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Cannot edit an approved or rejected attendance request."
}
```

---

### Delete Attendance Request

Delete an attendance request. Only pending requests can be deleted.

**Endpoint:** `DELETE /api/attendance-employee/request/delete/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the attendance request to delete

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance request deleted successfully."
}
```

**Error Responses:**

**404 Not Found**:
```json
{
  "success": false,
  "message": "Attendance request not found."
}
```

**403 Forbidden**:
```json
{
  "success": false,
  "message": "Cannot delete an approved or rejected attendance request."
}
```

---

## Leave Management

### Get My Leave Types and Requests

Retrieve employee's available leave types with current balances and leave requests for the current year.

**Endpoint:** `GET /api/leave/leave-employee`  
**Authentication:** Required

**Query Parameters:**
- `dateRange` (optional): Date range filter in format "mm/dd/yyyy - mm/dd/yyyy"
- `status` (optional): Filter by status (`pending`, `approved`, `rejected`)
- `leavetype` (optional): Filter by leave type ID

**Success Response (200):**
```json
{
  "message": "Available leave types fetched.",
  "status": "success",
  "leaveTypes": {
    "1": {
      "id": 1,
      "name": "Vacation Leave",
      "current_balance": 5
    },
    "2": {
      "id": 2,
      "name": "Sick Leave",
      "current_balance": 2
    }
  },
  "leaveRequests": [
    {
      "id": 10,
      "leave_type_id": 1,
      "start_date": "2025-06-01",
      "end_date": "2025-06-03",
      "days_requested": 3,
      "half_day_type": null,
      "status": "pending",
      "reason": "Family vacation",
      "file_attachment": "leave_requests/10/document.pdf",
      "lastApproverName": "Maria Santos",
      "lastApproverDept": "HR"
    }
  ]
}
```

---

### Submit Leave Request

Submit a new leave request for a specific date range.

**Endpoint:** `POST /api/leave/leave-request`  
**Authentication:** Required

**Request Body (multipart/form-data):**
```json
{
  "leave_type_id": 1,
  "start_date": "2025-12-01",
  "end_date": "2025-12-03",
  "half_day_type": "AM",
  "reason": "Medical checkup",
  "file_attachment": "[File Upload - Optional]"
}
```

**Field Descriptions:**
- `leave_type_id`: **Required**. The ID of the leave type
- `start_date`: **Required**. Start date of the leave (format: YYYY-MM-DD)
- `end_date`: **Required**. End date of the leave (format: YYYY-MM-DD)
- `half_day_type`: Optional. Half-day selection ("AM" or "PM") if allowed by leave type
- `reason`: Optional. Reason for the leave request (max 500 characters)
- `file_attachment`: Optional. Supporting document (PDF, JPG, JPEG, PNG, max 2MB)

**Validation Rules:**
- Leave type must exist and be active
- Must have sufficient leave balance
- Documents may be required for certain leave types
- Half-day option only available if enabled for the leave type

**Success Response (201):**
```json
{
  "message": "Your leave request was sent. We'll notify your approver.",
  "leave_request": {
    "id": 123,
    "leave_type_id": 1,
    "start_date": "2025-12-01",
    "end_date": "2025-12-03",
    "days_requested": 3,
    "half_day_type": null,
    "file_attachment": "leave_requests/123/attachment.pdf",
    "reason": "Medical checkup",
    "status": "pending"
  }
}
```

**Error Responses:**

**403 Forbidden** - No permission:
```json
{
  "status": "error",
  "message": "Sorry, you can't file a leave right now. Your account doesn't have permission to do this."
}
```

**404 Not Found** - Invalid leave type:
```json
{
  "message": "Sorry, we couldn't find that leave type. Please choose a valid option."
}
```

**422 Unprocessable Entity** - Insufficient balance:
```json
{
  "message": "You don't have enough leave credits for the dates you chose."
}
```

**422 Unprocessable Entity** - Document required:
```json
{
  "message": "This leave type requires you to upload a supporting document."
}
```

---

### Update Leave Request

Update an existing leave request (only pending requests can be updated).

**Endpoint:** `POST /api/leave/leave-request/edit/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the leave request to update

**Request Body (multipart/form-data):**
```json
{
  "leave_type_id": 1,
  "start_date": "2025-12-01",
  "end_date": "2025-12-05",
  "half_day_type": null,
  "reason": "Updated: Extended medical leave",
  "file_attachment": "[File Upload - Optional]"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Leave request updated successfully."
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Cannot edit an approved or rejected leave request."
}
```

---

### Delete Leave Request

Delete a leave request (only pending requests can be deleted).

**Endpoint:** `DELETE /api/leave/leave-request/delete/{id}`  
**Authentication:** Required

**URL Parameters:**
- `id`: The ID of the leave request to delete

**Success Response (200):**
```json
{
  "success": true,
  "message": "Leave request deleted successfully."
}
```

**Error Responses:**

**404 Not Found**:
```json
{
  "success": false,
  "message": "Leave request not found."
}
```

**403 Forbidden**:
```json
{
  "success": false,
  "message": "Cannot delete an approved or rejected leave request."
}
```

---

## Error Handling

All API endpoints follow a consistent error response format.

### Common Error Responses

#### 400 Bad Request
```json
{
  "success": false,
  "message": "Bad request"
}
```

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

#### 403 Forbidden
```json
{
  "success": false,
  "message": "You don't have permission to perform this action"
}
```

#### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

#### 422 Unprocessable Entity (Validation Error)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

#### 429 Too Many Requests
```json
{
  "message": "Too many attempts. Please try again later."
}
```

#### 500 Internal Server Error
```json
{
  "success": false,
  "message": "An error occurred. Please try again later."
}
```

---

## Rate Limiting

- **Login endpoint:** Limited to prevent brute force attacks (5 attempts per minute)
- **Other endpoints:** Standard rate limiting applies (60 requests per minute per user)
- Rate limit headers are included in responses:
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests
  - `Retry-After`: Seconds until limit resets (when exceeded)

---

## Data Formats

### Dates
- Format: `YYYY-MM-DD`
- Example: `2025-11-29`

### Times
- Format: `HH:MM:SS` (24-hour)
- Example: `14:30:00`

### Timestamps
- Format: `YYYY-MM-DD HH:MM:SS`
- Example: `2025-11-29 14:30:00`

### Currency
- All amounts in Philippine Peso (PHP)
- Format: Decimal with 2 places
- Example: `50000.00`

---

## Best Practices

1. **Always use HTTPS** in production
2. **Store tokens securely** - Never commit tokens to version control
3. **Implement token refresh** mechanism for long-lived sessions
4. **Handle errors gracefully** - Check response status codes
5. **Respect rate limits** - Implement exponential backoff
6. **Validate input** on client side before sending to API
7. **Use pagination** for list endpoints to improve performance
8. **Upload files using multipart/form-data** format when required

---

## Support

For technical support or API access requests:
- **Email:** support@timora.com
- **Documentation:** https://docs.timora.com
- **Status Page:** https://status.timora.com

---

## Changelog

### Version 1.0 (November 2025)
- Initial public API release
- Authentication endpoints
- Employee self-service endpoints (attendance, clock in/out, breaks)
- Leave management endpoints
- Overtime management endpoints
- Official business management endpoints
- Attendance request endpoints

---

**© 2025 Timora HRIS. All rights reserved.**
