<?php
namespace Tests\Domain\Subscriptions;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Event;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class SubscriptionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_get_webhook_and_create_subscription()
    {
        Event::fake([
            SubscriptionWasCreated::class,
        ]);

        $user = User::factory()
            ->create();

        // Create customer
        $customer = Customer::factory()
            ->create([
                'user_id'        => $user->id,
                'driver_user_id' => 'CUS_xnxdt6s1zg1f4nx',
                'driver'         => 'paystack',
            ]);

        // Create plan with features
        $plan = Plan::factory()
            ->hasFeatures(2)
            ->create();

        $planDriver = $plan->drivers()->create([
            'driver_plan_id' => 'PLN_gx2wn530m0i3w3m',
            'driver'         => 'paystack',
        ]);

        // Send webhook
        $this->postJson('/api/subscription/webhooks', [
            'event' => 'subscription.create',
            'data'  => [
                'domain'            => 'test',
                'status'            => 'active',
                'subscription_code' => 'SUB_vsyqdmlzble3uii',
                'amount'            => 50000,
                'cron_expression'   => '0 0 28 * *',
                'next_payment_date' => '2016-05-19T07:00:00.000Z',
                'open_invoice'      => null,
                'createdAt'         => '2016-03-20T00:23:24.000Z',
                'plan'              => [
                    'name'          => 'Monthly retainer',
                    'plan_code'     => $planDriver->driver_plan_id,
                    'description'   => null,
                    'amount'        => $plan->amount,
                    'interval'      => $plan->interval,
                    'send_invoices' => true,
                    'send_sms'      => true,
                    'currency'      => 'NGN',
                ],
                'authorization'     => [
                    'authorization_code' => 'AUTH_96xphygz',
                    'bin'                => '539983',
                    'last4'              => '7357',
                    'exp_month'          => '10',
                    'exp_year'           => '2017',
                    'card_type'          => 'MASTERCARD DEBIT',
                    'bank'               => 'GTBANK',
                    'country_code'       => 'NG',
                    'brand'              => 'MASTERCARD',
                    'account_name'       => 'BoJack Horseman',
                ],
                'customer'          => [
                    'first_name'    => 'BoJack',
                    'last_name'     => 'Horseman',
                    'email'         => 'bojack@horsinaround.com',
                    'customer_code' => $customer->driver_user_id,
                    'phone'         => '',
                    'metadata'      => [
                    ],
                    'risk_action'   => 'default',
                ],
                'created_at'        => '2016-10-01T10:59:59.000Z',
            ],
        ]);

        // Check if subscription was created
        $subscription = Subscription::first();

        // Check relationships are correct
        $this->assertEquals($user->id, $subscription->user->id);
        $this->assertEquals($plan->id, $subscription->plan->id);

        $this->assertDatabaseHas('subscriptions', [
            'driver_subscription_id' => $subscription->driver_subscription_id,
        ]);

        Event::assertDispatched(fn (SubscriptionWasCreated $event) => $event->subscription->id === $subscription->id);
    }
}
