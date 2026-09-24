<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\ChecksManagement;

class InvoicePolicy
{
    use ChecksManagement;

    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->lease->user_id === $user->id || $this->manages($user, $invoice->lease->room->property_id);
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        return $invoice->lease->user_id === $user->id;
    }

    public function recordCash(User $user, Invoice $invoice): bool
    {
        return $this->manages($user, $invoice->lease->room->property_id);
    }
}
