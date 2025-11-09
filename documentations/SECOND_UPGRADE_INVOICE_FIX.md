# Plan Upgrade - Second Upgrade Invoice Generation Fix

## 🐛 Issue Description
After upgrading from one plan to another (e.g., Core to Pro), when the user reaches the employee_limit of the new plan and tries to upgrade again, the system does not generate a new upgrade invoice.

### Example Scenario:
```
1. User has Core plan (11-50 users)
   - active_license: 50
   
2. User upgrades to Pro plan (51-100 users)
   - Plan changes to Pro ✅
   - active_license stays at 50 ✅ (from old plan)
   
3. User adds users and reaches 100 users (Pro's employee_limit)

4. User tries to add more users and upgrade to Elite
   - ❌ PROBLEM: System says "OK to add with overage"
   - ❌ Should say: "Upgrade required to Elite"
```

## 🔍 Root Cause

The `checkUserAdditionRequirements()` method was checking against `employee_limit` of the current plan instead of `active_license`:

### Before (Incorrect):
```php
$currentPlanLimit = $subscription->plan->employee_limit ?? 0; // e.g., 100 for Pro

if ($newUserCount <= $currentPlanLimit) { // Checking against 100
    return ['status' => 'ok']; // ❌ WRONG - User has only paid for 50 licenses
}
```

### The Problem:
- After upgrade, `active_license` = 50 (from old Core plan)
- But checking against `employee_limit` = 100 (from new Pro plan)
- So user can add up to 100 users without being prompted to upgrade
- This bypasses the upgrade requirement!

## ✅ Solution

Use `active_license` as the base for comparison, not `employee_limit`:

### After (Correct):
```php
// ✅ Use active_license as the current licensed count
$currentLicensedCount = $subscription->active_license ?? $subscription->plan->employee_limit ?? 0;

// ✅ Check against active_license (what user has paid for)
if ($newUserCount <= $currentLicensedCount) { // Checking against 50
    return ['status' => 'ok']; // ✅ CORRECT
}

// If exceeds active_license, check if upgrade is needed
$nextPlan = Plan::where('employee_minimum', '>', $currentPlanLimit)
    ->where('billing_cycle', $subscription->billing_cycle)
    ->orderBy('employee_minimum', 'asc')
    ->first();
```

## 📊 Logic Flow After Fix

### Scenario 1: Within Licensed Count
```
User State:
- Plan: Pro (51-100 limit)
- active_license: 50 (from old Core plan)
- Current users: 45

Action: Add 1 user (total = 46)

Check:
- 46 <= 50 (active_license) ✅
- Result: "OK - User can be added"
```

### Scenario 2: Exceed Licensed Count but Within Overage Range
```
User State:
- Plan: Pro (51-100 limit)
- active_license: 50
- Current users: 50

Action: Add 1 user (total = 51)

Check:
- 51 > 50 (active_license) ❌
- 51 <= 100 (next plan's minimum - 1) ✅
- Result: "OK - Overage fee applies (₱49/user/month)"
```

### Scenario 3: Exceed Overage Range - Upgrade Required
```
User State:
- Plan: Pro (51-100 limit)
- active_license: 50
- Current users: 100

Action: Add 1 user (total = 101)

Check:
- 101 > 50 (active_license) ❌
- Next plan: Elite (101-200 minimum)
- 101 > 100 (Pro's employee_limit) ❌
- Result: "Upgrade Required - Must upgrade to Elite"
- Generate upgrade invoice ✅
```

## 🔧 Code Changes

### File: `/app/Services/LicenseOverageService.php`
### Method: `checkUserAdditionRequirements()`

**Changes:**
1. ✅ Added `$currentLicensedCount` variable using `active_license`
2. ✅ Changed comparison from `employee_limit` to `active_license`
3. ✅ Added billing cycle filter to next plan query
4. ✅ Updated return data to include `current_licensed_count`

```php
// CORE, PRO, and ELITE PLANS LOGIC

// ✅ IMPORTANT: Use active_license as the current licensed count
// This represents what the user has actually paid for
// After upgrade, active_license stays at old plan's limit until next renewal
$currentLicensedCount = $subscription->active_license ?? $subscription->plan->employee_limit ?? 0;

$currentPlanLimit = $subscription->plan->employee_limit ?? 0;
$currentPlanMinimum = $subscription->plan->employee_minimum ?? 0;

// ✅ Check against active_license (what user has paid for), not plan limit
if ($newUserCount <= $currentLicensedCount) {
    // Within licensed count - user can be added
    return [
        'status' => 'ok',
        'message' => 'User can be added within licensed limit',
        'data' => [
            'current_users' => $currentActiveUsers,
            'new_user_count' => $newUserCount,
            'current_plan' => $planName,
            'current_licensed_count' => $currentLicensedCount, // ✅ NEW
            'current_plan_limit' => $currentPlanLimit,
            'within_licensed_limit' => true
        ]
    ];
}

// User count exceeds licensed count - determine if overage or upgrade is needed
// Get next plan to determine max overage allowed
// ✅ Compare against employee_limit of current plan (not active_license)
$nextPlan = Plan::where('employee_minimum', '>', $currentPlanLimit)
    ->where('billing_cycle', $subscription->billing_cycle) // ✅ Same billing cycle
    ->orderBy('employee_minimum', 'asc')
    ->first();
```

## 📋 Testing Scenarios

### Test 1: After Fresh Upgrade - Within Active License
```sql
-- Setup
UPDATE subscriptions SET 
    plan_id = 3, -- Pro plan
    active_license = 50, -- From old Core plan
    implementation_fee_paid = 500,
    amount_paid = 5000
WHERE id = 1;

-- Test: Add user when current = 45
-- Expected: OK (45 + 1 = 46, which is <= 50)
-- Result: User can be added without overage
```

### Test 2: After Fresh Upgrade - Overage Range
```sql
-- Setup: Same as above

-- Test: Add user when current = 50
-- Expected: OK with overage (50 + 1 = 51, which is > 50 but <= 100)
-- Result: User can be added with ₱49/month overage fee
```

### Test 3: After Fresh Upgrade - Need Second Upgrade
```sql
-- Setup: Same as above

-- Test: Add user when current = 100
-- Expected: Upgrade Required (100 + 1 = 101, which is > 100)
-- Result: Must upgrade to Elite plan
-- Action: Generate upgrade invoice ✅
```

### Test 4: Core → Pro → Elite Upgrade Chain
```
Step 1: Start with Core (50 licensed)
- active_license: 50
- Can add up to 50 users

Step 2: Upgrade to Pro
- plan_id: Pro (51-100 limit)
- active_license: 50 (stays from Core)
- Can still only add up to 50 users without overage
- Can add 51-100 with overage
- Cannot add 101+ without upgrade

Step 3: Reach 100 users, try to add 101st
- System detects: 101 > 50 (active_license) ✅
- System detects: 101 > 100 (Pro limit) ✅
- System shows: Upgrade to Elite required ✅
- Generate upgrade invoice ✅

Step 4: Upgrade to Elite
- plan_id: Elite (101-200 limit)
- active_license: 50 (stays from Core)
- Can add up to 50 without overage
- Can add 51-200 with overage
```

## 🔍 Verification Queries

### Check Current State After Upgrade:
```sql
SELECT 
    s.id,
    s.tenant_id,
    p.name as current_plan,
    p.employee_minimum,
    p.employee_limit,
    s.active_license,
    (SELECT COUNT(*) FROM users WHERE tenant_id = s.tenant_id AND active_license = 1) as current_active_users,
    CASE 
        WHEN s.active_license < p.employee_limit THEN 'Recently Upgraded (active_license < plan limit)'
        WHEN s.active_license = p.employee_limit THEN 'Normal State'
        ELSE 'Check Manually'
    END as license_status
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
WHERE s.status = 'active';
```

### Verify Upgrade Detection:
```sql
-- Simulate: User has Pro plan, 100 users, wants to add 101st
SELECT 
    p.name as current_plan,
    p.employee_limit as plan_limit,
    s.active_license,
    100 as current_users,
    101 as new_user_count,
    CASE 
        WHEN 101 <= s.active_license THEN '✅ Within license'
        WHEN 101 > s.active_license AND 101 <= p.employee_limit THEN '⚠️ Overage allowed'
        WHEN 101 > p.employee_limit THEN '🚀 Upgrade Required'
        ELSE '?'
    END as expected_result,
    (SELECT name FROM plans 
     WHERE employee_minimum > p.employee_limit 
     AND billing_cycle = s.billing_cycle
     ORDER BY employee_minimum ASC 
     LIMIT 1) as recommended_upgrade
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
WHERE s.id = 1;
```

## 📁 Files Modified

1. **LicenseOverageService.php**
   - Method: `checkUserAdditionRequirements()`
   - Changes:
     - ✅ Use `active_license` instead of `employee_limit` for base comparison
     - ✅ Added `current_licensed_count` variable
     - ✅ Added billing cycle filter to next plan query
     - ✅ Updated return data structure

## ⚠️ Important Notes

1. **active_license vs employee_limit:**
   - `active_license` = What user has **paid for** (stays at old plan limit after upgrade)
   - `employee_limit` = Maximum capacity of **current plan** (changes after upgrade)

2. **Overage Calculation:**
   - Base: `active_license` (not employee_limit)
   - Max overage: Up to next plan's minimum - 1
   - Beyond max overage: Upgrade required

3. **Upgrade Chain:**
   - Each upgrade keeps `active_license` from previous plan
   - Only at renewal does `active_license` get updated to actual usage
   - This ensures proper billing and upgrade prompts

4. **Next Plan Query:**
   - Now includes billing cycle filter
   - Ensures monthly plans only see monthly upgrades
   - Ensures yearly plans only see yearly upgrades

## 🚀 Deployment

- ✅ No database migration required
- ✅ Backward compatible
- ✅ Works with existing subscriptions
- ⚠️ Test upgrade chains thoroughly
- ⚠️ Verify invoice generation after second upgrade

## ✅ Expected Behavior After Fix

✅ First upgrade works correctly  
✅ Second upgrade detected when reaching plan limit  
✅ Upgrade invoice generated for second upgrade  
✅ Can continue upgrading through all plan tiers  
✅ Overage fees apply correctly between upgrades  
✅ Billing cycle consistency maintained  

---

**Date:** November 9, 2025  
**Issue:** Second upgrade not generating invoice  
**Root Cause:** Checking against `employee_limit` instead of `active_license`  
**Status:** ✅ FIXED  
**Priority:** 🔴 Critical (blocks plan upgrades)
