<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class StripeEngine implements Engine
{
    public function getPlan(string $planId): Response
    {
        // TODO: Implement getPlan() method.
    }

    public function createPlan(CreatePlanData $data): array
    {
        // TODO: Implement createPlan() method.
    }

    public function updatePlan(Plan $plan): Response
    {
        // TODO: Implement updatePlan() method.
    }

    public function deletePlan(string $planId): Response
    {
        // TODO: Implement deletePlan() method.
    }

    public function createCustomer(array $user): Response
    {
        // TODO: Implement createCustomer() method.
    }

    public function updateCustomer(array $user): Response
    {
        // TODO: Implement updateCustomer() method.
    }

    public function getSubscription(string $subscriptionId): Response
    {
        // TODO: Implement getSubscription() method.
    }

    public function swapSubscription(Subscription $subscription, Plan $plan): Response
    {
        // TODO: Implement swapSubscription() method.
    }

    public function updateSubscription(Subscription $subscription, ?Plan $plan = null): array
    {
        // TODO: Implement updateSubscription() method.
    }

    public function cancelSubscription(Subscription $subscription): Response
    {
        // TODO: Implement cancelSubscription() method.
    }

    public function webhook(Request $request): void
    {
        // TODO: Implement webhook() method.
    }
}
