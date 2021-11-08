<?php

namespace Tests\Domain\Plans;

use Tests\TestCase;
use Tests\Models\User;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

class PlansTest extends TestCase
{
    /**
     * @test
     */
    public function it_create_plan()
    {
        $user = User::factory()->create();

        $plan = Plan::factory()->make();

        $this
            ->actingAs($user)
            ->post('/api/subscription/plans', [
                'name'        => $plan->name,
                'description' => $plan->description,
                'interval'    => $plan->interval,
                'price'       => $plan->price,
                'amount'      => $plan->amount,
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
            fn($driver) => $this
                ->assertDatabaseHas('plan_drivers', [
                    'driver' => $driver,
                ])
        );

        $this
            ->assertDatabaseHas('plans', [
                'name'        => $plan->name,
                'description' => $plan->description,
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
}
