<?php

return [
    'driver'            => env('SUBSCRIPTION_DRIVER', 'stripe'),
    'available_drivers' => [
        'paystack',
        'paypal',
        'stripe',
    ],
    'credentials'       => [
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
    'paystack' => [
        'allowed_ips' => [
            '52.31.139.75', '52.49.173.169', '52.214.14.220',
        ],
    ],
];
