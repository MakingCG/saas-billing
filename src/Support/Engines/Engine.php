<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;

interface Engine
{
    /**
     * Create new plan for subscription
     */
    public function createPlan(CreatePlanData $data): array;

    /**
     * Get plan
     */
    public function getPlan(string $planId): Response;

    /**
     * Create new customer for service
     */
    public function createCustomer(array $user): Customer;

    /**
     * Create new subscription
     */
    public function webhook(Request $request): void;
}
