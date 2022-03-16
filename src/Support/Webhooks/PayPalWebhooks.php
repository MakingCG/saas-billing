<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Http\Request;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasUpdated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCancelled;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Subscriptions\Models\SubscriptionDriver;

trait PayPalWebhooks
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
            'type'    => 'fixed',
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

        // Get notification
        $SubscriptionWasCreatedNotification = config('subscription.notifications.SubscriptionWasCreatedNotification');

        // Notify user
        $subscription->user->notify(new $SubscriptionWasCreatedNotification($subscription));

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

            $subscriptionDriver->subscription->refresh();

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

            $driver->subscription->refresh();

            SubscriptionWasCancelled::dispatch($driver->subscription);
        }
    }

    /**
     * Record transaction for subscription payment
     */
    public function handlePaymentSaleCompleted(Request $request): void
    {
        // Get subscription code from received webhook
        $subscriptionCode = $request->input('resource.billing_agreement_id');

        // Get original subscription detail from PayPal
        $subscription = resolve(EngineManager::class)
            ->driver('paypal')
            ->getSubscription($subscriptionCode)
            ->json();

        // Get plan data from our database
        $plan = PlanDriver::where('driver_plan_id', $subscription['plan_id'])
            ->first()
            ->plan;

        // Get our user
        $user = config('auth.providers.users.model')::find($request->input('resource.custom'));

        // Store transaction
        $user->transactions()->create([
            'status'    => 'completed',
            'type'      => 'charge',
            'driver'    => 'paypal',
            'note'      => $plan->name,
            'reference' => $request->input('resource.billing_agreement_id'),
            'currency'  => $request->input('resource.amount.currency'),
            'amount'    => $request->input('resource.amount.total'),
        ]);
    }

    /**
     * Record transaction for single charge payment
     *
     * TODO: review if single charge has handlePaymentCaptureCompleted
     */
    public function handleCheckoutOrderApproved(Request $request): void
    {
        // Get our user
        $user = config('auth.providers.users.model')::find($request->input('resource.purchase_units.0.custom_id'));

        $user->creditBalance(
            credit: $request->input('resource.purchase_units.0.amount.value'),
            currency: $request->input('resource.purchase_units.0.amount.currency_code'),
        );

        // Store transaction
        $user->transactions()->create([
            'status'    => 'completed',
            'type'      => 'charge',
            'driver'    => 'paypal',
            'note'      => 'Account Fund',
            'reference' => $request->input('resource.id'),
            'currency'  => $request->input('resource.purchase_units.0.amount.currency_code'),
            'amount'    => $request->input('resource.purchase_units.0.amount.value'),
        ]);
    }
}
