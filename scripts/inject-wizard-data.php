<?php
/**
 * GitHub Actions Wizard Data Injection Script
 *
 * Supports:
 *  - repository_dispatch (CLI JSON argument)
 *  - workflow_dispatch (base64 JSON via .env)
 *
 * Usage:
 *   php scripts/inject-wizard-data.php '{"subscription_details": {...}}'
 */

require_once __DIR__ . '/../vendor/autoload.php';

function injectWizardData(?string $wizardDataJson)
{
    try {
        echo "🚀 Starting wizard data injection...\n";

        /**
         * --------------------------------------------------
         * STEP 1: Resolve wizard JSON source
         * Priority:
         *   1) CLI argument (repository_dispatch)
         *   2) Base64 env (workflow_dispatch + SSH)
         * --------------------------------------------------
         */
        $rawJson = null;

        if (!empty($wizardDataJson) && trim($wizardDataJson) !== '{}') {
            $rawJson = $wizardDataJson;
            echo "📦 Using wizard data from CLI argument\n";
        } else {
            $encoded = getenv('WIZARD_SUBSCRIPTION_DATA');

            if ($encoded) {
                $decoded = base64_decode($encoded, true);
                if ($decoded === false) {
                    throw new Exception("Invalid base64 in WIZARD_SUBSCRIPTION_DATA");
                }
                $rawJson = $decoded;
                echo "📦 Using wizard data from WIZARD_SUBSCRIPTION_DATA env\n";
            }
        }

        if (!$rawJson) {
            throw new Exception("No wizard data found (CLI arg and env both empty)");
        }

        echo "📝 Raw JSON length: " . strlen($rawJson) . " characters\n";

        /**
         * --------------------------------------------------
         * STEP 2: Decode JSON safely
         * --------------------------------------------------
         */
        $wizardData = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(
                "Invalid wizard JSON: " . json_last_error_msg() .
                "\nPreview: " . substr($rawJson, 0, 300)
            );
        }

        /**
         * --------------------------------------------------
         * STEP 3: Normalize formats
         * --------------------------------------------------
         */
        if (isset($wizardData['subscription_details']) && is_string($wizardData['subscription_details'])) {
            echo "📦 Detected stringified JSON fields – decoding...\n";

            // Function to parse object notation like {key:value} to proper array
            $parseObjectNotation = function($str) {
                // Try JSON first
                $decoded = json_decode($str, true);
                if ($decoded !== null) {
                    return $decoded;
                }
                
                // Parse object notation manually
                $result = [];
                
                // Remove outer braces and split by commas
                $str = trim($str, '{}');
                $pairs = explode(',', $str);
                
                foreach ($pairs as $pair) {
                    if (strpos($pair, ':') !== false) {
                        [$key, $value] = explode(':', $pair, 2);
                        $key = trim($key);
                        $value = trim($value);
                        
                        // Handle arrays like [item1,item2]
                        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                            $arrayContent = trim($value, '[]');
                            $result[$key] = $arrayContent ? explode(',', $arrayContent) : [];
                        } 
                        // Handle boolean values
                        elseif ($value === 'true') {
                            $result[$key] = true;
                        } elseif ($value === 'false') {
                            $result[$key] = false;
                        } 
                        // Handle numeric values
                        elseif (is_numeric($value)) {
                            $result[$key] = (int)$value;
                        } 
                        // Handle string values
                        else {
                            $result[$key] = $value;
                        }
                    }
                }
                
                return $result;
            };

            $wizardData['subscription_details'] = 
                $parseObjectNotation($wizardData['subscription_details']);

            $wizardData['pricing_breakdown'] =
                $parseObjectNotation($wizardData['pricing_breakdown'] ?? '{}');

            $wizardData['selected_devices'] =
                $parseObjectNotation($wizardData['selected_devices'] ?? '[]');

            $wizardData['selected_biometric_services'] =
                $parseObjectNotation($wizardData['selected_biometric_services'] ?? '{}');

            $wizardData['company_details'] =
                $parseObjectNotation($wizardData['company_details'] ?? '{}');

            $wizardData['user_details'] =
                $parseObjectNotation($wizardData['user_details'] ?? '{}');
        }

        /**
         * --------------------------------------------------
         * STEP 4: Validate & fallback
         * --------------------------------------------------
         */
        $subscriptionDetails = $wizardData['subscription_details'] ?? null;
        $pricingBreakdown    = $wizardData['pricing_breakdown'] ?? null;

        if (!$subscriptionDetails || !$pricingBreakdown) {
            echo "⚠️ Missing subscription_details or pricing_breakdown – applying fallback\n";

            $subscriptionDetails ??= [
                'plan_slug'      => 'unknown',
                'billing_period' => 'monthly',
                'system_slug'    => 'timora',
            ];

            $pricingBreakdown ??= [
                'total_amount'        => 0,
                'base_price'          => 0,
                'implementation_fee'  => 0,
            ];

            $wizardData['subscription_details'] = $subscriptionDetails;
            $wizardData['pricing_breakdown']    = $pricingBreakdown;
        }

        /**
         * --------------------------------------------------
         * STEP 5: Log summary
         * --------------------------------------------------
         */
        echo "✅ Wizard data validated\n";
        echo "📊 Plan: " . ($subscriptionDetails['plan_slug'] ?? 'unknown') . "\n";
        echo "🔄 Billing: " . ($subscriptionDetails['billing_period'] ?? 'unknown') . "\n";
        echo "💰 Total: ₱" . number_format($pricingBreakdown['total_amount'] ?? 0, 2) . "\n";
        echo "🏢 System: " . ($subscriptionDetails['system_slug'] ?? 'unknown') . "\n";

        /**
         * --------------------------------------------------
         * STEP 6: Write storage file
         * --------------------------------------------------
         */
        $storagePath = __DIR__ . '/../storage/wizard_subscription_data.json';
        @mkdir(dirname($storagePath), 0755, true);

        file_put_contents(
            $storagePath,
            json_encode($wizardData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        echo "✅ Stored wizard data: $storagePath\n";

        /**
         * --------------------------------------------------
         * STEP 7: Write base64 env
         * --------------------------------------------------
         */
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $env = file_get_contents($envPath);
            $env = preg_replace('/^WIZARD_SUBSCRIPTION_DATA=.*$/m', '', $env);
            $env = trim($env) . PHP_EOL;

            $encoded = base64_encode(json_encode($wizardData));
            $env .= "WIZARD_SUBSCRIPTION_DATA=$encoded\n";

            file_put_contents($envPath, $env);
            echo "✅ Updated .env with base64 wizard data\n";
        }

        /**
         * --------------------------------------------------
         * STEP 8: Write cached config
         * --------------------------------------------------
         */
        $cachePath = __DIR__ . '/../bootstrap/cache/wizard_data.php';
        @mkdir(dirname($cachePath), 0755, true);

        file_put_contents(
            $cachePath,
            "<?php\nreturn " . var_export($wizardData, true) . ";\n"
        );

        echo "✅ Cached wizard config written\n";

        /**
         * --------------------------------------------------
         * STEP 9: Validate read-back
         * --------------------------------------------------
         */
        $check = json_decode(file_get_contents($storagePath), true);
        if (!isset($check['subscription_details']) || 
            (!isset($check['subscription_details']['plan_slug']) && 
             !isset($check['subscription_details']['plan_name']))) {
            throw new Exception("Storage validation failed: missing subscription details or plan identifier");
        }

        echo "🎉 Wizard data injection completed successfully\n";
        return true;

    } catch (Throwable $e) {
        echo "❌ Wizard injection failed\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
        return false;
    }
}

/**
 * --------------------------------------------------
 * CLI execution
 * --------------------------------------------------
 */
$wizardDataJson = $argv[1] ?? null;
exit(injectWizardData($wizardDataJson) ? 0 : 1);
