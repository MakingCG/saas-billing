<?php
namespace Tests\Domain\Plans;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Models\User;

class PlansTest extends TestCase
{
    public Model $user;

    public array $plan;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->plan = [
            'name'        => Str::lower('test-plan-' . Str::random()),
            'description' => 'When your business start grow up.',
            'interval'    => 'month',
            'price'       => 1000,
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

        $availableDrivers = collect(config('subscription.available_drivers'));

        $availableDrivers->each(fn ($driver) =>
            $this
                ->assertDatabaseHas('plan_drivers', [
                    'driver' => $driver,
                ])
        );

        $this
            ->assertDatabaseHas('plans', [
                'name'        => $this->plan['name'],
                'description' => $this->plan['description'],
            ])
            ->assertDatabaseCount('plans', 1);
    }
}
