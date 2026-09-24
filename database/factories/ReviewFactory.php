<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Ulasan dibuat dari kontrak nyata di seeder/test (lease_id, user_id, property_id diisi pemanggil).
 *
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake('id_ID')->sentence(12),
            'is_published' => true,
        ];
    }
}
