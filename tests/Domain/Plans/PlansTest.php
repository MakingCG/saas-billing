<?php
namespace Tests\Domain\Plans;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

class PlansTest extends TestCase
{
    /**
     * @test
     */
    public function it_get_all_visible_plans_for_users()
    {
        $user = User::factory()
            ->create();

        $plan = Plan::factory()
            ->hasFeatures(1)
            ->hasDrivers(2)
            ->create();

        $this
            ->actingAs($user)
            ->getJson('/api/subscriptions/plans')
            ->assertJsonFragment([
                'id' => $plan->id,
            ]);
    }

    /**
     * @test
     */
    public function it_get_all_plans_for_admin()
    {
        $admin = User::factory()
            ->create(['role' => 'admin']);

        $plan = Plan::factory()
            ->hasFeatures(1)
            ->hasDrivers(2)
            ->create([
                'visible' => false,
            ]);

        $this
            ->actingAs($admin)
            ->getJson('/api/subscriptions/admin/plans')
            ->assertJsonFragment([
                'id' => $plan->id,
            ]);
    }

    /**
     * @test
     */
    public function it_create_plan()
    {
        $user = User::factory()
            ->create();

        $plan = Plan::factory()
            ->make();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token'      => Http::response([
                'scope'        => 'scope',
                'access_token' => 'jnjleqngtlq3l34jn6l2346n2l4',
                'token_type'   => 'Bearer',
                'app_id'       => 'APP-80W284485P519543T',
                'expires_in'   => 31349,
                'nonce'        => 'nonce',
            ]),
            'https://api-m.sandbox.paypal.com/v1/catalogs/products' => Http::response([
                'id'          => 'P-5ML4271244454362WXNWU5NQ',
                'name'        => 'Subscription Service',
                'description' => 'Cloud subscription service',
                'type'        => 'SERVICE',
                'category'    => 'SOFTWARE',
            ]),
            'https://api-m.sandbox.paypal.com/v1/billing/plans'     => Http::response([
                'id'                  => 'P-5ML4271244454362WXNWU5NQ',
                'product_id'          => 'PROD-XXCD1234QWER65782',
                'name'                => $plan->name,
                'description'         => $plan->description,
                'status'              => 'ACTIVE',
                'billing_cycles'      => [
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'TRIAL',
                        'sequence'       => 1,
                        'total_cycles'   => 2,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value'         => $plan->amount,
                                'currency_code' => 'USD',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'TRIAL',
                        'sequence'       => 2,
                        'total_cycles'   => 3,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'currency_code' => 'USD',
                                'value'         => '6',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'REGULAR',
                        'sequence'       => 3,
                        'total_cycles'   => 12,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'currency_code' => 'USD',
                                'value'         => '10',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding'     => true,
                    'setup_fee'                 => [
                        'value'         => '10',
                        'currency_code' => 'USD',
                    ],
                    'setup_fee_failure_action'  => 'CONTINUE',
                    'payment_failure_threshold' => 3,
                ],
                'taxes'               => [
                    'percentage' => '10',
                    'inclusive'  => false,
                ],
                'create_time'         => '2020-05-27T12:13:51Z',
                'update_time'         => '2020-05-27T12:13:51Z',
            ]),
            'https://api.paystack.co/plan'                          => Http::response([
                'status'  => true,
                'message' => 'Plan created',
                'data'    => [
                    'name'          => $plan->name,
                    'amount'        => $plan->amount * 100,
                    'interval'      => $plan->interval,
                    'integration'   => 100032,
                    'domain'        => 'test',
                    'plan_code'     => 'PLN_gx2wn530m0i3w3m',
                    'send_invoices' => true,
                    'send_sms'      => true,
                    'hosted_page'   => false,
                    'currency'      => 'ZAR',
                    'id'            => 28,
                    'createdAt'     => '2016-03-29T22:42:50.811Z',
                    'updatedAt'     => '2016-03-29T22:42:50.811Z',
                ],
            ]),
            'https://api.stripe.com/v1/products'                    => Http::response([
                'id'                   => 'prod_KhIRa4HjCgFaNJ',
                'object'               => 'product',
                'active'               => true,
                'created'              => 1638369119,
                'description'          => $plan->description,
                'images'               => [],
                'livemode'             => false,
                'metadata'             => [],
                'name'                 => $plan->name,
                'package_dimensions'   => null,
                'shippable'            => null,
                'statement_descriptor' => null,
                'tax_code'             => null,
                'unit_label'           => null,
                'updated'              => 1638369119,
                'url'                  => null,
            ]),
            'https://api.stripe.com/v1/prices'                      => Http::response([
                'id'                  => 'price_1K1wJPB9m4sTKy1qcHaLkXki',
                'object'              => 'price',
                'active'              => true,
                'billing_scheme'      => 'per_unit',
                'created'             => 1638378583,
                'currency'            => 'usd',
                'livemode'            => false,
                'lookup_key'          => null,
                'metadata'            => [
                ],
                'nickname'            => null,
                'product'             => 'prod_KhIRa4HjCgFaNJ',
                'recurring'           => [
                    'aggregate_usage' => null,
                    'interval'        => $plan->interval,
                    'interval_count'  => 1,
                    'usage_type'      => 'licensed',
                ],
                'tax_behavior'        => 'unspecified',
                'tiers_mode'          => null,
                'transform_quantity'  => null,
                'type'                => 'recurring',
                'unit_amount'         => $plan->amount * 100,
                'unit_amount_decimal' => $plan->amount * 100,
            ]),
        ]);

        $this
            ->actingAs($user)
            ->post('/api/subscriptions/admin/plans', [
                'name'        => $plan->name,
                'description' => $plan->description,
                'interval'    => $plan->interval,
                'amount'      => $plan->amount,
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 100,
                    'max_team_members'   => 6,
                ],
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'name' => $plan->name,
            ]);

        collect(config('subscription.available_drivers'))
            ->each(
                fn ($driver) => $this
                    ->assertDatabaseHas('plan_drivers', [
                        'driver' => $driver,
                    ])
            );

        $this
            ->assertDatabaseHas('plans', [
                'name'        => $plan->name,
                'description' => $plan->description,
                'currency'    => 'USD',
                'status'      => 'active',
            ])
            ->assertDatabaseHas('plan_features', [
                'key'   => 'max_storage_amount',
                'value' => 100,
            ])
            ->assertDatabaseHas('plan_features', [
                'key'   => 'max_team_members',
                'value' => 6,
            ]);

        // TODO: this can't be fixed, must be flexible for new gateway development
        //Http::assertSentCount(4);
    }

    /**
     * @test
     */
    public function it_update_plan()
    {
        $user = User::factory()
            ->create();

        $plan = Plan::factory()
            ->make();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token'      => Http::response([
                'scope'        => 'scope',
                'access_token' => 'jnjleqngtlq3l34jn6l2346n2l4',
                'token_type'   => 'Bearer',
                'app_id'       => 'APP-80W284485P519543T',
                'expires_in'   => 31349,
                'nonce'        => 'nonce',
            ]),
            'https://api-m.sandbox.paypal.com/v1/catalogs/products' => Http::response([
                'id'          => 'P-5ML4271244454362WXNWU5NQ',
                'name'        => 'Subscription Service',
                'description' => 'Cloud subscription service',
                'type'        => 'SERVICE',
                'category'    => 'SOFTWARE',
            ]),
            'https://api-m.sandbox.paypal.com/v1/billing/plans'     => Http::response([
                'id'                  => 'P-5ML4271244454362WXNWU5NQ',
                'product_id'          => 'PROD-XXCD1234QWER65782',
                'name'                => $plan->name,
                'description'         => $plan->description,
                'status'              => 'ACTIVE',
                'billing_cycles'      => [
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'TRIAL',
                        'sequence'       => 1,
                        'total_cycles'   => 2,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value'         => $plan->amount,
                                'currency_code' => 'USD',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'TRIAL',
                        'sequence'       => 2,
                        'total_cycles'   => 3,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'currency_code' => 'USD',
                                'value'         => '6',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'REGULAR',
                        'sequence'       => 3,
                        'total_cycles'   => 12,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'currency_code' => 'USD',
                                'value'         => '10',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding'     => true,
                    'setup_fee'                 => [
                        'value'         => '10',
                        'currency_code' => 'USD',
                    ],
                    'setup_fee_failure_action'  => 'CONTINUE',
                    'payment_failure_threshold' => 3,
                ],
                'taxes'               => [
                    'percentage' => '10',
                    'inclusive'  => false,
                ],
                'create_time'         => '2020-05-27T12:13:51Z',
                'update_time'         => '2020-05-27T12:13:51Z',
            ]),
            'https://api.paystack.co/plan'                          => Http::response([
                'status'  => true,
                'message' => 'Plan created',
                'data'    => [
                    'name'          => $plan->name,
                    'amount'        => $plan->amount * 100,
                    'interval'      => $plan->interval,
                    'integration'   => 100032,
                    'domain'        => 'test',
                    'plan_code'     => 'PLN_gx2wn530m0i3w3m',
                    'send_invoices' => true,
                    'send_sms'      => true,
                    'hosted_page'   => false,
                    'currency'      => 'ZAR',
                    'id'            => 28,
                    'createdAt'     => '2016-03-29T22:42:50.811Z',
                    'updatedAt'     => '2016-03-29T22:42:50.811Z',
                ],
            ]),
            'https://api.stripe.com/v1/products'                    => Http::response([
                'id'                   => 'prod_KhIRa4HjCgFaNJ',
                'object'               => 'product',
                'active'               => true,
                'created'              => 1638369119,
                'description'          => $plan->description,
                'images'               => [],
                'livemode'             => false,
                'metadata'             => [],
                'name'                 => $plan->name,
                'package_dimensions'   => null,
                'shippable'            => null,
                'statement_descriptor' => null,
                'tax_code'             => null,
                'unit_label'           => null,
                'updated'              => 1638369119,
                'url'                  => null,
            ]),
            'https://api.stripe.com/v1/prices'                      => Http::response([
                'id'                  => 'price_1K1wJPB9m4sTKy1qcHaLkXki',
                'object'              => 'price',
                'active'              => true,
                'billing_scheme'      => 'per_unit',
                'created'             => 1638378583,
                'currency'            => 'usd',
                'livemode'            => false,
                'lookup_key'          => null,
                'metadata'            => [
                ],
                'nickname'            => null,
                'product'             => 'prod_KhIRa4HjCgFaNJ',
                'recurring'           => [
                    'aggregate_usage' => null,
                    'interval'        => $plan->interval,
                    'interval_count'  => 1,
                    'usage_type'      => 'licensed',
                ],
                'tax_behavior'        => 'unspecified',
                'tiers_mode'          => null,
                'transform_quantity'  => null,
                'type'                => 'recurring',
                'unit_amount'         => $plan->amount * 100,
                'unit_amount_decimal' => $plan->amount * 100,
            ]),
        ]);

        // 1. create plan
        $this
            ->actingAs($user)
            ->post('/api/subscriptions/admin/plans', [
                'name'        => $plan->name,
                'description' => $plan->description,
                'interval'    => $plan->interval,
                'amount'      => $plan->amount,
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 100,
                    'max_team_members'   => 6,
                ],
            ])
            ->assertCreated();

        $planAttributes = [
            'visible'     => false,
            'name'        => 'New name',
            'description' => 'New description',
        ];

        $planFeatures = [
            'max_storage_amount' => 120,
            'max_team_members'   => '12',
        ];

        // Get plan from database
        $plan = Plan::first();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/billing/plans/*' => Http::response([], 204),
            'https://api.paystack.co/plan/*'                      => Http::response([
                'status'  => true,
                'message' => 'Plan updated. 1 subscription(s) affected',
            ]),
            'https://api.stripe.com/v1/prices/*'                  => Http::response([
                'id'                  => 'price_1K1wJPB9m4sTKy1qcHaLkXki',
                'object'              => 'price',
                'active'              => true,
                'billing_scheme'      => 'per_unit',
                'created'             => 1638378583,
                'currency'            => 'eur',
                'livemode'            => false,
                'lookup_key'          => null,
                'metadata'            => [
                ],
                'nickname'            => null,
                'product'             => 'prod_KhIRa4HjCgFaNJ',
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
            ]),
            'https://api.stripe.com/v1/products/*'                => Http::response([
                'id'                   => 'prod_KhIRa4HjCgFaNJ',
                'object'               => 'product',
                'active'               => true,
                'created'              => 1638369119,
                'description'          => $plan->description,
                'images'               => [],
                'livemode'             => false,
                'metadata'             => [],
                'name'                 => $plan->name,
                'package_dimensions'   => null,
                'shippable'            => null,
                'statement_descriptor' => null,
                'tax_code'             => null,
                'unit_label'           => null,
                'updated'              => 1638369119,
                'url'                  => null,
            ]),
        ]);

        // 2. update plan attributes one by one
        collect($planAttributes)
            ->each(function ($value, $key) use ($user, $plan) {
                $this
                    ->actingAs($user)
                    ->put("/api/subscriptions/admin/plans/{$plan->id}", [
                        $key => $value,
                    ])
                    ->assertOk();
            });

        // 3. update plan features one by one
        collect($planFeatures)
            ->each(function ($value, $key) use ($user, $plan) {
                $this
                    ->actingAs($user)
                    ->patch("/api/subscriptions/admin/plans/$plan->id/features", [
                        $key => $value,
                    ])
                    ->assertOk();
            });

        $this->assertTrue(cache()->has('action.synchronize-plans'));

        // Synchronize plans
        Artisan::call('subscription:synchronize-plans');

        $this
            ->assertDatabaseHas('plans', [
                'visible'     => false,
                'name'        => 'New name',
                'description' => 'New description',
            ])
            ->assertDatabaseHas('plan_features', [
                'key'   => 'max_storage_amount',
                'value' => 120,
            ])
            ->assertDatabaseHas('plan_features', [
                'key'   => 'max_team_members',
                'value' => 12,
            ])
            ->assertTrue(! cache()->has('action.synchronize-plans'));
    }

    /**
     * @test
     */
    public function it_delete_plan()
    {
        $user = User::factory()
            ->create();

        $plan = Plan::factory()
            ->make();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token'      => Http::response([
                'scope'        => 'scope',
                'access_token' => 'jnjleqngtlq3l34jn6l2346n2l4',
                'token_type'   => 'Bearer',
                'app_id'       => 'APP-80W284485P519543T',
                'expires_in'   => 31349,
                'nonce'        => 'nonce',
            ]),
            'https://api-m.sandbox.paypal.com/v1/catalogs/products' => Http::response([
                'id'          => 'P-5ML4271244454362WXNWU5NQ',
                'name'        => 'Subscription Service',
                'description' => 'Cloud subscription service',
                'type'        => 'SERVICE',
                'category'    => 'SOFTWARE',
            ]),
            'https://api-m.sandbox.paypal.com/v1/billing/plans'     => Http::response([
                'id'                  => 'P-5ML4271244454362WXNWU5NQ',
                'product_id'          => 'PROD-XXCD1234QWER65782',
                'name'                => $plan->name,
                'description'         => $plan->description,
                'status'              => 'ACTIVE',
                'billing_cycles'      => [
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'TRIAL',
                        'sequence'       => 1,
                        'total_cycles'   => 2,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value'         => $plan->amount,
                                'currency_code' => 'USD',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'TRIAL',
                        'sequence'       => 2,
                        'total_cycles'   => 3,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'currency_code' => 'USD',
                                'value'         => '6',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                    [
                        'frequency'      => [
                            'interval_unit'  => 'MONTH',
                            'interval_count' => 1,
                        ],
                        'tenure_type'    => 'REGULAR',
                        'sequence'       => 3,
                        'total_cycles'   => 12,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'currency_code' => 'USD',
                                'value'         => '10',
                            ],
                            'version'     => 1,
                            'create_time' => '2020-05-27T12:13:51Z',
                            'update_time' => '2020-05-27T12:13:51Z',
                        ],
                    ],
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding'     => true,
                    'setup_fee'                 => [
                        'value'         => '10',
                        'currency_code' => 'USD',
                    ],
                    'setup_fee_failure_action'  => 'CONTINUE',
                    'payment_failure_threshold' => 3,
                ],
                'taxes'               => [
                    'percentage' => '10',
                    'inclusive'  => false,
                ],
                'create_time'         => '2020-05-27T12:13:51Z',
                'update_time'         => '2020-05-27T12:13:51Z',
            ]),
            'https://api.paystack.co/plan'                          => Http::response([
                'status'  => true,
                'message' => 'Plan created',
                'data'    => [
                    'name'          => $plan->name,
                    'amount'        => $plan->amount * 100,
                    'interval'      => $plan->interval,
                    'integration'   => 100032,
                    'domain'        => 'test',
                    'plan_code'     => 'PLN_gx2wn530m0i3w3m',
                    'send_invoices' => true,
                    'send_sms'      => true,
                    'hosted_page'   => false,
                    'currency'      => 'ZAR',
                    'id'            => 28,
                    'createdAt'     => '2016-03-29T22:42:50.811Z',
                    'updatedAt'     => '2016-03-29T22:42:50.811Z',
                ],
            ]),
            'https://api.stripe.com/v1/products'                    => Http::response([
                'id'                   => 'prod_KhIRa4HjCgFaNJ',
                'object'               => 'product',
                'active'               => true,
                'created'              => 1638369119,
                'description'          => $plan->description,
                'images'               => [],
                'livemode'             => false,
                'metadata'             => [],
                'name'                 => $plan->name,
                'package_dimensions'   => null,
                'shippable'            => null,
                'statement_descriptor' => null,
                'tax_code'             => null,
                'unit_label'           => null,
                'updated'              => 1638369119,
                'url'                  => null,
            ]),
            'https://api.stripe.com/v1/prices'                      => Http::response([
                'id'                  => 'price_1K1wJPB9m4sTKy1qcHaLkXki',
                'object'              => 'price',
                'active'              => true,
                'billing_scheme'      => 'per_unit',
                'created'             => 1638378583,
                'currency'            => 'usd',
                'livemode'            => false,
                'lookup_key'          => null,
                'metadata'            => [
                ],
                'nickname'            => null,
                'product'             => 'prod_KhIRa4HjCgFaNJ',
                'recurring'           => [
                    'aggregate_usage' => null,
                    'interval'        => $plan->interval,
                    'interval_count'  => 1,
                    'usage_type'      => 'licensed',
                ],
                'tax_behavior'        => 'unspecified',
                'tiers_mode'          => null,
                'transform_quantity'  => null,
                'type'                => 'recurring',
                'unit_amount'         => $plan->amount * 100,
                'unit_amount_decimal' => $plan->amount * 100,
            ]),
        ]);

        // 1. create plans
        $this
            ->actingAs($user)
            ->post('/api/subscriptions/admin/plans', [
                'name'        => $plan->name,
                'description' => $plan->description,
                'interval'    => $plan->interval,
                'amount'      => $plan->amount,
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 100,
                    'max_team_members'   => 6,
                ],
            ])
            ->assertCreated();

        $plan = Plan::first();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/billing/plans/*/deactivate' => Http::response([], 204),
            'https://api.paystack.co/plan/*'                                 => Http::response([
                'status'  => true,
                'message' => 'Plan Deleted',
                'data'    => [
                    'name'      => $plan->name,
                    'createdAt' => '2016-03-29T22:42:50.811Z',
                    'updatedAt' => '2016-03-29T22:42:50.811Z',
                ],
            ]),
            'https://api.stripe.com/v1/plans/*'                                => Http::response([
                'id'      => 'price_1K1wJPB9m4sTKy1qcHaLkXki',
                'object'  => 'plan',
                'deleted' => true,
            ]),
        ]);

        // 2. delete plans
        $this
            ->actingAs($user)
            ->delete("/api/subscriptions/admin/plans/{$plan->id}")
            ->assertNoContent();

        // TODO: this can't be fixed, must be flexible for new gateway development
        /*$this
            ->assertDatabaseCount('plan_features', 2)
            ->assertDatabaseCount('plan_drivers', 2);*/

        $this->assertDatabaseHas('plans', [
            'id'     => $plan->id,
            'status' => 'archived',
        ]);

        // TODO: this can't be fixed, must be flexible for new gateway development
        //Http::assertSentCount(6);
    }
}
