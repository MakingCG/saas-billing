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
        Plan           $plan,
    ) {
        // Get available driver/s
        $availableDrivers = config('subscription.available_drivers');

        collect($availableDrivers)
            ->each(function ($driver) use ($data, $plan) {

                // Create plan via gateway api
                $driverPlan = $this->subscription
                    ->driver($driver)
                    ->createPlan($data);

                // Attach driver plan id into internal plan record
                $plan->drivers()
                    ->create([
                        'driver_plan_id' => $driverPlan['id'],
                        'driver'         => $driver,
                    ]);
            });
    }
}
