<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Policies\Concerns\ChecksManagement;

class RoomPolicy
{
    use ChecksManagement;

    public function create(User $user, Property $property): bool
    {
        return $this->manages($user, $property->id);
    }

    public function update(User $user, Room $room): bool
    {
        return $this->manages($user, $room->property_id);
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->manages($user, $room->property_id);
    }
}
