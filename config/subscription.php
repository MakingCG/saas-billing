<?php

return [
    // Implemented drivers: stripe, paystack
    'driver' => env('SUBSCRIPTION_DRIVER', 'stripe'),

    'default_currency' => env('SUBSCRIPTION_DEFAULT_CURRENCY', 'USD'),

    'available_drivers' => [
        //'paystack',
        //'paypal',
        'stripe',
    ],

    'credentials' => [
        'stripe'   => [
            'secret'     => env('STRIPE_SECRET_KEY'),
            'public_key' => env('STRIPE_PUBLIC_KEY'),
        ],
        'paystack' => [
            'secret'     => env('PAYSTACK_SECRET'),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        ],
        'paypal'   => [
            'id'     => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_CLIENT_SECRET'),
        ],
    ],
];
