<?php
namespace Tests\Support\Webhooks;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Tests\Mocking\PayPal\VerifyWebhookPayPalMocksClass;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasUpdated;
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
            ->hasDrivers([
                'driver' => 'paypal',
            ])
            ->hasFixedFeatures(2)
            ->create();

        resolve(VerifyWebhookPayPalMocksClass::class)();

        // Send webhook
        $this
            ->postJson('/api/subscriptions/paypal/webhooks', [
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
                    'plan_id'         => $plan->driverId('paypal'),
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
            ])
            ->assertOk();

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
    public function paypal_webhook_update_subscription()
    {
        Event::fake([
            SubscriptionWasUpdated::class,
        ]);

        [$plan, $planHigher] = Plan::factory()
            ->hasDrivers([
                'driver' => 'paypal',
            ])
            ->count(2)
            ->create();

        $subscription = Subscription::factory()
            ->hasDriver([
                'driver' => 'paypal',
            ])
            ->create([
                'plan_id' => $plan->id,
                'name'    => $plan->name,
                'status'  => 'active',
            ]);

        resolve(VerifyWebhookPayPalMocksClass::class)();

        // Send webhook
        $this->postJson('/api/subscriptions/paypal/webhooks', [
            'id'               => 'WH-5W900153L71312432-5CW911939G6805917',
            'create_time'      => '2021-11-16T10:50:47.259Z',
            'resource_type'    => 'subscription',
            'event_type'       => 'BILLING.SUBSCRIPTION.UPDATED',
            'summary'          => 'Subscription updated',
            'resource'         => [
                'quantity'           => '1',
                'subscriber'         => [
                    'email_address'    => 'ernest@azet.sk',
                    'payer_id'         => 'XEBW65LBRMPMA',
                    'name'             => [
                        'given_name' => 'Michal',
                        'surname'    => 'Kamenicky',
                    ],
                    'shipping_address' => [
                        'address' => [
                            'address_line_1' => '1 Main St',
                            'admin_area_2'   => 'San Jose',
                            'admin_area_1'   => 'CA',
                            'postal_code'    => '95131',
                            'country_code'   => 'US',
                        ],
                    ],
                ],
                'create_time'        => '2021-11-10T06:42:47Z',
                'custom_id'          => 'user_id_howdy',
                'plan_overridden'    => false,
                'shipping_amount'    => [
                    'currency_code' => 'USD',
                    'value'         => '0.0',
                ],
                'start_time'         => '2021-11-10T06:42:22Z',
                'update_time'        => '2021-11-10T06:42:48Z',
                'billing_info'       => [
                    'outstanding_balance'   => [
                        'currency_code' => 'USD',
                        'value'         => '0.0',
                    ],
                    'cycle_executions'      => [
                        [
                            'tenure_type'                    => 'REGULAR',
                            'sequence'                       => 1,
                            'cycles_completed'               => 0,
                            'cycles_remaining'               => 0,
                            'current_pricing_scheme_version' => 1,
                            'total_cycles'                   => 0,
                        ],
                    ],
                    'last_payment'          => [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value'         => '500.0',
                        ],
                        'time'   => '2021-11-10T06:42:48Z',
                    ],
                    'next_billing_time'     => '2021-12-10T10:00:00Z',
                    'failed_payments_count' => 0,
                ],
                'links'              => [
                    [
                        'href'    => 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-GW6LUW7CW1AC/cancel',
                        'rel'     => 'cancel',
                        'method'  => 'POST',
                        'encType' => 'application/json',
                    ],
                    [
                        'href'    => 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-GW6LUW7CW1AC',
                        'rel'     => 'edit',
                        'method'  => 'PATCH',
                        'encType' => 'application/json',
                    ],
                    [
                        'href'    => 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-GW6LUW7CW1AC',
                        'rel'     => 'self',
                        'method'  => 'GET',
                        'encType' => 'application/json',
                    ],
                    [
                        'href'    => 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-GW6LUW7CW1AC/suspend',
                        'rel'     => 'suspend',
                        'method'  => 'POST',
                        'encType' => 'application/json',
                    ],
                    [
                        'href'    => 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-GW6LUW7CW1AC/capture',
                        'rel'     => 'capture',
                        'method'  => 'POST',
                        'encType' => 'application/json',
                    ],
                ],
                'id'                 => $subscription->driverId(),
                'plan_id'            => $planHigher->driverId('paypal'),
                'status'             => 'ACTIVE',
                'status_update_time' => '2021-11-10T06:42:48Z',
            ],
            'status'           => 'PENDING',
            'transmissions'    => [
                [
                    'webhook_url'      => 'https://internanogalakticky.vuefilemanager.com/api/subscriptions/paypal/webhooks',
                    'http_status'      => 500,
                    'reason_phrase'    => 'HTTP/1.1 200 Connection established',
                    'response_headers' => [
                        'Transfer-Encoding'           => 'chunked',
                        'date'                        => 'Tue, 16 Nov 2021 10:53:54 GMT',
                        'Server'                      => 'nginx/1.14.2',
                        'Cache-Control'               => 'no-cache, private',
                        'Access-Control-Allow-Origin' => '*',
                        'Connection'                  => 'keep-alive',
                        'Content-Type'                => 'text/html; charset=UTF-8',
                    ],
                    'transmission_id'  => '1092bc00-46cb-11ec-9b26-e1d8a0400366',
                    'status'           => 'PENDING',
                    'timestamp'        => '2021-11-16T10:50:51Z',
                ],
            ],
            'links'            => [
                [
                    'href'    => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-5W900153L71312432-5CW911939G6805917',
                    'rel'     => 'self',
                    'method'  => 'GET',
                    'encType' => 'application/json',
                ],
                [
                    'href'    => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-5W900153L71312432-5CW911939G6805917/resend',
                    'rel'     => 'resend',
                    'method'  => 'POST',
                    'encType' => 'application/json',
                ],
            ],
            'event_version'    => '1.0',
            'resource_version' => '2.0',
        ])
            ->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'plan_id' => $planHigher->id,
            'name'    => $planHigher->name,
        ]);

        Event::assertDispatched(fn (SubscriptionWasUpdated $event) => $event->subscription->id === $subscription->id);
    }

    /**
     * @test
     */
    public function paypal_webhook_cancel_subscription()
    {
        Event::fake([
            SubscriptionWasCancelled::class,
        ]);

        $subscription = Subscription::factory()
            ->hasDriver([
                'driver' => 'paypal',
            ])
            ->create([
                'status'     => 'active',
                'ends_at'    => null,
                'created_at' => now()->subDays(14),
            ]);

        $cancelledAt = now()->addDays(14);

        resolve(VerifyWebhookPayPalMocksClass::class)();

        // Send webhook
        $this->postJson('/api/subscriptions/paypal/webhooks', [
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
        ])
            ->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'status'  => 'cancelled',
            'ends_at' => $cancelledAt,
        ]);

        Event::assertDispatched(fn (SubscriptionWasCancelled $event) => $event->subscription->id === $subscription->id);
    }

    /**
     * @test
     */
    public function paypal_webhook_payment_sale_completed()
    {
        $user = User::factory()
            ->create();

        $plan = Plan::factory()
            ->hasDrivers([
                'driver' => 'paypal',
            ])
            ->create();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token'            => Http::response([
                'scope'        => 'scope',
                'access_token' => 'jnjleqngtlq3l34jn6l2346n2l4',
                'token_type'   => 'Bearer',
                'app_id'       => 'APP-80W284485P519543T',
                'expires_in'   => 31349,
                'nonce'        => 'nonce',
            ], 204),
            'https://api-m.sandbox.paypal.com/v1/billing/subscriptions/*' => Http::response([
                'id'                 => 'I-6W1M3FWTVL19',
                'plan_id'            => $plan->driverId('paypal'),
                'start_time'         => '2019-04-10T07:00:00Z',
                'quantity'           => '20',
                'shipping_amount'    => [
                    'currency_code' => 'USD',
                    'value'         => '10.0',
                ],
                'subscriber'         => [
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
                    'name'             => [
                        'given_name' => 'John',
                        'surname'    => 'Doe',
                    ],
                    'email_address'    => 'customer@example.com',
                    'payer_id'         => '2J6QB8YJQSJRJ',
                ],
                'billing_info'       => [
                    'outstanding_balance'   => [
                        'currency_code' => 'USD',
                        'value'         => '1.0',
                    ],
                    'cycle_executions'      => [
                        [
                            'tenure_type'      => 'TRIAL',
                            'sequence'         => 1,
                            'cycles_completed' => 0,
                            'cycles_remaining' => 2,
                            'total_cycles'     => 2,
                        ],
                        [
                            'tenure_type'      => 'TRIAL',
                            'sequence'         => 2,
                            'cycles_completed' => 0,
                            'cycles_remaining' => 3,
                            'total_cycles'     => 3,
                        ],
                        [
                            'tenure_type'      => 'REGULAR',
                            'sequence'         => 3,
                            'cycles_completed' => 0,
                            'cycles_remaining' => 12,
                            'total_cycles'     => 12,
                        ],
                    ],
                    'last_payment'          => [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value'         => '1.15',
                        ],
                        'time'   => '2019-04-09T10:27:20Z',
                    ],
                    'next_billing_time'     => '2019-04-09T10:26:04Z',
                    'failed_payments_count' => 0,
                ],
                'create_time'        => '2019-04-09T10:26:04Z',
                'update_time'        => '2019-04-09T10:27:27Z',
                'links'              => [
                    [
                        'href'   => 'https://api-m.paypal.com/v1/billing/subscriptions/I-BW452GLLEP1G/cancel',
                        'rel'    => 'cancel',
                        'method' => 'POST',
                    ],
                    [
                        'href'   => 'https://api-m.paypal.com/v1/billing/subscriptions/I-BW452GLLEP1G',
                        'rel'    => 'edit',
                        'method' => 'PATCH',
                    ],
                    [
                        'href'   => 'https://api-m.paypal.com/v1/billing/subscriptions/I-BW452GLLEP1G',
                        'rel'    => 'self',
                        'method' => 'GET',
                    ],
                    [
                        'href'   => 'https://api-m.paypal.com/v1/billing/subscriptions/I-BW452GLLEP1G/suspend',
                        'rel'    => 'suspend',
                        'method' => 'POST',
                    ],
                    [
                        'href'   => 'https://api-m.paypal.com/v1/billing/subscriptions/I-BW452GLLEP1G/capture',
                        'rel'    => 'capture',
                        'method' => 'POST',
                    ],
                ],
                'status'             => 'ACTIVE',
                'status_update_time' => '2019-04-09T10:27:27Z',
            ]),
        ]);

        resolve(VerifyWebhookPayPalMocksClass::class)();

        $this->postJson('/api/subscriptions/paypal/webhooks', [
            'id'            => 'WH-9XV66238KD489590N-2R389597JR522592U',
            'create_time'   => '2021-11-18T07:58:41.727Z',
            'resource_type' => 'sale',
            'event_type'    => 'PAYMENT.SALE.COMPLETED',
            'summary'       => 'Payment completed for $ 10.0 USD',
            'resource'      => [
                'amount'                      => [
                    'total'    => '10.00',
                    'currency' => 'USD',
                    'details'  => [
                        'subtotal' => '10.00',
                    ],
                ],
                'payment_mode'                => 'INSTANT_TRANSFER',
                'create_time'                 => '2021-11-18T07:58:19Z',
                'custom'                      => $user->id,
                'transaction_fee'             => [
                    'currency' => 'USD',
                    'value'    => '0.64',
                ],
                'billing_agreement_id'        => 'I-6W1M3FWTVL19',
                'update_time'                 => '2021-11-18T07:58:19Z',
                'soft_descriptor'             => 'PAYPAL *MAKINGCG',
                'protection_eligibility_type' => 'ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE',
                'protection_eligibility'      => 'ELIGIBLE',
                'links'                       => [
                    [
                        'method' => 'GET',
                        'rel'    => 'self',
                        'href'   => 'https://api.sandbox.paypal.com/v1/payments/sale/25S0310919017921W',
                    ],
                    [
                        'method' => 'POST',
                        'rel'    => 'refund',
                        'href'   => 'https://api.sandbox.paypal.com/v1/payments/sale/25S0310919017921W/refund',
                    ],
                ],
                'id'                          => '25S0310919017921W',
                'state'                       => 'completed',
                'invoice_number'              => '',
            ],
            'status'        => 'PENDING',
            'transmissions' => [
                [
                    'webhook_url'      => 'https://internanogalakticky.vuefilemanager.com/api/subscriptions/paypal/webhooks',
                    'http_status'      => 500,
                    'reason_phrase'    => 'HTTP/1.1 200 Connection established',
                    'response_headers' => [
                        'Transfer-Encoding'           => 'chunked',
                        'date'                        => 'Thu, 18 Nov 2021 07:58:56 GMT',
                        'Server'                      => 'nginx/1.14.2',
                        'Cache-Control'               => 'no-cache, private',
                        'Access-Control-Allow-Origin' => '*',
                        'Connection'                  => 'keep-alive',
                        'Content-Type'                => 'text/html; charset=UTF-8',
                    ],
                    'transmission_id'  => '5af9daf0-4845-11ec-939e-edb49e359949',
                    'status'           => 'PENDING',
                    'timestamp'        => '2021-11-18T07:58:45Z',
                ],
            ],
            'links'         => [
                [
                    'href'    => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-9XV66238KD489590N-2R389597JR522592U',
                    'rel'     => 'self',
                    'method'  => 'GET',
                    'encType' => 'application/json',
                ],
                [
                    'href'    => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-9XV66238KD489590N-2R389597JR522592U/resend',
                    'rel'     => 'resend',
                    'method'  => 'POST',
                    'encType' => 'application/json',
                ],
            ],
            'event_version' => '1.0',
        ])
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'user_id'   => $user->id,
            'status'    => 'completed',
            'plan_name' => $plan->name,
            'currency'  => 'USD',
            'amount'    => 10,
            'driver'    => 'paypal',
            'reference' => 'I-6W1M3FWTVL19',
        ]);
    }
}
