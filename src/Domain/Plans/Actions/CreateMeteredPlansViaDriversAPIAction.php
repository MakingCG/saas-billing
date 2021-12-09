<?php
namespace VueFileManager\Subscription\Domain\Plans\Actions;

use Spatie\QueueableAction\QueueableAction;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateMeteredPlanData;

class CreateMeteredPlansViaDriversAPIAction
{
    use QueueableAction;

    public function __construct(
        public EngineManager $engine,
    ) {
    }

    public function __invoke(
        CreateMeteredPlanData $meteredPlanData,
        Plan $plan,
    ) {
        collect(config('subscription.metered_billing.native_support'))
            ->each(function ($driver) use ($meteredPlanData, $plan) {
                if (in_array($driver, config('subscription.available_drivers'))) {
                    $driverPlan = $this
                        ->engine
                        ->driver($driver)
                        ->createMeteredPlan($meteredPlanData);

                    // Attach driver plan id into internal plan record
                    $plan
                        ->drivers()
                        ->create([
                            'driver_plan_id' => $driverPlan['id'],
                            'driver'         => $driver,
                        ]);
                }
            });
    }
}
