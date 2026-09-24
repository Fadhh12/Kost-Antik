<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequest>
 */
class BookingRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->tenant(),
            'room_id' => Room::factory(),
            'start_date' => today()->addDays(7),
            'duration_months' => fake()->randomElement([1, 3, 6, 12]),
            'note' => null,
            'status' => BookingStatus::Pending,
        ];
    }
}
