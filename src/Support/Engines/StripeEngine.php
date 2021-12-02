<?php
namespace VueFileManager\Subscription\Support\Engines;

use Tests\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Services\StripeHttpService;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Support\Webhooks\StripeWebhooks;

class StripeEngine extends StripeWebhooks implements Engine
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

    /*
     * https://stripe.com/docs/api/customers/create
     */
    public function createCustomer(array $user): Response
    {
        $response = $this->api->post('/customers', [
            'metadata' => [
                'id' => $user['id'],
            ],
            'email'    => $user['email'],
            'name'     => $user['name'] . ' ' . $user['surname'],
            'phone'    => $user['phone'] ?? null,
        ]);

        // Store customer id to the database
        Customer::create([
            'user_id'        => $user['id'],
            'driver_user_id' => $response->json()['id'],
            'driver'         => 'stripe',
        ]);

        return $response;
    }

    /*
     * https://stripe.com/docs/api/customers/update
     */
    public function updateCustomer(array $user): Response
    {
        // Get stripe customer id
        $customer = User::find($user['id']);

        // Update customer request
        return $this->api->post("/customers/{$customer->customerDriverId('stripe')}", [
            'email' => $user['email'],
            'name'  => $user['name'] . ' ' . $user['surname'],
            'phone' => $user['phone'] ?? null,
        ]);
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
        $method = 'handle' . Str::studly(str_replace('.', '_', $request->input('type')));

        if (method_exists($this, $method)) {
            $this->{$method}($request);
        }
    }
}
