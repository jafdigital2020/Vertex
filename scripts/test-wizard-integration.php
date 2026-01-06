<?php
/**
 * Test Script for Wizard Data Integration
 * 
 * This script tests the wizard data integration flow to ensure
 * that invoice generation works correctly with wizard data.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Set up Laravel environment
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\WizardInvoiceService;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function testWizardDataIntegration()
{
    echo "🧪 Testing Wizard Data Integration...\n\n";
    
    // Test data simulating what would come from HitPayController
    $sampleWizardData = [
        'subscription_details' => [
            'plan_slug' => 'pro',
            'system_slug' => 'timora',
            'billing_period' => 'monthly',
            'total_employees' => 150,
            'mobile_access' => true,
            'mobile_app_users' => 50,
            'biometric_device_count' => 2,
            'selected_addons' => ['geofencing', 'custom_logo'],
            'is_trial' => false,
            'referral_code' => null
        ],
        'pricing_breakdown' => [
            'base_price' => 9999,
            'extra_cost' => 2450,    // 50 extra employees * 49
            'mobile_cost' => 2450,   // 50 mobile users * 49
            'addon_cost' => 10000,   // geofencing monthly
            'one_time_addon_cost' => 3999, // custom logo
            'biometric_device_cost' => 15000,
            'biometric_services_cost' => 9999,
            'total_biometric_cost' => 24999,
            'implementation_fee' => 39999,
            'monthly_subtotal' => 24899,
            'recurring_total' => 24899,
            'vat' => 10788,
            'total_amount' => 109686
        ],
        'selected_devices' => [
            [
                'name' => 'ZKTeco K40 Pro',
                'price' => 7500,
                'quantity' => 2,
                'total_cost' => 15000
            ]
        ],
        'selected_biometric_services' => [
            'installation' => true,
            'integration' => true
        ],
        'company_details' => [
            'company_name' => 'Test Company Ltd',
            'company_code' => 'TESTCO',
            'industry' => 'Technology',
            'country' => 'Philippines',
            'state' => 'Metro Manila',
            'city' => 'Makati',
            'subdomain' => 'testco'
        ],
        'user_details' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@testcompany.com',
            'phone' => '+63912345678',
            'username' => 'johndoe'
        ]
    ];
    
    // Test 1: JSON Encoding/Decoding (simulates GitHub Actions format)
    echo "1️⃣ Testing JSON encoding/decoding...\n";
    $encodedData = json_encode($sampleWizardData);
    $decodedData = json_decode($encodedData, true);
    
    if ($decodedData && isset($decodedData['subscription_details']['plan_slug'])) {
        echo "✅ JSON encoding/decoding works correctly\n";
        echo "   Plan: " . $decodedData['subscription_details']['plan_slug'] . "\n";
        echo "   Total Amount: ₱" . number_format($decodedData['pricing_breakdown']['total_amount']) . "\n\n";
    } else {
        echo "❌ JSON encoding/decoding failed\n\n";
        return false;
    }
    
    // Test 2: Wizard Data Injection Script
    echo "2️⃣ Testing wizard data injection script...\n";
    $tempFile = tempnam(sys_get_temp_dir(), 'wizard_test_');
    file_put_contents($tempFile, $encodedData);
    
    $output = shell_exec("cd " . __DIR__ . "/.. && php scripts/inject-wizard-data.php '" . $encodedData . "' 2>&1");
    echo "Script Output:\n" . $output . "\n";
    
    // Check if data was written to storage
    $storagePath = __DIR__ . '/../storage/wizard_subscription_data.json';
    if (file_exists($storagePath)) {
        echo "✅ Wizard data written to storage file\n";
        $storageData = json_decode(file_get_contents($storagePath), true);
        if ($storageData && isset($storageData['subscription_details']['plan_slug'])) {
            echo "✅ Storage data is valid\n\n";
        } else {
            echo "❌ Storage data is invalid\n\n";
        }
    } else {
        echo "⚠️ No storage file created\n\n";
    }
    
    // Test 3: WizardInvoiceService
    echo "3️⃣ Testing WizardInvoiceService...\n";
    try {
        // Create a test subscription object
        $testSubscription = (object) [
            'id' => 999,
            'tenant_id' => 1,
            'plan_id' => 1
        ];
        
        $wizardService = new WizardInvoiceService();
        $invoiceId = $wizardService->createInvoiceFromWizardData($sampleWizardData, $testSubscription, 1);
        
        if ($invoiceId) {
            echo "✅ WizardInvoiceService created invoice successfully\n";
            echo "   Invoice ID: $invoiceId\n";
            
            // Check invoice details
            $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
            if ($invoice) {
                echo "   Amount Due: ₱" . number_format($invoice->amount_due) . "\n";
                echo "   Plan: " . ($sampleWizardData['subscription_details']['plan_slug'] ?? 'unknown') . "\n";
                
                // Check line items
                $lineItems = DB::table('invoice_items')->where('invoice_id', $invoiceId)->get();
                echo "   Line Items: " . $lineItems->count() . " items\n";
                
                foreach ($lineItems as $item) {
                    $metadata = json_decode($item->metadata, true);
                    $type = $metadata['type'] ?? 'unknown';
                    echo "     - {$item->description} (Type: {$type}, Amount: ₱" . number_format($item->amount) . ")\n";
                }
                
                echo "✅ Invoice generation completed successfully\n\n";
            } else {
                echo "❌ Invoice not found in database\n\n";
            }
        } else {
            echo "❌ WizardInvoiceService failed to create invoice\n\n";
        }
    } catch (\Exception $e) {
        echo "❌ WizardInvoiceService error: " . $e->getMessage() . "\n\n";
    }
    
    // Test 4: Invoice Seeder Data Loading
    echo "4️⃣ Testing invoice seeder data loading...\n";
    try {
        $seederClass = new \Database\Seeders\InvoicesTableSeeder(new WizardInvoiceService());
        $reflection = new \ReflectionClass($seederClass);
        $method = $reflection->getMethod('getWizardDataFromEnvironment');
        $method->setAccessible(true);
        
        // Mock command object
        $mockCommand = new class {
            public function info($message) { echo "ℹ️  $message\n"; }
            public function warn($message) { echo "⚠️  $message\n"; }
            public function error($message) { echo "❌ $message\n"; }
        };
        $seederClass->command = $mockCommand;
        
        $loadedData = $method->invoke($seederClass);
        
        if ($loadedData) {
            echo "✅ Invoice seeder loaded wizard data successfully\n";
            echo "   Plan: " . ($loadedData['subscription_details']['plan_slug'] ?? 'unknown') . "\n";
            echo "   Total: ₱" . number_format($loadedData['pricing_breakdown']['total_amount'] ?? 0) . "\n\n";
        } else {
            echo "⚠️ Invoice seeder could not load wizard data (this is expected if no .env data)\n\n";
        }
    } catch (\Exception $e) {
        echo "❌ Invoice seeder test error: " . $e->getMessage() . "\n\n";
    }
    
    // Clean up test data
    echo "🧹 Cleaning up test data...\n";
    if (isset($invoiceId)) {
        DB::table('invoice_items')->where('invoice_id', $invoiceId)->delete();
        DB::table('invoices')->where('id', $invoiceId)->delete();
        echo "✅ Test invoice data cleaned up\n";
    }
    
    unlink($tempFile);
    
    echo "\n🎉 Wizard data integration test completed!\n";
    
    return true;
}

// Run the test
try {
    $success = testWizardDataIntegration();
    exit($success ? 0 : 1);
} catch (\Exception $e) {
    echo "💥 Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}