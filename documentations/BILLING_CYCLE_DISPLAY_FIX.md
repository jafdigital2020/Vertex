# Billing Cycle Display Fix

## Issue
When selecting a yearly plan during upgrade, the invoice was showing "monthly" instead of "yearly" in the billing cycle display.

## Root Cause
The `billing_cycle` column migration was **pending** and had not been run on the database. While the code was correctly storing the billing cycle in the invoice creation logic, the column didn't exist in the database, so the value was not being persisted.

## Solution Implemented

### 1. Migration Applied ✅
```bash
php artisan migrate
```
- Migration file: `2025_01_09_000001_add_billing_cycle_to_invoices_table.php`
- Added `billing_cycle` enum column to `invoices` table with values: 'monthly', 'yearly'
- Column placed after `invoice_type` for logical ordering

### 2. Enhanced UI Display ✅

#### A. Invoice Table Description
**File**: `resources/views/tenant/billing/billing.blade.php`

Added billing cycle badge to plan upgrade invoices in the table:
```blade
@elseif(($inv->invoice_type ?? 'subscription') === 'plan_upgrade')
    Plan Upgrade: {{ $inv->upgradePlan->name ?? 'Plan Upgrade' }}
    @if($inv->billing_cycle)
        <span class="badge bg-primary ms-1">{{ ucfirst($inv->billing_cycle) }}</span>
    @endif
    <br><small class="text-info">
        <i class="ti ti-arrow-up me-1"></i>
        Upgrading from {{ $inv->subscription->plan->name ?? 'Current Plan' }}
        ({{ ucfirst($inv->subscription->billing_cycle ?? 'N/A') }})
    </small>
```

**Display Example**:
- "Plan Upgrade: Elite Yearly [Yearly Badge]"
- "Upgrading from Core (Monthly)"

#### B. Invoice Modal
Updated the modal to display billing cycle in the plan upgrade row:
```javascript
trPlan.innerHTML = `
    <td>Plan Price Difference
        <br><small class="text-muted">From ${d.currentPlan || 'Current Plan'} to ${d.plan || 'New Plan'} (${d.billingCycle ? d.billingCycle.charAt(0).toUpperCase() + d.billingCycle.slice(1) : 'N/A'})</small>
    </td>
    ...
`;
```

**Display Example**:
- "From Core to Elite (Yearly)"

#### C. PDF Download
Updated PDF generation to include billing cycle:
```javascript
upgradeItemsHTML += `
    <tr>
        <td>Plan Price Difference<br><small style="color: #666;">From ${data.currentPlan || 'Current Plan'} to ${data.plan || 'New Plan'} (${data.billingCycle ? data.billingCycle.charAt(0).toUpperCase() + data.billingCycle.slice(1) : 'N/A'})</small></td>
        ...
    </tr>
`;
```

### 3. Existing Backend Logic (Already Correct) ✅

#### Invoice Creation
**File**: `app/Services/LicenseOverageService.php`
```php
$invoice = Invoice::create([
    // ...
    'billing_cycle' => $newPlan->billing_cycle, // ✅ Correctly stores billing cycle
    // ...
]);
```

#### Plan Upgrade Processing
**File**: `app/Http/Controllers/Tenant/Employees/EmployeeListController.php`
```php
$subscription->billing_cycle = $newPlan->billing_cycle; // ✅ Updates subscription billing cycle
```

## Files Modified

1. **resources/views/tenant/billing/billing.blade.php**
   - Added billing cycle badge in invoice table description
   - Added billing cycle display in invoice modal
   - Added billing cycle in PDF generation

## Testing Checklist

### Before Migration (Issue Present)
- [ ] Invoice table shows plan name but no billing cycle indicator
- [ ] Invoice modal doesn't show billing cycle
- [ ] PDF download doesn't show billing cycle
- [ ] Database query shows `billing_cycle` column doesn't exist

### After Migration (Issue Fixed)
- [x] Migration successfully applied (`php artisan migrate`)
- [x] `billing_cycle` column exists in `invoices` table
- [x] New plan upgrade invoices store billing cycle correctly
- [x] Invoice table shows billing cycle badge (e.g., "Yearly")
- [x] Invoice modal shows billing cycle (e.g., "From Core to Elite (Yearly)")
- [x] PDF download includes billing cycle information
- [x] Correct billing cycle is displayed for both monthly and yearly upgrades

## Example Scenarios

### Scenario 1: Upgrade from Monthly to Yearly
- **Action**: User upgrades from "Core Monthly" to "Elite Yearly"
- **Expected**: 
  - Invoice table shows: "Plan Upgrade: Elite Yearly [Yearly]"
  - Invoice modal shows: "From Core to Elite (Yearly)"
  - PDF shows: billing cycle as "Yearly"

### Scenario 2: Upgrade from Monthly to Monthly
- **Action**: User upgrades from "Core Monthly" to "Pro Monthly"
- **Expected**:
  - Invoice table shows: "Plan Upgrade: Pro Monthly [Monthly]"
  - Invoice modal shows: "From Core to Pro (Monthly)"
  - PDF shows: billing cycle as "Monthly"

### Scenario 3: Upgrade from Yearly to Yearly
- **Action**: User upgrades from "Pro Yearly" to "Elite Yearly"
- **Expected**:
  - Invoice table shows: "Plan Upgrade: Elite Yearly [Yearly]"
  - Invoice modal shows: "From Pro to Elite (Yearly)"
  - PDF shows: billing cycle as "Yearly"

## Deployment Steps

### Local/Development
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/Vertex
php artisan migrate
```

### Production
```bash
cd /path/to/production
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Verification

After deployment, verify:
1. Create a new plan upgrade invoice
2. Check invoice table - billing cycle badge should appear
3. Click "View" - modal should show correct billing cycle
4. Download PDF - billing cycle should be included
5. Check database: `SELECT id, invoice_number, invoice_type, billing_cycle FROM invoices WHERE invoice_type = 'plan_upgrade' ORDER BY id DESC LIMIT 5;`

## Related Documentation
- Migration: `database/migrations/2025_01_09_000001_add_billing_cycle_to_invoices_table.php`
- Invoice Model: `app/Models/Invoice.php`
- Service: `app/Services/LicenseOverageService.php`
- Controller: `app/Http/Controllers/Tenant/Employees/EmployeeListController.php`
- View: `resources/views/tenant/billing/billing.blade.php`

## Summary
The issue was caused by a pending migration. Once the migration was run to add the `billing_cycle` column to the `invoices` table, the backend code (which was already correct) began storing the billing cycle properly. Additionally, UI enhancements were made to clearly display the billing cycle in:
- Invoice table (badge)
- Invoice modal (text description)
- PDF download (text description)

This ensures users can clearly see which billing cycle they selected during plan upgrade.
