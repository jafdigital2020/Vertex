# Plan Selection and Upgrade Flow Diagram

## Visual Flow Chart

```
┌─────────────────────────────────────────────────────────────────────┐
│                   User Clicks "Add Employee" Button                  │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│            AJAX: POST /employees/check-license-overage              │
│                    (checkLicenseBeforeOpeningAddModal)              │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Backend: EmployeeListController::checkLicenseOverage()             │
│           calls LicenseOverageService::checkUserAdditionRequirements│
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                ┌────────────────┼────────────────┐
                │                │                │
                ▼                ▼                ▼
    ┌───────────────┐  ┌─────────────────┐  ┌─────────────────┐
    │  Status: OK   │  │ Implementation  │  │ Upgrade         │
    │               │  │ Fee Required    │  │ Required        │
    └───────┬───────┘  └────────┬────────┘  └────────┬────────┘
            │                   │                     │
            ▼                   ▼                     ▼
    ┌───────────────┐  ┌─────────────────┐  ┌─────────────────┐
    │ Open Add      │  │ Show Impl. Fee  │  │ Show Plan       │
    │ Employee      │  │ Modal           │  │ Upgrade Modal   │
    │ Modal         │  │                 │  │ ◄─── FOCUS HERE │
    └───────────────┘  └─────────────────┘  └────────┬────────┘
                                                      │
                                                      ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      PLAN UPGRADE MODAL                              │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ Current Plan Info:                                         │    │
│  │ - Plan Name: "Core Starter Monthly Plan"                  │    │
│  │ - Current Users: 20                                        │    │
│  │ - After Adding: 21 users                                  │    │
│  └────────────────────────────────────────────────────────────┘    │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ Select Your Upgrade Plan:                                  │    │
│  │                                                             │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │    │
│  │  │ Core Plan    │  │ Pro Plan     │  │ Elite Plan   │    │    │
│  │  │ RECOMMENDED  │  │              │  │              │    │    │
│  │  │──────────────│  │──────────────│  │──────────────│    │    │
│  │  │ 100 users    │  │ 200 users    │  │ 500 users    │    │    │
│  │  │ ₱62,700/year │  │ ₱108,300/yr  │  │ ₱165,300/yr  │    │    │
│  │  │              │  │              │  │              │    │    │
│  │  │ Impl Fee:    │  │ Impl Fee:    │  │ Impl Fee:    │    │    │
│  │  │ ₱14,999      │  │ ₱39,999      │  │ ₱79,999      │    │    │
│  │  │              │  │              │  │              │    │    │
│  │  │ To Pay:      │  │ To Pay:      │  │ To Pay:      │    │    │
│  │  │ ₱10,000 ✓    │  │ ₱35,000      │  │ ₱75,000      │    │    │
│  │  │              │  │              │  │              │    │    │
│  │  │ [Select]     │  │ [Select]     │  │ [Select]     │    │    │
│  │  └──────────────┘  └──────────────┘  └──────────────┘    │    │
│  │                                                             │    │
│  └────────────────────────────────────────────────────────────┘    │
│                                                                      │
│  User clicks on a plan card...                                      │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│              JavaScript: $('.plan-option').on('click')              │
│                                                                      │
│  1. Remove border from all cards                                    │
│  2. Add blue border to selected card                                │
│  3. Find plan data from available_plans array                       │
│  4. Update summary section                                          │
│  5. Enable "Proceed with Upgrade" button                           │
│  6. Store selected plan ID in button data                           │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    UPGRADE SUMMARY APPEARS                           │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ Selected Plan: Core Monthly Plan                           │    │
│  │ User Limit: Up to 100 users                                │    │
│  │ Monthly Price: ₱62,700.00                                  │    │
│  │                                                             │    │
│  │ Current Impl. Fee Paid: ₱4,999.00                          │    │
│  │ New Plan Impl. Fee: ₱14,999.00                             │    │
│  │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │    │
│  │ Amount Due: ₱10,000.00                                     │    │
│  │ (Only the difference in implementation fees)               │    │
│  └────────────────────────────────────────────────────────────┘    │
│                                                                      │
│  [Cancel]  [Proceed with Upgrade] ← Enabled                        │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                     User clicks "Proceed with Upgrade"
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│   AJAX: POST /employees/generate-plan-upgrade-invoice               │
│   Data: { new_plan_id: <selected_plan_id> }                        │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Backend: EmployeeListController::generatePlanUpgradeInvoice()     │
│                                                                      │
│  1. Validate new_plan_id exists                                     │
│  2. Get current subscription and plan                               │
│  3. Verify new plan is an upgrade (higher limit)                    │
│  4. Verify billing cycle matches                                    │
│  5. Check for existing plan_upgrade invoice                         │
│  6. Calculate implementation fee difference:                        │
│     - New Plan Fee: ₱14,999                                         │
│     - Already Paid: ₱4,999                                          │
│     - Difference: ₱10,000                                           │
│  7. Call LicenseOverageService::createPlanUpgradeInvoice()         │
│  8. Store new_plan_id in invoice for later processing              │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│      LicenseOverageService::createPlanUpgradeInvoice()              │
│                                                                      │
│  Create Invoice:                                                     │
│  - invoice_type: 'plan_upgrade'                                     │
│  - amount_due: ₱10,000 (implementation fee difference)             │
│  - implementation_fee: ₱10,000                                      │
│  - period_start: current period start                               │
│  - period_end: current period end                                   │
│  - status: 'pending'                                                │
│  - due_date: 7 days from now                                        │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                   Invoice Created Successfully                       │
│                   Return to Frontend                                 │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│              JavaScript Success Handler                              │
│                                                                      │
│  1. Hide plan upgrade modal                                         │
│  2. Show success toastr message                                     │
│  3. Redirect to /billing page after 1.5 seconds                     │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      BILLING PAGE                                    │
│                                                                      │
│  User sees:                                                          │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ Invoice #INV-UPG-20250107-XXXXX                            │    │
│  │ Type: Plan Upgrade                                          │    │
│  │ Amount: ₱10,000.00                                          │    │
│  │ Status: Pending                                             │    │
│  │ Due: November 14, 2025                                      │    │
│  │                                                              │    │
│  │ [Pay Now]                                                   │    │
│  └────────────────────────────────────────────────────────────┘    │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                         User clicks "Pay Now"
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                   PAYMENT PROCESSING                                 │
│  (PayMongo, DragonPay, or other payment gateway)                    │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                         Payment Successful
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    PAYMENT WEBHOOK                                   │
│  (PaymentController or Webhook Handler)                             │
│                                                                      │
│  1. Receive payment notification                                    │
│  2. Update invoice status to 'paid'                                 │
│  3. Update invoice paid_at timestamp                                │
│  4. Check invoice_type === 'plan_upgrade'                           │
│  5. Call EmployeeListController::processPlanUpgrade($invoice_id)   │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│      EmployeeListController::processPlanUpgrade($invoiceId)         │
│                                                                      │
│  1. Get invoice with subscription                                   │
│  2. Verify invoice_type === 'plan_upgrade'                          │
│  3. Get new_plan_id from invoice (stored in vat_amount field)      │
│  4. Find new plan                                                   │
│  5. Update subscription:                                            │
│     UPDATE subscriptions SET                                        │
│       plan_id = <new_plan_id>,                                      │
│       implementation_fee_paid = <new_plan_implementation_fee>       │
│     WHERE id = <subscription_id>                                    │
│  6. Log the upgrade                                                 │
│                                                                      │
└──────────────────────────────────┬───────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                  SUBSCRIPTION UPGRADED! ✅                          │
│                                                                      │
│  Old Plan: Core Starter Monthly (20 users)                          │
│  New Plan: Core Monthly (100 users)                                 │
│  Implementation Fee Paid: ₱14,999                                   │
│                                                                      │
│  User can now:                                                       │
│  - Add up to 100 employees                                          │
│  - Continue adding employees without upgrade modal                  │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

## Data Flow Details

### 1. Frontend Data Structure

**Available Plans Array (from backend):**
```javascript
window.upgradeData = {
    current_plan: "Core Starter Monthly Plan",
    current_users: 20,
    new_user_count: 21,
    current_plan_limit: 20,
    current_implementation_fee_paid: 4999.00,
    billing_cycle: "monthly",
    recommended_plan: {
        id: 2,
        name: "Core Monthly Plan",
        employee_limit: 100,
        price: 62700.00,
        implementation_fee: 14999.00,
        implementation_fee_difference: 10000.00,
        billing_cycle: "monthly",
        is_recommended: true
    },
    available_plans: [
        {
            id: 2,
            name: "Core Monthly Plan",
            employee_limit: 100,
            price: 62700.00,
            implementation_fee: 14999.00,
            implementation_fee_difference: 10000.00,
            billing_cycle: "monthly",
            is_recommended: true
        },
        {
            id: 3,
            name: "Pro Monthly Plan",
            employee_limit: 200,
            price: 108300.00,
            implementation_fee: 39999.00,
            implementation_fee_difference: 35000.00,
            billing_cycle: "monthly",
            is_recommended: false
        },
        {
            id: 4,
            name: "Elite Monthly Plan",
            employee_limit: 500,
            price: 165300.00,
            implementation_fee: 79999.00,
            implementation_fee_difference: 75000.00,
            billing_cycle: "monthly",
            is_recommended: false
        }
    ]
};
```

### 2. Plan Selection Event Handler
```javascript
$('.plan-option').on('click', function() {
    const planId = $(this).data('plan-id'); // e.g., 2
    const plan = data.available_plans.find(p => p.id === planId);
    
    // Visual feedback
    $('.plan-option').removeClass('border-primary border-3');
    $(this).addClass('border-primary border-3');
    
    // Update summary
    $('#summary_plan_name').text(plan.name);
    $('#summary_plan_limit').text('Up to ' + plan.employee_limit + ' users');
    $('#summary_plan_price').text('₱' + parseFloat(plan.price).toLocaleString());
    $('#summary_current_impl_fee').text('₱' + parseFloat(data.current_implementation_fee_paid).toLocaleString());
    $('#summary_new_impl_fee').text('₱' + parseFloat(plan.implementation_fee).toLocaleString());
    $('#summary_amount_due').text('₱' + parseFloat(plan.implementation_fee_difference).toLocaleString());
    
    // Enable confirm button with selected plan ID
    $('#selected_plan_summary').show();
    $('#confirmPlanUpgradeBtn')
        .prop('disabled', false)
        .data('selected-plan-id', planId); // Store planId: 2
});
```

### 3. Invoice Generation Request
```javascript
$('#confirmPlanUpgradeBtn').on('click', function() {
    const selectedPlanId = $(this).data('selected-plan-id'); // Get planId: 2
    
    $.ajax({
        url: '/employees/generate-plan-upgrade-invoice',
        type: 'POST',
        data: {
            new_plan_id: selectedPlanId // Send: 2
        },
        success: function(response) {
            // Redirect to billing
            window.location.href = '/billing';
        }
    });
});
```

### 4. Backend Validation
```php
public function generatePlanUpgradeInvoice(Request $request)
{
    // Validate
    $request->validate([
        'new_plan_id' => 'required|exists:plans,id'
    ]);
    
    $newPlanId = $request->new_plan_id; // 2
    $newPlan = Plan::find($newPlanId);   // Core Monthly Plan
    
    // Verify upgrade
    if ($newPlan->employee_limit <= $subscription->plan->employee_limit) {
        return error('Not an upgrade');
    }
    
    // Verify billing cycle
    if ($newPlan->billing_cycle !== $subscription->billing_cycle) {
        return error('Billing cycle must match');
    }
    
    // Generate invoice
    $invoice = $this->licenseOverageService->createPlanUpgradeInvoice(
        $subscription,
        $newPlan,
        0 // proration
    );
    
    // Store new plan ID for later processing
    $invoice->update(['vat_amount' => $newPlan->id]);
    
    return success($invoice);
}
```

### 5. Database State After Invoice Generation

**Invoices Table:**
```sql
INSERT INTO invoices (
    tenant_id,
    subscription_id,
    invoice_type,
    invoice_number,
    amount_due,
    implementation_fee,
    status,
    due_date,
    vat_amount,        -- Temporarily storing new_plan_id
    created_at
) VALUES (
    123,               -- tenant_id
    456,               -- subscription_id
    'plan_upgrade',
    'INV-UPG-20250107-001',
    10000.00,          -- implementation fee difference
    10000.00,
    'pending',
    '2025-11-14',
    2,                 -- new_plan_id stored here
    NOW()
);
```

### 6. After Payment - Upgrade Process
```php
public function processPlanUpgrade($invoiceId)
{
    $invoice = Invoice::with('subscription.plan')->find($invoiceId);
    
    // Get new plan ID
    $newPlanId = $invoice->vat_amount; // 2
    $newPlan = Plan::find($newPlanId); // Core Monthly Plan
    
    // Update subscription
    $invoice->subscription->update([
        'plan_id' => $newPlan->id,                           // 2
        'implementation_fee_paid' => $newPlan->implementation_fee, // 14999.00
    ]);
    
    // Subscription is now upgraded!
}
```

### 7. Final State

**Subscriptions Table (Before):**
```sql
| id  | tenant_id | plan_id | implementation_fee_paid |
|-----|-----------|---------|------------------------|
| 456 | 123       | 1       | 4999.00                |
```

**Subscriptions Table (After):**
```sql
| id  | tenant_id | plan_id | implementation_fee_paid |
|-----|-----------|---------|------------------------|
| 456 | 123       | 2       | 14999.00               |
```

## Key Implementation Details

### Implementation Fee Calculation
```
Current Plan: Starter (impl_fee: ₱4,999, already paid: ₱4,999)
Selected Plan: Core (impl_fee: ₱14,999)

Calculation:
Amount to Pay = New Plan Fee - Already Paid
             = ₱14,999 - ₱4,999
             = ₱10,000 ✅

User only pays the DIFFERENCE!
```

### Plan Hierarchy (Monthly)
```
1. Core Starter Monthly → 20 users  → ₱4,999 impl fee
2. Core Monthly         → 100 users → ₱14,999 impl fee
3. Pro Monthly          → 200 users → ₱39,999 impl fee
4. Elite Monthly        → 500 users → ₱79,999 impl fee
```

### Recommended Plan Logic
```php
// Get next tier plan (first plan with higher limit)
$nextPlan = Plan::where('billing_cycle', $subscription->billing_cycle)
    ->where('employee_limit', '>', $subscription->plan->employee_limit)
    ->orderBy('employee_limit', 'asc')
    ->first();

// Starter → Core (recommended)
// Core → Pro (recommended)
// Pro → Elite (recommended)
// Elite → NULL (no further upgrades)
```

## Edge Cases Handled

1. **No Plans Available** (Elite users)
   - Shows "No upgrade plans available" message
   - Optional: "Contact Sales" button

2. **Billing Cycle Mismatch**
   - Only shows plans with same billing cycle
   - Monthly user sees monthly plans only
   - Yearly user sees yearly plans only

3. **Not an Upgrade**
   - Validates employee_limit > current limit
   - Rejects downgrades or same-tier plans

4. **Duplicate Invoice**
   - Checks for existing plan_upgrade invoice
   - Returns existing invoice if found within 7 days

5. **Payment Failure**
   - Invoice remains 'pending'
   - Subscription not upgraded
   - User can retry payment

## Success Metrics

✅ **Dynamic Plan Loading** - All plans loaded from database
✅ **User Choice** - User can select any available plan
✅ **Visual Feedback** - Clear indication of selected plan
✅ **Accurate Calculation** - Only charges the difference
✅ **Safe Upgrade** - Only upgrades after payment confirmed
✅ **No Duplication** - Prevents duplicate invoices
✅ **Proper Validation** - All edge cases handled

## Testing Checklist

- [ ] Plans appear in modal
- [ ] Recommended plan is highlighted
- [ ] Plan selection works (blue border)
- [ ] Summary appears after selection
- [ ] Amount calculation is correct
- [ ] Invoice generated with correct plan_id
- [ ] Redirect to billing works
- [ ] Payment processes correctly
- [ ] Subscription upgraded after payment
- [ ] User can add employees after upgrade
- [ ] No duplicate invoices created
- [ ] Error handling works
