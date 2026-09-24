<?php

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    /**
     * BR-12: tampilkan uang sebagai "Rp1.250.000".
     */
    function rupiah(int|float|null $amount): string
    {
        return 'Rp'.number_format((int) ($amount ?? 0), 0, ',', '.');
    }
}

if (! function_exists('tanggal')) {
    /**
     * BR-11: format tanggal "d M Y" dengan locale Indonesia.
     */
    function tanggal(CarbonInterface|string|null $date, string $format = 'd M Y'): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        $date = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return $date->locale('id')->translatedFormat($format);
    }
}
