<?php
namespace Support\Miscellaneous\Stripe\Actions;

use VueFileManager\Subscription\Support\Services\StripeHttpClient;

class ChargeSavedPaymentMethodAction
{
    use StripeHttpClient;

    /**
     * It gets stored payment method and charge custom amount of money from customer
     * TODO: make a test
     */
    public function __invoke($user, $amount)
    {
        // Get payment method
        $paymentMethodCode = $user->creditCards()->first()->reference;

        // Create payment intent
        $paymentIntent = $this->post('/payment_intents', [
            'amount'         => $amount,
            'currency'       => 'usd', // TODO: set currency
            'customer'       => $user->customerId('stripe'),
            'payment_method' => $paymentMethodCode,
            'off_session'    => 'true',
            'confirm'        => 'true',
        ]);
    }
}
