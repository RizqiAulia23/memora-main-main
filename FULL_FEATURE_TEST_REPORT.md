# FULL FEATURE TEST REPORT — Memorify

- **Tanggal:** 13 Agustus 2026
- **Lingkungan:** Laravel 13, SQLite, PHP `artisan serve` @ `127.0.0.1:8123` (satu instance), Playwright (Chromium), Windows
- **Ruang lingkup:** Verifikasi end-to-end seluruh fitur via browser (Playwright), audit keamanan/storage/database/deployment, dan verifikasi otomatis (`php artisan test` + `pint --test`)
- **Batasan:** Tidak ada kode aplikasi/CSS/JS/migration/config yang diubah; hanya file test baru yang ditambahkan. Pint dijalankan `--test` tanpa auto-fix.

---

## 1. Executive Summary

| Area | Status |
|---|---|
| Suite otomatis (`php artisan test`) | **180/181 PASS** (1 gagal = test bukti bug, lihat BUG-001) |
| `vendor\bin\pint --test` | **PASS** |
| Verifikasi browser semua fitur (Playwright) | **PASS** |
| Audit database integrity | **PASS** (0 orphan, 0 duplikat, foreign key 0 pelanggaran) |
| Audit storage security | **PASS** (semua upload ke disk `private`, tidak ada symlink public) |
| Audit deployment readiness | **PASS** (tidak ada secret/DB ter-track) |
| Bug ditemukan | **1 bug aplikasi (BUG-001, MEDIUM)** + 2 temuan minor |

Kesimpulan: Aplikasi secara fungsional sehat dan aman. Satu bug nyata ditemukan di halaman Settings (kunjungan pertama) yang menyebabkan form tampil tanpa nilai default dan *silent failure* saat menyimpan tanpa mengubah tema.

---

## 2. Hasil Run Otomatis

### `php artisan test` (final)
```
Tests:  181
Passed: 180
Failed:  1  (intentional — FullSettingsFirstVisitTest::test_first_settings_visit_renders_checked_theme_radio)
Assertions: 613
Duration: ~32s
```
Test yang gagal adalah **bukti terotomatisasi BUG-001** dan sengaja dibiarkan gagal sampai bug diperbaiki (lihat §5).

### `vendor\bin\pint --test` (final)
```
PASSED
```
5 file test baru sempat melanggar style (unused import, spasi operator, EOF); diperbaiki manual mengikuti konvensi Pint tanpa menjalankan auto-fix pada repo.

---

## 3. Verifikasi Browser (Playwright) — Semua PASS

### 3.1 Auth
- Register user baru → redirect ke dashboard → **flash sukses tampil** di halaman tujuan. ✅
- Login/logout, akses halaman terproteksi tanpa sesi → redirect ke `/login`. ✅

### 3.2 Memories (CRUD)
- Create via UI (judul, deskripsi, tanggal, konten, upload gambar) → tampil di dashboard; memory text-only (tanpa gambar) juga tersimpan. ✅
- Edit via halaman show (tombol "Edit" → "Update Memory"). ✅
- Validasi server-side (judul/deskripsi wajib, tipe file, ukuran >2.5MB) diverifikasi di test otomatis. ✅

### 3.3 Gallery & Lightbox
- Gallery menampilkan **hanya** memory ber-gambar (3 item; text-only dieksklusi). ✅
- Lightbox: buka klik gambar, tombol next/prev, tutup via X, overlay click, dan tombol **Escape**. ✅
- Link "View Memory" di lightbox → halaman `/memories/{id}` (by design, bukan download). ✅
- **Regression OK:** klik tombol download di gallery **tidak** memicu lightbox. ✅

### 3.4 Calendar
- Hari dengan memory ditandai class `.has-memory` + ikon hati (`.cal-day-heart`) pada `2024-02-15`. ✅
- Navigasi bulan (prev/next `a.cal-nav`), klik hari → panel detail (`.cal-details`) ter-load via AJAX. ✅
- Bulan kosong (2020-01): 0 penanda + pesan kosong. ✅

### 3.5 Timeline
- Grup per bulan Januari–April 2024 benar, urutan benar. ✅

### 3.6 Favorites
- Toggle AJAX `[data-favorite-toggle]`: `active` class sebelum=false → setelah=true → untoggle=false. ✅
- Halaman Favorites berisi memory yang ditandai; toggle di halaman Favorites konsisten. ✅
- DB: 6 row favorites, 0 duplikat, 0 orphan. ✅

### 3.7 Love Letters
- Create via RTE (`[data-rte-editor]`), mood valid (`happy|love|romantic|nostalgic|grateful|thoughtful|missing` — diuji 'romantic'), tombol "Send Letter". ✅

### 3.8 Global Search
- Instant search `[data-global-search-input]` → hasil di `[data-global-search-results]`. ✅
- Catatan: hasil muncul setelah debounce (~700ms); menunggu ≥1 detik selalu konsisten (bukan bug).

### 3.9 Profile
- Bio `textarea[name="bio"]` tersimpan, tombol "Save Profile". ✅

### 3.10 Settings (untuk user dengan settings sudah ada)
- Radio theme tampil `checked` sesuai nilai DB; save persisten setelah reload; checkbox notifikasi ter-toggle; **flash sukses tampil**. ✅
- **Khusus kunjungan pertama → BUG-001** (lihat §5).

### 3.11 Flash Messages
- Flash setelah redirect **terbukti bekerja** (register, memory create, settings update): 3/3 test (`TempFlashProbeTest`) PASS + diverifikasi di browser DOM.

---

## 4. Audit Keamanan & Data

### Storage
- Semua upload (memory images, avatar) ke disk **`private`** (`ImageStore::DISK='private'`). ✅
- 7 file upload berada di `storage/app/private/memories/`; **tidak ada** symlink `public/storage`; tidak ada kode menulis ke disk public. ✅
- File diserve hanya via route ter-autoritasi (`memories/{memory}/image`, `users/{user}/avatar`) yang memvalidasi kepemilikan. ✅

### Database
- `migrate:status`: semua 11 migrasi `Ran`. ✅
- 0 orphan di memories/letters/favorites/settings; 0 duplikat settings per user; 0 duplikat email. ✅
- `PRAGMA foreign_key_check` = 0 pelanggaran. ✅

### Git / Deployment
- `.gitignore` menutup `.env`, `database/*.sqlite`, storage; hanya `.env.example` ter-track (APP_KEY kosong, tanpa secret nyata). ✅
- `.env` lokal: `APP_ENV=local`, `APP_DEBUG=true`, kredensial mail/AWS placeholder. ✅
- Tidak ada sqlite DB/secret/upload ter-track di git. ✅

---

## 5. BUG-001 — Settings: kunjungan pertama tidak menampilkan nilai default → save gagal diam-diam (MEDIUM)

| | |
|---|---|
| **File** | `app/Models/User.php:72-75` (`getSettings()`) |
| **Severity** | **MEDIUM** |
| **Status** | Terbukti (Playwright + curl + test gagal + tinker), **belum diperbaiki** |

**Akar masalah:**
```php
return $this->settings ?: $this->settings()->create(['user_id' => $this->id]);
```
Pada kunjungan pertama ke `/settings`, `create()` menyimpan row dengan nilai default kolom DB (`theme='light'`, `notifications_enabled=1`), tetapi **model in-memory yang dikembalikan memiliki `theme=null`** (default SQLite tidak direfleksikan ke model). Akibatnya blade:
```blade
$settings->theme === 'light' ? 'checked' : ''
```
merender **tanpa radio ter-check** dan tanpa class `selected`; checkbox notifikasi juga unchecked.

**Dampak:**
1. Form tampil tanpa nilai default (radio & checkbox kosong) pada kunjungan pertama.
2. User menekan "Save Settings" tanpa menyentuh tema → POST body tanpa `theme` (terbukti: `_token=...&_method=PUT&notifications_enabled=1`) → validasi `UpdateSettingsRequest` (`theme` required) gagal → error "The theme field is required." → **tidak ada yang tersimpan, tidak ada flash sukses** (silent failure).

**Bukti:**
- Playwright: DOM pertama kali tanpa `checked`; POST body tanpa `theme`; tidak ada flash setelah save.
- curl: respon PUT berisi teks error validasi, tidak ada redirect sukses.
- Test bukti: `tests/Feature/FullSettingsFirstVisitTest::test_first_settings_visit_renders_checked_theme_radio` **GAGAL** (assert `value="light" checked` tidak ada). Test kedua (save dengan theme dipilih) **PASS** — membuktikan save bekerja bila nilai hadir.
- `php artisan tinker`: model hasil `getSettings()` pertama → `theme=null`, sedangkan row DB → `'light'`.

**Rekomendasi perbaikan (belum diterapkan — menunggu persetujuan):**
- Setelah `create()`, isi default ke model sebelum return, mis. `$settings->fill(['theme' => 'light', 'notifications_enabled' => true])` atau `return $this->settings()->firstOrCreate([...], [...])` — versi teraman tanpa mengubah skema DB.
- Keputusan user sebelumnya: fix **"hanya teks peringatan Save"** (tanpa mengubah `settings.js`) — masih pending sampai testing selesai.

---

## 6. Temuan Lainnya

### 6.1 (Lingkungan, bukan bug kode) Dual `php artisan serve` di port sama
- Ditemukan **dua+ proses `php artisan serve`** terikat ke port 8123 yang sama (SO_REUSEADDR di Windows). Request terdistribusi antar server dengan state berbeda → rendering inkonsisten intermiten (sebelumnya sempat menyerupai "flash hilang").
- Solusi: matikan semua proses PHP, jalankan satu instance. Bukan bug aplikasi; jangan jalankan beberapa server di port yang sama saat debugging.

### 6.2 (LOW) Memory image hilang di disk → HTTP 500
- `GET /memories/{id}/image` (authorized) saat file tidak ada di disk → **500** (harusnya 404). Avatar yang hilang sudah benar → **404**. Verifikasi: curl authorized `missing-image=500`, `missing-avatar=404`; unauthorized keduanya 302 → login (aman).

### 6.3 (LOW) `/login` & `/register` tetap terbuka untuk user terautentikasi
- User login yang mengakses `/login` melihat halaman login (belum di-redirect ke dashboard). Bukan risiko keamanan, hanya UX. Tidak ada middleware guest yang dipasang di `bootstrap/app.php`.

### 6.4 (Test-only artifact, bukan bug)
- Test kedua `FullSettingsFirstVisitTest` sempat 500 `UNIQUE constraint` karena instance user di-reuse antar request simulasi (artefak test harness Laravel, bukan produksi — HTTP asli memakai instance baru per request). Diperbaiki dengan `$user->refresh()`.

---

## 7. Daftar File Test Baru

| File | Isi | Hasil |
|---|---|---|
| `tests/Feature/FullSettingsFirstVisitTest.php` | Regression BUG-001: (1) first visit render checked radio [GAGAL = bukti], (2) save dengan theme [PASS] | 1 FAIL / 1 PASS |
| `tests/Feature/TempFlashProbeTest.php` | Probe flash register/memory-create/settings-update | 3/3 PASS |
| `tests/Feature/Full*Test.php` (14 file, ~63 test) | CRUD, validasi, pagination, security/integrity, performa N+1, boundary | Semua PASS |

---

## 8. Kesimpulan & Rekomendasi

1. **Satu bug harus diperbaiki:** BUG-001 (MEDIUM) — Settings kunjungan pertama (silent failure). Fix kecil di `User::getSettings()`.
2. Perbaikan minor opsional: 404 untuk memory image hilang (6.2), redirect `/login`/`/register` untuk user login (6.3).
3. Setelah BUG-001 diperbaiki, jalankan ulang suite — test bukti harus berubah menjadi PASS dan total menjadi 181/181.
4. Dokumentasi lingkungan: jangan jalankan banyak `php artisan serve` pada port yang sama (6.1).
