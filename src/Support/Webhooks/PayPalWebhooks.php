<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Http\Request;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

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
}
