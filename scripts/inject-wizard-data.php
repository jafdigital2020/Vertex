<?php
/**
 * GitHub Actions Wizard Data Injection Script
 * 
 * This script is called by GitHub Actions to inject wizard subscription data
 * into the Vertex system during provisioning.
 * 
 * Usage: php scripts/inject-wizard-data.php '{"subscription_details": {...}, "pricing_breakdown": {...}}'
 */

require_once __DIR__ . '/../vendor/autoload.php';

function injectWizardData($wizardDataJson)
{
    try {
<<<<<<< HEAD
        // Validate JSON input
        $wizardData = json_decode($wizardDataJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        // Validate required data structure
        if (!isset($wizardData['subscription_details']) || !isset($wizardData['pricing_breakdown'])) {
            throw new Exception('Missing required subscription_details or pricing_breakdown');
        }
        
        echo "✅ Wizard data validation passed\n";
        echo "📊 Subscription Plan: " . ($wizardData['subscription_details']['plan_slug'] ?? 'Unknown') . "\n";
        echo "💰 Total Amount: ₱" . number_format($wizardData['pricing_breakdown']['total_amount'] ?? 0, 2) . "\n";
=======
        echo "🚀 Starting wizard data injection...\n";
        echo "📝 Input data length: " . strlen($wizardDataJson) . " characters\n";
        
        // Validate JSON input
        $wizardData = json_decode($wizardDataJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg() . "\nInput data: " . substr($wizardDataJson, 0, 500));
        }
        
        // Enhanced validation - support both direct format and GitHub Actions format
        $subscriptionDetails = null;
        $pricingBreakdown = null;
        
        // Check if data comes from GitHub Actions (with json_encoded strings)
        if (isset($wizardData['subscription_details']) && is_string($wizardData['subscription_details'])) {
            echo "📦 Detected GitHub Actions format - decoding JSON strings...\n";
            $subscriptionDetails = json_decode($wizardData['subscription_details'], true);
            $pricingBreakdown = json_decode($wizardData['pricing_breakdown'], true);
            
            // Decode all JSON string fields
            $wizardData['subscription_details'] = $subscriptionDetails;
            $wizardData['pricing_breakdown'] = $pricingBreakdown;
            
            if (isset($wizardData['selected_devices']) && is_string($wizardData['selected_devices'])) {
                $wizardData['selected_devices'] = json_decode($wizardData['selected_devices'], true) ?: [];
            }
            if (isset($wizardData['selected_biometric_services']) && is_string($wizardData['selected_biometric_services'])) {
                $wizardData['selected_biometric_services'] = json_decode($wizardData['selected_biometric_services'], true) ?: [];
            }
            if (isset($wizardData['company_details']) && is_string($wizardData['company_details'])) {
                $wizardData['company_details'] = json_decode($wizardData['company_details'], true) ?: [];
            }
            if (isset($wizardData['user_details']) && is_string($wizardData['user_details'])) {
                $wizardData['user_details'] = json_decode($wizardData['user_details'], true) ?: [];
            }
        } else {
            echo "📊 Detected direct format - using data as-is...\n";
            $subscriptionDetails = $wizardData['subscription_details'] ?? null;
            $pricingBreakdown = $wizardData['pricing_breakdown'] ?? null;
        }
        
        // Validate required data structure
        if (!$subscriptionDetails || !$pricingBreakdown) {
            echo "⚠️ Warning: Missing or invalid subscription_details or pricing_breakdown\n";
            echo "📋 Available keys: " . implode(', ', array_keys($wizardData)) . "\n";
            
            // Create minimal fallback structure
            if (!$subscriptionDetails) {
                $subscriptionDetails = [
                    'plan_slug' => 'unknown',
                    'billing_period' => 'monthly',
                    'system_slug' => 'timora'
                ];
            }
            if (!$pricingBreakdown) {
                $pricingBreakdown = [
                    'total_amount' => 0,
                    'base_price' => 0,
                    'implementation_fee' => 0
                ];
            }
            $wizardData['subscription_details'] = $subscriptionDetails;
            $wizardData['pricing_breakdown'] = $pricingBreakdown;
        }
        
        echo "✅ Wizard data validation passed\n";
        echo "📊 Subscription Plan: " . ($subscriptionDetails['plan_slug'] ?? 'Unknown') . "\n";
        echo "🔄 Billing Period: " . ($subscriptionDetails['billing_period'] ?? 'Unknown') . "\n";
        echo "💰 Total Amount: ₱" . number_format($pricingBreakdown['total_amount'] ?? 0, 2) . "\n";
        echo "🏢 System: " . ($subscriptionDetails['system_slug'] ?? 'Unknown') . "\n";
>>>>>>> 7aac2cc3 (invoice testing for alignment)
        
        // Method 1: Write to storage file
        $storagePath = __DIR__ . '/../storage/wizard_subscription_data.json';
        $storageDir = dirname($storagePath);
        
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
<<<<<<< HEAD
        }
        
        file_put_contents($storagePath, json_encode($wizardData, JSON_PRETTY_PRINT));
        echo "✅ Wizard data written to storage file: $storagePath\n";
        
        // Method 2: Append to .env file (will be read by config)
=======
            echo "📁 Created storage directory: $storageDir\n";
        }
        
        $writeResult = file_put_contents($storagePath, json_encode($wizardData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($writeResult === false) {
            throw new Exception("Failed to write wizard data to storage file: $storagePath");
        }
        echo "✅ Wizard data written to storage file: $storagePath (" . $writeResult . " bytes)\n";
        
        // Method 2: Append to .env file (base64 encoded to avoid shell issues)
>>>>>>> 7aac2cc3 (invoice testing for alignment)
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            // Remove existing WIZARD_SUBSCRIPTION_DATA if present
            $envContent = preg_replace('/^WIZARD_SUBSCRIPTION_DATA=.*$/m', '', $envContent);
            $envContent = trim($envContent) . "\n";
            
            // Add new wizard data (base64 encoded to avoid shell issues)
<<<<<<< HEAD
            $encodedData = base64_encode($wizardDataJson);
            $envContent .= "WIZARD_SUBSCRIPTION_DATA=" . $encodedData . "\n";
            
            file_put_contents($envPath, $envContent);
            echo "✅ Wizard data added to .env file\n";
=======
            $encodedData = base64_encode(json_encode($wizardData));
            $envContent .= "WIZARD_SUBSCRIPTION_DATA=" . $encodedData . "\n";
            
            $envWriteResult = file_put_contents($envPath, $envContent);
            if ($envWriteResult === false) {
                echo "⚠️ Warning: Failed to write wizard data to .env file\n";
            } else {
                echo "✅ Wizard data added to .env file (" . strlen($encodedData) . " chars encoded)\n";
            }
        } else {
            echo "⚠️ Warning: .env file not found at $envPath\n";
>>>>>>> 7aac2cc3 (invoice testing for alignment)
        }
        
        // Method 3: Create temporary config file for immediate use
        $tempConfigPath = __DIR__ . '/../bootstrap/cache/wizard_data.php';
<<<<<<< HEAD
        $configContent = "<?php\n\nreturn " . var_export($wizardData, true) . ";\n";
        file_put_contents($tempConfigPath, $configContent);
        echo "✅ Wizard data written to temporary config file: $tempConfigPath\n";
=======
        $tempConfigDir = dirname($tempConfigPath);
        
        if (!is_dir($tempConfigDir)) {
            mkdir($tempConfigDir, 0755, true);
            echo "📁 Created config cache directory: $tempConfigDir\n";
        }
        
        $configContent = "<?php\n\n// Auto-generated wizard data - " . date('Y-m-d H:i:s') . "\nreturn " . var_export($wizardData, true) . ";\n";
        $configWriteResult = file_put_contents($tempConfigPath, $configContent);
        if ($configWriteResult === false) {
            echo "⚠️ Warning: Failed to write wizard data to temporary config file\n";
        } else {
            echo "✅ Wizard data written to temporary config file: $tempConfigPath (" . $configWriteResult . " bytes)\n";
        }
        
        // Method 4: Validate the written data can be read back
        echo "🔍 Validating written data...\n";
        if (file_exists($storagePath)) {
            $testData = json_decode(file_get_contents($storagePath), true);
            if ($testData && isset($testData['subscription_details']['plan_slug'])) {
                echo "✅ Storage file validation passed - plan: " . $testData['subscription_details']['plan_slug'] . "\n";
            } else {
                echo "⚠️ Warning: Storage file validation failed\n";
            }
        }
>>>>>>> 7aac2cc3 (invoice testing for alignment)
        
        echo "🎉 Wizard data injection completed successfully!\n";
        return true;
        
    } catch (Exception $e) {
        echo "❌ Error injecting wizard data: " . $e->getMessage() . "\n";
        echo "📋 Stack trace: " . $e->getTraceAsString() . "\n";
<<<<<<< HEAD
=======
        echo "🔍 Debug info:\n";
        echo "  - Input data first 500 chars: " . substr($wizardDataJson, 0, 500) . "\n";
        echo "  - JSON error: " . json_last_error_msg() . "\n";
>>>>>>> 7aac2cc3 (invoice testing for alignment)
        return false;
    }
}

// Command-line execution
if ($argc < 2) {
    echo "Usage: php scripts/inject-wizard-data.php '<json-data>'\n";
    echo "Example: php scripts/inject-wizard-data.php '{\"subscription_details\": {\"plan_slug\": \"pro\"}, \"pricing_breakdown\": {\"total_amount\": 15000}}'\n";
    exit(1);
}

$wizardDataJson = $argv[1];
echo "🚀 Starting wizard data injection...\n";
echo "📝 Input data length: " . strlen($wizardDataJson) . " characters\n";

$success = injectWizardData($wizardDataJson);
exit($success ? 0 : 1);