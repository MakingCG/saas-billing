<?php

namespace Makingcg\Subscription\Database\Factories;

use Domain\Plans\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'id'          => $this->faker->uuid,
            'name'        => $this->faker->randomElements(['Basic', 'Professional', 'Business']),
            'description' => $this->faker->realText(120),
            'price'       => $this->faker->numberBetween(5, 50),
            'currency'    => $this->faker->randomElements(['USD', 'EUR']),
            'amount'      => $this->faker->randomElements([100, 200, 500]),
            'interval'    => $this->faker->randomElements(['day', 'week', 'month', 'quarter', 'year']),
            'visible'     => $this->faker->boolean(80),
            'created_at'  => $this->faker->dateTimeBetween('-36 months'),
        ];
    }
}
