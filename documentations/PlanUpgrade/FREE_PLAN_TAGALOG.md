# Free Plan - Tagalog Summary

## Ano ang Free Plan?

Ang **Free Plan** ay isang libreng subscription plan na nagbibigay ng access sa sistema para sa hanggang **2 employees lang**.

## Detalye ng Plan

- **Pangalan:** Free Plan
- **Minimum Employees:** 1
- **Maximum Employees:** 2
- **Presyo:** ₱0.00 (LIBRE)
- **Implementation Fee:** ₱0.00 (WALANG BAYAD)
- **Overage Allowed:** WALA (Hindi pwede lumagpas sa 2)

## Paano Gumagana?

### Pag-add ng 1st at 2nd Employee
✅ **PWEDE** - Walang babayaran
- Pwede kang magdagdag ng hanggang 2 employees
- Walang kailangang bayaran
- Walang implementation fee

### Pag-add ng 3rd Employee (PANGATLONG EMPLOYEE)
🚫 **HINDI PWEDE** - Kailangan mag-upgrade

Kapag sinubukan mong magdagdag ng **3rd employee**, lalabas agad ang **Plan Upgrade Modal** na nagpapakita ng:

1. **Current Plan Info:**
   - Free Plan (Up to 2 users)
   - Current Active Users: 2
   - After Adding New User: 3 ❌ (Hindi allowed sa Free Plan)

2. **Available Upgrade Plans:**
   - Starter Monthly Plan - ₱5,000/buwan (Recommended)
   - Starter Yearly Plan - ₱57,000/taon (May savings!)
   - Core Monthly Plan - ₱5,500/buwan
   - Core Yearly Plan - ₱62,700/taon
   - At iba pang higher plans...

3. **Kailangan mong:**
   - Pumili ng plan
   - Magbayad
   - Pagkatapos, pwede ka nang magdagdag ng 3rd employee

## User Experience Flow

```
┌─────────────────────────────────────────────────┐
│  1. May 2 employees ka na (Free Plan limit)     │
│                                                  │
│  2. Click "Add Employee" button                 │
│                                                  │
│  3. ❌ HINDI lalabas ang Add Employee Form      │
│                                                  │
│  4. ✅ LALABAS ang Plan Upgrade Modal           │
│     - Makikita mo current plan (Free Plan)      │
│     - Makikita mo available plans               │
│     - May toggle para Monthly/Yearly            │
│                                                  │
│  5. Piliin ang plan (e.g., Starter Monthly)     │
│                                                  │
│  6. Click "Proceed with Upgrade"                │
│                                                  │
│  7. Bayaran ang invoice                         │
│                                                  │
│  8. ✅ Upgraded na! Pwede na magdagdag ng       │
│     3rd employee at mas marami pa!              │
└─────────────────────────────────────────────────┘
```

## Mga Mahahalagang Punto

✅ **Walang Credit Card Needed** - Libre ang Free Plan, walang kailangang payment setup

✅ **Automatic Upgrade Prompt** - Automatic na lalabas ang modal kapag umabot na sa limit

✅ **Clear na Mensahe** - Alam mo kung bakit kailangan mag-upgrade

✅ **Flexible Options** - Pwede kang pumili ng Monthly o Yearly billing

## Halimbawa ng Scenario

### Scenario: Company ABC (Small Business)

**Day 1:**
- Sign up sa system
- May Free Plan (2 employee limit)
- Add 1st employee: ✅ Success
- Add 2nd employee: ✅ Success

**Day 15:**
- Business is growing!
- Try to add 3rd employee
- 🚀 **Plan Upgrade Modal appears!**
- Message: "Free Plan allows only up to 2 employees. Please upgrade to add more users."

**Modal Shows:**
- Current: Free Plan (2/2 users)
- After adding: 3 users (❌ exceeds limit)
- Recommended: Starter Monthly Plan (₱5,000/month for 10-20 users)

**Action:**
- Select "Starter Monthly Plan"
- See upgrade cost breakdown:
  - Plan Price: ₱5,000
  - Implementation Fee: ₱4,999
  - VAT (12%): ₱1,199.88
  - **Total: ₱11,198.88**

**After Payment:**
- ✅ Upgraded to Starter Monthly Plan
- ✅ Can now add 3rd employee
- ✅ Can add up to 20 employees (with overage up to 20 max)

## Technical Implementation

Ang system ay nag-check ng license bago buksan ang "Add Employee" form:

```javascript
// Pag-click ng "Add Employee" button
1. Check license: /employees/check-license-overage
2. If Free Plan + 2 active employees already:
   → Return "upgrade_required" status
   → Show plan upgrade modal
   → DO NOT show add employee form
3. If within limit:
   → Show add employee form
```

## Support

Kung may tanong tungkol sa Free Plan o plan upgrades:
1. Contact support team
2. Check documentation sa `/documentations/PlanUpgrade/`
3. Review billing and subscription guides

---

**Summary:**
- Free Plan = 2 employees max
- 3rd employee = Automatic upgrade required
- Modal shows available plans
- Choose plan → Pay → Can add more employees! 🎉
