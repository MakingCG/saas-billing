<?php

namespace Tests\Domain;

use Makingcg\Subscription\EngineManager;
use Tests\TestCase;

class EngineTest extends TestCase
{
    /**
     * @test
     */
    public function it_test_engine()
    {
        $subscription = resolve(EngineManager::class);

        dd($subscription->hello());
    }
}
