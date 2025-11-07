# Troubleshooting Guide - Implementation Fee & Plan Upgrade Feature

## Issue 1: SQL Error "Data truncated for column 'invoice_type'"

### Symptoms
```
SQLSTATE[01000]: Warning: 1265 Data truncated for column 'invoice_type' at row 1
```

### Root Cause
The `invoice_type` ENUM column in the `invoices` table doesn't include the new values (`implementation_fee` and `plan_upgrade`).

### Solution
1. **Check migration status:**
   ```bash
   php artisan migrate:status | grep invoice
   ```

2. **Run the migration:**
   ```bash
   php artisan migrate
   ```

3. **If migration already ran but ENUM not updated, force update:**
   ```bash
   php artisan migrate:rollback --step=1
   php artisan migrate
   ```

4. **Verify the ENUM was updated:**
   ```bash
   php artisan tinker --execute="
   \$invoice = new App\Models\Invoice();
   \$invoice->tenant_id = 1;
   \$invoice->subscription_id = 1;
   \$invoice->invoice_type = 'implementation_fee';
   \$invoice->invoice_number = 'TEST-001';
   \$invoice->amount_due = 4999.00;
   \$invoice->currency = 'PHP';
   \$invoice->status = 'pending';
   \$invoice->due_date = now()->addDays(7);
   \$invoice->issued_at = now();
   \$invoice->period_start = now();
   \$invoice->period_end = now()->addMonth();
   try {
       \$invoice->save();
       echo '✅ SUCCESS';
       \$invoice->delete();
   } catch (Exception \$e) {
       echo '❌ ERROR: ' . \$e->getMessage();
   }
   "
   ```

5. **Expected output:** ✅ SUCCESS

---

## Issue 2: Add Employee Form Still Shows When It Shouldn't

### Symptoms
- User clicks "Add Employee"
- Implementation fee or upgrade is required
- But the add employee form shows anyway instead of the payment/upgrade modal

### Root Cause
The "Add Employee" button still has `data-bs-toggle="modal"` and `data-bs-target="#add_employee"` attributes, which directly opens the modal without checking requirements first.

### Solution
1. **Edit the Blade template:**
   File: `resources/views/tenant/employee/employeelist.blade.php`

2. **Find the Add Employee button** (around line 55-58):
   ```html
   <!-- WRONG - Don't use this -->
   <a href="#" data-bs-toggle="modal" data-bs-target="#add_employee"
       class="btn btn-primary d-flex align-items-center gap-2">
       <i class="ti ti-circle-plus"></i>Add Employee
   </a>
   ```

3. **Replace with:**
   ```html
   <!-- CORRECT -->
   <a href="#" id="addEmployeeBtn"
       class="btn btn-primary d-flex align-items-center gap-2">
       <i class="ti ti-circle-plus"></i>Add Employee
   </a>
   ```

4. **Clear browser cache** or do a hard refresh:
   - Chrome/Edge: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
   - Firefox: `Ctrl+F5` (Windows) or `Cmd+Shift+R` (Mac)

5. **Verify JavaScript is loaded:**
   - Open browser console (F12)
   - Check for JavaScript errors
   - Verify `employeelist.js` is loaded

---

## Issue 3: JavaScript Not Working (Modal Still Opens Directly)

### Symptoms
- Button has correct ID (`addEmployeeBtn`)
- But clicking it still opens the add employee modal directly
- No check happens

### Root Cause
JavaScript file is cached or not loaded.

### Solution
1. **Check if JavaScript file exists:**
   ```bash
   ls -la public/build/js/employeelist.js
   ```

2. **Verify the function exists in the file:**
   ```bash
   grep -n "checkLicenseBeforeOpeningAddModal" public/build/js/employeelist.js
   ```

3. **Clear Laravel cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

4. **Clear browser cache** (hard refresh)

5. **Check browser console for errors:**
   - Open DevTools (F12)
   - Go to Console tab
   - Look for JavaScript errors
   - Check Network tab to see if `employeelist.js` is loaded

6. **If using Vite, rebuild assets:**
   ```bash
   npm run build
   ```

---

## Issue 4: 402/403 HTTP Errors When Checking License

### Symptoms
```
POST /employees/check-license-overage 402 Payment Required
or
POST /employees/check-license-overage 403 Forbidden
```

### Root Cause
Controller is returning error status codes, but frontend needs to handle them properly.

### Solution
The frontend JavaScript already handles these cases:

```javascript
error: function (xhr) {
    // Handle 402 (implementation fee required)
    if (xhr.status === 402 && xhr.responseJSON) {
        const response = xhr.responseJSON;
        if (response.status === 'implementation_fee_required') {
            showImplementationFeeModal(response.data, null);
            return;
        }
    }
    // Handle 403 (upgrade required)
    if (xhr.status === 403 && xhr.responseJSON) {
        const response = xhr.responseJSON;
        if (response.status === 'upgrade_required') {
            showPlanUpgradeModal(response.data, null);
            return;
        }
    }
    toastr.error('Unable to verify license status. Please try again.');
}
```

**No action needed** - this is expected behavior. The modals should show correctly.

---

## Issue 5: Duplicate Invoices Created

### Symptoms
Multiple implementation fee invoices created for the same subscription.

### Root Cause
No check for existing pending/paid invoices before creating new one.

### Solution
Already implemented in `generateImplementationFeeInvoice()` method:

```php
// Check if implementation fee invoice already exists
$existingInvoice = Invoice::where('tenant_id', $tenantId)
    ->where('subscription_id', $subscription->id)
    ->where('invoice_type', 'implementation_fee')
    ->whereIn('status', ['pending', 'paid'])
    ->first();

if ($existingInvoice) {
    return response()->json([
        'status' => 'success',
        'message' => 'Implementation fee invoice already exists',
        'invoice' => $existingInvoice
    ]);
}
```

**No action needed** - duplicates are prevented.

---

## Issue 6: Implementation Fee Not Marked as Paid

### Symptoms
- User pays implementation fee invoice
- Still can't add 11th+ employees
- System keeps asking for implementation fee

### Root Cause
The `implementation_fee_paid` field in the `subscriptions` table is not updated when the invoice is marked as paid.

### Solution
Need to update payment processing logic to set this field.

**TODO:** Update the payment webhook/processing code:

```php
// When marking implementation fee invoice as paid:
if ($invoice->invoice_type === 'implementation_fee') {
    $subscription = $invoice->subscription;
    $subscription->implementation_fee_paid = $invoice->implementation_fee;
    $subscription->save();
}
```

---

## Issue 7: Routes Not Found (404 Errors)

### Symptoms
```
POST /employees/check-license-overage 404 Not Found
or
POST /employees/generate-implementation-fee-invoice 404 Not Found
```

### Root Cause
Routes not registered or route cache is stale.

### Solution
1. **Check if routes exist:**
   ```bash
   php artisan route:list | grep -E "(checkLicenseOverage|generateImplementationFeeInvoice)"
   ```

2. **Clear route cache:**
   ```bash
   php artisan route:clear
   php artisan cache:clear
   ```

3. **Verify routes in `routes/web.php`:**
   ```php
   Route::post('/employees/check-license-overage', 
       [EmployeeListController::class, 'checkLicenseOverage'])
       ->name('checkLicenseOverage');

   Route::post('/employees/generate-implementation-fee-invoice', 
       [EmployeeListController::class, 'generateImplementationFeeInvoice'])
       ->name('generateImplementationFeeInvoice');
   ```

---

## Issue 8: Modals Not Showing

### Symptoms
- Button click does nothing
- No modal appears
- No errors in console

### Root Cause
Modal HTML might be missing from Blade template.

### Solution
1. **Verify modals exist in Blade template:**
   ```bash
   grep -n 'id="implementation_fee_modal"' resources/views/tenant/employee/employeelist.blade.php
   grep -n 'id="plan_upgrade_modal"' resources/views/tenant/employee/employeelist.blade.php
   ```

2. **Expected output:** Should show line numbers where modals are defined

3. **If missing, add the modal HTML** (see template in documentation)

---

## Issue 9: CSRF Token Mismatch

### Symptoms
```
419 Page Expired
CSRF token mismatch
```

### Root Cause
CSRF token not included in AJAX requests or session expired.

### Solution
1. **Verify CSRF token is set in page:**
   ```html
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

2. **Verify AJAX setup in JavaScript:**
   ```javascript
   $.ajaxSetup({
       headers: {
           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
       }
   });
   ```

3. **Refresh the page** to get a new CSRF token

---

## Issue 10: Plan Upgrade Logic Not Working for Plans Beyond Core

### Symptoms
- System doesn't know what plan to upgrade to after Core
- Hardcoded plan names cause issues

### Root Cause
Current implementation only handles Starter → Core upgrade.

### Solution (Future Enhancement)
Implement dynamic plan hierarchy:

```php
// In Plan model or service
public function getNextPlan()
{
    return Plan::where('max_users', '>', $this->max_users)
        ->orderBy('max_users', 'asc')
        ->first();
}
```

**Current Status:** Only Starter → Core is implemented. Enhancement needed for additional tiers.

---

## Quick Diagnostic Commands

### Check Everything at Once
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/Vertex
./test_implementation_fee.sh
```

### Manual Checks
```bash
# 1. Check migrations
php artisan migrate:status | grep invoice

# 2. Test ENUM values
php artisan tinker --execute="echo implode(', ', array_column((array)DB::select('SHOW COLUMNS FROM invoices WHERE Field = \"invoice_type\"'), 'Type'));"

# 3. Check routes
php artisan route:list | grep -i employee

# 4. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Rebuild assets
npm run build
```

---

## Getting Help

If none of these solutions work:

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Enable debug mode** (`.env`):
   ```
   APP_DEBUG=true
   ```

3. **Check browser console** for JavaScript errors

4. **Check Network tab** in DevTools for failed requests

5. **Review error responses** for more context

---

## Contact & Support

For additional help, provide:
- Error message (full stack trace)
- Laravel version
- Browser console errors
- Network request/response details
- Steps to reproduce
