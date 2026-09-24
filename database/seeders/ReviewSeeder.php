<?php

namespace Database\Seeders;

use App\Enums\LeaseStatus;
use App\Models\Lease;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            [5, 'Kamarnya bersih dan terang, jendela besar jadi tidak pengap. Ibu penjaga ramah dan cepat kalau ada keran bocor.'],
            [5, 'Dekat halte dan minimarket. Tagihan bulanan jelas, tinggal upload bukti transfer dan langsung dikonfirmasi.'],
            [4, 'Wi-Fi stabil untuk kuliah online. Dapur bersama kadang ramai di jam makan malam, tapi masih nyaman.'],
            [4, 'Lantai tegelnya adem, cocok buat Bekasi yang panas. Parkir motor cukup luas.'],
            [5, 'Sudah dua tahun di sini. Pengelola responsif dan lingkungannya aman, ada CCTV di lorong.'],
            [3, 'Kamar oke untuk harganya. Air sempat kecil beberapa hari waktu musim kemarau.'],
            [4, 'Lokasinya strategis ke kawasan industri. Kamar mandi dalam bersih dan airnya lancar.'],
            [5, 'Aturan jelas dan penghuninya tertib. Jam malam membuat suasana tenang untuk istirahat.'],
            [4, 'Proses sewa gampang, dari ajukan online sampai dapat kunci cuma dua hari.'],
            [3, 'Suara motor dari jalan depan cukup terdengar di kamar lantai satu. Selebihnya baik.'],
            [5, 'Harga sepadan dengan fasilitas. Laundry di lantai atas sangat membantu.'],
            [4, 'Ruang tamunya enak untuk kerja kelompok. Pengelola mengingatkan tagihan dengan sopan.'],
        ];

        $leases = Lease::with('room')
            ->whereIn('status', [LeaseStatus::Completed, LeaseStatus::Active])
            ->whereDate('start_date', '<=', today()->subDays(config('kost.review_min_active_days')))
            ->doesntHave('review')
            ->orderBy('id')
            ->get();

        // Sebar ulasan ke semua gedung (round-robin per gedung).
        $groups = $leases->groupBy(fn (Lease $l) => $l->room->property_id)->values();
        $spread = collect();
        for ($i = 0; $spread->count() < $leases->count(); $i++) {
            $groups->each(function ($group) use ($i, $spread) {
                if ($group->has($i)) {
                    $spread->push($group[$i]);
                }
            });
        }

        foreach ($spread->take(count($comments)) as $i => $lease) {
            [$rating, $comment] = $comments[$i];

            $review = new Review(['rating' => $rating, 'comment' => $comment]);
            $review->forceFill([
                'lease_id' => $lease->id,
                'user_id' => $lease->user_id,
                'property_id' => $lease->room->property_id,
                // Satu ulasan disembunyikan untuk demo moderasi (FR-REV-04).
                'is_published' => $i !== 5,
                'created_at' => $lease->start_date->copy()->addDays(40)->min(now()),
            ])->save();
        }
    }
}
