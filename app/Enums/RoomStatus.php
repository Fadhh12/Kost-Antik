<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum RoomStatus: string
{
    use HasOptions;

    case Available = 'available';
    case Occupied = 'occupied';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Occupied => 'Terisi',
            self::Maintenance => 'Perbaikan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Occupied, self::Maintenance => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Available => 'door-open',
            self::Occupied => 'door-closed',
            self::Maintenance => 'wrench',
        };
    }

    /**
     * FR-PROP-03: status yang boleh dipilih manual (occupied dikelola sistem).
     *
     * @return array<string, string>
     */
    public static function manualOptions(): array
    {
        return [
            self::Available->value => self::Available->label(),
            self::Maintenance->value => self::Maintenance->label(),
        ];
    }
}
