<?php

namespace App\Scheduler;

use Illuminate\Support\Collection;
use Support\Miscellaneous\Stripe\Actions\ChargeFromSavedPaymentMethodAction;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Usage\Actions\SumUsageForCurrentPeriodAction;
use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;
use VueFileManager\Subscription\Domain\Credits\Notifications\InsufficientBalanceNotification;

class SettlePrePaidSubscriptionPeriodSchedule
{
    public function __construct(
        public SumUsageForCurrentPeriodAction     $sumUsageForCurrentPeriod,
        public ChargeFromSavedPaymentMethodAction $chargeFromSavedPaymentMethod,
    ) {}

    public function __invoke()
    {
        Subscription::where('type', 'pre-paid')
            ->where('status', 'active')
            ->whereDate('renews_at', today())
            ->cursor()
            ->each(function ($subscription) {
                // Get usage estimates
                $usageEstimates = ($this->sumUsageForCurrentPeriod)($subscription);

                // Withdraw from card
                if ($subscription->user->creditCards()->exists()) {
                    $this->withdrawFromCreditCard($subscription, $usageEstimates);
                }

                // Withdraw from balance
                if (!$subscription->user->creditCards()->exists()) {
                    $this->withdrawFromBalance($subscription, $usageEstimates);
                }

                // Update next subscription period date
                $subscription->update([
                    'renews_at' => now()->addDays(
                        config('subscription.settlement_period')
                    ),
                ]);

                // Reset alert
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
        // Withdraw from balance if user has more credits than usage
        if ($subscription->user->balance->amount > $usageEstimates->sum('amount')) {
            $this->withdrawFromBalance($subscription, $usageEstimates);
        }

        // Withdraw from credit card
        if ($subscription->user->balance->amount <= 1) {
            ($this->chargeFromSavedPaymentMethod)(
                user: $subscription->user,
                amount: $usageEstimates->sum('amount')
            );
        }
    }

    /**
     * Withdraw from user native balance
     */
    protected function withdrawFromBalance(Subscription $subscription, Collection $usageEstimates): void
    {
        try {
            // Make withdrawal
            $subscription->user->withdrawBalance($usageEstimates->sum('amount'));

            // Create transaction
            $subscription->user->transactions()->create([
                'type'     => 'withdrawal',
                'status'   => 'completed',
                'currency' => $subscription->plan->currency,
                'amount'   => $usageEstimates->sum('amount'),
                'driver'   => 'system',
                'note'     => now()->format('d. M') . ' - ' . now()->subDays(config('subscription.settlement_period'))->format('d. M'),
            ]);

        } catch (InsufficientBalanceException $e) {
            // Notify user
            $subscription->user->notify(new InsufficientBalanceNotification());

            // Create error transaction
            $transaction = $subscription->user->transactions()->create([
                'type'     => 'withdrawal',
                'status'   => 'error',
                'currency' => $subscription->plan->currency,
                'amount'   => $usageEstimates->sum('amount'),
                'driver'   => 'system',
                'note'     => now()->format('d. M') . ' - ' . now()->subDays(config('subscription.settlement_period'))->format('d. M'),
            ]);

            // Store debt record
            $subscription->user->debts()->create([
                'currency'       => $subscription->plan->currency,
                'amount'         => $usageEstimates->sum('amount'),
                'transaction_id' => $transaction->id,
            ]);
        }
    }
}
