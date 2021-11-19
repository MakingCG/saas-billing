<?php
namespace App\Scheduler;

use VueFileManager\Subscription\Support\Events\SubscriptionWasExpired;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class HaltExpiredSubscriptionsSchedule
{
    public function __invoke()
    {
        Subscription::where('status', 'cancelled')
            ->whereDate('ends_at', today())
            ->get()
            ->each(function ($subscription) {
                // Update status
                $subscription->update([
                    'status' => 'completed',
                ]);

                // Dispatch event
                SubscriptionWasExpired::dispatch($subscription);
            });
    }
}
