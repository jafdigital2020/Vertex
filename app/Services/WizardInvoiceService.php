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
            
            // Calculate subtotal (excluding VAT)
            $recurringTotal = $pricingBreakdown['recurring_total'] ?? 0;
            $implementationFee = $pricingBreakdown['implementation_fee'] ?? 0;
            $oneTimeAddonCost = $pricingBreakdown['one_time_addon_cost'] ?? 0;
            $biometricCosts = ($pricingBreakdown['biometric_device_cost'] ?? 0) + ($pricingBreakdown['biometric_services_cost'] ?? 0);
            $subtotal = $recurringTotal + $implementationFee + $oneTimeAddonCost + $biometricCosts;
            
            // Prepare invoice data with enhanced mapping
            $invoiceData = [
                'tenant_id' => $tenantId,
                'subscription_id' => $subscription->id,
                'invoice_type' => 'subscription',
                'billing_cycle' => $billingCycle,
                'invoice_number' => $invoiceNumber,
                'amount_due' => $pricingBreakdown['total_amount'] ?? 0,
                'amount_paid' => $pricingBreakdown['total_amount'] ?? 0, // Paid in full
                'subscription_amount' => $pricingBreakdown['recurring_total'] ?? $pricingBreakdown['monthly_subtotal'] ?? 0,
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
                'implementation_fee' => $implementationFee,
                'vat_amount' => $pricingBreakdown['vat'] ?? 0,
                'vat_percentage' => 12.00,
                'subtotal' => $subtotal,
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
        
        // If pricing breakdown doesn't have detailed fields, calculate them
        if (empty($pricingBreakdown['base_price']) && isset($subscriptionDetails['plan_slug'])) {
            $pricingBreakdown = $this->calculateDetailedPricing($subscriptionDetails, $selectedDevices, $biometricServices);
        }
        
        $lineItems = [];
        $billingPeriod = $subscriptionDetails['billing_period'] ?? 'monthly';
        $planName = ucfirst($subscriptionDetails['plan_slug'] ?? 'Unknown');
        $systemName = ucfirst($subscriptionDetails['system_slug'] ?? 'Timora');
        
        // Calculate period dates
        $periodStart = date('n/j/Y');
        if ($billingPeriod === 'yearly') {
            $periodEnd = date('n/j/Y', strtotime('+1 year'));
        } else {
            $periodEnd = date('n/j/Y', strtotime('+1 month'));
        }

        // 1. Implementation Fee (show first)
        if (($pricingBreakdown['implementation_fee'] ?? 0) > 0) {
            $lineItems[] = [
                'invoice_id' => $invoiceId,
                'description' => "Implementation Fee",
                'quantity' => 1,
                'rate' => $pricingBreakdown['implementation_fee'],
                'amount' => $pricingBreakdown['implementation_fee'],
                'period' => "$periodStart - $periodEnd",
                'metadata' => json_encode(['type' => 'implementation_fee']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // 2. Base Subscription Fee
        if (($pricingBreakdown['base_price'] ?? 0) > 0) {
            // Get the actual plan base price from config
            $planDefaults = $this->getPlanDefaults();
            $planSlug = $subscriptionDetails['plan_slug'] ?? 'unknown';
            $configBasePrice = $planDefaults[$planSlug]['base_price'] ?? $pricingBreakdown['base_price'];
            
            // Use config price if available, otherwise use wizard price
            $actualBasePrice = $configBasePrice > 0 ? $configBasePrice : $pricingBreakdown['base_price'];
            
            $lineItems[] = [
                'invoice_id' => $invoiceId,
                'description' => "{$planName} Monthly Plan Subscription",
                'quantity' => 1,
                'rate' => $actualBasePrice,
                'amount' => $actualBasePrice,
                'period' => $billingPeriod,
                'metadata' => json_encode([
                    'type' => 'base_subscription', 
                    'plan' => $subscriptionDetails['plan_slug'],
                    'config_base_price' => $configBasePrice,
                    'wizard_base_price' => $pricingBreakdown['base_price'],
                    'actual_base_price' => $actualBasePrice
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // 2. Additional Employees
        $totalEmployees = $subscriptionDetails['total_employees'] ?? 0;
        $planDefaults = $this->getPlanDefaults();
        $planSlug = $subscriptionDetails['plan_slug'] ?? 'unknown';
        $includedEmployees = $planDefaults[$planSlug]['included_employees'] ?? 0;
        
        // Calculate extra employees even if extra_cost is 0 (wizard might bundle costs)
        $extraEmployees = max(0, $totalEmployees - $includedEmployees);
        
        if ($extraEmployees > 0) {
            $extraUserRate = config('wizard.rates.extra_user_rate', 49);
            $extraEmployeeCost = $extraEmployees * $extraUserRate;
            
            // Use explicit extra_cost if provided, otherwise calculate it
            $actualExtraCost = ($pricingBreakdown['extra_cost'] ?? 0) > 0 
                ? $pricingBreakdown['extra_cost'] 
                : $extraEmployeeCost;
                
            $lineItems[] = [
                'invoice_id' => $invoiceId,
                'description' => "Additional Employees ({$extraEmployees} employees beyond {$includedEmployees} included)",
                'quantity' => $extraEmployees,
                'rate' => $extraUserRate,
                'amount' => $actualExtraCost,
                'period' => $billingPeriod,
                'metadata' => json_encode([
                    'type' => 'additional_employees', 
                    'employee_count' => $extraEmployees,
                    'total_employees' => $totalEmployees,
                    'included_employees' => $includedEmployees,
                    'rate_per_employee' => $extraUserRate,
                    'calculated_cost' => $extraEmployeeCost,
                    'actual_cost' => $actualExtraCost
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // 3. Mobile App Access
        $mobileUsers = $subscriptionDetails['mobile_app_users'] ?? 0;
        $mobileAccess = $subscriptionDetails['mobile_access'] ?? false;
        
        if ($mobileAccess && $mobileUsers > 0) {
            $mobileAppRate = config('wizard.rates.mobile_app_rate', 49);
            $calculatedMobileCost = $mobileUsers * $mobileAppRate;
            
            // Use explicit mobile_cost if provided, otherwise calculate it
            $actualMobileCost = ($pricingBreakdown['mobile_cost'] ?? 0) > 0 
                ? $pricingBreakdown['mobile_cost'] 
                : $calculatedMobileCost;
                
            $lineItems[] = [
                'invoice_id' => $invoiceId,
                'description' => "Mobile App Access ({$mobileUsers} users)",
                'quantity' => $mobileUsers,
                'rate' => $mobileAppRate,
                'amount' => $actualMobileCost,
                'period' => $billingPeriod,
                'metadata' => json_encode([
                    'type' => 'mobile_access', 
                    'user_count' => $mobileUsers,
                    'rate_per_user' => $mobileAppRate,
                    'calculated_cost' => $calculatedMobileCost,
                    'actual_cost' => $actualMobileCost
                ]),
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
        $addons = $subscriptionDetails['selected_addons'] ?? [];
        $addonPrices = $this->getAddonPrices();
        $totalOneTimeAddonCost = $pricingBreakdown['one_time_addon_cost'] ?? 0;
        
        if (!empty($addons)) {
            $oneTimeAddons = array_filter($addons, function($addonKey) use ($addonPrices) {
                return isset($addonPrices[$addonKey]) && $addonPrices[$addonKey]['type'] === 'one-time';
            });
            
            if (!empty($oneTimeAddons)) {
                $calculatedTotal = 0;
                foreach ($oneTimeAddons as $addonKey) {
                    $calculatedTotal += $addonPrices[$addonKey]['price'];
                }
                
                // If wizard provided a total, distribute proportionally, otherwise use config prices
                foreach ($oneTimeAddons as $addonKey) {
                    $configPrice = $addonPrices[$addonKey]['price'];
                    
                    if ($totalOneTimeAddonCost > 0 && $calculatedTotal > 0) {
                        // Distribute total cost proportionally
                        $proportion = $configPrice / $calculatedTotal;
                        $actualPrice = $totalOneTimeAddonCost * $proportion;
                    } else {
                        $actualPrice = $configPrice;
                    }
                    
                    $lineItems[] = [
                        'invoice_id' => $invoiceId,
                        'description' => $this->getAddonDescription($addonKey) . " - One-time Setup",
                        'quantity' => 1,
                        'rate' => $actualPrice,
                        'amount' => $actualPrice,
                        'period' => 'one-time',
                        'metadata' => json_encode([
                            'type' => 'addon_onetime', 
                            'addon_key' => $addonKey,
                            'config_price' => $configPrice,
                            'actual_price' => $actualPrice,
                            'wizard_total' => $totalOneTimeAddonCost
                        ]),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
        }

        // 6. Biometric Devices
        $deviceBreakdown = $pricingBreakdown['device_breakdown'] ?? [];
        if (!empty($deviceBreakdown)) {
            foreach ($deviceBreakdown as $device) {
                $deviceName = $device['name'] ?? 'Unknown Device';
                $devicePrice = (float) ($device['unit_price'] ?? $device['price'] ?? 0);
                $deviceQuantity = (int) ($device['quantity'] ?? 1);
                $deviceTotal = (float) ($device['total_cost'] ?? ($devicePrice * $deviceQuantity));
                
                if ($deviceQuantity > 0 && $deviceTotal > 0) {
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
        } elseif (!empty($selectedDevices)) {
            // Fallback: use selectedDevices if device_breakdown not available
            $biometricDevices = config('wizard.biometric_devices', []);
            
            foreach ($selectedDevices as $device) {
                $deviceId = $device['id'] ?? 0;
                $deviceName = $device['name'] ?? 'Unknown Device';
                
                // Use price from config if device ID matches, otherwise use provided price
                if (isset($biometricDevices[$deviceId])) {
                    $devicePrice = $biometricDevices[$deviceId]['price'];
                    $deviceName = $biometricDevices[$deviceId]['name'];
                } else {
                    $devicePrice = (float) ($device['price'] ?? 0);
                }
                
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
                        'metadata' => json_encode([
                            'type' => 'biometric_device', 
                            'device' => $device,
                            'device_id' => $deviceId,
                            'capacity' => $biometricDevices[$deviceId]['capacity'] ?? null,
                            'features' => $biometricDevices[$deviceId]['features'] ?? []
                        ]),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
        }

        // 7. Biometric Services
        if (!empty($biometricServices)) {
            $servicesBreakdown = $pricingBreakdown['services_breakdown'] ?? [];
            $addonPrices = $this->getAddonPrices();
            
            if (!empty($servicesBreakdown)) {
                // Use detailed services breakdown if available
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
            } else {
                // Fallback: Check for common biometric service patterns
                if (isset($biometricServices['installation']) && $biometricServices['installation']) {
                    $serviceKey = 'wall_mounted'; // Default installation type
                    if (isset($addonPrices[$serviceKey])) {
                        $lineItems[] = [
                            'invoice_id' => $invoiceId,
                            'description' => $addonPrices[$serviceKey]['name'],
                            'quantity' => 1,
                            'rate' => $addonPrices[$serviceKey]['price'],
                            'amount' => $addonPrices[$serviceKey]['price'],
                            'period' => 'one-time',
                            'metadata' => json_encode(['type' => 'biometric_installation', 'service_key' => $serviceKey]),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                }
                
                if (isset($biometricServices['integration']) && $biometricServices['integration']) {
                    $serviceKey = 'biometric_integration'; // Legacy integration service
                    if (isset($addonPrices[$serviceKey])) {
                        $lineItems[] = [
                            'invoice_id' => $invoiceId,
                            'description' => $addonPrices[$serviceKey]['name'],
                            'quantity' => 1,
                            'rate' => $addonPrices[$serviceKey]['price'],
                            'amount' => $addonPrices[$serviceKey]['price'],
                            'period' => 'one-time',
                            'metadata' => json_encode(['type' => 'biometric_integration', 'service_key' => $serviceKey]),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
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
            'starter' => ['included_employees' => 10],  // Fixed: 10 employees included
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
        // Get addon prices from config to ensure alignment
        $configAddons = config('wizard.addons', []);
        
        $addonPrices = [];
        foreach ($configAddons as $key => $addon) {
            $addonPrices[$key] = [
                'price' => $addon['price'],
                'type' => $addon['type'],
                'name' => $addon['name']
            ];
        }
        
        return $addonPrices;
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
            'biometric_device' => 'Biometric Device',
        ];
        
        return $descriptions[$addonKey] ?? ucfirst(str_replace('_', ' ', $addonKey));
    }

    /**
     * Calculate detailed pricing breakdown from subscription details
     * This is used when the pricing_breakdown doesn't contain detailed fields
     */
    protected function calculateDetailedPricing(array $subscriptionDetails, array $selectedDevices, array $biometricServices)
    {
        $planSlug = $subscriptionDetails['plan_slug'] ?? 'starter';
        $billingPeriod = $subscriptionDetails['billing_period'] ?? 'monthly';
        $totalEmployees = (int) ($subscriptionDetails['total_employees'] ?? 0);
        $mobileAppUsers = (int) ($subscriptionDetails['mobile_app_users'] ?? 0);
        $mobileAccess = $subscriptionDetails['mobile_access'] ?? false;
        $selectedAddons = $subscriptionDetails['selected_addons'] ?? [];
        
        // Get plan pricing from config
        $planDefaults = config('wizard.plan_defaults', []);
        $planData = $planDefaults[$planSlug] ?? null;
        
        if (!$planData) {
            // Fallback pricing
            $basePrice = 0;
            $includedEmployees = 10;
            $perEmployeeRate = 49;
            $implementationFee = 14999;
        } else {
            $basePrice = $planData['base_price'] ?? 0;
            $includedEmployees = $planData['included_employees'] ?? 10;
            $perEmployeeRate = config('wizard.rates.extra_user_rate', 49);
            $implementationFee = $planData['implementation_fee'] ?? 14999;
        }
        
        // Calculate extra employee cost
        $extraEmployees = max(0, $totalEmployees - $includedEmployees);
        $extraCost = $extraEmployees * $perEmployeeRate;
        
        // Calculate mobile cost
        $mobileCost = 0;
        if ($mobileAccess && $mobileAppUsers > 0) {
            $mobileCost = $mobileAppUsers * 49; // ₱49 per mobile user
        }
        
        // Calculate addon costs
        $addonCost = 0;
        $oneTimeAddonCost = 0;
        $addonPrices = $this->getAddonPrices();
        
        foreach ($selectedAddons as $addonKey) {
            if (isset($addonPrices[$addonKey])) {
                if ($addonPrices[$addonKey]['type'] === 'monthly') {
                    $addonCost += $addonPrices[$addonKey]['price'];
                } else {
                    $oneTimeAddonCost += $addonPrices[$addonKey]['price'];
                }
            }
        }
        
        // Calculate biometric device costs
        $biometricDeviceCost = 0;
        $deviceBreakdown = [];
        
        if (!empty($selectedDevices)) {
            $biometricDevices = config('wizard.biometric_devices', []);
            
            foreach ($selectedDevices as $device) {
                $deviceId = $device['id'] ?? 0;
                $deviceQuantity = (int) ($device['quantity'] ?? 0);
                
                if (isset($biometricDevices[$deviceId]) && $deviceQuantity > 0) {
                    $devicePrice = $biometricDevices[$deviceId]['price'];
                    $deviceTotal = $devicePrice * $deviceQuantity;
                    $biometricDeviceCost += $deviceTotal;
                    
                    $deviceBreakdown[] = [
                        'name' => $biometricDevices[$deviceId]['name'],
                        'quantity' => $deviceQuantity,
                        'unit_price' => $devicePrice,
                        'total_cost' => $deviceTotal
                    ];
                }
            }
        }
        
        // Calculate biometric services cost
        $biometricServicesCost = 0;
        $servicesBreakdown = [];
        
        if (!empty($biometricServices)) {
            foreach ($biometricServices as $serviceKey => $selected) {
                if ($selected && isset($addonPrices[$serviceKey])) {
                    $serviceCost = $addonPrices[$serviceKey]['price'];
                    $biometricServicesCost += $serviceCost;
                    
                    $servicesBreakdown[] = [
                        'service' => $addonPrices[$serviceKey]['name'],
                        'cost' => $serviceCost
                    ];
                }
            }
        }
        
        // Calculate totals
        $recurringTotal = $basePrice + $extraCost + $mobileCost + $addonCost;
        $monthlySubtotal = $recurringTotal;
        $subtotal = $recurringTotal + $implementationFee + $oneTimeAddonCost + $biometricDeviceCost + $biometricServicesCost;
        $vat = $subtotal * 0.12;
        $totalAmount = $subtotal + $vat;
        
        return [
            'base_price' => $basePrice,
            'extra_cost' => $extraCost,
            'mobile_cost' => $mobileCost,
            'addon_cost' => $addonCost,
            'one_time_addon_cost' => $oneTimeAddonCost,
            'biometric_device_cost' => $biometricDeviceCost,
            'biometric_services_cost' => $biometricServicesCost,
            'implementation_fee' => $implementationFee,
            'recurring_total' => $recurringTotal,
            'monthly_subtotal' => $monthlySubtotal,
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total_amount' => $totalAmount,
            'device_breakdown' => $deviceBreakdown,
            'services_breakdown' => $servicesBreakdown,
        ];
    }
}