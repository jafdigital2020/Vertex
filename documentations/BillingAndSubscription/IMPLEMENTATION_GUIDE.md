# Technical Implementation Guide

## Overview

This guide provides detailed technical instructions for implementing, maintaining, and troubleshooting the Vertex billing and subscription system.

## 🛠 Prerequisites

### Server Requirements
- PHP 8.1+
- Laravel 10.x
- MySQL 8.0+
- Redis (for queues)
- Cron job support

### External Services
- HitPay Payment Gateway account
- SMTP server for notifications
- SSL certificate for webhook security

## 📦 Installation & Setup

### 1. Environment Configuration

Create/update your `.env` file:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vertex
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Payment Gateway (HitPay)
HITPAY_API_KEY=your_sandbox_or_live_api_key
HITPAY_SALT=your_salt_key
HITPAY_BASE_URL=https://api.sandbox.hit-pay.com
# For production: https://api.hit-pay.com

# Billing Settings
OVERAGE_RATE_PER_LICENSE=49.00
DEFAULT_VAT_PERCENTAGE=12.00
STARTER_IMPLEMENTATION_FEE=5000.00

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@vertex.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Database Setup

Run migrations to create billing tables:

```bash
# Create billing-related tables
php artisan migrate

# Seed initial plans
php artisan db:seed --class=PlansSeeder
```

### 3. Service Registration

Ensure the `LicenseOverageService` is properly registered in your service container:

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->singleton(LicenseOverageService::class, function ($app) {
        return new LicenseOverageService();
    });
}
```

### 4. Queue Configuration

Set up queue workers for background processing:

```bash
# Start queue worker
php artisan queue:work --daemon

# For production (using supervisor)
sudo supervisorctl start laravel-worker:*
```

### 5. Scheduled Commands

Add to your crontab:

```bash
# Edit crontab
crontab -e

# Add these lines:
# Generate renewal invoices (daily at midnight)
0 0 * * * cd /path/to/vertex && php artisan invoices:generate >> /dev/null 2>&1

# Generate monthly overage invoices (daily at 2 AM)
0 2 * * * cd /path/to/vertex && php artisan invoices:generate-monthly-overage >> /dev/null 2>&1

# Laravel scheduler (runs every minute)
* * * * * cd /path/to/vertex && php artisan schedule:run >> /dev/null 2>&1
```

## 🔧 Configuration

### Plan Configuration

Create/update plans in the database:

```php
// Database seeder or manual insert
use App\Models\Plan;

$plans = [
    [
        'name' => 'Starter Monthly',
        'description' => 'Perfect for small teams',
        'price' => 1500.00,
        'currency' => 'PHP',
        'billing_cycle' => 'monthly',
        'employee_minimum' => 1,
        'employee_limit' => 5,
        'implementation_fee' => 5000.00,
        'vat_percentage' => 12.00,
        'is_active' => true
    ],
    [
        'name' => 'Basic Monthly',
        'description' => 'Great for growing companies',
        'price' => 3000.00,
        'currency' => 'PHP',
        'billing_cycle' => 'monthly',
        'employee_minimum' => 6,
        'employee_limit' => 15,
        'implementation_fee' => 0.00,
        'vat_percentage' => 12.00,
        'is_active' => true
    ],
    [
        'name' => 'Pro Monthly',
        'description' => 'Ideal for medium businesses',
        'price' => 7500.00,
        'currency' => 'PHP',
        'billing_cycle' => 'monthly',
        'employee_minimum' => 16,
        'employee_limit' => 50,
        'implementation_fee' => 0.00,
        'vat_percentage' => 12.00,
        'is_active' => true
    ],
    [
        'name' => 'Elite Yearly',
        'description' => 'Best value for large enterprises',
        'price' => 25000.00,
        'currency' => 'PHP',
        'billing_cycle' => 'yearly',
        'employee_minimum' => 51,
        'employee_limit' => 100,
        'implementation_fee' => 0.00,
        'vat_percentage' => 12.00,
        'is_active' => true
    ]
];

foreach ($plans as $plan) {
    Plan::create($plan);
}
```

### HitPay Webhook Setup

Configure webhook in HitPay dashboard:

```
Webhook URL: https://yourdomain.com/hitpay/webhook
Events: payment.completed, payment.failed
```

## 🚀 API Integration

### Employee License Checking

Integrate license checking in your employee management:

```javascript
// Frontend integration
function checkLicenseBeforeEmployeeAction(action, formData) {
    $.ajax({
        url: '/employees/check-license-overage',
        method: 'POST',
        data: { action: action },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            handleLicenseCheckResponse(response, formData);
        },
        error: function(xhr) {
            console.error('License check failed:', xhr.responseText);
            alert('Unable to verify license status. Please try again.');
        }
    });
}

function handleLicenseCheckResponse(response, formData) {
    switch(response.status) {
        case 'ok':
            // Proceed with employee creation
            submitEmployeeForm(formData);
            break;
            
        case 'implementation_fee_required':
            showImplementationFeeModal(response.data, formData);
            break;
            
        case 'upgrade_suggested':
            showPlanUpgradeModal(response.data, formData);
            break;
            
        case 'overage_confirmation':
            showOverageModal(response.data, formData);
            break;
            
        case 'contact_sales':
            showContactSalesModal(response.data);
            break;
            
        default:
            console.error('Unknown license check status:', response.status);
    }
}
```

### Backend Controller Integration

```php
// app/Http/Controllers/Tenant/Employees/EmployeeListController.php

public function checkLicenseOverage(Request $request)
{
    try {
        $authUser = $this->authUser();
        $newUserCount = $request->input('new_user_count', 1);
        
        $result = $this->licenseOverageService->checkLicenseBeforeAdding(
            $authUser->tenant_id, 
            $newUserCount
        );
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        Log::error('License overage check failed', [
            'tenant_id' => $authUser->tenant_id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Unable to check license status'
        ], 500);
    }
}

public function employeeAdd(Request $request)
{
    // Validate request
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        // ... other validation rules
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();
        
        // Create user
        $user = User::create([
            'tenant_id' => $this->authUser()->tenant_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'active_license' => true,
            // ... other fields
        ]);
        
        // Handle license activation (may create invoices)
        $invoice = $this->licenseOverageService->handleEmployeeActivation($user->id);
        
        DB::commit();
        
        $response = [
            'success' => true,
            'message' => 'Employee added successfully',
            'user_id' => $user->id
        ];
        
        if ($invoice) {
            $response['invoice_generated'] = true;
            $response['invoice_id'] = $invoice->id;
            $response['invoice_amount'] = $invoice->amount_due;
        }
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Employee creation failed', [
            'tenant_id' => $this->authUser()->tenant_id,
            'email' => $request->email,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to create employee'
        ], 500);
    }
}
```

## 📊 Monitoring & Logging

### Database Performance Monitoring

```sql
-- Monitor invoice generation performance
SELECT 
    DATE(created_at) as date,
    invoice_type,
    COUNT(*) as count,
    AVG(amount_due) as avg_amount,
    SUM(amount_due) as total_amount
FROM invoices 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at), invoice_type
ORDER BY date DESC;

-- Check subscription health
SELECT 
    s.billing_cycle,
    s.status,
    COUNT(*) as subscription_count,
    AVG(s.active_license) as avg_licenses,
    SUM(CASE WHEN s.active_license > p.employee_limit THEN 1 ELSE 0 END) as overage_count
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
GROUP BY s.billing_cycle, s.status;

-- License usage analysis
SELECT 
    t.name as tenant_name,
    COUNT(u.id) as active_users,
    s.active_license as recorded_licenses,
    p.employee_limit as plan_limit,
    (COUNT(u.id) - p.employee_limit) as current_overage
FROM users u
JOIN tenants t ON u.tenant_id = t.id
JOIN subscriptions s ON s.tenant_id = t.id
JOIN plans p ON s.plan_id = p.id
WHERE u.active_license = 1 AND s.status = 'active'
GROUP BY t.id, s.id, p.id
HAVING current_overage > 0;
```

### Application Logging

```php
// Add to LicenseOverageService methods
Log::info('Invoice generation started', [
    'tenant_id' => $tenantId,
    'invoice_type' => $invoiceType,
    'amount' => $amount,
    'trigger' => $trigger
]);

// Monitor critical operations
Log::critical('License limit exceeded significantly', [
    'tenant_id' => $tenantId,
    'current_users' => $currentUsers,
    'plan_limit' => $planLimit,
    'overage_amount' => $overageAmount
]);

// Track payment processing
Log::info('Payment webhook received', [
    'invoice_id' => $invoiceId,
    'payment_status' => $status,
    'amount' => $amount,
    'reference' => $reference
]);
```

### Queue Monitoring

```bash
# Monitor queue status
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## 🔐 Security Considerations

### Webhook Security

Verify HitPay webhook signatures:

```php
// app/Http/Controllers/Tenant/Billing/PaymentController.php
public function webhook(Request $request)
{
    // Verify webhook signature
    $providedSignature = $request->header('X-HITPAY-Signature');
    $payload = $request->getContent();
    $expectedSignature = hash_hmac('sha256', $payload, env('HITPAY_SALT'));
    
    if (!hash_equals($expectedSignature, $providedSignature)) {
        Log::warning('Invalid webhook signature', [
            'provided' => $providedSignature,
            'expected' => $expectedSignature,
            'ip' => $request->ip()
        ]);
        
        return response()->json(['error' => 'Invalid signature'], 403);
    }
    
    // Process webhook...
}
```

### Input Validation

```php
// Validate all financial calculations
private function validateInvoiceAmount($amount)
{
    if (!is_numeric($amount) || $amount < 0) {
        throw new InvalidArgumentException('Invalid invoice amount');
    }
    
    if ($amount > 1000000) { // 1M PHP maximum
        throw new InvalidArgumentException('Invoice amount exceeds maximum');
    }
    
    return round($amount, 2);
}

// Validate user counts
private function validateUserCount($count)
{
    if (!is_int($count) || $count < 0 || $count > 10000) {
        throw new InvalidArgumentException('Invalid user count');
    }
    
    return $count;
}
```

### Rate Limiting

```php
// app/Http/Kernel.php - Add to routeMiddleware
'billing.throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':10,1',

// Apply to billing routes
Route::middleware(['auth', 'billing.throttle'])->group(function () {
    Route::post('/employees/check-license-overage', [EmployeeListController::class, 'checkLicenseOverage']);
    Route::post('/billing/payment/initiate/{invoice}', [PaymentController::class, 'initiatePayment']);
});
```

## 🧪 Testing

### Unit Tests

```php
// tests/Unit/Services/LicenseOverageServiceTest.php
class LicenseOverageServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $service;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->service = new LicenseOverageService();
    }
    
    public function test_calculates_overage_correctly()
    {
        // Create test tenant, subscription, and users
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create(['employee_limit' => 5]);
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id
        ]);
        
        User::factory()->count(7)->create([
            'tenant_id' => $tenant->id,
            'active_license' => true
        ]);
        
        // Test overage calculation
        $result = $this->service->checkLicenseBeforeAdding($tenant->id, 1);
        
        $this->assertEquals('overage_confirmation', $result['status']);
        $this->assertEquals(3, $result['data']['current_overage']); // 7 - 5 + 1
    }
    
    public function test_implementation_fee_required_for_starter()
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create([
            'name' => 'Starter Monthly',
            'implementation_fee' => 5000.00
        ]);
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'implementation_fee_paid' => 0
        ]);
        
        $result = $this->service->checkLicenseBeforeAdding($tenant->id, 1);
        
        $this->assertEquals('implementation_fee_required', $result['status']);
        $this->assertEquals(5000.00, $result['data']['amount_due']);
    }
}
```

### Feature Tests

```php
// tests/Feature/BillingTest.php
class BillingTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_employee_addition_triggers_overage_invoice()
    {
        // Setup test environment
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create subscription at limit
        $subscription = Subscription::factory()->create([
            'tenant_id' => $user->tenant_id,
            'active_license' => 5
        ]);
        
        User::factory()->count(5)->create([
            'tenant_id' => $user->tenant_id,
            'active_license' => true
        ]);
        
        // Attempt to add employee
        $response = $this->post('/employee-add', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com'
        ]);
        
        // Verify overage invoice created
        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $user->tenant_id,
            'invoice_type' => 'license_overage',
            'license_overage_count' => 1
        ]);
    }
}
```

### Load Testing

```bash
# Install artillery for load testing
npm install -g artillery

# Create load test config
cat > billing-load-test.yml << EOF
config:
  target: 'https://your-domain.com'
  phases:
    - duration: 60
      arrivalRate: 10
  headers:
    Authorization: 'Bearer YOUR_API_TOKEN'

scenarios:
  - name: "License check load test"
    requests:
      - post:
          url: "/employees/check-license-overage"
          json:
            new_user_count: 1
EOF

# Run load test
artillery run billing-load-test.yml
```

## 🚨 Troubleshooting

### Common Issues

#### Invoice Generation Failures

```php
// Debug invoice generation
php artisan tinker

// Check subscription status
$subscription = App\Models\Subscription::find(1);
dd($subscription->toArray());

// Test invoice creation
$service = new App\Services\LicenseOverageService();
$result = $service->checkAndCreateOverageInvoice(1); // tenant_id
dd($result);

// Check for existing invoices
App\Models\Invoice::where('tenant_id', 1)
    ->where('status', 'pending')
    ->get();
```

#### Payment Webhook Issues

```bash
# Check webhook logs
grep -i "webhook" storage/logs/laravel.log | tail -20

# Test webhook locally with ngrok
ngrok http 8000
# Update HitPay webhook URL to ngrok URL

# Test webhook manually
curl -X POST http://localhost:8000/hitpay/webhook \
  -H "Content-Type: application/json" \
  -H "X-HITPAY-Signature: test_signature" \
  -d '{"test": "data"}'
```

#### License Count Mismatches

```sql
-- Fix license count discrepancies
UPDATE subscriptions s 
SET active_license = (
    SELECT COUNT(*) 
    FROM users u 
    WHERE u.tenant_id = s.tenant_id 
    AND u.active_license = 1
)
WHERE s.status = 'active';

-- Verify counts
SELECT 
    s.tenant_id,
    s.active_license as recorded_count,
    COUNT(u.id) as actual_count,
    (COUNT(u.id) - s.active_license) as difference
FROM subscriptions s
LEFT JOIN users u ON u.tenant_id = s.tenant_id AND u.active_license = 1
WHERE s.status = 'active'
GROUP BY s.tenant_id, s.active_license
HAVING difference != 0;
```

### Performance Issues

#### Slow Invoice Queries

```sql
-- Add indexes for better performance
CREATE INDEX idx_invoices_tenant_status ON invoices(tenant_id, status);
CREATE INDEX idx_invoices_period ON invoices(period_start, period_end);
CREATE INDEX idx_users_tenant_license ON users(tenant_id, active_license);
CREATE INDEX idx_subscriptions_renewal ON subscriptions(next_renewal_date, status);
```

#### Memory Issues with Large Datasets

```php
// Use chunking for large operations
User::where('tenant_id', $tenantId)
    ->where('active_license', true)
    ->chunk(100, function ($users) {
        foreach ($users as $user) {
            // Process user
        }
    });

// Use database aggregation instead of collection methods
$activeCount = User::where('tenant_id', $tenantId)
    ->where('active_license', true)
    ->count();
```

### Emergency Procedures

#### Manual Invoice Generation

```php
// Generate missing renewal invoice
php artisan tinker

$subscription = App\Models\Subscription::find($id);
$service = new App\Services\LicenseOverageService();
$invoice = $service->createConsolidatedRenewalInvoice($subscription);
echo "Invoice created: " . $invoice->invoice_number;
```

#### Reset Failed Payments

```php
// Reset failed payment status
$invoice = App\Models\Invoice::where('invoice_number', 'INV-2025-001')->first();
$invoice->update(['status' => 'pending']);

// Recreate payment transaction
$payment = App\Models\PaymentTransaction::create([
    'invoice_id' => $invoice->id,
    'subscription_id' => $invoice->subscription_id,
    'transaction_reference' => 'MANUAL_' . time(),
    'amount' => $invoice->total_amount,
    'currency' => 'PHP',
    'status' => 'pending'
]);
```

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks

```bash
# Weekly tasks
php artisan queue:restart
php artisan config:cache
php artisan route:cache

# Monthly tasks
php artisan telescope:prune --hours=720  # If using Telescope
php artisan horizon:snapshot  # If using Horizon

# Database maintenance
OPTIMIZE TABLE invoices, subscriptions, users, license_usage_logs;
```

### Monitoring Dashboard Queries

```sql
-- Daily billing summary
SELECT 
    DATE(created_at) as date,
    COUNT(*) as invoice_count,
    SUM(amount_due) as total_amount,
    AVG(amount_due) as avg_amount
FROM invoices 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Active subscriptions by plan
SELECT 
    p.name as plan_name,
    p.billing_cycle,
    COUNT(s.id) as active_subscriptions,
    SUM(s.active_license) as total_licenses
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
WHERE s.status = 'active'
GROUP BY p.id, p.name, p.billing_cycle;
```

Last Updated: November 2024  
Version: 1.0
