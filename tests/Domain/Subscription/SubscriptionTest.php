<?php
namespace Tests\Domain\Subscription;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
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
    public function it_get_all_subscription()
    {
        $admin = User::factory()
            ->create(['role' => 'admin']);

        $subscription = Subscription::factory()
            ->count(2)
            ->hasDriver()
            ->create();

        $this
            ->actingAs($admin)
            ->getJson('/api/subscriptions/admin')
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('data.0.data.id', $subscription->pluck('id')[0])
                    ->where('data.1.data.id', $subscription->pluck('id')[1])
                    ->etc()
            );
    }

    /**
     * @test
     */
    public function it_get_my_subscription()
    {
        $user = User::factory()
            ->create();

        Subscription::factory()
            ->hasDriver()
            ->create(['user_id' => $user->id]);

        $this
            ->actingAs($user)
            ->getJson('/api/subscriptions/detail')
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
            ->create();

        Subscription::factory()
            ->hasDriver()
            ->create(['user_id' => $user->id]);

        $this
            ->actingAs($admin)
            ->getJson("/api/subscriptions/admin/users/{$user->id}/subscription")
            ->assertJsonFragment([
                'id' => $user->subscription->id,
            ]);
    }

    /**
     * @test
     */
    public function it_delete_driver_after_subscription_deletion()
    {
        $subscription = Subscription::factory()
            ->hasDriver()
            ->create();

        $subscription->delete();

        $this->assertDatabaseMissing('subscription_drivers', [
            'subscription_id' => $subscription->id,
        ]);
    }
}
