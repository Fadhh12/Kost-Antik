<?php

namespace Database\Factories;

use App\Enums\GenderTarget;
use App\Enums\SubmissionStatus;
use App\Models\PropertySubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertySubmission>
 */
class PropertySubmissionFactory extends Factory
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
            'contact_name' => fake('id_ID')->name(),
            'contact_phone' => '08'.fake()->numerify('##########'),
            'photos' => [],
            'status' => SubmissionStatus::Pending,
        ];
    }
}
