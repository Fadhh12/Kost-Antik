<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum InstanceType: string
{
    use HasOptions;

    case Campus = 'campus';
    case Office = 'office';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Campus => 'Kampus',
            self::Office => 'Kantor',
            self::Other => 'Lainnya',
        };
    }
}
