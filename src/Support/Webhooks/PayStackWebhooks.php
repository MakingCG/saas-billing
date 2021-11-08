<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class PayStackWebhooks
{
    public function handleSubscriptionCreate(Request $request): void
    {
        $customerCode = $request->input('data.customer.customer_code');

        // Get existing customer
        $customer = Customer::where('driver', 'paystack')
            ->where('driver_user_id', $customerCode)
            ->first();

        if (! $customer) {
            Log::error("Customer with id $customerCode do not exist. We can't perform subscription.create");

            throw new ModelNotFoundException;
        }

        Subscription::create([
            'user_id'         => $customer->user_id,
            'name'            => $request->input('data.plan.name'),
            'plan_id'         => $request->input('data.plan.plan_code'),
            'subscription_id' => $request->input('data.subscription_code'),
            'status'          => $request->input('data.status'),
        ]);
    }
}
