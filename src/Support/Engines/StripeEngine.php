<?php

namespace VueFileManager\Subscription\Support\Engines;

use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class StripeEngine implements Engine
{
    public StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('subscription.credentials.stripe.secret'));
    }

    public function getPlan(string $planId): array
    {
        $response = $this->stripe->prices->retrieve($planId);

        return $response->toArray();
    }

    public function createPlan(CreatePlanData $data): array
    {
        // Create product
        $product = $this->stripe->products->create([
            'url'         => url('/'),
            'name'        => $data->name,
            'description' => $data->description,
        ]);

        // Next create subscription plan
        $plan = $this->stripe->prices->create([
            'product'     => $product->toArray()['id'],
            'currency'    => strtolower($data->currency),
            'unit_amount' => $data->amount * 100,
            'recurring'   => [
                'interval' => $data->interval,
            ],
        ]);

        return [
            'id'   => $plan->toArray()['id'],
            'name' => $data->name,
        ];
    }

    public function updatePlan(Plan $plan): array
    {
        // Get original stripe plan where is stored product_id
        $stripePlan = $this->getPlan($plan->driverId('stripe'));

        // Update stripe product where are stored name and description
        $response = $this->stripe->products->update($stripePlan['product'], [
            'name'        => $plan->name,
            'description' => $plan->description,
        ]);

        return $response->toArray();
    }

    public function deletePlan(string $planId): void
    {
        $this->stripe->plans->delete($planId);
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
