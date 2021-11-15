<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Support\Services\PayPalHttp;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Support\Webhooks\PayPalWebhooks;

class PayPalEngine extends PayPalWebhooks implements Engine
{
    public PayPalHttp $api;

    public function __construct()
    {
        $this->api = resolve(PayPalHttp::class);
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
                            'currency_code' => config('subscription.default_currency'),
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
        // Get PayPal plan id
        $planDriver = $plan
            ->drivers()
            ->where('driver', 'paypal')
            ->first();

        return $this->api->patch("/billing/plans/$planDriver->driver_plan_id", [
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
    public function deletePlan(string $planId): Response
    {
        return $this->api->post("/billing/plans/{$planId}/deactivate", []);
    }

    /**
     * Method is not provided by PayPal api
     */
    public function createCustomer(array $user): Response
    {
        // ...
    }

    /**
     * Method is not provided by PayPal api
     */
    public function updateCustomer(array $user): Response
    {
        // ...
    }

    public function getSubscription(string $subscriptionId): Response
    {
        // TODO: Implement getSubscription() method.
    }

    public function cancelSubscription(Subscription $subscription): Response
    {
        // TODO: Implement cancelSubscription() method.
    }

    public function resumeSubscription(Subscription $subscription): Response
    {
        // TODO: Implement resumeSubscription() method.
    }

    /**
     * https://paystack.com/docs/payments/webhooks
     */
    public function webhook(Request $request): void
    {
        $method = 'handle' . Str::studly(str_replace('.', '_', strtolower($request->input('event_type'))));

        if (method_exists($this, $method)) {
            $this->{$method}($request);
        }
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
     * If isn't any created product, create them. If there is
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
