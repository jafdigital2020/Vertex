# Testing Environment Setup Guide

This guide helps you quickly set up a complete testing environment for the Vertex HR/Payroll system with sample data.

## Quick Setup

### Option 1: Using the Custom Command (Recommended)

```bash
# Fresh setup (destroys existing data)
php artisan setup:testing --fresh

# Seed only (keeps existing data)
php artisan setup:testing
```

### Option 2: Manual Seeding

```bash
# Fresh migrate + seed
php artisan migrate:fresh --seed --seeder=DatabaseTestingSeeder

# Or just seed
php artisan db:seed --class=DatabaseTestingSeeder
```

## What Gets Created

### Central Database (Global)
- **Test Tenant**: Test Company (TEST001)
- **Domain**: test.timora.ph  
- **Global Admin**: admin@test.com / password123
- **Subscription**: Starter Plan (Monthly, $299)
- **Mobile Licenses**: 10 total licenses, 2 used, 8 available

### Tenant Database (Company-Specific)
- **Departments**: HR, IT, Sales & Marketing
- **Branches**: Main Office
- **5 Sample Employees** with complete profiles
- **Roles**: Admin, HR, Employee
- **Mobile Access**: 2 employees with access assigned

## Login Credentials

### Global Admin (Central System)
- **Email**: admin@test.com
- **Password**: password123
- **Company**: Test Company

### Sample Employees (Tenant System)
All employees use password: `password123`

| Username | Email | Role | Department | Mobile Access |
|----------|-------|------|------------|---------------|
| admin.user | admin@testcompany.com | Admin | HR | ❌ |
| john.doe | john.doe@testcompany.com | Employee | IT | ✅ |
| jane.smith | jane.smith@testcompany.com | HR | HR | ✅ |
| mike.johnson | mike.johnson@testcompany.com | Employee | IT | ❌ |
| sarah.wilson | sarah.wilson@testcompany.com | Employee | Sales | ❌ |

## Testing Scenarios

### Mobile Access License Testing
1. **View License Pool**: Check the mobile access dashboard
2. **Assign New License**: Assign to mike.johnson or sarah.wilson
3. **Revoke Access**: Revoke from john.doe or jane.smith
4. **Purchase More**: Test the purchase flow
5. **Filter/Search**: Test table filtering and search

### Multi-Tenant Testing
1. **Domain Access**: Access via test.timora.ph
2. **Tenant Isolation**: Verify data separation
3. **User Roles**: Test different permission levels
4. **Cross-Tenant**: Ensure no data leakage

## Database Structure

```
Central Database:
├── tenants (test-company)
├── domains (test.timora.ph)
├── global_users (admin@test.com)
├── plans (Free, Starter, Pro)
├── subscriptions (Starter Monthly)
└── mobile_access_licenses (10 licenses)

Tenant Database (test-company):
├── users (5 employees)
├── personal_informations
├── employment_details  
├── departments (3 departments)
├── designations (6 positions)
├── branches (1 main office)
└── mobile_access_assignments (2 assignments)
```

## Environment Requirements

Make sure your `.env` has:

```env
DB_CONNECTION=mysql
DB_DATABASE=your_database_name
TENANCY_DB_PREFIX=tenant

# For testing locally
APP_URL=http://localhost:8000
# Or for subdomain testing
APP_URL=https://test.timora.ph
```

## Troubleshooting

### Common Issues

1. **Tenant not found error**
   ```bash
   php artisan tenants:list
   php artisan config:clear
   ```

2. **Permission errors**
   ```bash
   php artisan permission:cache-reset
   ```

3. **Database connection issues**
   ```bash
   php artisan config:clear
   php artisan migrate:status
   ```

4. **Cache issues**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

## Customization

To customize the test data, edit these seeders:
- `TestingSeeder.php` - Central/global data
- `TenantTestingSeeder.php` - Tenant-specific data
- `DatabaseTestingSeeder.php` - Coordination logic

## Production Warning

⚠️ **Never run these testing seeders in production!** They are designed for development and testing environments only.

## Next Steps

After setup:
1. Test the mobile access license functionality
2. Verify DataTables are working correctly
3. Test tenant isolation
4. Validate permission systems
5. Check mobile app integration