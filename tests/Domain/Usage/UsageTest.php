<?php
namespace Tests\Domain\Usage;

use Tests\TestCase;
use Tests\Models\User;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class UsageTest extends TestCase
{
    /**
     * @test
     */
    public function it_store_usage()
    {
        $user = User::factory()
            ->create();

        $plan = Plan::factory()
            ->hasDrivers()
            ->hasMeteredFeatures([
                'key' => 'bandwidth',
            ])
            ->create([
                'type' => 'metered',
            ]);

        $subscription = Subscription::factory()
            ->hasDriver([
                'driver' => 'paypal',
            ])
            ->create([
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'status'  => 'active',
            ]);

        $subscription->recordUsage('bandwidth', 0.12);

        $this->assertDatabaseHas('usages', [
            'plan_metered_feature_id' => $plan->meteredFeatures()->first()->id,
            'subscription_id'      => $subscription->id,
            'quantity'             => 0.12,
        ]);
    }
}
