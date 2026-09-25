<?php

namespace App\Models;

use App\Enums\GenderTarget;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'manager_id',
        'name',
        'address',
        'city',
        'latitude',
        'longitude',
        'gender_target',
        'description',
        'rules',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gender_target' => GenderTarget::class,
            'status' => PropertyStatus::class,
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        // Slug otomatis dari nama, unik (termasuk yang soft-deleted).
        static::saving(function (Property $property) {
            if (! $property->slug || $property->isDirty('name')) {
                $base = Str::slug($property->name) ?: 'kost';
                $slug = $base;
                $i = 2;
                while (static::withTrashed()->where('slug', $slug)->whereKeyNot($property->getKey() ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $property->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relasi

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderByDesc('is_cover')->orderBy('sort_order');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->ofMany(
            ['is_cover' => 'max', 'id' => 'min'],
        );
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class)->orderBy('floor')->orderBy('code');
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class);
    }

    public function leases(): HasManyThrough
    {
        return $this->hasManyThrough(Lease::class, Room::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function publishedReviews(): HasMany
    {
        return $this->reviews()->where('is_published', true);
    }

    // Scope

    public function scopeActive(Builder $query): void
    {
        $query->where('status', PropertyStatus::Active);
    }

    public function scopeForManager(Builder $query, User $user): void
    {
        if (! $user->isOwner()) {
            $query->where('manager_id', $user->id);
        }
    }

    /**
     * Agregat untuk kartu katalog & tabel admin (FR-PROP-05, FR-PROP-06, FR-REV-04).
     */
    public function scopeWithCatalogStats(Builder $query): void
    {
        $query->withCount([
            'rooms',
            'rooms as available_rooms_count' => fn (Builder $q) => $q->where('status', RoomStatus::Available),
            'rooms as occupied_rooms_count' => fn (Builder $q) => $q->where('status', RoomStatus::Occupied),
            'publishedReviews as reviews_count',
        ])
            ->withMin(['rooms as min_available_price' => fn (Builder $q) => $q->where('status', RoomStatus::Available)], 'monthly_price')
            ->withMin('rooms as min_price', 'monthly_price')
            ->withAvg('publishedReviews as rating_avg', 'rating');
    }

    // Atribut

    /**
     * FR-PROP-06: harga "mulai dari".
     */
    protected function startingPrice(): Attribute
    {
        return Attribute::get(fn () => (int) ($this->min_available_price ?? $this->min_price ?? 0));
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->coverImage?->url ?? asset('images/placeholders/kost-1.jpg'));
    }

    protected function hasLocation(): Attribute
    {
        return Attribute::get(fn () => $this->latitude !== null && $this->longitude !== null);
    }

    /**
     * FR-PROP-01: gedung dengan riwayat kontrak tidak boleh dihapus.
     */
    public function hasLeaseHistory(): bool
    {
        return $this->leases()->exists();
    }
}
