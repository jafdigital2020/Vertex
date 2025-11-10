# Billing System Architecture Guide

## System Architecture Overview

The Vertex billing system follows a service-oriented architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────┐
│                   Frontend Layer                        │
│  ┌─────────────┬─────────────┬─────────────┬──────────┐ │
│  │   Employee  │   Billing   │Subscription │ Payment  │ │
│  │ Management  │ Dashboard   │ Management  │Processing│ │
│  └─────────────┴─────────────┴─────────────┴──────────┘ │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                 Controller Layer                        │
│  ┌─────────────┬─────────────┬─────────────┬──────────┐ │
│  │EmployeeList │   Billing   │Subscription │ Payment  │ │
│  │ Controller  │ Controller  │ Controller  │Controller│ │
│  └─────────────┴─────────────┴─────────────┴──────────┘ │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                 Service Layer                           │
│  ┌───────────────────────────────────────────────────┐ │
│  │         LicenseOverageService                     │ │
│  │  ┌─────────────┬─────────────┬─────────────────┐  │ │
│  │  │   License   │   Invoice   │    Payment      │  │ │
│  │  │ Management  │ Generation  │   Processing    │  │ │
│  │  └─────────────┴─────────────┴─────────────────┘  │ │
│  └───────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                   Model Layer                           │
│  ┌─────────────┬─────────────┬─────────────┬──────────┐ │
│  │Subscription │   Invoice   │     Plan    │   User   │ │
│  │             │             │             │          │ │
│  └─────────────┴─────────────┴─────────────┴──────────┘ │
└─────────────────────────────────────────────────────────┘
```

## Core Service: LicenseOverageService

### Primary Responsibilities

1. **License Usage Tracking**: Monitor active users and license consumption
2. **Overage Detection**: Identify when usage exceeds plan limits
3. **Invoice Generation**: Create various types of invoices automatically
4. **Implementation Fee Management**: Handle one-time setup charges
5. **Plan Upgrade Processing**: Manage plan changes with prorated billing

### Key Methods

```php
class LicenseOverageService
{
    // License Management
    public function checkLicenseBeforeAdding($tenantId, $newUserCount = 1)
    public function handleEmployeeActivation($userId)
    public function handleEmployeeDeactivation($userId)
    
    // Invoice Generation
    public function checkAndCreateOverageInvoice($tenantId)
    public function createImplementationFeeInvoice($subscription, $implementationFee)
    public function createPlanUpgradeInvoice($subscription, $newPlan, $proratedAmount = 0)
    public function createConsolidatedRenewalInvoice($subscription)
    
    // Overage Calculations
    public function calculateMonthlyOverageLicenses($tenantId, $period)
    public function getCurrentMonthlyPeriod($subscription)
    
    // Implementation Fee Management
    public function getRequiredImplementationFee($subscription, $newUserCount)
}
```

## Billing Flow Diagrams

### 1. Employee Addition Flow

```
User clicks "Add Employee"
         │
         ▼
Check License Status
    ┌────┴────┐
    │ Service │ checkLicenseBeforeAdding()
    └────┬────┘
         │
         ▼
┌────────────────────┐
│   Evaluation       │
│                    │
│ • Current users    │
│ • Plan limits      │
│ • Implementation   │
│   fee status       │
│ • Available        │
│   upgrades         │
└────────┬───────────┘
         │
         ▼
    Decision Tree:
    
    Within Limit?
    ├─ Yes → Create Employee
    │
    ├─ Implementation Fee Required?
    │  └─ Yes → Show Implementation Fee Modal
    │          └─ Pay Fee → Create Employee
    │
    ├─ Upgrade Available?
    │  └─ Yes → Show Upgrade Modal
    │          └─ Choose Plan → Pay Difference → Create Employee
    │
    └─ Overage Allowed?
       ├─ Yes → Show Overage Confirmation
       │        └─ Accept Fee → Create Employee
       │
       └─ No → Contact Sales Message
```

### 2. Invoice Generation Flow

```
┌─────────────────────┐
│   Trigger Event     │
│                     │
│ • Employee Added    │
│ • Renewal Date      │
│ • Manual Request    │
│ • Scheduled Cron    │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ LicenseOverageService│
│ checkAndCreateOverage │
│       Invoice()     │
└──────────┬──────────┘
           │
           ▼
    Calculate Usage:
    ┌─────────────────┐
    │ • Active users  │
    │ • Plan limit    │
    │ • Billing period│
    │ • Overage count │
    └─────────┬───────┘
              │
              ▼
    Generate Invoice:
    ┌─────────────────┐
    │ • Invoice type  │
    │ • Amount due    │
    │ • VAT calc      │
    │ • Period dates  │
    └─────────┬───────┘
              │
              ▼
    ┌─────────────────┐
    │ Save to Database│
    │ Send Notification│
    │ Log Transaction │
    └─────────────────┘
```

### 3. Payment Processing Flow

```
User clicks "Pay Invoice"
         │
         ▼
┌─────────────────────┐
│ PaymentController   │
│ initiatePayment()   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│   HitPay Gateway   │
│                     │
│ • Create payment    │
│ • Generate URL      │
│ • Set webhook       │
└──────────┬──────────┘
           │
           ▼
    User Payment:
    ┌─────────────────┐
    │ • Card details  │
    │ • Authentication│
    │ • Processing    │
    └─────────┬───────┘
              │
              ▼
    ┌─────────────────┐
    │ Webhook Received│
    │                 │
    │ • Status update │
    │ • Invoice paid  │
    │ • Subscription  │
    │   renewal       │
    └─────────┬───────┘
              │
              ▼
    ┌─────────────────┐
    │ Post-Payment    │
    │                 │
    │ • Update status │
    │ • Log usage     │
    │ • Send receipt  │
    └─────────────────┘
```

## Data Models Relationships

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Tenant    │────▶│Subscription │────▶│    Plan     │
│             │     │             │     │             │
│ • id        │     │ • tenant_id │     │ • id        │
│ • name      │     │ • plan_id   │     │ • name      │
│ • domain    │     │ • status    │     │ • price     │
└─────────────┘     │ • next_     │     │ • employee_ │
        │           │   renewal   │     │   limit     │
        │           └─────────────┘     └─────────────┘
        │                   │
        ▼                   ▼
┌─────────────┐     ┌─────────────┐
│    User     │     │   Invoice   │
│             │     │             │
│ • tenant_id │     │ • subscription_id
│ • active_   │     │ • invoice_type
│   license   │     │ • amount_due
│ • status    │     │ • overage_count
└─────────────┘     │ • status    │
        │           └─────────────┘
        │                   │
        ▼                   ▼
┌─────────────┐     ┌─────────────┐
│LicenseUsage │     │ Payment     │
│    Log      │     │Transaction  │
│             │     │             │
│ • user_id   │     │ • invoice_id│
│ • activated │     │ • amount    │
│ • deactivated│    │ • status    │
│ • period    │     │ • paid_at   │
└─────────────┘     └─────────────┘
```

## Invoice Types and Triggers

### 1. Subscription Invoice
**Trigger**: Automated renewal (7 days before due date)
```php
// Generated by: GenerateInvoices Command
// Schedule: Daily cron job
// Includes: Base subscription + consolidated overage
```

### 2. License Overage Invoice
**Trigger**: User activation beyond plan limit
```php
// Generated by: handleEmployeeActivation()
// Immediate: When overage detected
// Amount: Overage count × ₱49
```

### 3. Implementation Fee Invoice
**Trigger**: First user addition on Starter plan
```php
// Generated by: generateImplementationFeeInvoice()
// Manual: Via employee addition flow
// Amount: ₱5,000 (Starter plan)
```

### 4. Plan Upgrade Invoice
**Trigger**: User chooses plan upgrade
```php
// Generated by: generatePlanUpgradeInvoice()
// Manual: Via subscription management
// Amount: Prorated difference
```

## Billing Cycles

### Monthly Subscriptions
```
Subscription Period: Month 1
├─ Day 1-30: Usage tracking
├─ Day 23: Renewal invoice generated (7 days before)
├─ Day 30: Current period ends
└─ Day 31: New period begins (if paid)

Overage Billing:
├─ Real-time: When limit exceeded
└─ Immediate: Overage invoice created
```

### Yearly Subscriptions
```
Subscription Period: Year 1
├─ Month 1-12: Monthly overage tracking
├─ Each Month: Separate overage invoices
├─ Month 12 Day 23: Renewal invoice generated
└─ Year end: New subscription period

Example Timeline:
├─ Jan: 105 users → Overage invoice (5 × ₱49)
├─ Feb: 108 users → Overage invoice (8 × ₱49)
├─ Mar: 95 users → No overage invoice
└─ Dec 23: Renewal invoice (₱25,000 base)
```

## Configuration Management

### Environment Settings
```env
# Core billing settings
OVERAGE_RATE_PER_LICENSE=49.00
DEFAULT_VAT_PERCENTAGE=12.00

# Plan-specific settings
STARTER_IMPLEMENTATION_FEE=5000.00
BASIC_IMPLEMENTATION_FEE=0.00
PRO_IMPLEMENTATION_FEE=0.00
ELITE_IMPLEMENTATION_FEE=0.00

# Payment gateway
HITPAY_API_KEY=your_api_key
HITPAY_SALT=your_salt_key
HITPAY_BASE_URL=https://api.hit-pay.com
```

### Database Configuration
```sql
-- Plan configuration
INSERT INTO plans (name, price, employee_limit, implementation_fee, billing_cycle) VALUES
('Starter Monthly', 1500.00, 5, 5000.00, 'monthly'),
('Basic Monthly', 3000.00, 15, 0.00, 'monthly'),
('Pro Monthly', 7500.00, 50, 0.00, 'monthly'),
('Elite Yearly', 25000.00, 100, 0.00, 'yearly');
```

## Automated Tasks

### 1. Daily Renewal Invoice Generation
```bash
# Crontab entry
0 0 * * * cd /path/to/vertex && php artisan invoices:generate

# What it does:
# - Finds subscriptions due in 7 days
# - Creates consolidated renewal invoices
# - Sends email notifications
# - Logs all operations
```

### 2. Monthly Overage Invoice Generation
```bash
# Crontab entry
0 2 * * * cd /path/to/vertex && php artisan invoices:generate-monthly-overage

# What it does:
# - Processes yearly subscriptions
# - Calculates monthly overage
# - Creates overage invoices
# - Skips renewal periods (7 days before yearly renewal)
```

### 3. License Usage Cleanup
```bash
# Custom command (recommended monthly)
0 3 1 * * cd /path/to/vertex && php artisan license:cleanup-logs

# What it should do:
# - Archive old license usage logs
# - Clean up inactive user records
# - Optimize license counting performance
```

## Error Handling and Monitoring

### Critical Error Scenarios

1. **Invoice Generation Failure**
```php
try {
    $invoice = $this->createOverageInvoice($tenantId);
} catch (\Exception $e) {
    Log::error('Invoice generation failed', [
        'tenant_id' => $tenantId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Fallback: Queue for retry
    dispatch(new GenerateInvoiceJob($tenantId))->delay(60);
}
```

2. **Payment Webhook Failure**
```php
// PaymentController webhook method includes:
DB::transaction(function() use ($paymentData) {
    $this->updateInvoiceStatus($paymentData);
    $this->updateSubscriptionStatus($paymentData);
    $this->logPaymentTransaction($paymentData);
});
```

3. **License Count Mismatch**
```php
// Regular reconciliation needed:
$actualCount = User::where('tenant_id', $tenantId)
    ->where('active_license', true)
    ->count();

$recordedCount = $subscription->active_license;

if ($actualCount !== $recordedCount) {
    Log::warning('License count mismatch detected', [
        'tenant_id' => $tenantId,
        'actual_count' => $actualCount,
        'recorded_count' => $recordedCount
    ]);
}
```

### Monitoring Queries

```sql
-- Monitor active subscriptions
SELECT 
    COUNT(*) as active_subscriptions,
    billing_cycle,
    AVG(active_license) as avg_licenses
FROM subscriptions 
WHERE status = 'active' 
GROUP BY billing_cycle;

-- Check pending invoices
SELECT 
    COUNT(*) as pending_count,
    SUM(amount_due) as total_pending,
    invoice_type
FROM invoices 
WHERE status = 'pending' 
GROUP BY invoice_type;

-- Usage vs Plan Limits
SELECT 
    s.id as subscription_id,
    p.name as plan_name,
    p.employee_limit,
    s.active_license,
    (s.active_license - p.employee_limit) as overage
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
WHERE s.active_license > p.employee_limit;
```

This architecture ensures scalable, maintainable billing operations with comprehensive error handling and monitoring capabilities.
