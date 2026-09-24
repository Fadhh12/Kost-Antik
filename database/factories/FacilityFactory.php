<?php

namespace Database\Factories;

use App\Enums\FacilityType;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'icon' => 'check',
            'type' => fake()->randomElement(FacilityType::cases()),
        ];
    }
}
