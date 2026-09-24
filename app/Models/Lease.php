<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Models\Concerns\ScopedToManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lease extends Model
{
    use HasFactory, ScopedToManager;

    /**
     * Semua kolom finansial diisi LeaseService (BR-04, BR-07).
     */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'terminated_at' => 'date',
            'status' => LeaseStatus::class,
            'duration_months' => 'integer',
            'monthly_price' => 'integer',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class, 'booking_request_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('sequence');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', LeaseStatus::Active);
    }

    /**
     * FR-LEASE-08: kontrak yang masih punya tagihan belum lunas.
     */
    public function scopeWithArrears(Builder $query): void
    {
        $query->whereHas('invoices', fn (Builder $q) => $q->where('status', InvoiceStatus::Overdue));
    }

    public function isActive(): bool
    {
        return $this->status === LeaseStatus::Active;
    }

    public function hasArrears(): bool
    {
        if (array_key_exists('overdue_invoices_count', $this->attributes)) {
            return $this->overdue_invoices_count > 0;
        }

        return $this->invoices()->where('status', InvoiceStatus::Overdue)->exists();
    }

    protected function remainingDays(): Attribute
    {
        return Attribute::get(fn () => $this->isActive()
            ? max(0, (int) today()->diffInDays($this->end_date, false))
            : 0);
    }

    protected function paymentProgress(): Attribute
    {
        return Attribute::get(fn () => $this->total_amount > 0
            ? min(100, (int) round($this->paid_amount / $this->total_amount * 100))
            : 0);
    }
}
