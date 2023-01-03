<?php

return [
    'controller' => '\mykholy\PayMob\Controllers\DummyController',
    'accept'     => [
        'api_key'         => env('ACCEPT_API_KEY'),
        'merchant_id'     => env('ACCEPT_MERCHANT_ID'),
        'delivery_needed' => false,
        'conversion_rate' => 100, // cents
        'currency'        => 'EGP',
        'exp_after'       => 10, // seconds
        'min_amount'      => 5, // pounds
        'url'             => [
            'token'       => 'https://accept.paymobsolutions.com/api/auth/tokens',
            'order'       => 'https://accept.paymobsolutions.com/api/ecommerce/orders',
            'payment_key' => 'https://accept.paymobsolutions.com/api/acceptance/payment_keys',
            'refund'      => 'https://accept.paymobsolutions.com/api/acceptance/void_refund/refund',
            'hmac'        => 'https://accept.paymobsolutions.com/api/acceptance/transactions',
        ],
        'payment_types' => [
            'card_payment' => [
                'url'            => 'https://accept.paymobsolutions.com/api/acceptance/iframes/' . env('ACCEPT_CARD_IFRAME_ID'),
                'integration_id' => env('ACCEPT_CARD_INTEGRATION_ID'),
            ],
            'mobile_wallet' => [
                'url'            => 'https://accept.paymobsolutions.com/api/acceptance/payments/pay',
                'integration_id' => env('ACCEPT_MW_INTEGRATION_ID'),
            ],
        ],
    ],
    'payout' => [
        'auth' => env('APP_ENV') == 'local' ? 'https://stagingpayouts.paymobsolutions.com/api/secure/o/token/' : 'https://payouts.paymobsolutions.com/api/secure/o/token/',
        'budget' => env('APP_ENV') == 'local' ? 'https://stagingpayouts.paymobsolutions.com/api/secure/budget/inquire/' : 'https://payouts.paymobsolutions.com/api/secure/budget/inquire/',
        'payout' => env('APP_ENV') == 'local' ? 'https://stagingpayouts.paymobsolutions.com/api/secure/disburse/' : 'https://payouts.paymobsolutions.com/api/secure/disburse/',
        'client_id' => env('APP_ENV') == 'local' ? env('STAGING_ACCEPT_PAYOUT_CLIENT_ID') : env('ACCEPT_PAYOUT_CLIENT_ID'),
        'client_secret' => env('APP_ENV') == 'local' ? env('STAGING_ACCEPT_PAYOUT_CLIENT_SECRET') : env('ACCEPT_PAYOUT_CLIENT_SECRET'),
        'username' => env('APP_ENV') == 'local' ? env('STAGING_ACCEPT_PAYOUT_USERNAME') : env('ACCEPT_PAYOUT_USERNAME'),
        'password' => env('APP_ENV') == 'local' ? env('STAGING_ACCEPT_PAYOUT_PASSWORD') : env('ACCEPT_PAYOUT_PASSWORD'),
    ],
    'payout_fee_percentage' => [
        'bank_card' => 0.1,
        'wallet' => 2,
    ],
    'payout_fee_boundaries_for_bank_card' => [
        'min' => 30, // L.E
        'max' => 250, // L.E
    ],

    'accept_payin_fixed_fee' => 3, // L.E for each payin transaction

    'payout_log_email' => env('PAYOUT_LOG_EMAIL'),
];
