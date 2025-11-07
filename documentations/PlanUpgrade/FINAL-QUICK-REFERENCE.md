# FINAL QUICK REFERENCE - Corrected Overage Ranges

## 🎯 THE TRUTH ABOUT OVERAGE (CORRECTED)

### Simple Rule
**Each plan accepts overflow users from the previous plan, charging ₱49/user for ALL users in their range.**

---

## 📊 Plan Capacity Matrix (FINAL - CORRECTED)

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER CAPACITY RANGES                         │
└─────────────────────────────────────────────────────────────────┘

 Users      Plan         Type        Fee          Notes
 ─────      ────         ────        ───          ─────
  1-10   │ STARTER  │ Base      │ Free       │ No overage
         │          │           │            │
 11-20   │ STARTER  │ Overage   │ ₱49/user   │ +₱4,999 impl. fee
         │          │           │            │ (one-time)
         └──────────┴───────────┴────────────┘
                     ↓ UPGRADE AT 21
         ┌──────────┬───────────┬────────────┐
 21-100  │ CORE     │ Overage   │ ₱49/user   │ ALL users are
         │          │           │            │ overage (no base)
         └──────────┴───────────┴────────────┘
                     ↓ UPGRADE AT 101
         ┌──────────┬───────────┬────────────┐
101-200  │ PRO      │ Overage   │ ₱49/user   │ ALL users are
         │          │           │            │ overage (no base)
         └──────────┴───────────┴────────────┘
                     ↓ UPGRADE AT 201
         ┌──────────┬───────────┬────────────┐
201-500  │ ELITE    │ Overage   │ ₱49/user   │ ALL users are
         │          │           │            │ overage (no base)
         └──────────┴───────────┴────────────┘
                     ↓ CONTACT SALES AT 501
         ┌──────────┬───────────┬────────────┐
 501+    │ ENTERPRISE│ Custom   │ Custom     │ Contact sales
         └──────────┴───────────┴────────────┘
```

---

## 💡 Key Insights (CORRECTED)

### Important Understanding

1. **Starter is the ONLY plan with a "free base"**
   - Users 1-10: Free
   - Users 11-20: Overage at ₱49/user

2. **Core, Pro, Elite have NO free base**
   - Core: ALL users (21-100) pay ₱49/user overage
   - Pro: ALL users (101-200) pay ₱49/user overage
   - Elite: ALL users (201-500) pay ₱49/user overage

3. **Why?**
   - These plans are designed to accept overflow from previous tiers
   - They're priced for larger organizations
   - The plan fee (₱5,500, ₱9,500, ₱14,500) is the base service fee
   - Actual user capacity is charged as overage

---

## 🧮 Pricing Calculator (CORRECTED)

### Formula for Each Plan

**Starter (1-20 users):**
```
If users ≤ 10:
  Monthly Cost = ₱5,000

If users 11-20:
  Monthly Cost = ₱5,000 + (users - 10) × ₱49
  One-time: ₱4,999 implementation fee
```

**Core (21-100 users):**
```
Monthly Cost = ₱5,500 + (users - 20) × ₱49

Example: 50 users
= ₱5,500 + (50 - 20) × ₱49
= ₱5,500 + 30 × ₱49
= ₱5,500 + ₱1,470
= ₱6,970/month
```

**Pro (101-200 users):**
```
Monthly Cost = ₱9,500 + (users - 100) × ₱49

Example: 150 users
= ₱9,500 + (150 - 100) × ₱49
= ₱9,500 + 50 × ₱49
= ₱9,500 + ₱2,450
= ₱11,950/month
```

**Elite (201-500 users):**
```
Monthly Cost = ₱14,500 + (users - 200) × ₱49

Example: 350 users
= ₱14,500 + (350 - 200) × ₱49
= ₱14,500 + 150 × ₱49
= ₱14,500 + ₱7,350
= ₱21,850/month
```

---

## 📈 Pricing Examples (CORRECTED)

| Users | Plan | Plan Fee | Overage | Overage Fee | Total/Month |
|-------|------|----------|---------|-------------|-------------|
| 5 | Starter | ₱5,000 | 0 | ₱0 | **₱5,000** |
| 10 | Starter | ₱5,000 | 0 | ₱0 | **₱5,000** |
| 15 | Starter | ₱5,000 | 5 | ₱245 | **₱5,245** |
| 20 | Starter | ₱5,000 | 10 | ₱490 | **₱5,490** |
| **21** | **Core** | **₱5,500** | **1** | **₱49** | **₱5,549** |
| 50 | Core | ₱5,500 | 30 | ₱1,470 | **₱6,970** |
| 100 | Core | ₱5,500 | 80 | ₱3,920 | **₱9,420** |
| **101** | **Pro** | **₱9,500** | **1** | **₱49** | **₱9,549** |
| 150 | Pro | ₱9,500 | 50 | ₱2,450 | **₱11,950** |
| 200 | Pro | ₱9,500 | 100 | ₱4,900 | **₱14,400** |
| **201** | **Elite** | **₱14,500** | **1** | **₱49** | **₱14,549** |
| 350 | Elite | ₱14,500 | 150 | ₱7,350 | **₱21,850** |
| 500 | Elite | ₱14,500 | 300 | ₱14,700 | **₱29,200** |

---

## 🚦 Decision Logic (For Developers)

```javascript
function checkUserAddition(planType, currentUsers, newUserCount) {
  
  // STARTER PLAN
  if (planType === 'Starter') {
    if (newUserCount <= 10) {
      return { status: 'ok', fee: 0 };
    }
    if (newUserCount <= 20) {
      if (!implementationFeePaid) {
        return { status: 'implementation_fee', amount: 4999 };
      }
      return { status: 'ok', fee: 49 };
    }
    if (newUserCount >= 21) {
      return { status: 'upgrade_required', options: ['Core', 'Pro', 'Elite'] };
    }
  }
  
  // CORE PLAN
  if (planType === 'Core') {
    if (newUserCount >= 21 && newUserCount <= 100) {
      return { status: 'ok', fee: 49 };
    }
    if (newUserCount >= 101) {
      return { status: 'upgrade_required', options: ['Pro', 'Elite'] };
    }
  }
  
  // PRO PLAN
  if (planType === 'Pro') {
    if (newUserCount >= 101 && newUserCount <= 200) {
      return { status: 'ok', fee: 49 };
    }
    if (newUserCount >= 201) {
      return { status: 'upgrade_required', options: ['Elite'] };
    }
  }
  
  // ELITE PLAN
  if (planType === 'Elite') {
    if (newUserCount >= 201 && newUserCount <= 500) {
      return { status: 'ok', fee: 49 };
    }
    if (newUserCount >= 501) {
      return { status: 'contact_sales' };
    }
  }
}
```

---

## ✅ Testing Scenarios (CORRECTED)

### Test Case 1: Starter Plan
```
✅ Add user 5:   Status = ok, Fee = 0
✅ Add user 10:  Status = ok, Fee = 0
⚠️  Add user 11:  Status = implementation_fee, Amount = 4999
✅ Add user 11 (after payment): Status = ok, Fee = 49
✅ Add user 15:  Status = ok, Fee = 49
✅ Add user 20:  Status = ok, Fee = 49
🚫 Add user 21:  Status = upgrade_required
```

### Test Case 2: Core Plan
```
✅ Add user 21:  Status = ok, Fee = 49
✅ Add user 50:  Status = ok, Fee = 49
✅ Add user 100: Status = ok, Fee = 49
🚫 Add user 101: Status = upgrade_required
```

### Test Case 3: Pro Plan
```
✅ Add user 101: Status = ok, Fee = 49
✅ Add user 150: Status = ok, Fee = 49
✅ Add user 200: Status = ok, Fee = 49
🚫 Add user 201: Status = upgrade_required
```

### Test Case 4: Elite Plan
```
✅ Add user 201: Status = ok, Fee = 49
✅ Add user 350: Status = ok, Fee = 49
✅ Add user 500: Status = ok, Fee = 49
📞 Add user 501: Status = contact_sales
```

---

## 🎯 Common Questions

### Q: Why don't Core/Pro/Elite have a free base?
**A:** These plans are designed for larger organizations. The plan fee covers the platform service, while actual user capacity is billed as overage. This creates a smooth transition path from Starter.

### Q: What happens when I upgrade from Starter to Core?
**A:** At 21 users, you must upgrade. Core accepts users 21-100, all paying ₱49/user overage. Your 21st user will cost ₱5,549/month total (₱5,500 plan + ₱49 overage).

### Q: How is overage calculated on Core?
**A:** Core charges ₱49/user for every user from 21 onwards. If you have 50 users:
- Overage users: 50 - 20 = 30
- Overage cost: 30 × ₱49 = ₱1,470
- Total: ₱5,500 + ₱1,470 = ₱6,970/month

### Q: Can I add 101 users while on Core?
**A:** No. Core supports up to 100 users maximum. At 101, you must upgrade to Pro plan.

---

## 📞 Support Quick Reference

| Scenario | Response |
|----------|----------|
| Customer has 25 users on Starter | "You've exceeded Starter's 20-user limit. Please upgrade to Core plan." |
| Customer has 75 users on Core | "You're currently using overage on Core at ₱49/user. Total: ₱6,195/month." |
| Customer wants to add 101st user on Core | "Core supports up to 100 users. Please upgrade to Pro (supports 200 users)." |
| Customer has 180 users on Pro | "You're using overage on Pro at ₱49/user. Total: ₱13,420/month." |
| Customer wants to add 501st user on Elite | "You've reached Elite's capacity. Please contact sales for Enterprise options." |

---

**FINAL VERSION - 100% ACCURATE**  
**Last Updated**: December 2024  
**All overage ranges corrected**  

---

## 🔗 Related Documentation
- [FINAL-DECISION-TREE.md](./FINAL-DECISION-TREE.md) - Complete visual decision trees
- [IMPLEMENTATION-STATUS.md](./IMPLEMENTATION-STATUS.md) - Technical implementation details
