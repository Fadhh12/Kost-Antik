<?php

namespace Database\Seeders;

use App\Enums\FacilityType;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            // Fasilitas kamar
            ['AC', 'air-vent', FacilityType::Room],
            ['Kasur & bantal', 'bed-double', FacilityType::Room],
            ['Lemari pakaian', 'shirt', FacilityType::Room],
            ['Meja belajar', 'lamp-desk', FacilityType::Room],
            ['Kamar mandi dalam', 'shower-head', FacilityType::Room],
            ['Kipas angin', 'fan', FacilityType::Room],
            // Fasilitas bersama
            ['Wi-Fi', 'wifi', FacilityType::Shared],
            ['Dapur bersama', 'cooking-pot', FacilityType::Shared],
            ['Parkir motor', 'bike', FacilityType::Shared],
            ['Laundry', 'washing-machine', FacilityType::Shared],
            ['CCTV 24 jam', 'cctv', FacilityType::Shared],
            ['Ruang tamu', 'sofa', FacilityType::Shared],
            ['Kulkas bersama', 'refrigerator', FacilityType::Shared],
        ];

        foreach ($facilities as [$name, $icon, $type]) {
            Facility::updateOrCreate(['name' => $name], ['icon' => $icon, 'type' => $type]);
        }
    }
}
