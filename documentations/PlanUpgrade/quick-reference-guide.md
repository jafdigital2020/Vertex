# Plan Upgrade Quick Reference Guide

## 🎯 At-a-Glance Summary

This is your **quick reference guide** for understanding the Vertex HRMS plan upgrade and implementation fee flow. For detailed decision trees and technical documentation, see:
- `plan-upgrade-flow.md` - Complete technical documentation
- `complete-upgrade-decision-tree.md` - Detailed decision trees for all plans
- `visual-flow-quick-reference.md` - Visual flow diagrams

---

## 📊 Plan Comparison Table

| Feature | Starter | Core | Pro | Elite |
|---------|---------|------|-----|-------|
| **Monthly Price** | ₱5,000 | ₱5,500 | ₱9,500 | ₱14,500 |
| **Yearly Price** | ₱57,000 | ₱62,700 | ₱108,300 | ₱165,300 |
| **Base Licenses** | 10 | 100 | 200 | 500 |
| **Max Licenses** | 20 (with impl. fee) | Unlimited | Unlimited | Unlimited |
| **Implementation Fee** | ₱4,999 | ₱14,999 | ₱39,999 | ₱79,999 |
| **Overage Rate** | ₱49/user/month | ₱49/user/month | ₱49/user/month | ₱49/user/month |
| **Overage Available** | Yes (11-20 users) | Yes | Yes | Yes |
| **Can Upgrade** | ✅ Yes | ✅ Yes | ✅ Yes | ❌ Highest tier |

---

## 💰 Implementation Fee Upgrade Costs

### Quick Calculation Formula

```
Amount to Pay = New Plan Impl. Fee - Already Paid Impl. Fee
```

### Upgrade Cost Matrix

|  | **To: Core** | **To: Pro** | **To: Elite** |
|---|-------------|------------|--------------|
| **From: Starter** | ₱10,000 | ₱35,000 | ₱75,000 |
| **From: Core** | — | ₱25,000 | ₱65,000 |
| **From: Pro** | — | — | ₱40,000 |

**Example:** Starter → Core
- Core impl. fee: ₱14,999
- Already paid (Starter): ₱4,999
- **You pay: ₱10,000**

---

## 🔄 When Do I Need to Pay?

### Scenario Quick Reference

| Situation | Modal Shown | Fee Type | Amount |
|-----------|------------|----------|--------|
| **Starter: Add 11th-20th user (first time)** | Implementation Fee Modal | One-time setup | ₱4,999 |
| **Starter: Add 11th-20th user (fee paid)** | Overage Confirmation | Monthly overage | ₱49 × users |
| **Starter: Add 21st+ user** | Plan Upgrade Modal (FORCED) | Upgrade + impl. diff | Varies |
| **Core/Pro/Elite: Over base limit** | Overage Confirmation | Monthly overage | ₱49 × users |
| **Any: Voluntary upgrade** | Plan Upgrade Modal | Upgrade + impl. diff | Varies |

---

## 📋 What Happens When You Upgrade?

### Step-by-Step Process

```
1️⃣ Select New Plan
   └─> Choose from available higher-tier plans

2️⃣ System Creates TWO Invoices
   ├─> Invoice #1: Plan Upgrade (prorated subscription cost)
   └─> Invoice #2: Implementation Fee Difference

3️⃣ Pay Both Invoices
   └─> Redirect to billing page → Pay Now

4️⃣ Subscription Updated
   ├─> New plan activated
   ├─> New license limit applied
   ├─> New monthly/yearly rate
   └─> Implementation fee total updated

5️⃣ Add Employee
   └─> Now within new plan limits
```

---

## 🎨 How Invoices Appear

### Implementation Fee Invoice

```
┌─────────────────────────────────────────┐
│ 🟡 IMPLEMENTATION FEE                   │
├─────────────────────────────────────────┤
│ Description:                            │
│ Implementation Fee: Core Monthly Plan   │
│                                         │
│ Details:                                │
│ Already Paid:    ₱4,999                │
│ Total Fee:       ₱14,999               │
│ Amount Due:      ₱10,000               │
└─────────────────────────────────────────┘
```

### Plan Upgrade Invoice

```
┌─────────────────────────────────────────┐
│ 🟢 PLAN UPGRADE                         │
├─────────────────────────────────────────┤
│ Description:                            │
│ Plan Upgrade: Core Monthly Plan         │
│ ↑ From: Starter Monthly Plan            │
│                                         │
│ Prorated Amount: ₱XXX                   │
└─────────────────────────────────────────┘
```

### Overage Invoice

```
┌─────────────────────────────────────────┐
│ 🔵 LICENSE OVERAGE                      │
├─────────────────────────────────────────┤
│ Description:                            │
│ License Overage: 5 users                │
│                                         │
│ Details:                                │
│ 5 users × ₱49 = ₱245                    │
└─────────────────────────────────────────┘
```

---

## ⚡ Common Questions & Answers

### Q: What is an implementation fee?
**A:** A one-time setup fee for configuring and integrating the HRMS system for your organization. Each plan tier has a different implementation fee based on the complexity and features.

### Q: Why do I pay implementation fee again when upgrading?
**A:** You don't pay it again fully! You only pay the **difference** between your current plan's fee and the new plan's fee.

### Q: Can I go back to a lower plan?
**A:** No, the system only supports **one-way upgrades**. Contact support if you need to discuss plan changes.

### Q: What happens if I have 15 users on Starter plan?
**A:** 
- Base: 10 users (included in ₱5,000/month)
- Overage: 5 users × ₱49 = ₱245/month
- Total: ₱5,245/month
- You must have paid the ₱4,999 implementation fee first

### Q: When am I forced to upgrade?
**A:** Only when you're on **Starter plan** and try to add your **21st employee**. All other plans allow overages.

### Q: How much does it cost to upgrade from Starter to Pro?
**A:**
- Implementation fee difference: ₱35,000 (one-time)
- Monthly subscription: ₱9,500 (ongoing)
- Total first payment: ₱35,000 + prorated subscription

---

## 🛠️ Technical Quick Reference

### Key Files

```
Backend:
├── app/Services/LicenseOverageService.php
├── app/Http/Controllers/Tenant/Employees/EmployeeListController.php
├── app/Models/Invoice.php (upgrade_plan_id relationship)
└── app/Models/Subscription.php

Frontend:
├── resources/views/tenant/employee/employeelist.blade.php
├── public/build/js/employeelist.js
└── resources/views/tenant/billing/billing.blade.php

Database:
└── database/migrations/2025_11_07_202151_add_upgrade_plan_id_to_invoices_table.php
```

### Key Functions

```php
// Check if user can add employee
LicenseOverageService::checkUserAdditionRequirements($tenantId)
→ Returns: 'can_add', 'needs_implementation_fee', 'needs_plan_upgrade'

// Create implementation fee invoice
LicenseOverageService::createImplementationFeeInvoice($subscription, $implementationFee)

// Create plan upgrade invoice
LicenseOverageService::createPlanUpgradeInvoice($subscription, $newPlan, $proratedAmount)

// Get available upgrade plans
LicenseOverageService::getAvailableUpgradePlans($subscription)
```

### Important Database Fields

```sql
invoices table:
├── invoice_type (enum: subscription, license_overage, implementation_fee, plan_upgrade)
├── upgrade_plan_id (NEW - stores new plan for upgrades)
├── implementation_fee (decimal)
├── license_overage_count (integer)
└── license_overage_amount (decimal)

subscriptions table:
├── plan_id (current active plan)
├── implementation_fee_paid (total paid so far)
└── active_license (current user count)
```

---

## 🎯 Business Rules Checklist

- [x] Implementation fees are **one-time per plan tier**
- [x] Only pay the **difference** when upgrading
- [x] **No downgrades** - upgrades only
- [x] Starter plan has **max 20 users** (with impl. fee)
- [x] All other plans have **unlimited users** (with overage fees)
- [x] Overage rate is **₱49/user/month** for all plans
- [x] Billing cycle must match (monthly → monthly, yearly → yearly)
- [x] **Two invoices** created on upgrade (plan + impl. fee)
- [x] `upgrade_plan_id` used to display **correct plan** in invoices

---

## 📈 Typical User Journeys

### Journey 1: Small Business Growth (Starter → Core)

```
Day 1:   Subscribe to Starter (10 users) - ₱5,000/month
Month 3: Hire 11th employee
         → Pay ₱4,999 implementation fee
         → Can now have up to 20 users
Month 6: Hiring spree! Need to add 21st employee
         → Forced to upgrade to Core
         → Pay ₱10,000 impl. fee difference
         → New rate: ₱5,500/month
         → Can now have up to 100 users
Month 12: Happy with Core, 85 users, no more fees
```

### Journey 2: Medium Business (Start with Core)

```
Day 1:   Subscribe to Core (100 users) - ₱5,500/month
         → Pay ₱14,999 implementation fee upfront
Month 1: Add 100 employees
         → All within limit, no extra cost
Month 6: Growing! Add 101st-110th employees
         → Overage: 10 × ₱49 = ₱490/month
         → Total: ₱5,990/month
Month 12: Decide to upgrade to Pro to avoid overage
         → Pay ₱25,000 impl. fee difference
         → New rate: ₱9,500/month (saves on overage)
```

---

## 🎨 Modal Screenshots Reference

### 1. Implementation Fee Modal (Starter Plan)
**Shown when:** Starter user adds 11th-20th employee (fee not paid)

**Contains:**
- Current employee count
- New employee count
- Implementation fee: ₱4,999
- Benefits explanation
- "Pay Implementation Fee" button
- "Cancel" button

---

### 2. License Overage Confirmation Modal
**Shown when:** Any plan exceeds base limit (except Starter at 21+)

**Contains:**
- Current plan name
- Current vs new employee count
- Overage count
- Rate: ₱49/user/month
- Total overage cost
- "Pay & Continue" button
- "Cancel" button

---

### 3. Plan Upgrade Modal
**Shown when:** 
- Starter user tries to add 21st+ employee (FORCED)
- User voluntarily upgrades from Billing page

**Contains:**
- Current plan details
- Available upgrade plans (cards)
- Each plan shows:
  - Plan name
  - License limit
  - Monthly/yearly price
  - Implementation fee (full + difference)
  - "Select Plan" button
- Recommended plan badge
- "Cancel" button

---

## 📞 Support Cheat Sheet

### Customer Says: "I can't add more employees!"

**Check:**
1. What plan are they on?
2. How many active users?
3. What's their plan's base limit?
4. Is implementation fee paid? (check `implementation_fee_paid`)

**Solutions:**
- Starter (1-10): OK to add
- Starter (10+, fee NOT paid): Need to pay ₱4,999
- Starter (11-20, fee paid): Overage fees apply
- Starter (20+): Must upgrade
- Other plans: Overage fees apply

---

### Customer Says: "Why am I paying implementation fee twice?"

**Explain:**
"You're not! When you upgrade, you only pay the difference:
- You already paid ₱X,XXX for [Current Plan]
- [New Plan] requires ₱X,XXX
- You only pay ₱X,XXX more (the difference)
- Think of it as a credit for what you've already paid!"

---

### Customer Says: "Can I downgrade to save money?"

**Response:**
"Our system doesn't support automatic downgrades. Here's why:
- Your implementation fee is an investment in your setup
- Higher plans include more features and integrations
- Your data and configurations are already scaled up

However, if you'd like to discuss your needs, I can connect you with our account team to see if there are other ways we can help optimize your costs."

---

## 🔍 Troubleshooting

### Issue: User paid impl. fee but still can't add employees

**Check:**
```sql
-- Check subscription record
SELECT 
    id,
    plan_id,
    implementation_fee_paid,
    active_license,
    license_limit
FROM subscriptions 
WHERE tenant_id = 'XXX';

-- Check invoice status
SELECT 
    id,
    invoice_type,
    implementation_fee,
    status,
    paid_at
FROM invoices 
WHERE subscription_id = XXX 
    AND invoice_type = 'implementation_fee'
ORDER BY created_at DESC;
```

**Fix:**
- If invoice is paid but `implementation_fee_paid` = 0, update manually
- Verify webhook processed payment correctly

---

### Issue: Wrong plan name shown in invoice

**Check:**
```sql
-- Check invoice record
SELECT 
    id,
    invoice_type,
    plan_id,
    upgrade_plan_id,
    description
FROM invoices 
WHERE id = XXX;
```

**Fix:**
- For `plan_upgrade` or `implementation_fee` invoices, ensure `upgrade_plan_id` is set
- Frontend should use `upgradePlan` relationship, not `plan` relationship
- Check billing.blade.php and BillingController

---

### Issue: Two implementation fee invoices for same upgrade

**Root Cause:**
- User double-clicked "Select Plan" button
- AJAX call made twice

**Prevention:**
- Disable button after click
- Check for existing pending invoices before creating new

---

## ✅ Launch Checklist

Before going live with plan upgrade feature:

### Database
- [ ] Migration run: `upgrade_plan_id` column added to invoices
- [ ] All plans have correct implementation fees in `plans` table
- [ ] Test subscriptions have correct `implementation_fee_paid` values

### Backend
- [ ] `LicenseOverageService` tested for all scenarios
- [ ] Invoice generation creates correct invoice types
- [ ] `upgrade_plan_id` properly set for upgrade invoices
- [ ] Payment webhook updates `implementation_fee_paid`
- [ ] Subscription plan changes on successful payment

### Frontend
- [ ] All modals display correctly
- [ ] Plan cards are selectable
- [ ] Correct plan names shown in invoices
- [ ] PDF downloads show correct plan
- [ ] No duplicate modal IDs
- [ ] Event delegation works for dynamic content

### Testing
- [ ] Starter: Add 11th employee (impl. fee not paid)
- [ ] Starter: Add 11th employee (impl. fee paid)
- [ ] Starter: Add 21st employee (forced upgrade)
- [ ] Core: Add 101st employee (overage)
- [ ] Voluntary upgrade from each plan
- [ ] Invoice display shows correct plan name
- [ ] PDF generation works correctly

---

## 📚 Additional Resources

- **Full Documentation:** `plan-upgrade-flow.md`
- **Decision Trees:** `complete-upgrade-decision-tree.md`
- **Visual Diagrams:** `visual-flow-quick-reference.md`
- **Code Files:** See "Technical Quick Reference" section above

---

## 🎉 Quick Win Tips

1. **For Customers:** Always explain implementation fees as "one-time setup that carries forward"
2. **For Support:** Use the "Upgrade Cost Matrix" to quickly calculate costs
3. **For Devs:** Always check `upgrade_plan_id` when displaying plan names in invoices
4. **For Testing:** Use the "Testing Checklist" in `complete-upgrade-decision-tree.md`

---

*Last Updated: November 7, 2025*
*Version: 1.0*
*Quick Reference - Print & Keep Handy!*
