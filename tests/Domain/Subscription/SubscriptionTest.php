<?php
namespace Tests\Domain\Subscription;

use Tests\Models\User;
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

    /**
     * @test
     */
    public function it_test_ended_subscription()
    {
        [$isEnded, $isNotEnded] = Subscription::factory()
            ->count(2)
            ->sequence(
                ['ends_at' => now()->subDay()],
                ['ends_at' => now()->addDay()]
            )->create();

        $this->assertEquals(true, $isEnded->ended());
        $this->assertEquals(false, $isNotEnded->ended());
    }

    /**
     * @test
     */
    public function it_get_my_subscription()
    {
        $user = User::factory()
            ->hasSubscription()
            ->create();

        $this
            ->actingAs($user)
            ->getJson('/api/subscription/detail')
            ->assertJsonFragment([
                'id' => $user->subscription->id,
            ]);
    }

    /**
     * @test
     */
    public function it_get_user_subscription()
    {
        $admin = User::factory()
            ->create(['role' => 'admin']);

        $user = User::factory()
            ->hasSubscription()
            ->create();

        $this
            ->actingAs($admin)
            ->getJson("/api/subscription/users/{$user->id}/subscription")
            ->assertJsonFragment([
                'id' => $user->subscription->id,
            ]);
    }
}
