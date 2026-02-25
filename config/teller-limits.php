<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Teller Limits
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'teller' => [
            'per_transaction' => env('TELLER_DEFAULT_LIMIT', 10000),
            'daily' => env('TELLER_DAILY_LIMIT', 50000),
        ],
        'senior_teller' => [
            'per_transaction' => env('SENIOR_TELLER_LIMIT', 25000),
            'daily' => env('SENIOR_TELLER_DAILY_LIMIT', 100000),
        ],
        'supervisor' => [
            'per_transaction' => env('SUPERVISOR_LIMIT', 50000),
            'daily' => env('SUPERVISOR_DAILY_LIMIT', 200000),
        ],
        'manager' => [
            'per_transaction' => env('MANAGER_LIMIT', 100000),
            'daily' => env('MANAGER_DAILY_LIMIT', 500000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-approval Settings
    |--------------------------------------------------------------------------
    */
    'auto_approval' => [
        'enabled' => env('AUTO_SUPERVISOR_APPROVAL', true),
        'auto_assign' => env('AUTO_ASSIGN_SUPERVISOR', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'notify_supervisor' => env('NOTIFY_SUPERVISOR_ON_LIMIT', true),
        'notify_teller' => env('NOTIFY_TELLER_ON_APPROVAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hard Limits vs Soft Limits
    |--------------------------------------------------------------------------
    | Hard limits: Transaction cannot proceed without approval
    | Soft limits: Warning but can proceed
    */
    'limit_type' => env('TELLER_LIMIT_TYPE', 'hard'), // hard or soft

    /*
    |--------------------------------------------------------------------------
    | Approval Expiry (in minutes)
    |--------------------------------------------------------------------------
    */
    'approval_expiry' => env('SUPERVISOR_APPROVAL_EXPIRY', 30),

    /*
    |--------------------------------------------------------------------------
    | Excluded Transaction Types
    |--------------------------------------------------------------------------
    | Transaction types that don't require supervisor approval regardless of amount
    */
    'excluded_types' => [
        'initial_deposit',
        'fee_collection',
    ],
];
