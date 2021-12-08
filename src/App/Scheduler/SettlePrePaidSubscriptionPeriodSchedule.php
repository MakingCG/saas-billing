<?php
namespace App\Scheduler;

use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

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
                $toPay = $subscription->plan->meteredFeatures->map(function ($feature) use ($subscription) {
                    // Get first tier
                    // TODO: support of multiple tier for near future
                    $tier = $feature->tiers()->first();

                    // Sum subscription usage
                    $usage = $subscription
                        ->usages()
                        ->where('created_at', '>=', now()->subDays(30))
                        ->where('subscription_id', $subscription->id)
                        ->where('metered_feature_id', $feature->id)
                        ->sum('quantity');

                    // return sum of money
                    return ($tier->per_unit * $usage) + $tier->flat_fee;
                })->toArray();

                // Make withdrawal
                $subscription->user->withdrawBalance(array_sum($toPay));

                // Create transaction
                $subscription->user->transactions()->create([
                    'type'      => 'withdrawal',
                    'status'    => 'completed',
                    'plan_name' => $subscription->plan->name,
                    'currency'  => $subscription->plan->currency,
                    'amount'    => array_sum($toPay),
                    'driver'    => 'system',
                ]);
            });
    }
}
