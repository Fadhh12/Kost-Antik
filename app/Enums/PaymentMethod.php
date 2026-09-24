<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PaymentMethod: string
{
    use HasOptions;

    case Transfer = 'transfer';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Transfer => 'Transfer bank',
            self::Cash => 'Tunai',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Transfer => 'landmark',
            self::Cash => 'banknote',
        };
    }
}
