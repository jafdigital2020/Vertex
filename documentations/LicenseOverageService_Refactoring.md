# License Overage Service Refactoring Documentation

## Overview
This document describes the refactoring of `LicenseOverageService.php` to remove hardcoded plan limits and use dynamic values from the `Plan` model's `employee_minimum` and `employee_limit` columns.

## Date: 2024
## Status: Completed

---

## Summary of Changes

### 1. Removed Hardcoded Constants

**Before:**
```php
const STARTER_PLAN_LIMIT = 10;
const STARTER_MAX_LIMIT = 20;
const CORE_PLAN_BASE = 21;
const CORE_PLAN_LIMIT = 100;
const PRO_PLAN_BASE = 101;
const PRO_PLAN_LIMIT = 200;
const ELITE_PLAN_BASE = 201;
const ELITE_PLAN_LIMIT = 500;
```

**After:**
```php
const OVERAGE_RATE_PER_LICENSE = 49.00;
// Note: Plan limits are now dynamically retrieved from the Plan model
// using employee_minimum and employee_limit columns
```

### 2. Added Plan Model Import

Added `use App\Models\Plan;` to support dynamic plan queries.

### 3. Refactored `checkUserAdditionRequirements()` Method

#### Starter Plan Logic (Lines 845-903)
- **Implementation Fee Trigger**: When reaching 11th user
- **Overage Range**: 11-20 users (or up to `plan.employee_limit`)
- **Upgrade Required**: When exceeding `plan.employee_limit`

**Key Changes:**
- Uses `$subscription->plan->employee_limit` instead of `self::STARTER_MAX_LIMIT`
- Dynamic message shows actual limit from plan table
- Implementation fee check uses `$subscription->plan->implementation_fee`

#### Core/Pro/Elite Plans Logic (Lines 904-1037)
**Complete Rewrite** to use dynamic plan-based logic:

1. **Within Plan Limit Check**: If `newUserCount <= plan.employee_limit`, user can be added

2. **Overage Logic**:
   - Queries next plan: `Plan::where('employee_minimum', '>', $currentPlanLimit)`
   - Allows overage up to `nextPlan.employee_minimum - 1`
   - Charges ₱49/user overage fee

3. **Upgrade Required Logic**:
   - When `newUserCount > maxWithOverage`
   - Calls `getRecommendedUpgradePlan()` and `getAvailableUpgradePlans()`
   - Returns upgrade options with costs

4. **Elite Plan Special Handling**:
   - If no next plan exists (Elite is highest tier)
   - Allows 100 additional users as overage
   - Beyond that, status = `contact_sales`

**Removed:**
- Hardcoded plan type checks (`$isCoreOrCorePlan`, `$isProPlan`, `$isElitePlan`)
- Hardcoded `$maxWithOverage` and `$upgradeAtUser` values

---

## 4. Updated `createPlanUpgradeInvoice()` Method

### New Business Logic Implementation

**Calculates upgrade costs as:**
```
Subtotal = Implementation Fee Difference + Plan Price Difference
VAT = Subtotal × (vat_percentage / 100)
Total = Subtotal + VAT
```

**Implementation Fee Difference:**
```php
$implementationFeeDifference = max(0, $newPlan->implementation_fee - $subscription->implementation_fee_paid);
```

**Plan Price Difference:**
```php
$planPriceDifference = max(0, $newPlan->price - $currentPlan->price);
```

**Invoice Fields:**
- `subscription_amount`: Plan price difference (or prorated amount)
- `implementation_fee`: Implementation fee difference
- `subtotal`: Sum of both differences
- `vat_percentage`: From new plan (default 12%)
- `vat_amount`: Calculated VAT
- `amount_due`: Total including VAT

**Enhanced Logging:**
- Logs old/new plan names and prices
- Logs all calculation steps
- Tracks implementation fee paid vs. new fee

---

## 5. Refactored `getAvailableUpgradePlans()` Method

**Query Change:**
```php
// OLD: where('employee_limit', '>', $currentPlan->employee_limit)
// NEW: where('employee_minimum', '>', $currentPlan->employee_limit)
```

This ensures plans are suggested based on the next tier's minimum, not just a higher limit.

**New Return Fields:**
```php
[
    'id' => $plan->id,
    'name' => $plan->name,
    'employee_minimum' => $plan->employee_minimum,
    'employee_limit' => $plan->employee_limit,
    'price' => $plan->price,
    'implementation_fee' => $plan->implementation_fee,
    'implementation_fee_difference' => $implementationFeeDifference,
    'plan_price_difference' => $planPriceDifference,
    'subtotal' => $subtotal,
    'vat_percentage' => $vatPercentage,
    'vat_amount' => $vatAmount,
    'total_upgrade_cost' => $totalAmount,
    'billing_cycle' => $plan->billing_cycle,
    'current_user_count' => $currentUserCount,
    'is_recommended' => false
]
```

Frontend now receives complete upgrade cost breakdown including VAT.

---

## 6. Refactored `getRecommendedUpgradePlan()` Method

**Same changes as `getAvailableUpgradePlans()`:**
- Uses `employee_minimum` for next tier selection
- Returns full cost breakdown with VAT
- Includes `current_user_count` for context

---

## 7. Removed `getCorePlanImplementationFee()` Dependency

This method is now obsolete as all implementation fees come directly from the Plan model.

**Note**: The method still exists but is no longer referenced in the main logic. Consider removing it in future cleanup.

---

## Business Flow Decision Tree

### Starter Plan (1-10 base, 11-20 with overage)

```
Current Users: X, Adding 1 more = Y
├─ Y ≤ 10
│  └─ Status: OK (within base limit)
├─ Y = 11
│  ├─ Implementation Fee Paid?
│  │  ├─ NO → Status: implementation_fee (charge fee)
│  │  └─ YES → Status: ok (overage fee ₱49)
│  └─ Implementation Fee Paid
├─ 11 < Y ≤ 20 (plan.employee_limit)
│  ├─ Implementation Fee Paid?
│  │  ├─ NO → Status: implementation_fee
│  │  └─ YES → Status: ok (overage fee ₱49)
└─ Y > 20 (plan.employee_limit)
   └─ Status: upgrade_required
      └─ Recommended: Core Plan
```

### Core/Pro/Elite Plans

```
Current Users: X, Adding 1 more = Y
├─ Y ≤ plan.employee_limit
│  └─ Status: OK (within plan limit)
├─ plan.employee_limit < Y ≤ (nextPlan.employee_minimum - 1)
│  └─ Status: ok (overage fee ₱49/user)
└─ Y > (nextPlan.employee_minimum - 1)
   ├─ Next Plan Exists?
   │  ├─ YES → Status: upgrade_required
   │  │        └─ Show available plans with costs
   │  └─ NO (Elite Plan)
   │     ├─ Y ≤ (plan.employee_limit + 100)
   │     │  └─ Status: ok (overage fee ₱49)
   │     └─ Y > (plan.employee_limit + 100)
   │        └─ Status: contact_sales
```

### Upgrade Cost Calculation

```
When Upgrading from Plan A to Plan B:
1. Implementation Fee Difference = max(0, B.implementation_fee - subscription.implementation_fee_paid)
2. Plan Price Difference = max(0, B.price - A.price)
3. Subtotal = Implementation Fee Difference + Plan Price Difference
4. VAT = Subtotal × (B.vat_percentage / 100)
5. Total = Subtotal + VAT

Invoice Created:
- subscription_amount: Plan Price Difference
- implementation_fee: Implementation Fee Difference
- subtotal: Subtotal
- vat_amount: VAT
- amount_due: Total
```

---

## Testing Checklist

### Starter Plan Tests
- [ ] Add 11th user without implementation fee paid → Should require fee
- [ ] Add 11th user with implementation fee paid → Should charge overage
- [ ] Add 15th user → Should charge overage (₱49 × overage count)
- [ ] Add 21st user → Should require upgrade to Core

### Core Plan Tests
- [ ] Add user within limit (≤100) → Should be OK
- [ ] Add user in overage range (101-100) → Should charge overage
- [ ] Add user beyond overage → Should require upgrade to Pro

### Pro Plan Tests
- [ ] Add user within limit (≤200) → Should be OK
- [ ] Add user in overage range (201-200) → Should charge overage
- [ ] Add user beyond overage → Should require upgrade to Elite

### Elite Plan Tests
- [ ] Add user within limit (≤500) → Should be OK
- [ ] Add user in overage range (501-600) → Should charge overage
- [ ] Add user beyond 600 → Should show contact_sales

### Upgrade Tests
- [ ] Upgrade from Starter to Core → Calculate correct implementation fee difference
- [ ] Upgrade from Core to Pro → Calculate correct plan price difference
- [ ] Verify VAT calculation (12% or plan-specific)
- [ ] Check invoice fields: subtotal, vat_amount, amount_due
- [ ] Verify upgrade_plan_id is stored correctly

---

## Database Schema Requirements

### Plans Table
```sql
employee_minimum INT -- Minimum users for this plan
employee_limit INT -- Maximum users (base limit)
implementation_fee DECIMAL(10,2) -- One-time setup fee
price DECIMAL(10,2) -- Monthly/yearly subscription price
vat_percentage DECIMAL(5,2) -- VAT rate (default 12%)
billing_cycle ENUM('monthly', 'yearly')
is_active BOOLEAN
```

### Subscriptions Table
```sql
plan_id INT
implementation_fee_paid DECIMAL(10,2) -- Tracks cumulative implementation fee paid
active_license INT -- Current active user count
billing_cycle ENUM('monthly', 'yearly')
next_renewal_date DATE
```

### Invoices Table
```sql
upgrade_plan_id INT -- Target plan ID for upgrades
subscription_amount DECIMAL(10,2) -- Plan price component
implementation_fee DECIMAL(10,2) -- Implementation fee component
subtotal DECIMAL(10,2) -- Before VAT
vat_percentage DECIMAL(5,2)
vat_amount DECIMAL(10,2)
amount_due DECIMAL(10,2) -- Total including VAT
```

---

## Migration Recommendations

### If Migrating from Old System:

1. **Update Plans Table**: Ensure all plans have `employee_minimum` and `employee_limit` populated

   Example:
   ```sql
   -- Starter: 1-10 base, 11-20 overage
   UPDATE plans SET employee_minimum = 1, employee_limit = 20 WHERE name LIKE '%Starter%';

   -- Core: 21-100 base
   UPDATE plans SET employee_minimum = 21, employee_limit = 100 WHERE name LIKE '%Core%';

   -- Pro: 101-200 base
   UPDATE plans SET employee_minimum = 101, employee_limit = 200 WHERE name LIKE '%Pro%';

   -- Elite: 201-500 base
   UPDATE plans SET employee_minimum = 201, employee_limit = 500 WHERE name LIKE '%Elite%';
   ```

2. **Audit Subscriptions**: Verify `implementation_fee_paid` is correctly set for all active subscriptions

3. **Test Upgrade Flows**: Run through all upgrade scenarios before deploying to production

---

## Benefits of Refactoring

1. **Flexibility**: Plans can now be adjusted via database without code changes
2. **Maintainability**: No hardcoded limits to update when business rules change
3. **Accuracy**: Upgrade costs calculated based on actual plan differences
4. **Transparency**: Full cost breakdown (subtotal, VAT, total) provided to frontend
5. **Scalability**: Easy to add new plan tiers without modifying service logic

---

## Related Files

- **Service**: `/app/Services/LicenseOverageService.php` (refactored)
- **Model**: `/app/Models/Plan.php`
- **Model**: `/app/Models/Subscription.php`
- **Model**: `/app/Models/Invoice.php`

---

## Future Enhancements

1. **Dynamic Overage Limits**: Add `max_overage` column to plans table to replace hardcoded Elite "+100" logic
2. **Tiered Overage Rates**: Support different overage rates per plan (currently fixed at ₱49)
3. **Proration Service**: Create dedicated service for prorated upgrade calculations
4. **Webhook Integration**: Notify finance/billing system on plan upgrades
5. **Audit Trail**: Log all plan changes and cost calculations for compliance

---

## Contact

For questions or issues related to this refactoring, please contact the development team.

---

**Last Updated**: 2024
**Refactored By**: GitHub Copilot
**Review Status**: Pending QA Testing
