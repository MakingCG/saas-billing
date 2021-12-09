<?php

namespace VueFileManager\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VueFileManager\Subscription\Domain\Credits\Models\Debt;

class DebtFactory extends Factory
{
    protected $model = Debt::class;

    public function definition(): array
    {
        return [
            'id'             => $this->faker->uuid,
            'user_id'        => $this->faker->uuid,
            'transaction_id' => $this->faker->uuid,
            'amount'         => $this->faker->randomElement([12.23, 26.20, 31.39]),
            'currency'       => $this->faker->randomElement(['USD', 'EUR']),
            'created_at'     => $this->faker->dateTimeBetween('-36 months'),
            'updated_at'     => $this->faker->dateTimeBetween('-36 months'),
        ];
    }
}
