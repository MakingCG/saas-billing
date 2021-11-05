<?php

namespace Support\Engines;

use Domain\Customers\Models\Customer;
use Domain\Plans\DTO\CreatePlanData;
use Domain\Subscriptions\Models\Subscription;
use Illuminate\Http\Request;
use Support\Services\PayStackHttp;
use Tests\Models\User;

class PayStackEngine implements Engine
{
    public PayStackHttp $api;

    public function __construct()
    {
        $this->api = resolve(PayStackHttp::class);
    }

    public function hello(): string
    {
        return "Hello, I'm PayStack!";
    }

    /**
     * https://paystack.com/docs/api/#plan-create
     */
    public function createPlan(CreatePlanData $data): array
    {
        $response = $this->api->post('/plan', [
            'name'     => $data->name,
            'amount'   => $data->price,
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

    public function webhook(Request $request): void
    {
        if ($request->input('event') === 'subscription.create') {

            // Get existing customer
            $customer = Customer::where('driver', 'paystack')
                ->where('driver_user_id', $request->input('data.customer.customer_code'))
                ->firstOrFail();

            Subscription::create([
                'user_id'             => $customer->user_id,
                'name'                => $request->input('data.plan.name'),
                'plan_id'             => $request->input('data.plan.plan_code'),
                'subscription_id'     => $request->input('data.subscription_code'),
                'status' => $request->input('data.status'),
            ]);
        }
    }

    private function mapInterval(string $interval): string
    {
        return match ($interval) {
            'day' => 'daily',
            'week' => 'weekly',
            'month' => 'monthly',
            //'quarter' => 'quarterly',
            'year' => 'annually',
        };
    }
}
