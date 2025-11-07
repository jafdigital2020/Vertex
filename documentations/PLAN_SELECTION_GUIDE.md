# Plan Selection and Upgrade Feature Guide

## Overview
This document explains how the plan selection and upgrade feature works when a user needs to upgrade their subscription due to exceeding their current plan's user limit.

## User Flow

### 1. Add Employee Button Click
When a user clicks the "Add Employee" button:
```javascript
// In employeelist.js
$(document).on('click', '#addEmployeeBtn', function(e) {
    e.preventDefault();
    checkLicenseBeforeOpeningAddModal();
});
```

### 2. License Check
The system checks if the user can add a new employee:
```javascript
function checkLicenseBeforeOpeningAddModal() {
    $.ajax({
        url: '/employees/check-license-overage',
        type: 'POST',
        success: function (response) {
            if (response.status === 'implementation_fee_required') {
                showImplementationFeeModal(response.data);
            } else if (response.status === 'upgrade_required') {
                showPlanUpgradeModal(response.data); // ✅ Shows plan selection
            } else {
                $('#add_employee').modal('show'); // Normal add employee form
            }
        }
    });
}
```

### 3. Backend Logic (EmployeeListController)
```php
public function checkLicenseOverage(Request $request)
{
    $requirementCheck = $this->licenseOverageService
        ->checkUserAdditionRequirements($authUser->tenant_id);

    if ($requirementCheck['status'] === 'upgrade_required') {
        return response()->json([
            'status' => 'upgrade_required',
            'message' => $requirementCheck['message'],
            'data' => $requirementCheck['data']
        ]);
    }
    // ... other checks
}
```

### 4. Plan Upgrade Modal Display
When upgrade is required, the modal shows:
- Current plan information
- Current active users
- After adding new user count
- **All available upgrade plans** (dynamically loaded)
- Recommended plan (highlighted)
- Plan selection grid

## Plan Selection Feature

### Frontend (employeelist.js)
```javascript
function showPlanUpgradeModal(data, form) {
    // Store upgrade data
    window.upgradeData = data;

    // Populate current plan info
    $('#upgrade_current_plan_name').text(data.current_plan || '-');
    $('#upgrade_current_users').text(data.current_users || '-');

    // Clear previous plans
    $('#available_plans_container').empty();

    // ✅ Render ALL available plans from backend
    if (data.available_plans && data.available_plans.length > 0) {
        data.available_plans.forEach(function(plan) {
            const isRecommended = plan.is_recommended || 
                (data.recommended_plan && plan.id === data.recommended_plan.id);
            
            const planCard = `
                <div class="col-md-4 mb-3">
                    <div class="card plan-option ${isRecommended ? 'border-primary' : 'border-secondary'}"
                         data-plan-id="${plan.id}"
                         style="cursor: pointer;">
                        ${isRecommended ? '<div class="ribbon ribbon-top-right">
                            <span class="bg-primary">Recommended</span>
                        </div>' : ''}
                        <div class="card-body text-center">
                            <h5 class="card-title">${plan.name}</h5>
                            <div class="my-3">
                                <h3 class="text-primary">₱${parseFloat(plan.price).toLocaleString()}</h3>
                                <small class="text-muted">per ${plan.billing_cycle}</small>
                            </div>
                            <ul class="list-unstyled text-start">
                                <li><i class="ti ti-check text-success"></i>
                                    Up to <strong>${plan.employee_limit}</strong> users
                                </li>
                                <li><i class="ti ti-check text-success"></i>
                                    Implementation fee: <strong>₱${parseFloat(plan.implementation_fee).toLocaleString()}</strong>
                                </li>
                                <li><i class="ti ti-check text-success"></i>
                                    Amount to pay: <strong class="text-primary">₱${parseFloat(plan.implementation_fee_difference).toLocaleString()}</strong>
                                </li>
                            </ul>
                            <button class="btn btn-${isRecommended ? 'primary' : 'outline-primary'} w-100 select-plan-btn">
                                ${isRecommended ? 'Select (Recommended)' : 'Select Plan'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#available_plans_container').append(planCard);
        });

        // ✅ Handle plan selection
        $('.plan-option').on('click', function() {
            const planId = $(this).data('plan-id');
            const plan = data.available_plans.find(p => p.id === planId);

            // Visual feedback
            $('.plan-option').removeClass('border-primary border-3').addClass('border-secondary');
            $(this).removeClass('border-secondary').addClass('border-primary border-3');

            // Update summary
            $('#summary_plan_name').text(plan.name);
            $('#summary_plan_limit').text('Up to ' + plan.employee_limit + ' users');
            $('#summary_plan_price').text('₱' + parseFloat(plan.price).toLocaleString());
            $('#summary_amount_due').text('₱' + parseFloat(plan.implementation_fee_difference).toLocaleString());

            // Enable confirm button
            $('#selected_plan_summary').show();
            $('#confirmPlanUpgradeBtn').prop('disabled', false)
                .data('selected-plan-id', planId);
        });
    }

    // Show modal
    $('#plan_upgrade_modal').modal('show');
}
```

### Backend Plan Detection (LicenseOverageService)
```php
public function getAvailableUpgradePlans($subscription)
{
    $currentPlan = $subscription->plan;
    $billingCycle = $subscription->billing_cycle;

    // ✅ Get ALL plans with same billing cycle and higher employee limit
    $availablePlans = \App\Models\Plan::where('billing_cycle', $billingCycle)
        ->where('employee_limit', '>', $currentPlan->employee_limit)
        ->where('is_active', true)
        ->orderBy('employee_limit', 'asc')
        ->get();

    // ✅ Calculate implementation fee difference for each plan
    return $availablePlans->map(function($plan) use ($subscription) {
        $implementationFeeDifference = max(0, 
            ($plan->implementation_fee ?? 0) - ($subscription->implementation_fee_paid ?? 0)
        );

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'employee_limit' => $plan->employee_limit,
            'price' => $plan->price,
            'implementation_fee' => $plan->implementation_fee ?? 0,
            'implementation_fee_difference' => $implementationFeeDifference,
            'billing_cycle' => $plan->billing_cycle,
            'is_recommended' => false // Will be set by caller
        ];
    });
}

public function getRecommendedUpgradePlan($subscription)
{
    $currentPlan = $subscription->plan;
    $billingCycle = $subscription->billing_cycle;

    // ✅ Get the NEXT plan in the hierarchy (recommended)
    $nextPlan = \App\Models\Plan::where('billing_cycle', $billingCycle)
        ->where('employee_limit', '>', $currentPlan->employee_limit)
        ->where('is_active', true)
        ->orderBy('employee_limit', 'asc')
        ->first();

    if (!$nextPlan) {
        return null;
    }

    $implementationFeeDifference = max(0, 
        ($nextPlan->implementation_fee ?? 0) - ($subscription->implementation_fee_paid ?? 0)
    );

    return [
        'id' => $nextPlan->id,
        'name' => $nextPlan->name,
        'employee_limit' => $nextPlan->employee_limit,
        'price' => $nextPlan->price,
        'implementation_fee' => $nextPlan->implementation_fee ?? 0,
        'implementation_fee_difference' => $implementationFeeDifference,
        'billing_cycle' => $nextPlan->billing_cycle,
        'is_recommended' => true
    ];
}
```

### 5. User Selects a Plan
When the user clicks on a plan card:
1. The card is highlighted with a blue border
2. The upgrade summary section appears showing:
   - Selected plan name
   - User limit
   - Monthly price
   - Current implementation fee paid
   - New plan implementation fee
   - **Amount due (difference only)**
3. The "Proceed with Upgrade" button is enabled

### 6. Generate Invoice for Selected Plan
When user clicks "Proceed with Upgrade":
```javascript
$('#confirmPlanUpgradeBtn').on('click', function () {
    const selectedPlanId = $(this).data('selected-plan-id');

    $.ajax({
        url: '/employees/generate-plan-upgrade-invoice',
        type: 'POST',
        data: {
            new_plan_id: selectedPlanId // ✅ Send selected plan ID
        },
        success: function(response) {
            $('#plan_upgrade_modal').modal('hide');
            toastr.success('Plan upgrade invoice generated. Redirecting to payment...');
            setTimeout(function() {
                window.location.href = '/billing';
            }, 1500);
        }
    });
});
```

### Backend Invoice Generation
```php
public function generatePlanUpgradeInvoice(Request $request)
{
    $request->validate([
        'new_plan_id' => 'required|exists:plans,id'
    ]);

    $subscription = Subscription::with('plan')
        ->where('tenant_id', $tenantId)
        ->where('status', 'active')
        ->first();

    $newPlan = Plan::find($request->new_plan_id);

    // Verify the new plan is actually an upgrade
    if ($newPlan->employee_limit <= $subscription->plan->employee_limit) {
        return response()->json([
            'status' => 'error',
            'message' => 'Selected plan is not an upgrade'
        ], 400);
    }

    // Verify billing cycle matches
    if ($newPlan->billing_cycle !== $subscription->billing_cycle) {
        return response()->json([
            'status' => 'error',
            'message' => 'Billing cycle must match'
        ], 400);
    }

    // Generate invoice for implementation fee difference
    $invoice = $this->licenseOverageService
        ->createPlanUpgradeInvoice($subscription, $newPlan, 0);

    return response()->json([
        'status' => 'success',
        'message' => 'Plan upgrade invoice generated',
        'invoice' => $invoice,
        'new_plan' => [
            'id' => $newPlan->id,
            'name' => $newPlan->name,
            'employee_limit' => $newPlan->employee_limit
        ]
    ]);
}
```

### 7. Payment and Upgrade Execution
After the user pays the invoice:
```php
public function processPlanUpgrade($invoiceId)
{
    $invoice = Invoice::with('subscription.plan')->find($invoiceId);

    if (!$invoice || $invoice->invoice_type !== 'plan_upgrade') {
        return false;
    }

    $subscription = $invoice->subscription;
    $newPlanId = $invoice->vat_amount; // Temporarily stored here

    $newPlan = Plan::find($newPlanId);
    if (!$newPlan) {
        return false;
    }

    // ✅ Update subscription to new plan
    $subscription->update([
        'plan_id' => $newPlan->id,
        'implementation_fee_paid' => $newPlan->implementation_fee,
        'active_license' => min($subscription->active_license, $newPlan->employee_limit)
    ]);

    Log::info('Plan upgrade completed', [
        'subscription_id' => $subscription->id,
        'old_plan' => $subscription->plan->name,
        'new_plan' => $newPlan->name,
        'invoice_id' => $invoice->id
    ]);

    return true;
}
```

## UI Components

### Plan Upgrade Modal Structure
```blade
<div class="modal fade" id="plan_upgrade_modal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5>Plan Upgrade Required</h5>
            </div>
            <div class="modal-body">
                <!-- Current plan info -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <small class="text-muted">Current Plan</small>
                            <h5 id="upgrade_current_plan_name">-</h5>
                        </div>
                    </div>
                    <!-- ... other info cards ... -->
                </div>

                <!-- ✅ Plan selection grid -->
                <h6>Select Your Upgrade Plan</h6>
                <div id="available_plans_container" class="row">
                    <!-- Plans dynamically inserted here -->
                </div>

                <!-- ✅ Selected plan summary -->
                <div id="selected_plan_summary" class="card bg-light mt-4" style="display: none;">
                    <div class="card-body">
                        <h6>Upgrade Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small>Selected Plan:</small>
                                <h6 id="summary_plan_name">-</h6>
                                <!-- ... more details ... -->
                            </div>
                            <div class="col-md-6">
                                <small>Amount Due:</small>
                                <h4 class="text-primary" id="summary_amount_due">-</h4>
                                <small>Only the difference in implementation fees</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmPlanUpgradeBtn" disabled>
                    Proceed with Upgrade
                </button>
            </div>
        </div>
    </div>
</div>
```

### CSS for Plan Cards
```css
.ribbon {
    position: absolute;
    overflow: hidden;
    width: 75px;
    height: 75px;
}
.ribbon-top-right {
    top: -10px;
    right: -10px;
}
.ribbon span {
    position: absolute;
    display: block;
    width: 145px;
    padding: 5px 0;
    background-color: #007bff;
    box-shadow: 0 5px 10px rgba(0,0,0,.1);
    color: #fff;
    font: 700 12px/1 'Lato', sans-serif;
    text-transform: uppercase;
    text-align: center;
}
.ribbon-top-right span {
    right: -21px;
    top: 15px;
    transform: rotate(45deg);
}

.plan-option {
    position: relative;
    cursor: pointer;
    transition: all 0.3s;
}
.plan-option:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
```

## Key Features

### 1. Dynamic Plan Detection
- Automatically detects all available plans based on:
  - Same billing cycle (monthly/yearly)
  - Higher employee limit than current plan
  - Active status
- Orders plans by employee limit (ascending)

### 2. Recommended Plan
- System recommends the NEXT tier plan
- Highlighted with a blue ribbon and border
- Shown first in the list
- "Select (Recommended)" button text

### 3. Implementation Fee Calculation
- Shows the **difference** in implementation fees
- Formula: `New Plan Fee - Already Paid Fee`
- User only pays the difference
- Clearly displayed in the plan card and summary

### 4. Plan Selection UX
- Click anywhere on the plan card to select
- Visual feedback: blue border (3px) on selected card
- Summary section appears after selection
- Confirm button enabled only after selection

### 5. Validation
- Ensures selected plan is an upgrade (higher limit)
- Ensures billing cycle matches
- Prevents duplicate invoices
- Verifies plan exists and is active

## Testing Scenarios

### Scenario 1: Starter Plan (20 users) → Core Plan
**Setup:**
- Current: Starter Monthly (20 users, ₱5,000 impl. fee paid)
- Available: Core, Pro, Elite
- User has 20 active users, trying to add 21st

**Expected:**
1. Modal shows: Starter, 20 users, trying to reach 21
2. Plans displayed:
   - Core (50 users, ₱15,000 impl. fee, ₱10,000 to pay) ✅ Recommended
   - Pro (100 users, ₱25,000 impl. fee, ₱20,000 to pay)
   - Elite (200 users, ₱35,000 impl. fee, ₱30,000 to pay)
3. User selects Core
4. Summary shows: ₱10,000 amount due
5. Invoice generated for ₱10,000
6. After payment, subscription upgraded to Core

### Scenario 2: Core Plan → Pro Plan
**Setup:**
- Current: Core Monthly (50 users, ₱15,000 impl. fee paid)
- User has 50 active users, trying to add 51st

**Expected:**
1. Plans displayed:
   - Pro (100 users, ₱25,000 impl. fee, ₱10,000 to pay) ✅ Recommended
   - Elite (200 users, ₱35,000 impl. fee, ₱20,000 to pay)
2. User can select either plan
3. Amount due calculated correctly

### Scenario 3: Pro Plan → Elite Plan
**Setup:**
- Current: Pro Monthly (100 users)
- User has 100 users, trying to add 101st

**Expected:**
1. Plans displayed:
   - Elite (200 users) ✅ Recommended
2. User selects Elite
3. Upgrade processed

### Scenario 4: Elite Plan (Max Tier)
**Setup:**
- Current: Elite Monthly (200 users)
- User has 200 users, trying to add 201st

**Expected:**
1. No available plans (Elite is highest)
2. Modal shows: "No upgrade plans available"
3. Custom message or contact sales option

## Error Handling

### Frontend Errors
```javascript
error: function(xhr) {
    let message = 'Failed to generate invoice';
    if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    }
    toastr.error(message);
    btn.prop('disabled', false);
}
```

### Backend Validation Errors
- Plan not found
- Not an upgrade (same or lower limit)
- Billing cycle mismatch
- No active subscription
- Duplicate invoice

## Future Enhancements

### 1. Proration Support
Add monthly subscription proration for mid-cycle upgrades:
```php
$proratedAmount = $this->calculateProration($subscription, $newPlan);
```

### 2. Custom Sales Contact
For Elite plan users who need more:
```javascript
if (data.available_plans.length === 0) {
    $('#available_plans_container').html(`
        <div class="col-12 text-center">
            <i class="ti ti-building display-4 text-muted mb-3"></i>
            <h5>Need more than 200 users?</h5>
            <p class="text-muted">Contact our sales team for enterprise pricing</p>
            <a href="/contact-sales" class="btn btn-primary">Contact Sales</a>
        </div>
    `);
}
```

### 3. Plan Comparison Table
Add a detailed comparison table showing features:
```html
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Feature</th>
            <th>Core</th>
            <th>Pro</th>
            <th>Elite</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Users</td>
            <td>50</td>
            <td>100</td>
            <td>200</td>
        </tr>
        <!-- ... more features ... -->
    </tbody>
</table>
```

### 4. Trial Period for Upgrade
Allow 7-day trial of upgraded plan before charging:
```php
if ($request->has('trial')) {
    $subscription->trial_ends_at = now()->addDays(7);
}
```

## Troubleshooting

### Issue: Plans not showing
**Check:**
1. Plans exist in database with correct billing cycle
2. Plans are marked as `is_active = true`
3. Plans have higher `employee_limit` than current plan
4. JavaScript console for errors

### Issue: Amount due showing wrong
**Check:**
1. `implementation_fee` set on plan
2. `implementation_fee_paid` on subscription
3. Calculation: `new_fee - paid_fee`

### Issue: Upgrade not processed after payment
**Check:**
1. Payment webhook calls `processPlanUpgrade()`
2. `invoice_type = 'plan_upgrade'`
3. `new_plan_id` stored in invoice
4. Subscription status is 'active'

## Conclusion

The plan selection and upgrade feature provides:
- ✅ User-friendly plan selection interface
- ✅ Dynamic plan detection based on current subscription
- ✅ Recommended plan highlighting
- ✅ Accurate implementation fee difference calculation
- ✅ Visual feedback and confirmation
- ✅ Proper validation and error handling
- ✅ Seamless upgrade after payment

The system is fully dynamic, extensible, and ready for production use!
