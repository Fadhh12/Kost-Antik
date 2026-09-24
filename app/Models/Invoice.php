<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Concerns\ScopedToManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory, ScopedToManager;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => InvoiceStatus::class,
            'amount' => 'integer',
            'sequence' => 'integer',
        ];
    }

    protected static function propertyRelationPath(): ?string
    {
        return 'lease.room';
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function pendingPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', PaymentStatus::Pending)->latestOfMany();
    }

    public function scopeStatus(Builder $query, InvoiceStatus ...$statuses): void
    {
        $query->whereIn('status', $statuses);
    }

    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->whereHas('lease', fn (Builder $q) => $q->where('user_id', $user->id));
    }

    public function isPayable(): bool
    {
        return $this->status->isPayable();
    }

    /**
     * FR-PAY-03: status setelah pembayaran ditolak, tergantung tanggal.
     */
    public function statusWhenUnpaid(): InvoiceStatus
    {
        return today()->gt($this->due_date) ? InvoiceStatus::Overdue : InvoiceStatus::Unpaid;
    }

    public function periodLabel(): string
    {
        return tanggal($this->period_start, 'F Y');
    }
}
