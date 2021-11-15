<?php
namespace VueFileManager\Subscription\Support\Engines;

use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;

interface Engine
{
    /**
     * Create new subscription plan
     */
    public function createPlan(CreatePlanData $data): array;

    /**
     * Update subscription plan
     */
    public function updatePlan(Plan $plan): Response;

    /**
     * Get subscription plan
     */
    public function getPlan(string $planId): Response;

    /**
     * Delete subscription plan
     */
    public function deletePlan(string $planId): Response;

    /**
     * Create new customer for service
     */
    public function createCustomer(array $user): Response;

    /**
     * Update customer for service
     */
    public function updateCustomer(array $user): Response;

    /**
     * Create new subscription
     */
    public function webhook(Request $request): void;
}
