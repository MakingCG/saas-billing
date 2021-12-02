<?php

namespace Tests\Support\Webhooks;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCreated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasUpdated;
use VueFileManager\Subscription\Support\Events\SubscriptionWasCancelled;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class StripeWebhooksTest extends TestCase
{
    /**
     * @test
     */
    public function paystack_webhook_create_subscription()
    {
        Event::fake([
            SubscriptionWasCreated::class,
        ]);

        $user = User::factory()
            ->create();

        Customer::create([
            'user_id'        => $user->id,
            'driver_user_id' => 'cus_KhYcsbpXlseQU0',
            'driver'         => 'stripe',
        ]);

        // Create plan with features
        $plan = Plan::factory()
            ->hasDrivers([
                'driver'         => 'stripe',
                'driver_plan_id' => 'price_1K1vL5B9m4sTKy1qrkbJ7QEF',
            ])
            ->hasFeatures(2)
            ->create();

        // Send webhook
        $this->postJson('/api/subscriptions/stripe/webhooks', [
            'id'               => 'evt_1K2AJ6B9m4sTKy1qS04uRoOf',
            'object'           => 'event',
            'api_version'      => '2020-08-27',
            'created'          => 1638432379,
            'data'             =>
                [
                    'object' =>
                        [
                            'id'                                => 'sub_1K2AJ4B9m4sTKy1qtr8XNc2D',
                            'object'                            => 'subscription',
                            'application_fee_percent'           => null,
                            'automatic_tax'                     =>
                                [
                                    'enabled' => false,
                                ],
                            'billing_cycle_anchor'              => 1638432378,
                            'billing_thresholds'                => null,
                            'cancel_at'                         => null,
                            'cancel_at_period_end'              => false,
                            'canceled_at'                       => null,
                            'collection_method'                 => 'charge_automatically',
                            'created'                           => 1638432378,
                            'current_period_end'                => 1669968378,
                            'current_period_start'              => 1638432378,
                            'customer'                          => 'cus_KhYcsbpXlseQU0',
                            'days_until_due'                    => null,
                            'default_payment_method'            => null,
                            'default_source'                    => null,
                            'default_tax_rates'                 =>
                                [
                                ],
                            'discount'                          => null,
                            'ended_at'                          => null,
                            'items'                             =>
                                [
                                    'object'      => 'list',
                                    'data'        =>
                                        [
                                            0 =>
                                                [
                                                    'id'                 => 'si_KhZR5Hq52f5FJw',
                                                    'object'             => 'subscription_item',
                                                    'billing_thresholds' => null,
                                                    'created'            => 1638432379,
                                                    'metadata'           =>
                                                        [
                                                        ],
                                                    'plan'               =>
                                                        [
                                                            'id'                => 'price_1K1vL5B9m4sTKy1qrkbJ7QEF',
                                                            'object'            => 'plan',
                                                            'active'            => true,
                                                            'aggregate_usage'   => null,
                                                            'amount'            => 9949,
                                                            'amount_decimal'    => '9949',
                                                            'billing_scheme'    => 'per_unit',
                                                            'created'           => 1638374843,
                                                            'currency'          => 'usd',
                                                            'interval'          => 'year',
                                                            'interval_count'    => 1,
                                                            'livemode'          => false,
                                                            'metadata'          =>
                                                                [
                                                                ],
                                                            'nickname'          => null,
                                                            'product'           => 'prod_KhJzsgmz5m457o',
                                                            'tiers_mode'        => null,
                                                            'transform_usage'   => null,
                                                            'trial_period_days' => null,
                                                            'usage_type'        => 'licensed',
                                                        ],
                                                    'price'              =>
                                                        [
                                                            'id'                  => 'price_1K1vL5B9m4sTKy1qrkbJ7QEF',
                                                            'object'              => 'price',
                                                            'active'              => true,
                                                            'billing_scheme'      => 'per_unit',
                                                            'created'             => 1638374843,
                                                            'currency'            => 'usd',
                                                            'livemode'            => false,
                                                            'lookup_key'          => null,
                                                            'metadata'            =>
                                                                [
                                                                ],
                                                            'nickname'            => null,
                                                            'product'             => 'prod_KhJzsgmz5m457o',
                                                            'recurring'           =>
                                                                [
                                                                    'aggregate_usage'   => null,
                                                                    'interval'          => 'year',
                                                                    'interval_count'    => 1,
                                                                    'trial_period_days' => null,
                                                                    'usage_type'        => 'licensed',
                                                                ],
                                                            'tax_behavior'        => 'unspecified',
                                                            'tiers_mode'          => null,
                                                            'transform_quantity'  => null,
                                                            'type'                => 'recurring',
                                                            'unit_amount'         => 9949,
                                                            'unit_amount_decimal' => '9949',
                                                        ],
                                                    'quantity'           => 1,
                                                    'subscription'       => 'sub_1K2AJ4B9m4sTKy1qtr8XNc2D',
                                                    'tax_rates'          =>
                                                        [
                                                        ],
                                                ],
                                        ],
                                    'has_more'    => false,
                                    'total_count' => 1,
                                    'url'         => '/v1/subscription_items?subscription=sub_1K2AJ4B9m4sTKy1qtr8XNc2D',
                                ],
                            'latest_invoice'                    => 'in_1K2AJ4B9m4sTKy1qMLOPkwSY',
                            'livemode'                          => false,
                            'metadata'                          =>
                                [
                                ],
                            'next_pending_invoice_item_invoice' => null,
                            'pause_collection'                  => null,
                            'payment_settings'                  =>
                                [
                                    'payment_method_options' => null,
                                    'payment_method_types'   => null,
                                ],
                            'pending_invoice_item_interval'     => null,
                            'pending_setup_intent'              => 'seti_1K2AJ5B9m4sTKy1q6Eli1sqb',
                            'pending_update'                    => null,
                            'plan'                              =>
                                [
                                    'id'                => 'price_1K1vL5B9m4sTKy1qrkbJ7QEF',
                                    'object'            => 'plan',
                                    'active'            => true,
                                    'aggregate_usage'   => null,
                                    'amount'            => 9949,
                                    'amount_decimal'    => '9949',
                                    'billing_scheme'    => 'per_unit',
                                    'created'           => 1638374843,
                                    'currency'          => 'usd',
                                    'interval'          => 'year',
                                    'interval_count'    => 1,
                                    'livemode'          => false,
                                    'metadata'          =>
                                        [
                                        ],
                                    'nickname'          => null,
                                    'product'           => 'prod_KhJzsgmz5m457o',
                                    'tiers_mode'        => null,
                                    'transform_usage'   => null,
                                    'trial_period_days' => null,
                                    'usage_type'        => 'licensed',
                                ],
                            'quantity'                          => 1,
                            'schedule'                          => null,
                            'start_date'                        => 1638432378,
                            'status'                            => 'active',
                            'transfer_data'                     => null,
                            'trial_end'                         => null,
                            'trial_start'                       => null,
                        ],
                ],
            'livemode'         => false,
            'pending_webhooks' => 2,
            'request'          =>
                [
                    'id'              => 'req_E392CXtmClwEn8',
                    'idempotency_key' => 'a26d8d85-67a3-47f8-a3a6-be8a945bf25a',
                ],
            'type'             => 'customer.subscription.created',
        ]);

        // Check if subscription was created
        $subscription = Subscription::first();

        // Check relationships are correct
        $this->assertEquals($user->id, $subscription->user->id);
        $this->assertEquals($plan->id, $subscription->plan->id);

        $this->assertDatabaseHas('subscription_drivers', [
            'driver_subscription_id' => 'sub_1K2AJ4B9m4sTKy1qtr8XNc2D',
            'driver'                 => 'stripe',
        ]);

        Event::assertDispatched(fn(SubscriptionWasCreated $event) => $event->subscription->id === $subscription->id);
    }

}
