<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Support\Webhooks\PayPalWebhooks;
use VueFileManager\Subscription\Support\Services\PayPalHttpService;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class PayPalEngine extends PayPalWebhooks implements Engine
{
    public PayPalHttpService $api;

    public function __construct()
    {
        $this->api = resolve(PayPalHttpService::class);
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#plans_create
     */
    public function createPlan(CreatePlanData $data): array
    {
        $productId = $this->getOrCreateProductId();

        $plan = $this->api->post('/billing/plans', [
            'product_id'          => $productId,
            'name'                => $data->name,
            'description'         => $data->description,
            'billing_cycles'      => [
                [
                    'frequency'      => [
                        'interval_unit'  => $this->mapInterval($data->interval),
                        'interval_count' => 1,
                    ],
                    'tenure_type'    => 'REGULAR',
                    'sequence'       => 1,
                    'total_cycles'   => 0,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value'         => $data->amount,
                            'currency_code' => $data->currency,
                        ],
                    ],
                ],
            ],
            'payment_preferences' => [
                'auto_bill_outstanding'     => true,
                'payment_failure_threshold' => 3,
            ],
        ]);

        return [
            'id'   => $plan->json()['id'],
            'name' => $plan->json()['name'],
        ];
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#plans_patch
     */
    public function updatePlan(Plan $plan): Response
    {
        $response = $this->api->patch("/billing/plans/{$plan->driverId('paypal')}", [
            [
                'op'    => 'replace',
                'path'  => '/name',
                'value' => $plan->name,
            ],
            [
                'op'    => 'replace',
                'path'  => '/description',
                'value' => $plan->description,
            ],
        ]);

        return $response;
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#plans_get
     */
    public function getPlan(string $planId): Response
    {
        return $this->api->get("/billing/plans/$planId");
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#plans_deactivate
     */
    public function deletePlan(string $planId): void
    {
        $this->api->post("/billing/plans/{$planId}/deactivate", []);
    }

    /**
     * Method is not provided by PayPal api
     */
    public function createCustomer(array $user): null|Response
    {
        return null;
    }

    /**
     * Method is not provided by PayPal api
     */
    public function updateCustomer(array $user): null|Response
    {
        return null;
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_get
     */
    public function getSubscription(string $subscriptionId): Response
    {
        return $this->api->get("/billing/subscriptions/$subscriptionId");
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_revise
     * TODO: confirm subscription change on frontend
     */
    public function swapSubscription(Subscription $subscription, Plan $plan): Response
    {
        return $this->api->post("/billing/subscriptions/{$subscription->driverId()}/revise", [
            'plan_id'             => $plan->driverId('paypal'),
            'application_context' => [
                'url' => url('/user/settings/subscription'),
            ],
        ]);
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_revise
     */
    public function updateSubscription(Subscription $subscription, ?Plan $plan = null): array
    {
        $response = $this->api->post("/billing/subscriptions/{$subscription->driverId()}/revise", [
            'plan_id'             => $plan->driverId('paypal'),
            'application_context' => [
                'return_url' => url('/user/settings/subscription'),
            ],
        ]);

        return [
            'driver' => 'paypal',
            'url'    => $response->json()['links'][0]['href'],
        ];
    }

    /**
     * https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_cancel
     */
    public function cancelSubscription(Subscription $subscription): Response
    {
        // Get subscription details from payment gateway
        $originalSubscription = $this->getSubscription($subscription->driverId());

        // Cancel subscription request
        $response = $this->api->post("/billing/subscriptions/{$subscription->driverId()}/cancel", [
            'reason' => 'User decided cancel his subscription',
        ]);

        // Store end_at period and update status as cancelled
        $subscription->update([
            'status'  => 'cancelled',
            'ends_at' => $originalSubscription->json()['billing_info']['next_billing_time'],
        ]);

        return $response;
    }

    /**
     * https://developer.paypal.com/docs/api/webhooks/v1/
     */
    public function webhook(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $method = 'handle' . Str::studly(str_replace('.', '_', strtolower($request->input('event_type'))));

        if (method_exists($this, $method)) {
            $this->{$method}($request);
        }

        return new \Symfony\Component\HttpFoundation\Response('Webhook Handled', 200);
    }

    /**
     * Map internal request interval to PayPal supported intervals
     */
    private function mapInterval(string $interval): string
    {
        return match ($interval) {
            'day'   => 'DAY',
            'week'  => 'WEEK',
            'month' => 'MONTH',
            'year'  => 'YEAR',
        };
    }

    /**
     * If isn't any product created, create them. If there is
     * some product, then get his id.
     */
    private function getOrCreateProductId(): string
    {
        $paypalPlan = PlanDriver::where('driver', 'paypal')
            ->first();

        if ($paypalPlan) {
            $plan = $this->getPlan($paypalPlan->driver_plan_id);

            return $plan->json()['product_id'];
        }

        $response = $this->api->post('/catalogs/products', [
            'name'        => 'Subscription Service',
            'description' => 'Cloud subscription service',
            'type'        => 'SERVICE',
            'category'    => 'SOFTWARE',
        ]);

        return $response->json()['id'];
    }
}
