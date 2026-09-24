<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoomService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $facilityIds
     */
    public function create(Property $property, array $data, array $facilityIds, User $actor): Room
    {
        return DB::transaction(function () use ($property, $data, $facilityIds, $actor) {
            $room = $property->rooms()->create($data);
            $room->facilities()->sync($facilityIds);

            activity('room')->performedOn($room)->causedBy($actor)->event('created')
                ->withProperties(['property' => $property->name, 'price' => $room->monthly_price])
                ->log('Kamar dibuat');

            return $room;
        });
    }

    /**
     * FR-PROP-03: status occupied hanya dikelola sistem.
     *
     * @param  array<string, mixed>  $data
     * @param  list<int>  $facilityIds
     */
    public function update(Room $room, array $data, array $facilityIds, User $actor): Room
    {
        return DB::transaction(function () use ($room, $data, $facilityIds, $actor) {
            $room = Room::whereKey($room->id)->lockForUpdate()->firstOrFail();

            if ($room->status === RoomStatus::Occupied) {
                // Kamar terisi: status tidak bisa diubah manual.
                unset($data['status']);
            }

            $oldPrice = $room->monthly_price;
            $room->fill($data)->save();
            $room->facilities()->sync($facilityIds);

            // BR-09: perubahan harga dicatat.
            if ($oldPrice !== $room->monthly_price) {
                activity('room')->performedOn($room)->causedBy($actor)->event('price_changed')
                    ->withProperties(['old' => $oldPrice, 'new' => $room->monthly_price])
                    ->log('Harga kamar diubah');
            }

            return $room;
        });
    }

    public function delete(Room $room, User $actor): void
    {
        if ($room->leases()->exists() || $room->bookings()->exists()) {
            throw new BusinessRuleException('Kamar '.$room->code.' punya riwayat booking/kontrak sehingga tidak bisa dihapus. Ubah statusnya menjadi perbaikan.');
        }

        DB::transaction(function () use ($room, $actor) {
            $room->facilities()->detach();
            $room->delete();
            activity('room')->performedOn($room)->causedBy($actor)->event('deleted')->log('Kamar dihapus');
        });
    }
}
