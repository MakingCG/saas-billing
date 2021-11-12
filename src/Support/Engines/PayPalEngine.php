<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Support\Services\PayPalHttp;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Support\Webhooks\PayPalWebhooks;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;

class PayPalEngine extends PayPalWebhooks implements Engine
{
    public PayPalHttp $api;

    public function __construct()
    {
        $this->api = resolve(PayPalHttp::class);
    }

    /**
     * https://paystack.com/docs/api/#plan-create
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

    public function getPlan(string $planId): Response
    {
        return $this->api->get("/billing/plans/$planId");
    }

    /**
     * https://paystack.com/docs/api/#customer-create
     */
    public function createCustomer(array $user): Customer
    {
        // empty
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
