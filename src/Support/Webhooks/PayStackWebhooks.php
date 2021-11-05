<?php

namespace Support\Webhooks;

use Domain\Customers\Models\Customer;
use Domain\Subscriptions\Models\Subscription;
use Illuminate\Http\Request;

class PayStackWebhooks
{
    public function handleSubscriptionCreate(Request $request): void
    {
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
