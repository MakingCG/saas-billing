<?php
namespace Tests\Support\Webhooks;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Event;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCancelled;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class PayPalWebhooksTest extends TestCase
{
    /**
     * @test
     */
    public function paypal_webhook_create_subscription()
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
     * @test
     */
    public function paypal_webhook_cancel_subscription()
    {
        Event::fake([
            SubscriptionWasCancelled::class,
        ]);

        $user = User::factory()
            ->create();

        $subscription = Subscription::factory()
            ->hasDriver([
                'driver' => 'paypal',
            ])
            ->create([
                'user_id'    => $user->id,
                'status'     => 'active',
                'ends_at'    => null,
                'created_at' => now()->subDays(14),
            ]);

        $cancelledAt = now()->addDays(14);

        // Send webhook
        $this->postJson('/api/subscription/paypal/webhooks', [
            'id'               => 'WH-UY687577TY25889J9-2R6T55435R66168Y6',
            'create_time'      => '2018-19-12T22:20:32.000Z',
            'resource_type'    => 'subscription',
            'event_type'       => 'BILLING.SUBSCRIPTION.CANCELLED',
            'summary'          => 'A billing subscription was cancelled.',
            'resource'         => [
                'quantity'           => '20',
                'subscriber'         => [
                    'name'             => [
                        'given_name' => 'John',
                        'surname'    => 'Doe',
                    ],
                    'email_address'    => 'customer@example.com',
                    'shipping_address' => [
                        'name'    => [
                            'full_name' => 'John Doe',
                        ],
                        'address' => [
                            'address_line_1' => '2211 N First Street',
                            'address_line_2' => 'Building 17',
                            'admin_area_2'   => 'San Jose',
                            'admin_area_1'   => 'CA',
                            'postal_code'    => '95131',
                            'country_code'   => 'US',
                        ],
                    ],
                ],
                'create_time'        => '2018-12-10T21:20:49Z',
                'shipping_amount'    => [
                    'currency_code' => 'USD',
                    'value'         => '10.00',
                ],
                'start_time'         => '2018-11-01T00:00:00Z',
                'update_time'        => '2018-12-10T21:20:49Z',
                'billing_info'       => [
                    'outstanding_balance'   => [
                        'currency_code' => 'USD',
                        'value'         => '10.00',
                    ],
                    'cycle_executions'      => [
                        [
                            'tenure_type'                    => 'TRIAL',
                            'sequence'                       => 1,
                            'cycles_completed'               => 1,
                            'cycles_remaining'               => 0,
                            'current_pricing_scheme_version' => 1,
                        ],
                        [
                            'tenure_type'                    => 'REGULAR',
                            'sequence'                       => 2,
                            'cycles_completed'               => 1,
                            'cycles_remaining'               => 0,
                            'current_pricing_scheme_version' => 1,
                        ],
                    ],
                    'last_payment'          => [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value'         => '500.00',
                        ],
                        'time'   => '2018-12-01T01:20:49Z',
                    ],
                    'next_billing_time'     => $cancelledAt,
                    'final_payment_time'    => '2020-01-01T00:20:49Z',
                    'failed_payments_count' => 2,
                ],
                'links'              => [
                    [
                        'href'   => 'https://api.paypal.com/v1/billing/subscriptions/I-BW452GLLEP1G',
                        'rel'    => 'self',
                        'method' => 'GET',
                    ],
                    [
                        'href'   => 'https://api.paypal.com/v1/billing/subscriptions/I-BW452GLLEP1G',
                        'rel'    => 'edit',
                        'method' => 'PATCH',
                    ],
                ],
                'id'                 => $subscription->driverId(),
                'plan_id'            => 'P-5ML4271244454362WXNWU5NQ',
                'auto_renewal'       => true,
                'status'             => 'CANCELLED',
                'status_update_time' => '2018-12-10T21:20:49Z',
            ],
            'links'            => [
                [
                    'href'    => 'https://api.paypal.com/v1/notifications/webhooks-events/WH-UY687577TY25889J9-2R6T55435R66168Y6',
                    'rel'     => 'self',
                    'method'  => 'GET',
                    'encType' => 'application/json',
                ],
                [
                    'href'    => 'https://api.paypal.com/v1/notifications/webhooks-events/WH-UY687577TY25889J9-2R6T55435R66168Y6/resend',
                    'rel'     => 'resend',
                    'method'  => 'POST',
                    'encType' => 'application/json',
                ],
            ],
            'event_version'    => '1.0',
            'resource_version' => '2.0',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'status'  => 'cancelled',
            'ends_at' => $cancelledAt,
        ]);

        Event::assertDispatched(fn (SubscriptionWasCancelled $event) => $event->subscription->id === $subscription->id);
    }
}
