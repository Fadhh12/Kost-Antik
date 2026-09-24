<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Aturan Bisnis Kost Antik
    |--------------------------------------------------------------------------
    |
    | Nilai di sini dipakai oleh service layer (lihat docs/BLUEPRINT.md).
    |
    */

    // FR-INV-02: masa tenggang pembayaran sejak awal periode invoice.
    'grace_days' => (int) env('KOST_GRACE_DAYS', 3),

    // FR-BOOK-07: booking pending lebih lama dari ini otomatis expired.
    'booking_expire_days' => (int) env('KOST_BOOKING_EXPIRE_DAYS', 7),

    // Durasi sewa yang boleh dipilih (bulan).
    'durations' => [1, 3, 6, 12],

    // FR-REV-01 & FR-REV-03.
    'review_min_active_days' => 30,
    'review_edit_days' => 7,

    'bank_info' => env('KOST_BANK_INFO', 'BCA 1234567890 a.n. Kost Antik'),

    'contact' => [
        'phone' => env('KOST_CONTACT_PHONE', '+62 812-0000-0000'),
        'email' => env('KOST_CONTACT_EMAIL', 'halo@kostantik.test'),
        'address' => env('KOST_ADDRESS', 'Jl. Kemang Pratama Raya No. 12, Bekasi'),
        'hours' => 'Senin–Sabtu, 08.00–20.00 WIB',
    ],

];
