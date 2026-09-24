<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        $floor = fake()->numberBetween(1, 3);

        return [
            'property_id' => Property::factory(),
            'code' => $floor.fake()->unique()->bothify('?#'),
            'floor' => $floor,
            'size_m2' => fake()->randomElement([9, 10.5, 12, 14, 16]),
            'monthly_price' => fake()->randomElement([850000, 950000, 1100000, 1250000, 1500000]),
            'capacity' => 1,
            'status' => RoomStatus::Available,
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => RoomStatus::Maintenance]);
    }

    public function occupied(): static
    {
        return $this->state(fn () => ['status' => RoomStatus::Occupied]);
    }
}
