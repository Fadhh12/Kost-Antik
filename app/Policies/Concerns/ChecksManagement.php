<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * BR-08: pengelola hanya berwenang atas gedung yang ditugaskan kepadanya.
 * Owner sudah diizinkan lewat Gate::before.
 */
trait ChecksManagement
{
    protected function manages(User $user, ?int $propertyId): bool
    {
        return $propertyId !== null
            && $user->isManager()
            && in_array($propertyId, $user->managedPropertyIds(), true);
    }
}
