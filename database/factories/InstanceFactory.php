<?php

namespace Database\Factories;

use App\Enums\InstanceType;
use App\Models\Instance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instance>
 */
class InstanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'type' => fake()->randomElement(InstanceType::cases()),
            'address' => fake('id_ID')->city(),
        ];
    }
}
