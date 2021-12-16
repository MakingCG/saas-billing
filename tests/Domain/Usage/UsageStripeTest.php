<?php
namespace Tests\Domain\Usage;

use Tests\TestCase;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class UsageStripeTest extends TestCase
{
    /**
     *
     */
    public function it_report_usage_to_stripe()
    {
        $plan = Plan::factory()
            ->hasMeteredFeatures([
                'key' => 'bandwidth',
            ])
            ->create([
                'type' => 'metered',
            ]);

        $subscription = Subscription::factory()
            ->hasDriver([
                'driver'                 => 'stripe',
                'driver_subscription_id' => 'sub_1K5BluB9m4sTKy1qsxUAhtt8',
            ])
            ->create([
                'plan_id' => $plan->id,
            ]);

        $subscription->recordUsage('bandwidth', 0.12345);
    }
}
