<?php

namespace Tests;

use Makingcg\Subscription\EngineManager;
use Str;

class EngineTest extends TestCase
{
    public EngineManager $subscription;
    public string $planName;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscription = resolve(EngineManager::class);

        $this->planName = Str::lower('test-plan-' . Str::random());
    }

    /**
     * @test
     */
    public function it_create_stripe_plan()
    {
        // Create subscription
        $plan = $this->subscription
            ->driver('stripe')
            ->createPlan([
                'name'               => $this->planName,
                'description'        => 'When your business start grow up.',
                'price'              => '$44.99',
                'capacity'           => 1000,
                'capacity_formatted' => '1TB',
                'currency'           => 'USD',
                'tax_rates'          => [],
            ]);

        $this->assertEquals($this->planName, $plan['id']);
    }

    /**
     * @test
     */
    public function it_create_flutter_wave_plan()
    {
        // Create subscription
        $plan = $this->subscription
            ->driver('flutter-wave')
            ->createPlan([
                'name'     => $this->planName,
                'amount'   => '2000',
                'interval' => 'monthly',
                'duration' => null,
            ]);

        $this->assertEquals($this->planName, $plan['data']['name']);
    }
}
