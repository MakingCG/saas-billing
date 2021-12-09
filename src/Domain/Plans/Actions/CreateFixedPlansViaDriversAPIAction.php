<?php
namespace VueFileManager\Subscription\Domain\Plans\Actions;

use Spatie\QueueableAction\QueueableAction;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateFixedPlanData;

class CreateFixedPlansViaDriversAPIAction
{
    use QueueableAction;

    public function __construct(
        public EngineManager $subscription,
    ) {
    }

    public function __invoke(
        CreateFixedPlanData $data,
        Plan $plan,
    ) {
        collect(config('subscription.available_drivers'))
            ->each(function ($driver) use ($data, $plan) {
                // Create plan via gateway api
                $driverPlan = $this->subscription
                    ->driver($driver)
                    ->createFixedPlan($data);

                // Attach driver plan id into internal plan record
                $plan
                    ->drivers()
                    ->create([
                        'driver_plan_id' => $driverPlan['id'],
                        'driver'         => $driver,
                    ]);
            });
    }
}
