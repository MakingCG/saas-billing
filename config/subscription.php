<?php

return [
    // Implemented drivers: stripe, paystack
    'driver' => env('SUBSCRIPTION_DRIVER', 'stripe'),

    'available_drivers' => [
        'paystack'
    ],

    'credentials' => [
        'stripe' => [
            'secret' => env('STRIPE_SECRET'),
        ],
        'paystack' => [
            'secret' => env('PAYSTACK_SECRET'),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        ],
    ],
];
