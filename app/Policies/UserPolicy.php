<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * FR-USER-04: pengelola hanya melihat penyewa yang punya booking/kontrak di gedungnya.
     */
    public function view(User $user, User $target): bool
    {
        if (! $user->isManager() || ! $target->isTenant()) {
            return false;
        }

        $ids = $user->managedPropertyIds();

        return $target->bookings()->whereHas('room', fn ($q) => $q->whereIn('property_id', $ids))->exists()
            || $target->leases()->whereHas('room', fn ($q) => $q->whereIn('property_id', $ids))->exists();
    }

    /** Setujui/tolak/nonaktifkan/buat pengelola: hanya owner. */
    public function manage(User $user): bool
    {
        return false;
    }
}
