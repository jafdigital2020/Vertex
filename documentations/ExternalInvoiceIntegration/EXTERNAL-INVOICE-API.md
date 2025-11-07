# External Invoice Integration API Documentation

## Overview

This document provides comprehensive information for external billing/payment systems to integrate with the Vertex HRMS invoice system. The API endpoint allows external systems to submit invoice/order data that will be automatically mapped to the internal Invoice model.

---

## Endpoint Information

### POST: Create Invoice from External Source

**URL:** `/api/external/invoice/receive`

**Method:** `POST`

**Content-Type:** `application/json`

**Authentication:** Currently none (Consider implementing API key/token authentication for production)

---

## Request Structure

### Headers

```http
Content-Type: application/json
Accept: application/json
```

### Request Body Schema

All fields are described below with their validation rules:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `tenant_id` | integer | **Yes** | Must exist in tenants table | The ID of the tenant this invoice belongs to |
| `subscription_id` | integer | No | - | The subscription ID if applicable |
| `upgrade_plan_id` | integer | No | - | The new plan ID for plan upgrades |
| `invoice_type` | string | **Yes** | One of: `subscription`, `license_overage`, `combo`, `plan_upgrade` | Type of invoice being created |
| `license_overage_count` | integer | No | Min: 0 | Number of users in overage |
| `license_overage_rate` | decimal | No | Min: 0 | Rate per overage user (e.g., 49.00) |
| `license_overage_amount` | decimal | No | Min: 0 | Total amount for license overage |
| `subscription_amount` | decimal | No | Min: 0 | Base subscription amount |
| `invoice_number` | string | **Yes** | Must be unique | Unique invoice number (e.g., INV-2024-001) |
| `amount_due` | decimal | **Yes** | Min: 0 | Total amount due for this invoice |
| `amount_paid` | decimal | No | Min: 0 | Amount already paid (if any) |
| `currency` | string | No | Max: 3 chars | Currency code (default: PHP) |
| `due_date` | date | **Yes** | Valid date format | Invoice due date (YYYY-MM-DD) |
| `status` | string | **Yes** | One of: `pending`, `paid`, `overdue`, `canceled` | Current invoice status |
| `issued_at` | datetime | No | Valid datetime | When invoice was issued (default: now) |
| `paid_at` | datetime | No | Valid datetime | When invoice was paid (if paid) |
| `period_start` | date | No | Valid date | Billing period start date |
| `period_end` | date | No | Valid date | Billing period end date |
| `consolidated_into_invoice_id` | integer | No | - | ID of parent invoice if consolidated |
| `unused_overage_count` | integer | No | Min: 0 | Count of unused overage credits |
| `unused_overage_amount` | decimal | No | Min: 0 | Amount of unused overage credits |
| `gross_overage_count` | integer | No | Min: 0 | Total gross overage count |
| `gross_overage_amount` | decimal | No | Min: 0 | Total gross overage amount |
| `implementation_fee` | decimal | No | Min: 0 | One-time implementation fee (₱4,999 for Starter 11-20) |
| `vat_amount` | decimal | No | Min: 0 | VAT/tax amount |
| `external_order_id` | string | No | - | External system's order ID (logged, not stored in DB) |
| `external_reference` | string | No | - | External reference number (logged, not stored in DB) |
| `notes` | string | No | - | Additional notes (logged, not stored in DB) |

---

## Sample Requests

### 1. License Overage Invoice (Starter Plan: 11-20 Users)

This example shows a Starter plan customer with 15 users (5 in overage range with implementation fee):

```json
{
  "tenant_id": 1,
  "subscription_id": 101,
  "invoice_type": "combo",
  "license_overage_count": 5,
  "license_overage_rate": 49.00,
  "license_overage_amount": 245.00,
  "subscription_amount": 0.00,
  "implementation_fee": 4999.00,
  "invoice_number": "INV-2024-001-STARTER",
  "amount_due": 5244.00,
  "amount_paid": 0.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "issued_at": "2024-01-15T10:00:00Z",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31",
  "vat_amount": 0.00,
  "external_order_id": "EXT-ORD-12345",
  "external_reference": "PAYMENT-REF-67890",
  "notes": "First overage invoice for Starter plan customer"
}
```

**Calculation:**
- Base (1-10 users): ₱0 (free)
- Overage (11-15 users): 5 × ₱49 = ₱245
- Implementation fee: ₱4,999 (one-time)
- **Total: ₱5,244**

---

### 2. License Overage Invoice (Core Plan: 21-100 Users)

Core plan customer with 45 users (all 45 are overage):

```json
{
  "tenant_id": 2,
  "subscription_id": 102,
  "invoice_type": "license_overage",
  "license_overage_count": 45,
  "license_overage_rate": 49.00,
  "license_overage_amount": 2205.00,
  "subscription_amount": 0.00,
  "invoice_number": "INV-2024-002-CORE",
  "amount_due": 2205.00,
  "amount_paid": 0.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "issued_at": "2024-01-15T10:00:00Z",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31",
  "vat_amount": 0.00,
  "external_order_id": "EXT-ORD-12346"
}
```

**Calculation:**
- All users (21-100): 45 × ₱49 = ₱2,205
- No implementation fee for Core
- **Total: ₱2,205**

---

### 3. License Overage Invoice (Pro Plan: 101-200 Users)

Pro plan customer with 150 users (all 150 are overage):

```json
{
  "tenant_id": 3,
  "subscription_id": 103,
  "invoice_type": "license_overage",
  "license_overage_count": 150,
  "license_overage_rate": 49.00,
  "license_overage_amount": 7350.00,
  "subscription_amount": 0.00,
  "invoice_number": "INV-2024-003-PRO",
  "amount_due": 7350.00,
  "amount_paid": 0.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "issued_at": "2024-01-15T10:00:00Z",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31",
  "vat_amount": 0.00,
  "external_order_id": "EXT-ORD-12347"
}
```

**Calculation:**
- All users (101-200): 150 × ₱49 = ₱7,350
- **Total: ₱7,350**

---

### 4. License Overage Invoice (Elite Plan: 201-500 Users)

Elite plan customer with 350 users (all 350 are overage):

```json
{
  "tenant_id": 4,
  "subscription_id": 104,
  "invoice_type": "license_overage",
  "license_overage_count": 350,
  "license_overage_rate": 49.00,
  "license_overage_amount": 17150.00,
  "subscription_amount": 0.00,
  "invoice_number": "INV-2024-004-ELITE",
  "amount_due": 17150.00,
  "amount_paid": 0.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "issued_at": "2024-01-15T10:00:00Z",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31",
  "vat_amount": 0.00,
  "external_order_id": "EXT-ORD-12348"
}
```

**Calculation:**
- All users (201-500): 350 × ₱49 = ₱17,150
- **Total: ₱17,150**

---

### 5. Paid Invoice Example

Example of an invoice that has been paid:

```json
{
  "tenant_id": 1,
  "subscription_id": 101,
  "invoice_type": "license_overage",
  "license_overage_count": 5,
  "license_overage_rate": 49.00,
  "license_overage_amount": 245.00,
  "subscription_amount": 0.00,
  "invoice_number": "INV-2024-005-PAID",
  "amount_due": 245.00,
  "amount_paid": 245.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "paid",
  "issued_at": "2024-01-15T10:00:00Z",
  "paid_at": "2024-01-20T14:30:00Z",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31",
  "vat_amount": 0.00,
  "external_order_id": "EXT-ORD-12349"
}
```

---

### 6. Plan Upgrade Invoice

Example of a plan upgrade invoice:

```json
{
  "tenant_id": 5,
  "subscription_id": 105,
  "upgrade_plan_id": 3,
  "invoice_type": "plan_upgrade",
  "subscription_amount": 5000.00,
  "invoice_number": "INV-2024-006-UPGRADE",
  "amount_due": 5000.00,
  "amount_paid": 0.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "issued_at": "2024-01-15T10:00:00Z",
  "period_start": "2024-01-15",
  "period_end": "2024-02-15",
  "vat_amount": 0.00,
  "external_order_id": "EXT-ORD-12350",
  "notes": "Upgrade from Starter to Core plan"
}
```

---

## Response Format

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice_id": 123,
    "invoice_number": "INV-2024-001-STARTER",
    "tenant_id": 1,
    "amount_due": "5244.00",
    "status": "pending",
    "created_at": "2024-01-15T10:00:00.000000Z"
  }
}
```

### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "tenant_id": [
      "The tenant id field is required."
    ],
    "invoice_number": [
      "The invoice number has already been taken."
    ],
    "amount_due": [
      "The amount due must be at least 0."
    ]
  }
}
```

### Tenant Not Found (404 Not Found)

```json
{
  "success": false,
  "message": "Tenant not found"
}
```

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to create invoice: Database connection error"
}
```

---

## Invoice Types Explained

### 1. `subscription`
Regular subscription invoice without overage charges.

### 2. `license_overage`
Invoice containing only license overage charges (used for Core, Pro, Elite plans).

### 3. `combo`
Invoice containing both subscription and overage charges (typically used for Starter plan with overage).

### 4. `plan_upgrade`
Invoice for upgrading from one plan to another.

---

## Plan-Specific Pricing Rules

### Starter Plan (1-20 Users)
- **1-10 users:** Free (no charge)
- **11-20 users:** ₱49/user overage + ₱4,999 one-time implementation fee
- **21+ users:** Must upgrade to Core

**Example Invoice Fields:**
```json
{
  "invoice_type": "combo",
  "license_overage_count": 5,
  "license_overage_rate": 49.00,
  "license_overage_amount": 245.00,
  "implementation_fee": 4999.00,
  "subscription_amount": 0.00,
  "amount_due": 5244.00
}
```

### Core Plan (21-100 Users)
- **All users (21-100):** ₱49/user (all are overage)
- **101+ users:** Must upgrade to Pro

**Example Invoice Fields:**
```json
{
  "invoice_type": "license_overage",
  "license_overage_count": 45,
  "license_overage_rate": 49.00,
  "license_overage_amount": 2205.00,
  "subscription_amount": 0.00,
  "amount_due": 2205.00
}
```

### Pro Plan (101-200 Users)
- **All users (101-200):** ₱49/user (all are overage)
- **201+ users:** Must upgrade to Elite

**Example Invoice Fields:**
```json
{
  "invoice_type": "license_overage",
  "license_overage_count": 150,
  "license_overage_rate": 49.00,
  "license_overage_amount": 7350.00,
  "subscription_amount": 0.00,
  "amount_due": 7350.00
}
```

### Elite Plan (201-500 Users)
- **All users (201-500):** ₱49/user (all are overage)
- **501+ users:** Contact sales

**Example Invoice Fields:**
```json
{
  "invoice_type": "license_overage",
  "license_overage_count": 350,
  "license_overage_rate": 49.00,
  "license_overage_amount": 17150.00,
  "subscription_amount": 0.00,
  "amount_due": 17150.00
}
```

---

## Implementation Notes

### 1. **Unique Invoice Numbers**
Ensure invoice numbers are unique across the system. The API will reject duplicate invoice numbers with a 422 error.

### 2. **Tenant Validation**
Always verify the tenant exists before sending the request. Invalid tenant IDs will result in a 404 error.

### 3. **Date Formats**
- Dates: `YYYY-MM-DD` (e.g., `2024-01-15`)
- Datetimes: `YYYY-MM-DDTHH:MM:SSZ` (e.g., `2024-01-15T10:00:00Z`)

### 4. **Decimal Precision**
Monetary values should be sent with 2 decimal places (e.g., `49.00`, not `49`).

### 5. **Status Management**
Valid statuses:
- `pending` - Invoice created but not paid
- `paid` - Invoice has been paid
- `overdue` - Invoice is past due date
- `canceled` - Invoice has been canceled

### 6. **Logging**
All requests are logged with:
- Full payload
- IP address
- User agent
- Timestamp

### 7. **External Metadata**
The fields `external_order_id`, `external_reference`, and `notes` are logged but not stored in the database. Use these for cross-referencing with your external system.

---

## Error Handling Best Practices

### 1. **Retry Logic**
Implement exponential backoff for failed requests:
```
Attempt 1: Immediate
Attempt 2: 5 seconds
Attempt 3: 30 seconds
Attempt 4: 2 minutes
Attempt 5: 10 minutes
```

### 2. **Idempotency**
Use unique invoice numbers to prevent duplicate invoice creation. If a request fails after the invoice is created, retrying with the same invoice number will result in a validation error.

### 3. **Validation Errors**
Handle 422 errors by checking the `errors` object and correcting the data before retrying.

### 4. **Connection Timeouts**
Set a reasonable timeout (30-60 seconds) for API calls.

---

## Testing the Integration

### Using cURL

```bash
curl -X POST https://your-vertex-domain.com/api/external/invoice/receive \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tenant_id": 1,
    "invoice_type": "license_overage",
    "license_overage_count": 5,
    "license_overage_rate": 49.00,
    "license_overage_amount": 245.00,
    "subscription_amount": 0.00,
    "invoice_number": "TEST-INV-001",
    "amount_due": 245.00,
    "currency": "PHP",
    "due_date": "2024-02-15",
    "status": "pending",
    "issued_at": "2024-01-15T10:00:00Z",
    "external_order_id": "TEST-ORD-001"
  }'
```

### Using Postman

1. Set method to `POST`
2. URL: `https://your-vertex-domain.com/api/external/invoice/receive`
3. Headers:
   - `Content-Type: application/json`
   - `Accept: application/json`
4. Body: Select `raw` and `JSON`, then paste one of the sample requests above
5. Click `Send`

---

## Security Recommendations

### 1. **API Key Authentication** (Recommended for Production)

Consider implementing API key authentication by adding middleware:

```php
Route::post('/external/invoice/receive', [InvoiceController::class, 'receiveExternalInvoice'])
    ->middleware('api.key.validation')
    ->name('api.external.invoice.receive');
```

Pass API key in headers:
```http
X-API-Key: your-secret-api-key-here
```

### 2. **IP Whitelisting**

Restrict access to known external server IP addresses.

### 3. **HTTPS Only**

Ensure the endpoint is only accessible via HTTPS in production.

### 4. **Rate Limiting**

Implement rate limiting to prevent abuse (e.g., 100 requests per minute).

---

## Support and Contact

For integration support or questions, please contact:
- **Technical Support:** support@vertex.com
- **API Issues:** api-support@vertex.com
- **Documentation Updates:** Check the latest version at `/documentations/ExternalInvoiceIntegration/`

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2024-01-15 | Initial API documentation |

---

## Related Documentation

- [Plan Upgrade Decision Tree](../PlanUpgrade/FINAL-DECISION-TREE.md)
- [Quick Reference Guide](../PlanUpgrade/FINAL-QUICK-REFERENCE.md)
- [Implementation Status](../PlanUpgrade/IMPLEMENTATION-STATUS.md)
