<?php
namespace Support;

use Support\Engines\Engine;
use Illuminate\Support\Manager;
use Support\Engines\StripeEngine;
use Support\Engines\PayStackEngine;
use Domain\Plans\DTO\CreatePlanData;

/**
 * @method createPlan(CreatePlanData $data)
 * @method createCustomer(array $user)
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

    public function createPayStackDriver(): Engine
    {
        return new PayStackEngine();
    }
}
