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
     * Generate link for subscription detail update right in payment gateway
     */
    public function generateUpdateLink()
    {
        return $this->gateway()
            ->driver($this->driver->driver)
            ->updateSubscription($this, $this->plan ?? null);
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
    public function driverName(): null|string
    {
        return $this->driver->driver ?? null;
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
    public function fixedFeatures()
    {
        return $this->plan->fixedFeatures()->pluck('value', 'key');
    }

    /**
     * Get single subscription plan feature by name
     */
    public function fixedFeature(string $feature)
    {
        return $this->plan->fixedFeatures()->where('key', $feature)->first()->value;
    }

    /**
     * Store subscription feature usage
     */
    public function recordUsage($key, $quantity): void
    {
        $meteredItem = $this->plan
            ->meteredFeatures()
            ->where('key', $key)
            ->first();

        $this->usages()->create([
            'metered_feature_id'   => $meteredItem->id,
            'quantity'             => $quantity,
        ]);
    }
}
