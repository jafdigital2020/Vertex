# Plan Upgrade Decision Tree - Starter to Elite
## Complete Flow Without Overage for Core/Pro/Elite Plans

---

## 📋 Overview

This document provides the **complete decision tree** for all plan types where:
- **Starter Plan**: Allows overage (11-20 users) with implementation fee
- **Core, Pro, Elite Plans**: **NO OVERAGE ALLOWED** - Must upgrade when limit is reached

---

## 🎯 Key Business Rules

| Plan | Base Limit | Overage Allowed? | Max with Overage | Action When Full |
|------|-----------|------------------|------------------|------------------|
| **Starter** | 10 users | ✅ Yes (with impl. fee) | 20 users | Pay ₱4,999 impl. fee |
| **Core** | 100 users | ❌ NO | N/A | Must upgrade to Pro/Elite |
| **Pro** | 200 users | ❌ NO | N/A | Must upgrade to Elite |
| **Elite** | 500 users | ❌ NO | N/A | Contact sales (highest plan) |

---

## 🌳 Complete Decision Tree

### STARTER PLAN (10 users base, 20 max)

```
                        ADD EMPLOYEE ON STARTER PLAN
                                    │
                                    ▼
                        Check Current User Count
                                    │
        ┌───────────────────────────┼───────────────────────────┐
        │                           │                           │
        ▼                           ▼                           ▼
    Count: 1-10              Count: 11-20                  Count: 21+
    (Under Base)             (Overage Range)              (Exceeds Max)
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────┐     ┌─────────────────────────┐    ┌──────────────────────┐
│ ✅ ADD OK     │     │ Check Implementation    │    │ 🚫 UPGRADE REQUIRED  │
│ No Fee        │     │ Fee Status              │    │ (FORCED)             │
└───────────────┘     └─────────┬───────────────┘    └──────────┬───────────┘
                                │                               │
                    ┌───────────┴─────────┐                     │
                    ▼                     ▼                     │
            ┌──────────────┐      ┌──────────────┐             │
            │ Fee NOT Paid │      │ Fee PAID     │             │
            └──────┬───────┘      └──────┬───────┘             │
                   │                     │                     │
                   ▼                     ▼                     │
        ┌──────────────────┐    ┌──────────────┐             │
        │ Show Impl. Fee   │    │ ✅ ADD OK    │             │
        │ Modal: ₱4,999    │    │ (11-20 range)│             │
        └────────┬─────────┘    └──────────────┘             │
                 │                                             │
                 ▼                                             │
        ┌──────────────────┐                                  │
        │ User Pays ₱4,999 │                                  │
        └────────┬─────────┘                                  │
                 │                                             │
                 ▼                                             │
        ┌──────────────────┐                                  │
        │ ✅ Can Now Add   │                                  │
        │ Users 11-20      │                                  │
        └──────────────────┘                                  │
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ Show Plan Upgrade    │
                                                    │ Modal (REQUIRED)     │
                                                    │                      │
                                                    │ Available Plans:     │
                                                    │ • Core (100 users)   │
                                                    │ • Pro (200 users)    │
                                                    │ • Elite (500 users)  │
                                                    └──────────┬───────────┘
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ User Selects Plan    │
                                                    └──────────┬───────────┘
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ Create 2 Invoices:   │
                                                    │ 1. Plan Upgrade      │
                                                    │ 2. Impl. Fee Diff    │
                                                    └──────────┬───────────┘
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ Payment & Upgrade    │
                                                    │ Complete             │
                                                    └──────────┬───────────┘
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ ✅ Employee Added    │
                                                    └──────────────────────┘
```

---

### CORE PLAN (100 users, NO OVERAGE)

```
                        ADD EMPLOYEE ON CORE PLAN
                                    │
                                    ▼
                        Check Current User Count
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
                    ▼                               ▼
            Count: 1-100                       Count: 101+
            (Within Limit)                    (Exceeds Limit)
                    │                               │
                    ▼                               ▼
            ┌───────────────┐               ┌────────────────────┐
            │ ✅ ADD OK     │               │ 🚫 UPGRADE REQUIRED│
            │ No Fee        │               │ (NO OVERAGE)       │
            └───────────────┘               └──────────┬─────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ Show Plan Upgrade    │
                                            │ Modal (REQUIRED)     │
                                            │                      │
                                            │ Available Plans:     │
                                            │ • ✨ Pro (200 users) │
                                            │ • Elite (500 users)  │
                                            └──────────┬───────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ User Selects Plan    │
                                            └──────────┬───────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ Create 2 Invoices:   │
                                            │ 1. Plan Upgrade      │
                                            │ 2. Impl. Fee Diff:   │
                                            │    ₱25,000 (Pro)     │
                                            │    ₱65,000 (Elite)   │
                                            └──────────┬───────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ Payment & Upgrade    │
                                            │ Complete             │
                                            └──────────┬───────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ ✅ Employee Added    │
                                            └──────────────────────┘
```

---

### PRO PLAN (200 users, NO OVERAGE)

```
                        ADD EMPLOYEE ON PRO PLAN
                                    │
                                    ▼
                        Check Current User Count
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
                    ▼                               ▼
            Count: 1-200                       Count: 201+
            (Within Limit)                    (Exceeds Limit)
                    │                               │
                    ▼                               ▼
            ┌───────────────┐               ┌────────────────────┐
            │ ✅ ADD OK     │               │ 🚫 UPGRADE REQUIRED│
            │ No Fee        │               │ (NO OVERAGE)       │
            └───────────────┘               └──────────┬─────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ Show Plan Upgrade    │
                                            │ Modal (REQUIRED)     │
                                            │                      │
                                            │ Available Plan:      │
                                            │ • ✨ Elite (500 usr) │
                                            └──────────┬───────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ Create 2 Invoices:   │
                                            │ 1. Plan Upgrade      │
                                            │ 2. Impl. Fee Diff:   │
                                            │    ₱40,000           │
                                            └──────────┬───────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ Payment & Upgrade    │
                                            │ Complete             │
                                            └──────────┬───────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ ✅ Employee Added    │
                                            └──────────────────────┘
```

---

### ELITE PLAN (500 users, NO OVERAGE - Highest Tier)

```
                        ADD EMPLOYEE ON ELITE PLAN
                                    │
                                    ▼
                        Check Current User Count
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
                    ▼                               ▼
            Count: 1-500                       Count: 501+
            (Within Limit)                    (Exceeds Limit)
                    │                               │
                    ▼                               ▼
            ┌───────────────┐               ┌────────────────────┐
            │ ✅ ADD OK     │               │ 🚫 CONTACT SALES   │
            │ No Fee        │               │ (No Higher Plan)   │
            └───────────────┘               └──────────┬─────────┘
                                                       │
                                                       ▼
                                            ┌──────────────────────┐
                                            │ Show Contact Sales   │
                                            │ Modal/Message        │
                                            │                      │
                                            │ "You've reached the  │
                                            │ maximum capacity of  │
                                            │ our Elite plan.      │
                                            │ Please contact our   │
                                            │ sales team for       │
                                            │ enterprise options." │
                                            └──────────────────────┘
```

---

## 📊 Complete Upgrade Matrix

### All Possible Upgrade Paths

```
FROM          TO           Impl. Fee     Monthly Cost    Monthly Cost
PLAN          PLAN         Difference    Increase        Change
────────────  ───────────  ────────────  ──────────────  ─────────────
Starter  →    Core         ₱10,000       +₱500           ₱5,000→₱5,500
Starter  →    Pro          ₱35,000       +₱4,500         ₱5,000→₱9,500
Starter  →    Elite        ₱75,000       +₱9,500         ₱5,000→₱14,500

Core     →    Pro          ₱25,000       +₱4,000         ₱5,500→₱9,500
Core     →    Elite        ₱65,000       +₱9,000         ₱5,500→₱14,500

Pro      →    Elite        ₱40,000       +₱5,000         ₱9,500→₱14,500
```

---

## 💡 Business Logic Summary

### Starter Plan (Special Case - Overage Allowed)

1. **Users 1-10**: Add freely ✅
2. **User 11 (first overage)**: 
   - If impl. fee NOT paid → Require ₱4,999 payment
   - If impl. fee paid → Add user ✅
3. **Users 12-20**: Add with overage awareness (₱49/user/month extra)
4. **User 21+**: **FORCED UPGRADE** - Cannot proceed without upgrading

### Core Plan (NO Overage)

1. **Users 1-100**: Add freely ✅
2. **User 101+**: **FORCED UPGRADE** to Pro or Elite

### Pro Plan (NO Overage)

1. **Users 1-200**: Add freely ✅
2. **User 201+**: **FORCED UPGRADE** to Elite

### Elite Plan (NO Overage - Highest Plan)

1. **Users 1-500**: Add freely ✅
2. **User 501+**: Contact sales for enterprise solutions

---

## 🔑 Key Differences from Previous Implementation

### ❌ REMOVED: Overage option for Core/Pro/Elite

**OLD BEHAVIOR:**
- Core plan user adds 101st employee → Show overage modal (₱49/user)
- Pro plan user adds 201st employee → Show overage modal (₱49/user)

**NEW BEHAVIOR:**
- Core plan user adds 101st employee → Show upgrade modal (REQUIRED)
- Pro plan user adds 201st employee → Show upgrade modal (REQUIRED)

### ✅ RETAINED: Overage option for Starter (11-20 only)

**Starter plan maintains special behavior:**
- Users 11-20 can be added with overage after paying implementation fee
- User 21+ triggers FORCED upgrade

---

## 🎨 Modal Behavior

### 1. Starter Plan - Implementation Fee Modal (Users 11-20)
**Triggered**: Adding 11th employee when impl. fee NOT paid

```
┌──────────────────────────────────────────┐
│ 💰 Implementation Fee Required           │
├──────────────────────────────────────────┤
│ Your Starter plan includes 10 users.     │
│                                           │
│ To add users 11-20, you need to pay a    │
│ one-time implementation fee:              │
│                                           │
│ Amount: ₱4,999.00                         │
│                                           │
│ After payment, you can add up to 20      │
│ total users with additional ₱49/user/mo  │
│                                           │
│ [Cancel]  [💳 Pay Implementation Fee]    │
└──────────────────────────────────────────┘
```

### 2. Plan Upgrade Modal (All Plans at Limit)
**Triggered**: 
- Starter: Adding 21st+ employee
- Core: Adding 101st+ employee
- Pro: Adding 201st+ employee

```
┌──────────────────────────────────────────┐
│ 🚀 Plan Upgrade Required                 │
├──────────────────────────────────────────┤
│ You've reached the maximum capacity of   │
│ your [Current Plan] plan.                │
│                                           │
│ Current Users: XX                         │
│ Plan Limit: XX                            │
│                                           │
│ Select an upgrade plan to continue:      │
│                                           │
│ [Available upgrade plan cards]            │
│                                           │
│ [Cancel]  [💰 Proceed with Upgrade]      │
└──────────────────────────────────────────┘
```

### 3. Elite Plan - Contact Sales Modal (501+ users)
**Triggered**: Elite plan user adding 501st+ employee

```
┌──────────────────────────────────────────┐
│ 📞 Enterprise Solutions Required         │
├──────────────────────────────────────────┤
│ Congratulations! You've reached the      │
│ maximum capacity of our Elite plan.      │
│                                           │
│ For organizations with more than 500     │
│ employees, we offer custom enterprise    │
│ solutions.                                │
│                                           │
│ Please contact our sales team:           │
│ • Email: sales@timora.ph                 │
│ • Phone: +63 XXX XXXX                    │
│                                           │
│ We'll create a custom plan tailored to   │
│ your organization's needs.               │
│                                           │
│ [OK]                                      │
└──────────────────────────────────────────┘
```

---

## 📋 Testing Scenarios

### Test Case 1: Starter Plan Journey

```
✅ Add employees 1-10 → Should succeed
✅ Add 11th employee (impl. fee not paid) → Show impl. fee modal
✅ Pay ₱4,999 → Implementation fee invoice created
✅ Add 11th employee again → Should succeed
✅ Add employees 12-20 → Should succeed
❌ Add 21st employee → Show upgrade modal (FORCED)
✅ Select Core plan → Create 2 invoices (upgrade + impl. fee diff ₱10,000)
✅ Pay invoices → Subscription upgraded
✅ Add 21st employee → Should succeed (now on Core plan)
```

### Test Case 2: Core Plan Journey

```
✅ Add employees 1-100 → Should succeed
❌ Add 101st employee → Show upgrade modal (REQUIRED, NO overage option)
✅ Available plans → Pro and Elite only
✅ Select Pro plan → Create 2 invoices (upgrade + impl. fee diff ₱25,000)
✅ Pay invoices → Subscription upgraded
✅ Add 101st employee → Should succeed (now on Pro plan)
```

### Test Case 3: Pro Plan Journey

```
✅ Add employees 1-200 → Should succeed
❌ Add 201st employee → Show upgrade modal (REQUIRED, NO overage option)
✅ Available plan → Elite only
✅ Select Elite plan → Create 2 invoices (upgrade + impl. fee diff ₱40,000)
✅ Pay invoices → Subscription upgraded
✅ Add 201st employee → Should succeed (now on Elite plan)
```

### Test Case 4: Elite Plan Journey

```
✅ Add employees 1-500 → Should succeed
❌ Add 501st employee → Show contact sales modal
❌ Cannot add employee until custom enterprise solution is arranged
```

---

## 🛠️ Backend Response Format

### When `checkUserAdditionRequirements()` is called:

#### Response for OK to Add:
```json
{
  "status": "ok",
  "message": "User can be added within plan limits",
  "data": {
    "current_users": 50,
    "new_user_count": 51,
    "current_plan": "Core Monthly Plan",
    "current_plan_limit": 100,
    "overage_allowed": false
  }
}
```

#### Response for Implementation Fee (Starter Only):
```json
{
  "status": "implementation_fee",
  "message": "Implementation fee required to exceed 10 users",
  "data": {
    "current_users": 10,
    "new_user_count": 11,
    "implementation_fee": 4999,
    "already_paid": 0,
    "amount_due": 4999
  }
}
```

#### Response for Upgrade Required:
```json
{
  "status": "upgrade_required",
  "message": "Plan upgrade required. Your current Core Monthly Plan plan supports up to 100 users. Please upgrade to add more users.",
  "data": {
    "current_users": 100,
    "new_user_count": 101,
    "current_plan": "Core Monthly Plan",
    "current_plan_id": 2,
    "current_plan_limit": 100,
    "recommended_plan": { /* Pro plan details */ },
    "available_plans": [ /* Array of upgrade options */ ],
    "current_implementation_fee_paid": 14999,
    "billing_cycle": "monthly",
    "requires_upgrade": true,
    "overage_allowed": false
  }
}
```

---

## 📞 Support FAQ

### Q: Why can't I add more users to my Core plan with overage fees?

**A:** Unlike the Starter plan which allows a limited overage (11-20 users), the Core, Pro, and Elite plans are designed with fixed capacity tiers. This ensures better resource allocation and service quality. When you reach your plan's limit, upgrading to the next tier gives you significantly more capacity and potentially better value.

### Q: What if I only need a few more licenses beyond my Core plan?

**A:** We recommend upgrading to the Pro plan. While the initial implementation fee difference may seem significant, the Pro plan provides double the capacity (200 vs 100 users), giving you room to grow. Plus, your monthly subscription only increases by ₱4,000.

### Q: Can I temporarily exceed my limit and upgrade later?

**A:** No, you cannot add employees beyond your plan's limit. You must upgrade your plan before adding the additional employee. This is to ensure all users have access to the full features and performance of the system.

### Q: I'm on Elite plan with 500 users. Can I still grow?

**A:** Yes! Elite is our highest standard plan, but we offer custom enterprise solutions for organizations with more than 500 users. Contact our sales team at sales@timora.ph to discuss your specific needs.

---

## ✅ Summary

| Plan | Users | Overage? | Action at Limit |
|------|-------|----------|-----------------|
| **Starter** | 10 base | ✅ Yes (11-20 with fee) | User 21+ → Upgrade required |
| **Core** | 100 | ❌ NO | User 101+ → Upgrade required |
| **Pro** | 200 | ❌ NO | User 201+ → Upgrade required |
| **Elite** | 500 | ❌ NO | User 501+ → Contact sales |

**Key Principle**: Only Starter plan allows overage. All other plans require upgrade when reaching capacity.

---

*Last Updated: November 7, 2025*
*Version: 2.0 - Updated with NO overage policy for Core/Pro/Elite*
