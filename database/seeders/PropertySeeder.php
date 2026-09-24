<?php

namespace Database\Seeders;

use App\Enums\FacilityType;
use App\Enums\GenderTarget;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Models\Facility;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $managerA = User::where('email', 'manager@kostantik.test')->first();
        $managerB = User::where('email', 'manager2@kostantik.test')->first();

        $roomFacilities = Facility::where('type', FacilityType::Room)->pluck('id', 'name');
        $sharedFacilities = Facility::where('type', FacilityType::Shared)->pluck('id', 'name');

        $rules = "Jam malam pukul 22.00. Gerbang dikunci setelahnya.\nTamu lawan jenis hanya boleh di ruang tamu sampai pukul 21.00.\nDilarang merokok di dalam kamar.\nListrik token per kamar, diisi sendiri oleh penghuni.\nPembayaran sewa paling lambat 3 hari setelah tanggal tagihan.";

        $properties = [
            [
                'data' => [
                    'name' => 'Kost Melati Putri',
                    'address' => 'Jl. Kemang Pratama Raya No. 18, Rawalumbu',
                    'city' => 'Bekasi',
                    'latitude' => -6.2718200,
                    'longitude' => 106.9934100,
                    'gender_target' => GenderTarget::Female,
                    'description' => "Rumah kost dua lantai khusus putri di kompleks Kemang Pratama. Lima menit jalan kaki ke halte Transpatriot dan minimarket. Lantai kamar masih memakai tegel kunci asli yang dirawat, jendela besar di setiap kamar, dan penjaga kost tinggal di lantai dasar.\n\nCocok untuk mahasiswi Unisma dan pekerja di sekitar Bekasi Timur.",
                    'manager_id' => $managerA?->id,
                ],
                'shared' => ['Wi-Fi', 'Dapur bersama', 'Parkir motor', 'CCTV 24 jam', 'Ruang tamu', 'Kulkas bersama'],
                'images' => ['kost-1.svg', 'kamar-1.svg', 'tegel-1.svg', 'kamar-3.svg', 'tegel-3.svg'],
                'rooms' => [
                    ['1A', 1, 12, 1_250_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['1B', 1, 12, 1_250_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['1C', 1, 9, 950_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['2A', 2, 14, 1_400_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['2B', 2, 12, 1_250_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['2C', 2, 9, 950_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['2D', 2, 9, 950_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian']],
                ],
            ],
            [
                'data' => [
                    'name' => 'Kost Jati Putra',
                    'address' => 'Jl. Kasuari Raya Blok B2 No. 7, Cikarang Baru',
                    'city' => 'Cikarang',
                    'latitude' => -6.3027400,
                    'longitude' => 107.1564300,
                    'gender_target' => GenderTarget::Male,
                    'description' => "Kost putra dekat Kawasan Industri Jababeka. Kusen dan pintu kayu jati, kamar lega dengan ventilasi silang. Parkir motor beratap untuk 20 motor dan area jemur di lantai atas.\n\nBanyak dihuni karyawan pabrik dan mahasiswa President University.",
                    'manager_id' => $managerB?->id,
                ],
                'shared' => ['Wi-Fi', 'Parkir motor', 'Laundry', 'CCTV 24 jam', 'Dapur bersama'],
                'images' => ['kost-2.svg', 'kamar-2.svg', 'tegel-2.svg', 'kamar-4.svg'],
                'rooms' => [
                    ['A1', 1, 10.5, 900_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian']],
                    ['A2', 1, 10.5, 900_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian']],
                    ['A3', 1, 12, 1_100_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['B1', 2, 12, 1_100_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['B2', 2, 12, 1_100_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['B3', 2, 14, 1_300_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['B4', 2, 14, 1_300_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam'], RoomStatus::Maintenance],
                    ['C1', 3, 9, 850_000, ['Kipas angin', 'Kasur & bantal']],
                ],
            ],
            [
                'data' => [
                    'name' => 'Wisma Tegel Kunci',
                    'address' => 'Jl. Ahmad Yani No. 44, Bekasi Selatan',
                    'city' => 'Bekasi',
                    'latitude' => -6.2441300,
                    'longitude' => 106.9930200,
                    'gender_target' => GenderTarget::Mixed,
                    'description' => "Rumah peninggalan tahun 70-an yang dipugar menjadi kost campur dengan lantai putra dan putri terpisah. Dekat Summarecon Mall Bekasi dan Stasiun Bekasi. Setiap kamar punya kamar mandi dalam dan AC.\n\nPengelola tinggal di paviliun depan dan bisa dihubungi setiap hari.",
                    'manager_id' => $managerA?->id,
                ],
                'shared' => ['Wi-Fi', 'Dapur bersama', 'Laundry', 'CCTV 24 jam', 'Ruang tamu', 'Parkir motor'],
                'images' => ['kost-3.svg', 'tegel-3.svg', 'kamar-3.svg', 'kamar-1.svg'],
                'rooms' => [
                    ['101', 1, 14, 1_600_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['102', 1, 14, 1_600_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['103', 1, 16, 1_850_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['201', 2, 14, 1_600_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['202', 2, 14, 1_600_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                    ['203', 2, 16, 1_850_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar', 'Kamar mandi dalam']],
                ],
            ],
            [
                'data' => [
                    'name' => 'Rumah Kost Kenari',
                    'address' => 'Jl. Jababeka Raya Blok C No. 12, Cikarang Utara',
                    'city' => 'Cikarang',
                    'latitude' => -6.2896100,
                    'longitude' => 107.1440200,
                    'gender_target' => GenderTarget::Mixed,
                    'description' => 'Kost campur dengan harga bersahabat di jantung Jababeka. Sepuluh menit ke Stasiun Cikarang. Kamar sederhana tapi bersih, cocok untuk yang baru mulai bekerja.',
                    'manager_id' => $managerB?->id,
                ],
                'shared' => ['Wi-Fi', 'Parkir motor', 'Kulkas bersama'],
                'images' => ['kost-4.svg', 'kamar-4.svg', 'tegel-4.svg'],
                'rooms' => [
                    ['K1', 1, 9, 800_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian']],
                    ['K2', 1, 9, 800_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian']],
                    ['K3', 1, 10, 850_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['K4', 2, 10, 850_000, ['Kipas angin', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['K5', 2, 12, 1_000_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar'], RoomStatus::Maintenance],
                    ['K6', 2, 12, 1_000_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                    ['K7', 2, 12, 1_000_000, ['AC', 'Kasur & bantal', 'Lemari pakaian', 'Meja belajar']],
                ],
            ],
            [
                // FR-PROP-02: gedung nonaktif tidak tampil di katalog.
                'data' => [
                    'name' => 'Kost Cempaka',
                    'address' => 'Jl. Cempaka Putih No. 3, Tambun Selatan',
                    'city' => 'Bekasi',
                    'latitude' => null,
                    'longitude' => null,
                    'gender_target' => GenderTarget::Female,
                    'description' => 'Sedang direnovasi. Dibuka kembali setelah pekerjaan atap selesai.',
                    'status' => PropertyStatus::Inactive,
                    'manager_id' => null,
                ],
                'shared' => ['Wi-Fi'],
                'images' => ['kost-2.svg'],
                'rooms' => [
                    ['1', 1, 9, 750_000, ['Kasur & bantal']],
                    ['2', 1, 9, 750_000, ['Kasur & bantal']],
                ],
            ],
        ];

        foreach ($properties as $spec) {
            $property = Property::updateOrCreate(
                ['name' => $spec['data']['name']],
                $spec['data'] + ['rules' => $rules, 'status' => PropertyStatus::Active],
            );

            $property->facilities()->sync($sharedFacilities->only($spec['shared'])->values());

            $property->images()->delete();
            foreach ($spec['images'] as $i => $image) {
                $property->images()->create([
                    'path' => 'placeholders/'.$image,
                    'is_cover' => $i === 0,
                    'sort_order' => $i,
                ]);
            }

            foreach ($spec['rooms'] as $room) {
                [$code, $floor, $size, $price, $facilities] = $room;
                $model = $property->rooms()->updateOrCreate(['code' => $code], [
                    'floor' => $floor,
                    'size_m2' => $size,
                    'monthly_price' => $price,
                    'capacity' => 1,
                    'status' => $room[5] ?? RoomStatus::Available,
                ]);
                $model->facilities()->sync($roomFacilities->only($facilities)->values());
            }
        }
    }
}
