@include('errors.layout', [
    'code' => 500,
    'title' => 'Terjadi kesalahan',
    'heading' => 'Ada yang rusak di sistem kami',
    'message' => 'Permintaanmu gagal diproses dan kesalahannya sudah tercatat. Coba lagi beberapa saat lagi.',
])
