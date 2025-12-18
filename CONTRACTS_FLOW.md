# Contract Management System - Flow Documentation

## Overview
The Contract Management System allows you to create contract templates and generate employee contracts using either PDF templates or text/HTML content.

---

## System Flow

### 1️⃣ **Setup Phase: Create Contract Templates**

**Location:** Settings → App Settings → Contract Templates

**Steps:**
1. Click "Add Template" button
2. Fill in template details:
   - **Template Name**: e.g., "Probationary Employment Contract 2024"
   - **Contract Type**: Probationary, Regular, Contractual, or Project-Based
   - **Template Source**: Choose between:
     - **PDF Template (Recommended)**: Use pre-designed PDF files
     - **Text/HTML Content**: Manually write template content

**Option A: Using PDF Template**
- System shows dropdown with available PDF files:
  - `01 Rev. 04 Probationary Employment 2024.pdf` (for Probationary)
  - `02 Rev. 02 Regular Employment 2024.pdf` (for Regular)
- Auto-selects matching PDF based on contract type
- No need to write content manually

**Option B: Using Text/HTML Content**
- Manually type or paste template content
- Use placeholders for dynamic data:
  - `{{employee_full_name}}`
  - `{{date_hired}}`
  - `{{probationary_end_date}}`
  - `{{employee_id}}`
  - `{{position}}`
  - `{{department}}`
  - `{{current_date}}`

3. Set template status (Active/Inactive)
4. Save template

---

### 2️⃣ **Usage Phase: Generate Employee Contracts**

**Location:** Employees → Contracts

**Steps:**
1. Click "Add Contract" or "Generate Contract"
2. Select employee from dropdown
3. Select contract template
4. System automatically:
   - Fetches employee data (name, position, department, etc.)
   - Replaces placeholders with actual data
   - Generates contract content
5. Set contract details:
   - **Start Date**: Contract start date
   - **End Date**: Auto-calculated for probationary (6 months), optional for others
   - **Status**: Draft, Active, Expired, or Terminated
6. Save contract

**Code Flow:**
```php
// ContractController.php - store() method
1. User selects employee + template
2. System loads employee with relationships:
   - Personal Information
   - Employment Details
   - Designation
   - Department
3. If template selected:
   - Calls: $template->generateContract($employee)
   - Replaces placeholders with employee data
4. Creates contract record in database
5. Contract status = Draft
```

---

### 3️⃣ **Contract Lifecycle Management**

**Contract Statuses:**
- **Draft**: Initial state, can be edited
- **Active**: Contract is signed and active
- **Expired**: Contract has passed end date
- **Terminated**: Contract was terminated early

**Actions Available:**

#### **Sign Contract**
- Endpoint: `POST /contracts/{contract}/sign`
- Changes status from Draft → Active
- Records signed date and who signed it
- Used by authorized personnel (HR/Admin)

#### **View/Preview Contract**
- Shows generated contract with employee data filled in
- Displays contract details, dates, status

#### **Print/Download Contract**
- Endpoint: `GET /contracts/{contract}/print`
- Generates printable version
- Can be used for PDF generation

#### **Edit Contract**
- Modify contract details
- Update content if needed
- Change status

#### **Delete Contract**
- Remove contract from system
- Soft delete (can be recovered if implemented)

---

## Database Structure

### **contract_templates table**
```
- id
- name                  (e.g., "Probationary Contract 2024")
- contract_type         (Probationary, Regular, Contractual, Project-Based)
- content              (nullable - HTML/text template content)
- pdf_template_path    (nullable - path to PDF file)
- is_active           (true/false)
- tenant_id           (multi-tenant support)
- timestamps
```

### **contracts table**
```
- id
- user_id             (employee)
- template_id         (which template was used)
- contract_type       (copied from template)
- content            (generated contract with filled data)
- start_date
- end_date           (auto-calculated for probationary)
- status             (Draft, Active, Expired, Terminated)
- signed_date        (when contract was signed)
- signed_by          (user who signed it)
- tenant_id
- timestamps
```

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    1. CREATE TEMPLATE                        │
│                                                              │
│  Admin → Contract Templates → Add Template                  │
│         ↓                                                    │
│  Choose Source: PDF or Text                                 │
│         ↓                                                    │
│  PDF: Select from available PDFs                            │
│   OR                                                         │
│  Text: Write content with {{placeholders}}                  │
│         ↓                                                    │
│  Save to contract_templates table                           │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│                 2. GENERATE CONTRACT                         │
│                                                              │
│  HR/Admin → Contracts → Add Contract                        │
│         ↓                                                    │
│  Select Employee + Template                                 │
│         ↓                                                    │
│  System fetches employee data:                              │
│    - Full Name (from personal_information)                  │
│    - Employee ID (from employment_detail)                   │
│    - Position (from designation)                            │
│    - Department (from department)                           │
│    - Date Hired (from employment_detail)                    │
│         ↓                                                    │
│  Template->generateContract($employee)                      │
│         ↓                                                    │
│  Replace placeholders with actual data                      │
│         ↓                                                    │
│  Save to contracts table (Status: Draft)                    │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│                  3. CONTRACT LIFECYCLE                       │
│                                                              │
│  Draft → Preview → Edit if needed                           │
│       ↓                                                      │
│  Sign Contract (authorized user)                            │
│       ↓                                                      │
│  Status: Active                                             │
│  Signed Date: Current timestamp                             │
│  Signed By: Auth user ID                                    │
│       ↓                                                      │
│  Print/Download for records                                 │
│       ↓                                                      │
│  Monitor status:                                            │
│    - Active: Contract is valid                              │
│    - Expired: Passed end_date                               │
│    - Terminated: Manually ended                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Why Content Field Is Now Optional

**Before:**
- You HAD to manually write HTML/text content with placeholders
- Time-consuming and error-prone

**Now (After Updates):**
- **Option 1**: Use PDF template (Recommended)
  - Just select the PDF file
  - No need to write content
  - System will fill PDF forms with employee data (requires PDF library)

- **Option 2**: Use Text/HTML content
  - For custom contracts not available as PDF
  - Manually write content with placeholders
  - More flexible but requires more work

**The `content` field is nullable** because:
- If using PDF template → `pdf_template_path` is filled, `content` can be NULL
- If using text template → `content` is filled, `pdf_template_path` can be NULL
- When generating contracts, system checks which one is available and uses it

---

## Available Placeholders

When creating templates (text or for PDF filling), these placeholders are available:

| Placeholder | Data Source | Example Output |
|------------|-------------|----------------|
| `{{employee_full_name}}` | personal_information table | "Juan Dela Cruz" |
| `{{employee_id}}` | employment_detail table | "EMP-001" |
| `{{position}}` | designation table | "Software Developer" |
| `{{department}}` | department table | "IT Department" |
| `{{date_hired}}` | employment_detail table | "January 15, 2024" |
| `{{probationary_end_date}}` | Calculated (+6 months from hire) | "July 15, 2024" |
| `{{current_date}}` | Current timestamp | "December 17, 2024" |

---

## Key Files Reference

### Controllers
- `app/Http/Controllers/Tenant/ContractTemplateController.php` - Manage templates
- `app/Http/Controllers/Tenant/ContractController.php` - Manage contracts

### Models
- `app/Models/ContractTemplate.php` - Template logic & data preparation
- `app/Models/Contract.php` - Contract logic & relationships

### Views
- `resources/views/tenant/contract-templates/index.blade.php` - Template list
- `resources/views/tenant/contracts/index.blade.php` - Contract list

### Routes
- `Route::resource('contract-templates', ContractTemplateController::class)`
- `Route::resource('contracts', ContractController::class)`
- `POST /contracts/generate` - Generate contract from template
- `POST /contracts/{contract}/sign` - Sign contract
- `GET /contracts/{contract}/print` - Print contract

### Database Migrations
- `database/migrations/2025_12_16_164212_create_contract_templates_table.php`
- `database/migrations/2025_12_16_165234_create_contracts_table.php`

---

## Next Steps (Optional Enhancements)

### 📄 For PDF Template Support
To actually fill PDF forms, you'll need to:
1. Install PDF library: `composer require setasign/fpdi setasign/fpdf`
2. Create PDF service to fill form fields
3. Update ContractTemplate model to use PDF service
4. Generate filled PDF when creating contracts

### 🔒 For Digital Signatures
1. Add signature field to contracts table
2. Integrate signature pad (e.g., signature_pad.js)
3. Store signature image when signing
4. Display signature on printed contracts

### 📧 For Automated Notifications
1. Send email when contract is generated
2. Notify employee when contract is ready to sign
3. Remind when contract is about to expire

---

## Summary

The contract system now supports **both PDF templates and text templates**, giving you flexibility:

- **Use PDF templates** (your 2 existing PDFs) for standardized contracts
- **Use text templates** for custom or simple contracts
- System automatically handles employee data population
- Full lifecycle management from Draft → Active → Expired
- Integration with employee data (personal info, employment details, position, department)

**You don't need to add content anymore if you select "PDF Template" as the source!** The system will use the PDF file instead.
