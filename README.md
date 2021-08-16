# Subscription module for all great payment gateway

## Installation

You can install the package via composer:

```bash
composer require VueFileManager/subscription
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Makingcg\\Subscription\SubscriptionServiceProvider" --tag="subscription-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Makingcg\\Subscription\SubscriptionServiceProvider" --tag="subscription-config"
```

This is the contents of the published config file:

```php
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
```

## Testing

```bash
composer test
```

## Credits

- [Peter Papp](https://github.com/MakingCG)
