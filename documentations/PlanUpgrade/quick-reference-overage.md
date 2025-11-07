# Quick Reference: Plan Limits & Overage Rules

## 📊 One-Page Overview

### Plan Capacity Matrix

| Plan Type | Base Users | Overage Range | Overage Fee | Max Capacity | Upgrade At |
|-----------|-----------|---------------|-------------|--------------|------------|
| **Starter Monthly** | 1-10 | 11-20 | ₱49/user* | 20 users | 21st user |
| **Core Monthly** | 1-100 | 101-200 | ₱49/user | 200 users | 201st user |
| **Pro Monthly** | 1-200 | 201-500 | ₱49/user | 500 users | 501st user |
| **Elite Monthly** | 1-500 | 501+ | ₱49/user | Unlimited** | Contact Sales at 501+ |

\* Starter plan requires ₱4,999 implementation fee (one-time) before allowing overage  
\*\* Elite plan allows overage beyond 500 but triggers contact sales message at 501+

### Quick Decision Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                     EMPLOYEE ADDITION LOGIC                         │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   STARTER    │────▶│     CORE     │────▶│     PRO      │────▶│    ELITE     │
│   (10 base)  │     │  (100 base)  │     │  (200 base)  │     │  (500 base)  │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘     └──────┬───────┘
       │                    │                    │                    │
       ▼                    ▼                    ▼                    ▼
  Users 1-10           Users 1-100          Users 1-200          Users 1-500
  ✅ FREE              ✅ FREE              ✅ FREE              ✅ FREE
       │                    │                    │                    │
       ▼                    ▼                    ▼                    ▼
  Users 11-20          Users 101-200        Users 201-500        Users 501+
  ⚠️ ₱49/user*         ⚠️ ₱49/user          ⚠️ ₱49/user          ⚠️ ₱49/user
  *Need ₱4,999                                                   📞 +Contact Sales
       │                    │                    │                    
       ▼                    ▼                    ▼                    
  User 21+             User 201+            User 501+            
  🚫 UPGRADE           🚫 UPGRADE           🚫 UPGRADE           
  to Core/Pro/Elite    to Pro/Elite         to Elite             
```

---

## 🎯 Status Responses Quick Guide

| Status | When Triggered | Action Required |
|--------|---------------|-----------------|
| `ok` | Within base limit OR within overage range | Proceed with adding user (may charge ₱49 overage) |
| `implementation_fee` | Starter plan, 11th user, fee not paid | Pay ₱4,999 before proceeding |
| `upgrade_required` | Exceeded max capacity with overage | Select higher plan, generate invoice |
| `contact_sales` | Elite plan, 501+ users | Show contact sales modal/form |

---

## 💡 Common Scenarios

### Scenario 1: New Starter Customer
- **Month 1**: 5 employees → ₱5,000/month
- **Month 3**: Add 6th-10th employees → Still ₱5,000/month
- **Month 4**: Add 11th employee → Pay ₱4,999 implementation fee (one-time) + ₱5,049/month (₱5,000 + ₱49 overage)
- **Month 6**: Add 15th employee → ₱5,245/month (₱5,000 + 5×₱49)
- **Month 8**: Add 21st employee → **MUST UPGRADE** to Core/Pro/Elite

### Scenario 2: Core Plan Customer
- **Month 1**: 80 employees → ₱5,500/month
- **Month 3**: Add 21st-100th employees → Still ₱5,500/month
- **Month 5**: Add 101st employee → ₱5,549/month (₱5,500 + ₱49)
- **Month 8**: Now at 150 employees → ₱7,950/month (₱5,500 + 50×₱49)
- **Month 12**: Add 201st employee → **MUST UPGRADE** to Pro/Elite

### Scenario 3: Pro Plan Customer
- **Month 1**: 180 employees → ₱9,500/month
- **Month 4**: Now at 200 employees → Still ₱9,500/month
- **Month 6**: Add 201st employee → ₱9,549/month (₱9,500 + ₱49)
- **Month 12**: Now at 350 employees → ₱16,850/month (₱9,500 + 150×₱49)
- **Year 2**: Add 501st employee → **MUST UPGRADE** to Elite

### Scenario 4: Elite Plan Customer
- **Month 1**: 450 employees → ₱14,500/month
- **Month 6**: Now at 500 employees → Still ₱14,500/month
- **Month 9**: Add 501st employee → ₱14,549/month + **CONTACT SALES** message shown
- **Note**: Can continue adding with overage, but sales team should reach out for Enterprise plan

---

## 🔢 Pricing Calculator

### Formula
```
Monthly Cost = Base Plan Price + (Overage Users × ₱49)

Where:
- Overage Users = Total Active Users - Base Plan Limit
- If Overage Users < 0, then Overage Users = 0
```

### Examples

| Plan | Base | Active Users | Overage | Calculation | Total |
|------|------|--------------|---------|-------------|-------|
| Starter | ₱5,000 | 8 | 0 | ₱5,000 + (0 × ₱49) | **₱5,000** |
| Starter | ₱5,000 | 15 | 5 | ₱5,000 + (5 × ₱49) | **₱5,245** |
| Core | ₱5,500 | 125 | 25 | ₱5,500 + (25 × ₱49) | **₱6,725** |
| Pro | ₱9,500 | 280 | 80 | ₱9,500 + (80 × ₱49) | **₱13,420** |
| Elite | ₱14,500 | 520 | 20 | ₱14,500 + (20 × ₱49) | **₱15,480** |

---

## 📞 When to Contact Sales

| Situation | Plan | User Count | Action |
|-----------|------|------------|--------|
| Small startup growing | Starter | 21+ | **Upgrade** to Core (auto) |
| Mid-size company growing | Core | 201+ | **Upgrade** to Pro (auto) |
| Large company growing | Pro | 501+ | **Upgrade** to Elite (auto) |
| Enterprise organization | Elite | 501+ | **Contact Sales** for custom Enterprise plan |

---

## ⚡ API Integration Cheat Sheet

### Request
```javascript
POST /employees/check-license-overage
Headers: {
  'X-CSRF-TOKEN': token,
  'Content-Type': 'application/json'
}
```

### Response Handling
```javascript
if (response.status === 'ok') {
  // Proceed with adding employee
  if (response.data.within_overage_range) {
    // Show overage fee notification: ₱49/user
  }
  submitEmployeeForm();
}

else if (response.status === 'implementation_fee') {
  // Starter plan only: Show impl. fee modal
  showImplementationFeeModal(response.data);
}

else if (response.status === 'upgrade_required') {
  // Show upgrade modal with plan options
  showPlanUpgradeModal(response.data);
}

else if (response.status === 'contact_sales') {
  // Elite 501+: Show contact sales modal
  showContactSalesModal(response.data);
}
```

---

## 🎨 Modal Display Logic

```javascript
// Check response and show appropriate modal
function handleLicenseCheck(response) {
  switch(response.status) {
    case 'ok':
      if (response.data.within_overage_range) {
        showOverageConfirmation(response.data); // "Add user for ₱49/month?"
      } else {
        proceedWithAddEmployee(); // Within base limit
      }
      break;
      
    case 'implementation_fee':
      showImplementationFeeModal(response.data); // ₱4,999 one-time
      break;
      
    case 'upgrade_required':
      showUpgradeModal(response.data); // Plan selection
      break;
      
    case 'contact_sales':
      showContactSalesModal(response.data); // Elite 501+
      break;
  }
}
```

---

## 📋 Testing Quick Checklist

### For Each Plan (Starter, Core, Pro, Elite)

**Test Monthly:**
- [ ] Within base limit: No extra charges
- [ ] First overage user: Correct ₱49 fee shown
- [ ] Multiple overage users: Cumulative ₱49 × count
- [ ] At max capacity: Upgrade modal shown
- [ ] After upgrade: New plan limits apply

**Test Yearly:**
- [ ] Same as monthly but with yearly pricing
- [ ] Overage still billed monthly at ₱49/user
- [ ] Upgrade prorates remaining yearly period

**Starter Specific:**
- [ ] 11th user requires ₱4,999 impl. fee
- [ ] Fee only charged once
- [ ] After fee paid, 11-20 works with ₱49/user

**Elite Specific:**
- [ ] 501+ shows contact sales message
- [ ] Overage billing still works
- [ ] No upgrade options shown

---

## 🔗 Related Documentation

- [Complete Decision Tree](./overage-enabled-decision-tree.md) - Full detailed flowcharts
- [Implementation Status](./IMPLEMENTATION-STATUS.md) - Current deployment status
- [Plan Upgrade Flow](./plan-upgrade-flow.md) - Step-by-step upgrade process

---

**Last Updated**: December 2024  
**Quick Ref Version**: 2.0
