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
<<<<<<< HEAD
            'included_employees' => 1,
            'max_employees' => 20,
            'base_price' => 999,
=======
            'included_employees' => 10,  // Fixed: 10 employees included
            'max_employees' => 20,
            'base_price' => 5000,        // Fixed: ₱5,000 base price
>>>>>>> 7aac2cc3 (invoice testing for alignment)
            'implementation_fee' => 4999,
        ],
        'core' => [
            'included_employees' => 21,
            'max_employees' => 100,
<<<<<<< HEAD
            'base_price' => 3999,
=======
            'base_price' => 5549,        // Fixed: ₱5,549 base price
>>>>>>> 7aac2cc3 (invoice testing for alignment)
            'implementation_fee' => 14999,
        ],
        'pro' => [
            'included_employees' => 101,
            'max_employees' => 200,
<<<<<<< HEAD
            'base_price' => 9999,
=======
            'base_price' => 9549,        // Fixed: ₱9,549 base price
>>>>>>> 7aac2cc3 (invoice testing for alignment)
            'implementation_fee' => 39999,
        ],
        'elite' => [
            'included_employees' => 201,
            'max_employees' => 500,
<<<<<<< HEAD
            'base_price' => 19999,
=======
            'base_price' => 14549,       // Fixed: ₱14,549 base price
>>>>>>> 7aac2cc3 (invoice testing for alignment)
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
<<<<<<< HEAD
=======
        'wall_mounted' => [
            'name' => 'Wall Mounted Installation',
            'price' => 9999,
            'type' => 'one-time',
            'description' => 'Standard wall mounting installation for biometric devices',
        ],
        'door_access' => [
            'name' => 'Door Access Installation',
            'price' => 14999,
            'type' => 'one-time',
            'description' => 'Complete door access control system installation',
        ],
        // Legacy addon keys for backward compatibility
>>>>>>> 7aac2cc3 (invoice testing for alignment)
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
<<<<<<< HEAD
=======
    | Biometric Device Configuration
    |--------------------------------------------------------------------------
    |
    | Biometric devices available in the wizard with exact pricing
    |
    */
    
    'biometric_devices' => [
        1 => [
            'name' => 'ZKTeco eFace10',
            'price' => 13798.40,
            'capacity' => '1,000 users',
            'features' => ['Face Recognition', 'Access Control'],
        ],
        2 => [
            'name' => 'ZKTeco IN05 / IN05-A',
            'price' => 21481.60,
            'capacity' => '3,000 users',
            'features' => ['Fingerprint Recognition', 'PIN Code'],
        ],
        3 => [
            'name' => 'ZKTeco MB10-VL',
            'price' => 9094.40,
            'capacity' => '500 users',
            'features' => ['Fingerprint Recognition', 'Face Recognition', 'Access Control'],
        ],
        4 => [
            'name' => 'ZKTeco MB560-VL',
            'price' => 22892.80,
            'capacity' => '3,000 users',
            'features' => ['Fingerprint Recognition', 'Face Recognition'],
        ],
        5 => [
            'name' => 'ZKTeco SenseFace 2A',
            'price' => 10035.20,
            'capacity' => '3,000 users',
            'features' => ['Face Recognition', 'Fingerprint Recognition', 'Access Control'],
        ],
        6 => [
            'name' => 'ZKTeco SF400',
            'price' => 20898.30,
            'capacity' => '1,500 users',
            'features' => ['Fingerprint Recognition', 'Access Control'],
        ],
        7 => [
            'name' => 'ZKTeco UA860',
            'price' => 15366.00,
            'capacity' => '3,000 users',
            'features' => ['Fingerprint Recognition', 'TCP/IP Communication'],
        ],
        8 => [
            'name' => 'ZKTeco SpeedFace V3L',
            'price' => 18502.40,
            'capacity' => '500 users',
            'features' => ['Face Recognition', 'Fingerprint Recognition', 'Access Control'],
            'badges' => ['Most Popular'],
        ],
        9 => [
            'name' => 'ZKTeco SpeedFace V5L',
            'price' => 34809.60,
            'capacity' => '6,000 users',
            'features' => ['Face Recognition', 'Fingerprint Recognition'],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
>>>>>>> 7aac2cc3 (invoice testing for alignment)
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