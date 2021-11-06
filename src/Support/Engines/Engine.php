<?php
namespace Support\Engines;

use Illuminate\Http\Request;
use Domain\Plans\DTO\CreatePlanData;
use Domain\Customers\Models\Customer;

interface Engine
{
    /**
     * Create new plan for subscription
     */
    public function createPlan(CreatePlanData $data): array;

    /**
     * Create new customer for service
     */
    public function createCustomer(array $user): Customer;

    /**
     * Create new subscription
     */
    public function webhook(Request $request): void;
}
