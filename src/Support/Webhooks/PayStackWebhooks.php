<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Http\Request;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasUpdated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCancelled;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Subscriptions\Models\SubscriptionDriver;

trait PayStackWebhooks
{
    public function handleSubscriptionCreate(Request $request): void
    {
        $customerCode = $request->input('data.customer.customer_code');
        $customerEmail = $request->input('data.customer.email');
        $subscriptionCode = $request->input('data.subscription_code');
        $planCode = $request->input('data.plan.plan_code');

        // Get existing customer
        $customer = Customer::where('driver', 'paystack')
            ->where('driver_user_id', $customerCode)
            ->first();

        if (! $customer) {
            // Get user by email
            $user = config('auth.providers.users.model')::where('email', $customerEmail)
                ->first();

            // Store customer id to the database
            $customer = $user->customers()
                ->create([
                    'driver_user_id' => $customerCode,
                    'driver'         => 'paystack',
                ]);
        }

        $planDriver = PlanDriver::where('driver_plan_id', $planCode)
            ->first();

        // Replace existing subscription
        if ($customer->user->subscription && ($customer->user->subscription->driverId() !== $subscriptionCode)) {
            // Cancel previous subscription
            resolve(EngineManager::class)
                ->driver('paystack')
                ->cancelSubscription(
                    $customer->user->subscription
                );

            // Update subscription
            $customer->user->subscription()->update([
                'plan_id'       => $planDriver->plan->id,
                'name'          => $planDriver->plan->name,
                'status'        => 'active',
                'ends_at'       => null,
                'trial_ends_at' => null,
            ]);

            // Update subscription driver id
            $customer->user->subscription->driver()->update([
                'driver_subscription_id' => $subscriptionCode,
            ]);

            // Refresh subscription data
            $customer->user->subscription->refresh();

            // Emit SubscriptionWasUpdated
            SubscriptionWasUpdated::dispatch($customer->user->subscription);
        }

        // Create new subscription
        if (! $customer->user->subscription) {
            $subscription = Subscription::create([
                'type'    => 'fixed',
                'plan_id' => $planDriver->plan->id,
                'user_id' => $customer->user_id,
                'name'    => $planDriver->plan->name,
            ]);

            // Store subscription pivot to gateway
            $subscription
                ->driver()
                ->create([
                    'driver'                 => 'paystack',
                    'driver_subscription_id' => $subscriptionCode,
                ]);

            // Get notification
            $SubscriptionWasCreatedNotification = config('subscription.notifications.SubscriptionWasCreatedNotification');

            // Notify user
            $subscription->user->notify(new $SubscriptionWasCreatedNotification($subscription));

            // Emit SubscriptionWasCreated
            SubscriptionWasCreated::dispatch($subscription);
        }
    }

    public function handleSubscriptionNotRenew(Request $request): void
    {
        $subscriptionCode = $request->input('data.subscription_code');
        $endsAt = $request->input('data.cancelledAt');

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

    public function handleChargeSuccess(Request $request): void
    {
        // Get plan
        $plan = $request->input('data.plan');

        // Get user
        $user = config('auth.providers.users.model')::where('email', $request->input('data.customer.email'))
            ->first();

        // TODO: resolve conflict with ZAR/USD currency
        // Proceed as credit balance
        if (empty($plan)) {
            $user->creditBalance(
                credit: $request->input('data.amount') / 100,
                currency: $request->input('data.currency'),
            );

            $user->transactions()->create([
                'status'    => 'completed',
                'type'      => 'charge',
                'driver'    => 'paystack',
                'note'      => 'Account Fund',
                'reference' => $request->input('data.reference'),
                'currency'  => $request->input('data.currency'),
                'amount'    => $request->input('data.amount') / 100,
            ]);
        }

        // Proceed as subscription charge
        if (! empty($plan)) {
            $user->transactions()->create([
                'status'    => 'completed',
                'type'      => 'charge',
                'driver'    => 'paystack',
                'note'      => $request->input('data.plan.name'),
                'reference' => $request->input('data.reference'),
                'currency'  => $request->input('data.currency'),
                'amount'    => $request->input('data.amount') / 100,
            ]);
        }
    }
}
