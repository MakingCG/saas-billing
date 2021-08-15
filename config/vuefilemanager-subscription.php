<?php

return [
    // Implemented drivers: stripe, flutter-wave
    'driver' => env('SUBSCRIPTION_DRIVER', 'stripe'),

    'credentials' => [
        'stripe' => [
            'secret' => env('STRIPE_SECRET'),
        ],
        'flutter-wave' => [
            'secret' => env('FLUTTER_WAVE_SECRET'),
        ],
    ],
];
