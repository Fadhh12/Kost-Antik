<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Models\Concerns\ScopedToManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingRequest extends Model
{
    use HasFactory, ScopedToManager;

    protected $fillable = [
        'user_id',
        'room_id',
        'start_date',
        'duration_months',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'duration_months' => 'integer',
            'status' => BookingStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function propertyRelationPath(): ?string
    {
        return 'room';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function lease(): HasOne
    {
        return $this->hasOne(Lease::class);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', BookingStatus::Pending);
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    /**
     * Perkiraan total (harga kamar saat ini × durasi).
     */
    protected function estimatedTotal(): Attribute
    {
        return Attribute::get(fn () => (int) ($this->room?->monthly_price ?? 0) * $this->duration_months);
    }

    protected function endDate(): Attribute
    {
        return Attribute::get(fn () => $this->start_date?->copy()->addMonthsNoOverflow($this->duration_months)->subDay());
    }
}
