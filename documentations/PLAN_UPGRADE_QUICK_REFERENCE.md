# Plan Upgrade - Quick Reference Guide

## ✅ What's Been Fixed

### Before
- ❌ No plan selection - just redirected to billing
- ❌ Hardcoded to "Core" plan only
- ❌ Plan didn't actually upgrade after payment
- ❌ No support for Pro/Elite tiers

### After
- ✅ Interactive plan selection modal
- ✅ Shows all available upgrades (Core, Pro, Elite)
- ✅ Recommended plan highlighted
- ✅ Plan automatically upgrades after payment
- ✅ Works for all tiers (Starter → Core → Pro → Elite)
- ✅ Supports both monthly & yearly billing

## 🎯 How It Works Now

### Step 1: User Reaches Limit
```
Starter Plan: 10 users (with impl. fee) → 20 users max
Core Plan: 100 users max
Pro Plan: 200 users max  
Elite Plan: 500 users max
```

### Step 2: Upgrade Modal Shows
When trying to add user beyond limit:

```
┌─────────────────────────────────────┐
│ Plan Upgrade Required               │
│                                     │
│ Current: Starter (10 users)         │
│ Active Users: 20                    │
│ After Adding: 21                    │
│                                     │
│ Select Your Upgrade Plan:           │
│                                     │
│ [CORE - RECOMMENDED]  [PRO]  [ELITE]│
│  ₱5,500/mo            ₱9,500  ₱14,500│
│  100 users           200     500    │
│  Pay: ₱10,000        ₱35,000 ₱75,000│
│  [Select]            [Select][Select]│
│                                     │
│ [Cancel] [Proceed with Upgrade]     │
└─────────────────────────────────────┘
```

### Step 3: User Selects Plan
- Click any plan card
- Summary shows with amount breakdown
- "Proceed with Upgrade" button enables

### Step 4: Invoice Generated
- POST to `/employees/generate-plan-upgrade-invoice`
- Invoice created with type `plan_upgrade`
- Amount = difference in implementation fees
- User redirected to `/billing`

### Step 5: Payment & Upgrade
- User pays invoice
- `processPlanUpgrade()` called automatically
- Subscription upgraded to new plan
- implementation_fee_paid updated
- User can now add employees

## 💰 Implementation Fee Differences

| From → To | Monthly | Amount to Pay |
|-----------|---------|---------------|
| Starter → Core | ₱5,500 | ₱10,000 |
| Starter → Pro | ₱9,500 | ₱35,000 |
| Starter → Elite | ₱14,500 | ₱75,000 |
| Core → Pro | ₱9,500 | ₱25,000 |
| Core → Elite | ₱14,500 | ₱65,000 |
| Pro → Elite | ₱14,500 | ₱40,000 |

*Only pay the difference in implementation fees!*

## 🔧 Quick Test

### Test Plan Upgrade Flow:
```bash
# 1. Clear caches
php artisan cache:clear && php artisan config:clear && php artisan view:clear

# 2. Verify routes
php artisan route:list | grep plan-upgrade

# 3. Test in browser
# - Create tenant with Starter plan
# - Add 20 employees
# - Try to add 21st employee
# - Should see plan selection modal
# - Select Core plan
# - Verify invoice generated
# - Pay invoice
# - Verify plan upgraded
```

## 📝 For Developers

### Backend Endpoints

**Check Requirements:**
```http
POST /employees/check-license-overage
Returns: available_plans array with all upgrade options
```

**Generate Upgrade Invoice:**
```http
POST /employees/generate-plan-upgrade-invoice
Body: { "new_plan_id": 2 }
Returns: invoice with plan_upgrade type
```

### Key Methods

**Service (LicenseOverageService.php):**
- `getAvailableUpgradePlans($subscription)` - Get all plans user can upgrade to
- `getRecommendedUpgradePlan($subscription)` - Get next tier plan
- `checkUserAdditionRequirements($tenantId)` - Check if upgrade needed

**Controller (EmployeeListController.php):**
- `generatePlanUpgradeInvoice(Request $request)` - Create invoice with selected plan
- `processPlanUpgrade($invoiceId)` - Upgrade plan after payment

### Frontend (employeelist.js)

**Key Functions:**
- `showPlanUpgradeModal(data, form)` - Render plan cards
- Plan selection: Click handler on `.plan-option`
- Confirm: `$('#confirmPlanUpgradeBtn').click()`

## ⚠️ Important Notes

1. **Billing Cycle Must Match**
   - Monthly plans can only upgrade to monthly plans
   - Yearly plans can only upgrade to yearly plans

2. **Implementation Fee Tracking**
   - Always update `implementation_fee_paid` after payment
   - Use `processPlanUpgrade()` to handle this

3. **Payment Processing**
   - When plan_upgrade invoice is paid, call:
   ```php
   if ($invoice->invoice_type === 'plan_upgrade' && $invoice->status === 'paid') {
       app(EmployeeListController::class)->processPlanUpgrade($invoice->id);
   }
   ```

4. **Elite Plan Limit**
   - If user exceeds Elite (500 users), show "Contact Sales"
   - No automatic upgrade available beyond Elite

## 🐛 Troubleshooting

### Modal doesn't show plan options
- **Check:** Browser console for JavaScript errors
- **Fix:** Hard refresh (Ctrl+Shift+R)
- **Verify:** `employeelist.js` is loaded

### Wrong amount showing
- **Check:** implementation_fee_paid value in database
- **Fix:** Ensure payment webhook calls processPlanUpgrade()

### Can't select plan
- **Check:** Click handler attached to `.plan-option`
- **Fix:** Verify jQuery loaded, no conflicting scripts

### Routes not found
- **Fix:** 
  ```bash
  php artisan route:clear
  php artisan route:cache
  ```

## 📚 Documentation Files

1. `PLAN_UPGRADE_FEATURE_SUMMARY.md` - Complete feature documentation
2. `IMPLEMENTATION_FEE_AND_PLAN_UPGRADE_SUMMARY.md` - Overall system summary
3. `FLOW_DIAGRAMS.md` - Visual flow diagrams
4. `TROUBLESHOOTING_IMPLEMENTATION_FEE.md` - Common issues & solutions

## ✨ Features Summary

✅ **Dynamic Plan Detection** - No hardcoded plans
✅ **Interactive UI** - Beautiful plan selection cards
✅ **Smart Recommendations** - Highlights next tier
✅ **Flexible Upgrades** - User can choose any plan
✅ **Auto-Processing** - Plan upgrades after payment
✅ **Multi-Tier Support** - Starter → Core → Pro → Elite
✅ **Billing Cycle Aware** - Matches monthly/yearly correctly

## 🎉 Ready to Use!

The system is now fully functional with dynamic plan upgrades. Users can select their preferred plan and only pay the difference in implementation fees.
