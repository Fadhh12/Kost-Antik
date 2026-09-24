<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Nama role Spatie. Dipakai agar tidak ada string role yang tersebar.
 */
enum Role: string
{
    use HasOptions;

    case Owner = 'owner';
    case Manager = 'manager';
    case Tenant = 'tenant';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pemilik',
            self::Manager => 'Pengelola',
            self::Tenant => 'Penyewa',
        };
    }
}
