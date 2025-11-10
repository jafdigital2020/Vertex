# Billing System Flow Documentation

## Complete System Flow Overview

This document provides a comprehensive view of how billing flows work in the Vertex system, from user actions to payment completion.

## 🔄 Flow Categories

1. [Employee Addition Flows](#employee-addition-flows)
2. [Recurring Invoice Flows](#recurring-invoice-flows)
3. [Payment Processing Flows](#payment-processing-flows)
4. [Plan Upgrade Flows](#plan-upgrade-flows)
5. [Error Handling Flows](#error-handling-flows)

## Employee Addition Flows

### Flow 1: Normal Employee Addition (Within Limits)

```mermaid
graph TD
    A[User clicks 'Add Employee'] --> B[Fill Employee Form]
    B --> C[Submit Form]
    C --> D[checkLicenseOverage API Call]
    D --> E[LicenseOverageService.checkLicenseBeforeAdding()]
    E --> F{Within Plan Limit?}
    F -->|Yes| G[Return status: 'ok']
    G --> H[Frontend: Submit Employee Form]
    H --> I[employeeAdd Controller]
    I --> J[Create User Record]
    J --> K[handleEmployeeActivation()]
    K --> L[Update active_license = true]
    L --> M[Log License Usage]
    M --> N[Return Success Response]
    N --> O[Employee Added Successfully]
```

### Flow 2: Implementation Fee Required (Starter Plan)

```mermaid
graph TD
    A[User clicks 'Add Employee'] --> B[checkLicenseOverage API Call]
    B --> C[Check Implementation Fee Status]
    C --> D{Implementation Fee Paid?}
    D -->|No| E[Return status: 'implementation_fee_required']
    E --> F[Show Implementation Fee Modal]
    F --> G[User clicks 'Pay Implementation Fee']
    G --> H[generateImplementationFeeInvoice API]
    H --> I[Create Implementation Fee Invoice]
    I --> J[Redirect to Payment Gateway]
    J --> K[User Completes Payment]
    K --> L[Payment Webhook Received]
    L --> M[Update implementation_fee_paid]
    M --> N[Allow Employee Creation]
```

### Flow 3: Plan Upgrade Suggested

```mermaid
graph TD
    A[User exceeds plan limit] --> B[checkLicenseOverage]
    B --> C[Calculate upgrade options]
    C --> D[Return status: 'upgrade_suggested']
    D --> E[Show Plan Upgrade Modal]
    E --> F{User Choice}
    F -->|Upgrade Plan| G[generatePlanUpgradeInvoice]
    F -->|Pay Overage| H[Confirm Overage]
    F -->|Cancel| I[Cancel Action]
    G --> J[Create Plan Upgrade Invoice]
    J --> K[Process Payment]
    K --> L[Update Subscription Plan]
    L --> M[Allow Employee Creation]
    H --> N[Create Employee with Overage]
    N --> O[Generate Overage Invoice]
```

### Flow 4: License Overage Confirmation

```mermaid
graph TD
    A[User exceeds plan limit] --> B[checkLicenseOverage]
    B --> C[Calculate overage cost]
    C --> D[Return status: 'overage_confirmation']
    D --> E[Show Overage Modal]
    E --> F[Display: 'Extra cost: ₱49/user']
    F --> G{User Confirms?}
    G -->|Yes| H[Create Employee]
    G -->|No| I[Cancel Action]
    H --> J[handleEmployeeActivation]
    J --> K[Create Overage Invoice]
    K --> L[Employee Added + Invoice Generated]
```

## Recurring Invoice Flows

### Flow 1: Monthly Subscription Renewal

```mermaid
graph TD
    A[7 days before renewal] --> B[Cron: invoices:generate]
    B --> C[Find subscriptions due in 7 days]
    C --> D[For each subscription]
    D --> E[Check existing renewal invoice]
    E --> F{Invoice exists?}
    F -->|No| G[createConsolidatedRenewalInvoice]
    F -->|Yes| H[Skip creation, send email]
    G --> I[Calculate base subscription cost]
    I --> J[Check accumulated overage]
    J --> K[Consolidate into single invoice]
    K --> L[Send email notification]
    L --> M[User receives renewal notice]
    M --> N[User pays before due date]
    N --> O[Subscription renewed]
```

### Flow 2: Yearly Subscription with Monthly Overage

```mermaid
graph TD
    A[User activates beyond yearly limit] --> B[handleEmployeeActivation]
    B --> C{Billing Cycle?}
    C -->|Yearly| D[getCurrentMonthlyPeriod]
    C -->|Monthly| E[Standard overage flow]
    D --> F[createImmediateMonthlyOverageInvoice]
    F --> G[Calculate monthly overage amount]
    G --> H[Create separate overage invoice]
    H --> I[Invoice due immediately]
    I --> J[Continue with yearly renewal separately]
    
    K[Monthly Cron Job] --> L[invoices:generate-monthly-overage]
    L --> M[Process yearly subscriptions]
    M --> N[Calculate monthly periods]
    N --> O[Generate monthly overage invoices]
    O --> P[Skip if in renewal period 7 days]
```

## Payment Processing Flows

### Flow 1: Standard Payment Flow

```mermaid
graph TD
    A[User clicks 'Pay Now'] --> B[initiatePayment API]
    B --> C[Create PaymentTransaction record]
    C --> D[Call HitPay API]
    D --> E[Receive payment URL]
    E --> F[Redirect user to HitPay]
    F --> G[User enters payment details]
    G --> H[HitPay processes payment]
    H --> I{Payment Successful?}
    I -->|Yes| J[HitPay calls webhook]
    I -->|No| K[Payment failed]
    J --> L[webhook receives status]
    L --> M[Update PaymentTransaction]
    M --> N[Update Invoice status = 'paid']
    N --> O[updateSubscription if renewal]
    O --> P[Send confirmation email]
    K --> Q[Update status = 'failed']
    Q --> R[Allow retry]
```

### Flow 2: Webhook Processing Flow

```mermaid
graph TD
    A[Webhook received] --> B[Verify signature]
    B --> C{Signature valid?}
    C -->|No| D[Return 403 error]
    C -->|Yes| E[Find PaymentTransaction]
    E --> F{Transaction found?}
    F -->|No| G[Log error, return 404]
    F -->|Yes| H[Update transaction status]
    H --> I[Find related invoice]
    I --> J[updateInvoiceAndSubscription]
    J --> K{Invoice type?}
    K -->|subscription| L[Renew subscription]
    K -->|plan_upgrade| M[Process plan upgrade]
    K -->|implementation_fee| N[Update implementation fee]
    K -->|license_overage| O[Update overage status]
    L --> P[Update next_renewal_date]
    M --> Q[Change plan_id]
    N --> R[Set implementation_fee_paid]
    O --> S[Mark overage as paid]
    P --> T[Send confirmation]
    Q --> T
    R --> T
    S --> T
```

## Plan Upgrade Flows

### Flow 1: Mid-Cycle Plan Upgrade

```mermaid
graph TD
    A[User selects plan upgrade] --> B[getAvailablePlans API]
    B --> C[Calculate prorated amounts]
    C --> D[Display upgrade options]
    D --> E[User selects new plan]
    E --> F[Show upgrade confirmation]
    F --> G[User confirms upgrade]
    G --> H[generatePlanUpgradeInvoice]
    H --> I[Calculate current period remaining]
    I --> J[Calculate refund amount]
    J --> K[Calculate new plan prorated cost]
    K --> L[Create upgrade invoice]
    L --> M[Process payment]
    M --> N{Payment successful?}
    N -->|Yes| O[Update subscription plan_id]
    N -->|No| P[Keep current plan]
    O --> Q[Update license limits]
    Q --> R[Send upgrade confirmation]
```

### Flow 2: Automatic Upgrade Suggestion

```mermaid
graph TD
    A[Multiple users exceed limit] --> B[checkLicenseOverage]
    B --> C[Calculate overage costs]
    C --> D[Find better plan options]
    D --> E{Upgrade cheaper than overage?}
    E -->|Yes| F[Return upgrade_suggested]
    E -->|No| G[Return overage_confirmation]
    F --> H[Display upgrade comparison]
    H --> I[Show cost savings]
    I --> J{User chooses upgrade?}
    J -->|Yes| K[Process upgrade]
    J -->|No| L[Proceed with overage]
    K --> M[Plan upgrade flow]
    L --> N[Overage flow]
```

## Error Handling Flows

### Flow 1: Payment Failure Recovery

```mermaid
graph TD
    A[Payment fails] --> B[Update transaction status]
    B --> C[Keep invoice status = 'pending']
    C --> D[Send failure notification]
    D --> E[User receives failure email]
    E --> F[User retries payment]
    F --> G{Retry successful?}
    G -->|Yes| H[Normal payment flow]
    G -->|No| I[Multiple failures]
    I --> J[Account restrictions]
    J --> K[Manual intervention required]
```

### Flow 2: Invoice Generation Failure

```mermaid
graph TD
    A[Invoice generation fails] --> B[Log error details]
    B --> C[Send admin notification]
    C --> D[Queue retry job]
    D --> E[Retry after delay]
    E --> F{Retry successful?}
    F -->|Yes| G[Normal flow continues]
    F -->|No| H[Manual investigation]
    H --> I[Admin fixes issue]
    I --> J[Manual invoice generation]
```

### Flow 3: License Count Mismatch

```mermaid
graph TD
    A[System detects mismatch] --> B[Log discrepancy]
    B --> C[Count actual active users]
    C --> D[Compare with recorded count]
    D --> E[Update subscription.active_license]
    E --> F[Check if adjustment needed]
    F --> G{Overage created?}
    G -->|Yes| H[Generate adjustment invoice]
    G -->|No| I[Update counts only]
    H --> J[Notify admin and user]
    I --> K[Log correction]
```

## Integration Points

### Frontend Integration Points

1. **Employee Management**
   - Pre-submission license checking
   - Modal dialogs for overage/upgrade
   - Payment initiation

2. **Billing Dashboard**
   - Usage display
   - Invoice listing
   - Payment buttons

3. **Subscription Management**
   - Plan comparison
   - Upgrade flows
   - Billing cycle switching

### Backend Integration Points

1. **Employee Controllers**
   - License checking middleware
   - Post-creation activation
   - Deactivation handling

2. **Billing Controllers**
   - Invoice generation
   - Payment processing
   - Subscription management

3. **Services Integration**
   - LicenseOverageService (core)
   - HitPayService (payments)
   - NotificationService (emails)

### Database Integration Points

1. **Transaction Management**
   - Multi-table updates
   - Rollback on failure
   - Consistency checks

2. **Audit Trail**
   - License usage logs
   - Payment transaction logs
   - Subscription change logs

3. **Performance Optimization**
   - Indexed queries
   - Chunked processing
   - Background jobs

## Monitoring & Alerting Flow

```mermaid
graph TD
    A[System Events] --> B[Application Logs]
    B --> C[Log Aggregation]
    C --> D{Alert Conditions?}
    D -->|Payment failure rate > 5%| E[Alert: Payment issues]
    D -->|Invoice generation fails| F[Alert: Billing system error]
    D -->|License mismatches detected| G[Alert: Data inconsistency]
    D -->|Overage threshold exceeded| H[Alert: Usage spike]
    E --> I[Admin notification]
    F --> I
    G --> I
    H --> I
    I --> J[Investigation & resolution]
    J --> K[System correction]
    K --> L[Monitor resolution]
```

## Data Consistency Flows

### Flow 1: Daily Reconciliation

```mermaid
graph TD
    A[Daily Cron Job] --> B[Count actual active users]
    B --> C[Compare with subscription.active_license]
    C --> D{Discrepancies found?}
    D -->|Yes| E[Update recorded counts]
    D -->|No| F[Generate consistency report]
    E --> G[Check if overage invoices needed]
    G --> H[Generate adjustment invoices]
    H --> I[Notify administrators]
    I --> F
```

### Flow 2: Post-Payment Validation

```mermaid
graph TD
    A[Payment completed] --> B[Validate subscription status]
    B --> C[Check license counts]
    C --> D[Verify invoice amounts]
    D --> E{All checks pass?}
    E -->|Yes| F[Complete transaction]
    E -->|No| G[Flag for review]
    F --> H[Send confirmation]
    G --> I[Admin investigation]
    I --> J[Manual correction]
    J --> K[Update audit log]
```

This comprehensive flow documentation covers all major billing scenarios and their interactions within the Vertex system. Each flow is designed to handle specific business requirements while maintaining data integrity and user experience.
