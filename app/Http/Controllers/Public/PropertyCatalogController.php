<?php

namespace App\Http\Controllers\Public;

use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogRequest;
use App\Models\Property;
use App\Services\CatalogService;
use App\Services\PropertyDetailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyCatalogController extends Controller
{
    public function index(CatalogRequest $request, CatalogService $catalog): View
    {
        $filters = $request->filters();

        return view('public.catalog', [
            'properties' => $catalog->search($filters),
            'mapPoints' => $catalog->points($filters),
            'filters' => $filters,
            'cities' => $catalog->cities(),
            'sorts' => CatalogService::SORTS,
        ]);
    }

    public function show(Property $property, PropertyDetailService $details): View
    {
        abort_unless($property->status === PropertyStatus::Active, 404); // FR-PROP-02

        return view('public.property', $details->forPublic($property, auth()->user()));
    }

    /**
     * Flow 1: setelah login/daftar, kembali ke kamar yang dipilih dan buka form pengajuan.
     */
    public function apply(Request $request, Property $property): RedirectResponse
    {
        return redirect(route('kost.show', [$property, 'kamar' => $request->integer('kamar') ?: null]).'#kamar');
    }
}
