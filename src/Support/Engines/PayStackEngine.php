<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use VueFileManager\Subscription\Support\Services\PayStackHttp;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Webhooks\PayStackWebhooks;

class PayStackEngine extends PayStackWebhooks implements Engine
{
    public PayStackHttp $api;

    public function __construct()
    {
        $this->api = resolve(PayStackHttp::class);
    }

    /**
     * https://paystack.com/docs/api/#plan-create
     */
    public function createPlan(CreatePlanData $data): array
    {
        $response = $this->api->post('/plan', [
            'name'     => $data->name,
            'amount'   => $data->amount,
            'interval' => $this->mapInterval($data->interval),
        ]);

        return [
            'id'   => $response->json()['data']['plan_code'],
            'name' => $response->json()['data']['name'],
        ];
    }

    /**
     * https://paystack.com/docs/api/#customer-create
     */
    public function createCustomer(array $user): Customer
    {
        $response = $this->api->post('/customer', [
            'email'        => $user['email'],
            'first_name'   => $user['name'],
            'last_surname' => $user['surname'],
            'phone'        => $user['phone'],
        ]);

        return Customer::create([
            'user_id'        => $user['id'],
            'driver_user_id' => $response->json()['data']['customer_code'],
            'driver'         => 'paystack',
        ]);
    }

    /**
     * https://paystack.com/docs/payments/webhooks
     */
    public function webhook(Request $request): void
    {
        $method = 'handle' . Str::studly(str_replace('.', '_', $request->input('event')));

        if (method_exists($this, $method)) {
            $this->{$method}($request);
        }
    }

    private function mapInterval(string $interval): string
    {
        return match ($interval) {
            'day'   => 'daily',
            'week'  => 'weekly',
            'month' => 'monthly',
            //'quarter' => 'quarterly',
            'year' => 'annually',
        };
    }
}
