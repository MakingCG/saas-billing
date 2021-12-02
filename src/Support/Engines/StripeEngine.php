<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Support\Services\StripeHttpService;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class StripeEngine implements Engine
{
    public StripeHttpService $api;

    public function __construct()
    {
        $this->api = resolve(StripeHttpService::class);
    }

    /*
     * https://stripe.com/docs/api/prices/retrieve?lang=php
     */
    public function getPlan(string $planId): Response
    {
        return $this->api->get("/prices/$planId");
    }

    /*
     * https://stripe.com/docs/api/products/create?lang=php
     * https://stripe.com/docs/api/prices/create?lang=php
     */
    public function createPlan(CreatePlanData $data): array
    {
        // Create product
        $product = $this->api->post('/products', [
            'url'         => url('/'),
            'name'        => $data->name,
            'description' => $data->description,
        ]);

        // Next create subscription plan
        $plan = $this->api->post('/prices', [
            'product'     => $product->json()['id'],
            'currency'    => strtolower($data->currency),
            'unit_amount' => $data->amount * 100,
            'recurring'   => [
                'interval' => $data->interval,
            ],
        ]);

        return [
            'id'   => $plan->json()['id'],
            'name' => $data->name,
        ];
    }

    /*
     * https://stripe.com/docs/api/products/update?lang=php
     */
    public function updatePlan(Plan $plan): Response
    {
        // Get original stripe plan where is stored product_id
        $stripePlan = $this->getPlan($plan->driverId('stripe'));

        // Update stripe product where are stored name and description
        return $this->api->post("/products/{$stripePlan['product']}", [
            'name'        => $plan->name,
            'description' => $plan->description,
        ]);
    }

    /*
     * https://stripe.com/docs/api/plans/delete?lang=php
     */
    public function deletePlan(string $planId): void
    {
        $this->api->delete("/plans/{$planId}");
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
