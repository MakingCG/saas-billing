<?php
namespace Tests\Support\Webhooks;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Event;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class PayPalWebhooksTest extends TestCase
{
    /**
     * @test
     */
    public function webhook_create_subscription()
    {
        Event::fake([
            SubscriptionWasCreated::class,
        ]);

        $user = User::factory()
            ->create();

        // Create plan with features
        $plan = Plan::factory()
            ->hasFeatures(2)
            ->create();

        $planDriver = $plan->drivers()->create([
            'driver_plan_id' => 'P-1P873319R2491082NMGFK3RY',
            'driver'         => 'paypal',
        ]);

        // Send webhook
        $this->postJson('/api/subscription/paypal/webhooks', [
            'id'               => 'WH-8A715371GG332831A-4MM87741Y6956121U',
            'event_version'    => '1.0',
            'create_time'      => '2021-11-10T06:53:31.290Z',
            'resource_type'    => 'subscription',
            'resource_version' => '2.0',
            'event_type'       => 'BILLING.SUBSCRIPTION.CREATED',
            'summary'          => 'Subscription created',
            'resource'         => [
                'start_time'      => '2021-11-10T06:53:31Z',
                'quantity'        => '1',
                'create_time'     => '2021-11-10T06:53:31Z',
                'custom_id'       => $user->id,
                'links'           => [
                    [
                        'href'   => 'https://www.sandbox.paypal.com/webapps/billing/subscriptions?ba_token=BA-88260049KY7916255',
                        'rel'    => 'approve',
                        'method' => 'GET',
                    ],
                    [
                        'href'   => 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-KHY6B042F1YA',
                        'rel'    => 'edit',
                        'method' => 'PATCH',
                    ],
                    [
                        'href'   => 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-KHY6B042F1YA',
                        'rel'    => 'self',
                        'method' => 'GET',
                    ],
                ],
                'id'              => 'I-KHY6B042F1YA',
                'plan_overridden' => false,
                'plan_id'         => $planDriver->driver_plan_id,
                'status'          => 'APPROVAL_PENDING',
            ],
            'links'            => [
                [
                    'href'   => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-8A715371GG332831A-4MM87741Y6956121U',
                    'rel'    => 'self',
                    'method' => 'GET',
                ],
                [
                    'href'   => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-8A715371GG332831A-4MM87741Y6956121U/resend',
                    'rel'    => 'resend',
                    'method' => 'POST',
                ],
            ],
        ]);

        // Check if subscription was created
        $subscription = Subscription::first();

        // Check relationships are correct
        $this->assertEquals($user->id, $subscription->user->id);
        $this->assertEquals($plan->id, $subscription->plan->id);

        $this->assertDatabaseHas('subscription_drivers', [
            'driver_subscription_id' => 'I-KHY6B042F1YA',
        ]);

        Event::assertDispatched(fn (SubscriptionWasCreated $event) => $event->subscription->id === $subscription->id);
    }

    /**
     * TODO make webhook test
     */
    public function webhook_cancel_subscription()
    {
    }
}
