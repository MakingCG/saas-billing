<?php

return [
    /*
     * Get gateway credentials
     */
    'credentials'       => [
        'stripe'   => [
            'secret'      => env('STRIPE_SECRET_KEY'),
            'public_key'  => env('STRIPE_PUBLIC_KEY'),
            'webhook_key' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'paystack' => [
            'secret'     => env('PAYSTACK_SECRET'),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        ],
        'paypal'   => [
            'id'         => env('PAYPAL_CLIENT_ID'),
            'secret'     => env('PAYPAL_CLIENT_SECRET'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
            'is_live'    => env('PAYPAL_IS_LIVE'),
        ],
    ],

    /*
     * App default middlewares. Rewrite if you need your custom middleware
     */
    'middlewares' => [
        'admin' => \VueFileManager\Subscription\Support\Middleware\AdminCheck::class,
    ],

    /*
     * App default notifications. Rewrite if you need your custom notification
     */
    'notifications' => [
        'ChargeFromCreditCardFailedAgainNotification' => \VueFileManager\Subscription\Domain\FailedPayments\Notifications\ChargeFromCreditCardFailedAgainNotification::class,
        'ConfirmStripePaymentNotification'            => \VueFileManager\Subscription\Support\Miscellaneous\Stripe\Notifications\ConfirmStripePaymentNotification::class,
        'ChargeFromCreditCardFailedNotification'      => \VueFileManager\Subscription\Domain\FailedPayments\Notifications\ChargeFromCreditCardFailedNotification::class,
        'SubscriptionWasCreatedNotification'          => \VueFileManager\Subscription\Domain\Subscriptions\Notifications\SubscriptionWasCreatedNotification::class,
        'BillingAlertTriggeredNotification'           => \VueFileManager\Subscription\Domain\BillingAlerts\Notifications\BillingAlertTriggeredNotification::class,
        'InsufficientBalanceNotification'             => \VueFileManager\Subscription\Domain\Credits\Notifications\InsufficientBalanceNotification::class,
        'BonusCreditAddedNotification'                => \VueFileManager\Subscription\Domain\Credits\Notifications\BonusCreditAddedNotification::class,
    ],

    'metered_billing'   => [
        'settlement_period' => 30,
    ],

    'paystack'          => [
        /*
         * List of allowed ip address to verify paystack webhook request
         */
        'allowed_ips' => [
            '52.214.14.220',
            '52.49.173.169',
            '52.31.139.75',
        ],
    ],

    'is_demo' => env('APP_DEMO', false),
];
