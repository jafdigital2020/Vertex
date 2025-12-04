# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### PHP/Laravel Commands
- `composer dev` - Starts concurrent development environment (PHP server, queue worker, log monitoring, and Vite)
- `php artisan serve` - Start Laravel development server
- `php artisan queue:listen --tries=1` - Start queue worker
- `php artisan pail --timeout=0` - Monitor Laravel logs
- `php artisan test` - Run PHPUnit tests
- `vendor/bin/pint` - Laravel Pint code formatter (PSR-12)
- `php artisan migrate` - Run database migrations
- `php artisan migrate:rollback` - Rollback last migration batch
- `php artisan tinker` - Laravel REPL

### Frontend/Asset Commands  
- `npm run dev` - Start Vite development server (hot reload)
- `npm run build` - Build production assets with Vite
- `npm install` - Install Node.js dependencies

### Multi-Tenancy Commands
- `php artisan tenants:migrate` - Run tenant-specific migrations
- `php artisan tenants:seed` - Seed tenant databases
- `php artisan tenants:list` - List all tenants

## Architecture Overview

### Multi-Tenancy Structure
This is a multi-tenant Laravel application using the `stancl/tenancy` package. The architecture supports:

- **Central Application**: Manages tenant registration, subscriptions, and super admin functions
- **Tenant Applications**: Isolated instances for each organization with separate databases
- **Domain-based Identification**: Each tenant accessed via subdomain/domain routing

Key tenancy files:
- `config/tenancy.php` - Tenancy configuration
- `routes/tenant.php` - Tenant-specific routes
- `database/migrations/tenant/` - Tenant database migrations

### Application Domains

This is an HR/Payroll management system with the following core modules:

**Employee Management**:
- Employee profiles, employment details, resignations, terminations
- Department and designation management
- User management with role-based permissions

**Attendance & Time Tracking**:
- Biometric integration (ZKTeco devices)
- Attendance logs and bulk attendance management
- Overtime tracking and approval workflows
- Official business (OB) requests

**Leave Management**:
- Leave applications and approvals
- Leave type configuration
- Leave balance tracking

**Payroll System**:
- Payroll processing and batch operations
- Earnings, allowances, and deductions
- Payslip generation
- Government contributions (SSS, PhilHealth, PAG-IBIG)
- Withholding tax calculations

**Recruitment Module**:
- Job posting and manpower request management
- Candidate registration and application tracking
- Interview scheduling and management
- Job offer creation and approval
- Dynamic approval workflows

**Branch & Organization**:
- Multi-branch support within tenants
- Branch-specific configurations
- Subscription and addon management

**Reporting**:
- Payroll reports
- Government reports (SSS, Alphalist)
- Custom reporting capabilities

### Controller Organization

Controllers are organized by domain and access level:

- `app/Http/Controllers/` - Central/global controllers
- `app/Http/Controllers/Tenant/` - Tenant-specific business logic
- `app/Http/Controllers/SuperAdmin/` - Super admin functions

Key tenant controller directories:
- `Attendance/` - Time tracking and biometric integration
- `Employees/` - Employee lifecycle management
- `Payroll/` - Payroll processing and calculations
- `Leave/` - Leave management system
- `Settings/` - Branch configurations and customization
- `Report/` - Reporting and analytics
- `Billing/` - Subscription and payment handling
- `Recruitment/` - Job postings, candidates, interviews, offers

### Database Architecture

- **Central Database**: Stores tenants, domains, global users, subscriptions
- **Tenant Databases**: Isolated per tenant with prefix `tenant{id}`
- **Shared Models**: Some models (like `Tenant`, `GlobalUser`) exist in central database
- **Tenant Models**: Most business logic models are tenant-specific

### Key Packages & Integrations

- **stancl/tenancy** - Multi-tenancy implementation
- **spatie/laravel-permission** - Role and permission management
- **barryvdh/laravel-dompdf** - PDF generation for reports/payslips
- **maatwebsite/excel** - Excel export/import functionality
- **intervention/image** - Image processing for employee photos
- **rats/zkteco** - Biometric device integration
- **jenssegers/agent** - Device/browser detection

### Testing

- Test files located in `tests/Feature/` and `tests/Unit/`
- PHPUnit configuration in `phpunit.xml`
- Use `php artisan test` to run the test suite
- Tests use SQLite in-memory database for speed

### Middleware & Security

Key middleware:
- `CheckPermission` - Role-based access control
- `CheckAddon` - Feature access based on subscriptions
- `EnsureUserIsAuthenticated` - Authentication verification
- Tenancy middleware for proper tenant context

### File Storage

- Tenant-specific file storage isolation
- Employee photos, documents, and reports stored per tenant
- Configuration in `config/filesystems.php` with tenancy support

## Permission System

The application uses a dual permission system combining Laravel's Spatie Permission package with a custom addon-based system.

### Data Access Levels

**Organization-wide Access**
- Grants access to all branches or to specific selected branches

**Branch-level Access**  
- Grants access only to the user's branch
- If the logged-in user is a Department Head of another branch, access also expands to include that branch

**Department-level Access**
- Grants access only to the user's department  
- If the logged-in user is a Department Head of another department, access also expands to include that department

**Personal Access**
- Grants access only to the personal data of the logged-in user

### Permission Structure

**User Permissions Table Structure**:
- `module_ids`: Comma-separated module access (e.g., "1,2,3,4,5,6,15")
- `user_permission_ids`: CRUD permissions in format "{module}-{operation}" (e.g., "1-1,1-2,1-3")
- `menu_ids`: Menu access permissions

**Permission Operations**:
- Operation 1: Create
- Operation 2: Read/View  
- Operation 3: Update/Edit
- Operation 4: Delete
- Operation 5: Export
- Operation 6: Import

**Key Permission Examples**:
- Module 1 (Dashboard): "1-1,1-2,1-3,1-4,1-5,1-6" (full access)
- Module 15 (Settings): Required for settings page visibility

### Sub Module Documentation

**Complete module mapping (`{submodule_id} => {submodule_name}`):**

**1 – Dashboard**
- 1 ⇒ Admin Dashboard
- 2 ⇒ Employee Dashboard

**2 – Super Admin**
- 3 ⇒ Dashboard
- 4 ⇒ Tenants
- 5 ⇒ Subscriptions
- 6 ⇒ Packages
- 7 ⇒ Payment Transaction

**3 – Branch**
- 8 ⇒ Branch

**4 – Employees**
- 9 ⇒ Employee Lists
- 10 ⇒ Departments
- 11 ⇒ Designations
- 12 ⇒ Policies
- 53 ⇒ Employee Salary Record
- 57 ⇒ Inactive List

**5 – Holidays**
- 13 ⇒ Holidays

**6 – Attendance**
- 14 ⇒ Attendance (Admin)
- 15 ⇒ Attendance (Employee)
- 16 ⇒ Shift & Schedule
- 17 ⇒ Overtime (Admin)
- 18 ⇒ Attendance Settings
- 45 ⇒ Overtime (Employee)

**7 – Leaves**
- 19 ⇒ Leaves (Admin)
- 20 ⇒ Leave (Employee)
- 21 ⇒ Leave Settings

**8 – Resignation**
- 22 ⇒ Resignation
- 58 ⇒ Resignation Employee
- 59 ⇒ Resignation Settings

**9 – Termination**
- 23 ⇒ Termination

**10 – Payroll**
- 24 ⇒ Employee Salary
- 25 ⇒ Generated Payslips
- 26 ⇒ Payroll Items
- 51 ⇒ Payroll Batch Users
- 52 ⇒ Payroll Batch Settings

**11 – Payslip**
- 27 ⇒ Payslip

**12 – Help & Support**
- 28 ⇒ Knowledge Base
- 29 ⇒ Activities

**13 – User Management**
- 30 ⇒ Users
- 31 ⇒ Roles & Permissions

**15 – Settings**
- 43 ⇒ App Settings

**16 – Bank**
- 46 ⇒ Bank

**17 – Official Business**
- 47 ⇒ Official Business (Admin)
- 48 ⇒ Official Business (Employee)

**18 – Assets Management**
- 49 ⇒ Employee Assets
- 50 ⇒ Assets Settings

**19 – Reports**
- 54 ⇒ Payroll Summary Report
- 55 ⇒ Alphalist Report
- 56 ⇒ SSS Reports

**21 – Recruitment**
- 58 ⇒ Job Postings
- 59 ⇒ Candidates
- 60 ⇒ Job Applications
- 61 ⇒ Interviews
- 62 ⇒ Job Offers
- 63 ⇒ Manpower Requests

### Addon System

**CheckAddon Middleware**: Controls access to premium features
- Checks if user's branch has specific addons activated
- Returns `errors.featurerequired` view if addon missing
- Located in `app/Http/Middleware/CheckAddon.php`

**Database Tables**:
- `addons`: Available system addons with pricing
- `branch_addons`: Branch-specific addon subscriptions with active status

## Dynamic Approval System

The application uses a flexible approval workflow system that applies across multiple modules (Leave, Overtime, Recruitment, etc.).

### Core Components

**approval_steps Table**: Configures approval levels per branch
- `branch_id`: Branch-specific configuration
- `level`: Approval sequence (1, 2, 3, etc.)
- `approver_kind`: 'user' or 'department_head'
- `approver_user_id`: Specific user for approval

**Approval Flow**: Each approvable item (ManpowerRequest, LeaveRequest, etc.) gets corresponding approval records created automatically based on the branch's approval_steps configuration.

### Implementation Pattern

1. **Item Creation**: When an approvable item is created, the system automatically creates approval records for each configured level
2. **Sequential Approval**: Approvals must be completed in order (level 1 before level 2, etc.)
3. **Auto-assignment**: Department heads are auto-assigned based on the item's department
4. **Status Tracking**: Complete audit trail of who approved what and when

This system is used by:
- Leave management
- Overtime requests  
- Recruitment (manpower requests, job offers)
- Official business requests

## Database Naming Conventions

### Migration Patterns
- Use descriptive timestamps: `2025_11_24_120000_create_job_postings_table.php`
- Include `branch_id` in all tenant-specific tables for multi-branch isolation
- Use `enum('active', 'inactive')` instead of boolean for status fields
- Include `nullableMorphs('updated_by')` for audit trails
- Explicit foreign key declarations with proper cascade/set null actions

### Seeder Organization
- Use dynamic ID assignment with `insertGetId()` instead of hard-coded IDs
- Include safety checks for existing data with `where()->first()` before insertion
- Follow naming: `{Module}MenuModuleSeeder.php` for menu/module/submodule setup
- Include in `DatabaseSeeder.php` for automatic execution

## Recruitment Module Specifics

### Authentication System
- Separate candidate authentication using `auth:candidate` guard
- Candidates table mirrors users table structure but for job applicants
- Role-based permissions using same system as employees
- Public career page with protected application areas

### Workflow Integration
- Manpower requests trigger approval workflows
- Approved requests become job postings
- Applications go through interview and offer processes
- Full audit trail through `application_workflow` table

### Branch Integration
- All recruitment data is branch-isolated via `branch_id` foreign keys
- Data access levels control visibility (Organization/Branch/Department/Personal)
- Integrates with existing department/designation structures

# important-instruction-reminders
Do what has been asked; nothing more, nothing less.
NEVER create files unless they're absolutely necessary for achieving your goal.
ALWAYS prefer editing an existing file to creating a new one.
NEVER proactively create documentation files (*.md) or README files. Only create documentation files if explicitly requested by the User.