<?php
namespace VueFileManager\Subscription\Domain\Plans\Actions;

use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;

class StorePlanAndCreateDriverVersionAction
{
    public function __construct(
        private CreatePlansViaDriversAPIAction $createPlansViaDriversAPI,
    ) {
    }

    public function __invoke(CreatePlanData $data)
    {
        // Create plan
        $plan = Plan::create([
            'name'        => $data->name,
            'description' => $data->description,
            'interval'    => $data->interval,
            'amount'      => $data->amount,
        ]);

        // Create features
        foreach ($data->features as $feature => $value) {
            $plan
                ->features()
                ->create([
                    'key'   => $feature,
                    'value' => $value,
                ]);
        }

        // Create plan in available gateways
        $this->createPlansViaDriversAPI
            ->onQueue()
            ->execute($data, $plan);

        return $plan;
    }
}
