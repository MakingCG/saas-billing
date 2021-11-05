<?php
namespace Support\Engines;

use Domain\Customers\Models\Customer;
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

    /**
     * Create new customer for service
     */
    public function createCustomer(array $user): Customer;
}
