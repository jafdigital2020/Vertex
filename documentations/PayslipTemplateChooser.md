# Payslip Template Chooser System

## Overview

The payslip template chooser allows users to select from 3 different payslip designs when viewing employee payslips.

## Templates Available

### Template 1 - Modern (Default)

-   Clean, modern card-based design
-   Color-coded sections
-   Bootstrap 5 styled components
-   Ideal for digital viewing

### Template 2 - Classic (Wecall-style)

-   Traditional payslip format
-   Table-based layout
-   Black borders and structured sections
-   Mimics printed payslip format
-   Based on Wecall design requirements

### Template 3 - Minimal

-   Modern colorful gradient design
-   Icon-based sections
-   Highly visual presentation
-   Great for executive-level reports

## How to Use

### For Users

1. Open any payslip view
2. Click the "Template" dropdown button in the top right
3. Select from Template 1, 2, or 3
4. The payslip will reload with the selected template

### For Developers

#### File Structure

```
resources/views/tenant/payroll/payslip/userpayslip/
├── payslipview.blade.php (main view with template selector)
├── templates/
│   ├── template1.blade.php (Modern design)
│   ├── template2.blade.php (Classic/Wecall design)
│   ├── template3.blade.php (Minimal design)
│   └── partials/
│       ├── earnings.blade.php
│       ├── deductions.blade.php
│       └── time-tracking.blade.php
```

#### Database Field

-   Table: `payrolls`
-   Column: `payslip_template` (string, default: 'template1')

#### Adding a New Template

1. Create a new template file in `resources/views/tenant/payroll/payslip/userpayslip/templates/`
2. Follow the same data structure as existing templates
3. Add the template option to the dropdown in `payslipview.blade.php`
4. Use template partials where possible for consistency

#### Template Variables Available

All templates have access to the `$payslips` object which contains:

-   Employee information
-   Branch details
-   Earnings breakdown
-   Deductions breakdown
-   Time tracking data
-   Payment information
-   Dates and status

## Installation

### 1. Run Migration

```bash
php artisan migrate --path=database/migrations/2025_12_12_141733_add_payslip_template_to_payrolls_table.php
```

### 2. Clear Cache

```bash
php artisan view:clear
php artisan config:clear
```

## Customization

### Changing Default Template

Update the migration file default value:

```php
$table->string('payslip_template')->default('template2'); // Change to template2 or template3
```

### Saving Template Preference

To save user's template preference, you can:

1. Add a user preference field
2. Update the controller to save the selection
3. Load the saved preference when viewing payslips

## Technical Notes

-   Templates use Blade inheritance for consistency
-   PDF download functionality works with all templates
-   Templates are mobile-responsive
-   All templates use the same data source
-   Template switching is client-side for better performance

## Support

For issues or feature requests, contact the development team.
