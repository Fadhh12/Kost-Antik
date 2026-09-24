<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\PropertyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Foto gedung: unggah, jadikan cover, hapus (owner).
 */
class PropertyImageController extends Controller
{
    public function __construct(private PropertyService $properties) {}

    public function store(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('manage', $property);

        $request->validate([
            'images' => ['required', 'array', 'max:'.max(1, 10 - $property->images()->count())],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'images.max' => 'Satu gedung maksimal 10 foto.',
        ]);

        $this->properties->addImages($property, $request->file('images'));

        return back()->with('success', 'Foto ditambahkan.');
    }

    public function cover(PropertyImage $image): RedirectResponse
    {
        $this->authorize('manage', $image->property);
        $this->properties->setCover($image);

        return back()->with('success', 'Foto cover diperbarui.');
    }

    public function destroy(PropertyImage $image): RedirectResponse
    {
        $this->authorize('manage', $image->property);
        $this->properties->deleteImage($image);

        return back()->with('success', 'Foto dihapus.');
    }
}
