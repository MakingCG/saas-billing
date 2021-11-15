<?php
namespace Domain\Subscriptions\Traits;

use VueFileManager\Subscription\Support\EngineManager;

trait SubscriptionHelpers
{
    protected function gateway()
    {
        return resolve(EngineManager::class);
    }

    public function cancel()
    {
        $this->gateway()
            ->driver($this->driver->driver)
            ->cancelSubscription($this);
    }

    public function resume()
    {
        $this->gateway()
            ->driver($this->driver->driver)
            ->resumeSubscription($this);
    }

    public function onGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    public function active(): bool
    {
        return $this->status === 'active';
    }

    public function cancelled(): bool
    {
        return ! is_null($this->ends_at);
    }
}
