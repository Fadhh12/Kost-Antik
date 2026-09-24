<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * F-27 (P2): laporan pembayaran terverifikasi per periode + ekspor CSV (owner).
 */
class ReportController extends Controller
{
    public function payments(Request $request): View|StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $query = $this->query($request, $from, $to);

        if ($request->query('format') === 'csv') {
            return $this->csv($query, $from, $to);
        }

        $byProperty = (clone $query)
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('leases', 'leases.id', '=', 'invoices.lease_id')
            ->join('rooms', 'rooms.id', '=', 'leases.room_id')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->selectRaw('properties.name as property_name, count(payments.id) as count, sum(payments.amount) as amount')
            ->groupBy('properties.name')
            ->orderByDesc('amount')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->property_name => ['count' => (int) $row->count, 'amount' => (int) $row->amount]]);

        return view('admin.reports.payments', [
            'payments' => (clone $query)->latest('verified_at')->paginate(15)->withQueryString(),
            'total' => (int) (clone $query)->sum('amount'),
            'count' => (clone $query)->count(),
            'byProperty' => $byProperty,
            'from' => $from,
            'to' => $to,
            'properties' => Property::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = rescue(fn () => Carbon::parse($request->query('dari')), null, false) ?? now()->startOfMonth();
        $to = rescue(fn () => Carbon::parse($request->query('sampai')), null, false) ?? today();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    private function query(Request $request, Carbon $from, Carbon $to): Builder
    {
        return Payment::query()
            ->where('payments.status', PaymentStatus::Verified)
            ->whereBetween('payments.verified_at', [$from, $to])
            ->when($request->filled('property'), fn ($q) => $q->whereHas('invoice.lease.room', fn ($r) => $r->where('property_id', $request->integer('property'))))
            ->with(['invoice.lease.user', 'invoice.lease.room.property', 'verifier']);
    }

    private function csv(Builder $query, Carbon $from, Carbon $to): StreamedResponse
    {
        $filename = 'laporan-pembayaran-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Tanggal verifikasi', 'No. tagihan', 'Periode', 'Penyewa', 'Gedung', 'Kamar', 'Metode', 'Nominal', 'Diverifikasi oleh'], ';');

            $query->orderBy('verified_at')->chunk(200, function ($payments) use ($out) {
                foreach ($payments as $p) {
                    $lease = $p->invoice->lease;
                    fputcsv($out, [
                        $p->verified_at->format('Y-m-d H:i'),
                        $p->invoice->number,
                        $p->invoice->period_start->format('Y-m'),
                        $lease->user->name,
                        $lease->room->property->name,
                        $lease->room->code,
                        $p->method->label(),
                        $p->amount,
                        $p->verifier?->name,
                    ], ';');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
