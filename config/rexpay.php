<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Operating Mode
    |--------------------------------------------------------------------------
    | 'test' for sandbox, 'production' for live processing.
    */
    'mode' => getenv('REXPAY_MODE') ?: 'test',

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'sandbox' => [
            'pgs'        => 'https://pgs-sandbox.globalaccelerex.com/api/pgs',
            'cps'        => 'https://pgs-sandbox.globalaccelerex.com/api/cps/v1',
            'public_key' => 'https://pgs-sandbox.globalaccelerex.com/api/pgs/clients/v1/publicKey',
            'checkout'   => 'https://checkout-test.myrexpay.ng/pay',
        ],
        'production' => [
            'pgs'        => 'https://pgs.globalaccelerex.com/api/pgs',
            'cps'        => 'https://cps.globalaccelerex.com/api/cps/v1',
            'public_key' => 'https://pgs.globalaccelerex.com/api/pgs/clients/v1/publicKey',
            'checkout'   => 'https://checkout.myrexpay.ng/pay',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Settlement
    |--------------------------------------------------------------------------
    | Platform fee charged to merchants. Default is 0% (direct settlement).
    */
    'settlement' => [
        'platform_fee_percent' => 0.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    */
    'onboarding' => [
        'auto_sync_kyc' => true,
        'required_fields' => [
            'business_name',
            'email',
            'phone',
            'account_number',
            'bank_code',
            'bvn',
        ],
        'signup_url' => 'https://myrexpay.ng/auth/register',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Verification
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'signature_header' => 'HTTP_X_REXPAY_SIGNATURE',
        'algorithm'        => 'sha512',
    ],
];
