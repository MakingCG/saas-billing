<?php
namespace Tests\Domain\Plans;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Mocking\Stripe\CreateMeteredPlanStripeMocksClass;

class MeteredPlanTest extends TestCase
{
    /**
     * @test
     */
    public function it_create_metered_plan()
    {
        $user = User::factory()
            ->create();

        resolve(CreateMeteredPlanStripeMocksClass::class)();

        $this
            ->actingAs($user)
            ->post('/api/subscriptions/admin/plans', [
                'type'        => 'metered',
                'name'        => 'Basic Plan',
                'description' => 'Pay as you go is the best fit',
                'currency'    => 'USD',
                'meters'      => [
                    [
                        'key'                => 'bandwidth',
                        'aggregate_strategy' => 'sum_of_usage',
                        'tiers'              => [
                            [
                                'first_unit' => 1,
                                'last_unit'  => null,
                                'per_unit'   => 0.019,
                                'flat_fee'   => 2.49,
                            ],
                        ],
                    ],
                    [
                        'key'                => 'storage',
                        'aggregate_strategy' => 'maximum_usage',
                        'tiers'              => [
                            [
                                'first_unit' => 1,
                                'last_unit'  => null,
                                'per_unit'   => 0.09,
                                'flat_fee'   => null,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'name' => 'Basic Plan',
                'type' => 'metered',
            ]);

        // Check only drivers which have native support for metered billing
        collect(config('subscription.metered_billing.native_support'))
            ->each(function ($driver) {
                if (in_array($driver, config('subscription.available_drivers'))) {
                    $this
                        ->assertDatabaseHas('plan_drivers', [
                            'driver' => $driver,
                        ]);
                }
            });

        $this
            ->assertDatabaseHas('plans', [
                'type'        => 'metered',
                'name'        => 'Basic Plan',
                'description' => 'Pay as you go is the best fit',
                'currency'    => 'USD',
                'status'      => 'active',
            ])
            ->assertDatabaseHas('plan_metered_features', [
                'key'                => 'bandwidth',
                'aggregate_strategy' => 'sum_of_usage',
            ])
            ->assertDatabaseHas('plan_metered_features', [
                'key'                => 'storage',
                'aggregate_strategy' => 'maximum_usage',
            ])
            ->assertDatabaseHas('metered_tiers', [
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.019,
                'flat_fee'   => 2.49,
            ])
            ->assertDatabaseHas('metered_tiers', [
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.09,
                'flat_fee'   => null,
            ]);
    }
}
