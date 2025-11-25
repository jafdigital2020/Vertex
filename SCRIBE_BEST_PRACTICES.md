# 📚 Scribe API Documentation - Best Practices Guide

## ✅ Best Practices Implemented

### 1. **Detailed Descriptions**
```php
/**
 * Short title (appears in sidebar)
 *
 * Detailed description explaining what the endpoint does,
 * what it returns, and any important business logic.
 * Multiple lines are fine.
 *
 * @group GroupName
 * @authenticated
 */
```

### 2. **Complete Parameter Documentation**

#### Query Parameters (GET requests)
```php
/**
 * @queryParam branch_id integer Optional. Filter by branch ID. Example: 1
 * @queryParam status string Optional. Filter by status (0=Inactive, 1=Active). Example: 1
 */
```

#### Body Parameters (POST/PUT requests)
```php
/**
 * @bodyParam first_name string required Employee's first name. Example: Juan
 * @bodyParam email string required Valid email address. Must be unique. Example: juan@company.com
 * @bodyParam age integer Optional. Age in years (18-100). Example: 25
 */
```

**Format:**
```
@bodyParam {name} {type} {required|optional} {Description with validation rules}. Example: {value}
```

**Types:**
- `string` - Text
- `integer` - Whole numbers
- `number` - Decimals
- `boolean` - true/false
- `date` - Date format (YYYY-MM-DD)
- `file` - File upload
- `array` - Array of items
- `object` - JSON object

### 3. **Multiple Response Scenarios**

Always document **all possible responses**:

```php
/**
 * @response 200 scenario="Success" {
 *   "status": "success",
 *   "message": "Operation completed successfully.",
 *   "data": {...}
 * }
 *
 * @response 400 scenario="Bad Request" {
 *   "status": "error",
 *   "message": "Invalid input provided."
 * }
 *
 * @response 401 scenario="Unauthenticated" {
 *   "message": "Unauthenticated."
 * }
 *
 * @response 403 scenario="Insufficient Permissions" {
 *   "status": "error",
 *   "message": "You do not have permission to perform this action."
 * }
 *
 * @response 404 scenario="Not Found" {
 *   "message": "Resource not found."
 * }
 *
 * @response 422 scenario="Validation Error" {
 *   "message": "The email has already been taken.",
 *   "errors": {
 *     "email": ["The email has already been taken."]
 *   }
 * }
 *
 * @response 500 scenario="Server Error" {
 *   "message": "An error occurred.",
 *   "error": "Database connection failed"
 * }
 */
```

### 4. **Grouping Endpoints**

Use `@group` to organize related endpoints:

```php
/**
 * @group Employees
 * @group Attendance
 * @group Payroll
 * @group Leave Management
 */
```

### 5. **Authentication**

Mark authenticated endpoints:
```php
/**
 * @authenticated
 */
```

For unauthenticated endpoints (public), omit this tag.

---

## 📝 Documentation Template

Use this template for new endpoints:

```php
/**
 * [Short descriptive title]
 *
 * [Detailed description of what this endpoint does.
 * Include business logic, side effects, and important notes.]
 *
 * @group [Group Name]
 * @authenticated
 *
 * @urlParam id integer required The ID of the resource. Example: 1
 *
 * @queryParam filter string Optional. Filter results by criteria. Example: active
 * @queryParam page integer Optional. Page number for pagination. Example: 1
 * @queryParam per_page integer Optional. Items per page (max 100). Example: 20
 *
 * @bodyParam name string required The name field. Example: John Doe
 * @bodyParam email string required Valid email address. Must be unique. Example: john@example.com
 * @bodyParam age integer Optional. Age in years (18-100). Example: 25
 * @bodyParam status boolean Optional. Active status. Defaults to true. Example: true
 *
 * @response 200 scenario="Success" {
 *   "status": "success",
 *   "message": "Operation completed successfully.",
 *   "data": {
 *     "id": 1,
 *     "name": "John Doe",
 *     "email": "john@example.com"
 *   }
 * }
 *
 * @response 401 scenario="Unauthenticated" {
 *   "message": "Unauthenticated."
 * }
 *
 * @response 403 scenario="Forbidden" {
 *   "status": "error",
 *   "message": "You do not have permission."
 * }
 *
 * @response 404 scenario="Not Found" {
 *   "message": "Resource not found."
 * }
 *
 * @response 422 scenario="Validation Error" {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "email": ["The email has already been taken."],
 *     "age": ["The age must be at least 18."]
 *   }
 * }
 *
 * @response 500 scenario="Server Error" {
 *   "message": "An error occurred.",
 *   "error": "Error details"
 * }
 */
public function methodName(Request $request)
{
    // Implementation
}
```

---

## 🚀 Quick Commands

### Generate Documentation
```bash
# Standard generation
php artisan scribe:generate

# With more memory (recommended)
php -d memory_limit=512M artisan scribe:generate

# Apply CSS fix to hide Endpoints tab
./fix-scribe-docs.sh
```

### Workflow (One-liner)
```bash
php artisan route:clear && php -d memory_limit=512M artisan scribe:generate && ./fix-scribe-docs.sh
```

### Important Note About Visibility
Only endpoints with a `@group` annotation will be visible in the documentation. All endpoints without `@group` (or with the default "Endpoints" group) will be automatically hidden by CSS. This ensures only properly documented endpoints are shown to API consumers.

---

## 📊 Common HTTP Status Codes

| Code | Meaning | When to Use |
|------|---------|-------------|
| 200 | OK | Successful GET, PUT, PATCH |
| 201 | Created | Successful POST (resource created) |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | Authenticated but no permission |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server-side error |

---

## 🎯 Pro Tips

### 1. Use Realistic Examples
```php
// ❌ Bad
@bodyParam name string required Name. Example: test

// ✅ Good
@bodyParam name string required Employee's full name. Example: Juan Dela Cruz
```

### 2. Describe Validation Rules
```php
// ❌ Bad
@bodyParam email string required Email.

// ✅ Good
@bodyParam email string required Valid email address. Must be unique in the system. Example: juan@company.com
```

### 3. Document Business Logic
```php
/**
 * Create employee
 *
 * Creates a new employee record. This endpoint performs license validation
 * and may generate overage invoices if your plan's employee limit is exceeded.
 * Implementation fees must be paid before adding the first employee.
 */
```

### 4. Include Possible Enum Values
```php
/**
 * @bodyParam status string required Employment status. Must be one of: "Active", "On Leave", "Resigned", "Terminated". Example: Active
 * @bodyParam employment_type string required Type of employment. Options: "Regular", "Contractual", "Probationary", "Part-Time". Example: Regular
 */
```

### 5. Document Related IDs
```php
/**
 * @bodyParam branch_id integer required Branch where employee will be assigned. Must be a valid branch ID. Example: 1
 * @bodyParam role_id integer required Role/Permission template ID. See /api/roles for available roles. Example: 2
 */
```

---

## 🔧 Configuration Files

### Main Config
- **Location**: `config/scribe.php`
- **Key Settings**:
  - `title` - Documentation title
  - `description` - API description
  - `base_url` - API base URL
  - `type` - Documentation type (`laravel` or `static`)
  - `try_it_out.enabled` - Enable/disable API testing

### Custom CSS (Auto-applied)
- **Location**: `fix-scribe-docs.sh`
- Hides "Endpoints" section automatically after generation

---

## 📖 Additional Resources

- Scribe Official Docs: https://scribe.knuckles.wtf/laravel
- Scribe Config Reference: https://scribe.knuckles.wtf/laravel/reference/config
- HTTP Status Codes: https://httpstatuses.com/

---

## 💡 Need Help?

Check improved examples in:
- `app/Http/Controllers/Tenant/Employees/EmployeeListController.php`
  - `employeeListIndex()` - GET with query params
  - `employeeAdd()` - POST with comprehensive validation
  - `employeeEdit()` - PUT with optional params
  - `employeeDelete()` - DELETE endpoint

---

**Happy Documenting!** 🎉
