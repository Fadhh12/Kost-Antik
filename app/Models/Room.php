<?php

namespace App\Models;

use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Models\Concerns\ScopedToManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory, ScopedToManager;

    /**
     * status tidak termasuk "occupied" dari input; lihat RoomService.
     */
    protected $fillable = [
        'property_id',
        'code',
        'floor',
        'size_m2',
        'monthly_price',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'monthly_price' => 'integer',
            'capacity' => 'integer',
            'floor' => 'integer',
            'size_m2' => 'float',
        ];
    }

    protected static function propertyRelationPath(): ?string
    {
        return null;
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->where('status', LeaseStatus::Active)->latestOfMany();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where('status', RoomStatus::Available);
    }

    public function isAvailable(): bool
    {
        return $this->status === RoomStatus::Available;
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn () => 'Kamar '.$this->code);
    }
}
