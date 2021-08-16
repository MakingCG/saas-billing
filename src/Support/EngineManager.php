<?php
namespace Support;

use Support\Engines\Engine;
use Illuminate\Support\Manager;
use Support\Engines\StripeEngine;
use Domain\Plans\DTO\CreatePlanData;
use Support\Engines\FlutterWaveEngine;

/**
 * @method createPlan(CreatePlanData $data)
 */
class EngineManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('subscription.driver', 'stripe');
    }

    public function createStripeDriver(): Engine
    {
        return new StripeEngine();
    }

    public function createFlutterWaveDriver(): Engine
    {
        return new FlutterWaveEngine();
    }
}
