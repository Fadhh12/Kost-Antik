<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * F-02: katalog publik. Hanya gedung aktif (FR-PROP-02).
 */
class CatalogService
{
    public const PER_PAGE = 9;

    public const MAP_LIMIT = 200;

    public const SORTS = [
        'terbaru' => 'Terbaru',
        'termurah' => 'Harga termurah',
        'rating' => 'Rating tertinggi',
    ];

    /**
     * @param  array{q?:string,city?:string,gender?:string,min_price?:int,max_price?:int,available?:bool,sort?:string}  $filters
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->baseQuery(), $filters);

        match ($filters['sort'] ?? 'terbaru') {
            'termurah' => $query->orderByRaw('COALESCE(min_available_price, min_price) asc'),
            'rating' => $query->orderByDesc('rating_avg')->orderByDesc('reviews_count'),
            default => $query->latest(),
        };

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * Titik peta untuk kost di sekitar (F-XX): mengikuti filter yang sama dengan search(),
     * dibatasi MAP_LIMIT agar peta tetap ringan.
     *
     * @param  array{q?:string,city?:string,gender?:string,min_price?:int,max_price?:int,available?:bool,sort?:string}  $filters
     * @return Collection<int, Property>
     */
    public function points(array $filters): Collection
    {
        return $this->applyFilters($this->baseQuery(), $filters)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->limit(self::MAP_LIMIT)
            ->get();
    }

    /**
     * @param  array{q?:string,city?:string,gender?:string,min_price?:int,max_price?:int,available?:bool,sort?:string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        if ($q = trim((string) ($filters['q'] ?? ''))) {
            $query->where(fn (Builder $w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->orWhere('address', 'like', "%{$q}%"));
        }

        if ($city = $filters['city'] ?? null) {
            $query->where('city', $city);
        }

        if ($gender = $filters['gender'] ?? null) {
            $query->where('gender_target', $gender);
        }

        // Rentang harga: gedung yang punya kamar di rentang itu.
        $min = $filters['min_price'] ?? null;
        $max = $filters['max_price'] ?? null;
        if ($min || $max) {
            $query->whereHas('rooms', fn (Builder $r) => $r
                ->when($min, fn ($r) => $r->where('monthly_price', '>=', $min))
                ->when($max, fn ($r) => $r->where('monthly_price', '<=', $max)));
        }

        if (! empty($filters['available'])) {
            $query->whereHas('rooms', fn (Builder $r) => $r->where('status', RoomStatus::Available));
        }

        return $query;
    }

    /**
     * Kost unggulan di landing: yang masih ada kamar kosong, rating terbaik.
     *
     * @return Collection<int, Property>
     */
    public function featured(int $limit = 3): Collection
    {
        return $this->baseQuery()
            ->whereHas('rooms', fn (Builder $r) => $r->where('status', RoomStatus::Available))
            ->orderByDesc('available_rooms_count')
            ->orderByDesc('rating_avg')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<string>
     */
    public function cities(): array
    {
        return Property::active()->distinct()->orderBy('city')->pluck('city')->all();
    }

    private function baseQuery(): Builder
    {
        return Property::query()
            ->active()
            ->with('coverImage')
            ->withCatalogStats();
    }
}
