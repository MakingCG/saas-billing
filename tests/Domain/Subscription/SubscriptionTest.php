<?php
namespace Tests\Domain\Subscription;

use Tests\TestCase;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class SubscriptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_test_grace_period()
    {
        [$isOnGracePeriod, $isNotOnGracePeriod] = Subscription::factory()
            ->count(2)
            ->sequence(
                ['ends_at' => now()->addDays(14)],
                ['ends_at' => null]
            )->create();

        $this->assertEquals(true, $isOnGracePeriod->onGracePeriod());
        $this->assertEquals(false, $isNotOnGracePeriod->onGracePeriod());
    }

    /**
     * @test
     */
    public function it_test_active_subscription()
    {
        [$isActive, $isNotActive] = Subscription::factory()
            ->count(2)
            ->sequence(
                ['status' => 'active'],
                ['status' => 'cancelled']
            )->create();

        $this->assertEquals(true, $isActive->active());
        $this->assertEquals(false, $isNotActive->active());
    }

    /**
     * @test
     */
    public function it_test_cancelled_subscription()
    {
        [$isCancelled, $isNotCancelled] = Subscription::factory()
            ->count(2)
            ->sequence(
                ['ends_at' => now()],
                ['ends_at' => null]
            )->create();

        $this->assertEquals(true, $isCancelled->cancelled());
        $this->assertEquals(false, $isNotCancelled->cancelled());
    }
}
