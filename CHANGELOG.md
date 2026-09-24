# Changelog — Kost Antik

Ringkasan pembangunan per fase (Task Breakdown, `docs/BLUEPRINT.md` bagian 5). Status: ✅ selesai · ⏳ parsial · ❌ belum.

## Fase 0 — Setup Proyek
Bootstrap Laravel 11 + Breeze (Blade) + Tailwind + Alpine + spatie/laravel-permission, locale `id`, timezone `Asia/Jakarta`, terjemahan validasi Indonesia, `.env.example`, Pint.

## Fase 1 — Design System & Layout
Token warna/tipografi/motif tegel kunci di `tailwind.config.js` (lihat `docs/DESIGN.md`), 20+ komponen Blade, layout public/auth/tenant/admin, halaman error 403/404/419/500 bertema, `/_styleguide`.

## Fase 2 — Database & Domain
12 Enum PHP, migration seluruh skema (users, instances, properties, rooms, facilities, bookings, leases, invoices, payments, reviews, complaints), model + relasi + scope, factory, seeder demo (5 gedung, 30 kamar, 30 pengguna, riwayat 9 bulan).

## Fase 3 — Auth, Role & Akun
Registrasi penyewa (status `pending`), middleware `EnsureAccountIsActive`, redirect per role, throttle login, halaman profil (foto, kata sandi), Policy per model dibatasi gedung yang dikelola (BR-08), `Gate::before` untuk owner.

## Fase 4 — Halaman Publik
Landing, katalog (filter + sort + pagination via query string), detail kost (galeri + lightbox, peta Leaflet, ulasan, form ajukan sewa).

## Fase 5 — Admin: Master Data, Gedung & Kamar
CRUD fasilitas & instansi (modal), CRUD gedung (foto multi-upload, cover, peta pilih lokasi, penugasan pengelola), CRUD kamar (denah per lantai + tabel + slide-over), scoping pengelola.

## Fase 6 — Pengguna & Booking
Manajemen pengguna (approve/reject/deactivate/reactivate, buat akun pengelola), `BookingService` dengan row-lock, antrean booking admin dengan detail slide-over.

## Fase 7 — Kontrak & Tagihan
`LeaseService`, `InvoiceService`, `CodeGenerator`, kontrak dari booking & walk-in, terminasi, daftar tagihan admin & tenant, `routes/console.php` + 3 Artisan command terjadwal.

## Fase 8 — Pembayaran
Upload bukti (disk privat `local`), route stream terproteksi Policy, verifikasi/tolak/catat tunai, `paid_amount` & `activity_log` terupdate, pembatalan verifikasi (owner).

## Fase 9 — Dashboard, Ulasan & Laporan
`DashboardService` (owner/manager scoped) + Chart.js pendapatan 6 bulan, ulasan tenant (30 hari, edit 7 hari) + moderasi owner, ekspor CSV laporan pembayaran.

## Fase 10 — Testing, Polish & Dokumentasi
109 test otomatis (382 assertion) mencakup semua alur inti, audit responsif 375/768/1280px, README + CHANGELOG + ERD.

---

## Status requirement (F-xx)

### A. Publik
| ID | Fitur | Prioritas | Status |
|---|---|---|---|
| F-01 | Landing page | P0 | ✅ |
| F-02 | Katalog kost + filter | P0 | ✅ |
| F-03 | Detail kost | P0 | ✅ |
| F-04 | Tombol Ajukan Sewa → login/daftar | P0 | ✅ |

### B. Autentikasi & Akun
| ID | Fitur | Prioritas | Status |
|---|---|---|---|
| F-05 | Registrasi penyewa (pending) | P0 | ✅ |
| F-06 | Login, logout, lupa/reset sandi | P0 | ✅ |
| F-07 | Profil: data, foto, sandi | P0 | ✅ |
| F-08 | reCAPTCHA registrasi | P2 | ❌ |

### C. Penyewa
| ID | Fitur | Prioritas | Status |
|---|---|---|---|
| F-09 | Dashboard penyewa | P0 | ✅ |
| F-10 | Ajukan/batalkan booking | P0 | ✅ |
| F-11 | Daftar tagihan + upload bukti | P0 | ✅ |
| F-12 | Riwayat kontrak & pembayaran | P1 | ✅ |
| F-13 | Ulasan & rating | P1 | ✅ |
| F-14 | Laporan keluhan | P2 | ❌ (skema `complaints` disiapkan, UI belum) |

### D. Pengelola
| ID | Fitur | Prioritas | Status |
|---|---|---|---|
| F-15 | Dashboard gedung | P0 | ✅ |
| F-16 | Kelola kamar | P0 | ✅ |
| F-17 | Proses booking | P0 | ✅ |
| F-18 | Verifikasi/tolak pembayaran, tunai | P0 | ✅ |
| F-19 | Akhiri kontrak lebih awal | P1 | ✅ |
| F-20 | Tangani keluhan | P2 | ❌ |

### E. Pemilik
| ID | Fitur | Prioritas | Status |
|---|---|---|---|
| F-21 | Dashboard global | P0 | ✅ |
| F-22 | CRUD gedung + galeri + fasilitas + pengelola | P0 | ✅ |
| F-23 | Master fasilitas & instansi | P0 | ✅ |
| F-24 | Kelola pengguna | P0 | ✅ |
| F-25 | Semua fitur pengelola, semua gedung | P0 | ✅ |
| F-26 | Moderasi ulasan | P1 | ✅ |
| F-27 | Ekspor laporan pembayaran (CSV) | P2 | ✅ (CSV; PDF belum) |

**Ringkasan:** 24 dari 27 requirement selesai (semua P0 dan P1). 3 item P2 belum: reCAPTCHA registrasi (F-08), UI keluhan penyewa (F-14/F-20 — skema DB sudah ada), dan ekspor PDF (F-27 sudah CSV).

## Penyimpangan dari blueprint

Lihat `docs/DECISIONS.md` untuk daftar lengkap beserta alasannya. Ringkas:
- PHP 8.3 dipasang berdampingan (Laravel 11 butuh ≥8.2, lingkungan hanya punya 8.1).
- Dev & test memakai SQLite; `.env.example` tetap MySQL sesuai stack.
- Skill desain `taste-skill` dipakai sebagai pengganti `frontend-design` yang diminta prompt (tidak tersedia di lingkungan ini), prinsip sama.
- Commit & push per unit kerja kecil, bukan hanya per fase (permintaan eksplisit pemilik repo).
