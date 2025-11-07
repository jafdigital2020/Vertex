# Complete Plan Upgrade Decision Tree - Starter to Elite
## All Plans with Overage Support (Updated)

---

## 📋 Overview

This document provides the **complete decision tree** for all plan types where:
- **ALL PLANS** allow overage with ₱49/user/month fee
- **Starter Plan**: 10 base → 11-20 overage (requires ₱4,999 implementation fee) → 21+ upgrade required
- **Core Plan**: 100 base → 101-200 overage → 201+ upgrade required
- **Pro Plan**: 200 base → 201-500 overage → 501+ upgrade required
- **Elite Plan**: 500 base → 501+ contact sales for Enterprise

---

## 🎯 Key Business Rules

| Plan | Base Limit | Overage Range | Overage Fee | Action at Limit |
|------|-----------|---------------|-------------|-----------------|
| **Starter** | 10 users | 11-20 users | ₱49/user/month | Pay ₱4,999 impl. fee first |
| **Core** | 100 users | 101-200 users | ₱49/user/month | Automatic overage billing |
| **Pro** | 200 users | 201-500 users | ₱49/user/month | Automatic overage billing |
| **Elite** | 500 users | 501+ users | ₱49/user/month | Contact sales at 501+ |

### Upgrade Requirements

| Current Plan | Upgrade Trigger | Available Upgrade Options |
|--------------|----------------|---------------------------|
| **Starter** | 21st user | Core, Pro, Elite |
| **Core** | 201st user | Pro, Elite |
| **Pro** | 501st user | Elite |
| **Elite** | 501st user | Contact Sales (Enterprise) |

---

## 🌳 Complete Decision Tree

### 1️⃣ STARTER PLAN (10 base, 20 max with overage)

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
    (Within Base)            (Overage Range)              (Exceeds Max)
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────┐     ┌─────────────────────────┐    ┌──────────────────────┐
│ ✅ ADD OK     │     │ Check Implementation    │    │ 🚫 UPGRADE REQUIRED  │
│ No Extra Fee  │     │ Fee Status              │    │ (FORCED)             │
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
        │ Pay ₱4,999 Impl. │    │ ✅ ADD OK    │             │
        │ Fee First        │    │ + ₱49/user   │             │
        └────────┬─────────┘    └──────────────┘             │
                 │                                             │
                 ▼                                             │
        ┌──────────────────┐                                  │
        │ Create Invoice   │                                  │
        │ Redirect Billing │                                  │
        └────────┬─────────┘                                  │
                 │                                             │
                 ▼                                             │
        ┌──────────────────┐                                  │
        │ After Payment    │                                  │
        │ Can Add 11-20    │                                  │
        └──────────────────┘                                  │
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ Show Upgrade Modal   │
                                                    │                      │
                                                    │ Options:             │
                                                    │ • Core (100 users)   │
                                                    │ • Pro (200 users)    │
                                                    │ • Elite (500 users)  │
                                                    └──────────┬───────────┘
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ Create 2 Invoices:   │
                                                    │ 1. Plan Upgrade      │
                                                    │ 2. Impl. Fee Diff    │
                                                    └──────────────────────┘
```

### 2️⃣ CORE PLAN (100 base, 200 max with overage)

```
                        ADD EMPLOYEE ON CORE PLAN
                                    │
                                    ▼
                        Check Current User Count
                                    │
        ┌───────────────────────────┼───────────────────────────┐
        │                           │                           │
        ▼                           ▼                           ▼
    Count: 1-100             Count: 101-200                Count: 201+
    (Within Base)            (Overage Range)              (Exceeds Max)
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────┐     ┌─────────────────────────┐    ┌──────────────────────┐
│ ✅ ADD OK     │     │ ✅ ADD OK               │    │ 🚫 UPGRADE REQUIRED  │
│ No Extra Fee  │     │ + ₱49/user/month        │    │ (FORCED)             │
└───────────────┘     │ (Automatic Overage)     │    └──────────┬───────────┘
                      └─────────────────────────┘               │
                                                                 │
                                                                 ▼
                                                    ┌──────────────────────┐
                                                    │ Show Upgrade Modal   │
                                                    │                      │
                                                    │ Options:             │
                                                    │ • Pro (200 users)    │
                                                    │ • Elite (500 users)  │
                                                    └──────────┬───────────┘
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ Select Plan          │
                                                    │ Generate Invoice     │
                                                    │ Redirect to Billing  │
                                                    └──────────────────────┘
```

### 3️⃣ PRO PLAN (200 base, 500 max with overage)

```
                        ADD EMPLOYEE ON PRO PLAN
                                    │
                                    ▼
                        Check Current User Count
                                    │
        ┌───────────────────────────┼───────────────────────────┐
        │                           │                           │
        ▼                           ▼                           ▼
    Count: 1-200             Count: 201-500                Count: 501+
    (Within Base)            (Overage Range)              (Exceeds Max)
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────┐     ┌─────────────────────────┐    ┌──────────────────────┐
│ ✅ ADD OK     │     │ ✅ ADD OK               │    │ 🚫 UPGRADE REQUIRED  │
│ No Extra Fee  │     │ + ₱49/user/month        │    │ (FORCED)             │
└───────────────┘     │ (Automatic Overage)     │    └──────────┬───────────┘
                      └─────────────────────────┘               │
                                                                 │
                                                                 ▼
                                                    ┌──────────────────────┐
                                                    │ Show Upgrade Modal   │
                                                    │                      │
                                                    │ Option:              │
                                                    │ • Elite (500 users)  │
                                                    └──────────┬───────────┘
                                                               │
                                                               ▼
                                                    ┌──────────────────────┐
                                                    │ Generate Invoice     │
                                                    │ Redirect to Billing  │
                                                    └──────────────────────┘
```

### 4️⃣ ELITE PLAN (500 base, 501+ contact sales)

```
                        ADD EMPLOYEE ON ELITE PLAN
                                    │
                                    ▼
                        Check Current User Count
                                    │
        ┌───────────────────────────┼────────────────────────┐
        │                           │                        │
        ▼                           ▼                        ▼
    Count: 1-500             Count: 501-999+           Count: 501+
    (Within Base)            (Overage Range)           (Contact Sales)
        │                           │                        │
        ▼                           ▼                        ▼
┌───────────────┐     ┌─────────────────────────┐   ┌─────────────────────┐
│ ✅ ADD OK     │     │ ✅ ADD OK               │   │ 📞 CONTACT SALES    │
│ No Extra Fee  │     │ + ₱49/user/month        │   │ Enterprise Solution │
└───────────────┘     │ (Automatic Overage)     │   │                     │
                      │                         │   │ Show Message:       │
                      │ Note: At 501+ users,    │   │ "Maximum capacity   │
                      │ contact sales message   │   │  reached. Contact   │
                      │ will be triggered       │   │  sales for custom   │
                      └─────────────────────────┘   │  Enterprise plan."  │
                                                    └─────────────────────┘
```

---

## 📊 Summary Table: All Plans at a Glance

| User Count Range | Starter | Core | Pro | Elite |
|------------------|---------|------|-----|-------|
| **1-10** | ✅ Free | ❌ N/A | ❌ N/A | ❌ N/A |
| **11-20** | ⚠️ ₱49/user (req. impl. fee) | ❌ N/A | ❌ N/A | ❌ N/A |
| **21-100** | 🚫 Upgrade | ✅ Free | ❌ N/A | ❌ N/A |
| **101-200** | 🚫 Upgrade | ⚠️ ₱49/user | ✅ Free | ❌ N/A |
| **201-500** | 🚫 Upgrade | 🚫 Upgrade | ⚠️ ₱49/user | ✅ Free |
| **501+** | 🚫 Upgrade | 🚫 Upgrade | 🚫 Upgrade | 📞 Contact Sales |

**Legend:**
- ✅ Free = Within base plan limit, no extra fees
- ⚠️ ₱49/user = Overage fee applies
- 🚫 Upgrade = Must upgrade to higher plan
- 📞 Contact Sales = Reached maximum capacity, custom solution needed

---

## 🔄 Complete User Journey Flow

### Journey 1: Starter → Core → Pro → Elite

```
Start: Starter Plan (10 users)
    ↓
Add Users 11-20: Pay ₱4,999 impl. fee once → ₱49/user overage
    ↓
Add User 21: Forced upgrade
    ↓ (Choose Core)
Now: Core Plan (100 users base)
    ↓
Add Users 101-200: ₱49/user overage (no impl. fee needed)
    ↓
Add User 201: Forced upgrade
    ↓ (Choose Pro)
Now: Pro Plan (200 users base)
    ↓
Add Users 201-500: ₱49/user overage
    ↓
Add User 501: Forced upgrade
    ↓ (Choose Elite)
Now: Elite Plan (500 users base)
    ↓
Add User 501+: Contact sales for Enterprise
```

---

## 💰 Pricing Examples

### Example 1: Starter Plan with Overage
- **Base Plan**: 10 users @ ₱5,000/month
- **Implementation Fee**: ₱4,999 (one-time, required at 11th user)
- **Overage**: 5 users (users 11-15) @ ₱49/user = ₱245/month
- **Total Monthly**: ₱5,245/month (after impl. fee paid)

### Example 2: Core Plan with Overage
- **Base Plan**: 100 users @ ₱5,500/month
- **Overage**: 50 users (users 101-150) @ ₱49/user = ₱2,450/month
- **Total Monthly**: ₱7,950/month
- **Note**: No implementation fee required

### Example 3: Pro Plan with Overage
- **Base Plan**: 200 users @ ₱9,500/month
- **Overage**: 100 users (users 201-300) @ ₱49/user = ₱4,900/month
- **Total Monthly**: ₱14,400/month

### Example 4: Elite Plan with Overage
- **Base Plan**: 500 users @ ₱14,500/month
- **Overage**: 50 users (users 501-550) @ ₱49/user = ₱2,450/month
- **Total Monthly**: ₱16,950/month
- **Note**: At 501+, contact sales message appears

---

## 🔧 API Response Formats

### Within Base Limit (All Plans)
```json
{
  "status": "ok",
  "message": "User can be added within plan limits",
  "data": {
    "current_users": 50,
    "new_user_count": 51,
    "current_plan": "Core Monthly Plan",
    "current_plan_limit": 100,
    "overage_allowed": true,
    "within_base_limit": true
  }
}
```

### Within Overage Range (All Plans)
```json
{
  "status": "ok",
  "message": "User can be added with overage fee",
  "data": {
    "current_users": 150,
    "new_user_count": 151,
    "current_plan": "Core Monthly Plan",
    "current_plan_limit": 100,
    "overage_fee": 49.00,
    "overage_allowed": true,
    "within_overage_range": true,
    "max_with_overage": 200
  }
}
```

### Starter Plan - Implementation Fee Required
```json
{
  "status": "implementation_fee",
  "message": "Implementation fee required to exceed 10 users",
  "data": {
    "current_users": 10,
    "new_user_count": 11,
    "implementation_fee": 4999.00,
    "already_paid": 0,
    "amount_due": 4999.00
  }
}
```

### Upgrade Required (Starter, Core, Pro)
```json
{
  "status": "upgrade_required",
  "message": "Plan upgrade required. Your Core Monthly Plan supports up to 200 users (including overage). Please upgrade to add more users.",
  "data": {
    "current_users": 200,
    "new_user_count": 201,
    "current_plan": "Core Monthly Plan",
    "current_plan_id": 2,
    "current_plan_limit": 100,
    "max_with_overage": 200,
    "recommended_plan": {
      "id": 3,
      "name": "Pro Monthly Plan",
      "employee_limit": 200,
      "price": 9500,
      "implementation_fee": 39999,
      "is_recommended": true
    },
    "available_plans": [...],
    "billing_cycle": "monthly",
    "requires_upgrade": true,
    "overage_allowed": false
  }
}
```

### Contact Sales Required (Elite 501+)
```json
{
  "status": "contact_sales",
  "message": "You have reached the maximum capacity for Elite plan. Please contact sales for Enterprise solutions.",
  "data": {
    "current_users": 500,
    "new_user_count": 501,
    "current_plan": "Elite Monthly Plan",
    "current_plan_id": 4,
    "current_plan_limit": 500,
    "max_with_overage": 999,
    "requires_contact_sales": true
  }
}
```

---

## 🎨 Frontend Modal Behavior Guide

### 1. Implementation Fee Modal (Starter Only)
**Trigger**: `status === 'implementation_fee'`

**Display**:
- Current users: 10
- New user will be: 11
- Implementation fee: ₱4,999
- After payment: Can add users 11-20 with ₱49/user overage

**Actions**:
- Button: "Pay Implementation Fee" → Create invoice, redirect to billing
- Button: "Cancel"

### 2. Overage Confirmation Modal (All Plans)
**Trigger**: `status === 'ok' && data.within_overage_range === true`

**Display**:
- Current plan and limit
- New user count
- Overage fee: ₱49/user/month
- Total additional monthly cost

**Actions**:
- Button: "Add User" → Proceed with activation
- Button: "Cancel"

### 3. Upgrade Required Modal (Starter, Core, Pro)
**Trigger**: `status === 'upgrade_required'`

**Display**:
- Current plan capacity exceeded
- Available upgrade options with pricing
- Recommended plan highlighted
- Implementation fee difference (if applicable)

**Actions**:
- Button: "Select Plan" → Create upgrade invoice, redirect to billing
- Button: "Cancel"

### 4. Contact Sales Modal (Elite 501+)
**Trigger**: `status === 'contact_sales'`

**Display**:
- "Maximum Capacity Reached"
- Current plan: Elite (500 users)
- Message: "You've reached the maximum capacity. Our sales team can help you with custom Enterprise solutions."
- Contact information or form

**Actions**:
- Button: "Contact Sales" → Open contact form or email
- Button: "Cancel"

---

## ✅ Testing Checklist

### Starter Plan (Monthly/Yearly)
- [ ] Users 1-10: Add without fees
- [ ] User 11 (first time): Require ₱4,999 implementation fee
- [ ] User 11 (fee unpaid): Block and show payment modal
- [ ] Users 11-20 (fee paid): Add with ₱49/user overage
- [ ] User 21: Force upgrade, show Core/Pro/Elite options
- [ ] Verify implementation fee charged only once

### Core Plan (Monthly/Yearly)
- [ ] Users 1-100: Add without fees
- [ ] Users 101-200: Add with ₱49/user overage (no impl. fee)
- [ ] User 201: Force upgrade, show Pro/Elite options
- [ ] Verify overage billing appears in invoices
- [ ] Verify no implementation fee required for overage

### Pro Plan (Monthly/Yearly)
- [ ] Users 1-200: Add without fees
- [ ] Users 201-500: Add with ₱49/user overage
- [ ] User 501: Force upgrade, show Elite option
- [ ] Verify overage billing appears in invoices

### Elite Plan (Monthly/Yearly)
- [ ] Users 1-500: Add without fees
- [ ] Users 501+: Show contact sales message
- [ ] Verify overage can be added but triggers sales contact
- [ ] Verify no upgrade options shown (highest plan)

---

## 📝 Implementation Notes

### Backend Changes Required
1. ✅ Update `LicenseOverageService::checkUserAdditionRequirements()`
2. ✅ Add constants for all plan limits
3. ✅ Add `contact_sales` status to response types
4. ⏳ Update billing logic to handle overage for all plans
5. ⏳ Ensure implementation fee only applies to Starter plan

### Frontend Changes Required
1. ⏳ Update modal logic to handle `overage_allowed: true` for all plans
2. ⏳ Add new "Contact Sales" modal for Elite 501+
3. ⏳ Update upgrade modals to show correct plan options
4. ⏳ Remove "no overage" messaging for Core/Pro/Elite
5. ⏳ Add overage confirmation for users 101+ (Core), 201+ (Pro), 501+ (Elite)

### Database/Migration Notes
- No schema changes required
- All plans already support overage tracking in invoices
- Implementation fee field only used for Starter plan

---

## 🚀 Deployment Checklist

- [ ] Update `LicenseOverageService.php` with new logic
- [ ] Update all plan limits constants
- [ ] Test all plan scenarios (Starter, Core, Pro, Elite)
- [ ] Update frontend modals for overage support
- [ ] Add "Contact Sales" functionality for Elite 501+
- [ ] Update documentation and user guides
- [ ] Train support team on new overage rules
- [ ] Monitor billing invoices post-deployment

---

**Last Updated**: December 2024  
**Status**: ✅ Backend Updated, Frontend Updates Pending  
**Version**: 2.0 - Universal Overage Support
