<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum GenderTarget: string
{
    use HasOptions;

    case Male = 'male';
    case Female = 'female';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Putra',
            self::Female => 'Putri',
            self::Mixed => 'Campur',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Male => 'mars',
            self::Female => 'venus',
            self::Mixed => 'users',
        };
    }

    public function tone(): string
    {
        return 'info';
    }

    /**
     * BR-02: gender penyewa harus cocok, kecuali gedung campur.
     */
    public function accepts(Gender $gender): bool
    {
        return $this === self::Mixed || $this->value === $gender->value;
    }
}
