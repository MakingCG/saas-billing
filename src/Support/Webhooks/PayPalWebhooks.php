<?php

namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Http\Request;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCancelled;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Subscriptions\Models\SubscriptionDriver;
use VueFileManager\Subscription\Support\Events\SubscriptionWasUpdated;

class PayPalWebhooks
{
    public function handleBillingSubscriptionCreated(Request $request): void
    {
        // Get important variables
        $userId = $request->input('resource.custom_id');
        $planCode = $request->input('resource.plan_id');
        $subscriptionCode = $request->input('resource.id');

        // Get gateway plan id
        $planDriver = PlanDriver::where('driver_plan_id', $planCode)
            ->first();

        // Store new subscription
        $subscription = Subscription::create([
            'plan_id' => $planDriver->plan->id,
            'user_id' => $userId,
            'name'    => $planDriver->plan->name,
        ]);

        // Store subscription pivot to gateway
        $subscription
            ->driver()
            ->create([
                'driver'                 => 'paypal',
                'driver_subscription_id' => $subscriptionCode,
            ]);

        SubscriptionWasCreated::dispatch($subscription);
    }

    public function handleBillingSubscriptionUpdated(Request $request): void
    {
        $subscriptionCode = $request->input('resource.id');
        $planCode = $request->input('resource.plan_id');

        $subscriptionDriver = SubscriptionDriver::where('driver_subscription_id', $subscriptionCode)
            ->first();

        $planDriver = PlanDriver::where('driver_plan_id', $planCode)
            ->first();

        if ($subscriptionDriver->subscription->active()) {
            $subscriptionDriver->subscription->update([
                'name'    => $planDriver->plan->name,
                'plan_id' => $planDriver->plan->id,
            ]);

            SubscriptionWasUpdated::dispatch($subscriptionDriver->subscription);
        }
    }

    public function handleBillingSubscriptionCancelled(Request $request): void
    {
        $subscriptionCode = $request->input('resource.id');
        $endsAt = $request->input('resource.billing_info.next_billing_time');

        $driver = SubscriptionDriver::where('driver_subscription_id', $subscriptionCode)
            ->first();

        if ($driver->subscription->active()) {
            $driver->subscription->update([
                'status'  => 'cancelled',
                'ends_at' => $endsAt,
            ]);

            SubscriptionWasCancelled::dispatch($driver->subscription);
        }
    }
}
