# Billing Test Users Seeder Guide

## Overview
This seeder creates 90 test users specifically designed to test all billing scenarios in your system.

## Created: November 9, 2025

---

## Prerequisites

Before running the seeder, ensure you have:

1. ✅ At least one tenant in the database
2. ✅ Active subscription for the tenant (Starter plan recommended)
3. ✅ At least one branch, department, designation, and role configured
4. ✅ Branch has contribution settings configured (SSS, PhilHealth, Pag-IBIG)

---

## How to Run

### Method 1: Run Directly
```bash
php artisan db:seed --class=BillingTestUsersSeeder
```

### Method 2: Add to DatabaseSeeder
Edit `database/seeders/DatabaseSeeder.php`:
```php
public function run()
{
    // ...existing seeders...
    
    $this->call([
        BillingTestUsersSeeder::class,
    ]);
}
```

Then run:
```bash
php artisan db:seed
```

### Method 3: Fresh Migration with Seeder
⚠️ **WARNING: This will delete all data!**
```bash
php artisan migrate:fresh --seed
```

---

## What Gets Created

### 90 Test Users Breakdown

| User Range | Scenario | Expected Behavior |
|------------|----------|-------------------|
| **1-10** | Within base limit | ✅ No additional charges |
| **11** | 11th user on Starter | ⚠️ Implementation fee required (₱2,000) |
| **12-20** | Overage users | 💰 License overage invoices (₱49 each) |
| **21-90** | Beyond limit | 🚀 Plan upgrade required |

### User Details
Each user gets:
- ✅ User account (username, email, password)
- ✅ Personal information (name, phone, gender, civil status, DOB)
- ✅ Employment details (employee ID, hire date, employment type)
- ✅ Government IDs (SSS, PhilHealth, Pag-IBIG, TIN)
- ✅ Salary details (with contribution settings from branch)
- ✅ User permissions (assigned to available role)

### Naming Convention
- **Username**: `firstname` + `lastname` + `number`
  - Example: `juansantos1`, `mariareyes2`, `pedrocruz3`
- **Email**: `firstname.lastname.number@testbilling.com`
  - Example: `juan.santos.1@testbilling.com`
- **Employee ID**: `TEST-0001`, `TEST-0002`, etc.
- **Biometrics ID**: `BIO-000001`, `BIO-000002`, etc.
- **Password**: `password123` (same for all test users)

---

## Testing Scenarios

### Scenario 1: Within Base Limit (Users 1-10)
**Expected:**
- ✅ Users created successfully
- ✅ No invoices generated
- ✅ Subscription status remains active
- ✅ No additional charges

**To Test:**
1. Check `/billing` page
2. Verify no overage invoices exist
3. Confirm active license count = 10

---

### Scenario 2: Implementation Fee (User 11)
**Expected:**
- ⚠️ Implementation fee invoice created
- 💵 Amount: ₱2,240 (₱2,000 + 12% VAT)
- 📄 Invoice type: `implementation_fee`
- 🔒 User can be added but fee must be paid to continue beyond 20 users

**To Test:**
1. Check `/billing` page for implementation fee invoice
2. Verify invoice details:
   - Subtotal: ₱2,000
   - VAT: ₱240
   - Total: ₱2,240
3. Try adding user (should work)
4. Pay invoice to unlock 11-20 user range
5. Verify `subscription.implementation_fee_paid = 2000`

**Modal Flow:**
```
User 11 added → Implementation fee required
             → Modal shows fee breakdown
             → User clicks "Proceed to Payment"
             → Invoice generated
             → Redirected to /billing
             → User pays via HitPay
             → implementation_fee_paid updated
```

---

### Scenario 3: License Overage (Users 12-20)
**Expected:**
- 💰 License overage invoice for each user
- 💵 Amount per user: ₱54.88 (₱49 + 12% VAT)
- 📄 Invoice type: `license_overage`
- 📊 9 separate overage invoices created
- 🔗 Will be consolidated in next renewal invoice

**To Test:**
1. Check `/billing` page for license overage invoices
2. Verify each invoice:
   - license_overage_count: 1
   - license_overage_amount: ₱49
   - VAT: ₱5.88
   - Total: ₱54.88
   - Status: `pending`
3. Check invoice table shows:
   - Description, Period, **Quantity**, **Rate**, Amount (5 columns)
4. Verify license usage logs created
5. Test monthly overage billing command:
   ```bash
   php artisan invoices:generate-monthly-overage
   ```

**Consolidated Renewal:**
When renewal time comes:
- Base subscription: ₱5,000
- 9 overage users × ₱49 = ₱441
- Subtotal: ₱5,441
- VAT (12%): ₱652.92
- Total: ₱6,093.92

---

### Scenario 4: Plan Upgrade (Users 21-90)
**Expected:**
- 🚀 Plan upgrade required
- 🚫 Cannot add more users on Starter plan
- 📋 Modal shows available upgrade options:
  - Core (21-100 users)
  - Pro (101-200 users)
  - Elite (201-500 users)

**To Test:**
1. Try adding user 21 via UI
2. Verify upgrade modal appears with:
   - Current plan: Starter
   - Current users: 20
   - Trying to add: user 21
   - Available plans with cost breakdown
3. Select Core plan
4. Verify upgrade invoice calculation:
   ```
   Implementation Fee Difference:
   Core impl. fee (₱5,000) - Paid impl. fee (₱2,000) = ₱3,000
   
   Plan Price Difference:
   Core price (₱7,000) - Starter price (₱5,000) = ₱2,000
   
   Subtotal: ₱5,000
   VAT (12%): ₱600
   Total: ₱5,600
   ```
5. Generate upgrade invoice
6. Pay invoice
7. Verify subscription updated:
   - plan_id = Core
   - active_license = 100
   - implementation_fee_paid = 5000
   - amount_paid = 7000
8. Add remaining users (21-90) successfully

**Upgrade Modal Flow:**
```
User 21 → Upgrade required
       → Modal shows available plans
       → User selects Core plan
       → Cost breakdown displayed
       → User clicks "Proceed with Upgrade"
       → Upgrade invoice generated
       → Redirected to /billing
       → User pays invoice
       → Subscription upgraded
       → Can now add up to 100 users
```

---

## Verification Checklist

### After Seeding

- [ ] Check users table: 90 users created
- [ ] Check employment_details: 90 records
- [ ] Check employment_personal_information: 90 records
- [ ] Check employment_government_ids: 90 records
- [ ] Check salary_details: 90 records
- [ ] Check user_permissions: 90 records

### Invoice Verification

- [ ] Implementation fee invoice exists (if user 11 was added)
- [ ] License overage invoices exist (9 invoices for users 12-20)
- [ ] Check invoice amounts are correct
- [ ] Check VAT calculation (12%)
- [ ] Check invoice types are correct

### License Usage Logs

- [ ] Check license_usage_logs table
- [ ] Verify 90 activation records
- [ ] Check is_billable flag (true for users 11-20)
- [ ] Check overage_rate = 49

### Subscription State

- [ ] active_license = base limit (10 for Starter)
- [ ] implementation_fee_paid = 0 (before payment)
- [ ] Check next_renewal_date
- [ ] Check subscription_end date

---

## Commands to Run After Seeding

### 1. Check Active License Count
```bash
# In Tinker
php artisan tinker

$subscription = App\Models\Subscription::where('status', 'active')->first();
$activeCount = App\Models\User::where('tenant_id', $subscription->tenant_id)
    ->where('active_license', true)
    ->count();
echo "Active licenses: {$activeCount}\n";
```

### 2. Generate Renewal Invoice (Simulate 7 days before renewal)
```bash
php artisan invoices:generate
```

### 3. Generate Monthly Overage (For yearly subscriptions)
```bash
php artisan invoices:generate-monthly-overage
```

### 4. Check Invoices
```bash
# In Tinker
php artisan tinker

$invoices = App\Models\Invoice::where('status', 'pending')->get();
foreach ($invoices as $inv) {
    echo "Invoice: {$inv->invoice_number} | Type: {$inv->invoice_type} | Amount: ₱{$inv->amount_due}\n";
}
```

---

## Cleanup (Delete Test Users)

To remove all test users created by this seeder:

```sql
-- Delete test users and related data
DELETE FROM users WHERE email LIKE '%@testbilling.com';
DELETE FROM employment_details WHERE employee_id LIKE 'TEST-%';
DELETE FROM invoices WHERE invoice_number LIKE 'INV-%' AND created_at >= '2025-01-01';
```

Or create a cleanup seeder:

```bash
php artisan make:seeder CleanupBillingTestUsersSeeder
```

---

## Troubleshooting

### Issue: "No tenant found"
**Solution:**
1. Create a tenant first
2. Update `getTenantId()` method in seeder
3. Or hardcode tenant ID for testing

### Issue: "Missing required data (branch, department, etc.)"
**Solution:**
1. Ensure tenant has at least one branch
2. Ensure branch has at least one department
3. Ensure department has at least one designation
4. Create a role for the tenant

### Issue: "Branch contribution settings not configured"
**Solution:**
1. Open branch settings
2. Configure SSS, PhilHealth, Pag-IBIG contribution types
3. Set worked_days_per_year

### Issue: "Duplicate username or email"
**Solution:**
1. Clean up test users first
2. Run seeder again
3. Or modify seeder to generate unique names

### Issue: "No overage invoices created"
**Solution:**
1. Check subscription plan limits
2. Verify LicenseOverageService is working
3. Check license_usage_logs table
4. Review logs: `storage/logs/laravel.log`

---

## Expected Results Summary

| Metric | Expected Value |
|--------|---------------|
| Users Created | 90 |
| Implementation Fee Invoices | 1 (if user 11 added) |
| License Overage Invoices | 9 (users 12-20) |
| Total Overage Amount | ₱441 (9 × ₱49) |
| Implementation Fee | ₱2,000 |
| Users Requiring Upgrade | 70 (users 21-90) |

---

## Next Steps After Seeding

1. **Test Implementation Fee Flow**
   - Try adding user 11
   - Pay implementation fee
   - Verify fee recorded

2. **Test License Overage Flow**
   - Add users 12-20
   - Check overage invoices
   - Test payment flow

3. **Test Plan Upgrade Flow**
   - Try adding user 21
   - Select upgrade plan
   - Pay upgrade invoice
   - Verify subscription updated

4. **Test Consolidated Renewal**
   - Run renewal command
   - Check consolidated invoice
   - Verify overage consolidation

5. **Test Payment Gateway**
   - Use HitPay test credentials
   - Test successful payment
   - Test failed payment

6. **Test Invoice Modal & PDF**
   - Open various invoice types
   - Check column visibility (Qty/Rate dynamic)
   - Download PDFs
   - Verify amounts

---

## Related Documentation

- [Complete Billing System Documentation](./complete-billing-system-documentation.md)
- [Invoice Dynamic Columns Implementation](./invoice-dynamic-columns-implementation.md)
- [Invoice Dynamic Columns Testing Checklist](./invoice-dynamic-columns-testing-checklist.md)
- [Plan Upgrade Invoice Breakdown](./plan-upgrade-invoice-breakdown.md)

---

## Tips for Testing

1. **Start Fresh**: Consider using `migrate:fresh` before seeding
2. **Check Logs**: Monitor `storage/logs/laravel.log` for issues
3. **Use Tinker**: Great for inspecting data during testing
4. **Test Incrementally**: Add users in batches (1-10, then 11, then 12-20)
5. **Document Results**: Keep track of what works and what doesn't

---

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review Laravel logs
3. Check database for data consistency
4. Verify all prerequisites are met
