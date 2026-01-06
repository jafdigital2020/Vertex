<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wizard Subscription Data
    |--------------------------------------------------------------------------
    |
    | This configuration holds subscription data passed from the wizard system
    | during the GitHub Actions provisioning process. This data is used to
    | generate accurate invoices that reflect the customer's selections.
    |
    */

    'subscription_data' => env('WIZARD_SUBSCRIPTION_DATA') ? json_decode(env('WIZARD_SUBSCRIPTION_DATA'), true) : null,
    
    /*
    |--------------------------------------------------------------------------
    | Default Plan Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for different plan types used in wizard calculations
    |
    */
    
    'plan_defaults' => [
        'free' => [
            'included_employees' => 2,
            'max_employees' => 2,
            'base_price' => 0,
            'implementation_fee' => 0,
        ],
        'starter' => [
            'included_employees' => 1,
            'max_employees' => 20,
            'base_price' => 999,
            'implementation_fee' => 4999,
        ],
        'core' => [
            'included_employees' => 21,
            'max_employees' => 100,
            'base_price' => 3999,
            'implementation_fee' => 14999,
        ],
        'pro' => [
            'included_employees' => 101,
            'max_employees' => 200,
            'base_price' => 9999,
            'implementation_fee' => 39999,
        ],
        'elite' => [
            'included_employees' => 201,
            'max_employees' => 500,
            'base_price' => 19999,
            'implementation_fee' => 79999,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Addon Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for add-ons that can be selected in the wizard
    |
    */
    
    'addons' => [
        'custom_logo' => [
            'name' => 'Custom Logo Branding',
            'price' => 3999,
            'type' => 'one-time',
            'description' => 'Add your company logo to the system',
        ],
        'geofencing' => [
            'name' => 'Geofencing Location Tracking',
            'price' => 10000,
            'type' => 'monthly',
            'description' => 'Track employee locations with geofencing',
        ],
        'email' => [
            'name' => 'Email Integration Service',
            'price' => 456,
            'type' => 'monthly',
            'description' => 'Send automated emails and notifications',
        ],
        'biometric_installation' => [
            'name' => 'Biometric Installation Service',
            'price' => 9999,
            'type' => 'one-time',
            'description' => 'Professional biometric device installation',
        ],
        'biometric_integration' => [
            'name' => 'Biometric Integration Service',
            'price' => 10000,
            'type' => 'one-time',
            'description' => 'Integrate biometric devices with the system',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Rate Configuration
    |--------------------------------------------------------------------------
    |
    | Standard rates used across the system
    |
    */
    
    'rates' => [
        'extra_user_rate' => 49,
        'mobile_app_rate' => 49,
        'vat_rate' => 0.12,
        'annual_discount' => 0.05, // 5% discount for annual billing
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Billing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for billing periods and cycles
    |
    */
    
    'billing' => [
        'supported_periods' => ['monthly', 'annually', 'yearly'],
        'default_period' => 'monthly',
        'grace_period_days' => 7,
    ],
];