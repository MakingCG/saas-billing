<?php

namespace Tests\Domain\Usage;

use Tests\TestCase;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class UsageTest extends TestCase
{
    /**
     * @test
     */
    public function it_store_usage()
    {
        $plan = Plan::factory()
            ->hasMeteredFeatures([
                'key' => 'bandwidth',
            ])
            ->create([
                'type' => 'metered',
            ]);

        $subscription = Subscription::factory()
            ->create([
                'plan_id' => $plan->id,
            ]);

        $subscription->recordUsage('bandwidth', 0.12345);

        $this->assertDatabaseHas('usages', [
            'metered_feature_id' => $plan->meteredFeatures()->first()->id,
            'subscription_id'    => $subscription->id,
            'quantity'           => 0.12345,
        ]);
    }
}
