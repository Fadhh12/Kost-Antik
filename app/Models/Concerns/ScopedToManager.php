<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * BR-08: pengelola hanya melihat data di gedung yang ia kelola.
 * Owner melihat semua. Model memakai trait ini dengan mendefinisikan
 * propertyRelationPath(): relasi (dot) menuju model yang punya kolom
 * property_id, atau null bila kolom itu ada di tabel model sendiri.
 */
trait ScopedToManager
{
    abstract protected static function propertyRelationPath(): ?string;

    public function scopeForManager(Builder $query, User $user): void
    {
        if ($user->isOwner()) {
            return;
        }

        $ids = $user->managedPropertyIds();
        $path = static::propertyRelationPath();

        if ($path === null) {
            $query->whereIn($query->qualifyColumn('property_id'), $ids);

            return;
        }

        $query->whereHas($path, fn (Builder $q) => $q->whereIn('property_id', $ids));
    }
}
