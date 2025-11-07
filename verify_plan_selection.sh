#!/bin/bash

# Plan Selection Feature Verification Script
# This script tests the plan selection and upgrade feature

echo "========================================="
echo "Plan Selection Feature Verification"
echo "========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Check if plans exist
echo "Test 1: Checking if plans exist in database..."
php artisan tinker --execute="
    \$plans = App\Models\Plan::where('is_active', true)->get(['id', 'name', 'billing_cycle', 'employee_limit', 'implementation_fee']);
    if (\$plans->isEmpty()) {
        echo '❌ No active plans found in database!\n';
    } else {
        echo '✅ Found ' . \$plans->count() . ' active plans:\n';
        foreach (\$plans as \$plan) {
            echo '  - ' . \$plan->name . ' (' . \$plan->billing_cycle . '): ' . \$plan->employee_limit . ' users, ₱' . number_format(\$plan->implementation_fee, 2) . '\n';
        }
    }
"

echo ""

# Test 2: Check plan hierarchy
echo "Test 2: Checking plan hierarchy..."
php artisan tinker --execute="
    \$monthly = App\Models\Plan::where('billing_cycle', 'monthly')->where('is_active', true)->orderBy('employee_limit', 'asc')->get(['name', 'employee_limit']);
    \$yearly = App\Models\Plan::where('billing_cycle', 'yearly')->where('is_active', true)->orderBy('employee_limit', 'asc')->get(['name', 'employee_limit']);

    echo '✅ Monthly Plans Hierarchy:\n';
    foreach (\$monthly as \$plan) {
        echo '  ' . \$plan->name . ' (' . \$plan->employee_limit . ' users)\n';
    }

    echo '\n✅ Yearly Plans Hierarchy:\n';
    foreach (\$yearly as \$plan) {
        echo '  ' . \$plan->name . ' (' . \$plan->employee_limit . ' users)\n';
    }
"

echo ""

# Test 3: Test getAvailableUpgradePlans
echo "Test 3: Testing getAvailableUpgradePlans method..."
php artisan tinker --execute="
    use App\Services\LicenseOverageService;

    \$service = new LicenseOverageService();

    // Find a Starter subscription to test with
    \$subscription = App\Models\Subscription::with('plan')
        ->whereHas('plan', function(\$q) {
            \$q->where('name', 'like', '%Starter%');
        })
        ->where('status', 'active')
        ->first();

    if (!\$subscription) {
        echo '❌ No Starter subscription found to test with\n';
    } else {
        echo '✅ Testing with subscription ID: ' . \$subscription->id . '\n';
        echo '   Current plan: ' . \$subscription->plan->name . ' (' . \$subscription->plan->employee_limit . ' users)\n\n';

        \$availablePlans = \$service->getAvailableUpgradePlans(\$subscription);

        if (\$availablePlans->isEmpty()) {
            echo '❌ No upgrade plans found!\n';
        } else {
            echo '✅ Found ' . \$availablePlans->count() . ' available upgrade plans:\n';
            foreach (\$availablePlans as \$plan) {
                echo '  - ' . \$plan['name'] . ': ' . \$plan['employee_limit'] . ' users, ₱' . number_format(\$plan['implementation_fee_difference'], 2) . ' to pay\n';
            }
        }
    }
"

echo ""

# Test 4: Test getRecommendedUpgradePlan
echo "Test 4: Testing getRecommendedUpgradePlan method..."
php artisan tinker --execute="
    use App\Services\LicenseOverageService;

    \$service = new LicenseOverageService();

    \$subscription = App\Models\Subscription::with('plan')
        ->whereHas('plan', function(\$q) {
            \$q->where('name', 'like', '%Starter%');
        })
        ->where('status', 'active')
        ->first();

    if (!\$subscription) {
        echo '❌ No Starter subscription found to test with\n';
    } else {
        \$recommended = \$service->getRecommendedUpgradePlan(\$subscription);

        if (!\$recommended) {
            echo '❌ No recommended plan found!\n';
        } else {
            echo '✅ Recommended upgrade plan:\n';
            echo '   Plan: ' . \$recommended['name'] . '\n';
            echo '   Users: ' . \$recommended['employee_limit'] . '\n';
            echo '   Price: ₱' . number_format(\$recommended['price'], 2) . ' per ' . \$recommended['billing_cycle'] . '\n';
            echo '   Implementation Fee: ₱' . number_format(\$recommended['implementation_fee'], 2) . '\n';
            echo '   Amount to Pay: ₱' . number_format(\$recommended['implementation_fee_difference'], 2) . '\n';
        }
    }
"

echo ""

# Test 5: Test checkUserAdditionRequirements with upgrade scenario
echo "Test 5: Testing checkUserAdditionRequirements (upgrade scenario)..."
php artisan tinker --execute="
    use App\Services\LicenseOverageService;

    \$service = new LicenseOverageService();

    // Find a subscription near its limit
    \$subscription = App\Models\Subscription::with('plan')
        ->where('status', 'active')
        ->first();

    if (!\$subscription) {
        echo '❌ No active subscription found to test with\n';
    } else {
        \$tenantId = \$subscription->tenant_id;

        echo '✅ Testing with tenant ID: ' . \$tenantId . '\n';
        echo '   Current plan: ' . \$subscription->plan->name . '\n';
        echo '   Plan limit: ' . \$subscription->plan->employee_limit . ' users\n';

        \$currentUsers = App\Models\User::where('tenant_id', \$tenantId)->where('active_license', true)->count();
        echo '   Current users: ' . \$currentUsers . '\n\n';

        \$result = \$service->checkUserAdditionRequirements(\$tenantId);

        echo 'Status: ' . \$result['status'] . '\n';
        echo 'Message: ' . \$result['message'] . '\n';

        if (isset(\$result['data']['available_plans'])) {
            echo '\nAvailable plans:\n';
            foreach (\$result['data']['available_plans'] as \$plan) {
                \$label = \$plan['is_recommended'] ? ' (RECOMMENDED)' : '';
                echo '  - ' . \$plan['name'] . \$label . ': ' . \$plan['employee_limit'] . ' users\n';
            }
        }
    }
"

echo ""

# Test 6: Verify routes exist
echo "Test 6: Checking if routes are registered..."
echo -n "  /employees/check-license-overage: "
php artisan route:list --path=employees/check-license-overage --json | grep -q "check-license-overage" && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"

echo -n "  /employees/generate-plan-upgrade-invoice: "
php artisan route:list --path=employees/generate-plan-upgrade-invoice --json | grep -q "generate-plan-upgrade-invoice" && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"

echo ""

# Test 7: Check JavaScript file
echo "Test 7: Checking JavaScript implementation..."
if [ -f "public/build/js/employeelist.js" ]; then
    echo -n "  showPlanUpgradeModal function: "
    grep -q "function showPlanUpgradeModal" public/build/js/employeelist.js && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"

    echo -n "  Plan selection handler: "
    grep -q ".plan-option.*on.*click" public/build/js/employeelist.js && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"

    echo -n "  confirmPlanUpgradeBtn handler: "
    grep -q "confirmPlanUpgradeBtn.*on.*click" public/build/js/employeelist.js && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"
else
    echo -e "${RED}❌ employeelist.js not found!${NC}"
fi

echo ""

# Test 8: Check Blade template
echo "Test 8: Checking Blade template..."
if [ -f "resources/views/tenant/employee/employeelist.blade.php" ]; then
    echo -n "  Plan upgrade modal: "
    grep -q "id=\"plan_upgrade_modal\"" resources/views/tenant/employee/employeelist.blade.php && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"

    echo -n "  Available plans container: "
    grep -q "id=\"available_plans_container\"" resources/views/tenant/employee/employeelist.blade.php && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"

    echo -n "  Selected plan summary: "
    grep -q "id=\"selected_plan_summary\"" resources/views/tenant/employee/employeelist.blade.php && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"

    echo -n "  Ribbon CSS: "
    grep -q "class=\"ribbon" resources/views/tenant/employee/employeelist.blade.php && echo -e "${GREEN}✅ Exists${NC}" || echo -e "${RED}❌ Not found${NC}"
else
    echo -e "${RED}❌ employeelist.blade.php not found!${NC}"
fi

echo ""
echo "========================================="
echo "Verification Complete!"
echo "========================================="
echo ""
echo "If all tests passed, the plan selection feature is ready to use!"
echo ""
echo "To test manually:"
echo "1. Log in as a tenant user"
echo "2. Add users until you reach your plan limit"
echo "3. Click 'Add Employee' button"
echo "4. You should see the plan upgrade modal with all available plans"
echo "5. Select a plan and proceed with upgrade"
echo ""
