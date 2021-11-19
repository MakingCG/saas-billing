# Subscription module for all great payment gateway

## Installation

You can install the package via composer:

```bash
composer require VueFileManager/subscription
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="VueFileManager\\Subscription\SubscriptionServiceProvider" --tag="subscription-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="VueFileManager\\Subscription\SubscriptionServiceProvider" --tag="subscription-config"
```

This is the contents of the published config file:

## Functions
### Subscription
Get all active features under plan:
```bash
$user->subscription->features();
```
Get single feature under plan:
```bash
$user->subscription->feature('max_storage_amount');
```
Determine if user has subscription
```bash
$user->hasSubscription()
```
## Testing

```bash
composer test
```

## Credits

- [Peter Papp](https://github.com/MakingCG)
