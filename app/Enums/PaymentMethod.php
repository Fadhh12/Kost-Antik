<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PaymentMethod: string
{
    use HasOptions;

    case Transfer = 'transfer';
    case Cash = 'cash';
    case Midtrans = 'midtrans';

    public function label(): string
    {
        return match ($this) {
            self::Transfer => 'Transfer bank',
            self::Cash => 'Tunai',
            self::Midtrans => 'Midtrans (online)',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Transfer => 'landmark',
            self::Cash => 'banknote',
            self::Midtrans => 'credit-card',
        };
    }
}
