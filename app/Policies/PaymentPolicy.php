<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksManagement;

class PaymentPolicy
{
    use ChecksManagement;

    /** FR-PAY-06: bukti bayar hanya untuk pemilik tagihan & pengelola gedungnya. */
    public function viewProof(User $user, Payment $payment): bool
    {
        $lease = $payment->invoice->lease;

        return $lease->user_id === $user->id || $this->manages($user, $lease->room->property_id);
    }

    public function verify(User $user, Payment $payment): bool
    {
        return $this->manages($user, $payment->invoice->lease->room->property_id);
    }

    /** FR-PAY-05: hanya owner (Gate::before). */
    public function revoke(User $user, Payment $payment): bool
    {
        return false;
    }
}
