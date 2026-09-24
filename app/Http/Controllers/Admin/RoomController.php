<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomRequest;
use App\Models\Property;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * F-16: kamar per gedung. Daftar tampil di tab Kamar halaman gedung.
 */
class RoomController extends Controller
{
    public function __construct(private RoomService $rooms) {}

    public function store(RoomRequest $request, Property $property): RedirectResponse
    {
        $room = $this->rooms->create(
            $property,
            $request->safe()->except('facilities'),
            $request->input('facilities', []),
            $request->user(),
        );

        return back()->with('success', 'Kamar '.$room->code.' ditambahkan.');
    }

    public function update(RoomRequest $request, Property $property, Room $room): RedirectResponse
    {
        $this->rooms->update($room, $request->safe()->except('facilities'), $request->input('facilities', []), $request->user());

        return back()->with('success', 'Kamar '.$room->code.' diperbarui.');
    }

    public function destroy(Request $request, Property $property, Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);
        $this->rooms->delete($room, $request->user());

        return back()->with('success', 'Kamar '.$room->code.' dihapus.');
    }
}
