<?php
namespace VueFileManager\Subscription\Domain\Plans\Actions;

use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateFixedPlanData;

class StoreFixedPlanAction
{
    public function __construct(
        private CreateFixedPlansViaDriversAPIAction $createPlansViaDriversAPI,
    ) {
    }

    public function __invoke(CreateFixedPlanData $data)
    {
        // Create plan
        $plan = Plan::create([
            'type'        => 'fixed',
            'name'        => $data->name,
            'description' => $data->description,
            'interval'    => $data->interval,
            'amount'      => $data->amount,
            'currency'    => $data->currency,
        ]);

        // Create features
        foreach ($data->features as $feature => $value) {
            $plan->fixedFeatures()->create([
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
