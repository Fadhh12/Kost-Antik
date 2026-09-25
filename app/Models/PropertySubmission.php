<?php

namespace App\Models;

use App\Enums\GenderTarget;
use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengajuan pendaftaran kost dari pemilik luar, ditinjau owner sebelum
 * menjadi Property aktif (lihat PropertySubmissionService).
 */
class PropertySubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'latitude',
        'longitude',
        'gender_target',
        'description',
        'contact_name',
        'contact_phone',
        'photos',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'created_property_id',
    ];

    protected function casts(): array
    {
        return [
            'gender_target' => GenderTarget::class,
            'status' => SubmissionStatus::class,
            'photos' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'created_property_id');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', SubmissionStatus::Pending);
    }
}
