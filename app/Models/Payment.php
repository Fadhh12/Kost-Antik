<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\ScopedToManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, ScopedToManager;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    protected static function propertyRelationPath(): ?string
    {
        return 'invoice.lease.room';
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by')->withTrashed();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by')->withTrashed();
    }

    public function scopeStatus(Builder $query, PaymentStatus $status): void
    {
        $query->where('status', $status);
    }

    public function hasProof(): bool
    {
        return filled($this->proof_path);
    }

    public function proofIsPdf(): bool
    {
        return str_ends_with(strtolower((string) $this->proof_path), '.pdf');
    }
}
