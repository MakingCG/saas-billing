<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Carbon\Carbon;
use Illuminate\Http\Request;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasExpired;
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

    public function handleCustomerSubscriptionUpdated(Request $request): void
    {
        $subscriptionCode = $request->input('data.object.id');
        $currentPeriodEndsAt = $request->input('data.object.current_period_end');
        $cancelAtPeriodEnd = $request->input('data.object.cancel_at_period_end');
        $planCode = $request->input('data.object.plan.id');
        $status = $request->input('data.object.status');

        $driver = SubscriptionDriver::where('driver_subscription_id', $subscriptionCode)
            ->first();

        // Plan cancellation
        if ($cancelAtPeriodEnd) {
            $driver->subscription->update([
                'status'  => 'cancelled',
                'ends_at' => Carbon::createFromTimestamp($currentPeriodEndsAt),
            ]);

            SubscriptionWasCancelled::dispatch($driver->subscription);
        }

        // Plan payment expired
        if ($status === 'incomplete_expired') {
            $driver->subscription->update([
                'status'  => 'completed',
                'ends_at' => now(),
            ]);

            SubscriptionWasExpired::dispatch($driver->subscription);
        }

        // Swap plan subscription
        if ($driver->subscription->plan->driverId('stripe') !== $planCode) {
            if ($driver->subscription->active() || $driver->subscription->onGracePeriod()) {
                $planDriver = PlanDriver::where('driver_plan_id', $request->input('data.object.plan.id'))
                    ->first();

                $driver->subscription->update([
                    'name'    => $planDriver->plan->name,
                    'plan_id' => $planDriver->plan->id,
                ]);

                SubscriptionWasUpdated::dispatch($driver->subscription);
            }
        }
    }

    public function handleCustomerSubscriptionDeleted(Request $request): void
    {
        $subscriptionCode = $request->input('data.object.id');

        $driver = SubscriptionDriver::where('driver_subscription_id', $subscriptionCode)
            ->first();

        if ($driver->subscription->active() || $driver->subscription->onGracePeriod()) {
            $driver->subscription->update([
                'status'  => 'completed',
                'ends_at' => now(),
            ]);

            SubscriptionWasExpired::dispatch($driver->subscription);
        }
    }

    public function handleInvoicePaymentSucceeded(Request $request): void
    {
        $subscriptionCode = $request->input('data.object.subscription');
        $customerCode = $request->input('data.object.customer');

        $customer = Customer::where('driver_user_id', $customerCode)
            ->first();

        $subscriptionDriver = SubscriptionDriver::where('driver_subscription_id', $subscriptionCode)
            ->first();

        $customer->user->transactions()->create([
            'status'    => 'completed',
            'driver'    => 'stripe',
            'plan_name' => $subscriptionDriver->subscription->name,
            'reference' => $request->input('data.object.id'),
            'currency'  => $request->input('data.object.currency'),
            'amount'    => $request->input('data.object.amount_paid') / 100,
        ]);
    }

    /*
     * TODO: invoice.payment_failed
     * https://stripe.com/docs/billing/subscriptions/build-subscription?ui=checkout#provision-and-monitor
    */
}
