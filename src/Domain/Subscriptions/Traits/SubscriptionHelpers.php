<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Traits;

use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

trait SubscriptionHelpers
{
    /**
     * Get subscription driver
     */
    protected function gateway(): EngineManager
    {
        return resolve(EngineManager::class);
    }

    /**
     * Cancel subscription
     */
    public function cancel()
    {
        $this->gateway()
            ->driver($this->driver->driver)
            ->cancelSubscription($this);
    }

    /**
     * Swap subscription
     */
    public function swap(Plan $plan)
    {
        return $this->gateway()
            ->driver($this->driver->driver)
            ->swapSubscription($this, $plan);
    }

    /**
     * Get gateway subscription id
     */
    public function driverId(): string
    {
        return $this->driver->driver_subscription_id;
    }

    /**
     * Get gateway subscription id
     */
    public function driverName(): string
    {
        return $this->driver->driver;
    }

    /**
     * Check if subscription is on grace period
     */
    public function onGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Check if subscription is on grace period
     */
    public function ended(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Check if subscription is active
     */
    public function active(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is cancelled
     */
    public function cancelled(): bool
    {
        return ! is_null($this->ends_at);
    }

    /**
     * Get all subscription plan features
     */
    public function features()
    {
        return $this->plan->features()->pluck('value', 'key');
    }

    /**
     * Get single subscription plan feature by name
     */
    public function feature(string $feature)
    {
        return $this->plan->features()->where('key', $feature)->first()->value;
    }
}
