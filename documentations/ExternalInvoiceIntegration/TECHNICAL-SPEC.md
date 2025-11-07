# External Invoice Integration - Technical Specification

## Document Information

**Document Type:** Technical Specification  
**Version:** 1.0.0  
**Date:** January 15, 2024  
**Status:** Complete  
**Author:** Development Team  

---

## 1. Overview

### 1.1 Purpose
This document provides technical specifications for the external invoice integration endpoint that allows third-party billing systems to create invoices in the Vertex HRMS database.

### 1.2 Scope
- API endpoint implementation
- Request validation and processing
- Database operations and transactions
- Error handling and logging
- Security considerations

### 1.3 Stakeholders
- External billing system developers
- Vertex development team
- QA/Testing team
- DevOps/Infrastructure team

---

## 2. System Architecture

### 2.1 Components

```
External Billing System
        ↓
    [HTTPS POST]
        ↓
   API Endpoint (/api/external/invoice/receive)
        ↓
   InvoiceController::receiveExternalInvoice()
        ↓
    Validation Layer (Laravel Validator)
        ↓
   Database Transaction
        ↓
   Invoice Model (Eloquent ORM)
        ↓
   MySQL Database (invoices table)
```

### 2.2 File Structure

```
app/
  Http/
    Controllers/
      Tenant/
        Billing/
          InvoiceController.php          ← Main controller
  Models/
    Invoice.php                          ← Invoice model
    Tenant.php                           ← Tenant model
routes/
  api.php                                ← Route definition
documentations/
  ExternalInvoiceIntegration/            ← Documentation
    EXTERNAL-INVOICE-API.md
    QUICK-START-GUIDE.md
    README.md
    TECHNICAL-SPEC.md                    ← This file
```

---

## 3. API Endpoint Specification

### 3.1 Endpoint Details

**Route Name:** `api.external.invoice.receive`  
**URL:** `/api/external/invoice/receive`  
**Method:** `POST`  
**Authentication:** None (Consider adding for production)  
**Rate Limiting:** Not implemented (Consider adding)  

### 3.2 Controller Method

**Class:** `App\Http\Controllers\Tenant\Billing\InvoiceController`  
**Method:** `receiveExternalInvoice(Request $request)`  
**Return Type:** `\Illuminate\Http\JsonResponse`  

---

## 4. Data Flow

### 4.1 Request Processing Flow

```
1. Receive HTTP POST request
   ↓
2. Log incoming request (payload, IP, user agent)
   ↓
3. Validate request data against schema
   ↓
4. Check if tenant exists
   ↓
5. Begin database transaction
   ↓
6. Prepare invoice data array
   ↓
7. Create invoice record via Eloquent
   ↓
8. Log external metadata (if provided)
   ↓
9. Commit transaction
   ↓
10. Return success response (201)
```

### 4.2 Error Flow

```
Validation Fails
   ↓
Log warning with errors
   ↓
Return 422 response with error details

Tenant Not Found
   ↓
Log error
   ↓
Return 404 response

Database Error
   ↓
Rollback transaction
   ↓
Log error with stack trace
   ↓
Return 500 response

Unexpected Error
   ↓
Log error with stack trace
   ↓
Return 500 response
```

---

## 5. Database Schema

### 5.1 Invoices Table

**Table Name:** `invoices`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint (PK) | No | AUTO_INCREMENT | Primary key |
| tenant_id | bigint (FK) | No | - | Foreign key to tenants table |
| subscription_id | bigint (FK) | Yes | NULL | Foreign key to subscriptions table |
| upgrade_plan_id | bigint (FK) | Yes | NULL | Foreign key to plans table |
| invoice_type | varchar(50) | No | - | Type: subscription, license_overage, combo, plan_upgrade |
| license_overage_count | int | Yes | NULL | Number of users in overage |
| license_overage_rate | decimal(10,2) | Yes | NULL | Rate per overage user |
| license_overage_amount | decimal(10,2) | Yes | NULL | Total overage amount |
| subscription_amount | decimal(10,2) | Yes | NULL | Base subscription amount |
| invoice_number | varchar(100) | No | - | Unique invoice identifier |
| amount_due | decimal(10,2) | No | - | Total amount due |
| amount_paid | decimal(10,2) | Yes | 0.00 | Amount paid |
| currency | varchar(3) | Yes | PHP | Currency code |
| due_date | date | No | - | Payment due date |
| status | varchar(20) | No | - | pending, paid, overdue, canceled |
| issued_at | datetime | Yes | NULL | Invoice issue timestamp |
| paid_at | datetime | Yes | NULL | Payment timestamp |
| period_start | date | Yes | NULL | Billing period start |
| period_end | date | Yes | NULL | Billing period end |
| consolidated_into_invoice_id | bigint (FK) | Yes | NULL | Parent invoice for consolidation |
| unused_overage_count | int | Yes | NULL | Unused overage user count |
| unused_overage_amount | decimal(10,2) | Yes | NULL | Unused overage credit amount |
| gross_overage_count | int | Yes | NULL | Gross overage count |
| gross_overage_amount | decimal(10,2) | Yes | NULL | Gross overage amount |
| implementation_fee | decimal(10,2) | Yes | NULL | One-time implementation fee |
| vat_amount | decimal(10,2) | Yes | NULL | VAT/tax amount |
| created_at | timestamp | Yes | CURRENT_TIMESTAMP | Record creation timestamp |
| updated_at | timestamp | Yes | CURRENT_TIMESTAMP | Record update timestamp |

### 5.2 Relationships

```php
Invoice belongsTo Tenant (tenant_id)
Invoice belongsTo Subscription (subscription_id)
Invoice belongsTo Plan (upgrade_plan_id, as upgradePlan)
Invoice hasMany PaymentTransaction
Invoice hasOne PaymentTransaction (latestTransaction)
```

---

## 6. Validation Rules

### 6.1 Required Fields

```php
[
    'tenant_id' => 'required|integer|exists:tenants,id',
    'invoice_type' => 'required|string|in:subscription,license_overage,combo,plan_upgrade',
    'invoice_number' => 'required|string|unique:invoices,invoice_number',
    'amount_due' => 'required|numeric|min:0',
    'due_date' => 'required|date',
    'status' => 'required|string|in:pending,paid,overdue,canceled',
]
```

### 6.2 Optional Fields

```php
[
    'subscription_id' => 'nullable|integer',
    'upgrade_plan_id' => 'nullable|integer',
    'license_overage_count' => 'nullable|integer|min:0',
    'license_overage_rate' => 'nullable|numeric|min:0',
    'license_overage_amount' => 'nullable|numeric|min:0',
    'subscription_amount' => 'nullable|numeric|min:0',
    'amount_paid' => 'nullable|numeric|min:0',
    'currency' => 'nullable|string|max:3',
    'issued_at' => 'nullable|date',
    'paid_at' => 'nullable|date',
    'period_start' => 'nullable|date',
    'period_end' => 'nullable|date',
    'consolidated_into_invoice_id' => 'nullable|integer',
    'unused_overage_count' => 'nullable|integer|min:0',
    'unused_overage_amount' => 'nullable|numeric|min:0',
    'gross_overage_count' => 'nullable|integer|min:0',
    'gross_overage_amount' => 'nullable|numeric|min:0',
    'implementation_fee' => 'nullable|numeric|min:0',
    'vat_amount' => 'nullable|numeric|min:0',
    'external_order_id' => 'nullable|string',
    'external_reference' => 'nullable|string',
    'notes' => 'nullable|string',
]
```

### 6.3 Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

---

## 7. Logging Strategy

### 7.1 Info-Level Logs

```php
// Request received
Log::info('External invoice received', [
    'payload' => $request->all(),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

// External metadata
Log::info('External invoice metadata', [
    'invoice_id' => $invoice->id,
    'external_order_id' => $validated['external_order_id'] ?? null,
    'external_reference' => $validated['external_reference'] ?? null,
    'notes' => $validated['notes'] ?? null,
]);

// Success
Log::info('External invoice created successfully', [
    'invoice_id' => $invoice->id,
    'invoice_number' => $invoice->invoice_number,
    'tenant_id' => $invoice->tenant_id,
]);
```

### 7.2 Warning-Level Logs

```php
// Validation failures
Log::warning('External invoice validation failed', [
    'errors' => $validator->errors()->toArray(),
    'payload' => $request->all(),
]);
```

### 7.3 Error-Level Logs

```php
// Tenant not found
Log::error('Tenant not found for external invoice', [
    'tenant_id' => $validated['tenant_id'],
]);

// Database errors
Log::error('Failed to create invoice from external source', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
    'payload' => $validated,
]);

// Unexpected errors
Log::error('External invoice endpoint error', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
]);
```

### 7.4 Log Location

**Path:** `storage/logs/laravel.log`

---

## 8. Transaction Management

### 8.1 Database Transaction Usage

```php
DB::beginTransaction();

try {
    // Create invoice
    $invoice = Invoice::create($invoiceData);
    
    // Log metadata
    // ... additional operations ...
    
    DB::commit();
    
    return success response;
    
} catch (Exception $e) {
    DB::rollBack();
    
    Log::error(...);
    
    return error response;
}
```

### 8.2 Transaction Scope

**Operations within transaction:**
- Invoice creation
- Any related model updates (future expansion)

**Operations outside transaction:**
- Logging (before and after)
- Response generation

---

## 9. Response Formats

### 9.1 Success Response (201 Created)

```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice_id": 123,
    "invoice_number": "INV-2024-001",
    "tenant_id": 1,
    "amount_due": "245.00",
    "status": "pending",
    "created_at": "2024-01-15T10:00:00.000000Z"
  }
}
```

**HTTP Status:** 201 Created

### 9.2 Validation Error (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "tenant_id": ["The tenant id field is required."],
    "invoice_number": ["The invoice number has already been taken."]
  }
}
```

**HTTP Status:** 422 Unprocessable Entity

### 9.3 Not Found Error (404 Not Found)

```json
{
  "success": false,
  "message": "Tenant not found"
}
```

**HTTP Status:** 404 Not Found

### 9.4 Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to create invoice: Database connection error"
}
```

**HTTP Status:** 500 Internal Server Error

---

## 10. Security Considerations

### 10.1 Current Implementation

- ✅ Request validation
- ✅ Database transaction safety
- ✅ SQL injection protection (Eloquent ORM)
- ✅ Comprehensive logging
- ✅ Error message sanitization

### 10.2 Recommended Production Enhancements

#### 10.2.1 API Key Authentication

**Implementation:**
```php
Route::post('/external/invoice/receive', [InvoiceController::class, 'receiveExternalInvoice'])
    ->middleware('api.key.auth')
    ->name('api.external.invoice.receive');
```

**Middleware Example:**
```php
public function handle($request, Closure $next)
{
    $apiKey = $request->header('X-API-Key');
    
    if (!$apiKey || !$this->isValidApiKey($apiKey)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }
    
    return $next($request);
}
```

#### 10.2.2 IP Whitelisting

**Implementation:**
```php
Route::post('/external/invoice/receive', [InvoiceController::class, 'receiveExternalInvoice'])
    ->middleware('ip.whitelist')
    ->name('api.external.invoice.receive');
```

**Allowed IPs:**
```php
protected $allowedIps = [
    '192.168.1.100',
    '10.0.0.50',
    // Add external system IPs
];
```

#### 10.2.3 Rate Limiting

**Implementation:**
```php
Route::post('/external/invoice/receive', [InvoiceController::class, 'receiveExternalInvoice'])
    ->middleware('throttle:100,1') // 100 requests per minute
    ->name('api.external.invoice.receive');
```

#### 10.2.4 HTTPS Enforcement

**Web Server Configuration (Apache):**
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Laravel Middleware:**
```php
if (!$request->secure() && app()->environment('production')) {
    return redirect()->secure($request->getRequestUri());
}
```

---

## 11. Testing

### 11.1 Unit Tests

**File:** `tests/Unit/InvoiceControllerTest.php`

```php
public function test_creates_invoice_with_valid_data()
{
    $data = [
        'tenant_id' => 1,
        'invoice_type' => 'license_overage',
        'invoice_number' => 'TEST-001',
        'amount_due' => 245.00,
        'due_date' => '2024-02-15',
        'status' => 'pending',
    ];
    
    $response = $this->postJson('/api/external/invoice/receive', $data);
    
    $response->assertStatus(201)
             ->assertJsonStructure([
                 'success',
                 'message',
                 'data' => [
                     'invoice_id',
                     'invoice_number',
                     'tenant_id',
                     'amount_due',
                     'status',
                     'created_at'
                 ]
             ]);
    
    $this->assertDatabaseHas('invoices', [
        'invoice_number' => 'TEST-001',
        'tenant_id' => 1,
    ]);
}

public function test_rejects_duplicate_invoice_number()
{
    Invoice::create([...]);
    
    $data = [...]; // Same invoice_number
    
    $response = $this->postJson('/api/external/invoice/receive', $data);
    
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['invoice_number']);
}

public function test_rejects_invalid_tenant()
{
    $data = [
        'tenant_id' => 9999, // Non-existent
        // ... other fields
    ];
    
    $response = $this->postJson('/api/external/invoice/receive', $data);
    
    $response->assertStatus(422);
}
```

### 11.2 Integration Tests

**Test Scenarios:**
1. Create invoice with minimum required fields
2. Create invoice with all fields
3. Validate duplicate invoice number rejection
4. Validate invalid tenant rejection
5. Test all invoice types
6. Test Starter plan with implementation fee
7. Test Core/Pro/Elite overage calculations
8. Test paid invoice creation
9. Test transaction rollback on error

### 11.3 Manual Testing

**Tools:**
- cURL
- Postman
- HTTP clients (PHP/Python/Node.js)

**Test Cases:**
See [QUICK-START-GUIDE.md](./QUICK-START-GUIDE.md) for testing examples.

---

## 12. Performance Considerations

### 12.1 Expected Load

- **Typical:** 10-50 requests per hour
- **Peak:** 100-200 requests per hour
- **Max supported:** 100 requests per minute (with rate limiting)

### 12.2 Optimization Strategies

1. **Database Indexing**
   - Index on `invoice_number` (unique)
   - Index on `tenant_id` (foreign key)
   - Index on `status` (for filtering)
   - Index on `created_at` (for sorting)

2. **Caching**
   - Cache tenant lookups (if high volume)
   - Consider Redis for session/cache storage

3. **Async Processing** (Future Enhancement)
   - Queue invoice creation for very high volumes
   - Use Laravel Queues with Redis/Database driver

---

## 13. Monitoring and Alerts

### 13.1 Metrics to Monitor

- Request count per hour/day
- Success rate (201 responses)
- Error rate (4xx, 5xx responses)
- Response time (p50, p95, p99)
- Database transaction time
- Failed validation attempts

### 13.2 Alert Triggers

- Error rate > 5% over 1 hour
- Response time > 2 seconds (p95)
- Duplicate invoice attempts > 10 per hour
- Invalid tenant attempts > 20 per hour

### 13.3 Logging Analysis

**Query logs for patterns:**
```bash
# Count requests per hour
grep "External invoice received" storage/logs/laravel.log | wc -l

# Find validation errors
grep "External invoice validation failed" storage/logs/laravel.log

# Check success rate
grep "External invoice created successfully" storage/logs/laravel.log | wc -l
```

---

## 14. Deployment

### 14.1 Pre-Deployment Checklist

- [ ] Code review completed
- [ ] Unit tests passing
- [ ] Integration tests passing
- [ ] Documentation reviewed and updated
- [ ] Security review completed
- [ ] Database migrations run (if any)
- [ ] API key authentication configured (production)
- [ ] IP whitelist configured (production)
- [ ] Rate limiting enabled
- [ ] HTTPS enforced
- [ ] Monitoring configured
- [ ] Logging configured

### 14.2 Deployment Steps

1. Pull latest code to staging
2. Run tests in staging environment
3. Verify endpoint accessibility
4. Test with sample requests
5. Deploy to production
6. Verify production endpoint
7. Monitor logs for errors
8. Notify external system team

### 14.3 Rollback Plan

1. Identify issue via monitoring/logs
2. Disable route (comment out in api.php)
3. Deploy previous version
4. Investigate and fix issue
5. Re-test in staging
6. Re-deploy to production

---

## 15. Maintenance

### 15.1 Regular Tasks

**Daily:**
- Monitor error logs
- Check success/failure rates

**Weekly:**
- Review performance metrics
- Check for unusual patterns
- Update documentation if needed

**Monthly:**
- Security audit
- Performance optimization review
- Dependency updates

### 15.2 Database Maintenance

- Regular backups of invoices table
- Archive old invoices (> 2 years)
- Index optimization
- Query performance analysis

---

## 16. Future Enhancements

### 16.1 Planned Features

1. **Webhook Notifications**
   - Notify external system when invoice status changes
   - Webhook retry mechanism

2. **Bulk Invoice Creation**
   - Accept array of invoices in single request
   - Batch processing

3. **Invoice Retrieval API**
   - GET endpoint to retrieve invoice details
   - List invoices with filtering

4. **Invoice Update API**
   - PATCH endpoint to update invoice status
   - Update payment information

5. **Invoice Deletion API**
   - Soft delete invoices
   - Admin-only operation

### 16.2 Technical Improvements

1. **Async Processing**
   - Queue-based invoice creation
   - Background job processing

2. **Enhanced Validation**
   - Custom validation rules
   - Business logic validation

3. **API Versioning**
   - `/api/v1/external/invoice/receive`
   - `/api/v2/external/invoice/receive`

4. **GraphQL Support**
   - Alternative to REST API
   - Flexible data querying

---

## 17. References

### 17.1 Internal Documentation

- [EXTERNAL-INVOICE-API.md](./EXTERNAL-INVOICE-API.md)
- [QUICK-START-GUIDE.md](./QUICK-START-GUIDE.md)
- [README.md](./README.md)
- [../PlanUpgrade/FINAL-DECISION-TREE.md](../PlanUpgrade/FINAL-DECISION-TREE.md)
- [../PlanUpgrade/FINAL-QUICK-REFERENCE.md](../PlanUpgrade/FINAL-QUICK-REFERENCE.md)

### 17.2 External Resources

- Laravel Documentation: https://laravel.com/docs
- REST API Best Practices: https://restfulapi.net/
- JSON Schema Validation: https://json-schema.org/

---

## 18. Appendix

### 18.1 HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET/PUT/PATCH (not used in this endpoint) |
| 201 | Created | Successful invoice creation |
| 400 | Bad Request | Malformed JSON (handled by Laravel) |
| 401 | Unauthorized | Invalid API key (future) |
| 404 | Not Found | Tenant not found |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded (future) |
| 500 | Internal Server Error | Unexpected server error |

### 18.2 Invoice Type Definitions

| Type | Code | Usage |
|------|------|-------|
| Subscription | `subscription` | Regular subscription billing |
| License Overage | `license_overage` | Overage users only |
| Combo | `combo` | Subscription + overage |
| Plan Upgrade | `plan_upgrade` | Plan change billing |

### 18.3 Invoice Status Definitions

| Status | Code | Description |
|--------|------|-------------|
| Pending | `pending` | Invoice created, awaiting payment |
| Paid | `paid` | Invoice fully paid |
| Overdue | `overdue` | Past due date, unpaid |
| Canceled | `canceled` | Invoice canceled |

---

**Document Version:** 1.0.0  
**Last Updated:** January 15, 2024  
**Next Review:** April 15, 2024  
**Maintained By:** Vertex Development Team
