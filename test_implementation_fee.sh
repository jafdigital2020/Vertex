#!/bin/bash

# Test Script for Implementation Fee & Plan Upgrade Feature
# Usage: ./test_implementation_fee.sh

echo "========================================"
echo "Testing Implementation Fee Feature"
echo "========================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Check migration status
echo -e "${YELLOW}Test 1: Checking migration status...${NC}"
php artisan migrate:status | grep -i invoice
echo ""

# Test 2: Verify ENUM values
echo -e "${YELLOW}Test 2: Testing invoice creation with new ENUM values...${NC}"
php artisan tinker --execute="
\$invoice = new App\Models\Invoice();
\$invoice->tenant_id = 1;
\$invoice->subscription_id = 1;
\$invoice->invoice_type = 'implementation_fee';
\$invoice->invoice_number = 'TEST-001';
\$invoice->amount_due = 4999.00;
\$invoice->currency = 'PHP';
\$invoice->status = 'pending';
\$invoice->due_date = now()->addDays(7);
\$invoice->issued_at = now();
try {
    echo 'Attempting to create test invoice with implementation_fee type...\n';
    \$result = \$invoice->save();
    if (\$result) {
        echo '✅ SUCCESS: Invoice created with implementation_fee type\n';
        // Clean up test invoice
        \$invoice->delete();
        echo '✅ Test invoice deleted\n';
    }
} catch (Exception \$e) {
    echo '❌ ERROR: ' . \$e->getMessage() . '\n';
}
"
echo ""

# Test 3: Check routes
echo -e "${YELLOW}Test 3: Checking required routes...${NC}"
php artisan route:list | grep -E "(checkLicenseOverage|generateImplementationFeeInvoice)"
echo ""

# Test 4: Verify JavaScript file exists
echo -e "${YELLOW}Test 4: Checking JavaScript file...${NC}"
if [ -f "public/build/js/employeelist.js" ]; then
    echo -e "${GREEN}✅ employeelist.js found${NC}"
    grep -q "checkLicenseBeforeOpeningAddModal" "public/build/js/employeelist.js" && \
        echo -e "${GREEN}✅ checkLicenseBeforeOpeningAddModal function exists${NC}" || \
        echo -e "${RED}❌ checkLicenseBeforeOpeningAddModal function NOT found${NC}"
else
    echo -e "${RED}❌ employeelist.js NOT found${NC}"
fi
echo ""

# Test 5: Verify Blade template has correct button
echo -e "${YELLOW}Test 5: Checking Blade template...${NC}"
if grep -q 'id="addEmployeeBtn"' "resources/views/tenant/employee/employeelist.blade.php"; then
    echo -e "${GREEN}✅ Add Employee button has correct ID${NC}"
else
    echo -e "${RED}❌ Add Employee button missing correct ID${NC}"
fi

if grep -q 'data-bs-toggle.*add_employee' "resources/views/tenant/employee/employeelist.blade.php"; then
    echo -e "${RED}❌ WARNING: Add Employee button still has data-bs-toggle attribute${NC}"
else
    echo -e "${GREEN}✅ Add Employee button does NOT have data-bs-toggle (correct)${NC}"
fi
echo ""

# Test 6: Check if modals exist
echo -e "${YELLOW}Test 6: Checking modals in Blade template...${NC}"
if grep -q 'id="implementation_fee_modal"' "resources/views/tenant/employee/employeelist.blade.php"; then
    echo -e "${GREEN}✅ Implementation fee modal exists${NC}"
else
    echo -e "${RED}❌ Implementation fee modal NOT found${NC}"
fi

if grep -q 'id="plan_upgrade_modal"' "resources/views/tenant/employee/employeelist.blade.php"; then
    echo -e "${GREEN}✅ Plan upgrade modal exists${NC}"
else
    echo -e "${RED}❌ Plan upgrade modal NOT found${NC}"
fi
echo ""

echo "========================================"
echo "Test Summary Complete"
echo "========================================"
echo ""
echo "Next Steps:"
echo "1. Test manually by adding employees through the UI"
echo "2. Verify implementation fee modal appears at user 11"
echo "3. Verify plan upgrade modal appears at user 21"
echo "4. Check that add employee form never shows when blocked"
echo ""
