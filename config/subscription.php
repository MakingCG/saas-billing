<?php

return [
    // Implemented drivers: stripe, paystack
    'driver' => env('SUBSCRIPTION_DRIVER', 'paystack'),

    'default_currency' => env('SUBSCRIPTION_DEFAULT_CURRENCY', 'USD'),

    'available_drivers' => [
        'paypal', 'paystack',
    ],

    'credentials' => [
        'stripe'   => [
            'secret' => env('STRIPE_SECRET'),
        ],
        'paystack' => [
            'secret'     => env('PAYSTACK_SECRET'),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        ],
        'paypal'   => [
            'id'     => env('CLIENT_ID'),
            'secret' => env('CLIENT_SECRET'),
        ],
    ],
];
