<?php

namespace Support\Miscellaneous\Stripe\Actions;

use VueFileManager\Subscription\Support\Services\StripeHttpClient;

class ChargeFromSavedPaymentMethodAction
{
    use StripeHttpClient;

    /**
     * It gets stored payment method and charge custom amount of money from customer
     */
    public function __invoke($user, $amount)
    {
        // Get payment method
        $paymentMethodCode = $user->creditCards()->first()->reference;

        // Get charge amount
        $chargeAmount = intval(round($amount, 2) * 100);

        // Create payment intent
        $paymentIntent = $this->post('/payment_intents', [
            'amount'         => $chargeAmount,
            'currency'       => 'usd', // TODO: set currency
            'customer'       => $user->customerId('stripe'),
            'payment_method' => $paymentMethodCode,
            'off_session'    => 'true',
            'confirm'        => 'true',
        ]);

        // When error
        if (array_key_exists('error', $paymentIntent->json())) {
            // TODO: handle error payment
        }

        // When succeeded
        if (array_key_exists('status', $paymentIntent->json()) && $paymentIntent->json()['status'] === 'succeeded') {

            // Create transaction
            $user->transactions()->create([
                'reference' => $paymentIntent->json()['charges']['data'][0]['id'],
                'type'     => 'charge',
                'status'   => 'completed',
                'note'     => now()->format('d. M') . ' - ' . now()->subDays(config('subscription.settlement_period'))->format('d. M'),
                'currency' => $user->subscription->plan->currency,
                'amount'   => $chargeAmount / 100,
                'driver'   => 'stripe',
            ]);
        }

    }
}
