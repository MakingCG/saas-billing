<?php

namespace VueFileManager\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use VueFileManager\Subscription\Domain\DunningEmails\Models\Dunning;

class DunningFactory extends Factory
{
    protected $model = Dunning::class;

    public function definition(): array
    {
        return [
            'id'         => $this->faker->uuid,
            'user_id'    => $this->faker->uuid,
            'sequence'  => $this->faker->randomNumber(1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
