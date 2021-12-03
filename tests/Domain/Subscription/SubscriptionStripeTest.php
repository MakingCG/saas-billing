<?php

namespace Tests\Domain\Subscription;

use Carbon\Carbon;
use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Http;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class SubscriptionStripeTest extends TestCase
{
    /**
     * @test
     */
    public function it_cancel_stripe_subscription()
    {
        $user = User::factory()
            ->create();

        $subscription = Subscription::factory()
            ->hasDriver([
                'driver'                 => 'stripe',
                'driver_subscription_id' => 'sub_1K2XDAJDPzmN3vrvWtvQLNYT',
            ])
            ->create([
                'user_id'    => $user->id,
                'status'     => 'active',
                'ends_at'    => null,
                'created_at' => now()->subDays(14),
            ]);

        Http::fake([
            "https://api.stripe.com/v1/subscriptions/{$subscription->driverId()}" => Http::response([
                'id'                                => 'sub_1K2XDAJDPzmN3vrvWtvQLNYT',
                'object'                            => 'subscription',
                'application_fee_percent'           => NULL,
                'automatic_tax'                     => [
                    'enabled' => false,
                ],
                'billing_cycle_anchor'              => 1638520424,
                'billing_thresholds'                => NULL,
                'cancel_at'                         => NULL,
                'cancel_at_period_end'              => false,
                'canceled_at'                       => 1638520426,
                'collection_method'                 => 'charge_automatically',
                'created'                           => 1638520424,
                'current_period_end'                => 1641198824,
                'current_period_start'              => 1638520424,
                'customer'                          => 'cus_Khx75aZbZBpSrn',
                'days_until_due'                    => NULL,
                'default_payment_method'            => NULL,
                'default_source'                    => NULL,
                'default_tax_rates'                 => [
                ],
                'discount'                          => NULL,
                'ended_at'                          => NULL,
                'items'                             => [
                    'object'   => 'list',
                    'data'     => [
                        [
                            'id'                 => 'si_Khx7WgescRFpRF',
                            'object'             => 'subscription_item',
                            'billing_thresholds' => NULL,
                            'created'            => 1638520425,
                            'metadata'           => [
                            ],
                            'price'              => [
                                'id'                  => 'gold',
                                'object'              => 'price',
                                'active'              => true,
                                'billing_scheme'      => 'per_unit',
                                'created'             => 1590175305,
                                'currency'            => 'eur',
                                'livemode'            => false,
                                'lookup_key'          => NULL,
                                'metadata'            => [
                                ],
                                'nickname'            => NULL,
                                'product'             => 'prod_HKL7vHEYRSC4Ur',
                                'recurring'           => [
                                    'aggregate_usage' => NULL,
                                    'interval'        => 'month',
                                    'interval_count'  => 1,
                                    'usage_type'      => 'licensed',
                                ],
                                'tax_behavior'        => 'unspecified',
                                'tiers_mode'          => NULL,
                                'transform_quantity'  => NULL,
                                'type'                => 'recurring',
                                'unit_amount'         => 2000,
                                'unit_amount_decimal' => '2000',
                            ],
                            'quantity'           => 1,
                            'subscription'       => 'sub_1K2XDAJDPzmN3vrvWtvQLNYT',
                            'tax_rates'          => [
                            ],
                        ],
                    ],
                    'has_more' => false,
                    'url'      => '/v1/subscription_items?subscription=sub_1K2XDAJDPzmN3vrvWtvQLNYT',
                ],
                'latest_invoice'                    => NULL,
                'livemode'                          => false,
                'metadata'                          => [
                ],
                'next_pending_invoice_item_invoice' => NULL,
                'pause_collection'                  => NULL,
                'payment_settings'                  => [
                    'payment_method_options' => NULL,
                    'payment_method_types'   => NULL,
                ],
                'pending_invoice_item_interval'     => NULL,
                'pending_setup_intent'              => NULL,
                'pending_update'                    => NULL,
                'schedule'                          => NULL,
                'start_date'                        => 1638520424,
                'status'                            => 'canceled',
                'transfer_data'                     => NULL,
                'trial_end'                         => NULL,
                'trial_start'                       => NULL,
            ]),
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/subscriptions/cancel')
            ->assertOk()
            ->assertJsonFragment([
                'status' => 'cancelled',
            ]);

        $subscription->refresh();

        $this
            ->assertDatabaseHas('subscriptions', [
                'status'  => 'cancelled',
                'ends_at' => Carbon::createFromTimestamp(1641198824),
            ])
            ->assertEquals(true, $subscription->onGracePeriod());

        Http::assertSentCount(1);
    }
}
