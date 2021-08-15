<?php

namespace Makingcg\Subscription\Engines;

use Domain\Plans\DTO\CreatePlanData;

interface Engine
{
    /**
     * For testing purpose
     */
    public function hello(): string;

    /**
     * Create new plan for subscription
     */
    public function createPlan(CreatePlanData $data): array;
}
