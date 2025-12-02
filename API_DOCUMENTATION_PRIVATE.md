# Timora HRIS Private API Documentation (Admin)

**Version:** 1.0  
**Last Updated:** November 29, 2025  
**Audience:** Internal Developers, System Administrators

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Base URL](#base-url)
4. [Admin Attendance Management](#admin-attendance-management)
5. [Admin Overtime Management](#admin-overtime-management)
6. [Admin Leave Management](#admin-leave-management)
7. [Admin Official Business Management](#admin-official-business-management)
8. [Admin Attendance Request Management](#admin-attendance-request-management)
9. [Employee Management](#employee-management)
10. [Employee Details Management](#employee-details-management)
11. [Salary Management](#salary-management)
12. [Shift Management](#shift-management)
13. [Payroll Management](#payroll-management)
14. [13th Month Pay Management](#13th-month-pay-management)
15. [Bulk Operations](#bulk-operations)
16. [Export & Import](#export--import)
17. [Error Handling](#error-handling)
18. [Support](#support)

---

## Overview

This documentation covers **administrative endpoints** for the Timora HRIS API. These endpoints are restricted to users with admin permissions and are used for managing employees, attendance, payroll, leaves, overtime, and other HR operations.

**Key Features:**
- Full CRUD operations for employees, attendance, leave, overtime, and OB
- Advanced filtering and search capabilities
- Bulk approval/rejection workflows
- Payroll processing and generation
- Excel/PDF export functionality
- Import employees and attendance via CSV
- Data access control based on branch/department/designation

---

## Authentication

All admin endpoints require:
- **Bearer Token** authentication
- **Admin-level permissions** for the specific module
- Active subscription (non-expired)

**Headers:**
```
Authorization: Bearer {your_admin_token}
Content-Type: application/json
Accept: application/json
```

---

## Base URL

```
Production: https://api.timora.com/api
Staging: https://staging-api.timora.com/api
```

---

## Admin Attendance Management

### List All Attendance Records (Admin)

Retrieve all attendance records with advanced filtering options.

**Endpoint:** `GET /api/attendance-admin`  
**Authentication:** Required (Admin)  
**Permission:** Attendance Admin (Module ID: 14)

**Query Parameters:**
```
?dateRange=11/01/2025 - 11/30/2025
&branch=1
&department=2
&designation=3
&status=present
&page=1
```

**Query Parameters:**
- `dateRange` (optional): Date range in format "mm/dd/yyyy - mm/dd/yyyy"
- `branch` (optional): Filter by branch ID
- `department` (optional): Filter by department ID
- `designation` (optional): Filter by designation ID
- `status` (optional): Filter by status (`present`, `late`, `absent`)
- `page` (optional): Page number for pagination

**Success Response (200):**
```json
{
  "success": true,
  "allData": [
    {
      "id": 123,
      "user_id": 10,
      "shift_id": 1,
      "shift_assignment_id": 5,
      "geofence_id": 2,
      "holiday_id": null,
      "attendance_date": "2025-11-29",
      "date_time_in": "2025-11-29 08:00:00",
      "date_time_out": "2025-11-29 17:00:00",
      "multiple_login": [
        {
          "in": "2025-11-29 08:00:00",
          "out": "2025-11-29 12:00:00"
        },
        {
          "in": "2025-11-29 13:00:00",
          "out": "2025-11-29 17:00:00"
        }
      ],
      "multiple_logout": null,
      "break_in": "2025-11-29 12:00:00",
      "break_out": "2025-11-29 13:00:00",
      "break_late": 0,
      "status": "present",
      "time_in_latitude": 14.5995,
      "time_in_longitude": 120.9842,
      "time_in_address": "Makati City, Metro Manila",
      "time_out_latitude": 14.5995,
      "time_out_longitude": 120.9842,
      "time_out_address": "Makati City, Metro Manila",
      "within_geofence": true,
      "time_in_photo_path": "attendance/photos/123_in.jpg",
      "time_out_photo_path": "attendance/photos/123_out.jpg",
      "clock_in_method": "Timora Mobile App",
      "clock_out_method": "Timora Mobile App",
      "is_rest_day": false,
      "is_holiday": false,
      "total_work_minutes": 480,
      "total_late_minutes": 0,
      "late_status_box": null,
      "total_night_diff_minutes": 0,
      "total_undertime_minutes": 0,
      "user": {
        "id": 10,
        "username": "juan.delacruz",
        "email": "juan@company.com",
        "personal_information": {
          "first_name": "Juan",
          "last_name": "Dela Cruz",
          "profile_picture": "profile_images/juan.jpg"
        },
        "employment_detail": {
          "employee_id": "EMP-001",
          "branch_id": 1,
          "department_id": 2,
          "designation_id": 3,
          "branch": {
            "id": 1,
            "branch_name": "Main Office"
          },
          "department": {
            "id": 2,
            "department_name": "IT Department"
          },
          "designation": {
            "id": 3,
            "designation_name": "Software Engineer"
          }
        }
      },
      "shift": {
        "id": 1,
        "shift_name": "Morning Shift",
        "shift_start": "08:00:00",
        "shift_end": "17:00:00",
        "break_start": "12:00:00",
        "break_end": "13:00:00",
        "branch": {
          "id": 1,
          "branch_name": "Main Office"
        }
      },
      "created_at": "2025-11-29 08:00:00",
      "updated_at": "2025-11-29 17:00:00"
    }
  ],
  "totalPresent": 450,
  "totalLate": 30,
  "totalAbsent": 20
}
```

---

### Create Attendance Record (Admin)

Manually create an attendance record for an employee.

**Endpoint:** `POST /api/attendance-admin/create`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "user_ids": [10, 11, 12],
  "attendance_date": "2025-11-29",
  "date_time_in": "2025-11-29 08:00:00",
  "date_time_out": "2025-11-29 17:00:00",
  "break_in": "2025-11-29 12:00:00",
  "break_out": "2025-11-29 13:00:00",
  "shift_id": 1,
  "total_work_minutes": 480,
  "total_late_minutes": 0,
  "total_night_diff_minutes": 0,
  "total_undertime_minutes": 0,
  "status": "present",
  "is_rest_day": false,
  "is_holiday": false,
  "clock_in_method": "Admin Manual Entry",
  "clock_out_method": "Admin Manual Entry"
}
```

**Field Descriptions:**
- `user_ids`: **Required**. Array of employee user IDs. Use `["all"]` to select all employees
- `attendance_date`: **Required**. Date of attendance (YYYY-MM-DD)
- `date_time_in`: **Required**. Clock-in date and time (YYYY-MM-DD HH:MM:SS)
- `date_time_out`: Optional. Clock-out date and time (YYYY-MM-DD HH:MM:SS)
- `break_in`: Optional. Break start time (YYYY-MM-DD HH:MM:SS)
- `break_out`: Optional. Break end time (YYYY-MM-DD HH:MM:SS)
- `shift_id`: **Required**. Shift ID for the employee
- `total_work_minutes`: Optional. Total work minutes (calculated automatically if not provided)
- `total_late_minutes`: Optional. Late minutes
- `total_night_diff_minutes`: Optional. Night differential minutes
- `total_undertime_minutes`: Optional. Undertime minutes
- `status`: Optional. Status (`present`, `late`, `absent`, `edited`)
- `is_rest_day`: Optional. Boolean, is this a rest day attendance
- `is_holiday`: Optional. Boolean, is this a holiday attendance
- `clock_in_method`: Optional. Method used for clock-in
- `clock_out_method`: Optional. Method used for clock-out

**Success Response (201):**
```json
{
  "success": true,
  "message": "Attendance records created successfully.",
  "created_count": 3,
  "skipped_count": 0,
  "data": [
    {
      "id": 124,
      "user_id": 10,
      "attendance_date": "2025-11-29",
      "date_time_in": "2025-11-29 08:00:00",
      "date_time_out": "2025-11-29 17:00:00",
      "total_work_minutes": 480,
      "status": "present",
      "shift_id": 1
    },
    {
      "id": 125,
      "user_id": 11,
      "attendance_date": "2025-11-29",
      "date_time_in": "2025-11-29 08:00:00",
      "date_time_out": "2025-11-29 17:00:00",
      "total_work_minutes": 480,
      "status": "present",
      "shift_id": 1
    }
  ]
}
```

---

### Update Attendance Record (Admin)

Update an existing attendance record.

**Endpoint:** `PUT /api/attendance-admin/update/{id}`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `id`: The ID of the attendance record to update

**Request Body:**
```json
{
  "date_time_in": "2025-11-29 08:00:00",
  "date_time_out": "2025-11-29 17:00:00",
  "break_in": "2025-11-29 12:00:00",
  "break_out": "2025-11-29 13:00:00",
  "total_work_minutes": 480,
  "total_late_minutes": 0,
  "total_night_diff_minutes": 0,
  "total_undertime_minutes": 0,
  "status": "edited",
  "is_rest_day": false,
  "is_holiday": false,
  "clock_in_method": "Admin Updated",
  "clock_out_method": "Admin Updated"
}
```

**Field Descriptions:**
- `date_time_in`: Optional. Clock-in date and time (YYYY-MM-DD HH:MM:SS)
- `date_time_out`: Optional. Clock-out date and time (YYYY-MM-DD HH:MM:SS)
- `break_in`: Optional. Break start time (YYYY-MM-DD HH:MM:SS)
- `break_out`: Optional. Break end time (YYYY-MM-DD HH:MM:SS)
- `total_work_minutes`: Optional. Total work minutes
- `total_late_minutes`: Optional. Late minutes
- `total_night_diff_minutes`: Optional. Night differential minutes
- `total_undertime_minutes`: Optional. Undertime minutes
- `status`: Optional. Status is automatically set to `edited` when updated by admin
- `is_rest_day`: Optional. Boolean, is this a rest day attendance
- `is_holiday`: Optional. Boolean, is this a holiday attendance
- `clock_in_method`: Optional. Method used for clock-in
- `clock_out_method`: Optional. Method used for clock-out

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance updated successfully.",
  "data": {
    "id": 123,
    "user_id": 10,
    "attendance_date": "2025-11-29",
    "date_time_in": "2025-11-29 08:00:00",
    "date_time_out": "2025-11-29 17:00:00",
    "status": "edited",
    "total_work_minutes": 480
  }
}
```

**Error Responses:**

**403 Forbidden - No permission:**
```json
{
  "success": false,
  "message": "You do not have the permission to update."
}
```

**404 Not Found:**
```json
{
  "success": false,
  "message": "The attendance record you are trying to update was not found."
}
```

---

### Delete Attendance Record (Admin)

Delete an attendance record.

**Endpoint:** `DELETE /api/attendance-admin/delete/{id}`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `id`: The ID of the attendance record to delete

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance record deleted successfully."
}
```

---

### Bulk Attendance Operations

Perform bulk actions on multiple attendance records.

**Endpoint:** `POST /api/attendance-admin/bulk-attendance/bulk-action`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "action": "delete",
  "attendance_ids": [123, 124, 125]
}
```

**Field Descriptions:**
- `action`: **Required**. Action to perform (`delete`)
- `attendance_ids`: **Required**. Array of attendance record IDs

**Success Response (200):**
```json
{
  "success": true,
  "message": "Bulk action completed successfully.",
  "deleted_count": 3
}
```

---

### Export Attendance Records

Export attendance records to Excel or PDF.

**Endpoint:** `GET /api/attendance-admin/export`  
**Authentication:** Required (Admin)

**Query Parameters:**
```
?format=excel
&dateRange=11/01/2025 - 11/30/2025
&branch=1
&department=2
```

**Query Parameters:**
- `format`: **Required**. Export format (`excel` or `pdf`)
- `dateRange`: Optional. Date range filter
- `branch`: Optional. Branch filter
- `department`: Optional. Department filter

**Success Response (200):**
Returns a downloadable file (Excel or PDF)

---

## Admin Overtime Management

### List All Overtime Requests (Admin)

Retrieve all overtime requests with filtering and summary statistics.

**Endpoint:** `GET /api/overtime`  
**Authentication:** Required (Admin)  
**Permission:** Overtime Admin (Module ID: 17)

**Query Parameters:**
```
?dateRange=11/01/2025 - 11/30/2025
&branch=1
&department=2
&designation=3
&status=pending
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "overtimes": [
      {
        "id": 1,
        "user_id": 10,
        "overtime_date": "2025-11-01",
        "date_ot_in": "2025-11-01 18:00:00",
        "date_ot_out": "2025-11-01 20:00:00",
        "total_ot_minutes": 120,
        "total_night_diff_minutes": 30,
        "reason": "Project deadline",
        "status": "pending",
        "ot_login_type": "manual",
        "file_attachment": "overtime_attachments/proof.pdf",
        "employee": {
          "id": 10,
          "full_name": "Juan Dela Cruz",
          "employee_id": "EMP-001",
          "branch": "Main Office",
          "department": "IT"
        },
        "current_step": 1,
        "total_steps": 2,
        "lastApproverName": "Maria Santos",
        "lastApproverDept": "HR",
        "nextApprovers": ["Pedro Reyes"],
        "created_at": "2025-11-01 20:05:00"
      }
    ],
    "summary": {
      "pendingRequests": 5,
      "approvedRequests": 20,
      "rejectedRequests": 2,
      "totalRequests": 27
    }
  }
}
```

---

### Approve Overtime Request

Approve an overtime request (supports multi-level approval).

**Endpoint:** `POST /api/overtime/{overtime}/approve`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `overtime`: The ID of the overtime request

**Request Body:**
```json
{
  "comment": "Approved due to project urgency"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Overtime request approved successfully.",
  "is_final": true,
  "approval_step": 2
}
```

---

### Reject Overtime Request

Reject an overtime request.

**Endpoint:** `POST /api/overtime/{overtime}/reject`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `overtime`: The ID of the overtime request

**Request Body:**
```json
{
  "comment": "Insufficient justification provided"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Overtime request rejected successfully."
}
```

---

### Update Overtime Request (Admin)

Update an existing overtime request.

**Endpoint:** `POST /api/overtime/update/{id}`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `id`: The ID of the overtime request

**Request Body:**
```json
{
  "overtime_date": "2025-11-01",
  "date_ot_in": "2025-11-01 18:00:00",
  "date_ot_out": "2025-11-01 21:00:00",
  "total_ot_minutes": 180,
  "reason": "Updated reason",
  "status": "approved"
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

### Delete Overtime Request (Admin)

Delete an overtime request.

**Endpoint:** `DELETE /api/overtime/delete/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Overtime request deleted successfully."
}
```

---

### Bulk Overtime Actions

Perform bulk approval or rejection of overtime requests.

**Endpoint:** `POST /api/overtime/bulk-action`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "action": "approve",
  "overtime_ids": [1, 2, 3],
  "comment": "Bulk approved for month-end processing"
}
```

**Field Descriptions:**
- `action`: **Required**. Action to perform (`approve` or `reject`)
- `overtime_ids`: **Required**. Array of overtime request IDs
- `comment`: Optional. Comment for the bulk action

**Success Response (200):**
```json
{
  "success": true,
  "message": "Bulk action completed successfully.",
  "approved_count": 3,
  "failed": []
}
```

---

## Admin Leave Management

### List All Leave Requests (Admin)

Retrieve all leave requests for the current year with filtering options.

**Endpoint:** `GET /api/leave/leave-admin`  
**Authentication:** Required (Admin)  
**Permission:** Leave Admin (Module ID: 19)

**Query Parameters:**
```
?dateRange=01/01/2025 - 12/31/2025
&status=pending
&leavetype=1
```

**Success Response (200):**
```json
{
  "message": "This is the leave admin index endpoint.",
  "status": "success",
  "leaveRequests": [
    {
      "id": 10,
      "user_id": 5,
      "leave_type_id": 1,
      "leave_type_name": "Vacation Leave",
      "start_date": "2025-06-01",
      "end_date": "2025-06-03",
      "days_requested": 3,
      "half_day_type": null,
      "status": "pending",
      "reason": "Family vacation",
      "file_attachment": "leave_requests/10/document.pdf",
      "employee": {
        "id": 5,
        "full_name": "Maria Santos",
        "employee_id": "EMP-005",
        "branch": "Main Office",
        "department": "HR"
      },
      "remaining_balance": 5,
      "current_step": 1,
      "total_steps": 2,
      "lastApproverName": "Juan Dela Cruz",
      "lastApproverDept": "Management",
      "nextApprovers": ["Pedro Reyes"],
      "created_at": "2025-05-25 10:00:00"
    }
  ],
  "leaveTypes": [
    {
      "id": 1,
      "name": "Vacation Leave",
      "total_allocated": 10
    },
    {
      "id": 2,
      "name": "Sick Leave",
      "total_allocated": 5
    }
  ],
  "approvedLeavesCount": 15,
  "rejectedLeavesCount": 2,
  "pendingLeavesCount": 8
}
```

---

### Approve Leave Request

Approve a leave request (supports multi-level approval).

**Endpoint:** `POST /api/leave/leave-request/{leave}/approve`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `leave`: The ID of the leave request

**Request Body:**
```json
{
  "comment": "Approved. Have a safe trip!"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Leave request approved successfully.",
  "is_final": true,
  "approval_step": 2
}
```

---

### Reject Leave Request

Reject a leave request and refund the leave balance.

**Endpoint:** `POST /api/leave/leave-request/{leave}/reject`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `leave`: The ID of the leave request

**Request Body:**
```json
{
  "comment": "Insufficient staffing during this period"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Leave request rejected successfully.",
  "balance_refunded": 3
}
```

---

### Update Leave Request (Admin)

Update an existing leave request.

**Endpoint:** `POST /api/leave/leave-admin/update/{id}`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `id`: The ID of the leave request

**Request Body:**
```json
{
  "leave_type_id": 1,
  "start_date": "2025-06-01",
  "end_date": "2025-06-05",
  "reason": "Updated reason",
  "status": "approved"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Leave request updated successfully."
}
```

---

### Delete Leave Request (Admin)

Delete a leave request and refund balance if applicable.

**Endpoint:** `DELETE /api/leave/leave-admin/delete/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Leave request deleted successfully.",
  "balance_refunded": 3
}
```

---

### Bulk Leave Actions

Perform bulk approval or rejection of leave requests.

**Endpoint:** `POST /api/leave/bulk-action`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "action": "approve",
  "leave_ids": [10, 11, 12],
  "comment": "Bulk approved for department shutdown"
}
```

**Field Descriptions:**
- `action`: **Required**. Action to perform (`approve` or `reject`)
- `leave_ids`: **Required**. Array of leave request IDs
- `comment`: Optional. Comment for the bulk action

**Success Response (200):**
```json
{
  "success": true,
  "message": "Bulk action completed successfully.",
  "approved_count": 3,
  "failed": []
}
```

---

## Admin Official Business Management

### List All Official Business Requests (Admin)

Retrieve all official business (OB) requests with filtering options.

**Endpoint:** `GET /api/official-business/admin`  
**Authentication:** Required (Admin)  
**Permission:** OB Admin (Module ID: 47)

**Query Parameters:**
```
?dateRange=01/01/2025 - 12/31/2025
&branch=1
&department=2
&designation=3
&status=pending
```

**Success Response (200):**
```json
{
  "message": "Admin Official Business Index",
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "ob_date": "2025-11-01",
      "date_ob_in": "2025-11-01 08:00:00",
      "date_ob_out": "2025-11-01 17:00:00",
      "ob_break_minutes": 60,
      "total_ob_minutes": 480,
      "purpose": "Client meeting at BGC office",
      "status": "pending",
      "file_attachment": "ob_attachments/document.pdf",
      "employee": {
        "id": 10,
        "full_name": "Juan Dela Cruz",
        "employee_id": "EMP-001",
        "branch": "Main Office",
        "department": "Sales"
      },
      "current_step": 1,
      "total_steps": 2,
      "lastApproverName": "Maria Santos",
      "lastApproverDept": "HR",
      "nextApprovers": ["Pedro Reyes"],
      "created_at": "2025-10-30 14:00:00"
    }
  ],
  "counts": {
    "pending": 5,
    "approved": 20,
    "rejected": 1,
    "total": 26
  }
}
```

---

### Approve Official Business Request

Approve an OB request and automatically create/update attendance record.

**Endpoint:** `POST /api/official-business/admin/{ob}/approve`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `ob`: The ID of the OB request

**Request Body:**
```json
{
  "comment": "Approved for client meeting"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Official business request approved successfully.",
  "is_final": true,
  "attendance_created": true
}
```

---

### Reject Official Business Request

Reject an OB request.

**Endpoint:** `POST /api/official-business/admin/{ob}/reject`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `ob`: The ID of the OB request

**Request Body:**
```json
{
  "comment": "Please provide more details"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Official business request rejected successfully."
}
```

---

### Update Official Business Request (Admin)

Update an existing OB request.

**Endpoint:** `POST /api/official-business/admin/update/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Official business request updated successfully."
}
```

---

### Delete Official Business Request (Admin)

Delete an OB request.

**Endpoint:** `DELETE /api/official-business/admin/delete/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Official business request deleted successfully."
}
```

---

### Bulk Official Business Actions

Perform bulk approval or rejection of OB requests.

**Endpoint:** `POST /api/official-business/admin/bulk-action`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "action": "approve",
  "ob_ids": [1, 2, 3],
  "comment": "Bulk approved"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Bulk action completed successfully.",
  "approved_count": 3,
  "failed": []
}
```

---

## Admin Attendance Request Management

### List All Attendance Requests (Admin)

Retrieve all employee attendance requests with filtering.

**Endpoint:** `GET /api/attendance-admin/request-attendance`  
**Authentication:** Required (Admin)  
**Permission:** Attendance Admin (Module ID: 14)

**Query Parameters:**
```
?dateRange=11/01/2025 - 11/30/2025
&branch=1
&department=2
&designation=3
&status=pending
```

**Success Response (200):**
```json
{
  "status": true,
  "userAttendance": [
    {
      "id": 1,
      "user_id": 10,
      "request_date": "2025-11-01",
      "request_date_in": "2025-11-01 08:00:00",
      "request_date_out": "2025-11-01 17:00:00",
      "total_break_minutes": 60,
      "total_request_minutes": 480,
      "total_request_nd_minutes": 60,
      "reason": "Forgot to clock in due to system error",
      "status": "pending",
      "file_attachment": "attendance_attachments/proof.pdf",
      "employee": {
        "id": 10,
        "full_name": "Juan Dela Cruz",
        "employee_id": "EMP-001",
        "branch": "Main Office",
        "department": "IT"
      },
      "current_step": 1,
      "total_steps": 2,
      "lastApproverName": "Maria Santos",
      "lastApproverDept": "HR",
      "nextApprovers": ["Pedro Reyes"],
      "created_at": "2025-11-01 18:00:00"
    }
  ],
  "summary": {
    "total_present": 50,
    "total_late": 5,
    "total_absent": 2
  }
}
```

---

### Approve Attendance Request

Approve an attendance request and create/update attendance record.

**Endpoint:** `POST /api/attendance-admin/request-attendance/{req}/approve`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `req`: The ID of the attendance request

**Request Body:**
```json
{
  "comment": "Approved. System issue verified."
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance request approved successfully.",
  "is_final": true,
  "attendance_created": true
}
```

---

### Reject Attendance Request

Reject an attendance request.

**Endpoint:** `POST /api/attendance-admin/request-attendance/{req}/reject`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `req`: The ID of the attendance request

**Request Body:**
```json
{
  "comment": "Insufficient documentation provided"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance request rejected successfully."
}
```

---

### Bulk Attendance Request Actions

Perform bulk approval or rejection of attendance requests.

**Endpoint:** `POST /api/attendance-admin/request-attendance/bulk-action`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "action": "approve",
  "attendance_request_ids": [1, 2, 3],
  "comment": "Bulk approved"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Bulk action completed successfully.",
  "approved_count": 3,
  "failed": []
}
```

---

## Employee Management

### List All Employees

Retrieve a paginated list of all employees with filtering options.

**Endpoint:** `GET /api/employees`  
**Authentication:** Required (Admin)  
**Permission:** Employee Admin (Module ID: 9)

**Query Parameters:**
```
?branch_id=1
&department_id=2
&designation_id=3
&status=1
&sort=desc
&page=1
```

**Query Parameters:**
- `branch_id` (optional): Filter by branch ID
- `department_id` (optional): Filter by department ID
- `designation_id` (optional): Filter by designation ID
- `status` (optional): Filter by status (0=Inactive, 1=Active)
- `sort` (optional): Sort order (`asc`, `desc`, `last_month`, `last_7_days`)
- `page` (optional): Page number

**Success Response (200):**
```json
{
  "employees": [
    {
      "user": {
        "id": 1,
        "username": "juan.delacruz",
        "email": "juan@company.com",
        "role": "Employee",
        "tenant_id": 1
      },
      "employment_detail": {
        "id": 1,
        "employee_id": "EMP-0001",
        "date_hired": "2024-01-15",
        "employment_type": "Regular",
        "employment_status": "Active",
        "branch_id": 1,
        "branch_name": "Main Office",
        "department_id": 2,
        "department_name": "IT Department",
        "designation_id": 3,
        "designation_name": "Software Engineer",
        "status": 1
      },
      "personal_information": {
        "first_name": "Juan",
        "last_name": "Dela Cruz",
        "middle_name": "Santos",
        "suffix": "Jr.",
        "phone_number": "09171234567",
        "profile_picture": "profile_images/1234567890_photo.jpg",
        "date_of_birth": "1990-05-15",
        "gender": "Male"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 150,
    "per_page": 50,
    "last_page": 3
  }
}
```

---

### Create Employee

Add a new employee to the system.

**Endpoint:** `POST /api/employees`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "username": "maria.santos",
  "email": "maria@company.com",
  "password": "SecurePass123!",
  "employee_id": "EMP-0002",
  "first_name": "Maria",
  "last_name": "Santos",
  "middle_name": "Cruz",
  "phone_number": "09181234567",
  "date_of_birth": "1992-08-20",
  "gender": "Female",
  "date_hired": "2024-11-01",
  "employment_type": "Regular",
  "employment_status": "Active",
  "branch_id": 1,
  "department_id": 2,
  "designation_id": 3,
  "role_id": 2
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Employee created successfully.",
  "data": {
    "user_id": 2,
    "employee_id": "EMP-0002",
    "username": "maria.santos",
    "email": "maria@company.com"
  }
}
```

---

### Update Employee

Update an existing employee's information.

**Endpoint:** `PUT /api/employees/update/{id}`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `id`: The user ID of the employee

**Request Body:**
```json
{
  "email": "maria.new@company.com",
  "phone_number": "09181234568",
  "department_id": 3,
  "designation_id": 4
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Employee updated successfully."
}
```

---

### Delete Employee

Permanently delete an employee from the system.

**Endpoint:** `DELETE /api/employees/delete/{id}`  
**Authentication:** Required (Admin)

**URL Parameters:**
- `id`: The user ID of the employee

**Success Response (200):**
```json
{
  "success": true,
  "message": "Employee deleted successfully."
}
```

---

### Activate Employee

Activate an inactive employee account.

**Endpoint:** `PUT /api/employees/activate/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Employee activated successfully."
}
```

---

### Deactivate Employee

Deactivate an active employee account.

**Endpoint:** `PUT /api/employees/deactivate/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Employee deactivated successfully."
}
```

---

## Employee Details Management

### Update Government IDs

Update employee's government identification details.

**Endpoint:** `PUT /api/employees/employee-details/{id}/government-id`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "sss_number": "12-3456789-0",
  "philhealth_number": "12-345678901-2",
  "pagibig_number": "1234-5678-9012",
  "tin_number": "123-456-789-000"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Government IDs updated successfully.",
  "data": {
    "sss_number": "12-3456789-0",
    "philhealth_number": "12-345678901-2",
    "pagibig_number": "1234-5678-9012",
    "tin_number": "123-456-789-000"
  }
}
```

---

### Update Bank Details

Update employee's bank account information.

**Endpoint:** `PUT /api/employees/employee-details/{id}/bank-details`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "bank_id": 1,
  "account_number": "1234567890",
  "account_name": "Juan Santos Dela Cruz"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Bank details updated successfully."
}
```

---

### Add Family Information

Add family member information for an employee.

**Endpoint:** `PUT /api/employees/employee-details/{id}/family-informations`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "relationship": "Spouse",
  "first_name": "Ana",
  "last_name": "Dela Cruz",
  "middle_name": "Santos",
  "date_of_birth": "1991-03-10",
  "occupation": "Teacher"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Family information added successfully."
}
```

---

### Update Family Information

Update existing family member information.

**Endpoint:** `PUT /api/employees/employee-details/{user}/family-informations/update/{family}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Family information updated successfully."
}
```

---

### Delete Family Information

Delete a family member record.

**Endpoint:** `DELETE /api/employees/employee-details/{user}/family-informations/delete/{family}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Family information deleted successfully."
}
```

---

### Add Education Details

Add educational background for an employee.

**Endpoint:** `POST /api/employees/employee-details/{id}/education-details`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "level": "Bachelor's Degree",
  "school_name": "University of the Philippines",
  "field_of_study": "Computer Science",
  "year_graduated": "2015"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Education details added successfully."
}
```

---

### Add Work Experience

Add work experience for an employee.

**Endpoint:** `POST /api/employees/employee-details/{id}/experience-details`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "company_name": "Acme Corporation",
  "position": "Junior Developer",
  "start_date": "2015-06-01",
  "end_date": "2018-12-31",
  "responsibilities": "Developed web applications using PHP and Laravel"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Work experience added successfully."
}
```

---

### Update Emergency Contacts

Update employee's emergency contact information.

**Endpoint:** `PUT /api/employees/employee-details/{id}/emergency-contacts`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "contact_name": "Ana Dela Cruz",
  "relationship": "Spouse",
  "phone_number": "09171234567",
  "address": "123 Main St, Quezon City"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Emergency contact updated successfully."
}
```

---

## Salary Management

### List Salary Records

View salary history for an employee.

**Endpoint:** `GET /api/employees/employee-details/{id}/salary-records`  
**Authentication:** Required (Admin)  
**Permission:** Salary Admin (Module ID: 53)

**Query Parameters:**
```
?dateRange=01/01/2025 - 12/31/2025
&salaryType=monthly_fixed
&status=1
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "salary_type": "monthly_fixed",
      "basic_salary": 50000.00,
      "effective_date": "2025-01-01",
      "is_active": true,
      "remarks": "Annual salary increase",
      "created_by": "Admin User",
      "created_at": "2024-12-15 10:00:00"
    },
    {
      "id": 2,
      "user_id": 10,
      "salary_type": "monthly_fixed",
      "basic_salary": 45000.00,
      "effective_date": "2024-01-01",
      "is_active": false,
      "remarks": "Initial salary",
      "created_at": "2024-01-10 09:00:00"
    }
  ]
}
```

---

### Create Salary Record

Add a new salary record for an employee.

**Endpoint:** `POST /api/employees/employee-details/{id}/salary-records/create`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "salary_type": "monthly_fixed",
  "basic_salary": 55000.00,
  "effective_date": "2026-01-01",
  "remarks": "Promotion to Senior Engineer"
}
```

**Field Descriptions:**
- `salary_type`: **Required**. Type of salary (`monthly_fixed`, `daily_rate`, `hourly_rate`)
- `basic_salary`: **Required**. Basic salary amount
- `effective_date`: **Required**. Date when salary takes effect (YYYY-MM-DD)
- `remarks`: Optional. Notes about the salary change

**Success Response (201):**
```json
{
  "success": true,
  "message": "Salary record created successfully.",
  "data": {
    "id": 3,
    "salary_type": "monthly_fixed",
    "basic_salary": 55000.00,
    "effective_date": "2026-01-01",
    "is_active": false
  }
}
```

---

### Update Salary Record

Update an existing salary record.

**Endpoint:** `PUT /api/employees/employee-details/{userId}/salary-records/update/{salaryId}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Salary record updated successfully."
}
```

---

### Delete Salary Record

Delete a salary record (cannot delete active record).

**Endpoint:** `DELETE /api/employees/employee-details/{userId}/salary-records/delete/{salaryId}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Salary record deleted successfully."
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Cannot delete the active salary record."
}
```

---

## Shift Management

### List All Shifts

Retrieve all shift schedules with filtering.

**Endpoint:** `GET /api/shift-management/shift-list`  
**Authentication:** Required (Admin)  
**Permission:** Shift Management (Module ID: 16)

**Query Parameters:**
```
?branch=1
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "shift_name": "Morning Shift",
      "branch_id": 1,
      "branch_name": "Main Office",
      "shift_start": "08:00:00",
      "shift_end": "17:00:00",
      "break_start": "12:00:00",
      "break_end": "13:00:00",
      "break_minutes": 60,
      "grace_period_minutes": 15,
      "is_flexible": false,
      "night_diff_start": "22:00:00",
      "night_diff_end": "06:00:00"
    }
  ]
}
```

---

### Create Shift

Create a new shift schedule.

**Endpoint:** `POST /api/shift-management/shift-list/create`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "shift_name": "Night Shift",
  "branch_id": 1,
  "shift_start": "22:00:00",
  "shift_end": "06:00:00",
  "break_start": "02:00:00",
  "break_end": "03:00:00",
  "break_minutes": 60,
  "grace_period_minutes": 15,
  "is_flexible": false
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Shift created successfully.",
  "data": {
    "id": 2,
    "shift_name": "Night Shift"
  }
}
```

---

### Update Shift

Update an existing shift schedule.

**Endpoint:** `PUT /api/shift-management/shift-list/update/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Shift updated successfully."
}
```

---

### Delete Shift

Delete a shift schedule.

**Endpoint:** `DELETE /api/shift-management/shift-list/delete/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Shift deleted successfully."
}
```

---

### Get Shift Assignments

View shift assignments for employees.

**Endpoint:** `GET /api/shift-management`  
**Authentication:** Required (Admin)

**Query Parameters:**
```
?start_date=2025-11-01
&end_date=2025-11-30
&branch_id=1
&department_id=2
&designation_id=3
```

**Success Response (200):**
```json
{
  "status": "success",
  "dateRange": [
    {
      "full": "2025-11-01",
      "short": "11/01/2025",
      "day": "Fri"
    }
  ],
  "assignments": [
    {
      "employee_id": 10,
      "employee_name": "Juan Dela Cruz",
      "2025-11-01": {
        "shift_id": 1,
        "shift_name": "Morning Shift",
        "shift_start": "08:00:00",
        "shift_end": "17:00:00"
      }
    }
  ]
}
```

---

### Create Shift Assignment

Assign shifts to employees.

**Endpoint:** `POST /api/shift-management/shift-assignment`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "assignment_type": "manual",
  "user_ids": [10, 11, 12],
  "shift_id": 1,
  "assignment_mode": "recurring",
  "start_date": "2025-12-01",
  "end_date": "2025-12-31",
  "days_of_week": ["mon", "tue", "wed", "thu", "fri"]
}
```

**Field Descriptions:**
- `assignment_type`: **Required**. Type of assignment (`manual`, `branch`, `department`, `designation`)
- `user_ids`: **Required** for manual. Array of user IDs
- `shift_id`: **Required**. Shift ID to assign
- `assignment_mode`: **Required**. Mode (`single_day` or `recurring`)
- `start_date`: **Required**. Start date for assignment
- `end_date`: Optional. End date (required for recurring)
- `days_of_week`: Optional. Days for recurring (`mon`, `tue`, `wed`, `thu`, `fri`, `sat`, `sun`)

**Success Response (201):**
```json
{
  "success": true,
  "message": "Shift assignments created successfully.",
  "assignments_created": 60
}
```

---

### Bulk Delete Shift Assignments

Delete multiple shift assignments.

**Endpoint:** `POST /api/shift-management/bulk-delete-assignments`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "user_ids": [10, 11, 12],
  "start_date": "2025-12-01",
  "end_date": "2025-12-31"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Shift assignments deleted successfully.",
  "deleted_count": 60
}
```

---

## Payroll Management

### Process Payroll

Process payroll for selected employees and date range.

**Endpoint:** `POST /api/payroll/process`  
**Authentication:** Required (Admin)  
**Permission:** Payroll Admin

**Request Body:**
```json
{
  "payroll_type": "normal_payroll",
  "assignment_type": "manual",
  "user_id": [10, 11, 12],
  "start_date": "2025-11-01",
  "end_date": "2025-11-15",
  "transaction_date": "2025-11-16",
  "sss_option": "semi_monthly",
  "philhealth_option": "semi_monthly",
  "pagibig_option": "monthly",
  "cutoff_option": "first_cutoff"
}
```

**Field Descriptions:**
- `payroll_type`: **Required**. Type (`normal_payroll`, `13th_month`, `bulk_attendance_payroll`)
- `assignment_type`: **Required**. Assignment type (`manual`, `payroll_batch`)
- `user_id`: **Required** for manual. Array of user IDs
- `payroll_batch_id`: Optional. Batch ID if using payroll_batch
- `start_date`: **Required**. Payroll period start date
- `end_date`: **Required**. Payroll period end date
- `transaction_date`: Optional. Payment date
- `sss_option`: **Required**. SSS deduction frequency (`monthly`, `semi_monthly`)
- `philhealth_option`: **Required**. PhilHealth deduction frequency
- `pagibig_option`: **Required**. Pag-IBIG deduction frequency
- `cutoff_option`: **Required**. Cutoff period (`first_cutoff`, `second_cutoff`)

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Payroll processed successfully.",
  "data": [
    {
      "payroll_id": 100,
      "user_id": 10,
      "employee_name": "Juan Dela Cruz",
      "basic_pay": 25000.00,
      "total_earnings": 28000.00,
      "total_deductions": 5000.00,
      "net_pay": 23000.00,
      "payroll_period": "November 1-15, 2025"
    }
  ]
}
```

---

### Update Payroll Record

Update a processed payroll record.

**Endpoint:** `PUT /api/payroll/update/{id}`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "basic_pay": 26000.00,
  "allowances": 2000.00,
  "deductions": 5500.00,
  "remarks": "Corrected basic pay"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payroll updated successfully."
}
```

---

### Delete Payroll Record

Delete a payroll record.

**Endpoint:** `DELETE /api/payroll/delete/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payroll deleted successfully."
}
```

---

### Bulk Delete Payroll

Delete multiple payroll records.

**Endpoint:** `POST /api/payroll/bulk-delete`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "payroll_ids": [100, 101, 102]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payroll records deleted successfully.",
  "deleted_count": 3
}
```

---

### Bulk Generate Payslips

Mark payroll records as paid and generate payslips.

**Endpoint:** `POST /api/payroll/bulk-generate-payslips`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "payroll_ids": [100, 101, 102]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payslips generated successfully.",
  "count": 3
}
```

---

## 13th Month Pay Management

### Process 13th Month Pay

Process 13th month pay by aggregating payroll data across months/years.

**Endpoint:** `POST /api/payroll/13th-month/process`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "payroll_type": "13th_month",
  "assignment_type": "manual",
  "user_id": [10, 11, 12],
  "from_year": 2025,
  "from_month": 1,
  "to_year": 2025,
  "to_month": 12,
  "payment_date": "2025-12-15"
}
```

**Field Descriptions:**
- `payroll_type`: **Required**. Must be `13th_month`
- `assignment_type`: **Required**. Assignment type (`manual`, `payroll_batch`)
- `user_id`: **Required** for manual. Array of user IDs
- `from_year`: **Required**. Start year (YYYY)
- `from_month`: **Required**. Start month (1-12)
- `to_year`: **Required**. End year (YYYY)
- `to_month`: **Required**. End month (1-12)
- `payment_date`: Optional. Payment date

**Success Response (201):**
```json
{
  "status": "success",
  "message": "13th month pay processed successfully.",
  "data": [
    {
      "thirteenth_month_id": 1,
      "user_id": 10,
      "employee_name": "Juan Dela Cruz",
      "total_basic_pay": 300000.00,
      "thirteenth_month_pay": 25000.00,
      "period": "January - December 2025",
      "payment_date": "2025-12-15",
      "status": "Pending"
    }
  ]
}
```

---

### Delete 13th Month Pay Record

Delete a 13th month pay record.

**Endpoint:** `DELETE /api/payroll/13th-month/delete/{id}`  
**Authentication:** Required (Admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "13th month pay deleted successfully."
}
```

---

### Bulk Generate 13th Month Payslips

Mark 13th month pay as released.

**Endpoint:** `POST /api/payroll/13th-month/bulk-generate`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "thirteenth_month_ids": [1, 2, 3]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "13th month pay marked as Released.",
  "count": 3
}
```

---

### Bulk Delete 13th Month Pay

Delete multiple 13th month pay records.

**Endpoint:** `POST /api/payroll/13th-month/bulk-delete`  
**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "thirteenth_month_ids": [1, 2, 3]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "13th month pay deleted successfully.",
  "deleted_count": 3
}
```

---

### Export 13th Month Pay (Excel)

Export 13th month pay records to Excel.

**Endpoint:** `GET /api/payroll/13th-month/export/excel`  
**Authentication:** Required (Admin)

**Query Parameters:**
```
?from_year=2025
&from_month=1
&to_year=2025
&to_month=12
```

**Success Response (200):**
Returns downloadable Excel file

---

### Export 13th Month Pay (PDF)

Export 13th month pay records to PDF.

**Endpoint:** `GET /api/payroll/13th-month/export/pdf`  
**Authentication:** Required (Admin)

**Query Parameters:**
```
?from_year=2025
&from_month=1
&to_year=2025
&to_month=12
```

**Success Response (200):**
Returns downloadable PDF file

---

## Bulk Operations

### Import Employees

Bulk import employees via CSV file.

**Endpoint:** `POST /api/employees/import`  
**Authentication:** Required (Admin)

**Request Body (multipart/form-data):**
```
file: [CSV File Upload]
```

**CSV Format:**
```csv
username,email,password,employee_id,first_name,last_name,date_hired,branch_id,department_id,designation_id
juan.cruz,juan@company.com,Pass123!,EMP-001,Juan,Cruz,2025-01-01,1,2,3
```

**Success Response (202):**
```json
{
  "success": true,
  "message": "Employee import job has been queued.",
  "job_id": "abc123"
}
```

---

### Import Attendance

Bulk import attendance records via CSV.

**Endpoint:** `POST /api/attendance/import`  
**Authentication:** Required (Admin)

**Request Body (multipart/form-data):**
```
file: [CSV File Upload]
```

**CSV Format:**
```csv
employee_id,attendance_date,time_in,time_out,shift_id
EMP-001,2025-11-01,08:00:00,17:00:00,1
```

**Success Response (202):**
```json
{
  "success": true,
  "message": "Attendance import completed.",
  "imported_count": 50,
  "failed_count": 2,
  "errors": [
    {
      "row": 5,
      "error": "Employee not found"
    }
  ]
}
```

---

### Import Overtime

Bulk import overtime records via CSV.

**Endpoint:** `POST /api/overtime/import`  
**Authentication:** Required (Admin)

**Success Response (202):**
```json
{
  "success": true,
  "message": "Overtime import completed.",
  "imported_count": 30
}
```

---

## Export & Import

### Download Employee Template

Download CSV template for employee import.

**Endpoint:** `GET /api/employees/template`  
**Authentication:** Required (Admin)

**Success Response (200):**
Returns CSV template file

---

### Download Attendance Template

Download CSV template for attendance import.

**Endpoint:** `GET /api/attendance/template`  
**Authentication:** Required (Admin)

**Success Response (200):**
Returns CSV template file

---

### Download Overtime Template

Download CSV template for overtime import.

**Endpoint:** `GET /api/overtime/template`  
**Authentication:** Required (Admin)

**Success Response (200):**
Returns CSV template file

---

### Export Employees

Export employee list to Excel.

**Endpoint:** `GET /api/employees/export`  
**Authentication:** Required (Admin)

**Query Parameters:**
```
?branch_id=1
&department_id=2
&status=1
```

**Success Response (200):**
Returns downloadable Excel file

---

## Error Handling

### Common Admin Error Responses

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

#### 403 Forbidden - No Permission
```json
{
  "status": "error",
  "message": "You do not have the permission to create."
}
```

#### 403 Forbidden - Subscription Expired
```json
{
  "status": "error",
  "message": "Your subscription has expired or is inactive."
}
```

#### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found."
}
```

#### 422 Unprocessable Entity
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "user_id": ["Please select at least one employee."],
    "start_date": ["The start date field is required."]
  }
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

## Permissions

All admin endpoints require specific module permissions:

| Module | Permission ID | Permission Name |
|--------|--------------|----------------|
| Attendance Admin | 14 | Attendance Management |
| Overtime Admin | 17 | Overtime Management |
| Leave Admin | 19 | Leave Management |
| OB Admin | 47 | Official Business Management |
| Employee Admin | 9 | Employee Management |
| Salary Admin | 53 | Salary Management |
| Shift Admin | 16 | Shift Management |
| Payroll Admin | N/A | Payroll Management |

**Permission Levels:**
- `Create`: Can create new records
- `Read`: Can view records
- `Update`: Can edit records
- `Delete`: Can delete records
- `Approve`: Can approve requests

---

## Data Access Control

Admin endpoints respect data access control based on:
1. **Branch Access**: Admins can only see data from assigned branches
2. **Department Access**: Limited to assigned departments
3. **Designation Access**: Limited to assigned designations
4. **Tenant Isolation**: All data is tenant-scoped

**Global Admins** have access to all data across the organization.

---

## Best Practices

1. **Always validate permissions** before calling admin endpoints
2. **Use filtering** to reduce payload size and improve performance
3. **Implement pagination** for list endpoints
4. **Use bulk operations** for multiple records to reduce API calls
5. **Download templates** before importing data
6. **Test imports** with small batches first
7. **Monitor job status** for async operations (imports)
8. **Export data regularly** for backup purposes
9. **Log all admin actions** for audit trails
10. **Handle errors gracefully** and provide user feedback

---

## Rate Limiting

- **Standard rate limit:** 60 requests per minute per user
- **Bulk operations:** 10 requests per minute
- **Export operations:** 5 requests per minute
- **Import operations:** 3 requests per minute

---

## Support

For technical support or API access:
- **Email:** support@timora.com
- **Developer Portal:** https://developers.timora.com
- **Documentation:** https://docs.timora.com
- **Status Page:** https://status.timora.com

---

## Changelog

### Version 1.0 (November 2025)
- Initial private API documentation
- Admin attendance management endpoints
- Admin overtime management endpoints
- Admin leave management endpoints
- Admin official business management endpoints
- Admin attendance request endpoints
- Employee management (CRUD)
- Employee details management
- Salary management endpoints
- Shift management endpoints
- Payroll processing endpoints
- 13th month pay processing
- Bulk operations and actions
- Import/Export functionality

---

**© 2025 Timora HRIS. All rights reserved.**

**Confidential:** This documentation is for internal use only. Do not share externally without authorization.
