# Free Plan Implementation

## Overview
The Free Plan allows tenants to use the system with up to 2 employees at no cost. When attempting to add a 3rd employee, they will be required to upgrade to a paid plan.

## Plan Details
- **Name:** Free Plan
- **Employee Minimum:** 1
- **Employee Limit:** 2
- **Price:** ₱0.00
- **Implementation Fee:** ₱0.00
- **Overage Allowed:** No

## Behavior

### Adding Employees (1-2)
✅ **Allowed**
- Users can add up to 2 employees without any payment
- No upgrade required
- No implementation fee

### Adding 3rd Employee
🚫 **Blocked - Upgrade Required**
- When trying to add the 3rd employee, the plan upgrade modal will appear
- No overage is allowed for Free Plan
- User must select and pay for a paid plan (Starter, Core, Pro, or Elite)

### Upgrade Options
When upgrading from Free Plan, users can choose:
1. **Starter Monthly Plan** - ₱5,000/month (Recommended for 10-20 employees)
2. **Starter Yearly Plan** - ₱57,000/year (Save more!)
3. **Core Monthly Plan** - ₱5,500/month (21-100 employees)
4. **Core Yearly Plan** - ₱62,700/year
5. And other higher-tier plans...

## Implementation Details

### Database Seeder
```php
Plan::create([
    'name' => 'Free Plan',
    'description' => 'Free plan for up to 2 employees.',
    'price' => 0.00,
    'currency' => 'PHP',
    'billing_cycle' => 'monthly',
    'employee_minimum' => 1,
    'employee_limit' => 2,
    'employee_price' => 0.00,
    'trial_days' => 0,
    'is_active' => true,
    'price_per_license' => 0.00,
    'implementation_fee' => 0.00,
    'vat_percentage' => 12,
    'base_license_count' => 2,
]);
```

### Service Logic (`LicenseOverageService.php`)
The `checkUserAdditionRequirements()` method includes Free Plan logic:

```php
// FREE PLAN LOGIC - No overage allowed, must upgrade after 2 users
if ($isFreePlan) {
    $freePlanLimit = $subscription->plan->employee_limit ?? 2;

    // If trying to add 3rd user, require upgrade
    if ($newUserCount > $freePlanLimit) {
        return [
            'status' => 'upgrade_required',
            'message' => 'Free Plan allows only up to ' . $freePlanLimit . ' employees.',
            // ... returns available plans for upgrade
        ];
    }

    // Within Free Plan limit - user can be added
    return ['status' => 'ok'];
}
```

### Frontend Handling
The existing plan upgrade modal (`plan_upgrade_modal`) automatically handles Free Plan upgrades:
- Shows current plan info (Free Plan, 2 employee limit)
- Displays available upgrade plans (Monthly and Yearly)
- Allows billing cycle toggle
- Shows upgrade costs with VAT

## User Journey

### Scenario: Adding 3rd Employee on Free Plan

1. **User Action:** Clicks "Add Employee" button
2. **System Check:** `checkLicenseBeforeOpeningAddModal()` is called
3. **Detection:** System detects user has Free Plan with 2 active employees
4. **Response:** `upgrade_required` status is returned
5. **UI:** Plan upgrade modal appears instead of employee form
6. **Display:**
   - Current Plan: Free Plan (Up to 2 users)
   - Current Active Users: 2
   - After Adding New User: 3
   - Available Plans: Starter, Core, Pro, Elite (Monthly & Yearly)
7. **User Choice:** Selects a plan and proceeds to payment
8. **After Payment:** Subscription is upgraded, user can add 3rd employee

## Key Features

✅ **No Credit Card Required** - Free Plan doesn't require payment setup
✅ **Instant Upgrade Prompt** - Modal appears automatically when limit is reached
✅ **Flexible Upgrade Options** - Can choose any paid plan and billing cycle
✅ **Clear Messaging** - Users know exactly why upgrade is needed

## Testing

### Test Case 1: Adding 1st and 2nd Employee
```
GIVEN a tenant with Free Plan and 0 employees
WHEN they add the 1st employee
THEN the employee is added successfully

WHEN they add the 2nd employee
THEN the employee is added successfully
```

### Test Case 2: Attempting to Add 3rd Employee
```
GIVEN a tenant with Free Plan and 2 active employees
WHEN they click "Add Employee" button
THEN the plan upgrade modal appears
AND the employee form does NOT appear
AND available plans (Starter, Core, Pro, Elite) are shown
```

### Test Case 3: After Upgrading from Free Plan
```
GIVEN a tenant just upgraded from Free Plan to Starter Plan
WHEN they click "Add Employee" button
THEN the employee form appears
AND they can add employees up to Starter Plan limit
```

## Notes

- Free Plan uses `employee_minimum = 1` to ensure it's the lowest tier
- Free Plan uses `employee_limit = 2` to enforce the 2-employee limit
- No overage is allowed for Free Plan (hard limit)
- Implementation fee is ₱0 for Free Plan
- When upgrading, implementation fee difference is calculated based on new plan

## Related Files

- `database/seeders/PlanSeeder.php` - Plan definitions
- `app/Services/LicenseOverageService.php` - License checking logic
- `app/Http/Controllers/Tenant/Employees/EmployeeListController.php` - Controller
- `public/build/js/employeelist.js` - Frontend logic
- `resources/views/tenant/employee/employeelist.blade.php` - Plan upgrade modal UI
