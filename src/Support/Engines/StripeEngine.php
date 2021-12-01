<?php

namespace VueFileManager\Subscription\Support\Engines;

use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class StripeEngine implements Engine
{
    public StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('subscription.credentials.stripe.secret'));
    }

    public function getPlan(string $planId): Response
    {
        // TODO: Implement getPlan() method.
    }

    public function createPlan(CreatePlanData $data): array
    {
        // Create product
        $productId = $this->getOrCreateProductId($data);

        // Next create subscription plan
        $plan = $this->stripe->prices->create([
            'product'     => $productId,
            'currency'    => strtolower($data->currency),
            'unit_amount' => $data->amount * 100,
            'recurring'   => [
                'interval' => $data->interval
            ],
        ]);

        return [
            'id'   => $plan->toArray()['id'],
            'name' => $data->name,
        ];
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

    private function getOrCreateProductId(CreatePlanData $data): string
    {
        $stripePlan = PlanDriver::where('driver', 'stripe')
            ->first();

        if ($stripePlan) {
            $plan = $this->getPlan($stripePlan->driver_plan_id);

            return $plan->toArray()['product'];
        }

        $response = $this->stripe->products->create([
            'url'         => url('/'),
            'name'        => $data->name,
            'description' => $data->description,
        ]);

        return $response->toArray()['id'];
    }
}
