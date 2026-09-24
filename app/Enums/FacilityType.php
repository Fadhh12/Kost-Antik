<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum FacilityType: string
{
    use HasOptions;

    case Room = 'room';
    case Shared = 'shared';

    public function label(): string
    {
        return match ($this) {
            self::Room => 'Fasilitas kamar',
            self::Shared => 'Fasilitas bersama',
        };
    }
}
