<?php
namespace Tests;

use Str;
use Tests\Models\User;

class EngineTest extends TestCase
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
    public function it_create_stripe_plan()
    {
        config()->set('vuefilemanager-subscription.driver', 'stripe');

        $this
            ->actingAs($this->user)
            ->post('/api/subscription/plans', $this->plan)
            ->assertCreated()
            ->assertJsonFragment([
                'id' => $this->plan['name'],
            ]);
    }

    /**
     * @test
     */
    public function it_create_flutter_wave_plan()
    {
        config()->set('vuefilemanager-subscription.driver', 'flutter-wave');

        $this
            ->actingAs($this->user)
            ->post('/api/subscription/plans', $this->plan)
            ->assertCreated()
            ->assertJsonFragment([
                'name' => $this->plan['name'],
            ]);
    }
}
