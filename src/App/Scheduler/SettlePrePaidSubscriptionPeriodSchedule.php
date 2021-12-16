<?php
namespace App\Scheduler;

use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;
use VueFileManager\Subscription\Domain\Credits\Notifications\InsufficientBalanceNotification;

class SettlePrePaidSubscriptionPeriodSchedule
{
    public function __invoke()
    {
        Subscription::where('type', 'pre-paid')
            ->where('status', 'active')
            ->whereDate('renews_at', today())
            ->cursor()
            ->each(function ($subscription) {
                // Sum usage
                $toPay = $subscription
                    ->plan
                    ->meteredFeatures
                    ->map(function ($feature) use ($subscription) {
                        // Get first tier
                        // TODO: support of multiple tier for near future
                        $tier = $feature->tiers()->first();

                        $usageQuery = $subscription
                            ->usages()
                            ->where('created_at', '>=', now()->subDays(30))
                            ->where('subscription_id', $subscription->id)
                            ->where('metered_feature_id', $feature->id);

                        $usage = match ($feature->aggregate_strategy) {
                            'sum_of_usage'  => $usageQuery->sum('quantity'),
                            'maximum_usage' => $usageQuery->max('quantity'),
                        };

                        // return sum of money
                        return ($tier->per_unit * $usage) + $tier->flat_fee;
                    })->toArray();

                try {
                    // Make withdrawal
                    $subscription->user->withdrawBalance(array_sum($toPay));

                    // Create transaction
                    $subscription->user->transactions()->create([
                        'type'     => 'withdrawal',
                        'status'   => 'completed',
                        'note'     => now()->format('d. M') . ' - ' . now()->subDays(30)->format('d. M'),
                        'currency' => $subscription->plan->currency,
                        'amount'   => array_sum($toPay),
                        'driver'   => 'system',
                    ]);
                } catch (InsufficientBalanceException $e) {
                    // Notify user
                    $subscription->user->notify(new InsufficientBalanceNotification());

                    // Create error transaction
                    $transaction = $subscription->user->transactions()->create([
                        'type'     => 'withdrawal',
                        'status'   => 'error',
                        'note'     => now()->format('d. M') . ' - ' . now()->subDays(30)->format('d. M'),
                        'currency' => $subscription->plan->currency,
                        'amount'   => array_sum($toPay),
                        'driver'   => 'system',
                    ]);

                    // Store debt record
                    $subscription->user->debts()->create([
                        'currency'       => $subscription->plan->currency,
                        'amount'         => array_sum($toPay),
                        'transaction_id' => $transaction->id,
                    ]);
                }

                // Update next subscription period date
                $subscription->update([
                    'renews_at' => now()->addDays(30),
                ]);
            });
    }
}
