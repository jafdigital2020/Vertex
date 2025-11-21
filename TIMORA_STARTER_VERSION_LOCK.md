# Timora Starter Version Lock Documentation

## Purpose
This document serves as formal documentation declaring that the current version of the **Timora Starter Automated Payroll System** has been officially locked (**v1.0.0**). This means that no new features, enhancements, or structural changes will be applied to this version, except for critical bug fixes required for system stability. The version lock signifies the completion and stabilization of the Starter tier product offering.

---

## Scope

The following modules and functionalities are included in this version lock for the **Timora Starter Plan**.

### **Dashboard**
- **Admin Dashboard**
  - Employee overview
  - Attendance summary
  - Payroll statistics
  - Quick actions panel

- **Employee Dashboard**
  - Personal attendance records
  - Payslip access
  - Leave balance overview
  - Quick time-keeping actions

---

### **Branch**
- **Branch Management**
  - Create and manage branches
  - Branch-specific configurations
  - Branch subscription management

---

### **Employees**
- **Employee Lists**
  - View all employees
  - Employee search and filtering
  - Export employee data
  - Employee status management

- **Employee Details**
  - Personal information
  - Employment details
  - Contact information
  - Emergency contacts

- **Salary Record**
  - Basic salary configuration
  - Salary history tracking
  - Compensation overview

- **Departments**
  - Department creation
  - Department assignment
  - Department hierarchy

- **Designations**
  - Designation/position management
  - Job title configuration
  - Role assignment

---

### **Holidays**
- **Holiday Management**
  - National holiday configuration
  - Company-wide holiday setup
  - Holiday calendar view

---

### **Attendance**
- **Attendance (Admin)**
  - View all employee attendance
  - Attendance logs and history
  - Late/absent tracking
  - Attendance approval

- **Attendance (Employee)**
  - Time Keeping (Check-in & Check-out)
  - Attendance photo capture
  - Daily time logs
  - Personal attendance history

- **Request Attendance (Admin)**
  - Review attendance requests
  - Approve/reject attendance corrections
  - Request history tracking

- **Request Attendance (Employee)**
  - File attendance corrections
  - View request status
  - Attendance justification

- **Shift Management**
  - Shift List (Day, Mid, Night, Flexi)
  - Shift scheduling
  - Shift assignment to employees
  - Flexible shift configuration

- **Overtime (Admin)**
  - Review overtime requests
  - Approve/reject overtime
  - Overtime calculation
  - Overtime reports

- **Overtime (Employee)**
  - File overtime requests
  - View overtime status
  - Overtime history

---

### **Leaves**
- **Leave (Admin)**
  - Review leave applications
  - Approve/reject leave requests
  - Leave balance management
  - Leave calendar view

- **Leave (Employee)**
  - File leave applications
  - View leave status
  - Leave balance tracking
  - Leave history

- **Leave Settings**
  - Leave type configuration
  - Leave credit allocation
  - Leave policies

---

### **Payroll**
- **Process Payroll**
  - Payroll computation engine
  - Salary calculation
  - Generate payroll batch
  - Preview payroll before finalization

- **Employee Salary**
  - Individual salary management
  - Salary components setup
  - Compensation adjustments

- **Generated Payslips**
  - Normal Payslip generation
  - Payslip batch processing
  - Payslip history

- **Payroll Items**
  - **SSS Contribution** - Social Security System calculations
  - **PhilHealth** - Philippine Health Insurance computation
  - **Withholding Tax** - BIR tax calculations
  - **OT Table** - Overtime rate configurations
  - **De Minimis** - Non-taxable benefits
  - **Earnings** - Additional income items
  - **Deductions** - Standard deductions (loans, advances, etc.)

---

### **Payslip**
- **Payslip (Employee)**
  - View personal payslips
  - Download payslips (PDF)
  - Payslip history access
  - Detailed earnings and deductions breakdown

---

### **User Management**
- **Users**
  - User account creation
  - User profile management
  - User activation/deactivation
  - Access control

- **Roles & Permissions**
  - Role creation and assignment
  - Permission configuration
  - Module access control
  - Data access level settings (Organization-wide, Branch-level, Department-level, Personal)

---

### **Settings**
- **Attendance Settings**
  - Work hours configuration
  - Tardiness rules
  - Undertime policies
  - Attendance capture settings

- **Approval Settings**
  - Single-level approval workflow
  - Approval routing configuration
  - Auto-approval settings

- **Leave Type**
  - Leave type creation
  - Leave credit rules
  - Leave policies per type

- **Custom Fields (Prefix)**
  - Employee ID prefix configuration
  - Custom field setup
  - Field formatting

- **App Settings**
  - Company information
  - System preferences
  - Email configurations
  - Localization settings

---

### **Geotagging & Location Tracking**
- Real-time location capture during check-in/check-out
- GPS coordinates logging
- Location verification
- Geofencing capabilities

---

### **Reports**
- **Government Report Generator**
  - Basic compliance reports
  - Summary reports for SSS, PhilHealth, PAG-IBIG
  - Export to Excel/PDF

---

### **Billing**
- **Bills & Payment**
  - Subscription invoice viewing
  - Payment history
  - Payment method management
  - Invoice download

- **Subscription**
  - Current plan details
  - Subscription renewal
  - Plan upgrade options
  - Add-on management

---

## Excluded Features (Available as Add-ons)

The following features are **NOT included** in the Starter version but are available as optional add-ons:

### **Optional Add-ons (Separate Purchase Required)**

1. **Employee Official Business** (₱1,200/month)
   - Business trip tracking
   - Travel requests and approval

2. **Asset Management Tracking** (₱1,900/month)
   - Asset inventory management
   - Assignment tracking
   - Maintenance logs

3. **Bank Data Export (CSV)** (₱1,000/month)
   - Multi-bank format support
   - Automated bank file generation

4. **Payroll Batch Processing** (₱600/month)
   - Bulk payroll operations
   - Multiple batch support

5. **Policy Upload** (₱500/month)
   - Company policy distribution
   - Policy acknowledgment tracking

6. **Custom Holiday** (₱800/month)
   - Custom holiday creation
   - Branch-specific holidays

7. **ZKTeco Biometrics Integration** (₱1,500/month)
   - Biometric device integration
   - Automated attendance sync

8. **Allowance Management** (₱700/month)
   - Custom allowance types
   - Payroll integration

9. **Alphalist Report** (₱900/month)
   - BIR Alphalist generation
   - Year-end reporting

10. **SSS Reports** (₱900/month)
    - SSS R3 report generation
    - Contribution reports

11. **Policies Management** (₱1,200/month - Upgrade)
    - Advanced policy management
    - Version control

12. **Advanced Approval Settings** (₱800/month - Upgrade)
    - Multi-tier approval workflows
    - Unlimited approval levels

13. **Official Business (Full Access)** (₱1,500/month - Upgrade)
    - Complete OB module with admin and employee features

14. **Assets Management (Full Access)** (₱2,200/month - Upgrade)
    - Complete asset lifecycle tracking

---

## Pricing Model

### **Base Plan Pricing**
- **Base Price:** ₱49.00 per employee per month (VAT Inclusive: ₱54.88)
- **Additional Employees:** ₱40.00 per employee (₱44.80 with VAT)
- **Billing Period:** Monthly or Annual
- **Trial Period:** 30 days
- **Currency:** Philippine Peso (PHP)

### **Pricing Formula**
```
Base Price = ₱49.00 (per employee)
Additional Employees = (total_employees - 1) × ₱40.00
Add-ons = Sum of selected addon prices
Subtotal = Base Price + Additional Employees + Add-ons
VAT (12%) = Subtotal × 0.12
Total Monthly = Subtotal + VAT
```

### **Annual Billing Discount**
- Add-ons receive 10% discount when paid annually
- Base employee pricing scales × 12 months

---

## Version Summary

**Version:** v1.0.0
**Release Date:** 11/20/2025
**Release Type:** Final Starter Plan Release
**Plan Slug:** `starter`
**Summary:** This version represents the official locked release of the Timora Starter plan, providing essential HR and payroll management features for small to medium-sized businesses. All core modules have been tested, validated, and optimized for production deployment. This version serves as the foundation tier with optional add-ons available for enhanced functionality.

---

## Multi-Tenancy Architecture

**Structure:**
- Central Database: Manages tenant registration, subscriptions, billing
- Tenant Databases: Isolated per tenant with prefix `tenant{id}`
- Domain-based Identification: Each tenant accessed via subdomain routing

**Key Packages:**
- `stancl/tenancy` - Multi-tenancy implementation
- `spatie/laravel-permission` - Role and permission management
- `barryvdh/laravel-dompdf` - PDF generation for payslips
- `maatwebsite/excel` - Excel export functionality

---

## Permission System

### **Data Access Levels**
- **Organization-wide Access:** All branches
- **Branch-level Access:** Specific branch only
- **Department-level Access:** Department-specific data
- **Personal Access:** Individual employee data only

### **Permission Operations**
- Operation 1: Create
- Operation 2: Read/View
- Operation 3: Update/Edit
- Operation 4: Delete
- Operation 5: Export
- Operation 6: Import

### **Core Modules in Starter**
- Module 1: Dashboard
- Module 3: Branch
- Module 4: Employees
- Module 5: Holidays
- Module 6: Attendance
- Module 7: Leaves
- Module 10: Payroll
- Module 11: Payslip
- Module 13: User Management
- Module 15: Settings

---

## Technical Stack

**Backend:**
- Laravel 11.x
- PHP 8.2+
- Multi-tenant Architecture (stancl/tenancy)

**Frontend:**
- Vite
- Blade Templates
- Bootstrap
- JavaScript

**Database:**
- MySQL
- Multi-tenant database isolation

**Key Dependencies:**
- Spatie Permission (Role management)
- DomPDF (PDF generation)
- Laravel Excel (Export functionality)
- Intervention Image (Image processing)

---

## Support & Maintenance Policy

**Covered Under Version Lock:**
- Critical security patches
- Bug fixes affecting system stability
- Performance optimizations
- Data integrity fixes

**Not Covered (Requires New Version):**
- New feature requests
- UI/UX enhancements
- Third-party integrations beyond scope
- Major architectural changes

---

## Migration Path to Full Version

Customers can upgrade from Starter to enhanced versions by:
1. Activating optional add-ons (modular approach)
2. Upgrading to higher-tier plans (future releases)
3. Seamless migration without data loss
4. Prorated billing for mid-cycle upgrades

---

## Contact & Support

For inquiries regarding this version or upgrade options:
- **Support Portal:** [Insert Support URL]
- **Documentation:** [Insert Docs URL]
- **Sales/Upgrades:** [Insert Contact]

---

**Document Version:** 1.0
**Last Updated:** 11/20/2025
**Approved By:** [Approver Name/Role]
**Status:** LOCKED - No feature additions beyond this scope

---

## Changelog Summary

**v1.0.0 (11/20/2025)** - Initial Starter Version Lock
- Established core HR and payroll modules
- Implemented multi-tenant architecture
- Configured base pricing model (₱49/employee)
- Created 14 optional add-ons
- Completed 30-day trial implementation
- Finalized permission and role system
- Implemented government compliance calculations (SSS, PhilHealth, Withholding Tax)
- Tested and validated all Starter plan features
- Locked version for production stability

---

**END OF DOCUMENT**
