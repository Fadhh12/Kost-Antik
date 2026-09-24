<?php

namespace App\Models;

use App\Enums\FacilityType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'type'];

    protected function casts(): array
    {
        return ['type' => FacilityType::class];
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }

    public function scopeOfType(Builder $query, FacilityType $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Nama komponen ikon Lucide, dengan cadangan bila kosong.
     */
    public function iconComponent(): string
    {
        return 'lucide-'.($this->icon ?: 'check');
    }
}
