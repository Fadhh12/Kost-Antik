<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PropertyStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
        };
    }

    public function tone(): string
    {
        return $this === self::Active ? 'success' : 'neutral';
    }

    public function icon(): string
    {
        return $this === self::Active ? 'circle-check' : 'eye-off';
    }
}
