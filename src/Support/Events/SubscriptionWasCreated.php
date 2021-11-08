<?php

namespace VueFileManager\Subscription\Support\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class SubscriptionWasCreated
{
    use Dispatchable, SerializesModels;

    public Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
