<?php

namespace Database\Seeders;

use App\Services\WizardInvoiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicesTableSeeder extends Seeder
{
    protected $wizardInvoiceService;

    public function __construct(WizardInvoiceService $wizardInvoiceService)
    {
        $this->wizardInvoiceService = $wizardInvoiceService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the subscription to link the invoice to
        $subscription = DB::table('subscriptions')->where('tenant_id', 1)->first();

        if (!$subscription) {
            $this->command->warn('No subscription found. Skipping invoice seeder.');
            return;
        }

        Log::info('Starting invoice seeding process', [
            'subscription_id' => $subscription->id,
            'subscription_data' => $subscription
        ]);

        // Try to get wizard data from environment variables or config
        $wizardData = $this->getWizardDataFromEnvironment();

        if ($wizardData) {
            // Use wizard data for accurate invoice generation
            $this->command->info('Using wizard data for accurate invoice generation...');
            
            try {
                $invoiceId = $this->wizardInvoiceService->createInvoiceFromWizardData(
                    $wizardData, 
                    $subscription, 
                    1 // tenant_id
                );
                
                $this->command->info('Invoice created successfully with wizard data. Invoice ID: ' . $invoiceId);
                
            } catch (\Exception $e) {
                $this->command->error('Failed to create invoice with wizard data: ' . $e->getMessage());
                $this->command->warn('Falling back to legacy invoice generation...');
                $this->createLegacyInvoice($subscription);
            }
        } else {
            // Fallback to legacy invoice generation
            $this->command->warn('No wizard data found. Using legacy invoice generation...');
            $this->createLegacyInvoice($subscription);
        }
    }

    /**
     * Get wizard data from environment variables or config files
     * This should be set during the GitHub Actions provisioning process
     */
    protected function getWizardDataFromEnvironment()
    {
<<<<<<< HEAD
        // Method 1: Check for wizard data in environment variables
        $wizardDataJson = env('WIZARD_SUBSCRIPTION_DATA');
        if ($wizardDataJson) {
            $wizardData = json_decode($wizardDataJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Wizard data loaded from environment variable');
                return $wizardData;
=======
        $this->command->info('🔍 Searching for wizard data from multiple sources...');
        
        // Method 1: Check for wizard data in environment variables
        $wizardDataJson = env('WIZARD_SUBSCRIPTION_DATA');
        if ($wizardDataJson) {
            $this->command->info('📦 Found wizard data in environment variable');
            
            // Handle base64 encoded data
            $decodedData = base64_decode($wizardDataJson, true);
            if ($decodedData !== false) {
                $wizardDataJson = $decodedData;
                $this->command->info('🔓 Decoded base64 wizard data');
            }
            
            $wizardData = json_decode($wizardDataJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Wizard data loaded from environment variable', [
                    'data_size' => strlen($wizardDataJson),
                    'plan' => $wizardData['subscription_details']['plan_slug'] ?? 'unknown',
                    'total_amount' => $wizardData['pricing_breakdown']['total_amount'] ?? 0
                ]);
                $this->command->info('✅ Environment wizard data is valid JSON');
                return $wizardData;
            } else {
                $this->command->error('❌ Environment wizard data is invalid JSON: ' . json_last_error_msg());
                Log::error('Invalid JSON in environment wizard data', [
                    'json_error' => json_last_error_msg(),
                    'data_preview' => substr($wizardDataJson, 0, 200)
                ]);
>>>>>>> 7aac2cc3 (invoice testing for alignment)
            }
        }

        // Method 2: Check for wizard data in config file
        $wizardData = config('wizard.subscription_data');
        if ($wizardData && is_array($wizardData)) {
<<<<<<< HEAD
            Log::info('Wizard data loaded from config file');
=======
            $this->command->info('📋 Found wizard data in config file');
            Log::info('Wizard data loaded from config file', [
                'plan' => $wizardData['subscription_details']['plan_slug'] ?? 'unknown'
            ]);
>>>>>>> 7aac2cc3 (invoice testing for alignment)
            return $wizardData;
        }

        // Method 3: Check for wizard data in storage file (written by GitHub Actions)
        $wizardDataPath = storage_path('wizard_subscription_data.json');
        if (file_exists($wizardDataPath)) {
<<<<<<< HEAD
            $wizardDataContent = file_get_contents($wizardDataPath);
            $wizardData = json_decode($wizardDataContent, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Wizard data loaded from storage file', ['path' => $wizardDataPath]);
                return $wizardData;
            }
        }

        Log::warning('No wizard data found in environment variables, config, or storage files');
=======
            $this->command->info("📄 Found wizard data storage file: $wizardDataPath");
            $wizardDataContent = file_get_contents($wizardDataPath);
            $wizardData = json_decode($wizardDataContent, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Wizard data loaded from storage file', [
                    'path' => $wizardDataPath,
                    'file_size' => filesize($wizardDataPath),
                    'plan' => $wizardData['subscription_details']['plan_slug'] ?? 'unknown',
                    'total_amount' => $wizardData['pricing_breakdown']['total_amount'] ?? 0
                ]);
                $this->command->info('✅ Storage wizard data is valid JSON');
                return $wizardData;
            } else {
                $this->command->error('❌ Storage wizard data is invalid JSON: ' . json_last_error_msg());
                Log::error('Invalid JSON in storage wizard data', [
                    'path' => $wizardDataPath,
                    'json_error' => json_last_error_msg(),
                    'data_preview' => substr($wizardDataContent, 0, 200)
                ]);
            }
        } else {
            $this->command->info('📂 No wizard data storage file found at: ' . $wizardDataPath);
        }

        // Method 4: Check for wizard data in temporary config cache
        $tempConfigPath = base_path('bootstrap/cache/wizard_data.php');
        if (file_exists($tempConfigPath)) {
            $this->command->info("⚡ Found wizard data cache file: $tempConfigPath");
            try {
                $wizardData = include $tempConfigPath;
                if (is_array($wizardData) && isset($wizardData['subscription_details'])) {
                    Log::info('Wizard data loaded from cache file', [
                        'path' => $tempConfigPath,
                        'plan' => $wizardData['subscription_details']['plan_slug'] ?? 'unknown'
                    ]);
                    $this->command->info('✅ Cache wizard data is valid');
                    return $wizardData;
                }
            } catch (\Exception $e) {
                $this->command->error('❌ Failed to load wizard data from cache: ' . $e->getMessage());
                Log::error('Failed to load wizard data from cache', [
                    'path' => $tempConfigPath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->command->warn('⚠️ No valid wizard data found in any source');
        Log::warning('No wizard data found in environment variables, config, storage files, or cache', [
            'checked_paths' => [
                'env_var' => 'WIZARD_SUBSCRIPTION_DATA',
                'config' => 'wizard.subscription_data',
                'storage' => $wizardDataPath,
                'cache' => $tempConfigPath
            ]
        ]);
>>>>>>> 7aac2cc3 (invoice testing for alignment)
        return null;
    }

    /**
     * Legacy invoice creation method (fallback)
     */
    protected function createLegacyInvoice($subscription)
    {
        // Default billing cycle (will match subscription's billing cycle)
        $billingCycle = $subscription->billing_cycle ?? 'monthly';

        // Calculate period dates based on billing cycle
        $periodStart = Carbon::now()->toDateString();

        if ($billingCycle === 'yearly') {
            $periodEnd = Carbon::now()->addYear()->subDay()->toDateString();
        } else { // monthly
            $periodEnd = Carbon::now()->addMonth()->subDay()->toDateString();
        }

        // Calculate amounts
        $subscriptionAmount = $subscription->amount_paid ?? 0.00;
        $implementationFee = $subscription->implementation_fee ?? 0.00;
        $vatPercentage = 12.00; // Default VAT percentage

        // Calculate subtotal (subscription + implementation fee)
        $subtotal = $subscriptionAmount + $implementationFee;

        // Calculate VAT amount
        $vatAmount = $subtotal * ($vatPercentage / 100);

        // Calculate total amount due
        $amountDue = $subtotal + $vatAmount;

        // Generate invoice number
        $invoiceNumber = 'INV-' . strtoupper(uniqid());

        DB::table('invoices')->insert([
            'tenant_id' => 1,
            'subscription_id' => $subscription->id,
            'invoice_type' => 'subscription',
            'billing_cycle' => $billingCycle,
            'invoice_number' => $invoiceNumber,
            'amount_due' => $amountDue,
            'amount_paid' => $amountDue, // Paid in full
            'subscription_amount' => $subscriptionAmount,
            'currency' => 'PHP',
            'due_date' => Carbon::now()->toDateString(),
            'status' => 'paid',
            'issued_at' => Carbon::now(),
            'paid_at' => Carbon::now(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'license_overage_count' => 0,
            'license_overage_rate' => 0.00,
            'license_overage_amount' => 0.00,
            'unused_overage_count' => 0,
            'unused_overage_amount' => 0.00,
            'gross_overage_count' => 0,
            'gross_overage_amount' => 0.00,
            'implementation_fee' => $implementationFee,
            'vat_amount' => $vatAmount,
            'vat_percentage' => $vatPercentage,
            'subtotal' => $subtotal,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Legacy invoice created successfully: ' . $invoiceNumber);
    }
}
