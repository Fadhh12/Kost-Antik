<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum InvoiceStatus: string
{
    use HasOptions;

    case Unpaid = 'unpaid';
    case PendingVerification = 'pending_verification';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum dibayar',
            self::PendingVerification => 'Menunggu verifikasi',
            self::Paid => 'Lunas',
            self::Overdue => 'Terlambat',
            self::Void => 'Dibatalkan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Unpaid => 'info',
            self::PendingVerification => 'warning',
            self::Paid => 'success',
            self::Overdue => 'danger',
            self::Void => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Unpaid => 'receipt-text',
            self::PendingVerification => 'hourglass',
            self::Paid => 'badge-check',
            self::Overdue => 'clock-alert',
            self::Void => 'ban',
        };
    }

    /**
     * FR-PAY-01: status yang boleh dibayar penyewa.
     */
    public function isPayable(): bool
    {
        return in_array($this, [self::Unpaid, self::Overdue], true);
    }

    /**
     * @return list<self>
     */
    public static function outstanding(): array
    {
        return [self::Unpaid, self::PendingVerification, self::Overdue];
    }
}
