<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

interface Engine
{
    /**
     * Get subscription plan
     */
    public function getPlan(string $planId): Response;

    /**
     * Create new subscription plan
     */
    public function createPlan(CreatePlanData $data): array;

    /**
     * Update subscription plan
     */
    public function updatePlan(Plan $plan): Response;

    /**
     * Delete subscription plan
     */
    public function deletePlan(string $planId): Response;

    /**
     * Create new customer for service
     */
    public function createCustomer(array $user): Response;

    /**
     * Update customer for service
     */
    public function updateCustomer(array $user): Response;

    /**
     * Get Subscription details
     */
    public function getSubscription(string $subscriptionId): Response;

    /**
     * Swap subscription plan
     */
    public function swapSubscription(Subscription $subscription, Plan $plan): Response;

    /**
     * Update subscription
     */
    public function updateSubscription(Subscription $subscription, ?Plan $plan = null): array;

    /**
     * Cancel Subscription
     */
    public function cancelSubscription(Subscription $subscription): Response;

    /**
     * Get webhook
     */
    public function webhook(Request $request): void;
}
