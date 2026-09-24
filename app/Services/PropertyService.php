<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyService
{
    /** Kolom yang boleh diubah pengelola (matriks otorisasi 3.5). */
    public const MANAGER_FIELDS = ['address', 'city', 'latitude', 'longitude', 'description', 'rules'];

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $facilityIds
     * @param  list<UploadedFile>  $images
     */
    public function create(array $data, array $facilityIds, array $images, User $actor): Property
    {
        return DB::transaction(function () use ($data, $facilityIds, $images, $actor) {
            $property = Property::create($data);
            $property->facilities()->sync($facilityIds);
            $this->addImages($property, $images);

            activity('property')->performedOn($property)->causedBy($actor)->event('created')->log('Gedung dibuat');

            return $property;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>|null  $facilityIds  null = tidak diubah
     */
    public function update(Property $property, array $data, ?array $facilityIds, User $actor): Property
    {
        return DB::transaction(function () use ($property, $data, $facilityIds, $actor) {
            $property->fill($data);
            $changes = $property->getDirty();
            $property->save();

            if ($facilityIds !== null) {
                $property->facilities()->sync($facilityIds);
            }

            activity('property')->performedOn($property)->causedBy($actor)->event('updated')
                ->withProperties(['changes' => array_keys($changes)])
                ->log('Gedung diperbarui');

            return $property;
        });
    }

    /**
     * FR-PROP-01: gedung dengan riwayat kontrak tidak boleh dihapus.
     */
    public function delete(Property $property, User $actor): void
    {
        if ($property->hasLeaseHistory()) {
            throw new BusinessRuleException('Gedung ini punya riwayat kontrak sehingga tidak bisa dihapus. Ubah statusnya menjadi nonaktif.');
        }

        DB::transaction(function () use ($property, $actor) {
            $property->delete();
            activity('property')->performedOn($property)->causedBy($actor)->event('deleted')->log('Gedung dihapus');
        });
    }

    /**
     * @param  list<UploadedFile>  $images
     */
    public function addImages(Property $property, array $images): void
    {
        $hasCover = $property->images()->where('is_cover', true)->exists();
        $order = (int) $property->images()->max('sort_order');

        foreach ($images as $i => $file) {
            $property->images()->create([
                'path' => $file->store("properties/{$property->id}", 'public'),
                'is_cover' => ! $hasCover && $i === 0,
                'sort_order' => ++$order,
            ]);
        }
    }

    public function setCover(PropertyImage $image): void
    {
        DB::transaction(function () use ($image) {
            $image->property->images()->update(['is_cover' => false]);
            $image->update(['is_cover' => true]);
        });
    }

    public function deleteImage(PropertyImage $image): void
    {
        DB::transaction(function () use ($image) {
            $property = $image->property;
            $wasCover = $image->is_cover;

            if (! $image->isPlaceholder()) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();

            if ($wasCover) {
                $property->images()->orderBy('sort_order')->first()?->update(['is_cover' => true]);
            }
        });
    }
}
