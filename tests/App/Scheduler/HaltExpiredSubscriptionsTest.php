<?php
namespace Tests\App\Scheduler;

use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use VueFileManager\Subscription\App\Scheduler\HaltExpiredSubscriptionsSchedule;
use VueFileManager\Subscription\Support\Events\SubscriptionWasExpired;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class HaltExpiredSubscriptionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_halt_expired_subscriptions()
    {
        Event::fake([
            SubscriptionWasExpired::class,
        ]);

        $subscription = Subscription::factory()
            ->hasDriver()
            ->create([
                'status'     => 'cancelled',
                'ends_at'    => now(),
            ]);

        // Run scheduler
        resolve(HaltExpiredSubscriptionsSchedule::class)();

        $this->assertDatabaseHas('subscriptions', [
            'status' => 'completed',
        ]);

        Event::assertDispatched(fn (SubscriptionWasExpired $event) => $event->subscription->id === $subscription->id);
    }
}
