# Plan Upgrade Payment - Subscription Extension Fix

## 🎯 Requirement
Kapag nag-upgrade at nag-bayad ng plan upgrade invoice, dapat:
1. ✅ `payment_status` → "paid"
2. ✅ `subscription_end` → Extended by billing cycle (+1 month or +1 year)
3. ✅ `next_renewal_date` → Same as new subscription_end
4. ✅ `renewed_at` → Current timestamp

## 📝 What Changed

### Before (Incomplete):
```php
$subscription->update([
    'plan_id' => $newPlan->id,
    'implementation_fee_paid' => $newPlan->implementation_fee ?? 0,
    'active_license' => max((($newPlan->employee_minimum ?? $newPlan->license_limit ?? 0) - 1), 0),
    'amount_paid' => $newPlan->price,
    // ❌ Missing: payment_status, subscription_end, next_renewal_date
]);
```

### After (Complete):
```php
// Calculate new subscription_end based on billing cycle
$billingCycle = $subscription->billing_cycle ?? 'monthly';
$currentEndDate = $subscription->subscription_end
    ? Carbon::parse($subscription->subscription_end)
    : now();

$baseDate = $currentEndDate->gt(now()) ? $currentEndDate : now();

$newEndDate = match ($billingCycle) {
    'yearly' => $baseDate->copy()->addYear(),
    'quarterly' => $baseDate->copy()->addMonths(3),
    default => $baseDate->copy()->addMonth(),
};

$subscription->update([
    'plan_id' => $newPlan->id,
    'implementation_fee_paid' => $newPlan->implementation_fee ?? 0,
    'active_license' => max((($newPlan->employee_minimum ?? $newPlan->license_limit ?? 0) - 1), 0),
    'amount_paid' => $newPlan->price,
    'payment_status' => 'paid', // ✅ Added
    'subscription_end' => $newEndDate, // ✅ Added
    'next_renewal_date' => $newEndDate, // ✅ Added
    'renewed_at' => now(), // ✅ Added
]);
```

## 🔄 Logic Flow

### Monthly Billing Cycle:
```
Current State:
- subscription_end: 2025-11-30
- billing_cycle: monthly

User upgrades and pays on: 2025-11-09

Result:
- subscription_end: 2025-12-30 (+1 month from current end)
- next_renewal_date: 2025-12-30
- payment_status: paid
- renewed_at: 2025-11-09 13:45:00
```

### Yearly Billing Cycle:
```
Current State:
- subscription_end: 2025-12-31
- billing_cycle: yearly

User upgrades and pays on: 2025-11-09

Result:
- subscription_end: 2026-12-31 (+1 year from current end)
- next_renewal_date: 2026-12-31
- payment_status: paid
- renewed_at: 2025-11-09 13:45:00
```

### Edge Case - Expired Subscription:
```
Current State:
- subscription_end: 2025-10-15 (expired)
- billing_cycle: monthly
- Current date: 2025-11-09

User upgrades and pays:

Result:
- subscription_end: 2025-12-09 (+1 month from NOW, not from expired date)
- next_renewal_date: 2025-12-09
- payment_status: paid
- renewed_at: 2025-11-09 13:45:00
```

## 📊 Database Updates

### subscriptions Table Fields Updated:
```sql
UPDATE subscriptions SET
    plan_id = 3,                          -- New plan ID
    implementation_fee_paid = 500,        -- New plan's implementation fee
    active_license = 50,                  -- Based on new plan's minimum - 1
    amount_paid = 5000,                   -- New plan's price
    payment_status = 'paid',              -- ✅ NEW
    subscription_end = '2025-12-09',      -- ✅ NEW (+1 month or +1 year)
    next_renewal_date = '2025-12-09',     -- ✅ NEW (same as subscription_end)
    renewed_at = '2025-11-09 13:45:00',  -- ✅ NEW (timestamp of upgrade)
    updated_at = NOW()
WHERE id = 1;
```

## 🧪 Testing Scenarios

### Test 1: Monthly Subscription Upgrade
```sql
-- Before
SELECT
    id,
    plan_id,
    billing_cycle,
    subscription_end,
    next_renewal_date,
    payment_status
FROM subscriptions
WHERE id = 1;
-- Result: id=1, plan_id=2, billing_cycle='monthly',
--         subscription_end='2025-11-30', next_renewal_date='2025-11-30',
--         payment_status='pending'

-- Pay upgrade invoice

-- After (Expected)
SELECT
    id,
    plan_id,
    billing_cycle,
    subscription_end,
    next_renewal_date,
    payment_status,
    renewed_at
FROM subscriptions
WHERE id = 1;
-- Result: id=1, plan_id=3, billing_cycle='monthly',
--         subscription_end='2025-12-30' ✅, next_renewal_date='2025-12-30' ✅,
--         payment_status='paid' ✅, renewed_at='2025-11-09...' ✅
```

### Test 2: Yearly Subscription Upgrade
```sql
-- Before
SELECT
    id,
    plan_id,
    billing_cycle,
    subscription_end,
    next_renewal_date,
    payment_status
FROM subscriptions
WHERE id = 2;
-- Result: id=2, plan_id=2, billing_cycle='yearly',
--         subscription_end='2025-12-31', payment_status='pending'

-- Pay upgrade invoice

-- After (Expected)
SELECT
    id,
    plan_id,
    billing_cycle,
    subscription_end,
    next_renewal_date,
    payment_status
FROM subscriptions
WHERE id = 2;
-- Result: id=2, plan_id=3, billing_cycle='yearly',
--         subscription_end='2026-12-31' ✅ (+1 year),
--         payment_status='paid' ✅
```

### Test 3: Expired Subscription Upgrade
```sql
-- Before (Expired)
SELECT
    id,
    subscription_end,
    CASE
        WHEN subscription_end < CURDATE() THEN 'EXPIRED'
        ELSE 'ACTIVE'
    END as status
FROM subscriptions
WHERE id = 3;
-- Result: id=3, subscription_end='2025-10-15', status='EXPIRED'

-- Pay upgrade invoice on 2025-11-09

-- After (Expected)
-- Should extend from NOW, not from expired date
SELECT
    id,
    subscription_end,
    next_renewal_date,
    billing_cycle
FROM subscriptions
WHERE id = 3;
-- Result: id=3, subscription_end='2025-12-09' ✅ (NOW + 1 month),
--         billing_cycle='monthly'
```

## 📋 Verification Checklist

After deploying, verify:
- [ ] `payment_status` changes to "paid" after upgrade payment
- [ ] `subscription_end` is extended by correct period (monthly/yearly)
- [ ] `next_renewal_date` matches new `subscription_end`
- [ ] `renewed_at` is set to payment timestamp
- [ ] Monthly subscriptions: +1 month from current end
- [ ] Yearly subscriptions: +1 year from current end
- [ ] Expired subscriptions: Extended from current date, not expired date

## 🔍 Quick Verification SQL

```sql
-- Check recent plan upgrades
SELECT
    s.id,
    s.tenant_id,
    s.plan_id,
    p.name as plan_name,
    s.billing_cycle,
    s.subscription_end,
    s.next_renewal_date,
    s.payment_status,
    s.renewed_at,
    i.invoice_number,
    i.invoice_type,
    i.paid_at,
    CASE
        WHEN s.subscription_end = s.next_renewal_date THEN '✅ Dates match'
        ELSE '❌ Dates mismatch'
    END as date_validation,
    CASE
        WHEN s.payment_status = 'paid' THEN '✅ Paid'
        ELSE '❌ Not paid'
    END as payment_validation,
    CASE
        WHEN s.billing_cycle = 'monthly'
            AND TIMESTAMPDIFF(MONTH, i.paid_at, s.subscription_end) >= 1
            THEN '✅ Extended +1 month'
        WHEN s.billing_cycle = 'yearly'
            AND TIMESTAMPDIFF(YEAR, i.paid_at, s.subscription_end) >= 1
            THEN '✅ Extended +1 year'
        ELSE '⚠️ Check period'
    END as period_validation
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
LEFT JOIN invoices i ON i.subscription_id = s.id
    AND i.invoice_type = 'plan_upgrade'
    AND i.status = 'paid'
WHERE i.paid_at IS NOT NULL
ORDER BY i.paid_at DESC
LIMIT 10;
```

## 📁 Files Modified

**File:** `/app/Http/Controllers/Tenant/Billing/PaymentController.php`
**Method:** `processPlanUpgrade()`
**Changes:**
- ✅ Added calculation for new subscription_end based on billing cycle
- ✅ Added `payment_status` = 'paid'
- ✅ Added `subscription_end` extension
- ✅ Added `next_renewal_date` update
- ✅ Added `renewed_at` timestamp

## ⚠️ Important Notes

1. **Billing Cycle Matters:**
   - Monthly → +1 month
   - Yearly → +1 year
   - Quarterly → +3 months

2. **Base Date Logic:**
   - If current subscription_end is in the future → Use it as base
   - If subscription_end has passed (expired) → Use current date as base

3. **Consistency:**
   - `subscription_end` and `next_renewal_date` should always be the same after upgrade
   - Both represent when the next payment is due

4. **Renewal Tracking:**
   - `renewed_at` tracks when the last renewal/upgrade happened
   - Useful for audit trails and billing history

## 🚀 Deployment Notes

- ✅ No database migration required
- ✅ Backward compatible
- ✅ Works with existing subscriptions
- ⚠️ Test with different billing cycles before production
- ⚠️ Verify date calculations for expired subscriptions

---

**Date:** November 9, 2025
**Issue:** Missing subscription extension on plan upgrade payment
**Status:** ✅ FIXED
**Priority:** 🔴 Critical (affects subscription validity)
