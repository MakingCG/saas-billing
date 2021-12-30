<?php
namespace App\Scheduler;

use Illuminate\Support\Collection;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Support\Miscellaneous\Stripe\Actions\ChargeFromSavedPaymentMethodAction;
use VueFileManager\Subscription\Domain\Usage\Actions\SumUsageForCurrentPeriodAction;
use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;
use VueFileManager\Subscription\Domain\Credits\Notifications\InsufficientBalanceNotification;
use VueFileManager\Subscription\Support\Miscellaneous\Stripe\Exceptions\ChargeFailedException;
use VueFileManager\Subscription\Domain\FailedPayments\Notifications\ChargeFromCreditCardFailedNotification;

class SettlePrePaidSubscriptionPeriodSchedule
{
    public function __construct(
        public SumUsageForCurrentPeriodAction $sumUsageForCurrentPeriod,
        public ChargeFromSavedPaymentMethodAction $chargeFromSavedPaymentMethod,
    ) {
    }

    public function __invoke()
    {
        Subscription::where('type', 'pre-paid')
            ->where('status', 'active')
            ->whereDate('renews_at', today())
            ->cursor()
            ->each(function ($subscription) {
                // Get usage estimates
                $usageEstimates = ($this->sumUsageForCurrentPeriod)($subscription);

                // Withdraw from credit card
                if ($subscription->user->creditCards()->exists()) {
                    $this->withdrawFromCreditCard($subscription, $usageEstimates);
                }

                // Withdraw from balance
                if (! $subscription->user->creditCards()->exists()) {
                    $this->withdrawFromBalance($subscription, $usageEstimates);
                }

                // Update next subscription period date
                $subscription->update([
                    'renews_at' => now()->addDays(config('subscription.settlement_period')),
                ]);

                // Reset alert if some exists
                if ($subscription->user->billingAlert()->exists()) {
                    $subscription->user->billingAlert->update([
                        'triggered' => false,
                    ]);
                }
            });
    }

    /**
     * Withdraw from registered user credit card
     */
    protected function withdrawFromCreditCard(Subscription $subscription, Collection $usageEstimates): void
    {
        // Round charge amount
        $chargeAmount = round($usageEstimates->sum('amount'), 2);

        // Withdraw from balance if user has more credits than usage
        if ($subscription->user->balance->amount >= $chargeAmount) {
            $this->withdrawFromBalance($subscription, $usageEstimates);
        }

        // Withdraw from credit card
        if ($subscription->user->balance->amount <= 1) {
            try {
                // Make charge
                $charge = ($this->chargeFromSavedPaymentMethod)(
                    user: $subscription->user,
                    amount: $chargeAmount
                );

                // Create transaction
                $subscription->user->transactions()->create([
                    'reference' => $charge['charges']['data'][0]['id'],
                    'type'      => 'charge',
                    'status'    => 'completed',
                    'note'      => get_metered_charge_period(),
                    'currency'  => $subscription->plan->currency,
                    'amount'    => $chargeAmount,
                    'driver'    => 'stripe',
                ]);
            } catch (ChargeFailedException $e) {
                // Notify user
                $subscription->user->notify(new ChargeFromCreditCardFailedNotification());

                // Create transaction
                $transaction = $subscription->user->transactions()->create([
                    'reference' => null,
                    'type'      => 'charge',
                    'status'    => 'error',
                    'note'      => get_metered_charge_period(),
                    'currency'  => $subscription->plan->currency,
                    'amount'    => $chargeAmount,
                    'driver'    => 'stripe',
                ]);

                // Store failed payment record
                $subscription->user->failedPayments()->create([
                    'currency'       => $subscription->plan->currency,
                    'amount'         => $chargeAmount,
                    'source'         => 'credit-card',
                    'note'           => get_metered_charge_period(),
                ]);
            }
        }
    }

    /**
     * Withdraw from user native balance
     */
    protected function withdrawFromBalance(Subscription $subscription, Collection $usageEstimates): void
    {
        // Round charge amount
        $chargeAmount = round($usageEstimates->sum('amount'), 2);

        try {
            // Make withdrawal
            $subscription->user->withdrawBalance($chargeAmount);

            // Create transaction
            $subscription->user->transactions()->create([
                'type'     => 'withdrawal',
                'status'   => 'completed',
                'currency' => $subscription->plan->currency,
                'amount'   => $chargeAmount,
                'driver'   => 'system',
                'note'     => get_metered_charge_period(),
            ]);
        } catch (InsufficientBalanceException $e) {
            // Notify user
            $subscription->user->notify(new InsufficientBalanceNotification());

            // Create error transaction
            $transaction = $subscription->user->transactions()->create([
                'type'     => 'withdrawal',
                'status'   => 'error',
                'currency' => $subscription->plan->currency,
                'amount'   => $chargeAmount,
                'driver'   => 'system',
                'note'     => get_metered_charge_period(),
            ]);

            // Store failed payment record
            $subscription->user->failedPayments()->create([
                'currency'       => $subscription->plan->currency,
                'amount'         => $chargeAmount,
                'source'         => 'balance',
                'note'           => get_metered_charge_period(),
            ]);
        }
    }
}
