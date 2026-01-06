<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WizardInvoiceService
{
    /**
     * Create accurate invoice using wizard subscription data
     */
    public function createInvoiceFromWizardData(array $wizardData, $subscription, $tenantId = 1)
    {
        try {
            Log::info('Creating invoice from wizard data', [
                'wizard_data_keys' => array_keys($wizardData),
                'subscription_id' => $subscription->id
            ]);

            // Extract pricing breakdown
            $pricingBreakdown = $wizardData['pricing_breakdown'] ?? [];
            $subscriptionDetails = $wizardData['subscription_details'] ?? [];
            
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Calculate period dates based on billing cycle
            $billingCycle = $subscriptionDetails['billing_period'] ?? 'monthly';
            $periodDates = $this->calculatePeriodDates($billingCycle);
            
            // Prepare invoice data
            $invoiceData = [
                'tenant_id' => $tenantId,
                'subscription_id' => $subscription->id,
                'invoice_type' => 'subscription',
                'billing_cycle' => $billingCycle,
                'invoice_number' => $invoiceNumber,
                'amount_due' => $pricingBreakdown['total_amount'] ?? 0,
                'amount_paid' => $pricingBreakdown['total_amount'] ?? 0, // Paid in full
                'subscription_amount' => $pricingBreakdown['recurring_total'] ?? 0,
                'currency' => 'PHP',
                'due_date' => Carbon::now()->toDateString(),
                'status' => 'paid',
                'issued_at' => Carbon::now(),
                'paid_at' => Carbon::now(),
                'period_start' => $periodDates['start'],
                'period_end' => $periodDates['end'],
                'license_overage_count' => 0,
                'license_overage_rate' => 0.00,
                'license_overage_amount' => 0.00,
                'unused_overage_count' => 0,
                'unused_overage_amount' => 0.00,
                'gross_overage_count' => 0,
                'gross_overage_amount' => 0.00,
                'implementation_fee' => $pricingBreakdown['implementation_fee'] ?? 0,
                'vat_amount' => $pricingBreakdown['vat'] ?? 0,
                'vat_percentage' => 12.00,
                'subtotal' => $pricingBreakdown['recurring_total'] + $pricingBreakdown['implementation_fee'] + ($pricingBreakdown['one_time_addon_cost'] ?? 0),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            // Insert invoice
            $invoiceId = DB::table('invoices')->insertGetId($invoiceData);
            
            // Create detailed line items
            $this->createInvoiceLineItems($invoiceId, $wizardData);
            
            Log::info('Invoice created successfully from wizard data', [
                'invoice_number' => $invoiceNumber,
                'invoice_id' => $invoiceId,
                'amount_due' => $invoiceData['amount_due']
            ]);
            
            return $invoiceId;
            
        } catch (\Exception $e) {
            Log::error('Failed to create invoice from wizard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'wizard_data' => $wizardData
            ]);
            throw $e;
        }
    }

    /**
     * Create detailed invoice line items based on wizard subscription data
     */
    protected function createInvoiceLineItems($invoiceId, array $wizardData)
    {
        $pricingBreakdown = $wizardData['pricing_breakdown'] ?? [];
        $subscriptionDetails = $wizardData['subscription_details'] ?? [];
        $selectedDevices = $wizardData['selected_devices'] ?? [];
        $biometricServices = $wizardData['selected_biometric_services'] ?? [];
        
        $lineItems = [];
        $billingPeriod = $subscriptionDetails['billing_period'] ?? 'monthly';
        $planName = ucfirst($subscriptionDetails['plan_slug'] ?? 'Unknown');
        $systemName = ucfirst($subscriptionDetails['system_slug'] ?? 'Timora');

        // 1. Base Subscription Fee
        if (($pricingBreakdown['base_price'] ?? 0) > 0) {
            $lineItems[] = [
                'invoice_id' => $invoiceId,
                'description' => "{$systemName} {$planName} Plan - " . ucfirst($billingPeriod) . " Subscription",
                'quantity' => 1,
                'rate' => $pricingBreakdown['base_price'],
                'amount' => $pricingBreakdown['base_price'],
                'period' => $billingPeriod,
                'metadata' => json_encode(['type' => 'base_subscription', 'plan' => $subscriptionDetails['plan_slug']]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // 2. Additional Employees
        if (($pricingBreakdown['extra_cost'] ?? 0) > 0) {
            $totalEmployees = $subscriptionDetails['total_employees'] ?? 0;
            $planDefaults = $this->getPlanDefaults();
            $includedEmployees = $planDefaults[$subscriptionDetails['plan_slug']]['included_employees'] ?? 0;
            $extraEmployees = max(0, $totalEmployees - $includedEmployees);
            
            if ($extraEmployees > 0) {
                $lineItems[] = [
                    'invoice_id' => $invoiceId,
                    'description' => "Additional Employees ({$extraEmployees} employees)",
                    'quantity' => $extraEmployees,
                    'rate' => 49.00, // Standard rate per additional employee
                    'amount' => $pricingBreakdown['extra_cost'],
                    'period' => $billingPeriod,
                    'metadata' => json_encode(['type' => 'additional_employees', 'employee_count' => $extraEmployees]),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // 3. Mobile App Access
        if (($pricingBreakdown['mobile_cost'] ?? 0) > 0) {
            $mobileUsers = $subscriptionDetails['mobile_app_users'] ?? 0;
            $lineItems[] = [
                'invoice_id' => $invoiceId,
                'description' => "Mobile App Access ({$mobileUsers} users)",
                'quantity' => $mobileUsers,
                'rate' => 49.00, // Standard rate per mobile user
                'amount' => $pricingBreakdown['mobile_cost'],
                'period' => $billingPeriod,
                'metadata' => json_encode(['type' => 'mobile_access', 'user_count' => $mobileUsers]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // 4. Monthly Add-ons
        if (($pricingBreakdown['addon_cost'] ?? 0) > 0) {
            $addons = $subscriptionDetails['selected_addons'] ?? [];
            $addonPrices = $this->getAddonPrices();
            
            foreach ($addons as $addonKey) {
                if (isset($addonPrices[$addonKey]) && $addonPrices[$addonKey]['type'] === 'monthly') {
                    $addonPrice = $addonPrices[$addonKey]['price'];
                    $lineItems[] = [
                        'invoice_id' => $invoiceId,
                        'description' => $this->getAddonDescription($addonKey) . " - Monthly",
                        'quantity' => 1,
                        'rate' => $addonPrice,
                        'amount' => $addonPrice,
                        'period' => $billingPeriod,
                        'metadata' => json_encode(['type' => 'addon_monthly', 'addon_key' => $addonKey]),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
        }

        // 5. One-time Add-ons
        if (($pricingBreakdown['one_time_addon_cost'] ?? 0) > 0) {
            $addons = $subscriptionDetails['selected_addons'] ?? [];
            $addonPrices = $this->getAddonPrices();
            
            foreach ($addons as $addonKey) {
                if (isset($addonPrices[$addonKey]) && $addonPrices[$addonKey]['type'] === 'one-time') {
                    $addonPrice = $addonPrices[$addonKey]['price'];
                    $lineItems[] = [
                        'invoice_id' => $invoiceId,
                        'description' => $this->getAddonDescription($addonKey) . " - One-time Setup",
                        'quantity' => 1,
                        'rate' => $addonPrice,
                        'amount' => $addonPrice,
                        'period' => 'one-time',
                        'metadata' => json_encode(['type' => 'addon_onetime', 'addon_key' => $addonKey]),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
        }

        // 6. Biometric Devices
        if (!empty($selectedDevices)) {
            foreach ($selectedDevices as $device) {
                $deviceName = $device['name'] ?? 'Unknown Device';
                $devicePrice = (float) ($device['price'] ?? 0);
                $deviceQuantity = (int) ($device['quantity'] ?? 0);
                $deviceTotal = $devicePrice * $deviceQuantity;
                
                if ($deviceQuantity > 0 && $devicePrice > 0) {
                    $lineItems[] = [
                        'invoice_id' => $invoiceId,
                        'description' => $deviceName . " - Biometric Device",
                        'quantity' => $deviceQuantity,
                        'rate' => $devicePrice,
                        'amount' => $deviceTotal,
                        'period' => 'one-time',
                        'metadata' => json_encode(['type' => 'biometric_device', 'device' => $device]),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
        }

        // 7. Biometric Services
        if (!empty($biometricServices)) {
            $servicesBreakdown = $pricingBreakdown['services_breakdown'] ?? [];
            
            foreach ($servicesBreakdown as $service) {
                if (($service['cost'] ?? 0) > 0) {
                    $lineItems[] = [
                        'invoice_id' => $invoiceId,
                        'description' => $service['service'] ?? 'Biometric Service',
                        'quantity' => 1,
                        'rate' => $service['cost'],
                        'amount' => $service['cost'],
                        'period' => 'one-time',
                        'metadata' => json_encode(['type' => 'biometric_service', 'service_data' => $service]),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
        }

        // 8. Implementation Fee
        if (($pricingBreakdown['implementation_fee'] ?? 0) > 0) {
            $lineItems[] = [
                'invoice_id' => $invoiceId,
                'description' => "Implementation & Setup Fee",
                'quantity' => 1,
                'rate' => $pricingBreakdown['implementation_fee'],
                'amount' => $pricingBreakdown['implementation_fee'],
                'period' => 'one-time',
                'metadata' => json_encode(['type' => 'implementation_fee']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // Insert all line items
        if (!empty($lineItems)) {
            DB::table('invoice_items')->insert($lineItems);
            Log::info('Invoice line items created', [
                'invoice_id' => $invoiceId,
                'line_items_count' => count($lineItems)
            ]);
        }
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber()
    {
        $lastInvoice = DB::table('invoices')->orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -6)) + 1 : 1;
        return 'INV-' . date('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate billing period dates
     */
    protected function calculatePeriodDates($billingCycle)
    {
        $start = Carbon::now()->toDateString();
        
        if ($billingCycle === 'yearly' || $billingCycle === 'annually' || $billingCycle === 'annual') {
            $end = Carbon::now()->addYear()->subDay()->toDateString();
        } else {
            $end = Carbon::now()->addMonth()->subDay()->toDateString();
        }
        
        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get plan defaults for employee calculations
     */
    protected function getPlanDefaults()
    {
        return [
            'free' => ['included_employees' => 2],
            'starter' => ['included_employees' => 1],
            'core' => ['included_employees' => 21],
            'pro' => ['included_employees' => 101],
            'elite' => ['included_employees' => 201],
        ];
    }

    /**
     * Get addon pricing configuration
     */
    protected function getAddonPrices()
    {
        return [
            'custom_logo' => ['price' => 3999, 'type' => 'one-time'],
            'geofencing' => ['price' => 10000, 'type' => 'monthly'],
            'email' => ['price' => 456, 'type' => 'monthly'],
            'biometric_installation' => ['price' => 9999, 'type' => 'one-time'],
            'biometric_integration' => ['price' => 10000, 'type' => 'one-time'],
        ];
    }

    /**
     * Get human-readable addon descriptions
     */
    protected function getAddonDescription($addonKey)
    {
        $descriptions = [
            'custom_logo' => 'Custom Logo Branding',
            'geofencing' => 'Geofencing Location Tracking',
            'email' => 'Email Integration Service',
            'biometric_installation' => 'Biometric Installation Service',
            'biometric_integration' => 'Biometric Integration Service',
        ];
        
        return $descriptions[$addonKey] ?? ucfirst(str_replace('_', ' ', $addonKey));
    }
}