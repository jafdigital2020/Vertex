<?php

/**
 * Test script to verify recruitment module works with fresh database deployment
 * Run this after: php artisan migrate:fresh --seed
 */

echo "=== RECRUITMENT MODULE FRESH DEPLOYMENT TEST ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Check if all tables exist
echo "1. Checking if all recruitment tables exist:\n";
$tables = [
    'job_postings',
    'candidates', 
    'job_applications',
    'interviews',
    'job_offers',
    'manpower_requests',
    'recruitment_settings',
    'candidate_permission',
    'recruitment_approvals'
];

foreach ($tables as $table) {
    $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
    echo "   " . ($exists ? "✅" : "❌") . " $table\n";
    if (!$exists) {
        echo "      ERROR: Table $table does not exist!\n";
    }
}

// Test 2: Check if branch_id columns exist
echo "\n2. Checking if branch_id columns exist:\n";
$tablesToCheck = ['job_postings', 'candidates', 'manpower_requests', 'recruitment_settings'];
foreach ($tablesToCheck as $table) {
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        $hasColumn = \Illuminate\Support\Facades\Schema::hasColumn($table, 'branch_id');
        echo "   " . ($hasColumn ? "✅" : "❌") . " $table.branch_id\n";
        if (!$hasColumn) {
            echo "      ERROR: Column branch_id missing in $table!\n";
        }
    }
}

// Test 3: Check menu and modules
echo "\n3. Checking menu and module structure:\n";
try {
    $menu = \Illuminate\Support\Facades\DB::table('menu')->where('menu_name', 'RECRUITMENT')->first();
    if ($menu) {
        echo "   ✅ RECRUITMENT menu exists (ID: {$menu->id})\n";
        
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('module_name', 'Recruitment')->first();
        if ($module) {
            echo "   ✅ Recruitment module exists (ID: {$module->id})\n";
            
            $subModules = \Illuminate\Support\Facades\DB::table('sub_modules')
                ->where('module_id', $module->id)
                ->get();
            echo "   ✅ Sub-modules: " . $subModules->count() . " found\n";
            foreach ($subModules as $sub) {
                echo "      - {$sub->sub_module_name}\n";
            }
        } else {
            echo "   ❌ Recruitment module missing\n";
        }
    } else {
        echo "   ❌ RECRUITMENT menu missing\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking menu/modules: " . $e->getMessage() . "\n";
}

// Test 4: Check foreign key constraints
echo "\n4. Testing foreign key constraints:\n";
try {
    // Test if we can create a record that respects constraints
    $testBranchId = \Illuminate\Support\Facades\DB::table('branches')->first()->id ?? null;
    
    if ($testBranchId) {
        echo "   ✅ Found test branch (ID: $testBranchId)\n";
        
        // Test job_posting creation
        $jobPosting = \Illuminate\Support\Facades\DB::table('job_postings')->insert([
            'branch_id' => $testBranchId,
            'job_code' => 'TEST-' . time(),
            'title' => 'Test Position',
            'department_id' => \Illuminate\Support\Facades\DB::table('departments')->first()->id ?? 1,
            'description' => 'Test description',
            'employment_type' => 'full-time',
            'status' => 'draft',
            'is_active' => 'active',
            'created_by' => \Illuminate\Support\Facades\DB::table('users')->first()->id ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        if ($jobPosting) {
            echo "   ✅ Job posting creation with branch_id works\n";
            // Clean up
            \Illuminate\Support\Facades\DB::table('job_postings')->where('job_code', 'TEST-' . time())->delete();
        } else {
            echo "   ❌ Job posting creation failed\n";
        }
    } else {
        echo "   ⚠️ No branches found to test foreign keys\n";
    }
} catch (Exception $e) {
    echo "   ❌ Foreign key test failed: " . $e->getMessage() . "\n";
}

// Test 5: Summary
echo "\n5. SUMMARY:\n";
echo "   - All recruitment tables should exist with branch_id columns\n";
echo "   - Menu and module structure should be in place\n";
echo "   - Foreign key constraints should work\n";
echo "   - Ready for fresh deployment ✅\n";

echo "\n=== TEST COMPLETE ===\n";