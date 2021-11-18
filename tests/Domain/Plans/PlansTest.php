<?php
namespace Tests\Domain\Plans;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Artisan;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

class PlansTest extends TestCase
{
    /**
     * @test
     */
    public function it_get_plans()
    {
        $user = User::factory()
            ->create();

        $plan = Plan::factory()
            ->hasFeatures(1)
            ->hasDrivers(2)
            ->create();

        $this
            ->actingAs($user)
            ->getJson('/api/subscription/plans')
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

        $this
            ->actingAs($user)
            ->post('/api/subscription/plans', [
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

        $availableDrivers = collect(config('subscription.available_drivers'));

        $availableDrivers->each(
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
            ])
            ->assertDatabaseHas('plan_features', [
                'key'   => 'max_storage_amount',
                'value' => 100,
            ])
            ->assertDatabaseHas('plan_features', [
                'key'   => 'max_team_members',
                'value' => 6,
            ]);
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

        // 1. create plan
        $this
            ->actingAs($user)
            ->post('/api/subscription/plans', [
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

        // 2. update plan attributes one by one
        collect($planAttributes)
            ->each(function ($value, $key) use ($user, $plan) {
                $this
                    ->actingAs($user)
                    ->put("/api/subscription/plans/{$plan->id}", [
                        $key => $value,
                    ])
                    ->assertOk();
            });

        // 3. update plan features one by one
        collect($planFeatures)
            ->each(function ($value, $key) use ($user, $plan) {
                $this
                    ->actingAs($user)
                    ->put("/api/subscription/plans/$plan->id/features", [
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

        $this
            ->actingAs($user)
            ->post('/api/subscription/plans', [
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

        $this
            ->actingAs($user)
            ->delete("/api/subscription/plans/{$plan->id}")
            ->assertNoContent();

        $this
            ->assertDatabaseCount('plans', 0)
            ->assertDatabaseCount('plan_features', 0)
            ->assertDatabaseCount('plan_drivers', 0);
    }
}
