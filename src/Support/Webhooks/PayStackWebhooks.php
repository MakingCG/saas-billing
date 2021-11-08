<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Http\Request;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class PayStackWebhooks
{
    public function handleSubscriptionCreate(Request $request): void
    {
        // Get existing customer
        $customer = Customer::where('driver', 'paystack')
            ->where('driver_user_id', $request->input('data.customer.customer_code'))
            ->firstOrFail();

        Subscription::create([
            'user_id'         => $customer->user_id,
            'name'            => $request->input('data.plan.name'),
            'plan_id'         => $request->input('data.plan.plan_code'),
            'subscription_id' => $request->input('data.subscription_code'),
            'status'          => $request->input('data.status'),
        ]);
    }
}
