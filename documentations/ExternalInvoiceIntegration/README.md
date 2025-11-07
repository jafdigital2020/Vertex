# External Invoice Integration Documentation

This directory contains comprehensive documentation for integrating external billing/payment systems with the Vertex HRMS invoice system.

---

## 📁 Documentation Files

### 1. [EXTERNAL-INVOICE-API.md](./EXTERNAL-INVOICE-API.md)
**Complete API Reference Documentation**

Comprehensive guide covering:
- Endpoint details and authentication
- Request/response formats
- Field validation rules
- Sample requests for all plan types
- Error handling and troubleshooting
- Security recommendations
- Testing procedures

**Use this when:** You need detailed information about the API, field definitions, or handling specific edge cases.

---

### 2. [QUICK-START-GUIDE.md](./QUICK-START-GUIDE.md)
**Quick Integration Guide**

Fast-track guide for developers including:
- 5-minute setup steps
- Code examples (PHP, Python, Node.js, cURL)
- Common scenarios with sample requests
- Quick field reference
- Testing checklist

**Use this when:** You want to get started quickly with working examples.

---

## 🚀 Getting Started

### For Developers
1. Start with [QUICK-START-GUIDE.md](./QUICK-START-GUIDE.md) to get up and running
2. Reference [EXTERNAL-INVOICE-API.md](./EXTERNAL-INVOICE-API.md) for detailed specifications
3. Review plan pricing in [../PlanUpgrade/FINAL-QUICK-REFERENCE.md](../PlanUpgrade/FINAL-QUICK-REFERENCE.md)

### For Project Managers
1. Review the [EXTERNAL-INVOICE-API.md](./EXTERNAL-INVOICE-API.md) Overview section
2. Check security recommendations for production deployment
3. Coordinate with the development team on implementation timeline

---

## 📋 Integration Overview

### Endpoint
```
POST /api/external/invoice/receive
```

### Purpose
Allows external billing/payment systems to create invoices in the Vertex HRMS system by sending structured invoice data via HTTP POST requests.

### Key Features
- ✅ Automatic validation of all invoice data
- ✅ Support for all plan types (Starter, Core, Pro, Elite)
- ✅ Handles overage billing and implementation fees
- ✅ Comprehensive error reporting
- ✅ Transaction logging for debugging
- ✅ Idempotent invoice creation

---

## 💰 Supported Invoice Types

### 1. **License Overage** (`license_overage`)
For billing additional users beyond the base plan (Core, Pro, Elite).

### 2. **Combo** (`combo`)
For Starter plan customers with both base subscription and overage charges.

### 3. **Subscription** (`subscription`)
For regular subscription payments without overage.

### 4. **Plan Upgrade** (`plan_upgrade`)
For billing plan upgrades (e.g., Starter → Core).

---

## 📊 Plan Pricing Summary

| Plan | User Range | Pricing Model | Rate |
|------|------------|---------------|------|
| **Starter** | 1-10 | Free | ₱0 |
| **Starter** | 11-20 | Overage + Implementation Fee | ₱49/user + ₱4,999 |
| **Core** | 21-100 | All users overage | ₱49/user |
| **Pro** | 101-200 | All users overage | ₱49/user |
| **Elite** | 201-500 | All users overage | ₱49/user |

For complete pricing details, see [../PlanUpgrade/FINAL-QUICK-REFERENCE.md](../PlanUpgrade/FINAL-QUICK-REFERENCE.md)

---

## 🔧 Implementation Files

### Controller
```
/app/Http/Controllers/Tenant/Billing/InvoiceController.php
```

Contains the `receiveExternalInvoice()` method that handles incoming requests.

### Route
```
/routes/api.php
```

Route definition:
```php
Route::post('/external/invoice/receive', [InvoiceController::class, 'receiveExternalInvoice'])
    ->name('api.external.invoice.receive');
```

### Model
```
/app/Models/Invoice.php
```

Invoice model with all fillable fields for data mapping.

---

## 📝 Sample Request (Minimal)

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

---

## ✅ Response (Success)

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

## 🔐 Security Considerations

### Production Checklist
- [ ] Enable HTTPS only
- [ ] Implement API key authentication
- [ ] Configure IP whitelisting
- [ ] Set up rate limiting
- [ ] Enable request logging
- [ ] Monitor for suspicious activity
- [ ] Regular security audits

See [EXTERNAL-INVOICE-API.md](./EXTERNAL-INVOICE-API.md) Security Recommendations section for details.

---

## 🧪 Testing

### Test Scenarios
1. ✅ Create invoice with minimum required fields
2. ✅ Create invoice with all fields populated
3. ✅ Handle duplicate invoice numbers
4. ✅ Validate tenant existence
5. ✅ Test invalid field values
6. ✅ Verify Starter plan with implementation fee
7. ✅ Verify Core/Pro/Elite overage calculations
8. ✅ Test paid invoice creation
9. ✅ Test plan upgrade invoices

### Testing Tools
- **cURL:** Command-line testing
- **Postman:** GUI-based API testing
- **PHP/Python/Node.js:** Automated testing scripts

---

## 📚 Related Documentation

### Plan Upgrade System
- [FINAL-DECISION-TREE.md](../PlanUpgrade/FINAL-DECISION-TREE.md) - Complete decision tree and user journeys
- [FINAL-QUICK-REFERENCE.md](../PlanUpgrade/FINAL-QUICK-REFERENCE.md) - One-page quick reference
- [IMPLEMENTATION-STATUS.md](../PlanUpgrade/IMPLEMENTATION-STATUS.md) - Implementation status and testing

### Service Layer
- [/app/Services/LicenseOverageService.php](../../app/Services/LicenseOverageService.php) - Overage calculation logic

---

## 🐛 Troubleshooting

### Common Issues

**Issue:** 422 Validation Error
- **Cause:** Missing required fields or invalid values
- **Solution:** Check error response for specific field issues

**Issue:** 404 Tenant Not Found
- **Cause:** Invalid tenant_id
- **Solution:** Verify tenant exists in the system

**Issue:** 422 Duplicate Invoice Number
- **Cause:** Invoice number already used
- **Solution:** Generate unique invoice numbers

**Issue:** 500 Server Error
- **Cause:** Database or server issue
- **Solution:** Check logs at `storage/logs/laravel.log`

---

## 📞 Support

### Documentation Issues
If you find errors or need clarification in the documentation:
- Create an issue in the project repository
- Contact the documentation team

### Integration Support
For help integrating with the API:
- Email: support@vertex.com
- Technical Support: api-support@vertex.com

### Feature Requests
For new features or enhancements:
- Submit a feature request through the project management system
- Discuss with the product team

---

## 📅 Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2024-01-15 | Initial release with complete API documentation |

---

## 🎯 Quick Links

- **API Endpoint:** `POST /api/external/invoice/receive`
- **Controller:** [InvoiceController.php](../../app/Http/Controllers/Tenant/Billing/InvoiceController.php)
- **Model:** [Invoice.php](../../app/Models/Invoice.php)
- **Routes:** [api.php](../../routes/api.php)

---

**Last Updated:** January 15, 2024  
**Maintained By:** Vertex Development Team  
**Version:** 1.0.0
