# User Manual: 13th Month Pay Feature

## Table of Contents
1. [Overview](#overview)
2. [What is 13th Month Pay?](#what-is-13th-month-pay)
3. [For Administrators](#for-administrators)
4. [For Employees](#for-employees)
5. [Understanding the Monthly Breakdown](#understanding-the-monthly-breakdown)
6. [Frequently Asked Questions](#frequently-asked-questions)

---

## Overview

The 13th Month Pay feature in Vertex HRMS automates the computation, processing, and distribution of 13th month pay to employees. This mandatory benefit is required by Philippine law (Presidential Decree No. 851) and must be paid to all rank-and-file employees on or before December 24 of every year.

### Key Features
- **Automated Computation**: Automatically calculates 13th month pay based on accumulated payroll data
- **Flexible Period Selection**: Process for any date range across multiple years
- **Detailed Monthly Breakdown**: View month-by-month computation details
- **Analytics Dashboard**: Track total disbursements, employees paid, and payment trends
- **Export Functionality**: Download payslips as PDF for employee records
- **Bulk Operations**: Process multiple employees simultaneously

---

## What is 13th Month Pay?

### Legal Basis
13th Month Pay is mandated by Presidential Decree No. 851, requiring employers to pay all rank-and-file employees a 13th month salary regardless of the nature of their employment.

### Computation Formula
```
13th Month Pay = Total Basic Salary Earned During the Year ÷ 12
```

### What's Included
✅ **Basic Salary**: Your regular monthly salary
✅ **Leave Pay**: Paid vacation and sick leaves

### What's Excluded
❌ Allowances
❌ Overtime pay
❌ Night differential
❌ Holiday pay
❌ Bonuses
❌ Other benefits

### Deductions
The following are deducted from the gross 13th month pay:
- Late deductions
- Undertime deductions
- Absence deductions

---

## For Administrators

### Accessing the Feature

1. Navigate to **Payroll** > **Process Payroll** in the main menu
2. Scroll to the **13th Month Pay Processing** section
3. The section is located below the normal payroll processing area

### Processing 13th Month Pay

#### Step 1: Select Employees
1. Click the **Select Employees** dropdown
2. Choose one or multiple employees from the list
3. You can search by typing the employee name
4. Selected employees will appear with checkmarks

#### Step 2: Define Coverage Period
1. **From Period**:
   - Select the starting month (e.g., January)
   - Select the starting year (e.g., 2024)

2. **To Period**:
   - Select the ending month (e.g., December)
   - Select the ending year (e.g., 2024)

> **Note**: The system validates that the "To" date is after the "From" date. You can process across multiple years (e.g., November 2023 to December 2024).

#### Step 3: Set Payment Date
- Select the date when the 13th month pay will be disbursed
- Default is the current date
- Must be on or before December 24 for annual processing

#### Step 4: Process
1. Review all selected options
2. Click the **"Process 13th Month Pay"** button
3. Wait for the confirmation message
4. The system will:
   - Calculate total basic pay and leave pay for the period
   - Apply deductions (late, undertime, absences)
   - Compute final 13th month pay (Total ÷ 12)
   - Create monthly breakdown records
   - Generate payslip records with "Pending" status

### Viewing Processed Records

#### Process Table View
After processing, records appear in the **13th Month Pay Processed** table showing:
- **Employee**: Full name and ID
- **Branch**: Employee's branch
- **Department**: Employee's department
- **Year**: Processing year
- **Coverage**: From-To period (e.g., "Jan 2024 - Dec 2024")
- **Total Basic Pay**: Sum of basic pay for the period
- **Total Deductions**: Sum of late, undertime, and absence deductions
- **Total 13th Month**: Final computed amount
- **Status**: Processing status (Pending/Released)
- **Actions**: View details, generate payslip, or delete

#### Viewing Monthly Breakdown
1. Click the **"View"** (eye icon) button next to any processed record
2. A modal window opens showing:

   **Employee Information Card**:
   - Full name
   - Employee ID
   - Branch
   - Department
   - Coverage period
   - Payment date

   **Summary Card**:
   - Total Basic Pay
   - Total Deductions
   - Final 13th Month Pay (highlighted in green)

   **Monthly Breakdown Table**:
   - Lists each month in the coverage period
   - Shows: Month & Year, Payroll Count, Basic Pay, Leave Pay, Late, Undertime, Absent, and computed 13th Month Pay for that month
   - **Months are sorted chronologically** (automatically sorted by year first, then by month)
   - **Year is displayed with each month** (e.g., "January 2024", "February 2024") for clarity when spanning multiple years
   - Each month's 13th Month Pay = (Basic Pay + Leave Pay - Late - Undertime - Absent) ÷ 12
   - Multi-year coverage periods work seamlessly (e.g., Nov 2023 to Dec 2024 shows all months in order)

### Generating Payslips

#### Single Payslip Generation
1. Click the **"Generate"** button next to the desired record
2. Status changes from "Pending" to "Released"
3. Employee can now view their payslip

#### Bulk Payslip Generation
1. Select multiple records using checkboxes
2. Click **"Generate Payslip"** in the bulk actions menu
3. All selected records are marked as "Released"

### Managing Records

#### Deleting a Record
1. Click the **"Delete"** (trash icon) button
2. Confirm deletion in the modal
3. Record is permanently removed

#### Bulk Delete
1. Select multiple records using checkboxes
2. Click **"Delete"** in the bulk actions menu
3. Confirm bulk deletion
4. All selected records are removed

### Viewing Released Payslips

1. Navigate to **Payroll** > **Generated Payslips**
2. Click the **"Thirteenth Month Payslips"** tab
3. View the **Analytics Dashboard**:
   - Total 13th Month Pay disbursed
   - Total Basic Pay
   - Total Employees Paid
   - Average per Employee
   - Year-over-Year chart
   - Monthly breakdown trend chart

#### Analytics Features
- **Year Filter**: Select specific year to view analytics
- **Distribution Charts**: Visual representation of payments by year and monthly trends
- **Department Breakdown**: View payments grouped by department (if applicable)

### Payslip Management

#### Reverting a Payslip
1. Find the payslip in the **Thirteenth Month Payslips** view
2. Click the **"Revert"** button
3. Status changes from "Released" back to "Pending"
4. Employee loses access to the payslip

#### Deleting a Payslip
1. Click the **"Delete"** button next to the payslip
2. Confirm deletion
3. Payslip is permanently removed

#### Bulk Operations on Payslips
- **Bulk Revert**: Select multiple payslips and revert them to "Pending"
- **Bulk Delete**: Select multiple payslips and delete them permanently

### Exporting Data

#### Excel Export
1. Click the **"Export to Excel"** button
2. Excel file downloads with:
   - Employee details
   - Coverage periods
   - Computation breakdown
   - Summary totals
   - Professional formatting

#### PDF Export
1. Click the **"Export to PDF"** button
2. PDF file downloads with comprehensive report
3. Suitable for printing and official records

---

## For Employees

### Viewing Your 13th Month Payslips

1. Log in to your employee portal
2. Navigate to **Payroll** > **My Payslips**
3. Click the **"Thirteenth Month Payslips"** tab
4. View all your released 13th month payslips

### Understanding Your Payslip

When you click on a payslip, you'll see:

#### Header Section
- **Company Logo and Details**
- **Payslip Number**: Unique identifier (format: #13MP[ID])
- **Coverage Period**: Date range used for computation
- **Payment Date**: When payment was disbursed
- **Status**: Released status badge

#### Employee Information
- Full Name
- Employee ID
- Branch
- Department
- Designation
- Employment Status

#### Payroll Information
- Bank Account Number (for disbursement)
- TIN (Tax Identification Number)
- SSS Number
- PhilHealth Number
- Pag-IBIG Number

#### Monthly Breakdown Table
Shows detailed computation for each month:
- **Month & Year**: Chronologically sorted
- **Payroll Count**: Number of payrolls processed that month
- **Basic Pay**: Your basic salary for that month
- **Leave Pay**: Paid leave credits used
- **Late**: Deductions for tardiness
- **Undertime**: Deductions for early clock-out
- **Absent**: Deductions for absences
- **13th Month**: Computed amount for that month

#### Summary Cards
Three summary boxes showing:
1. **Total Payroll**: Number of payroll records included
2. **Total Basic Pay**: Accumulated basic salary
3. **Total 13th Month Pay**: Final amount (highlighted in green)

### Downloading Your Payslip

1. Click the **"Download"** button at the top of the payslip
2. PDF file is generated and downloaded
3. Save it for your personal records

### Your Personal Analytics

Your dashboard shows:
- **Total 13th Month Pay Received**: All-time total
- **Total Basic Pay**: Accumulated basic pay used in computation
- **Total Records**: Number of 13th month pay records
- **Average per Record**: Average amount per payment

#### Interactive Charts
- **Year Distribution Chart**: Visual breakdown by year
- **Monthly Trend Chart**: Shows payment trends across months

---

## Understanding the Monthly Breakdown

### Why Monthly Breakdown?

The monthly breakdown provides transparency in how your 13th month pay was calculated. Instead of seeing just a final number, you can verify the computation for each month included in the coverage period.

### How to Read the Breakdown

Each row in the monthly breakdown represents one month:

```
Example Row:
Month: January 2024
Payroll Count: 2
Basic Pay: ₱30,000.00
Leave Pay: ₱2,000.00
Late: ₱500.00
Undertime: ₱200.00
Absent: ₱0.00
13th Month: ₱2,608.33
```

**Computation**:
```
13th Month for January = (Basic Pay + Leave Pay - Late - Undertime - Absent) ÷ 12
                       = (30,000 + 2,000 - 500 - 200 - 0) ÷ 12
                       = 31,300 ÷ 12
                       = ₱2,608.33
```

### Key Points

1. **Chronological Order**: Months are sorted from oldest to newest, automatically handling multi-year periods
2. **Year Display**: Each month shows its year (e.g., "January 2024", "February 2024") to avoid confusion, especially when processing across calendar years
3. **Multi-Year Support**: Coverage periods can span multiple years (e.g., November 2023 to December 2024) and will display correctly in order
4. **Payroll Count**: Shows how many payroll records exist for that month (semi-monthly, weekly, etc.)
5. **Individual Computation**: Each month is computed separately then summed for the total
6. **Automatic Sorting**: The system automatically sorts months by year first, then by month number, ensuring proper chronological display

### Total 13th Month Pay

The **Total 13th Month Pay** (shown in the green summary card) is the sum of all monthly 13th month computations:

```
Total 13th Month Pay = Sum of all monthly 13th month amounts
```

### Verifying Your Payment

To verify your 13th month pay is correct:

1. ✅ Check that all months in your coverage period are listed
2. ✅ Verify the basic pay amounts match your payroll records
3. ✅ Confirm deductions (late, undertime, absent) are accurate
4. ✅ Ensure the year is displayed correctly for each month (important for multi-year periods)
5. ✅ Verify months are in chronological order (oldest to newest)
6. ✅ Add up the monthly 13th month amounts to get the total
7. ✅ For multi-year periods, ensure months transition smoothly (e.g., Dec 2023 → Jan 2024)

---

## Frequently Asked Questions

### General Questions

**Q: When will I receive my 13th month pay?**
A: By law, employers must pay 13th month pay on or before December 24 of each year. Your company may also provide advance payments.

**Q: Am I entitled to 13th month pay if I resigned mid-year?**
A: Yes! You're entitled to 13th month pay proportionate to the months you worked during the year.

**Q: Is 13th month pay taxable?**
A: 13th month pay is tax-exempt up to ₱90,000. Any amount exceeding this is subject to withholding tax.

**Q: What if I was on leave for several months?**
A: Paid leaves are included in the computation. Unpaid leaves may reduce your 13th month pay.

### For Administrators

**Q: Can I process 13th month pay for multiple years?**
A: Yes! Select a "From" date in one year and a "To" date in another year (e.g., Nov 2023 to Dec 2024).

**Q: What happens if an employee has no payroll records?**
A: The system will show a warning and skip that employee. Ensure payroll records exist for the selected period.

**Q: Can I edit a processed 13th month pay?**
A: No direct editing. You must delete the record and reprocess with correct parameters.

**Q: What's the difference between "Pending" and "Released"?**
A: 
- **Pending**: Computed but not yet released to employee
- **Released**: Payslip is generated and visible to employee

**Q: Can I revert a released payslip?**
A: Yes, use the "Revert" button to change status back to "Pending". Employee will lose access.

### For Employees

**Q: Why can't I see my 13th month payslip?**
A: Your administrator must first generate (release) the payslip. Contact HR if you believe it should be available.

**Q: Why is my 13th month pay less than expected?**
A: Check the monthly breakdown for:
- Deductions (late, undertime, absences)
- Months included in the computation
- Whether all your payroll records are captured

**Q: Can I request a specific coverage period?**
A: Coverage periods are set by your administrator. Contact HR if you have concerns.

**Q: What if I find an error in my payslip?**
A: Report immediately to your HR department with:
- Specific month(s) with discrepancies
- Expected vs actual amounts
- Supporting documents (timesheets, payslips)

### Technical Questions

**Q: Why are months sorted chronologically?**
A: To provide clear, logical progression and make verification easier. The system automatically sorts months by year first, then by month, so you can see your earnings from oldest to newest. This is especially important for multi-year periods (e.g., November 2023 to December 2024).

**Q: How does the sorting work for multi-year periods?**
A: When processing across calendar years (e.g., Nov 2023 to Dec 2024), the system automatically sorts by year first, then by month. So you'll see: Nov 2023, Dec 2023, Jan 2024, Feb 2024, etc. in perfect chronological order.

**Q: What does "Payroll Count" mean?**
A: The number of payroll records processed for that month (e.g., 2 for semi-monthly, 4 for weekly).

**Q: How is the monthly 13th month amount calculated?**
A: For each month: (Basic Pay + Leave Pay - Late - Undertime - Absent) ÷ 12

**Q: Why divide by 12?**
A: The 13th month pay is equivalent to 1/12 of your annual basic salary. Each month contributes 1/12 of its qualifying earnings.

---

## Recent Improvements (v2.0)

### Enhanced Monthly Breakdown Display

The monthly breakdown feature has been significantly improved in version 2.0:

#### 1. Automatic Chronological Sorting
- **Previous Behavior**: Months might appear out of order, especially when spanning multiple years
- **New Behavior**: Months are automatically sorted by year first, then by month number
- **Benefit**: Easier to verify calculations and track earnings progression

#### 2. Year Display for Each Month
- **Previous Behavior**: Only month names were shown (e.g., "January", "February")
- **New Behavior**: Full year and month display (e.g., "January 2024", "February 2024")
- **Benefit**: Eliminates confusion when processing multi-year periods

#### 3. Multi-Year Period Support
- **Previous Behavior**: Processing across calendar years might cause display issues
- **New Behavior**: Seamless handling of periods spanning multiple years (e.g., Nov 2023 to Dec 2024)
- **Benefit**: Accurate representation of all months in chronological order

#### Example Display
```
November 2023   ₱30,000.00  ₱2,500.00
December 2023   ₱30,000.00  ₱2,500.00
January 2024    ₱31,000.00  ₱2,583.33
February 2024   ₱31,000.00  ₱2,583.33
...
December 2024   ₱31,000.00  ₱2,583.33
```

### Technical Details

The sorting is performed using JavaScript with the following logic:
1. Parse each month name and year
2. Sort by year (ascending)
3. Then sort by month number (1-12)
4. Maintain stable order for equal values

This ensures that even if data arrives in random order from the database, the display is always chronologically correct.

---

## Support and Contact

### For Assistance
- **Email**: hr@yourcompany.com
- **Phone**: (Your HR Contact Number)
- **HR Office Hours**: Monday-Friday, 8:00 AM - 5:00 PM

### Reporting Issues
When reporting technical issues, provide:
1. Your employee ID
2. Screenshot of the issue
3. Date and time the issue occurred
4. Steps to reproduce the problem

---

## Compliance and Legal Information

This feature is designed to comply with:
- **Presidential Decree No. 851**: 13th Month Pay Law
- **DOLE Labor Advisory No. 03, Series of 2023**: Implementation guidelines
- **Department of Labor and Employment (DOLE)**: Regulations

### Important Reminders
1. 13th month pay must be paid on or before December 24
2. All rank-and-file employees are entitled regardless of employment status
3. Computation must be based on actual basic salary earned
4. Deductions should only include legitimate absences and tardiness
5. Keep all records for audit purposes (minimum 3 years)

---

## Document Information

- **Version**: 2.0
- **Last Updated**: January 2025
- **Applies To**: Vertex HRMS - 13th Month Pay Feature
- **Document Owner**: Vertex HRMS Development Team
- **Recent Updates**:
  - Enhanced monthly breakdown with automatic chronological sorting
  - Added year display for each month
  - Improved multi-year coverage period support
  - Added detailed troubleshooting for sorting and display issues

---

*This manual is subject to updates. Check for the latest version regularly.*
