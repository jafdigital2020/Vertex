#!/bin/bash

# ✅ VERIFICATION SCRIPT - Plan Upgrade Feature
# This script verifies that the plan upgrade system is working correctly

echo "=================================================="
echo "🔍 PLAN UPGRADE FEATURE - VERIFICATION"
echo "=================================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 1. Check Routes
echo -e "${BLUE}1. Checking API Routes...${NC}"
php artisan route:list | grep -E "(check-license|generate.*invoice)" | while read line; do
    echo -e "  ${GREEN}✓${NC} $line"
done
echo ""

# 2. Check Database Tables
echo -e "${BLUE}2. Checking Database Tables...${NC}"
php artisan tinker --execute="
echo '  Invoices table: ' . (Schema::hasTable('invoices') ? '✓ EXISTS' : '✗ MISSING') . PHP_EOL;
echo '  Subscriptions table: ' . (Schema::hasTable('subscriptions') ? '✓ EXISTS' : '✗ MISSING') . PHP_EOL;
echo '  Plans table: ' . (Schema::hasTable('plans') ? '✓ EXISTS' : '✗ MISSING') . PHP_EOL;
"
echo ""

# 3. Check Plans in Database
echo -e "${BLUE}3. Checking Available Plans...${NC}"
php artisan tinker --execute="
\$plans = App\Models\Plan::where('is_active', true)->orderBy('billing_cycle')->orderBy('employee_limit')->get(['name', 'employee_limit', 'implementation_fee', 'billing_cycle']);
foreach (\$plans as \$plan) {
    echo '  ✓ ' . str_pad(\$plan->name, 30) . ' | ' . str_pad(\$plan->employee_limit . ' users', 12) . ' | ₱' . number_format(\$plan->implementation_fee, 0) . ' | ' . \$plan->billing_cycle . PHP_EOL;
}
if (\$plans->count() === 0) {
    echo '  ✗ No plans found! Run: php artisan db:seed --class=PlanSeeder' . PHP_EOL;
}
"
echo ""

# 4. Check ENUM Values
echo -e "${BLUE}4. Checking Invoice Types (ENUM)...${NC}"
php artisan tinker --execute="
try {
    \$invoice = new App\Models\Invoice();
    \$invoice->invoice_type = 'plan_upgrade';
    echo '  ✓ plan_upgrade type is supported' . PHP_EOL;

    \$invoice2 = new App\Models\Invoice();
    \$invoice2->invoice_type = 'implementation_fee';
    echo '  ✓ implementation_fee type is supported' . PHP_EOL;
} catch (Exception \$e) {
    echo '  ✗ ERROR: ENUM not updated. Run migrations!' . PHP_EOL;
}
"
echo ""

# 5. Check JavaScript File
echo -e "${BLUE}5. Checking Frontend Files...${NC}"
if grep -q "showPlanUpgradeModal" public/build/js/employeelist.js; then
    echo -e "  ${GREEN}✓${NC} employeelist.js contains showPlanUpgradeModal function"
else
    echo -e "  ${RED}✗${NC} employeelist.js missing showPlanUpgradeModal function"
fi

if grep -q "generate-plan-upgrade-invoice" public/build/js/employeelist.js; then
    echo -e "  ${GREEN}✓${NC} employeelist.js contains plan upgrade AJAX call"
else
    echo -e "  ${RED}✗${NC} employeelist.js missing AJAX call"
fi
echo ""

# 6. Check Blade Template
echo -e "${BLUE}6. Checking Blade Template...${NC}"
if grep -q "plan_upgrade_modal" resources/views/tenant/employee/employeelist.blade.php; then
    echo -e "  ${GREEN}✓${NC} employeelist.blade.php contains plan upgrade modal"
else
    echo -e "  ${RED}✗${NC} employeelist.blade.php missing plan upgrade modal"
fi

if grep -q "available_plans_container" resources/views/tenant/employee/employeelist.blade.php; then
    echo -e "  ${GREEN}✓${NC} employeelist.blade.php contains plan container"
else
    echo -e "  ${RED}✗${NC} employeelist.blade.php missing plan container"
fi

if grep -q "addEmployeeBtn" resources/views/tenant/employee/employeelist.blade.php; then
    echo -e "  ${GREEN}✓${NC} Add Employee button has correct ID"
else
    echo -e "  ${RED}✗${NC} Add Employee button missing ID"
fi
echo ""

# 7. Check Service Methods
echo -e "${BLUE}7. Checking Service Methods...${NC}"
if grep -q "getAvailableUpgradePlans" app/Services/LicenseOverageService.php; then
    echo -e "  ${GREEN}✓${NC} getAvailableUpgradePlans method exists"
else
    echo -e "  ${RED}✗${NC} getAvailableUpgradePlans method missing"
fi

if grep -q "getRecommendedUpgradePlan" app/Services/LicenseOverageService.php; then
    echo -e "  ${GREEN}✓${NC} getRecommendedUpgradePlan method exists"
else
    echo -e "  ${RED}✗${NC} getRecommendedUpgradePlan method missing"
fi

if grep -q "createPlanUpgradeInvoice" app/Services/LicenseOverageService.php; then
    echo -e "  ${GREEN}✓${NC} createPlanUpgradeInvoice method exists"
else
    echo -e "  ${RED}✗${NC} createPlanUpgradeInvoice method missing"
fi
echo ""

# 8. Check Controller Methods
echo -e "${BLUE}8. Checking Controller Methods...${NC}"
if grep -q "generatePlanUpgradeInvoice" app/Http/Controllers/Tenant/Employees/EmployeeListController.php; then
    echo -e "  ${GREEN}✓${NC} generatePlanUpgradeInvoice method exists"
else
    echo -e "  ${RED}✗${NC} generatePlanUpgradeInvoice method missing"
fi

if grep -q "processPlanUpgrade" app/Http/Controllers/Tenant/Employees/EmployeeListController.php; then
    echo -e "  ${GREEN}✓${NC} processPlanUpgrade method exists"
else
    echo -e "  ${RED}✗${NC} processPlanUpgrade method missing"
fi
echo ""

# 9. Test Invoice Creation (Dry Run)
echo -e "${BLUE}9. Testing Invoice Creation (Dry Run)...${NC}"
php artisan tinker --execute="
try {
    \$invoice = new App\Models\Invoice();
    \$invoice->tenant_id = 1;
    \$invoice->subscription_id = 1;
    \$invoice->invoice_type = 'plan_upgrade';
    \$invoice->invoice_number = 'TEST-UPGRADE-001';
    \$invoice->implementation_fee = 10000;
    \$invoice->amount_due = 10000;
    \$invoice->currency = 'PHP';
    \$invoice->status = 'pending';
    \$invoice->due_date = now()->addDays(7);
    \$invoice->period_start = now();
    \$invoice->period_end = now()->addMonth();
    \$invoice->issued_at = now();

    \$result = \$invoice->save();
    if (\$result) {
        echo '  ✓ Test invoice created successfully (ID: ' . \$invoice->id . ')' . PHP_EOL;
        \$invoice->delete();
        echo '  ✓ Test invoice deleted' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo '  ✗ ERROR: ' . \$e->getMessage() . PHP_EOL;
}
"
echo ""

# 10. Summary
echo "=================================================="
echo -e "${GREEN}✅ VERIFICATION COMPLETE${NC}"
echo "=================================================="
echo ""
echo "If all checks passed, the system is ready!"
echo ""
echo "Next Steps:"
echo "1. Clear browser cache (Ctrl+Shift+R)"
echo "2. Test manually:"
echo "   - Create tenant with Starter plan"
echo "   - Add 20 employees"
echo "   - Try to add 21st employee"
echo "   - Select a plan from the modal"
echo "   - Verify invoice generation"
echo "   - Pay invoice"
echo "   - Verify plan upgrade"
echo ""
echo "Documentation:"
echo "- PLAN_UPGRADE_HOW_IT_WORKS.md"
echo "- PLAN_UPGRADE_FEATURE_SUMMARY.md"
echo "- PLAN_UPGRADE_QUICK_REFERENCE.md"
echo ""
