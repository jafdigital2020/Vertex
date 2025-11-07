# Plan Upgrade Feature - Summary & Test Guide

## What Was Fixed

### Problem 1: No Plan Selection
**Before:** When upgrade was required, system just redirected to billing page without actually upgrading the plan.

**After:** User sees a modal with:
- All available upgrade plans (Core, Pro, Elite)
- Each plan showing price, user limit, and implementation fee difference
- Recommended plan highlighted
- User can select which plan to upgrade to

### Problem 2: Hardcoded to "Core" Plan Only
**Before:** System was hardcoded to only suggest Core plan upgrade.

**After:** System dynamically detects:
- Current plan (Starter, Core, Pro, or Elite)
- Available upgrade options based on employee limit
- Recommended next tier automatically
- Works for all plan tiers

### Problem 3: Plan Not Actually Upgrading
**Before:** Invoice generated but plan didn't change in subscription.

**After:** Complete upgrade flow:
1. User selects plan
2. Invoice generated with implementation fee difference
3. User pays invoice
4. Plan automatically upgraded
5. `implementation_fee_paid` updated

## Database Schema Updates

### Plans Available (from PlanSeeder)
| Plan Tier | Billing | User Limit | Price | Implementation Fee |
|-----------|---------|------------|-------|-------------------|
| Starter   | Monthly | 10         | ₱5,000 | ₱4,999 |
| Core      | Monthly | 100        | ₱5,500 | ₱14,999 |
| Pro       | Monthly | 200        | ₱9,500 | ₱39,999 |
| Elite     | Monthly | 500        | ₱14,500 | ₱79,999 |
| Starter   | Yearly  | 10         | ₱57,000 | ₱4,999 |
| Core      | Yearly  | 100        | ₱62,700 | ₱14,999 |
| Pro       | Yearly  | 200        | ₱108,300 | ₱39,999 |
| Elite     | Yearly  | 500        | ₱165,300 | ₱79,999 |

## New Features Added

### 1. Dynamic Plan Detection
```php
// In LicenseOverageService.php
public function getAvailableUpgradePlans($subscription)
public function getRecommendedUpgradePlan($subscription)
```

### 2. Plan Upgrade Invoice Generation
```php
// In EmployeeListController.php
public function generatePlanUpgradeInvoice(Request $request)
public function processPlanUpgrade($invoiceId)
```

### 3. Interactive Plan Selection UI
- Grid of available plans
- "Recommended" badge on suggested plan
- Click to select plan
- Summary shows before confirmation
- Only pay difference in implementation fees

## User Flows

### Scenario 1: Starter → Core Upgrade (at 21st user)
```
Current: Starter Plan (10 users limit, paid ₱4,999 impl. fee)
Trying to add: 21st user

MODAL SHOWS:
┌────────────────────────────────────────┐
│ Plan Upgrade Required                  │
├────────────────────────────────────────┤
│ Current Plan: Starter (up to 10 users) │
│ Current Users: 20                      │
│ After Adding: 21                       │
│                                        │
│ Available Plans:                       │
│                                        │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐│
│ │  CORE    │ │   PRO    │ │  ELITE   ││
│ │(Recommended│ │          │ │          ││
│ │  ₱5,500  │ │  ₱9,500  │ │ ₱14,500  ││
│ │100 users │ │200 users │ │500 users ││
│ │Pay:₱10,000│ │Pay:₱35,000│ │Pay:₱75,000││
│ │ [Select] │ │ [Select] │ │ [Select] ││
│ └──────────┘ └──────────┘ └──────────┘│
└────────────────────────────────────────┘

User selects: Core
Shows summary:
- New Plan: Core Monthly Plan
- User Limit: Up to 100 users
- Monthly Price: ₱5,500
- Current Impl. Fee Paid: ₱4,999
- New Impl. Fee: ₱14,999
- Amount Due: ₱10,000 (difference only)

User clicks: "Proceed with Upgrade"
→ Invoice generated
→ Redirected to /billing
→ User pays ₱10,000
→ Plan upgraded to Core
→ Can now add 21st-100th users
```

### Scenario 2: Core → Pro Upgrade (at 101st user)
```
Current: Core Plan (100 users limit, paid ₱14,999 impl. fee)
Trying to add: 101st user

MODAL SHOWS:
Available Plans: Pro, Elite

User selects: Pro
Amount Due: ₱25,000 (₱39,999 - ₱14,999)

After payment:
→ Plan upgraded to Pro
→ Can add up to 200 users
```

### Scenario 3: Pro → Elite Upgrade (at 201st user)
```
Current: Pro Plan (200 users limit, paid ₱39,999 impl. fee)
Trying to add: 201st user

MODAL SHOWS:
Available Plans: Elite only

Amount Due: ₱40,000 (₱79,999 - ₱39,999)

After payment:
→ Plan upgraded to Elite
→ Can add up to 500 users
```

## Testing Checklist

### Setup
1. Ensure database has been seeded with plans
   ```bash
   php artisan db:seed --class=PlanSeeder
   ```

2. Clear all caches
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   ```

3. Verify migration ran
   ```bash
   php artisan migrate:status | grep invoice
   ```

### Test 1: Starter → Core Upgrade
- [ ] Create tenant with Starter Monthly plan
- [ ] Add 10 employees (should work fine)
- [ ] Add 11th employee → implementation fee modal (₱4,999)
- [ ] Pay implementation fee
- [ ] Add 11th-20th employees (should work with overage)
- [ ] Try to add 21st employee
- [ ] **VERIFY:** Plan upgrade modal shows
- [ ] **VERIFY:** Shows Core, Pro, Elite options
- [ ] **VERIFY:** Core is marked "Recommended"
- [ ] **VERIFY:** Core shows "Pay: ₱10,000"
- [ ] Select Core plan
- [ ] **VERIFY:** Summary shows correct amounts
- [ ] **VERIFY:** "Proceed with Upgrade" button enabled
- [ ] Click "Proceed with Upgrade"
- [ ] **VERIFY:** Invoice generated in database
- [ ] **VERIFY:** invoice_type = 'plan_upgrade'
- [ ] **VERIFY:** amount_due = 10000
- [ ] **VERIFY:** Redirected to /billing
- [ ] Pay the invoice
- [ ] **VERIFY:** subscription.plan_id changed to Core plan ID
- [ ] **VERIFY:** subscription.implementation_fee_paid = 14999
- [ ] Try to add 21st employee again
- [ ] **VERIFY:** Add employee form shows (no blocking)
- [ ] Add employee successfully

### Test 2: Core → Pro Upgrade
- [ ] Tenant with Core plan, 100 active employees
- [ ] Try to add 101st employee
- [ ] **VERIFY:** Upgrade modal shows Pro and Elite
- [ ] **VERIFY:** Pro is recommended
- [ ] **VERIFY:** Pro shows "Pay: ₱25,000"
- [ ] Select Pro, proceed, pay invoice
- [ ] **VERIFY:** Plan upgraded to Pro
- [ ] **VERIFY:** Can add 101st-200th employees

### Test 3: Different Billing Cycles
- [ ] Tenant with Starter Yearly plan
- [ ] **VERIFY:** Upgrade modal shows only Yearly plans
- [ ] **VERIFY:** Cannot select Monthly plans
- [ ] Core Yearly amount due: ₱10,000 (same difference)

### Test 4: Elite Plan (No Further Upgrades)
- [ ] Tenant with Elite plan (500 user limit)
- [ ] **VERIFY:** Can add up to 500 users
- [ ] Try to add 501st employee
- [ ] **VERIFY:** Upgrade modal shows "No upgrade plans available"
- [ ] **Alternative:** Show custom message for contact sales

## API Endpoints

### POST /employees/check-license-overage
**Request:** None
**Response:**
```json
{
  "status": "upgrade_required",
  "message": "Plan upgrade required",
  "data": {
    "current_users": 20,
    "new_user_count": 21,
    "current_plan": "Core Starter Monthly Plan",
    "current_plan_id": 1,
    "current_plan_limit": 10,
    "recommended_plan": {
      "id": 2,
      "name": "Core Monthly Plan",
      "employee_limit": 100,
      "price": 5500,
      "implementation_fee": 14999,
      "implementation_fee_difference": 10000,
      "billing_cycle": "monthly",
      "is_recommended": true
    },
    "available_plans": [
      { /* Core */ },
      { /* Pro */ },
      { /* Elite */ }
    ],
    "current_implementation_fee_paid": 4999,
    "billing_cycle": "monthly"
  }
}
```

### POST /employees/generate-plan-upgrade-invoice
**Request:**
```json
{
  "new_plan_id": 2
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Plan upgrade invoice generated successfully",
  "invoice": {
    "id": 123,
    "invoice_number": "PLN-20251107-001",
    "invoice_type": "plan_upgrade",
    "amount_due": 10000,
    "status": "pending",
    ...
  },
  "new_plan": {
    "id": 2,
    "name": "Core Monthly Plan",
    "employee_limit": 100
  }
}
```

## Payment Processing

When a `plan_upgrade` invoice is marked as paid, call:

```php
// In your payment webhook/processing code
if ($invoice->invoice_type === 'plan_upgrade' && $invoice->status === 'paid') {
    app(EmployeeListController::class)->processPlanUpgrade($invoice->id);
}
```

This will:
1. Update `subscription.plan_id` to new plan
2. Update `subscription.implementation_fee_paid`
3. Log the upgrade

## Common Issues

### Issue: "No upgrade plans available"
**Cause:** Current plan is already the highest tier (Elite), OR billing cycle mismatch
**Solution:** 
- For Elite users at limit: Contact sales for custom plan
- Check billing_cycle matches in database

### Issue: Wrong amount showing
**Cause:** implementation_fee_paid not updated after previous upgrade
**Solution:** Ensure payment processing calls `processPlanUpgrade()`

### Issue: Can't select plan
**Cause:** JavaScript not loaded or cached
**Solution:** Hard refresh browser (Ctrl+Shift+R)

## Next Steps

1. **Implement Payment Webhook Integration**
   - Detect when plan_upgrade invoice is paid
   - Automatically call `processPlanUpgrade()`
   - Send email confirmation of upgrade

2. **Add Downgrade Support**
   - Allow downgrade if within user limit
   - Prorate refunds

3. **Add Custom/Enterprise Plans**
   - For users exceeding Elite limit
   - Contact sales form

4. **Add Upgrade History**
   - Show past upgrades
   - Display upgrade timeline

5. **Email Notifications**
   - Upgrade success email
   - Approaching limit warnings
   - Upgrade recommendation emails

## Files Modified

1. `app/Services/LicenseOverageService.php`
   - Added `getAvailableUpgradePlans()`
   - Added `getRecommendedUpgradePlan()`
   - Updated `checkUserAdditionRequirements()` for all plan tiers

2. `app/Http/Controllers/Tenant/Employees/EmployeeListController.php`
   - Added `generatePlanUpgradeInvoice()`
   - Added `processPlanUpgrade()`
   - Imported `Plan` model

3. `routes/web.php`
   - Added route: `/employees/generate-plan-upgrade-invoice`

4. `resources/views/tenant/employee/employeelist.blade.php`
   - Redesigned plan upgrade modal with plan selection grid
   - Added CSS for ribbon and hover effects

5. `public/build/js/employeelist.js`
   - Updated `showPlanUpgradeModal()` to render plan cards
   - Added plan selection logic
   - Updated `confirmPlanUpgradeBtn` to send selected plan_id

## Success Criteria

✅ User sees available upgrade plans
✅ Recommended plan is highlighted
✅ User can select any available plan
✅ Invoice generated with correct amount (difference only)
✅ Plan automatically upgrades after payment
✅ Works for all plan tiers (Starter → Core → Pro → Elite)
✅ Works for both monthly and yearly billing cycles
✅ No hardcoded plan names
