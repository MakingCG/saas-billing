<?php
namespace VueFileManager\Subscription\Domain\Plans\Actions;

use Spatie\QueueableAction\QueueableAction;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

class DeletePlansFromPaymentServiceAction
{
    use QueueableAction;

    public function __construct(
        public EngineManager $subscription
    ) {
    }

    public function __invoke(Plan $plan)
    {
        // Delete plan from all available payment gateways
        collect(config('subscription.available_drivers'))
            ->each(function ($driver) use ($plan) {
                $planDriver = $plan
                    ->drivers()
                    ->where('driver', $driver)
                    ->first();

                $this->subscription
                    ->driver($driver)
                    ->deletePlan($planDriver->driver_plan_id);
            });
    }
}
