<?php

namespace App\Policies;

use App\Models\BookingRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksManagement;

class BookingRequestPolicy
{
    use ChecksManagement;

    public function view(User $user, BookingRequest $booking): bool
    {
        return $booking->user_id === $user->id || $this->manages($user, $booking->room->property_id);
    }

    /** Syarat lengkap (BR-01/02, akun accepted) dicek di BookingService. */
    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    public function cancel(User $user, BookingRequest $booking): bool
    {
        return $booking->user_id === $user->id && $booking->isPending();
    }

    public function process(User $user, BookingRequest $booking): bool
    {
        return $this->manages($user, $booking->room->property_id);
    }
}
