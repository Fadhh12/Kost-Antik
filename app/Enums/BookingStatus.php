<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum BookingStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
            self::Expired => 'Kedaluwarsa',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Cancelled, self::Expired => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'hourglass',
            self::Approved => 'circle-check',
            self::Rejected => 'circle-x',
            self::Cancelled => 'ban',
            self::Expired => 'timer-off',
        };
    }
}
