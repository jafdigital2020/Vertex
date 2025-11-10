# Quick Reference Guide

## 📚 Essential Information at a Glance

### Core Billing Constants

```php
// License overage rate
₱49.00 per user per month

// VAT rate  
12% on all charges

// Implementation fee
₱5,000 (Starter plan only)

// Invoice generation timing
7 days before renewal date
```

### Plan Limits Quick Reference

| Plan | Monthly | Yearly | Users | Implementation Fee |
|------|---------|--------|-------|-------------------|
| Starter | ₱1,500 | - | 5 | ₱5,000 |
| Basic | ₱3,000 | - | 15 | Free |
| Pro | ₱7,500 | - | 50 | Free |
| Elite | - | ₱25,000 | 100 | Free |

## 🚀 Quick Actions

### For End Users

#### Check Current Usage
```
Navigation: Billing & Payment → Usage Summary
Shows: Current users / Plan limit
```

#### Pay Outstanding Invoice
```
1. Go to Billing & Payment
2. Find pending invoice
3. Click "Pay Now"
4. Complete payment via HitPay
```

#### Add Employee (Quick Check)
```
1. Employee Management → Add Employee
2. System automatically checks limits
3. Follow prompts for any required payments
4. Employee added upon completion
```

#### Upgrade Plan
```
1. Subscriptions → View Available Plans
2. Select billing cycle (Monthly/Yearly)
3. Choose new plan
4. Pay prorated difference
```

### For Administrators

#### Manual Invoice Generation
```php
// Via Artisan commands
php artisan invoices:generate
php artisan invoices:generate-monthly-overage --dry-run

// Via Tinker
$service = new App\Services\LicenseOverageService();
$invoice = $service->createConsolidatedRenewalInvoice($subscription);
```

#### Check System Health
```sql
-- Active subscriptions
SELECT COUNT(*) FROM subscriptions WHERE status = 'active';

-- Pending invoices  
SELECT COUNT(*), SUM(amount_due) FROM invoices WHERE status = 'pending';

-- License usage vs limits
SELECT 
    s.tenant_id,
    COUNT(u.id) as actual_users,
    s.active_license as recorded,
    p.employee_limit as plan_limit
FROM subscriptions s
JOIN users u ON u.tenant_id = s.tenant_id AND u.active_license = 1  
JOIN plans p ON s.plan_id = p.id
WHERE s.status = 'active'
GROUP BY s.tenant_id;
```

#### Fix License Count Mismatch
```sql
UPDATE subscriptions s 
SET active_license = (
    SELECT COUNT(*) FROM users u 
    WHERE u.tenant_id = s.tenant_id AND u.active_license = 1
)
WHERE s.status = 'active';
```

## 🔧 API Endpoints Quick Reference

### License Management
```http
POST /employees/check-license-overage
POST /employees/generate-implementation-fee-invoice  
POST /employees/generate-plan-upgrade-invoice
```

### Billing
```http
GET  /billing/
POST /billing/payment/initiate/{invoice}
GET  /billing/payment/return/{invoice} 
GET  /billing/payment/success
```

### Subscriptions  
```http
GET  /subscriptions
GET  /subscriptions/available-plans
POST /subscriptions/upgrade
```

### Webhooks
```http
POST /hitpay/webhook
```

## 💾 Database Quick Queries

### Current Subscription Status
```sql
SELECT 
    t.name as company,
    p.name as plan,
    s.billing_cycle,
    s.active_license as users,
    p.employee_limit as limit,
    s.next_renewal_date,
    s.status
FROM subscriptions s
JOIN tenants t ON s.tenant_id = t.id  
JOIN plans p ON s.plan_id = p.id
WHERE s.status = 'active'
ORDER BY s.next_renewal_date;
```

### Revenue Summary
```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    invoice_type,
    COUNT(*) as count,
    SUM(amount_due) as revenue
FROM invoices 
WHERE status = 'paid' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY month, invoice_type
ORDER BY month DESC, invoice_type;
```

### Overage Analysis  
```sql
SELECT 
    t.name,
    COUNT(u.id) as active_users,
    p.employee_limit as plan_limit,
    (COUNT(u.id) - p.employee_limit) as overage,
    ((COUNT(u.id) - p.employee_limit) * 49) as monthly_overage_cost
FROM users u
JOIN tenants t ON u.tenant_id = t.id
JOIN subscriptions s ON s.tenant_id = t.id  
JOIN plans p ON s.plan_id = p.id
WHERE u.active_license = 1 
    AND s.status = 'active'
    AND COUNT(u.id) > p.employee_limit
GROUP BY t.id
ORDER BY overage DESC;
```

## ⚡ Common Scenarios & Solutions

### Scenario: User Can't Add Employee

**Check:**
1. Current license count: `SELECT COUNT(*) FROM users WHERE tenant_id = X AND active_license = 1`
2. Plan limit: `SELECT employee_limit FROM plans p JOIN subscriptions s ON s.plan_id = p.id WHERE s.tenant_id = X`
3. Pending invoices: `SELECT * FROM invoices WHERE tenant_id = X AND status = 'pending'`

**Solutions:**
- Pay pending invoices first
- Accept overage charges (₱49/user)  
- Upgrade to higher plan
- Deactivate unused employees

### Scenario: Payment Failed

**Check:**
1. Invoice status: `SELECT status FROM invoices WHERE id = X`
2. Payment transaction: `SELECT * FROM payment_transactions WHERE invoice_id = X ORDER BY created_at DESC`
3. HitPay logs: `grep -i "webhook\|payment" storage/logs/laravel.log`

**Solutions:**
- Retry payment with different card
- Check card limits/balance
- Contact payment gateway support
- Generate new payment URL if expired

### Scenario: Invoice Not Generated

**Check:**
1. Subscription status: `SELECT * FROM subscriptions WHERE tenant_id = X`
2. Next renewal date: `SELECT next_renewal_date FROM subscriptions WHERE tenant_id = X`  
3. Existing invoices: `SELECT * FROM invoices WHERE subscription_id = X AND invoice_type = 'subscription' ORDER BY created_at DESC`

**Solutions:**
- Wait for scheduled generation (7 days before renewal)
- Run manual generation: `php artisan invoices:generate`
- Check cron job status
- Generate manually via Tinker

### Scenario: Wrong Usage Count

**Check:**
1. Actual count: `SELECT COUNT(*) FROM users WHERE tenant_id = X AND active_license = 1`
2. Recorded count: `SELECT active_license FROM subscriptions WHERE tenant_id = X`
3. Recent activations: `SELECT * FROM license_usage_logs WHERE tenant_id = X ORDER BY created_at DESC LIMIT 10`

**Solutions:**
- Run reconciliation query above
- Check for recent activations/deactivations  
- Verify employee statuses manually
- Update subscription.active_license if needed

## 🎯 Business Logic Quick Reference

### When Overage Invoices Are Generated

**Monthly Plans:**
- Immediately when user added beyond limit
- Included in renewal invoice if accumulated

**Yearly Plans:**  
- Separate monthly overage invoices
- Generated immediately when limit exceeded
- Not included in yearly renewal

### Implementation Fee Logic

**Triggers:**
- First employee addition on Starter plan
- Only if implementation_fee_paid < implementation_fee

**Process:**
1. Check fee status before allowing employee creation
2. Generate implementation fee invoice  
3. User must pay before proceeding
4. Update implementation_fee_paid after payment

### Plan Upgrade Calculations

**Prorated Amount Formula:**
```
Current Plan:
- Days remaining = (renewal_date - today)
- Refund = (monthly_price / 30) × days_remaining

New Plan:  
- New cost = (new_monthly_price / 30) × days_remaining
- Amount due = new_cost - refund
```

## 📞 Emergency Contacts & Procedures

### Payment Gateway Issues
```
HitPay Support: support@hit-pay.com
Check status: https://status.hit-pay.com
Test webhook: Use ngrok for local testing
```

### System Errors
```
Check logs: tail -f storage/logs/laravel.log
Queue issues: php artisan queue:failed
Database locks: SHOW PROCESSLIST;
```

### Data Recovery
```
Backup restoration: [Your backup procedure]
Transaction rollback: Use database transactions
Invoice regeneration: Via LicenseOverageService methods
```

## 🔐 Security Checklist

**Webhook Security:**
- ✅ Verify HitPay signatures
- ✅ Use HTTPS for webhook URLs
- ✅ Rate limit webhook endpoints
- ✅ Log all webhook attempts

**Financial Data:**
- ✅ Validate all amounts before processing
- ✅ Use database transactions for money operations
- ✅ Audit trail for all billing changes
- ✅ Encrypt sensitive payment data

**Access Control:**
- ✅ Billing permissions per user role
- ✅ Two-factor auth for admin functions
- ✅ IP restrictions for sensitive operations
- ✅ Regular access review

## 📊 Monitoring Dashboard Queries

### Daily Health Check
```sql
-- Today's billing activity
SELECT 
    'Invoices Generated' as metric,
    COUNT(*) as count
FROM invoices 
WHERE DATE(created_at) = CURDATE()
UNION ALL
SELECT 
    'Payments Processed',
    COUNT(*)
FROM payment_transactions 
WHERE DATE(created_at) = CURDATE() AND status = 'paid'
UNION ALL  
SELECT 
    'New Subscriptions',
    COUNT(*)
FROM subscriptions
WHERE DATE(created_at) = CURDATE();
```

### Performance Metrics
```sql
-- Average payment processing time
SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, paid_at)) as avg_payment_time_minutes
FROM payment_transactions 
WHERE status = 'paid' AND paid_at IS NOT NULL;

-- Invoice generation success rate  
SELECT 
    (COUNT(CASE WHEN status != 'failed' THEN 1 END) / COUNT(*)) * 100 as success_rate
FROM invoices 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

---

**Last Updated:** November 2024  
**Version:** 1.0
