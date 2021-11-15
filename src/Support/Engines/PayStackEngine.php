<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Support\Services\PayStackHttp;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Webhooks\PayStackWebhooks;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

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
            'amount'   => $data->amount * 100,
            'interval' => $this->mapIntervals($data->interval),
        ]);

        return [
            'id'   => $response->json()['data']['plan_code'],
            'name' => $response->json()['data']['name'],
        ];
    }

    /**
     * https://paystack.com/docs/api/#plan-create
     */
    public function updatePlan(Plan $plan): Response
    {
        // Get paystack plan id
        $planDriver = $plan
            ->drivers()
            ->where('driver', 'paystack')
            ->first();

        return $this->api->put("/plan/{$planDriver->driver_plan_id}", [
            'name' => $plan->name,
        ]);
    }

    /**
     * https://paystack.com/docs/api/#plan-fetch
     */
    public function getPlan(string $planId): Response
    {
        $response = $this->api->get("/plan/$planId");

        // Check if subscription exist
        if (! $response->json()['status']) {
            throw new NotFoundHttpException($response->json()['message']);
        }

        return $response;
    }

    /**
     * Not documented, but it's working
     */
    public function deletePlan(string $planId): Response
    {
        return $this->api->delete("/plan/$planId");
    }

    /**
     * https://paystack.com/docs/api/#customer-create
     */
    public function createCustomer(array $user): Response
    {
        $response = $this->api->post('/customer', [
            'email'      => $user['email'],
            'first_name' => $user['name'],
            'last_name'  => $user['surname'],
            'phone'      => $user['phone'],
        ]);

        // Store customer id to the database
        Customer::create([
            'user_id'        => $user['id'],
            'driver_user_id' => $response->json()['data']['customer_code'],
            'driver'         => 'paystack',
        ]);

        return $response;
    }

    /**
     * https://paystack.com/docs/api/#customer-update
     */
    public function updateCustomer(array $user): Response
    {
        // Get paystack customer id
        $customer = Customer::where('user_id', $user['id'])
            ->where('driver', 'paystack')
            ->first();

        return $this->api->put("/customer/{$customer->driver_user_id}", [
            'email'      => $user['email'],
            'first_name' => $user['name'],
            'last_name'  => $user['surname'],
            'phone'      => $user['phone'],
        ]);
    }

    public function getSubscription(string $subscriptionId): Response
    {
        $response = $this->api->get("/subscription/$subscriptionId");

        // Check if subscription exist
        if (! $response->json()['status']) {
            throw new NotFoundHttpException($response->json()['message']);
        }

        return $response;
    }

    public function cancelSubscription(Subscription $subscription): Response
    {
        // Get subscription details from payment gateway
        $subscriptionDetail = $this->getSubscription(
            $subscription->driver->driver_subscription_id
        );

        // Send cancel subscription request
        $response = $this->api->post('/subscription/disable', [
            'code'  => $subscriptionDetail->json()['data']['subscription_code'],
            'token' => $subscriptionDetail->json()['data']['email_token'],
        ]);

        if (! $response->json()['status']) {
            // TODO: create exception
        }

        // Store end_at period and update status as cancelled
        $subscription->update([
            'status'  => 'cancelled',
            'ends_at' => $subscriptionDetail->json()['data']['next_payment_date'],
        ]);

        return $response;
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
        $method = 'handle' . Str::studly(str_replace('.', '_', $request->input('event')));

        if (method_exists($this, $method)) {
            $this->{$method}($request);
        }
    }

    /**
     * Map internal request interval to Paystack supported intervals
     */
    private function mapIntervals(string $interval): string
    {
        return match ($interval) {
            'day'   => 'daily',
            'week'  => 'weekly',
            'month' => 'monthly',
            'year'  => 'annually',
        };
    }
}
