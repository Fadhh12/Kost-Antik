<?php

namespace App\Models;

use App\Enums\InstanceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instance extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'address'];

    protected function casts(): array
    {
        return ['type' => InstanceType::class];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
