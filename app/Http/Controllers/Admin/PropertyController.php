<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FacilityType;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyRequest;
use App\Models\Facility;
use App\Models\Property;
use App\Models\User;
use App\Services\PropertyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F-22 & F-16: gedung (owner CRUD, pengelola ubah info dasar gedungnya).
 */
class PropertyController extends Controller
{
    public function __construct(private PropertyService $properties) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Property::class);

        $query = Property::forManager($request->user())
            ->withCatalogStats()
            ->withCount(['rooms as maintenance_rooms_count' => fn ($q) => $q->where('status', RoomStatus::Maintenance)])
            ->with(['manager', 'coverImage'])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('name');

        return view('admin.properties.index', [
            'properties' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Property::class);

        return view('admin.properties.form', $this->formData(new Property(['status' => PropertyStatus::Active])));
    }

    public function store(PropertyRequest $request): RedirectResponse
    {
        $property = $this->properties->create(
            $request->safe()->except(['facilities', 'images']),
            $request->input('facilities', []),
            $request->file('images', []),
            $request->user(),
        );

        return redirect()->route('admin.properties.show', $property)->with('success', 'Gedung berhasil dibuat. Tambahkan kamar di tab Kamar.');
    }

    public function show(Request $request, Property $property): View
    {
        $this->authorize('view', $property);

        $tab = in_array($request->query('tab'), ['info', 'kamar', 'foto', 'ulasan'], true) ? $request->query('tab') : 'kamar';

        $property->load(['manager', 'facilities', 'images', 'rooms.facilities', 'rooms.activeLease.user'])
            ->loadCount(['publishedReviews as reviews_count'])
            ->loadAvg('publishedReviews as rating_avg', 'rating');

        return view('admin.properties.show', [
            'property' => $property,
            'tab' => $tab,
            'roomFacilities' => Facility::where('type', FacilityType::Room)->orderBy('name')->get(),
            'reviews' => $tab === 'ulasan' ? $property->reviews()->with('user')->latest()->paginate(10)->withQueryString() : null,
        ]);
    }

    public function edit(Property $property): View
    {
        $this->authorize('update', $property);

        return view('admin.properties.form', $this->formData($property->load('facilities')));
    }

    public function update(PropertyRequest $request, Property $property): RedirectResponse
    {
        $manage = $request->user()->can('manage', $property);
        $data = $request->safe()->except(['facilities', 'images']);

        $this->properties->update(
            $property,
            $manage ? $data : array_intersect_key($data, array_flip(PropertyService::MANAGER_FIELDS)),
            $manage ? $request->input('facilities', []) : null,
            $request->user(),
        );

        if ($manage && $request->hasFile('images')) {
            $this->properties->addImages($property, $request->file('images'));
        }

        return redirect()->route('admin.properties.show', [$property, 'tab' => 'info'])->with('success', 'Data gedung diperbarui.');
    }

    public function destroy(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);
        $this->properties->delete($property, $request->user());

        return redirect()->route('admin.properties.index')->with('success', 'Gedung dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Property $property): array
    {
        return [
            'property' => $property,
            'sharedFacilities' => Facility::where('type', FacilityType::Shared)->orderBy('name')->get(),
            'managers' => User::managers()->where('status', 'accepted')->orderBy('name')->pluck('name', 'id'),
            'cities' => Property::distinct()->orderBy('city')->pluck('city'),
        ];
    }
}
