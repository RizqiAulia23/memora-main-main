# Audit Report: Memorify (Memora) Project
**Date:** August 13, 2026  
**Project:** Memorify - Couples Memory Companion  
**Framework:** Laravel 13  
**Test Status:** 87 tests passed / 290 assertions  

---

## RINGKASAN EKSEKUTIF

Proyek Memorify menunjukkan **praktik keamanan yang kuat** dengan implementasi otorisasi yang tepat, penyimpanan file yang aman, perlindungan CSRF, dan hashing password. Namun, beberapa **isu medium dan low-severity** ditemukan yang harus ditangani sebelum deployment production.

**Status Keamanan:** ✅ Tidak ada vulnerability KRITIS

---

## TEMUAN DETAIL

### 🔴 HIGH SEVERITY (2 issues)

#### 1. Missing Input Length Validation on Large Text Fields
**Lokasi:**
- `app/Http/Requests/StoreMemoryRequest.php` (line 17)
- `app/Http/Requests/UpdateMemoryRequest.php` (line 17)  
- `app/Http/Requests/StoreLoveLetterRequest.php` (line 17)
- `app/Http/Requests/UpdateLoveLetterRequest.php` (line 17)

**Masalah:**
```php
// ❌ TIDAK ADA BATAS MAKSIMAL
'description' => ['required', 'string'],
'content' => ['required', 'string'],
```

**Risiko:**
- Penyerang dapat mengirim data sangat besar → DoS
- Bloat database dan kehabisan memory server
- Tidak ada proteksi terhadap bulk data attacks

**Solusi:**
```php
// ✅ TAMBAHKAN max length validation
'description' => ['required', 'string', 'max:10000'],
'content' => ['required', 'string', 'max:50000'],
```

---

#### 2. Improper File Cleanup on Failure - Race Condition
**Lokasi:**
- `app/Services/AccountService.php` (lines 8-23)
- `app/Services/ProfileService.php` (lines 14-29)

**Masalah:**
Database dihapus **SEBELUM** file dihapus → jika file deletion gagal, file orphaned selamanya:

```php
// ❌ Files dihapus SETELAH DB deletion
$user->delete();  // DB berhasil dihapus

foreach ($images as $path) {
    Storage::disk(self::DISK)->delete($path);  // Jika gagal di sini, file orphaned
}
```

**Risiko:**
- File orphaned menumpuk di storage
- Tidak bisa rollback jika file deletion gagal
- Waste storage space

**Solusi:**
```php
// ✅ Hapus files TERLEBIH DAHULU
DB::transaction(function () use ($user, $images) {
    foreach ($images as $path) {
        Storage::disk(self::DISK)->delete($path);  // Delete DULU
    }
    $user->delete();  // DB TERAKHIR
});
```

---

### 🟡 MEDIUM SEVERITY (6 issues)

#### 1. Form Requests Don't Enforce Authorization
**Lokasi:** Semua 9 Form Requests di `app/Http/Requests/`

**Masalah:**
```php
// ❌ Authorization tidak enforce di request level
public function authorize(): bool
{
    return true;  // Selalu allow
}
```

**Risiko:**
- Otorisasi hanya di controller (manual), mudah dilupakan
- Tidak follow Laravel's defense-in-depth pattern

**Solusi:**
```php
// ✅ Implement proper authorization
public function authorize(): bool
{
    return auth()->check();  // Untuk create
    // return $this->user()->can('update', $this->memory);  // Untuk update
}
```

---

#### 2. N+1 Query Risk: Dashboard Sidebar Memory Count
**Lokasi:** `app/Providers/AppServiceProvider.php` (line 39)

**Masalah:**
```php
// ❌ Jalan di SETIAP view rendering
View::composer('partials.dashboard-sidebar', function ($view) {
    $memoryCount = app(DashboardService::class)->stats($user)['total_memories'];
    // stats() = 5 database queries!
});
```

**Risiko:**
- Jika sidebar di 10 halaman → 50+ queries per request
- Database overload, performance degradation
- Cache hanya 5 menit server-side

**Solusi:**
```php
// ✅ Cache per request atau load hanya di dashboard
$memoryCount = cache()->remember(
    'sidebar_memory_count_' . $user->id,
    now()->addMinutes(1),
    fn () => $user->memories()->count()
);
```

---

#### 3. RichTextSanitizer: Potential XSS Edge Cases
**Lokasi:** `app/Services/RichTextSanitizer.php` (lines 13-31)

**Masalah:**
Custom regex sanitization bisa ketinggalan XSS vectors:

```php
// ❌ Custom regex untuk sanitasi
preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
```

**Risiko:**
- Regex tidak catch semua XSS (encoding, data URIs, vbscript)
- Tidak gunakan library yang established (HTMLPurifier)
- Rawan XSS jika ada edge case

**Solusi:**
```php
// ✅ Gunakan library yang matang
use HtmlSanitizer\Sanitizer;

$purifier = new \HTMLPurifier();
return $purifier->purify($html);
```

---

#### 4. Missing Type Hints and Policy Authorization
**Lokasi:** `app/Http/Controllers/ProfileController.php` (line 53)

**Masalah:**
```php
// ❌ Manual authorization check
public function avatar(User $user)  
{
    abort_unless(auth()->id() === $user->id, 403);
    abort_if(! $user->avatar, 404);
}
```

**Risiko:**
- Authorization tidak enforce di framework level
- Mudah lupa di method baru

**Solusi:**
```php
// ✅ Gunakan policy authorization
public function avatar(User $user): Response
{
    $this->authorize('view', $user);  // Policy check
    abort_if(! $user->avatar, 404);
    return Storage::disk('private')->response($user->avatar);
}
```

---

#### 5. Inefficient PHP Grouping in TimelineController
**Lokasi:** `app/Http/Controllers/TimelineController.php` (lines 13-28)

**Masalah:**
```php
// ❌ Load SEMUA memories ke memory, group di PHP
$memories = $user->memories()
    ->orderBy('memory_date')
    ->get(['id', 'title', 'image', 'memory_date']);  // ALL ROWS!

$years = $memories
    ->groupBy(fn ($memory) => $memory->memory_date->year)  // PHP grouping
```

**Risiko:**
- Power users dengan 10,000+ memories → OOM error
- Database seharusnya group bukan PHP
- Tidak ada pagination

**Solusi:**
```php
// ✅ Gunakan database grouping
$years = $user->memories()
    ->selectRaw('YEAR(memory_date) as year')
    ->distinct()
    ->orderByDesc('year')
    ->pluck('year');
```

---

#### 6. No Maximum Limit on API Response Count
**Lokasi:** `app/Http/Controllers/FavoriteController.php` (line 31)

**Masalah:**
```php
// ❌ COUNT query setiap toggle favorite
'favoritesCount' => $user->favorites()->count(),
```

**Risiko:**
- Expensive COUNT query setiap kali
- Performance degradation saat banyak favorites

**Solusi:**
```php
// ✅ Cache count atau jangan expose
'favoritesCount' => cache()->remember(
    'user_favorites_count_' . $user->id,
    now()->addMinutes(5),
    fn () => $user->favorites()->count()
),
```

---

### 🟠 LOW SEVERITY (5 issues)

#### 1. Unhandled Exception Logging in CalendarController
**Lokasi:** `app/Http/Controllers/CalendarController.php` (lines 17-26)

**Masalah:**
```php
// ❌ Exception caught tanpa logging
try {
    $parsed = Carbon::createFromFormat('Y-m', ...);
} catch (\Throwable) {
    // Silent ignore - tidak ada visibility
}
```

**Solusi:**
```php
// ✅ Log untuk debugging
catch (\Throwable $e) {
    \Log::warning('Invalid month parameter: ' . $request->query('month'));
}
```

---

#### 2. Incomplete Error Handling in AccountService
**Lokasi:** `app/Services/AccountService.php` (line 17)

**Masalah:**
Jika satu file gagal dihapus, file lainnya tidak dicoba

**Solusi:**
```php
// ✅ Log failed deletions tapi continue
foreach ($images as $path) {
    try {
        Storage::disk(self::DISK)->delete($path);
    } catch (\Throwable $e) {
        \Log::warning("Failed to delete: $path");
    }
}
```

---

#### 3. Missing Pagination Input Validation
**Lokasi:** `app/Http/Controllers/GalleryController.php` (line 15)

**Masalah:**
```php
// ❌ Page parameter tidak validated
$photos = $request->user()->memories()
    ->withImage()
    ->paginate(12);
```

**Solusi:**
```php
// ✅ Validate page parameter
$page = max(1, min($request->integer('page', 1), 999));
$photos = $request->user()->memories()
    ->withImage()
    ->paginate(12, ['*'], 'page', $page);
```

---

#### 4. Missing Date Range Validation
**Lokasi:** 
- `app/Http/Requests/StoreMemoryRequest.php` (line 19)
- `app/Http/Requests/UpdateMemoryRequest.php` (line 19)

**Masalah:**
```php
// ❌ Bisa input tanggal 2999 atau 1800
'memory_date' => ['required', 'date'],
```

**Solusi:**
```php
// ✅ Add date constraints
'memory_date' => ['required', 'date', 'before_or_equal:today', 'after:1900-01-01'],
```

---

#### 5. No CORS Headers Configuration Review
**Lokasi:** Tidak ada CORS middleware found

**Catatan:**
Jika API di-expose ke external origins di masa depan, pastikan proper CORS handling di `config/cors.php`

---

## TINDAKAN YANG DIREKOMENDASIKAN

### URGENT (Sebelum Production)
1. ✅ Tambah max length validation ke semua text fields
2. ✅ Perbaiki file cleanup order (delete files dulu, DB terakhir)
3. ✅ Implement proper authorization di Form Requests

### SOON (Sprint Berikutnya)
1. 📌 Cache dashboard sidebar stats
2. 📌 Perbaiki timeline grouping (database bukan PHP)
3. 📌 Ganti custom sanitizer dengan HTMLPurifier
4. 📌 Implement policy authorization

### NICE-TO-HAVE
1. 📝 Add logging untuk silent exceptions
2. 📝 Validate date ranges dan pagination parameters

---

## TEST COVERAGE

✅ Project memiliki 87 passing tests dengan 290 assertions - good foundation!  
Rekomendasi: Tambah test coverage untuk:
- Form validation edge cases
- Authorization policies
- File deletion scenarios

---

## KESIMPULAN

**Security Grade:** B+ (Good)  
Memorify adalah aplikasi yang well-structured dengan foundation security yang baik. Dengan perbaikan ini, akan mencapai production-ready grade.

**Estimasi Perbaikan:** 4-6 jam development work untuk resolve semua HIGH dan MEDIUM severity issues.
