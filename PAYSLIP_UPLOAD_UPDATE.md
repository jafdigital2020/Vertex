# Payslip Upload Feature Update - Summary

## Changes Overview
Updated the payslip upload feature to use **Payroll Month** and **Payroll Year** instead of date fields, and added automatic **13th Month Pay** computation.

---

## 1. Updated CSV Template Columns

### OLD Format (Removed):
- Payroll Period Start
- Payroll Period End  
- Payment Date

### NEW Format (Added):
- **Payroll Month** - Accepts month name (e.g., "January", "Jan") or number (1-12)
- **Payroll Year** - 4-digit year (e.g., 2024)
- **Paid Leave** - Required for 13th month pay calculation

### Current Required Columns:
1. Employee ID (matched to `user->employmentDetail->employee_id`)
2. Payroll Month
3. Payroll Year
4. Net Salary

### Optional Columns:
- Payroll Type
- Basic Pay
- Gross Pay
- Total Earnings
- Total Deductions
- Holiday Pay
- Overtime Pay
- Night Differential Pay
- Paid Leave
- Late Deduction
- Undertime Deduction
- Absent Deduction
- SSS Contribution
- PhilHealth Contribution
- Pag-IBIG Contribution
- Withholding Tax
- Status

---

## 2. 13th Month Pay Auto-Computation

### Formula (from PayrollController):
```php
$thirteenthMonthPay = round(($basicPay + $paidLeave - $lateDeduction - $undertimeDeduction - $absentDeduction) / 12, 2);
```

### Implementation:
- Automatically calculated for each row during import
- Saved to `payrolls.thirteenth_month_pay` column
- Uses data from CSV columns: Basic Pay, Paid Leave, Late Deduction, Undertime Deduction, Absent Deduction

---

## 3. Month Name to Number Conversion

### Supported Formats:
- **Full names**: January, February, March, April, May, June, July, August, September, October, November, December
- **Short names**: Jan, Feb, Mar, Apr, Jun, Jul, Aug, Sep/Sept, Oct, Nov, Dec
- **Numbers**: 1-12
- **Case-insensitive**: "january", "JANUARY", "January" all work

### Implementation Location:
`app/Jobs/ImportPayslipsJob.php` - `parseMonth()` method

---

## 4. Files Modified

### Backend Files:

#### 1. `/app/Jobs/ImportPayslipsJob.php`
**Changes:**
- Removed `parseDate()` method
- Added `parseMonth()` method - converts month names to numbers
- Added `parseYear()` method - validates 4-digit years
- Updated `handle()` method to:
  - Parse Payroll Month and Year instead of dates
  - Calculate 13th month pay for each row
  - Save payroll records with `payroll_month` and `payroll_year`
  - Store `thirteenth_month_pay` value

**Key Code:**
```php
// Parse Payroll Month and Year
$payrollMonth = $this->parseMonth($row['Payroll Month'] ?? null);
$payrollYear = $this->parseYear($row['Payroll Year'] ?? null);

// Calculate 13th month pay
$basicPay = $this->parseAmount($row['Basic Pay'] ?? 0);
$paidLeave = $this->parseAmount($row['Paid Leave'] ?? 0);
$lateDeduction = $this->parseAmount($row['Late Deduction'] ?? 0);
$undertimeDeduction = $this->parseAmount($row['Undertime Deduction'] ?? 0);
$absentDeduction = $this->parseAmount($row['Absent Deduction'] ?? 0);

$thirteenthMonthPay = round(($basicPay + $paidLeave - $lateDeduction - $undertimeDeduction - $absentDeduction) / 12, 2);
```

#### 2. `/app/Http/Controllers/Tenant/Payroll/PayslipController.php`
**Changes:**
- Updated `uploadPayslips()` method validation:
  - Changed required columns from `['Employee ID', 'Payroll Period Start', 'Payroll Period End', 'Payment Date', 'Net Salary']`
  - To: `['Employee ID', 'Payroll Month', 'Payroll Year', 'Net Salary']`
  
- Updated `downloadTemplate()` method:
  - Removed: Payroll Period Start, Payroll Period End, Payment Date
  - Added: Payroll Month, Payroll Year, Paid Leave
  - Updated sample data to show "January" as month example

### Frontend Files:

#### 3. `/resources/views/tenant/payroll/payslip/generated-payslip.blade.php`
**Changes:**
- Updated upload modal instructions to explain:
  - Month can be name or number
  - Year must be 4-digit
  - 13th month pay is auto-calculated
- Updated form text for required columns

---

## 5. Database Schema

### Payroll Table Columns Used:
- `payroll_month` (integer, 1-12)
- `payroll_year` (integer, 4-digit)
- `thirteenth_month_pay` (decimal)
- `basic_pay`, `leave_pay`, `late_deduction`, `undertime_deduction`, `absent_deduction`

**Note:** These columns already exist in the `Payroll` model fillable array.

---

## 6. Usage Instructions

### For Users:
1. Click "Upload Payslip" button in Generated Payslips page
2. Download the CSV template
3. Fill in the data:
   - **Employee ID**: Must match existing employee IDs in system
   - **Payroll Month**: Use month name (e.g., "January") or number (1-12)
   - **Payroll Year**: Use 4-digit year (e.g., 2024)
   - **Basic Pay, Paid Leave, Deductions**: For accurate 13th month calculation
4. Upload the completed CSV
5. System processes in background (queue worker required)
6. View results with success count and any errors

### Sample CSV Row:
```csv
Employee ID,Payroll Type,Payroll Month,Payroll Year,Basic Pay,Gross Pay,Total Earnings,Total Deductions,Net Salary,Holiday Pay,Overtime Pay,Night Differential Pay,Paid Leave,Late Deduction,Undertime Deduction,Absent Deduction,SSS Contribution,PhilHealth Contribution,Pag-IBIG Contribution,Withholding Tax,Status
EMP001,Regular,January,2024,25000.00,28000.00,28000.00,3500.00,24500.00,0.00,1500.00,1500.00,2000.00,0.00,0.00,0.00,1125.00,875.00,500.00,1000.00,Paid
```

---

## 7. Error Handling

### Validation Errors:
- **Invalid Month**: "Invalid Payroll Month or Year"
- **Invalid Year**: "Invalid Payroll Month or Year"
- **Missing Employee**: "Employee not found with this ID"
- **Missing Columns**: "Missing required columns: [list]"

### Error Feedback:
- Errors are displayed in a table showing:
  - Row number
  - Employee ID
  - Error message
- Successful imports are counted separately
- Results cached for 24 hours

---

## 8. Background Processing

### Queue Setup Required:
```bash
php artisan queue:work
```

### Status Check:
- Frontend polls `/api/payroll/check-import-status` every 3 seconds
- Cache key: `payslip_import_result_{processor_id}`
- Results stored for 24 hours

---

## 9. Testing Checklist

- [ ] Download CSV template - verify new columns
- [ ] Upload CSV with month names (e.g., "January", "December")
- [ ] Upload CSV with month numbers (1-12)
- [ ] Upload CSV with mixed case month names
- [ ] Verify 13th month pay is calculated correctly
- [ ] Verify 13th month pay is saved to database
- [ ] Test with invalid month names
- [ ] Test with invalid years
- [ ] Test with non-existent employee IDs
- [ ] Verify error messages are clear
- [ ] Check that successful imports create payroll records with correct month/year
- [ ] Verify queue processing works
- [ ] Check import status feedback

---

## 10. Routes

All routes already exist in `routes/web.php`:
- `POST /payroll/upload-payslips` → `uploadPayslips`
- `GET /payroll/download-template` → `downloadTemplate`
- `GET /api/payroll/check-import-status` → `checkImportStatus`

---

## 11. Key Benefits

1. **Simplified Data Entry**: No need to calculate exact period dates
2. **Automatic Calculations**: 13th month pay computed automatically
3. **Flexible Input**: Month names or numbers accepted
4. **Better Organization**: Payrolls organized by month/year
5. **Error Prevention**: Validation catches invalid months/years
6. **Consistent Formula**: Uses same 13th month calculation as PayrollController

---

## 12. Reference: 13th Month Pay Formula Source

From `PayrollController.php` line 2784:
```php
protected function calculateThirteenthMonthPay(array $userIds, array $data, $salaryData)
{
    // Get basic pay data
    $basicPayData = $this->calculateBasicPay($userIds, $data, $salaryData);
    
    // Get deductions (late, undertime, absent)
    $deductions = $this->calculateDeductions($userIds, $totals, $salaryData);
    
    // Get paid leave
    $leavePayData = $this->calculateLeavePay($userIds, $data, $salaryData);
    
    $result = [];
    foreach ($userIds as $userId) {
        $basicPay = $basicPayData[$userId]['basic_pay'] ?? 0;
        $late = $deductions['lateDeductions'][$userId] ?? 0;
        $undertime = $deductions['undertimeDeductions'][$userId] ?? 0;
        $absent = $deductions['absentDeductions'][$userId] ?? 0;
        $paidLeave = $leavePayData[$userId]['total_leave_pay'] ?? 0;
        
        $thirteenthMonth = round(($basicPay + $paidLeave - $late - $undertime - $absent) / 12, 2);
        
        $result[$userId] = [
            'thirteenth_month' => $thirteenthMonth,
        ];
    }
    
    return $result;
}
```

---

## Migration Notes

### Database Migration Required ✅
A migration was created and run to make the old date fields nullable:

**Migration:** `2025_11_04_155025_make_payroll_date_fields_nullable_on_payrolls_table.php`

**Changes:**
- Made `payroll_period_start` nullable
- Made `payroll_period_end` nullable
- Made `payment_date` nullable

**Reason:** These fields are no longer required for uploaded payslips (which use `payroll_month` and `payroll_year` instead), but existing system-generated payrolls still use them.

**Status:** ✅ Migration has been run successfully

The `payrolls` table already has:
- `payroll_month` column
- `payroll_year` column
- `thirteenth_month_pay` column

These are already in the `Payroll` model's `$fillable` array.

---

## Completion Status: ✅ COMPLETE

All changes have been implemented:
- ✅ Removed date-based columns (Period Start/End, Payment Date)
- ✅ Added Payroll Month and Year columns
- ✅ Implemented month name to number conversion
- ✅ Implemented 13th month pay auto-calculation
- ✅ Updated CSV template
- ✅ Updated validation
- ✅ Updated UI instructions
- ✅ Error handling in place
- ✅ Background job processing configured

**Next Step**: Test the feature end-to-end with sample data!
