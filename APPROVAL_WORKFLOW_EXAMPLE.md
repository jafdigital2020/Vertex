# 🔄 Dynamic Approval Workflow Example

## Real-World Scenario: Software Developer Hiring

Let's walk through a complete example of hiring a Software Developer at Branch 1.

### 📊 Initial Setup

**Branch Configuration (approval_steps table):**
```sql
-- Branch 1 has 3-level approval for recruitment
id | branch_id | level | approver_kind    | approver_user_id
1  | 1         | 1     | department_head  | NULL
2  | 1         | 2     | user            | 25 (HR Manager)  
3  | 1         | 3     | user            | 5  (COO)
```

**Department Setup:**
```sql
-- IT Department  
departments: id=10, name='IT Department', head_of_department=15
users: id=15, name='John Smith', role='IT Manager'
users: id=25, name='Sarah Wilson', role='HR Manager'  
users: id=5,  name='Mike Johnson', role='COO'
```

## 🎯 Step-by-Step Process

### Step 1: Manpower Request Creation
IT Manager (John, ID=15) creates a manpower request:

```php
// Database record created
manpower_requests:
id: 100
branch_id: 1
request_number: 'MR-2025-001'
position: 'Software Developer'
department_id: 10
requested_by: 15 (John Smith)
status: 'pending'
```

**Automatic Approval Workflow Initialization:**
```php
// RecruitmentApprovalService automatically creates 3 approval records
recruitment_approvals:

Record 1:
├── approvable_type: 'App\Models\ManpowerRequest'
├── approvable_id: 100
├── branch_id: 1  
├── level: 1
├── status: 'pending'
├── approver_id: 15 (auto-assigned as dept head)

Record 2:
├── approvable_type: 'App\Models\ManpowerRequest'  
├── approvable_id: 100
├── level: 2
├── status: 'pending'
├── approver_id: 25 (HR Manager)

Record 3:
├── approvable_type: 'App\Models\ManpowerRequest'
├── approvable_id: 100  
├── level: 3
├── status: 'pending'
├── approver_id: 5 (COO)
```

### Step 2: Level 1 Approval (Department Head)
John Smith (IT Manager) approves the request:

```php
// Approval processing
$approvalService->processApproval(
    $manpowerRequest, 
    $level = 1, 
    $approverID = 15, 
    $action = 'approved',
    $comments = 'We need 2 developers for the new mobile app project'
);

// Database updates
recruitment_approvals (Record 1):
├── status: 'approved' ✅
├── approver_id: 15
├── approved_at: '2025-11-25 10:30:00'
├── comments: 'We need 2 developers for the new mobile app project'
```

**Notification System:**
```php
// Automatic notification to next approver
Mail::to('sarah.wilson@company.com')->send(new ApprovalPending($manpowerRequest, 2));
```

### Step 3: Level 2 Approval (HR Manager)  
Sarah Wilson (HR Manager) reviews and approves:

```php
$approvalService->processApproval($manpowerRequest, 2, 25, 'approved', 'Budget approved, position needed');

// Database updates  
recruitment_approvals (Record 2):
├── status: 'approved' ✅
├── approved_at: '2025-11-25 14:15:00'
├── comments: 'Budget approved, position needed'

// Notification to COO
Mail::to('mike.johnson@company.com')->send(new ApprovalPending($manpowerRequest, 3));
```

### Step 4: Level 3 Final Approval (COO)
Mike Johnson (COO) gives final approval:

```php
$approvalService->processApproval($manpowerRequest, 3, 5, 'approved', 'Approved for Q4 hiring');

// All approvals complete - manpower request status changes
manpower_requests (id=100):
├── status: 'approved' ✅
├── approved_by: 5
├── approved_at: '2025-11-25 16:45:00'
```

## 📝 Step 5: Job Posting Creation
HR creates the job posting from approved request:

```php
job_postings:
id: 50
├── branch_id: 1
├── job_code: 'MR-MR-2025-001' 
├── title: 'Software Developer'
├── department_id: 10
├── status: 'open'
├── created_by: 25 (Sarah - HR)
├── posted_date: '2025-11-25'

// Link back to manpower request
manpower_requests (id=100):
├── job_posting_id: 50 ✅
├── status: 'posted'
```

## 👤 Step 6: Candidate Application Process

**Candidate Registration:**
```php
candidates:
id: 200
├── candidate_code: 'CAND-2025-001'
├── email: 'alex.developer@email.com'
├── name: 'Alex Rodriguez'  
├── branch_id: 1
├── status: 'new'
```

**Job Application:**
```php
job_applications:
id: 300
├── application_code: 'APP-2025-001'
├── job_posting_id: 50
├── candidate_id: 200
├── status: 'applied'
├── applied_at: '2025-11-26 09:00:00'

// Workflow tracking
application_workflow:
├── job_application_id: 300
├── from_status: NULL
├── to_status: 'applied'  
├── changed_by: 200 (candidate)
├── notes: 'Initial application'
```

## 🔍 Step 7: Interview Process
HR schedules technical interview:

```php
interviews:
id: 400
├── interview_code: 'INT-2025-001'
├── job_application_id: 300
├── type: 'technical'
├── scheduled_at: '2025-11-28 14:00:00'
├── primary_interviewer: 15 (John - IT Manager)
├── status: 'scheduled'

// Application status update
job_applications (id=300):
├── status: 'interview_scheduled' ✅

application_workflow:
├── from_status: 'under_review'
├── to_status: 'interview_scheduled'
├── changed_by: 25 (HR)
```

## 💼 Step 8: Job Offer with Approval Workflow
After successful interview, HR creates job offer:

```php
job_offers:
id: 500
├── offer_code: 'OFFER-2025-001'  
├── job_application_id: 300
├── salary_offered: 65000.00
├── start_date: '2025-12-15'
├── status: 'draft'
├── prepared_by: 25 (HR)
```

**Another Approval Workflow Triggered:**
```php
// Same 3-level approval for job offers
recruitment_approvals:

Record 4: (Level 1 - Dept Head)
├── approvable_type: 'App\Models\JobOffer'
├── approvable_id: 500
├── level: 1, approver_id: 15
├── status: 'pending'

Record 5: (Level 2 - HR Manager) 
├── level: 2, approver_id: 25
├── status: 'pending'

Record 6: (Level 3 - COO)
├── level: 3, approver_id: 5  
├── status: 'pending'
```

## 🎯 Step 9: Offer Approval Chain

**Level 1:** John approves salary range ✅  
**Level 2:** Sarah approves offer terms ✅  
**Level 3:** Mike gives final approval ✅

```php
// All approvals complete
job_offers (id=500):
├── status: 'approved' ✅

// Offer sent to candidate  
├── status: 'sent'
├── sent_at: '2025-11-29 10:00:00'

Mail::to('alex.developer@email.com')->send(new JobOfferSent($jobOffer));
```

## ✅ Step 10: Candidate Accepts & Hiring Complete

```php
// Candidate accepts offer
job_offers (id=500):
├── status: 'accepted' ✅
├── responded_at: '2025-11-30 15:30:00'

job_applications (id=300):
├── status: 'hired' ✅

application_workflow:
├── from_status: 'offer_made'
├── to_status: 'hired'
├── changed_by: 200 (candidate acceptance)

// Optional: Convert to employee
users:
├── branch_id: 1
├── department_id: 10  
├── email: 'alex.developer@email.com'
├── role: 'Software Developer'
```

## 📊 Complete Audit Trail

The system maintains complete visibility:

```php
// Manpower request approval history
$manpowerApprovals = RecruitmentApproval::where('approvable_type', ManpowerRequest::class)
    ->where('approvable_id', 100)
    ->with(['approver'])
    ->get();

// Shows:
// Level 1: John Smith approved on 2025-11-25 10:30 ✅
// Level 2: Sarah Wilson approved on 2025-11-25 14:15 ✅  
// Level 3: Mike Johnson approved on 2025-11-25 16:45 ✅

// Job offer approval history  
$offerApprovals = RecruitmentApproval::where('approvable_type', JobOffer::class)
    ->where('approvable_id', 500)
    ->get();

// Application workflow history
$workflowHistory = ApplicationWorkflow::where('job_application_id', 300)
    ->orderBy('created_at')
    ->get();

// Complete timeline from application to hire
```

## 🔐 Permission & Access Control Throughout

**Data Access Examples:**
```php
// John (IT Manager) - Department Level Access
// Can see: Only IT department job postings & applications  

// Sarah (HR Manager) - Organization Level Access  
// Can see: All job postings & applications across all departments

// Mike (COO) - Organization Level Access
// Can see: All recruitment data for approval decisions

// Regular HR Assistant - Branch Level Access
// Can see: Only same branch recruitment data
```

**Permission Checks:**
```php
// Before any action, system checks:
Route::middleware('checkPermission:58-1')->group(function() {
    // Sub-module 58 (Job Postings), Operation 1 (Create)
    Route::post('/job-postings', [JobPostingController::class, 'store']);
});

Route::middleware('checkPermission:62-2')->group(function() {
    // Sub-module 62 (Job Offers), Operation 2 (Read) 
    Route::get('/job-offers', [JobOfferController::class, 'index']);
});
```

This example shows how your recruitment module integrates seamlessly with the existing dynamic approval system, permission structure, and branch isolation used throughout your HR application. Every action is tracked, controlled, and auditable.