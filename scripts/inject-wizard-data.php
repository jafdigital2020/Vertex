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
        
        // Method 1: Write to storage file
        $storagePath = __DIR__ . '/../storage/wizard_subscription_data.json';
        $storageDir = dirname($storagePath);
        
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        file_put_contents($storagePath, json_encode($wizardData, JSON_PRETTY_PRINT));
        echo "✅ Wizard data written to storage file: $storagePath\n";
        
        // Method 2: Append to .env file (will be read by config)
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            // Remove existing WIZARD_SUBSCRIPTION_DATA if present
            $envContent = preg_replace('/^WIZARD_SUBSCRIPTION_DATA=.*$/m', '', $envContent);
            $envContent = trim($envContent) . "\n";
            
            // Add new wizard data (base64 encoded to avoid shell issues)
            $encodedData = base64_encode($wizardDataJson);
            $envContent .= "WIZARD_SUBSCRIPTION_DATA=" . $encodedData . "\n";
            
            file_put_contents($envPath, $envContent);
            echo "✅ Wizard data added to .env file\n";
        }
        
        // Method 3: Create temporary config file for immediate use
        $tempConfigPath = __DIR__ . '/../bootstrap/cache/wizard_data.php';
        $configContent = "<?php\n\nreturn " . var_export($wizardData, true) . ";\n";
        file_put_contents($tempConfigPath, $configContent);
        echo "✅ Wizard data written to temporary config file: $tempConfigPath\n";
        
        echo "🎉 Wizard data injection completed successfully!\n";
        return true;
        
    } catch (Exception $e) {
        echo "❌ Error injecting wizard data: " . $e->getMessage() . "\n";
        echo "📋 Stack trace: " . $e->getTraceAsString() . "\n";
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