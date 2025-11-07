# FINAL Decision Tree - Complete Plan Structure with Correct Overage Ranges

## 🎯 Corrected Plan Structure

### Complete Overage Matrix (FINAL VERSION)

| Plan | User Range | Overage? | Fee | Action Required |
|------|-----------|----------|-----|-----------------|
| **Starter** | 1-10 | ❌ No | Free | None |
| **Starter** | 11-20 | ✅ Yes | ₱49/user | Pay ₱4,999 impl. fee first |
| **Starter** | 21+ | 🚫 N/A | N/A | **UPGRADE REQUIRED** |
| **Core** | 21-100 | ✅ Yes | ₱49/user | Automatic overage |
| **Core** | 101+ | 🚫 N/A | N/A | **UPGRADE REQUIRED** |
| **Pro** | 101-200 | ✅ Yes | ₱49/user | Automatic overage |
| **Pro** | 201+ | 🚫 N/A | N/A | **UPGRADE REQUIRED** |
| **Elite** | 201-500 | ✅ Yes | ₱49/user | Automatic overage |
| **Elite** | 501+ | 📞 N/A | N/A | **CONTACT SALES** |

---

## 📊 Visual Plan Progression

```
┌──────────────────────────────────────────────────────────────────────┐
│                    COMPLETE USER CAPACITY FLOW                       │
└──────────────────────────────────────────────────────────────────────┘

Users 1-10:    STARTER (Base) - Free
               ↓
Users 11-20:   STARTER (Overage) - ₱49/user + ₱4,999 impl. fee
               ↓
Users 21-100:  CORE (Overage from Starter) - ₱49/user
               ↓
Users 101-200: PRO (Overage from Core) - ₱49/user
               ↓
Users 201-500: ELITE (Overage from Pro) - ₱49/user
               ↓
Users 501+:    CONTACT SALES (Enterprise)
```

---

## 🌳 FINAL Complete Decision Tree

### 1️⃣ STARTER PLAN (1-20 users)

```
                        ADD EMPLOYEE ON STARTER
                                  │
                                  ▼
                      Check Current User Count
                                  │
        ┌─────────────────────────┼─────────────────────────┐
        │                         │                         │
        ▼                         ▼                         ▼
    1-10 users              11-20 users                21+ users
    (Base Plan)             (Overage)                 (Exceeds Max)
        │                         │                         │
        ▼                         ▼                         ▼
┌───────────────┐     ┌──────────────────────┐    ┌──────────────────┐
│ ✅ ADD OK     │     │ Check Impl. Fee      │    │ 🚫 MUST UPGRADE  │
│ Free          │     └──────────┬───────────┘    └────────┬─────────┘
└───────────────┘                │                         │
                      ┌──────────┴──────────┐              │
                      ▼                     ▼              │
            ┌─────────────────┐   ┌─────────────────┐     │
            │ Fee NOT Paid    │   │ Fee PAID        │     │
            │ (₱0)            │   │ (₱4,999)        │     │
            └────────┬────────┘   └────────┬────────┘     │
                     │                     │              │
                     ▼                     ▼              │
          ┌──────────────────┐  ┌──────────────────┐     │
          │ SHOW MODAL:      │  │ ✅ ADD OK        │     │
          │ Pay ₱4,999       │  │ + ₱49/user       │     │
          │ Implementation   │  │ overage          │     │
          │ Fee              │  └──────────────────┘     │
          └──────────────────┘                           │
                                                         │
                                                         ▼
                                              ┌──────────────────────┐
                                              │ SHOW UPGRADE MODAL   │
                                              │                      │
                                              │ Must upgrade to:     │
                                              │ • Core (21-100)      │
                                              │ • Pro (101-200)      │
                                              │ • Elite (201-500)    │
                                              └──────────────────────┘
```

---

### 2️⃣ CORE PLAN (21-100 users)

```
                        ADD EMPLOYEE ON CORE
                                  │
                                  ▼
                      Check Current User Count
                                  │
                  ┌───────────────┴───────────────┐
                  │                               │
                  ▼                               ▼
             21-100 users                    101+ users
         (Overage from Starter)            (Exceeds Core)
                  │                               │
                  ▼                               ▼
      ┌─────────────────────┐         ┌──────────────────────┐
      │ ✅ ADD OK           │         │ 🚫 MUST UPGRADE      │
      │ + ₱49/user overage  │         └──────────┬───────────┘
      │                     │                    │
      │ Automatic billing   │                    │
      │ No impl. fee needed │                    │
      └─────────────────────┘                    │
                                                 ▼
                                      ┌──────────────────────┐
                                      │ SHOW UPGRADE MODAL   │
                                      │                      │
                                      │ Must upgrade to:     │
                                      │ • Pro (101-200)      │
                                      │ • Elite (201-500)    │
                                      │                      │
                                      │ Core max: 100 users  │
                                      └──────────────────────┘
```

**Key Points for Core:**
- Accepts users from 21 onwards (Starter overflow)
- All users 21-100 pay ₱49/user overage
- NO base "free" tier for Core (all users are overage)
- At 101st user → Must upgrade to Pro or Elite

---

### 3️⃣ PRO PLAN (101-200 users)

```
                        ADD EMPLOYEE ON PRO
                                  │
                                  ▼
                      Check Current User Count
                                  │
                  ┌───────────────┴───────────────┐
                  │                               │
                  ▼                               ▼
            101-200 users                    201+ users
         (Overage from Core)              (Exceeds Pro)
                  │                               │
                  ▼                               ▼
      ┌─────────────────────┐         ┌──────────────────────┐
      │ ✅ ADD OK           │         │ 🚫 MUST UPGRADE      │
      │ + ₱49/user overage  │         └──────────┬───────────┘
      │                     │                    │
      │ Automatic billing   │                    │
      │ No impl. fee needed │                    │
      └─────────────────────┘                    │
                                                 ▼
                                      ┌──────────────────────┐
                                      │ SHOW UPGRADE MODAL   │
                                      │                      │
                                      │ Must upgrade to:     │
                                      │ • Elite (201-500)    │
                                      │                      │
                                      │ Pro max: 200 users   │
                                      └──────────────────────┘
```

**Key Points for Pro:**
- Accepts users from 101 onwards (Core overflow)
- All users 101-200 pay ₱49/user overage
- NO base "free" tier for Pro (all users are overage)
- At 201st user → Must upgrade to Elite

---

### 4️⃣ ELITE PLAN (201-500 users)

```
                        ADD EMPLOYEE ON ELITE
                                  │
                                  ▼
                      Check Current User Count
                                  │
                  ┌───────────────┴───────────────┐
                  │                               │
                  ▼                               ▼
            201-500 users                    501+ users
         (Overage from Pro)             (Exceeds Elite)
                  │                               │
                  ▼                               ▼
      ┌─────────────────────┐         ┌──────────────────────┐
      │ ✅ ADD OK           │         │ 📞 CONTACT SALES     │
      │ + ₱49/user overage  │         └──────────┬───────────┘
      │                     │                    │
      │ Automatic billing   │                    │
      │ No impl. fee needed │                    │
      └─────────────────────┘                    │
                                                 ▼
                                      ┌──────────────────────┐
                                      │ SHOW CONTACT SALES   │
                                      │ MODAL                │
                                      │                      │
                                      │ "Maximum capacity    │
                                      │  reached. Contact    │
                                      │  sales for custom    │
                                      │  Enterprise plan."   │
                                      │                      │
                                      │ Elite max: 500 users │
                                      └──────────────────────┘
```

**Key Points for Elite:**
- Accepts users from 201 onwards (Pro overflow)
- All users 201-500 pay ₱49/user overage
- NO base "free" tier for Elite (all users are overage)
- At 501st user → Contact sales for Enterprise plan

---

## 💰 Pricing Structure (CORRECTED)

### Starter Plan Pricing

| Users | Plan Fee | Impl. Fee | Overage Fee | Total Monthly |
|-------|----------|-----------|-------------|---------------|
| 5 | ₱5,000 | ₱0 | ₱0 | **₱5,000** |
| 10 | ₱5,000 | ₱0 | ₱0 | **₱5,000** |
| 11 | ₱5,000 | ₱4,999* | ₱49 | **₱5,049** |
| 15 | ₱5,000 | ₱0† | ₱245 (5×₱49) | **₱5,245** |
| 20 | ₱5,000 | ₱0† | ₱490 (10×₱49) | **₱5,490** |

\* Implementation fee is one-time payment  
† Already paid

### Core Plan Pricing

| Users | Plan Fee | Overage Users | Overage Fee | Total Monthly |
|-------|----------|---------------|-------------|---------------|
| 21 | ₱5,500 | 21 | ₱1,029 (21×₱49) | **₱6,529** |
| 50 | ₱5,500 | 50 | ₱2,450 (50×₱49) | **₱7,950** |
| 100 | ₱5,500 | 100 | ₱4,900 (100×₱49) | **₱10,400** |

**Note:** ALL Core users are overage users (no base free tier)

### Pro Plan Pricing

| Users | Plan Fee | Overage Users | Overage Fee | Total Monthly |
|-------|----------|---------------|-------------|---------------|
| 101 | ₱9,500 | 101 | ₱4,949 (101×₱49) | **₱14,449** |
| 150 | ₱9,500 | 150 | ₱7,350 (150×₱49) | **₱16,850** |
| 200 | ₱9,500 | 200 | ₱9,800 (200×₱49) | **₱19,300** |

**Note:** ALL Pro users are overage users (no base free tier)

### Elite Plan Pricing

| Users | Plan Fee | Overage Users | Overage Fee | Total Monthly |
|-------|----------|---------------|-------------|---------------|
| 201 | ₱14,500 | 201 | ₱9,849 (201×₱49) | **₱24,349** |
| 300 | ₱14,500 | 300 | ₱14,700 (300×₱49) | **₱29,200** |
| 500 | ₱14,500 | 500 | ₱24,500 (500×₱49) | **₱39,000** |

**Note:** ALL Elite users are overage users (no base free tier)

---

## 🔄 Complete Customer Journey

### Scenario: Growing from Startup to Enterprise

```
MONTH 1: Start with Starter Plan
└─ 5 employees → ₱5,000/month

MONTH 3: Growing team
└─ 10 employees → ₱5,000/month (still within base)

MONTH 5: First overage
└─ 12 employees → ₱5,098/month (₱5,000 + 2×₱49)
   💡 Paid ₱4,999 implementation fee (one-time)

MONTH 8: Approaching Starter limit
└─ 20 employees → ₱5,490/month (₱5,000 + 10×₱49)

MONTH 10: Must upgrade to Core
└─ 25 employees → Upgrade to Core
   → ₱6,725/month (₱5,500 + 25×₱49)
   💡 Pay implementation fee difference

MONTH 18: Growing on Core
└─ 75 employees → ₱9,175/month (₱5,500 + 75×₱49)

MONTH 24: Approaching Core limit
└─ 100 employees → ₱10,400/month (₱5,500 + 100×₱49)

MONTH 26: Must upgrade to Pro
└─ 125 employees → Upgrade to Pro
   → ₱15,625/month (₱9,500 + 125×₱49)

YEAR 3: Growing on Pro
└─ 180 employees → ₱18,320/month (₱9,500 + 180×₱49)

YEAR 4: Approaching Pro limit
└─ 200 employees → ₱19,300/month (₱9,500 + 200×₱49)

YEAR 5: Must upgrade to Elite
└─ 250 employees → Upgrade to Elite
   → ₱26,750/month (₱14,500 + 250×₱49)

YEAR 7: Large organization
└─ 450 employees → ₱36,550/month (₱14,500 + 450×₱49)

YEAR 8: Approaching Elite limit
└─ 500 employees → ₱39,000/month (₱14,500 + 500×₱49)

YEAR 9: Enterprise tier
└─ 501+ employees → Contact sales for custom Enterprise plan
```

---

## 📋 API Response Examples (CORRECTED)

### Core Plan - Within Range (21-100)
```json
{
  "status": "ok",
  "message": "User can be added with overage fee",
  "data": {
    "current_users": 75,
    "new_user_count": 76,
    "current_plan": "Core Monthly Plan",
    "current_plan_limit": 100,
    "overage_fee": 49.00,
    "overage_allowed": true,
    "within_overage_range": true,
    "max_with_overage": 100
  }
}
```

### Core Plan - Exceeds Limit (101+)
```json
{
  "status": "upgrade_required",
  "message": "Plan upgrade required. Your Core Monthly Plan supports up to 100 users (including overage). Please upgrade to add more users.",
  "data": {
    "current_users": 100,
    "new_user_count": 101,
    "current_plan": "Core Monthly Plan",
    "current_plan_id": 2,
    "current_plan_limit": 100,
    "max_with_overage": 100,
    "recommended_plan": {
      "id": 3,
      "name": "Pro Monthly Plan",
      "employee_limit": 200
    },
    "available_plans": [...],
    "requires_upgrade": true,
    "overage_allowed": false
  }
}
```

### Pro Plan - Within Range (101-200)
```json
{
  "status": "ok",
  "message": "User can be added with overage fee",
  "data": {
    "current_users": 150,
    "new_user_count": 151,
    "current_plan": "Pro Monthly Plan",
    "current_plan_limit": 200,
    "overage_fee": 49.00,
    "overage_allowed": true,
    "within_overage_range": true,
    "max_with_overage": 200
  }
}
```

### Elite Plan - Contact Sales (501+)
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
    "max_with_overage": 500,
    "requires_contact_sales": true
  }
}
```

---

## ✅ Validation Rules

### For Each Plan:

1. **Starter (1-20)**
   - ✅ Users 1-10: Free, no checks
   - ✅ User 11: Require ₱4,999 impl. fee if not paid
   - ✅ Users 11-20: Allow with ₱49/user if impl. fee paid
   - ✅ User 21: Block, require upgrade

2. **Core (21-100)**
   - ✅ Users 21-100: Allow with ₱49/user (all are overage)
   - ✅ User 101: Block, require upgrade to Pro/Elite

3. **Pro (101-200)**
   - ✅ Users 101-200: Allow with ₱49/user (all are overage)
   - ✅ User 201: Block, require upgrade to Elite

4. **Elite (201-500)**
   - ✅ Users 201-500: Allow with ₱49/user (all are overage)
   - ✅ User 501: Show contact sales message

---

## 🎨 Quick Reference Table

| User Count | Plan | Status | Action |
|-----------|------|--------|--------|
| 1-10 | Starter | ✅ Free | Add directly |
| 11-20 | Starter | ⚠️ Overage | Pay impl. fee + ₱49/user |
| 21-100 | Core | ⚠️ Overage | ₱49/user (auto) |
| 101-200 | Pro | ⚠️ Overage | ₱49/user (auto) |
| 201-500 | Elite | ⚠️ Overage | ₱49/user (auto) |
| 501+ | N/A | 📞 Sales | Contact for Enterprise |

---

**FINAL VERSION - CORRECTED OVERAGE RANGES**  
**Last Updated**: December 2024  
**Status**: ✅ Complete and Accurate
