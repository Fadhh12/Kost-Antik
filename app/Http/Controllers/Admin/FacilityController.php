<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacilityRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * F-23: master fasilitas (owner).
 */
class FacilityController extends Controller
{
    public function index(): View
    {
        return view('admin.facilities.index', [
            'facilities' => Facility::withCount(['properties', 'rooms'])->orderBy('type')->orderBy('name')->paginate(10),
        ]);
    }

    public function store(FacilityRequest $request): RedirectResponse
    {
        Facility::create($request->validated());

        return back()->with('success', 'Fasilitas ditambahkan.');
    }

    public function update(FacilityRequest $request, Facility $facility): RedirectResponse
    {
        $facility->update($request->validated());

        return back()->with('success', 'Fasilitas diperbarui.');
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $facility->delete();

        return back()->with('success', 'Fasilitas dihapus dari semua gedung dan kamar.');
    }
}
