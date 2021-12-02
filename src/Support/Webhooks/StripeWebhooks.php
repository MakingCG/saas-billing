<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Http\Request;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasUpdated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCancelled;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Subscriptions\Models\SubscriptionDriver;

class StripeWebhooks
{
    public function handleCustomerSubscriptionCreated(Request $request): void
    {
        $customerCode = $request->input('data.object.customer');
        $subscriptionCode = $request->input('data.object.id');
        $planCode = $request->input('data.object.plan.id');

        // Get existing customer
        $customer = Customer::where('driver', 'stripe')
            ->where('driver_user_id', $customerCode)
            ->first();

        $planDriver = PlanDriver::where('driver_plan_id', $planCode)
            ->first();

        $subscription = Subscription::create([
            'plan_id' => $planDriver->plan->id,
            'user_id' => $customer->user_id,
            'name'    => $planDriver->plan->name,
        ]);

        // Store subscription pivot to gateway
        $subscription
            ->driver()
            ->create([
                'driver'                 => 'stripe',
                'driver_subscription_id' => $subscriptionCode,
            ]);

        // Emit SubscriptionWasCreated
        SubscriptionWasCreated::dispatch($subscription);
    }
}
