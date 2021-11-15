<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Subscriptions\Models\SubscriptionDriver;

class PayStackWebhooks
{
    public function handleSubscriptionCreate(Request $request): void
    {
        $customerCode = $request->input('data.customer.customer_code');
        $planCode = $request->input('data.plan.plan_code');
        $subscriptionCode = $request->input('data.subscription_code');

        // Get existing customer
        $customer = Customer::where('driver', 'paystack')
            ->where('driver_user_id', $customerCode)
            ->first();

        if (! $customer) {
            Log::error("Customer with id $customerCode do not exist. We can't perform subscription.create");

            throw new ModelNotFoundException;
        }

        $planDriver = PlanDriver::where('driver_plan_id', $planCode)
            ->first();

        $subscription = Subscription::create([
            'plan_id' => $planDriver->plan->id,
            'user_id' => $customer->user_id,
            'name'    => $request->input('data.plan.name'),
        ]);

        // Store subscription pivot to gateway
        $subscription
            ->driver()
            ->create([
                'driver'                 => 'paystack',
                'driver_subscription_id' => $subscriptionCode,
            ]);

        SubscriptionWasCreated::dispatch($subscription);
    }

    public function handleSubscriptionNotRenew(Request $request): void
    {
        $subscriptionCode = $request->input('data.subscription_code');
        $endsAt = $request->input('data.cancelledAt');

        $subscriptionDriver = SubscriptionDriver::where('driver_subscription_id', $subscriptionCode)
            ->first();

        $subscription = Subscription::findOrFail($subscriptionDriver->subscription_id);

        if ($subscription->active()) {
            $subscription->update([
                'status'  => 'cancelled',
                'ends_at' => $endsAt,
            ]);
        }
    }
}
