<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;
use App\Policies\Concerns\ChecksManagement;

class LeasePolicy
{
    use ChecksManagement;

    public function view(User $user, Lease $lease): bool
    {
        return $lease->user_id === $user->id || $this->manages($user, $lease->room->property_id);
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function terminate(User $user, Lease $lease): bool
    {
        return $this->manages($user, $lease->room->property_id);
    }

    /** FR-REV-01: ulasan hanya dari penyewa kontrak itu. */
    public function review(User $user, Lease $lease): bool
    {
        return $lease->user_id === $user->id;
    }
}
