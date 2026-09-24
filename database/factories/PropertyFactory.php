<?php

namespace Database\Factories;

use App\Enums\GenderTarget;
use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Kost '.ucfirst(fake()->unique()->word()).' '.fake()->numberBetween(1, 99),
            'address' => fake('id_ID')->streetAddress(),
            'city' => fake()->randomElement(['Bekasi', 'Cikarang']),
            'latitude' => fake()->latitude(-6.4, -6.2),
            'longitude' => fake()->longitude(106.9, 107.2),
            'gender_target' => GenderTarget::Mixed,
            'description' => fake('id_ID')->paragraph(),
            'rules' => "Tamu lawan jenis dilarang masuk kamar.\nJam malam pukul 22.00.",
            'status' => PropertyStatus::Active,
        ];
    }

    public function forGender(GenderTarget $target): static
    {
        return $this->state(fn () => ['gender_target' => $target]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => PropertyStatus::Inactive]);
    }
}
