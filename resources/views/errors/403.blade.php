@include('errors.layout', [
    'code' => 403,
    'title' => 'Akses ditolak',
    'heading' => 'Pintu ini bukan untukmu',
    'message' => $exception?->getMessage() && $exception->getMessage() !== 'This action is unauthorized.'
        ? $exception->getMessage()
        : 'Akunmu tidak punya izin membuka halaman ini. Kalau menurutmu ini keliru, hubungi pengelola kost.',
])
