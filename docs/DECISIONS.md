# Catatan Keputusan

Keputusan teknis yang diambil selama pembangunan karena blueprint ambigu atau karena kondisi lingkungan. Format: tanggal, konteks, keputusan, alasan.

## D-01 · PHP 8.3 untuk Laravel 11
- **Konteks:** Laragon di mesin pengembangan hanya menyediakan PHP 8.1, sedangkan Laravel 11 butuh PHP 8.2+.
- **Keputusan:** Pasang PHP 8.3 (build resmi windows.php.net) berdampingan dengan PHP 8.1 di Laragon.
- **Dampak:** `composer.json` tetap mensyaratkan `php ^8.2`.

## D-02 · SQLite untuk dev lokal & test, MySQL untuk `.env.example`
- **Konteks:** Blueprint menargetkan MySQL 8, tetapi mengizinkan SQLite untuk dev.
- **Keputusan:** `.env.example` memakai MySQL (`kost_antik`). Test memakai SQLite in-memory (`phpunit.xml`). Migration ditulis agar kompatibel dengan keduanya (enum sebagai `string` + PHP Enum, tanpa fungsi SQL khusus MySQL di query).

## D-03 · Status Laravel 11
- **Konteks:** Dukungan keamanan Laravel 11 sudah berakhir. `composer audit` melaporkan advisory `laravel/framework` yang hanya diperbaiki di 12.x.
- **Keputusan:** Tetap Laravel 11 sesuai tech stack blueprint. Upgrade ke Laravel 12 disarankan sebelum produksi (struktur aplikasi identik, perubahan minimal).

## D-04 · Skill desain
- **Konteks:** Prompt meminta skill `frontend-design`. Skill yang terpasang di lingkungan ini adalah keluarga `taste-skill` (anti-template, arah desain berbasis brief).
- **Keputusan:** Pakai `taste-skill` sebagai pengganti dengan prinsip yang sama. Hasil keputusan desain ditulis di `docs/DESIGN.md`.

## D-05 · Granularitas commit
- **Keputusan:** Commit & push per unit kerja kecil (bukan hanya per fase), atas permintaan pemilik repo. Pesan tetap Conventional Commits, dan setiap commit lolos `php artisan test` + `pint --test`.
