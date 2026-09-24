<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum LeaseStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Completed = 'completed';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Terminated => 'Diakhiri',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Completed => 'neutral',
            self::Terminated => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Active => 'key-round',
            self::Completed => 'flag',
            self::Terminated => 'octagon-x',
        };
    }
}
