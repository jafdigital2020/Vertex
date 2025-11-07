# Implementation Fee Calculation - Quick Reference

## Overview
This document explains how the implementation fee difference is calculated when upgrading plans.

## Basic Formula

```
Amount to Pay = New Plan Implementation Fee - Already Paid Implementation Fee
```

## Real-World Examples

### Example 1: Starter → Core
```
Current Plan:  Core Starter Monthly
  - Implementation Fee: ₱4,999
  - Already Paid: ₱4,999 ✅

Selected Plan: Core Monthly
  - Implementation Fee: ₱14,999
  - Already Paid: ₱4,999

Calculation:
  Amount to Pay = ₱14,999 - ₱4,999 = ₱10,000

User pays: ₱10,000 (just the difference!)
```

### Example 2: Starter → Pro (skipping Core)
```
Current Plan:  Core Starter Monthly
  - Implementation Fee: ₱4,999
  - Already Paid: ₱4,999 ✅

Selected Plan: Pro Monthly
  - Implementation Fee: ₱39,999
  - Already Paid: ₱4,999

Calculation:
  Amount to Pay = ₱39,999 - ₱4,999 = ₱35,000

User pays: ₱35,000
```

### Example 3: Core → Pro
```
Current Plan:  Core Monthly
  - Implementation Fee: ₱14,999
  - Already Paid: ₱14,999 ✅

Selected Plan: Pro Monthly
  - Implementation Fee: ₱39,999
  - Already Paid: ₱14,999

Calculation:
  Amount to Pay = ₱39,999 - ₱14,999 = ₱25,000

User pays: ₱25,000
```

### Example 4: Pro → Elite
```
Current Plan:  Pro Monthly
  - Implementation Fee: ₱39,999
  - Already Paid: ₱39,999 ✅

Selected Plan: Elite Monthly
  - Implementation Fee: ₱79,999
  - Already Paid: ₱39,999

Calculation:
  Amount to Pay = ₱79,999 - ₱39,999 = ₱40,000

User pays: ₱40,000
```

## Implementation Fee Structure

### Monthly Plans
| Plan               | Employee Limit | Implementation Fee | Upgrade From Starter |
|--------------------|----------------|-------------------|---------------------|
| Core Starter       | 20             | ₱4,999            | N/A (base plan)     |
| Core               | 100            | ₱14,999           | Pay ₱10,000         |
| Pro                | 200            | ₱39,999           | Pay ₱35,000         |
| Elite              | 500            | ₱79,999           | Pay ₱75,000         |

### Yearly Plans
| Plan               | Employee Limit | Implementation Fee | Upgrade From Starter |
|--------------------|----------------|-------------------|---------------------|
| Core Starter       | 20             | ₱4,999            | N/A (base plan)     |
| Core               | 100            | ₱14,999           | Pay ₱10,000         |
| Pro                | 200            | ₱39,999           | Pay ₱35,000         |
| Elite              | 500            | ₱79,999           | Pay ₱75,000         |

## Code Implementation

### Backend (LicenseOverageService.php)
```php
public function getAvailableUpgradePlans($subscription)
{
    $currentPlan = $subscription->plan;
    $billingCycle = $subscription->billing_cycle;
    
    $availablePlans = Plan::where('billing_cycle', $billingCycle)
        ->where('employee_limit', '>', $currentPlan->employee_limit)
        ->where('is_active', true)
        ->orderBy('employee_limit', 'asc')
        ->get();
    
    return $availablePlans->map(function($plan) use ($subscription) {
        // ✅ KEY CALCULATION HERE
        $implementationFeeDifference = max(0, 
            ($plan->implementation_fee ?? 0) - ($subscription->implementation_fee_paid ?? 0)
        );
        
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'employee_limit' => $plan->employee_limit,
            'price' => $plan->price,
            'implementation_fee' => $plan->implementation_fee ?? 0,
            'implementation_fee_difference' => $implementationFeeDifference, // ✅ This is what we charge
            'billing_cycle' => $plan->billing_cycle,
        ];
    });
}
```

### Invoice Creation (LicenseOverageService.php)
```php
public function createPlanUpgradeInvoice($subscription, $newPlan, $proratedAmount = 0)
{
    // Calculate implementation fee difference
    $currentImplementationFeePaid = $subscription->implementation_fee_paid ?? 0;
    $newPlanImplementationFee = $newPlan->implementation_fee ?? 0;
    
    // ✅ Only charge the difference
    $implementationFeeDifference = max(0, $newPlanImplementationFee - $currentImplementationFeePaid);
    
    $invoiceNumber = $this->generateInvoiceNumber('plan_upgrade');
    $period = $subscription->getCurrentPeriod();
    
    $invoice = Invoice::create([
        'tenant_id' => $subscription->tenant_id,
        'subscription_id' => $subscription->id,
        'invoice_type' => 'plan_upgrade',
        'invoice_number' => $invoiceNumber,
        'amount_due' => $implementationFeeDifference + $proratedAmount, // ✅ Difference + any proration
        'implementation_fee' => $implementationFeeDifference,           // ✅ Just the difference
        'subscription_amount' => $proratedAmount,
        'currency' => 'PHP',
        'status' => 'pending',
        'due_date' => Carbon::now()->addDays(7),
        'issued_at' => Carbon::now(),
        'period_start' => $period['start'],
        'period_end' => $period['end'],
    ]);
    
    return $invoice;
}
```

### Frontend Display (employeelist.js)
```javascript
// Display in plan card
const planCard = `
    <div class="card plan-option" data-plan-id="${plan.id}">
        <div class="card-body">
            <h5>${plan.name}</h5>
            <ul>
                <li>Up to ${plan.employee_limit} users</li>
                <li>Implementation fee: ₱${parseFloat(plan.implementation_fee).toLocaleString()}</li>
                <li>Amount to pay: <strong>₱${parseFloat(plan.implementation_fee_difference).toLocaleString()}</strong></li>
            </ul>
        </div>
    </div>
`;

// Display in summary
$('#summary_current_impl_fee').text('₱' + parseFloat(data.current_implementation_fee_paid).toLocaleString());
$('#summary_new_impl_fee').text('₱' + parseFloat(plan.implementation_fee).toLocaleString());
$('#summary_amount_due').text('₱' + parseFloat(plan.implementation_fee_difference).toLocaleString());
```

## Database Fields

### Plans Table
```sql
CREATE TABLE plans (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255),
    employee_limit INT,
    price DECIMAL(10,2),
    implementation_fee DECIMAL(10,2),  -- ✅ Total implementation fee for this plan
    billing_cycle ENUM('monthly', 'yearly'),
    is_active BOOLEAN DEFAULT TRUE,
    -- ... other fields
);
```

### Subscriptions Table
```sql
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    plan_id BIGINT UNSIGNED,
    implementation_fee_paid DECIMAL(10,2) DEFAULT 0,  -- ✅ How much already paid
    status ENUM('active', 'expired', 'trial', 'canceled'),
    billing_cycle ENUM('monthly', 'yearly'),
    -- ... other fields
);
```

### Invoices Table
```sql
CREATE TABLE invoices (
    id BIGINT UNSIGNED PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    subscription_id BIGINT UNSIGNED,
    invoice_type ENUM('subscription', 'license_overage', 'implementation_fee', 'plan_upgrade'),
    amount_due DECIMAL(10,2),              -- ✅ Total amount to pay
    implementation_fee DECIMAL(10,2),      -- ✅ Implementation fee portion (difference)
    subscription_amount DECIMAL(10,2),     -- ✅ Proration portion (if any)
    status ENUM('pending', 'paid', 'overdue', 'canceled'),
    -- ... other fields
);
```

## Edge Cases

### Case 1: Implementation Fee Not Paid Yet
```
Current Plan: Starter (impl_fee: ₱4,999, paid: ₱0)
Selected Plan: Core (impl_fee: ₱14,999)

Calculation:
  Amount = ₱14,999 - ₱0 = ₱14,999

User pays: ₱14,999 (full implementation fee)
```

### Case 2: Partial Payment (shouldn't happen, but handled)
```
Current Plan: Starter (impl_fee: ₱4,999, paid: ₱2,000)
Selected Plan: Core (impl_fee: ₱14,999)

Calculation:
  Amount = ₱14,999 - ₱2,000 = ₱12,999

User pays: ₱12,999
```

### Case 3: Already Paid More (shouldn't happen, but handled)
```
Current Plan: Core (impl_fee: ₱14,999, paid: ₱20,000)
Selected Plan: Pro (impl_fee: ₱39,999)

Calculation:
  Amount = max(0, ₱39,999 - ₱20,000) = ₱19,999

User pays: ₱19,999 (not negative, thanks to max(0, ...))
```

## After Payment Processing

### Update Subscription
```php
public function processPlanUpgrade($invoiceId)
{
    $invoice = Invoice::with('subscription.plan')->find($invoiceId);
    $subscription = $invoice->subscription;
    $newPlanId = $invoice->vat_amount; // Stored temporarily
    $newPlan = Plan::find($newPlanId);
    
    // ✅ Update subscription with new plan's full implementation fee
    $subscription->update([
        'plan_id' => $newPlan->id,
        'implementation_fee_paid' => $newPlan->implementation_fee,  // ✅ Full amount now
    ]);
}
```

### Example Before/After

**Before Payment:**
```
Subscription:
  plan_id: 1 (Starter)
  implementation_fee_paid: ₱4,999
```

**After Payment of ₱10,000 upgrade invoice:**
```
Subscription:
  plan_id: 2 (Core)
  implementation_fee_paid: ₱14,999  // ✅ Updated to new plan's full fee
```

## Validation Rules

### ✅ Valid Upgrades
- Employee limit MUST be higher
- Billing cycle MUST match
- Implementation fee difference MUST be ≥ 0
- Plan MUST be active

### ❌ Invalid Upgrades
```php
// Same plan
if ($newPlan->id === $subscription->plan_id) {
    return error('Cannot upgrade to same plan');
}

// Lower tier (downgrade)
if ($newPlan->employee_limit <= $subscription->plan->employee_limit) {
    return error('Not an upgrade');
}

// Different billing cycle
if ($newPlan->billing_cycle !== $subscription->billing_cycle) {
    return error('Billing cycle must match');
}

// Inactive plan
if (!$newPlan->is_active) {
    return error('Selected plan is not available');
}
```

## Testing Scenarios

### Test 1: Starter to Core
```bash
# Setup
php artisan tinker

$sub = App\Models\Subscription::find(1);
$sub->plan_id = 1; // Starter
$sub->implementation_fee_paid = 4999;
$sub->save();

$service = new App\Services\LicenseOverageService();
$plans = $service->getAvailableUpgradePlans($sub);

// Expected: Core plan shows ₱10,000 difference
$plans->first()['implementation_fee_difference']; // Should be 10000
```

### Test 2: Core to Pro
```bash
$sub->plan_id = 2; // Core
$sub->implementation_fee_paid = 14999;
$sub->save();

$plans = $service->getAvailableUpgradePlans($sub);

// Expected: Pro plan shows ₱25,000 difference
$plans->first()['implementation_fee_difference']; // Should be 25000
```

## Troubleshooting

### Issue: Wrong Amount Calculated
**Check:**
1. `implementation_fee` field on plan
2. `implementation_fee_paid` field on subscription
3. Formula: `max(0, new_fee - paid_fee)`

### Issue: Full Fee Charged Instead of Difference
**Check:**
1. `implementation_fee_paid` is being set correctly
2. Backend using `implementation_fee_difference` not `implementation_fee`
3. Frontend displaying correct field

### Issue: Negative Amount
**Check:**
1. Using `max(0, ...)` to prevent negatives
2. Not possible to have paid more than plan fee

## Summary

The implementation fee calculation ensures:
- ✅ Users only pay the **difference** when upgrading
- ✅ No double-charging for implementation fees
- ✅ Simple and transparent pricing
- ✅ Works for all plan combinations
- ✅ Handles edge cases properly
- ✅ Easy to understand and verify

**Key Principle:** You only pay for what you haven't paid yet!
