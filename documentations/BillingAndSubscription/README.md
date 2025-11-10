# Vertex Billing & Subscription System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Core Components](#core-components)
3. [Billing Scenarios](#billing-scenarios)
4. [Implementation Guide](#implementation-guide)
5. [User Guide](#user-guide)
6. [Technical Reference](#technical-reference)
7. [Troubleshooting](#troubleshooting)

## System Overview

The Vertex billing and subscription system is a comprehensive multi-tenant SaaS billing solution that handles:

- **License-based billing** with overage management
- **Implementation fees** for plan onboarding
- **Plan upgrades** with prorated billing
- **Recurring invoices** (monthly/yearly cycles)
- **Automated invoice generation** with scheduled commands
- **Payment processing** via HitPay gateway
- **Usage tracking** and reporting

### Key Features

✅ **Dynamic License Management**: Automatic tracking of active users and license consumption  
✅ **Overage Billing**: Additional charges for exceeding plan limits  
✅ **Implementation Fees**: One-time setup charges for specific plans  
✅ **Plan Upgrades**: Seamless upgrading with prorated billing  
✅ **Recurring Billing**: Automated monthly/yearly invoice generation  
✅ **Consolidated Invoicing**: Combined subscription + overage billing  
✅ **Payment Integration**: HitPay payment gateway with webhook support  
✅ **Usage Analytics**: Detailed usage tracking and reporting  

## Core Components

### 1. Models

#### Subscription Model
```php
// Key fields:
- tenant_id
- plan_id
- billing_cycle (monthly/yearly)
- active_license (current license count)
- base_license_count (included in plan)
- overage_license_count (additional licenses)
- next_renewal_date
- implementation_fee_paid
```

#### Plan Model
```php
// Key fields:
- name
- price
- employee_limit (license limit)
- implementation_fee
- price_per_license (for overage)
- billing_cycle
- vat_percentage
```

#### Invoice Model
```php
// Key fields:
- invoice_type (subscription/license_overage/plan_upgrade/implementation_fee)
- amount_due
- license_overage_count
- license_overage_amount
- implementation_fee
- vat_amount
- period_start/period_end
```

### 2. Services

#### LicenseOverageService
Primary service handling all billing logic:

- License usage tracking
- Overage calculations
- Invoice generation
- Implementation fee management
- Plan upgrade processing

### 3. Controllers

- **BillingController**: Main billing dashboard
- **SubscriptionController**: Plan management and upgrades
- **PaymentController**: Payment processing
- **EmployeeListController**: License overage checking

## Billing Scenarios

### Scenario 1: License Overage Billing

**When it happens**: When active users exceed plan limits

#### Monthly Subscriptions
```
Plan Limit: 10 users
Active Users: 13 users
Overage: 3 users × ₱49/user = ₱147
```

#### Yearly Subscriptions
```
Monthly overage invoices generated separately
Plan: Elite Yearly (100 users)
Month 1 Usage: 105 users → Overage: 5 × ₱49 = ₱245
Month 2 Usage: 108 users → Overage: 8 × ₱49 = ₱392
```

### Scenario 2: Implementation Fee

**When it happens**: New users on Starter plan requiring implementation fee

```
Starter Plan Implementation Fee: ₱5,000
- Generated when adding first employee
- One-time charge per subscription
- Must be paid before user creation
```

### Scenario 3: Plan Upgrades

**When it happens**: User exceeds current plan capacity and chooses upgrade

```
Current: Basic Monthly (₱1,500/month, 5 users)
Upgrade to: Pro Monthly (₱3,000/month, 15 users)

Calculation:
- Current plan remaining days: 15 days
- Prorated refund: (₱1,500 ÷ 30) × 15 = ₱750
- New plan prorated cost: (₱3,000 ÷ 30) × 15 = ₱1,500
- Amount due: ₱1,500 - ₱750 = ₱750
```

### Scenario 4: Recurring Invoices

**When it happens**: Automated billing cycle renewals

#### Standard Renewal
```
Generated 7 days before renewal date
Includes: Base subscription + any accumulated overage
```

#### Consolidated Renewal
```
For yearly plans:
- Main subscription invoice (yearly)
- Monthly overage invoices (if any)
- Consolidated into single payment
```

## Implementation Guide

### 1. Setting Up Plans

```php
// Database seeder example
Plan::create([
    'name' => 'Starter Monthly',
    'price' => 1500.00,
    'employee_limit' => 5,
    'implementation_fee' => 5000.00,
    'billing_cycle' => 'monthly',
    'vat_percentage' => 12.00
]);
```

### 2. License Checking Integration

```javascript
// Frontend: Check before adding employee
$('#addEmployeeForm').on('submit', function (e) {
    e.preventDefault();
    checkLicenseOverageBeforeAdd($(this));
});

function checkLicenseOverageBeforeAdd(form) {
    $.ajax({
        url: '/employees/check-license-overage',
        type: 'POST',
        success: function (response) {
            if (response.status === 'implementation_fee_required') {
                showImplementationFeeModal(response.data, form);
            } else if (response.status === 'upgrade_required') {
                showPlanUpgradeModal(response.data, form);
            } else if (response.status === 'overage_confirmation') {
                showOverageConfirmation(response.data, form);
            } else {
                submitEmployeeForm(form);
            }
        }
    });
}
```

### 3. Automated Invoice Generation

```bash
# Cron setup for automated billing
# Add to crontab:
0 0 * * * cd /path/to/vertex && php artisan invoices:generate
0 2 * * * cd /path/to/vertex && php artisan invoices:generate-monthly-overage
```

### 4. Payment Integration

```php
// Payment initiation
Route::post('/billing/payment/initiate/{invoice}', 
    [PaymentController::class, 'initiatePayment']
);

// Webhook handling
Route::post('/hitpay/webhook', 
    [PaymentController::class, 'webhook']
);
```

## User Guide

### For End Users

#### 1. Adding Employees

1. **Navigate** to Employee Management
2. **Click** "Add Employee" button
3. **Fill** employee details
4. **Submit** form

**System will automatically check**:
- Current license usage
- Plan limits
- Required fees

**Possible outcomes**:
- ✅ **Employee added successfully** (within limits)
- ⚠️ **Implementation fee required** (Starter plan)
- ⚠️ **Plan upgrade suggested** (better value)
- ⚠️ **Overage fee applies** (₱49/user)
- ❌ **Contact sales required** (Elite plan limits)

#### 2. Viewing Bills

1. **Navigate** to Billing & Payment
2. **View** current usage summary
3. **Check** pending invoices
4. **Download** invoice PDFs

#### 3. Making Payments

1. **Click** "Pay Now" on pending invoice
2. **Review** payment details
3. **Complete** payment via HitPay
4. **Receive** confirmation

#### 4. Upgrading Plans

1. **Navigate** to Subscriptions
2. **View** available plans
3. **Select** desired plan
4. **Confirm** upgrade details
5. **Pay** prorated amount

### For Administrators

#### 1. Monitoring Usage

```sql
-- Check current usage per tenant
SELECT 
    t.name as tenant_name,
    s.plan_id,
    p.name as plan_name,
    p.employee_limit as plan_limit,
    COUNT(u.id) as active_users,
    (COUNT(u.id) - p.employee_limit) as overage
FROM subscriptions s
JOIN tenants t ON s.tenant_id = t.id
JOIN plans p ON s.plan_id = p.id
JOIN users u ON u.tenant_id = t.id AND u.active_license = 1
GROUP BY s.id;
```

#### 2. Manual Invoice Generation

```php
// Generate specific invoice types
$service = new LicenseOverageService();

// Implementation fee invoice
$invoice = $service->createImplementationFeeInvoice($subscription, $amount);

// Plan upgrade invoice
$invoice = $service->createPlanUpgradeInvoice($subscription, $newPlan);

// Monthly overage invoice
$invoice = $service->createImmediateMonthlyOverageInvoice($subscription, $period);
```

#### 3. Monitoring Commands

```bash
# Check renewal invoice generation
php artisan invoices:generate --dry-run

# Generate monthly overage invoices
php artisan invoices:generate-monthly-overage --dry-run

# View logs
tail -f storage/logs/laravel.log | grep -i "invoice\|billing"
```

## Technical Reference

### API Endpoints

#### License Management
```
POST /employees/check-license-overage
POST /employees/generate-implementation-fee-invoice
POST /employees/generate-plan-upgrade-invoice
```

#### Billing
```
GET  /billing/
POST /billing/payment/initiate/{invoice}
GET  /billing/payment/return/{invoice}
POST /hitpay/webhook
```

#### Subscriptions
```
GET  /subscriptions
GET  /subscriptions/available-plans
POST /subscriptions/upgrade
GET  /subscriptions-filter
```

### Database Schema

#### Subscriptions Table
```sql
CREATE TABLE subscriptions (
    id bigint PRIMARY KEY,
    tenant_id bigint,
    plan_id bigint,
    billing_cycle enum('monthly', 'yearly'),
    active_license int,
    base_license_count int,
    overage_license_count int,
    implementation_fee_paid decimal(10,2),
    next_renewal_date date,
    status enum('active', 'expired', 'trial', 'canceled')
);
```

#### Invoices Table
```sql
CREATE TABLE invoices (
    id bigint PRIMARY KEY,
    tenant_id bigint,
    subscription_id bigint,
    invoice_type enum('subscription', 'license_overage', 'plan_upgrade', 'implementation_fee'),
    amount_due decimal(10,2),
    license_overage_count int,
    license_overage_amount decimal(10,2),
    implementation_fee decimal(10,2),
    vat_amount decimal(10,2),
    period_start date,
    period_end date,
    status enum('pending', 'paid', 'failed', 'consolidated')
);
```

### Configuration

#### Environment Variables
```env
# Payment Gateway
HITPAY_API_KEY=your_api_key
HITPAY_SALT=your_salt_key
HITPAY_BASE_URL=https://api.sandbox.hit-pay.com

# Billing Settings
OVERAGE_RATE_PER_LICENSE=49.00
DEFAULT_VAT_PERCENTAGE=12.00
IMPLEMENTATION_FEE_STARTER=5000.00
```

#### Constants
```php
class LicenseOverageService {
    const OVERAGE_RATE_PER_LICENSE = 49.00;
    
    // Plan-specific limits
    const STARTER_LIMIT = 5;
    const BASIC_LIMIT = 15;
    const PRO_LIMIT = 50;
    const ELITE_LIMIT = 100;
}
```

## Troubleshooting

### Common Issues

#### 1. Invoice Generation Fails
**Symptoms**: Cron jobs failing, no invoices generated
**Solutions**:
```bash
# Check logs
tail -f storage/logs/laravel.log | grep invoice

# Test manually
php artisan invoices:generate

# Check subscription status
SELECT * FROM subscriptions WHERE status != 'active';
```

#### 2. Payment Webhook Issues
**Symptoms**: Payments not updating invoice status
**Solutions**:
```bash
# Check webhook logs
grep -i "webhook" storage/logs/laravel.log

# Verify webhook URL configuration
# Check HitPay dashboard settings

# Test webhook endpoint
curl -X POST your-domain.com/hitpay/webhook \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

#### 3. License Count Mismatches
**Symptoms**: Incorrect usage calculations
**Solutions**:
```sql
-- Reset license counts
UPDATE users SET active_license = 1 WHERE status = 'active';

-- Verify counts
SELECT tenant_id, COUNT(*) as active_count 
FROM users 
WHERE active_license = 1 
GROUP BY tenant_id;
```

#### 4. Overage Invoice Duplicates
**Symptoms**: Multiple invoices for same period
**Solutions**:
```php
// Add period checking in service
$existingInvoice = Invoice::where('tenant_id', $tenantId)
    ->where('period_start', $period['start'])
    ->where('period_end', $period['end'])
    ->whereIn('status', ['pending', 'paid'])
    ->first();

if ($existingInvoice) {
    return null; // Skip creation
}
```

### Debug Mode

Enable debug logging for billing operations:

```php
// In LicenseOverageService
Log::info('Billing operation', [
    'tenant_id' => $tenantId,
    'operation' => 'invoice_generation',
    'data' => $debugData
]);
```

### Performance Monitoring

Monitor billing performance:

```sql
-- Slow billing queries
SELECT * FROM information_schema.processlist 
WHERE info LIKE '%invoice%' OR info LIKE '%subscription%';

-- Large invoice tables
SELECT COUNT(*) as invoice_count FROM invoices;
SELECT COUNT(*) as usage_log_count FROM license_usage_logs;
```

---

## Support

For technical support or billing questions:
- Check logs: `storage/logs/laravel.log`
- Review database: Use provided SQL queries
- Contact: System Administrator

Last Updated: November 2024  
Version: 1.0
