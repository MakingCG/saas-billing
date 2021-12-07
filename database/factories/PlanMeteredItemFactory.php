<?php

namespace VueFileManager\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VueFileManager\Subscription\Domain\Plans\Models\PlanMeteredItem;

class PlanMeteredItemFactory extends Factory
{
    protected $model = PlanMeteredItem::class;

    public function definition(): array
    {
        return [
            'id'        => $this->faker->uuid,
            'plan_id'   => $this->faker->uuid,
            'charge_by' => $this->faker->randomElement(['sum_of_usage', 'maximum_usage']),
            'key'     => $this->faker->randomElement(['bandwidth', 'storage', 'members']),
        ];
    }
}
