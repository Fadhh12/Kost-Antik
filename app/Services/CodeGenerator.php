<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lease;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Kode dokumen: KA-LS-YYYYMM-XXXX (FR-LEASE-05) dan KA-INV-YYYYMM-XXXX (FR-INV-03).
 * Nomor urut per bulan; dipanggil di dalam transaksi, kolom unik menjaga tabrakan.
 */
class CodeGenerator
{
    public function leaseCode(CarbonInterface $date): string
    {
        return $this->next(Lease::class, 'code', 'KA-LS-'.$date->format('Ym').'-');
    }

    public function invoiceNumber(CarbonInterface $period): string
    {
        return $this->next(Invoice::class, 'number', 'KA-INV-'.$period->format('Ym').'-');
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function next(string $model, string $column, string $prefix): string
    {
        $last = $model::query()
            ->where($column, 'like', $prefix.'%')
            ->orderByDesc($column)
            ->value($column);

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
