# Performance Optimization — Jalur 1 (Selesaikan) + Jalur 2 (Livewire wire:navigate)
# Repo: ppmkh2sby/Web-Management-KH2

## Konteks Penting — Baca Dulu Sebelum Mulai

Repo ini adalah Laravel + Blade (MPA). Optimasi query DB dan ViewComposer
sudah sebagian besar selesai. Yang tersisa adalah:
1. **Jalur 1** — 2 perubahan config terakhir (session & cache driver)
2. **Jalur 2** — Implementasi Livewire `wire:navigate` agar navigasi sidebar
   tidak lagi melakukan full page reload

Kerjakan FASE 1 dahulu. Verifikasi hasilnya. Baru lanjut FASE 2.

---

# ═══════════════════════════════════════
# FASE 1 — Selesaikan Jalur 1 (Quick Fix)
# ═══════════════════════════════════════

## TASK 1.1 — Ubah Default Session Driver

**File: `config/session.php` baris 20**

```php
// BEFORE:
'driver' => env('SESSION_DRIVER', 'database'),

// AFTER:
'driver' => env('SESSION_DRIVER', 'file'),
```

**Alasan:** Setiap HTTP request saat ini melakukan query INSERT/UPDATE ke tabel
`sessions` di database hanya untuk menyimpan session. Driver `file` menyimpan
session ke disk lokal — jauh lebih cepat, tanpa overhead query DB sama sekali.

---

## TASK 1.2 — Ubah Default Cache Store

**File: `config/cache.php` baris 17**

```php
// BEFORE:
'default' => env('CACHE_STORE', 'database'),

// AFTER:
'default' => env('CACHE_STORE', 'file'),
```

**Alasan:** Sama seperti session — cache yang disimpan ke file disk
lebih cepat dibanding query ke tabel `cache` di database.

---

## TASK 1.3 — Tambahkan Artisan Optimization Commands

**Buat file baru: `deploy.sh` di root project**

```bash
#!/bin/bash
set -e
echo "=== PPM KH2 — Running deployment optimizations ==="

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "=== Optimization complete ==="
```

Buat file ini executable dan jalankan di server setelah setiap deploy:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## Verifikasi Setelah FASE 1

Setelah TASK 1.1–1.3 selesai, pastikan:
- [ ] `config/session.php` baris 20: default value adalah `'file'`
- [ ] `config/cache.php` baris 17: default value adalah `'file'`
- [ ] `deploy.sh` ada di root project dan bisa dieksekusi
- [ ] Jalankan `php artisan config:cache` untuk reload config
- [ ] Akses aplikasi, pastikan login dan navigasi masih berfungsi normal

---

# ═══════════════════════════════════════
# FASE 2 — Jalur 2: Livewire wire:navigate
# ═══════════════════════════════════════

## Tujuan

Setiap klik menu di sidebar saat ini menyebabkan **full page reload** —
browser mengunduh ulang seluruh HTML, parse ulang CSS, inisialisasi ulang JS.
Dengan `wire:navigate`, hanya konten tengah yang diganti. Sidebar, header,
dan asset CSS/JS **tidak pernah di-reload lagi**.

Hasil yang diharapkan: navigasi dari ~1.5s → ~100–200ms.

---

## TASK 2.1 — Install Livewire

Jalankan di terminal:
```bash
composer require livewire/livewire "^3.0"
```

Tidak perlu publish config. Tidak perlu membuat Livewire component apapun
untuk tahap ini — kita hanya menggunakan fitur `wire:navigate`.

---

## TASK 2.2 — Tambahkan Livewire Scripts ke Layout Utama

**File: `resources/views/layouts/santri-modern.blade.php`**

Tambahkan `@livewireScripts` tepat sebelum tag `</body>`:

```blade
{{-- Sebelum: --}}
</body>
</html>

{{-- Sesudah: --}}
    @livewireScripts
</body>
</html>
```

Juga tambahkan `@livewireStyles` di dalam `<head>`, tepat setelah
baris `@vite(...)`:

```blade
{{-- Sebelum: --}}
@vite(['resources/css/app.css','resources/js/app.js'])
<style>

{{-- Sesudah: --}}
@vite(['resources/css/app.css','resources/js/app.js'])
@livewireStyles
<style>
```

---

## TASK 2.3 — Tambahkan wire:navigate pada Semua Link Sidebar

**File: `resources/views/layouts/santri-modern.blade.php`**

Cari semua tag `<a href="...">` di dalam blok `<nav>` (baris 107–232)
dan tambahkan atribut `wire:navigate` pada setiap link navigasi.

Contoh pola perubahan:

```blade
{{-- BEFORE: --}}
<a href="{{ route('santri.home') }}" class="flex items-center gap-3 ...">

{{-- AFTER: --}}
<a href="{{ route('santri.home') }}" wire:navigate class="flex items-center gap-3 ...">
```

Daftar lengkap semua `<a>` di sidebar yang harus ditambahkan `wire:navigate`:

1. **Baris 143** — Link Dashboard (`route('santri.home')`)
2. **Baris 164** — Link Kehadiran Saya/Santri (`route('santri.presensi.index', ...)`)
3. **Baris 169** — Link Kafarah Saya (`route('santri.kafarah.index', ...)`)
4. **Baris 175** — Link Kehadiran Santri ketertiban (`route('santri.presensi.index', ...)`)
5. **Baris 180** — Link Rekap Presensi (`route('santri.presensi.rekap')`)
6. **Baris 185** — Link Kafarah Santri (`route('santri.kafarah.index', ...)`)
7. **Baris 194** — Link Progress Keilmuan (`route('santri.data.progres')`)
8. **Baris 215** — Link Input Keluar/Masuk (`route('santri.data.log', ...)`)
9. **Baris 219** — Link Log Saya (`route('santri.data.log', ...)`)
10. **Baris 225** — Link Log Keluar/Masuk staff (`route('santri.data.log')`)
11. **Baris 132** (wali section) — Semua `<a>` dalam loop `@foreach($waliMenu ...)` tambahkan `wire:navigate`
12. **Baris 268** — Link Profil di dropdown (`route('santri.profile')`)
13. **Baris 272** — Link Pengaturan di dropdown (`route('santri.setting')`)

**PENTING:** Jangan tambahkan `wire:navigate` pada:
- `<a href="#">` (link placeholder/kosong)
- `<form>` submit (logout) — biarkan tetap sebagai form POST
- Link yang mengarah ke domain eksternal

---

## TASK 2.4 — Tambahkan wire:navigate pada Link Brand (Logo)

**File: `resources/views/layouts/santri-modern.blade.php` baris 98**

```blade
{{-- BEFORE: --}}
<a href="{{ $brandRoute }}" class="flex items-center gap-2.5 group">

{{-- AFTER: --}}
<a href="{{ $brandRoute }}" wire:navigate class="flex items-center gap-2.5 group">
```

---

## TASK 2.5 — Handle Lucide Icons Setelah wire:navigate

Karena `wire:navigate` mengganti konten halaman tanpa full reload,
icon Lucide perlu di-reinisialisasi setiap navigasi.

**File: `resources/js/app.js`**

```js
// BEFORE:
import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// AFTER:
import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
Alpine.start();

// Inisialisasi awal
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// Reinisialisasi setelah setiap wire:navigate page swap
document.addEventListener('livewire:navigated', () => {
    createIcons({ icons });
});
```

---

## TASK 2.6 — Handle Alpine.js Setelah wire:navigate

Alpine.js perlu diinisialisasi ulang pada elemen baru setelah page swap.
Livewire 3 sudah menangani ini secara otomatis karena terintegrasi dengan Alpine.
Namun untuk memastikan, tambahkan ini juga di `app.js` setelah perubahan di TASK 2.5:

```js
// Tambahkan setelah event livewire:navigated:
document.addEventListener('livewire:navigated', () => {
    createIcons({ icons });
    // Alpine sudah di-handle otomatis oleh Livewire 3
});
```

Tidak perlu perubahan tambahan — Livewire 3 sudah built-in support untuk Alpine.

---

## TASK 2.7 — Tambahkan Loading Indicator (Opsional tapi Sangat Direkomendasikan)

Saat navigasi dengan `wire:navigate`, tambahkan visual indicator
agar user tahu halaman sedang berpindah.

**File: `resources/views/layouts/santri-modern.blade.php`**

Tambahkan di dalam `<head>`, setelah `@livewireStyles`:

```blade
@livewireStyles
{{-- Loading bar untuk wire:navigate --}}
<style>
  [wire\:loading-bar] {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981, #059669);
    z-index: 9999;
    transition: width 0.2s ease;
  }
</style>
```

Tambahkan juga elemen loading bar di dalam `<body>`, tepat setelah tag `<body>`:

```blade
<body class="bg-gray-100 text-gray-800 overflow-y-scroll antialiased">
<div wire:loading.delay wire:target="navigate"
     class="fixed top-0 left-0 right-0 h-[3px] bg-gradient-to-r
            from-emerald-400 to-emerald-600 z-[9999]"
     style="display:none;">
</div>
```

---

## TASK 2.8 — Rebuild Frontend Assets

Setelah semua perubahan JS selesai, jalankan:

```bash
npm run build
```

---

## Verifikasi Setelah FASE 2

Setelah semua task FASE 2 selesai, lakukan pengujian berikut:

### Test Navigasi
- [ ] Login sebagai santri → buka Dashboard → klik "Data Santri" → klik "Progress Keilmuan"
  → sidebar **tidak bergerak/flash** saat perpindahan halaman
- [ ] Login sebagai wali → navigasi antar tab → sidebar tetap stabil
- [ ] Login sebagai pengurus → navigasi ke semua fitur → tidak ada full reload

### Test Icon & Alpine
- [ ] Icon Lucide (search, dashboard, dll di sidebar) tetap muncul di semua halaman
- [ ] Dropdown profil (Alpine.js `x-data`) tetap berfungsi di semua halaman
- [ ] Accordion "Data Santri" dan "Log Keluar/Masuk" tetap bisa dibuka/tutup

### Test Logout
- [ ] Tombol Sign Out tetap berfungsi (form POST, bukan `wire:navigate`)

### Test Fungsionalitas
- [ ] Form di halaman presensi, kafarah, log masih bisa disubmit
- [ ] Tidak ada error JavaScript di browser console

---

## Catatan Penting untuk Code Agent

1. **JANGAN** ubah logic PHP di controller, model, atau middleware
2. **JANGAN** ubah struktur route di `routes/web.php`
3. `wire:navigate` hanya ditambahkan pada tag `<a>` — bukan `<button>` atau `<form>`
4. Jika ada halaman yang tidak menggunakan layout `santri-modern.blade.php`,
   **tidak perlu** ditambahkan `wire:navigate` — fokus hanya pada layout ini
5. Jika ada error "Class not found" saat install Livewire, jalankan:
   `composer dump-autoload`
6. Livewire 3 membutuhkan PHP >= 8.1 dan Laravel >= 10 — pastikan versi sudah sesuai

---

## Urutan Eksekusi

```
1. Kerjakan FASE 1 (TASK 1.1 → 1.2 → 1.3)
2. Jalankan: php artisan config:cache
3. Test: login dan navigasi masih normal
4. Kerjakan FASE 2 (TASK 2.1 → 2.2 → 2.3 → 2.4 → 2.5 → 2.6 → 2.7 → 2.8)
5. Jalankan: npm run build
6. Test semua checklist verifikasi di atas
```

---

## Summary Checklist Lengkap

### FASE 1
- [ ] `config/session.php` baris 20 → default diubah dari `'database'` ke `'file'`
- [ ] `config/cache.php` baris 17 → default diubah dari `'database'` ke `'file'`
- [ ] `deploy.sh` dibuat di root project

### FASE 2
- [ ] `composer require livewire/livewire "^3.0"` dijalankan
- [ ] `@livewireStyles` ditambah di `<head>` santri-modern.blade.php
- [ ] `@livewireScripts` ditambah sebelum `</body>` santri-modern.blade.php
- [ ] Semua `<a>` navigasi di sidebar ditambah `wire:navigate` (13 link)
- [ ] Link brand/logo ditambah `wire:navigate`
- [ ] `app.js` ditambah event `livewire:navigated` untuk reinit Lucide
- [ ] Loading bar indicator ditambahkan
- [ ] `npm run build` dijalankan
- [ ] Semua test verifikasi lulus