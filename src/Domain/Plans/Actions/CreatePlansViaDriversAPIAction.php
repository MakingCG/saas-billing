<?php
namespace Domain\Plans\Actions;

use Support\EngineManager;
use Domain\Plans\Models\Plan;
use Domain\Plans\Models\PlanDriver;
use Domain\Plans\DTO\CreatePlanData;
use Spatie\QueueableAction\QueueableAction;

class CreatePlansViaDriversAPIAction
{
    use QueueableAction;

    public function __construct(
        public EngineManager $subscription,
    ) {
    }

    public function __invoke(
        CreatePlanData $data,
        Plan $plan,
    ) {
        // Get available drivers
        $availableDrivers = ['stripe', 'flutter-wave'];

        collect($availableDrivers)
            ->each(function ($driver) use ($data, $plan) {
                // Create plan
                $driverPlan = $this->subscription
                    ->driver($driver)
                    ->createPlan($data);

                // Attach driver plan id into internal plan record
                PlanDriver::create([
                    'driver_plan_id' => $driverPlan['id'],
                    'plan_id'        => $plan->id,
                    'driver'         => $driver,
                ]);
            });
    }
}
