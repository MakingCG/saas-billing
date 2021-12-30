<?php
namespace VueFileManager\Subscription\Domain\FailedPayments\Actions;

use VueFileManager\Subscription\Domain\FailedPayments\Models\FailedPayment;
use VueFileManager\Subscription\Support\Miscellaneous\Stripe\Exceptions\ChargeFailedException;
use VueFileManager\Subscription\Support\Miscellaneous\Stripe\Actions\ChargeFromSavedPaymentMethodAction;
use VueFileManager\Subscription\Domain\FailedPayments\Notifications\ChargeFromCreditCardFailedAgainNotification;

class RetryChargeFromPaymentCardAction
{
    public function __construct(
        public ChargeFromSavedPaymentMethodAction $chargeFromSavedPaymentMethod,
    ) {
    }

    public function __invoke($user = null)
    {
        FailedPayment::where('source', 'credit-card')
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
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
