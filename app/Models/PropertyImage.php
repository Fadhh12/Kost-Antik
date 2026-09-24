<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'path', 'is_cover', 'sort_order'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Gambar placeholder seeder ada di public/images; unggahan di disk public.
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => str_starts_with($this->path, 'placeholders/')
            ? asset('images/'.$this->path)
            : Storage::disk('public')->url($this->path));
    }

    public function isPlaceholder(): bool
    {
        return str_starts_with($this->path, 'placeholders/');
    }
}
