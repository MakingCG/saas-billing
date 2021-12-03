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
                'application_fee_percent'           => null,
                'automatic_tax'                     => [
                    'enabled' => false,
                ],
                'billing_cycle_anchor'              => 1638520424,
                'billing_thresholds'                => null,
                'cancel_at'                         => null,
                'cancel_at_period_end'              => false,
                'canceled_at'                       => 1638520426,
                'collection_method'                 => 'charge_automatically',
                'created'                           => 1638520424,
                'current_period_end'                => 1641198824,
                'current_period_start'              => 1638520424,
                'customer'                          => 'cus_Khx75aZbZBpSrn',
                'days_until_due'                    => null,
                'default_payment_method'            => null,
                'default_source'                    => null,
                'default_tax_rates'                 => [
                ],
                'discount'                          => null,
                'ended_at'                          => null,
                'items'                             => [
                    'object'   => 'list',
                    'data'     => [
                        [
                            'id'                 => 'si_Khx7WgescRFpRF',
                            'object'             => 'subscription_item',
                            'billing_thresholds' => null,
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
                                'lookup_key'          => null,
                                'metadata'            => [
                                ],
                                'nickname'            => null,
                                'product'             => 'prod_HKL7vHEYRSC4Ur',
                                'recurring'           => [
                                    'aggregate_usage' => null,
                                    'interval'        => 'month',
                                    'interval_count'  => 1,
                                    'usage_type'      => 'licensed',
                                ],
                                'tax_behavior'        => 'unspecified',
                                'tiers_mode'          => null,
                                'transform_quantity'  => null,
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
                'latest_invoice'                    => null,
                'livemode'                          => false,
                'metadata'                          => [
                ],
                'next_pending_invoice_item_invoice' => null,
                'pause_collection'                  => null,
                'payment_settings'                  => [
                    'payment_method_options' => null,
                    'payment_method_types'   => null,
                ],
                'pending_invoice_item_interval'     => null,
                'pending_setup_intent'              => null,
                'pending_update'                    => null,
                'schedule'                          => null,
                'start_date'                        => 1638520424,
                'status'                            => 'canceled',
                'transfer_data'                     => null,
                'trial_end'                         => null,
                'trial_start'                       => null,
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
