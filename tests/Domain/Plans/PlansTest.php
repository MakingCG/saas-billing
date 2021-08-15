<?php
namespace Tests\Domain\Plans;

use Domain\Plans\Models\PlanDriver;
use Str;
use Tests\TestCase;
use Tests\Models\User;

class PlansTest extends TestCase
{
    public User $user;

    public array $plan;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->plan = [
            'name'        => Str::lower('test-plan-' . Str::random()),
            'description' => 'When your business start grow up.',
            'interval'    => 'month',
            'price'       => 10,
            'amount'      => 1000,
        ];
    }

    /**
     * @test
     */
    public function it_create_plan()
    {
        $this
            ->actingAs($this->user)
            ->post('/api/subscription/plans', $this->plan)
            ->assertCreated()
            ->assertJsonFragment([
                'name' => $this->plan['name'],
            ]);

        $this
            ->assertDatabaseHas('plan_drivers', [
                'driver' => 'stripe',
            ])
            ->assertDatabaseHas('plan_drivers', [
                'driver' => 'flutter-wave',
            ])
            ->assertDatabaseHas('plans', [
                'name'        => $this->plan['name'],
                'description' => $this->plan['description'],
            ])
            ->assertDatabaseCount('plans', 1);

        dd(PlanDriver::all()->toArray());
    }
}
