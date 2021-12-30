<?php
namespace Domain\FailedPayments\Actions;

use VueFileManager\Subscription\Domain\FailedPayments\Models\FailedPayment;
use Support\Miscellaneous\Stripe\Actions\ChargeFromSavedPaymentMethodAction;
use VueFileManager\Subscription\Support\Miscellaneous\Stripe\Exceptions\ChargeFailedException;
use VueFileManager\Subscription\Domain\FailedPayments\Notifications\ChargeFromCreditCardFailedAgainNotification;

class RetryChargeFromPaymentCardAction
{
    public function __construct(
        public ChargeFromSavedPaymentMethodAction $chargeFromSavedPaymentMethod,
    ) {
    }

    public function __invoke()
    {
        FailedPayment::where('source', 'credit-card')
            ->cursor()
            ->each(function (FailedPayment $payment) {
                try {
                    // Charge from saved credit card
                    $charge = ($this->chargeFromSavedPaymentMethod)(
                        user: $payment->user,
                        amount: $payment->amount,
                    );

                    // Create transaction
                    $payment->user->transactions()->create([
                        'reference' => $charge['charges']['data'][0]['id'],
                        'type'      => 'charge',
                        'status'    => 'completed',
                        'note'      => $payment->note,
                        'currency'  => $payment->currency,
                        'amount'    => $payment->amount,
                        'driver'    => 'stripe',
                    ]);

                    // Delete failed payment record
                    $payment->delete();
                } catch (ChargeFailedException $e) {
                    // Store attempt
                    $payment->increment('attempts');

                    // Send new reminder
                    if ($payment->attempts === 3) {
                        $payment->user->notify(new ChargeFromCreditCardFailedAgainNotification());
                    }
                }
            });
    }
}
