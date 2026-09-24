<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum UserStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu verifikasi',
            self::Accepted => 'Terverifikasi',
            self::Rejected => 'Ditolak',
            self::Inactive => 'Nonaktif',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'success',
            self::Rejected => 'danger',
            self::Inactive => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'hourglass',
            self::Accepted => 'badge-check',
            self::Rejected => 'circle-x',
            self::Inactive => 'ban',
        };
    }

    /**
     * FR-AUTH-03: akun ditolak/nonaktif tidak boleh login.
     */
    public function canLogin(): bool
    {
        return in_array($this, [self::Pending, self::Accepted], true);
    }
}
