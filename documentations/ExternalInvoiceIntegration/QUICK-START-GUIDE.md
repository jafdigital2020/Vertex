# Quick Integration Guide - External Invoice API

## 🚀 Quick Start

Get started with the Vertex HRMS External Invoice API in 5 minutes.

---

## 📋 Prerequisites

- Valid `tenant_id` from the Vertex system
- Unique invoice numbering system
- HTTPS-enabled server (for production)

---

## 🔧 Basic Integration Steps

### Step 1: Set Up Your Endpoint URL

**Development:**
```
http://localhost:8000/api/external/invoice/receive
```

**Production:**
```
https://your-vertex-domain.com/api/external/invoice/receive
```

### Step 2: Prepare Your Request

**Required Headers:**
```http
Content-Type: application/json
Accept: application/json
```

**Minimum Required Fields:**
```json
{
  "tenant_id": 1,
  "invoice_type": "license_overage",
  "invoice_number": "INV-2024-001",
  "amount_due": 245.00,
  "due_date": "2024-02-15",
  "status": "pending"
}
```

### Step 3: Send the Request

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/external/invoice/receive \
  -H "Content-Type: application/json" \
  -d '{
    "tenant_id": 1,
    "invoice_type": "license_overage",
    "invoice_number": "INV-2024-001",
    "amount_due": 245.00,
    "due_date": "2024-02-15",
    "status": "pending"
  }'
```

**PHP Example:**
```php
<?php

$data = [
    'tenant_id' => 1,
    'invoice_type' => 'license_overage',
    'invoice_number' => 'INV-2024-001',
    'amount_due' => 245.00,
    'due_date' => '2024-02-15',
    'status' => 'pending'
];

$ch = curl_init('http://localhost:8000/api/external/invoice/receive');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201) {
    echo "Invoice created successfully!\n";
    print_r(json_decode($response, true));
} else {
    echo "Error creating invoice\n";
    print_r(json_decode($response, true));
}
```

**Python Example:**
```python
import requests
import json

url = 'http://localhost:8000/api/external/invoice/receive'
headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}
data = {
    'tenant_id': 1,
    'invoice_type': 'license_overage',
    'invoice_number': 'INV-2024-001',
    'amount_due': 245.00,
    'due_date': '2024-02-15',
    'status': 'pending'
}

response = requests.post(url, headers=headers, json=data)

if response.status_code == 201:
    print('Invoice created successfully!')
    print(json.dumps(response.json(), indent=2))
else:
    print(f'Error: {response.status_code}')
    print(response.json())
```

**Node.js Example:**
```javascript
const axios = require('axios');

const url = 'http://localhost:8000/api/external/invoice/receive';
const data = {
  tenant_id: 1,
  invoice_type: 'license_overage',
  invoice_number: 'INV-2024-001',
  amount_due: 245.00,
  due_date: '2024-02-15',
  status: 'pending'
};

axios.post(url, data, {
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})
.then(response => {
  console.log('Invoice created successfully!');
  console.log(JSON.stringify(response.data, null, 2));
})
.catch(error => {
  console.error('Error creating invoice:');
  if (error.response) {
    console.error(error.response.status);
    console.error(error.response.data);
  } else {
    console.error(error.message);
  }
});
```

---

## 📊 Common Scenarios

### Scenario 1: Starter Plan Customer with 15 Users

**Calculation:**
- Free users (1-10): ₱0
- Overage users (11-15): 5 × ₱49 = ₱245
- Implementation fee: ₱4,999 (one-time)
- **Total: ₱5,244**

**Request:**
```json
{
  "tenant_id": 1,
  "subscription_id": 101,
  "invoice_type": "combo",
  "license_overage_count": 5,
  "license_overage_rate": 49.00,
  "license_overage_amount": 245.00,
  "implementation_fee": 4999.00,
  "subscription_amount": 0.00,
  "invoice_number": "INV-2024-STARTER-001",
  "amount_due": 5244.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31"
}
```

### Scenario 2: Core Plan Customer with 45 Users

**Calculation:**
- All users (21-100): 45 × ₱49 = ₱2,205
- **Total: ₱2,205**

**Request:**
```json
{
  "tenant_id": 2,
  "subscription_id": 102,
  "invoice_type": "license_overage",
  "license_overage_count": 45,
  "license_overage_rate": 49.00,
  "license_overage_amount": 2205.00,
  "subscription_amount": 0.00,
  "invoice_number": "INV-2024-CORE-001",
  "amount_due": 2205.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31"
}
```

### Scenario 3: Pro Plan Customer with 150 Users

**Calculation:**
- All users (101-200): 150 × ₱49 = ₱7,350
- **Total: ₱7,350**

**Request:**
```json
{
  "tenant_id": 3,
  "subscription_id": 103,
  "invoice_type": "license_overage",
  "license_overage_count": 150,
  "license_overage_rate": 49.00,
  "license_overage_amount": 7350.00,
  "subscription_amount": 0.00,
  "invoice_number": "INV-2024-PRO-001",
  "amount_due": 7350.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "pending",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31"
}
```

### Scenario 4: Marking an Invoice as Paid

**Request:**
```json
{
  "tenant_id": 1,
  "invoice_type": "license_overage",
  "license_overage_count": 5,
  "license_overage_rate": 49.00,
  "license_overage_amount": 245.00,
  "invoice_number": "INV-2024-PAID-001",
  "amount_due": 245.00,
  "amount_paid": 245.00,
  "currency": "PHP",
  "due_date": "2024-02-15",
  "status": "paid",
  "issued_at": "2024-01-15T10:00:00Z",
  "paid_at": "2024-01-20T14:30:00Z",
  "period_start": "2024-01-01",
  "period_end": "2024-01-31"
}
```

---

## ✅ Success Response

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

---

## ❌ Common Errors

### 1. Duplicate Invoice Number (422)

**Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "invoice_number": [
      "The invoice number has already been taken."
    ]
  }
}
```

**Solution:** Use a unique invoice number.

### 2. Invalid Tenant (404)

**Error:**
```json
{
  "success": false,
  "message": "Tenant not found"
}
```

**Solution:** Verify the tenant_id exists in the system.

### 3. Missing Required Fields (422)

**Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "tenant_id": ["The tenant id field is required."],
    "invoice_type": ["The invoice type field is required."],
    "amount_due": ["The amount due field is required."]
  }
}
```

**Solution:** Include all required fields in your request.

---

## 🔍 Testing Checklist

- [ ] Test with minimum required fields
- [ ] Test with all fields populated
- [ ] Test with duplicate invoice number (should fail)
- [ ] Test with invalid tenant_id (should fail)
- [ ] Test with negative amounts (should fail)
- [ ] Test with invalid invoice_type (should fail)
- [ ] Test with invalid status (should fail)
- [ ] Test with future dates
- [ ] Test with past dates
- [ ] Verify invoice appears in Vertex system

---

## 📚 Field Reference Quick Guide

| Field | Required? | Example Value |
|-------|-----------|---------------|
| tenant_id | ✅ Yes | `1` |
| subscription_id | ❌ No | `101` |
| upgrade_plan_id | ❌ No | `3` |
| invoice_type | ✅ Yes | `"license_overage"` |
| license_overage_count | ❌ No | `5` |
| license_overage_rate | ❌ No | `49.00` |
| license_overage_amount | ❌ No | `245.00` |
| subscription_amount | ❌ No | `0.00` |
| invoice_number | ✅ Yes | `"INV-2024-001"` |
| amount_due | ✅ Yes | `245.00` |
| amount_paid | ❌ No | `245.00` |
| currency | ❌ No | `"PHP"` |
| due_date | ✅ Yes | `"2024-02-15"` |
| status | ✅ Yes | `"pending"` |
| issued_at | ❌ No | `"2024-01-15T10:00:00Z"` |
| paid_at | ❌ No | `"2024-01-20T14:30:00Z"` |
| period_start | ❌ No | `"2024-01-01"` |
| period_end | ❌ No | `"2024-01-31"` |
| implementation_fee | ❌ No | `4999.00` |
| vat_amount | ❌ No | `0.00` |

---

## 🔐 Security Notes

1. **HTTPS Only:** Use HTTPS in production
2. **API Key:** Consider implementing API key authentication
3. **IP Whitelisting:** Restrict to known IP addresses
4. **Rate Limiting:** Monitor for unusual activity

---

## 📞 Support

For questions or issues:
- **Full API Documentation:** [EXTERNAL-INVOICE-API.md](./EXTERNAL-INVOICE-API.md)
- **Plan Pricing Details:** [FINAL-QUICK-REFERENCE.md](../PlanUpgrade/FINAL-QUICK-REFERENCE.md)
- **Technical Support:** support@vertex.com

---

## 🎯 Next Steps

1. Test the endpoint with sample data
2. Integrate into your billing system
3. Implement error handling and retry logic
4. Set up monitoring and logging
5. Plan for production deployment with security measures

---

**Last Updated:** January 15, 2024  
**API Version:** 1.0.0
