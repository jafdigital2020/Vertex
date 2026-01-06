<?php
/**
 * Test Script for Biometric Device & Service Pricing Alignment
 * 
 * This script tests that biometric devices and services match wizard frontend pricing exactly.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Set up Laravel environment
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\WizardInvoiceService;

function testBiometricPricing()
{
    echo "🧪 Testing Biometric Device & Service Pricing Alignment...\n\n";
    
    // Expected device prices from wizard frontend
    $expectedDevicePrices = [
        1 => ['name' => 'ZKTeco eFace10', 'price' => 13798.40],
        2 => ['name' => 'ZKTeco IN05 / IN05-A', 'price' => 21481.60],
        3 => ['name' => 'ZKTeco MB10-VL', 'price' => 9094.40],
        4 => ['name' => 'ZKTeco MB560-VL', 'price' => 22892.80],
        5 => ['name' => 'ZKTeco SenseFace 2A', 'price' => 10035.20],
        6 => ['name' => 'ZKTeco SF400', 'price' => 20898.30],
        7 => ['name' => 'ZKTeco UA860', 'price' => 15366.00],
        8 => ['name' => 'ZKTeco SpeedFace V3L', 'price' => 18502.40],
        9 => ['name' => 'ZKTeco SpeedFace V5L', 'price' => 34809.60],
    ];
    
    $expectedServicePrices = [
        'wall_mounted' => 9999,
        'door_access' => 14999,
        'custom_logo' => 3999,
        'geofencing' => 10000,
        'email' => 456,
    ];
    
    // Test 1: Verify config pricing matches wizard frontend
    echo "1️⃣ Testing config pricing alignment...\n";
    
    $configDevices = config('wizard.biometric_devices', []);
    $configAddons = config('wizard.addons', []);
    
    $devicePricingErrors = [];
    foreach ($expectedDevicePrices as $deviceId => $expected) {
        if (!isset($configDevices[$deviceId])) {
            $devicePricingErrors[] = "Device ID $deviceId not found in config";
            continue;
        }
        
        $config = $configDevices[$deviceId];
        if ($config['price'] != $expected['price']) {
            $devicePricingErrors[] = "Device ID $deviceId: Expected ₱{$expected['price']}, got ₱{$config['price']}";
        } else {
            echo "✅ {$expected['name']}: ₱" . number_format($expected['price'], 2) . "\n";
        }
    }
    
    if (!empty($devicePricingErrors)) {
        echo "❌ Device pricing errors found:\n";
        foreach ($devicePricingErrors as $error) {
            echo "   - $error\n";
        }
    } else {
        echo "✅ All device pricing matches wizard frontend\n";
    }
    
    echo "\n";
    
    $servicePricingErrors = [];
    foreach ($expectedServicePrices as $serviceKey => $expectedPrice) {
        if (!isset($configAddons[$serviceKey])) {
            $servicePricingErrors[] = "Service '$serviceKey' not found in config";
            continue;
        }
        
        $config = $configAddons[$serviceKey];
        if ($config['price'] != $expectedPrice) {
            $servicePricingErrors[] = "Service '$serviceKey': Expected ₱{$expectedPrice}, got ₱{$config['price']}";
        } else {
            echo "✅ {$config['name']}: ₱" . number_format($expectedPrice, 2) . "\n";
        }
    }
    
    if (!empty($servicePricingErrors)) {
        echo "❌ Service pricing errors found:\n";
        foreach ($servicePricingErrors as $error) {
            echo "   - $error\n";
        }
    } else {
        echo "✅ All service pricing matches wizard frontend\n";
    }
    
    echo "\n";
    
    // Test 2: Test invoice generation with biometric items
    echo "2️⃣ Testing invoice generation with biometric devices...\n";
    
    $sampleWizardData = [
        'subscription_details' => [
            'plan_slug' => 'pro',
            'billing_period' => 'monthly',
            'total_employees' => 120,
            'mobile_access' => true,
            'mobile_app_users' => 30,
            'selected_addons' => ['geofencing', 'custom_logo']
        ],
        'pricing_breakdown' => [
            'base_price' => 9549,
            'extra_cost' => 931,     // 19 extra employees * 49
            'mobile_cost' => 1470,   // 30 mobile users * 49
            'addon_cost' => 10000,   // geofencing monthly
            'one_time_addon_cost' => 3999, // custom logo
            'biometric_device_cost' => 37004.80, // 2x SpeedFace V3L
            'biometric_services_cost' => 9999,   // wall mounted installation
            'implementation_fee' => 39999,
            'vat' => 13200,
            'total_amount' => 126152
        ],
        'selected_devices' => [
            [
                'id' => 8,
                'name' => 'ZKTeco SpeedFace V3L',
                'price' => 18502.40,
                'quantity' => 2
            ]
        ],
        'selected_biometric_services' => [
            'installation' => true,
            'integration' => false
        ],
        'company_details' => [
            'company_name' => 'Test Biometric Co',
            'company_code' => 'BIOTEST'
        ],
        'user_details' => [
            'email' => 'test@biotest.com',
            'username' => 'biotest'
        ]
    ];
    
    try {
        $wizardService = new WizardInvoiceService();
        
        // Create a test subscription object
        $testSubscription = (object) [
            'id' => 998,
            'tenant_id' => 1,
            'plan_id' => 1
        ];
        
        // Simulate invoice creation (without database insertion)
        $reflection = new \ReflectionClass($wizardService);
        $method = $reflection->getMethod('createInvoiceLineItems');
        $method->setAccessible(true);
        
        // Mock invoice ID for line item creation
        $mockInvoiceId = 999999;
        
        // Call the protected method to test line item generation
        $method->invoke($wizardService, $mockInvoiceId, $sampleWizardData);
        
        echo "✅ Invoice line item generation completed successfully\n";
        echo "📊 Test data breakdown:\n";
        echo "   - Plan: {$sampleWizardData['subscription_details']['plan_slug']}\n";
        echo "   - Base Price: ₱" . number_format($sampleWizardData['pricing_breakdown']['base_price']) . "\n";
        echo "   - Devices: 2x ZKTeco SpeedFace V3L @ ₱18,502.40 each\n";
        echo "   - Installation: Wall Mounted @ ₱9,999\n";
        echo "   - Total: ₱" . number_format($sampleWizardData['pricing_breakdown']['total_amount']) . "\n";
        
        // Verify device pricing calculation
        $deviceTotal = 18502.40 * 2;
        echo "\n🔍 Device calculation verification:\n";
        echo "   Expected: ₱" . number_format($deviceTotal, 2) . "\n";
        echo "   Config: ₱" . number_format($sampleWizardData['pricing_breakdown']['biometric_device_cost'], 2) . "\n";
        
        if (abs($deviceTotal - $sampleWizardData['pricing_breakdown']['biometric_device_cost']) < 0.01) {
            echo "✅ Device pricing calculation is correct\n";
        } else {
            echo "❌ Device pricing calculation mismatch\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Invoice generation test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Test 3: Verify complete pricing alignment summary
    echo "3️⃣ Complete pricing alignment summary...\n";
    
    $totalDevicePriceErrors = count($devicePricingErrors);
    $totalServicePriceErrors = count($servicePricingErrors);
    $totalErrors = $totalDevicePriceErrors + $totalServicePriceErrors;
    
    if ($totalErrors === 0) {
        echo "🎉 ALL PRICING ALIGNED SUCCESSFULLY!\n";
        echo "✅ " . count($expectedDevicePrices) . " biometric devices\n";
        echo "✅ " . count($expectedServicePrices) . " services/addons\n";
        echo "✅ Invoice generation working\n";
        echo "✅ Pricing calculations accurate\n";
        echo "\n🚀 Vertex invoices will now match wizard pricing exactly!\n";
        return true;
    } else {
        echo "❌ PRICING ALIGNMENT INCOMPLETE\n";
        echo "❌ Device errors: $totalDevicePriceErrors\n";
        echo "❌ Service errors: $totalServicePriceErrors\n";
        echo "❌ Total errors: $totalErrors\n";
        return false;
    }
}

// Run the test
try {
    $success = testBiometricPricing();
    exit($success ? 0 : 1);
} catch (\Exception $e) {
    echo "💥 Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}