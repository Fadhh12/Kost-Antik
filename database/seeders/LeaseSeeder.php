<?php

namespace Database\Seeders;

use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\UserStatus;
use App\Models\BookingRequest;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Riwayat kontrak, tagihan, dan pembayaran ±6 bulan ke belakang
 * agar dashboard & grafik pendapatan punya data.
 */
class LeaseSeeder extends Seeder
{
    private LeaseService $leases;

    private PaymentService $payments;

    private User $owner;

    public function run(LeaseService $leases, PaymentService $payments): void
    {
        $this->leases = $leases;
        $this->payments = $payments;
        $this->owner = User::where('email', 'owner@kostantik.test')->firstOrFail();
        mt_srand(2026);

        $tenants = User::tenants()->where('status', UserStatus::Accepted)->get()->shuffle(fn () => mt_rand());
        $rooms = Room::with('property')
            ->whereHas('property', fn ($q) => $q->where('status', PropertyStatus::Active))
            ->where('status', RoomStatus::Available)
            ->get();

        // Akun demo penyewa selalu punya kontrak aktif.
        $demo = $tenants->firstWhere('email', 'tenant@kostantik.test');
        $tenants = $tenants->reject(fn ($t) => $t->is($demo))->prepend($demo);

        $today = today();

        // 1) Kontrak lama yang sudah selesai (untuk ulasan & riwayat).
        $historic = $rooms->take(7);
        foreach ($historic as $i => $room) {
            $tenant = $this->pickTenant($tenants, $room, skipDemo: true);
            if (! $tenant) {
                continue;
            }
            $start = $today->copy()->subMonths(9)->addDays($i * 3)->startOfDay();
            $lease = $this->leases->createManual($tenant, $room, $start, 6, $this->owner);
            $this->settleInvoices($lease, payRatio: 1.0);
        }
        $this->leases->completeExpired();

        // 2) Kontrak aktif. Sebagian kamar dibiarkan kosong untuk katalog.
        // Sisakan 2 kamar kosong per gedung agar katalog tetap punya pilihan.
        $toLease = Room::with('property')->whereIn('id', $rooms->pluck('id'))->where('status', RoomStatus::Available)->get()
            ->groupBy('property_id')
            ->flatMap(fn (Collection $group) => $group->slice(0, max(0, $group->count() - 2)))
            ->values();

        foreach ($toLease as $i => $room) {
            $tenant = $this->pickTenant($tenants, $room);
            if (! $tenant) {
                continue;
            }
            $monthsAgo = [5, 4, 3, 2, 1, 1, 0][$i % 7];
            $start = $today->copy()->subMonths($monthsAgo)->subDays(($i * 5) % 20)->startOfDay();
            $duration = [6, 12, 6, 12, 3][$i % 5];
            if ($start->copy()->addMonthsNoOverflow($duration)->lte($today)) {
                $duration = 12;
            }

            $lease = $this->leases->createManual($tenant, $room, $start, $duration, $this->owner);
            $this->settleInvoices($lease, payRatio: $i % 6 === 3 ? 0.5 : 0.95);
        }

        // 3) Booking menunggu dari penyewa tanpa kontrak.
        $this->seedBookings($tenants);
    }

    private function pickTenant(Collection $tenants, Room $room, bool $skipDemo = false): ?User
    {
        foreach ($tenants as $tenant) {
            if ($skipDemo && str_ends_with($tenant->email, '@kostantik.test')) {
                continue;
            }
            if (! $room->property->gender_target->accepts($tenant->gender)) {
                continue;
            }
            if ($tenant->hasActiveLease()) {
                continue;
            }

            return $tenant;
        }

        return null;
    }

    /**
     * Bayar invoice yang sudah jatuh tempo (dengan tanggal historis), sisakan sebagian terlambat,
     * dan buat pembayaran menunggu verifikasi untuk bulan berjalan.
     */
    private function settleInvoices(Lease $lease, float $payRatio): void
    {
        $today = today();

        /** @var Invoice $invoice */
        foreach ($lease->invoices()->get() as $invoice) {
            if ($invoice->period_start->gt($today)) {
                break;
            }

            $isCurrent = $invoice->due_date->gte($today);
            $roll = mt_rand() / mt_getrandmax();

            if ($isCurrent) {
                if ($roll < 0.45) {
                    $this->pendingTransfer($invoice, $lease);
                } elseif ($roll < 0.75) {
                    $this->paid($invoice, $lease, $invoice->period_start->copy()->addDays(mt_rand(0, 2)));
                }

                continue;
            }

            if ($roll <= $payRatio) {
                $this->paid($invoice, $lease, $invoice->period_start->copy()->addDays(mt_rand(0, 4)));
            }
        }
    }

    private function paid(Invoice $invoice, Lease $lease, Carbon $when): void
    {
        $when = $when->min(today());
        $cash = mt_rand(1, 4) === 1;

        $payment = $cash
            ? $this->payments->recordCash($invoice, $lease->room->property->manager ?? $this->owner, $when)
            : $this->payments->verify(
                $this->payments->submit($invoice, $lease->user, $invoice->amount, $when, $this->proof($invoice, $lease->user)),
                $lease->room->property->manager ?? $this->owner,
            );

        $verifiedAt = $when->copy()->setTime(mt_rand(9, 20), mt_rand(0, 59));
        $payment->forceFill(['verified_at' => $verifiedAt, 'created_at' => $verifiedAt, 'updated_at' => $verifiedAt])->save();
        $invoice->forceFill(['paid_at' => $verifiedAt])->save();
    }

    private function pendingTransfer(Invoice $invoice, Lease $lease): void
    {
        $this->payments->submit($invoice, $lease->user, $invoice->amount, today(), $this->proof($invoice, $lease->user));
    }

    private function seedBookings(Collection $tenants): void
    {
        $free = Room::with('property')
            ->where('status', RoomStatus::Available)
            ->whereHas('property', fn ($q) => $q->where('status', PropertyStatus::Active))
            ->get();

        $count = 0;
        foreach ($tenants as $tenant) {
            if ($count >= 4 || $tenant->hasActiveLease() || $tenant->hasPendingBooking()) {
                continue;
            }
            $room = $free->first(fn (Room $r) => $r->property->gender_target->accepts($tenant->gender));
            if (! $room) {
                continue;
            }

            $booking = new BookingRequest([
                'user_id' => $tenant->id,
                'room_id' => $room->id,
                'start_date' => today()->addDays(7 + $count * 3),
                'duration_months' => [6, 3, 12, 1][$count],
                'note' => ['Saya mahasiswa semester 3, rencana tinggal sampai lulus.', null, 'Boleh survei kamar hari Sabtu?', null][$count],
            ]);
            $booking->save();
            $booking->forceFill(['created_at' => now()->subDays($count + 1)])->save();

            $free = $free->reject(fn ($r) => $r->is($room));
            $count++;
        }
    }

    /**
     * Gambar bukti transfer sederhana (GD) agar demo verifikasi punya berkas nyata.
     */
    private function proof(Invoice $invoice, User $tenant): UploadedFile
    {
        $img = imagecreatetruecolor(600, 900);
        $bg = imagecolorallocate($img, 246, 247, 245);
        $ink = imagecolorallocate($img, 20, 32, 31);
        $green = imagecolorallocate($img, 15, 77, 72);
        $muted = imagecolorallocate($img, 86, 98, 95);
        imagefill($img, 0, 0, $bg);
        imagefilledrectangle($img, 0, 0, 600, 120, $green);
        imagestring($img, 5, 30, 40, 'BUKTI TRANSFER', imagecolorallocate($img, 255, 255, 255));
        imagestring($img, 3, 30, 70, 'm-Banking (contoh untuk demo)', imagecolorallocate($img, 207, 227, 223));

        $lines = [
            ['Status', 'BERHASIL'],
            ['Tanggal', today()->format('d/m/Y')],
            ['Dari', $tenant->name],
            ['Ke', 'Kost Antik - BCA 1234567890'],
            ['Nominal', rupiah($invoice->amount)],
            ['Berita', $invoice->number],
        ];
        foreach ($lines as $i => [$label, $value]) {
            imagestring($img, 3, 30, 170 + $i * 60, $label, $muted);
            imagestring($img, 5, 30, 190 + $i * 60, $value, $ink);
        }

        $path = tempnam(sys_get_temp_dir(), 'bukti').'.jpg';
        imagejpeg($img, $path, 80);
        imagedestroy($img);

        return new UploadedFile($path, 'bukti-transfer.jpg', 'image/jpeg', null, true);
    }
}
