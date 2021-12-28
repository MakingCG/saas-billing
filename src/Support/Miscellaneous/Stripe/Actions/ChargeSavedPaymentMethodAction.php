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
    public function __invoke()
    {
        // Get payment method
        $paymentMethods = $this->get('/payment_methods?customer=cus_KpRxjQhC61rSgd&type=card');

        // TODO: add functionality to handle multiple saved cards
        $defaultPaymentMethodCode = $paymentMethods->json()['data'][0]['id'];

        // Create payment intent
        $paymentIntent = $this->post('/payment_intents', [
            'amount'         => 1899,
            'currency'       => 'usd',
            'customer'       => 'cus_KpRxjQhC61rSgd',
            'payment_method' => $defaultPaymentMethodCode,
            'off_session'    => 'true',
            'confirm'        => 'true',
        ]);
    }
}
