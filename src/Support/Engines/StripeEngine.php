<?php
namespace VueFileManager\Subscription\Support\Engines;

use Carbon\Carbon;
use Tests\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Stripe\WebhookSignature;
use Illuminate\Http\Client\Response;
use Stripe\Exception\SignatureVerificationException;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Support\Webhooks\StripeWebhooks;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Services\StripeHttpService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

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
        return $this->api->post("/customers/{$customer->customerId('stripe')}", [
            'email' => $user['email'],
            'name'  => $user['name'] . ' ' . $user['surname'],
            'phone' => $user['phone'] ?? null,
        ]);
    }

    /*
     * https://stripe.com/docs/api/subscriptions/retrieve?lang=curl
     */
    public function getSubscription(string $subscriptionId): Response
    {
        return $this->api->get("/subscriptions/$subscriptionId");
    }

    /*
     * https://stripe.com/docs/api/subscriptions/update?lang=php
     */
    public function swapSubscription(Subscription $subscription, Plan $plan): Response
    {
        $stripeSubscription = $this->getSubscription($subscription->driverId());

        return $this->api->post("/subscriptions/{$subscription->driverId()}", [
            'items' => [
                [
                    'id'    => $stripeSubscription->json()['items']['data'][0]['id'],
                    'price' => $plan->driverId('stripe'),
                ],
            ],
        ]);
    }

    public function updateSubscription(Subscription $subscription, ?Plan $plan = null): array
    {
        // TODO: Implement updateSubscription() method.
    }

    /*
     * https://stripe.com/docs/api/subscriptions/retrieve?lang=curl
     */
    public function cancelSubscription(Subscription $subscription): Response
    {
        // Send cancel subscription request
        $response = $this->api->delete("/subscriptions/{$subscription->driverId()}");

        // Store end_at period and update status as cancelled
        $subscription->update([
            'status'  => 'cancelled',
            'ends_at' => Carbon::createFromTimestamp($response->json()['current_period_end']),
        ]);

        return $response;
    }

    /*
     * https://stripe.com/docs/webhooks
     */
    public function webhook(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        // Verify signature
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('subscription.credentials.stripe.secret'),
                300
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        // Extract method name
        $method = 'handle' . Str::studly(str_replace('.', '_', $request->input('type')));

        if (method_exists($this, $method)) {
            $this->{$method}($request);
        }

        return new \Symfony\Component\HttpFoundation\Response('Webhook Handled', 200);
    }
}
