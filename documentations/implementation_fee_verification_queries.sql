-- ====================================
-- Implementation Fee Payment Fix
-- Verification & Testing Queries
-- ====================================

-- ====================================
-- 1. CHECK CURRENT STATE
-- ====================================

-- Check subscription implementation fee status
SELECT
    s.id as subscription_id,
    s.tenant_id,
    t.company_name,
    p.name as plan_name,
    p.implementation_fee as plan_impl_fee_required,
    s.implementation_fee_paid as impl_fee_paid,
    (p.implementation_fee - COALESCE(s.implementation_fee_paid, 0)) as impl_fee_remaining,
    CASE
        WHEN s.implementation_fee_paid >= p.implementation_fee THEN '✅ PAID'
        WHEN s.implementation_fee_paid > 0 THEN '⚠️ PARTIAL'
        ELSE '❌ NOT PAID'
    END as payment_status,
    s.status as subscription_status
FROM subscriptions s
JOIN tenants t ON s.tenant_id = t.id
JOIN plans p ON s.plan_id = p.id
WHERE s.status = 'active'
ORDER BY s.tenant_id;

-- ====================================
-- 2. CHECK IMPLEMENTATION FEE INVOICES
-- ====================================

-- List all implementation fee invoices
SELECT
    i.id as invoice_id,
    i.invoice_number,
    i.tenant_id,
    t.company_name,
    i.invoice_type,
    i.implementation_fee,
    i.amount_due,
    i.amount_paid,
    i.status as invoice_status,
    i.created_at as invoice_created,
    i.paid_at,
    s.implementation_fee_paid as subscription_impl_fee_paid,
    CASE
        WHEN i.status = 'paid' AND s.implementation_fee_paid >= i.implementation_fee THEN '✅ SYNCED'
        WHEN i.status = 'paid' AND s.implementation_fee_paid = 0 THEN '❌ NOT SYNCED (BUG)'
        WHEN i.status = 'pending' THEN '⏳ PENDING PAYMENT'
        ELSE '⚠️ CHECK MANUALLY'
    END as sync_status
FROM invoices i
LEFT JOIN tenants t ON i.tenant_id = t.id
LEFT JOIN subscriptions s ON i.subscription_id = s.id
WHERE i.invoice_type = 'implementation_fee'
ORDER BY i.created_at DESC
LIMIT 20;

-- ====================================
-- 3. CHECK ACTIVE USERS VS PLAN LIMITS
-- ====================================

-- Count active users per tenant and check if impl fee is needed
SELECT
    u.tenant_id,
    t.company_name,
    COUNT(*) as active_users,
    p.name as plan_name,
    p.employee_limit,
    s.implementation_fee_paid,
    p.implementation_fee as impl_fee_required,
    CASE
        WHEN COUNT(*) <= 10 THEN '✅ Within base limit (no fee needed)'
        WHEN COUNT(*) <= 20 AND s.implementation_fee_paid >= p.implementation_fee THEN '✅ With overage (impl fee paid)'
        WHEN COUNT(*) <= 20 AND s.implementation_fee_paid = 0 THEN '❌ BLOCKED: Need impl fee (₱2,000)'
        WHEN COUNT(*) > 20 THEN '🚀 BLOCKED: Need plan upgrade'
        ELSE '⚠️ Check manually'
    END as user_status,
    CASE
        WHEN COUNT(*) > 10 AND s.implementation_fee_paid = 0 THEN p.implementation_fee
        ELSE 0
    END as impl_fee_due
FROM users u
JOIN tenants t ON u.tenant_id = t.id
JOIN subscriptions s ON u.tenant_id = s.tenant_id AND s.status = 'active'
JOIN plans p ON s.plan_id = p.id
WHERE u.active_license = true
GROUP BY u.tenant_id, t.company_name, p.name, p.employee_limit, s.implementation_fee_paid, p.implementation_fee
ORDER BY active_users DESC;

-- ====================================
-- 4. FIND BROKEN RECORDS (BUG CASES)
-- ====================================

-- Find paid implementation fee invoices where subscription was NOT updated
SELECT
    i.id as invoice_id,
    i.invoice_number,
    i.tenant_id,
    t.company_name,
    i.implementation_fee as invoice_impl_fee,
    i.status as invoice_status,
    i.paid_at,
    s.implementation_fee_paid as subscription_impl_fee,
    '❌ BUG: Subscription not updated after payment' as issue,
    CONCAT('UPDATE subscriptions SET implementation_fee_paid = ', i.implementation_fee, ' WHERE id = ', s.id, ';') as fix_query
FROM invoices i
JOIN tenants t ON i.tenant_id = t.id
JOIN subscriptions s ON i.subscription_id = s.id
WHERE i.invoice_type = 'implementation_fee'
    AND i.status = 'paid'
    AND (s.implementation_fee_paid = 0 OR s.implementation_fee_paid < i.implementation_fee);

-- ====================================
-- 5. AUDIT TRAIL - CHECK LOGS
-- ====================================

-- This would require checking application logs
-- But we can check payment transactions
SELECT
    pt.id as transaction_id,
    pt.invoice_id,
    i.invoice_number,
    i.invoice_type,
    i.implementation_fee,
    pt.amount,
    pt.status as transaction_status,
    pt.payment_method,
    pt.created_at as transaction_date,
    pt.paid_at,
    s.implementation_fee_paid as subscription_impl_fee_paid_after,
    CASE
        WHEN pt.status = 'paid' AND i.invoice_type = 'implementation_fee' AND s.implementation_fee_paid >= i.implementation_fee THEN '✅ OK'
        WHEN pt.status = 'paid' AND i.invoice_type = 'implementation_fee' AND s.implementation_fee_paid = 0 THEN '❌ BUG'
        ELSE '⚠️ CHECK'
    END as status_check
FROM payment_transactions pt
JOIN invoices i ON pt.invoice_id = i.id
LEFT JOIN subscriptions s ON i.subscription_id = s.id
WHERE i.invoice_type = 'implementation_fee'
ORDER BY pt.created_at DESC
LIMIT 20;

-- ====================================
-- 6. TEST DATA - CHECK TEST USERS
-- ====================================

-- If using BillingTestUsersSeeder, check user 11 (triggers impl fee)
SELECT
    u.id as user_id,
    u.username,
    u.email,
    ed.employee_id,
    CONCAT(epi.first_name, ' ', epi.last_name) as full_name,
    u.active_license,
    u.tenant_id,
    t.company_name
FROM users u
JOIN employment_details ed ON u.id = ed.user_id
JOIN employment_personal_informations epi ON u.id = epi.user_id
JOIN tenants t ON u.tenant_id = t.id
WHERE ed.employee_id = 'TEST-0011';

-- Check implementation fee invoice for test user's tenant
SELECT
    i.id,
    i.invoice_number,
    i.invoice_type,
    i.implementation_fee,
    i.amount_due,
    i.status,
    i.paid_at,
    s.implementation_fee_paid
FROM invoices i
LEFT JOIN subscriptions s ON i.subscription_id = s.id
WHERE i.tenant_id = (
    SELECT u.tenant_id
    FROM users u
    JOIN employment_details ed ON u.id = ed.user_id
    WHERE ed.employee_id = 'TEST-0011'
)
AND i.invoice_type = 'implementation_fee';

-- ====================================
-- 7. MANUAL FIX QUERIES (IF NEEDED)
-- ====================================

-- ⚠️ USE WITH CAUTION - Only for fixing broken records

-- Fix specific subscription (replace X with actual subscription_id)
-- UPDATE subscriptions
-- SET implementation_fee_paid = 2000
-- WHERE id = X;

-- Fix all broken records (where impl fee invoice is paid but subscription not updated)
-- UPDATE subscriptions s
-- JOIN (
--     SELECT DISTINCT
--         i.subscription_id,
--         i.implementation_fee
--     FROM invoices i
--     WHERE i.invoice_type = 'implementation_fee'
--         AND i.status = 'paid'
--         AND i.subscription_id IS NOT NULL
-- ) AS paid_invoices ON s.id = paid_invoices.subscription_id
-- SET s.implementation_fee_paid = paid_invoices.implementation_fee
-- WHERE s.implementation_fee_paid = 0;

-- ====================================
-- 8. VERIFY FIX IS WORKING
-- ====================================

-- After paying an implementation fee invoice, run this:
-- (Replace X with the invoice_id you just paid)

SELECT
    'BEFORE FIX' as check_point,
    i.id as invoice_id,
    i.status as invoice_status,
    i.paid_at,
    i.implementation_fee as invoice_impl_fee,
    s.implementation_fee_paid as subscription_impl_fee_before
FROM invoices i
JOIN subscriptions s ON i.subscription_id = s.id
WHERE i.id = X; -- Replace X with your invoice_id

-- Then make payment

-- Then run this again to verify:
SELECT
    'AFTER FIX' as check_point,
    i.id as invoice_id,
    i.status as invoice_status,
    i.paid_at,
    i.implementation_fee as invoice_impl_fee,
    s.implementation_fee_paid as subscription_impl_fee_after,
    CASE
        WHEN s.implementation_fee_paid >= i.implementation_fee THEN '✅ FIXED - Fee synced'
        ELSE '❌ STILL BROKEN - Check PaymentController'
    END as fix_status
FROM invoices i
JOIN subscriptions s ON i.subscription_id = s.id
WHERE i.id = X; -- Replace X with your invoice_id

-- ====================================
-- 9. SUMMARY REPORT
-- ====================================

-- Overall system health check
SELECT
    'Total Subscriptions' as metric,
    COUNT(*) as count
FROM subscriptions
WHERE status = 'active'

UNION ALL

SELECT
    'Impl Fee Paid',
    COUNT(*)
FROM subscriptions
WHERE status = 'active' AND implementation_fee_paid > 0

UNION ALL

SELECT
    'Impl Fee Not Paid',
    COUNT(*)
FROM subscriptions
WHERE status = 'active' AND implementation_fee_paid = 0

UNION ALL

SELECT
    'Impl Fee Invoices (Paid)',
    COUNT(*)
FROM invoices
WHERE invoice_type = 'implementation_fee' AND status = 'paid'

UNION ALL

SELECT
    'Impl Fee Invoices (Pending)',
    COUNT(*)
FROM invoices
WHERE invoice_type = 'implementation_fee' AND status = 'pending'

UNION ALL

SELECT
    '❌ BROKEN: Paid but not synced',
    COUNT(*)
FROM invoices i
JOIN subscriptions s ON i.subscription_id = s.id
WHERE i.invoice_type = 'implementation_fee'
    AND i.status = 'paid'
    AND s.implementation_fee_paid = 0;

-- ====================================
-- END OF VERIFICATION QUERIES
-- ====================================
