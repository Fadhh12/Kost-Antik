<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Policies\Concerns\ChecksManagement;

class PropertyPolicy
{
    use ChecksManagement;

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Property $property): bool
    {
        return $this->manages($user, $property->id);
    }

    /** Hanya owner (lewat Gate::before). */
    public function create(User $user): bool
    {
        return false;
    }

    /** Pengelola boleh mengubah info dasar gedungnya. */
    public function update(User $user, Property $property): bool
    {
        return $this->manages($user, $property->id);
    }

    /** Foto, fasilitas, pengelola, status: hanya owner. */
    public function manage(User $user, Property $property): bool
    {
        return false;
    }

    public function delete(User $user, Property $property): bool
    {
        return false;
    }
}
