# Kost Antik · Sistem Desain

Ringkasan keputusan visual. Semua nilai hidup di `tailwind.config.js` dan `resources/css/app.css`; Blade tidak boleh memakai warna hex langsung.

## 1. Pembacaan brief

Platform pemasaran dan manajemen kost untuk mahasiswa dan pekerja di Bekasi–Cikarang. Dua wajah:

| Permukaan | Karakter | Variance / Motion / Density |
|---|---|---|
| Publik & penyewa | Ekspresif, foto besar, tipografi berkarakter | 6 / 4 / 4 |
| Admin | Tenang, padat, fokus tabel & status | 3 / 2 / 7 |

Identitas diambil dari rumah lama Indonesia: **tegel kunci** (ubin semen bermotif), **kayu jati**, dan **kuningan**. Targetnya hangat tetapi rapi, bukan vintage kusam.

## 2. Palet

Kami sengaja menghindari klise "latar krem + serif + aksen terakota". Tegel kunci yang asli justru didominasi **hijau botol / hijau lumut tua** di atas semen abu-abu. Warna itu yang menjadi warna utama.

| Token | Hex | Peran |
|---|---|---|
| `tegel-700` | `#0F4D48` | Warna brand. Tombol utama, link aktif, sidebar admin |
| `tegel-800` / `900` | `#0B3B37` / `#082B28` | Permukaan gelap (hero, footer, panel auth) |
| `tegel-50` / `100` | `#E8F1EF` / `#CFE3DF` | Latar info, hover baris, sel terpilih |
| `kapur-50` | `#F6F7F5` | Latar halaman. Abu semen dingin, **bukan krem** |
| `kapur-200` | `#DDE1DD` | Garis & border |
| `ink-900` | `#14201F` | Teks utama (kontras 15.8:1 di kapur-50) |
| `ink-500` | `#56625F` | Teks sekunder (kontras 5.9:1, lolos AA) |
| `kuningan-500` | `#C39A3F` | Aksen tunggal: bintang rating, garis aksen, detail motif. **Tidak untuk teks di latar terang** |
| `kuningan-700` | `#7F6120` | Varian kuningan yang aman untuk teks (5.4:1) |
| `jati-600` | `#7A4E2D` | Aksen hangat sekunder, dipakai hanya di ilustrasi/motif |

Kuningan hanya dipakai sebagai aksen kecil (≤ 5% permukaan), seperti gagang pintu kuningan di rumah lama: kecil, tapi yang pertama tertangkap mata.

### Warna status (konsisten di seluruh aplikasi)

| Kelompok | Status | Token |
|---|---|---|
| Sukses | available, paid, verified, active, approved, accepted | `success` (hijau daun `#2F7A4B`, dibedakan dari hijau tegel) |
| Peringatan | pending, pending_verification | `warning` (`#8A5A00` di atas `#FCF1D8`) |
| Bahaya | overdue, rejected, terminated | `danger` (`#B23A2A` di atas `#FBE7E3`) |
| Info (hijau tegel) | occupied, unpaid | `info` (`tegel-700` di atas `tegel-50`) |
| Netral | maintenance, void, cancelled, expired, completed, inactive | `neutral` (`#56625F` di atas `#EDEFEC`) |

Badge selalu berisi **teks + ikon**, jadi status tidak bergantung pada warna saja (WCAG 1.4.1). Pemetaan ada di satu tempat: method `color()` pada tiap Enum, lalu dipakai oleh komponen `<x-status-badge>`.

## 3. Tipografi

| Peran | Font | Alasan |
|---|---|---|
| Display (judul, angka besar) | **Bricolage Grotesque** (variable) | Grotesk dengan sedikit "kaku tangan" di terminal hurufnya, terasa buatan tangan tanpa jatuh ke serif |
| Teks & UI | **Plus Jakarta Sans** (variable) | Didesain di Jakarta untuk identitas kota; rapi di ukuran kecil dan terbaca baik untuk tabel admin |

Keduanya di-*self-host* lewat `@fontsource-variable` (tanpa request ke Google Fonts). Angka uang dan tanggal memakai `tabular-nums`.

Skala: `text-xs 12` · `sm 14` · `base 16` · `lg 18` · `xl 20` · `2xl 24` · `3xl 30` · `4xl 36` · `5xl 48` · `6xl 60`. Judul hero maksimal `text-6xl`, admin maksimal `text-2xl`.

## 4. Bentuk, jarak, bayangan

Satu aturan radius yang ditaati di mana pun:

- **Tombol, input, select:** `rounded-lg` (8px)
- **Kartu, panel, modal, gambar:** `rounded-xl` (12px). Gambar hero boleh `rounded-2xl` (16px)
- **Badge & chip filter:** `rounded-full`

Bayangan diwarnai hijau tegel (`shadow-tile`, `shadow-lift`), tidak pernah hitam murni. Admin hampir tidak memakai bayangan; pemisah cukup `border` kapur-200.

## 5. Motif tegel kunci

Motif dibuat sebagai SVG pattern geometris sederhana (satu ubin 40×40: lingkaran seperempat di tiap sudut + belah ketupat di tengah; bila diulang, empat sudut bertemu membentuk bunga "kunci"). Tersedia sebagai:

- `bg-tegel` / `bg-tegel-light`: utilitas CSS (data-URI) untuk latar hero, panel auth, dan footer, dengan opasitas rendah (6–12%)
- `<x-tegel-divider>`: strip ubin tipis 12px sebagai pemisah antarsection di halaman publik
- `<x-empty-state>`: satu ubin besar sebagai ilustrasi kosong
- Placeholder foto kost: SVG berwarna (tegel, jati, kuningan) di `public/images/placeholders/` sehingga tidak ada gambar eksternal yang di-*hotlink*

Motif **tidak** dipakai di dalam tabel admin atau di belakang teks panjang.

## 6. Ikon

Lucide lewat `mallardduck/blade-lucide-icons` (SVG dirender di server, tanpa kedip saat load). Stroke 1.75, ukuran 16/20/24. Fasilitas menyimpan nama ikon Lucide di kolom `icon`.

## 7. Gerak

Transisi CSS 150–250ms `cubic-bezier(0.16,1,0.3,1)` untuk hover, dropdown, slide-over, toast. Tombol `active:translate-y-px`. Tidak ada animasi terus-menerus. Semua dimatikan di bawah `prefers-reduced-motion: reduce`.

## 8. Mode gelap

Tidak dikerjakan di MVP (lihat `DECISIONS.md` D-06). Skema satu tema terang dikunci di seluruh halaman.

## 9. Komponen Blade

`button` (primary/secondary/ghost/danger) · `input` · `select` · `textarea` · `file-upload` (preview) · `status-badge` · `stat-card` · `table` (menjadi kartu di mobile) · `modal` · `confirm-modal` · `toast` · `empty-state` · `pagination` · `breadcrumb` · `tabs` · `dropdown` · `money` · `slide-over`. Semua terlihat di `/_styleguide` (hanya env `local`).

## 10. Aturan copy

Bahasa Indonesia santai-sopan ("kamu"), spesifik dunia kost ("kamar", "penghuni", "tagihan bulan ini", "bukti transfer"). Tanpa em-dash di teks antarmuka. Label tombol maksimal 3 kata.
