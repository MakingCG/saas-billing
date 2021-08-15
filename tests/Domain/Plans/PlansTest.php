<?php
namespace Tests\Domain\Plans;

use Str;
use Tests\Models\User;
use Tests\TestCase;

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
            'storage'     => 1000,
        ];
    }

    /**
     * @test
     */
    public function it_create_plan_with_multiple_drivers()
    {
        collect(['stripe', 'flutter-wave'])
            ->each(function ($driver) {

                config()->set('vuefilemanager-subscription.driver', $driver);

                $this
                    ->actingAs($this->user)
                    ->post('/api/subscription/plans', $this->plan)
                    ->assertCreated()
                    ->assertJsonFragment([
                        'name' => $this->plan['name'],
                    ]);
            });

    }
}
