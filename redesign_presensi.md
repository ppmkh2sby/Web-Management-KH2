# Task: Redesign Fitur Presensi — Tambah Konsep Sesi/Pertemuan

## Konteks Proyek
- Framework: **Laravel** (PHP)
- Repo: `ppmkh2sby/Web-Management-KH2`
- Fitur yang diubah: **Presensi** (bukan fitur Kehadiran/Kafarah/Log)

## Struktur yang Sudah Ada (JANGAN DIHAPUS)
```
app/Models/Presensi.php         → Model presensi (santri_id, kegiatan_id, status, waktu, created_at)
app/Models/Kegiatan.php         → Kategori kegiatan (asrama/sambung/keakraban) + waktu (subuh/pagi/siang/sore/malam)
app/Models/Kelas.php            → Model kelas (id, nama)
app/Models/Santri.php           → Santri (id, nama_lengkap, kelas_id, gender, tim)
app/Http/Controllers/Santri/PresensiController.php
app/Policies/PresensiPolicy.php
resources/views/santri/presensi/index.blade.php
resources/views/santri/presensi/create.blade.php
resources/views/santri/presensi/show.blade.php
```

---

## Masalah yang Harus Diselesaikan

### Masalah 1 — Tidak Ada Konsep "Sesi Pertemuan"
Saat ini `total_pertemuan` dihitung dari jumlah record `presensis` milik santri itu sendiri.
Akibatnya jika ada santri yang tidak diinput pada suatu sesi, `total_pertemuan`-nya lebih kecil
dari santri lain → **persentase kehadiran tidak bisa dibandingkan secara adil**.

### Masalah 2 — Penggabungan Kelas Tidak Tertangani
Ketika 3 kelas digabung menjadi 1 sesi, sistem saat ini tidak punya cara untuk mencatat
bahwa santri dari kelas A, B, C semuanya mengikuti **1 sesi yang sama**.
Akibatnya rekap bulanan per santri bisa memiliki `total_pertemuan` yang berbeda-beda
padahal seharusnya sama.

### Masalah 3 — Santri Yang Tidak Diinput Di-skip
Di `PresensiController@store`, ada `if (! $status) { continue; }`.
Santri yang tidak dipilih statusnya tidak mendapat record sama sekali.

---

## Yang Harus Dibuat / Diubah

### 1. Migration: Tabel `sesi`
Buat file migration baru: `database/migrations/YYYY_MM_DD_create_sesi_table.php`

```php
Schema::create('sesi', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
    $table->date('tanggal');
    $table->string('catatan')->nullable();
    $table->timestamps();
});
```

### 2. Migration: Tabel Pivot `sesi_kelas`
Buat file migration baru: `database/migrations/YYYY_MM_DD_create_sesi_kelas_table.php`

```php
Schema::create('sesi_kelas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sesi_id')->constrained('sesi')->cascadeOnDelete();
    $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
    $table->timestamps();
});
```

### 3. Migration: Tambah Kolom `sesi_id` ke Tabel `presensis`
Buat file migration baru: `database/migrations/YYYY_MM_DD_add_sesi_id_to_presensis_table.php`

```php
Schema::table('presensis', function (Blueprint $table) {
    $table->foreignId('sesi_id')
          ->nullable()
          ->after('kegiatan_id')
          ->constrained('sesi')
          ->nullOnDelete();
});
```

---

### 4. Model Baru: `app/Models/Sesi.php`

Buat model baru dengan relasi:
- `belongsTo Kegiatan`
- `belongsToMany Kelas` (via pivot `sesi_kelas`)
- `hasMany Presensi`
- Method helper `santriTerdaftar()` → query santri dari semua kelas yang terdaftar di sesi ini

```php
public function santriTerdaftar(): \Illuminate\Database\Eloquent\Builder
{
    $kelasIds = $this->kelas()->pluck('kelas.id');
    return \App\Models\Santri::whereIn('kelas_id', $kelasIds);
}
```

---

### 5. Update Model `app/Models/Presensi.php`
Tambahkan relasi baru:
```php
public function sesi(): BelongsTo
{
    return $this->belongsTo(Sesi::class);
}
```

---

### 6. Update Model `app/Models/Kegiatan.php`
Tambahkan relasi baru:
```php
public function sesis(): HasMany
{
    return $this->hasMany(Sesi::class);
}
```

---

### 7. Update `PresensiController@create`

Ubah agar form input presensi menerima:
- `kelas_ids[]` — multi-select kelas yang ikut sesi ini (untuk mendukung penggabungan kelas)
- Daftar santri yang ditampilkan = semua santri dari `kelas_ids` yang dipilih
- Untuk Ketertiban: pilih berdasarkan gender (putra/putri) tetap seperti sekarang, tapi tidak pakai kelas
- Untuk Dewan Guru: pilih dari kelas yang diampu (sudah ada), tambahkan opsi multi-select kelas gabungan

---

### 8. Update `PresensiController@store` — LOGIKA INTI

Ubah total logika penyimpanan:

```
1. Validasi input (kategori, waktu, tanggal, kelas_ids[], presensi[santri_id => status])
2. firstOrCreate Kegiatan berdasarkan (kategori + waktu)
3. CREATE 1 record Sesi: { kegiatan_id, tanggal, catatan }
4. Attach kelas ke sesi: $sesi->kelas()->sync($data['kelas_ids'])
5. Ambil SEMUA santri dari kelas yang terdaftar di sesi
6. Loop tiap santri:
   - Ambil status dari input: $data['presensi'][$santri->id] ?? 'alpha'
   - JANGAN skip santri yang tidak dipilih → default ke 'alpha'
   - Simpan record Presensi dengan sesi_id terisi
7. Return redirect dengan pesan sukses
```

Khusus untuk Ketertiban yang input berdasarkan gender (bukan kelas): tetap gunakan logika lama
(loop semua santri gender terpilih), tapi tetap buat record Sesi dengan `kelas_ids = []` atau
tandai sebagai sesi "ketertiban" tanpa filter kelas. Default alpha tetap berlaku.

---

### 9. Update `PresensiController@index` — Kalkulasi Stats

Ubah kalkulasi `total_pertemuan` di mode `mine`:

```php
// SEBELUM (SALAH):
$stats['total_pertemuan'] = Presensi::where('santri_id', $santriId)->count();

// SESUDAH (BENAR):
// Hitung dari jumlah Sesi yang kelas-nya mencakup kelas santri ini
$stats['total_pertemuan'] = \App\Models\Sesi::whereHas('kelas', function ($q) use ($santri) {
    $q->where('kelas.id', $santri->kelas_id);
})->count();

// Untuk filter bulan tertentu:
$stats['total_pertemuan_bulan_ini'] = \App\Models\Sesi::whereHas('kelas', function ($q) use ($santri) {
    $q->where('kelas.id', $santri->kelas_id);
})
->whereYear('tanggal', now()->year)
->whereMonth('tanggal', now()->month)
->count();
```

---

### 10. Buat `StorePresensiRequest` (Update Validasi)

Update rules validasi di `app/Http/Requests/StorePresensiRequest.php`:

```php
public function rules(): array
{
    return [
        'kategori'     => ['required', 'string', Rule::in(\App\Models\Kegiatan::KATEGORI)],
        'waktu'        => ['required', 'string', Rule::in(\App\Models\Kegiatan::WAKTU)],
        'tanggal'      => ['required', 'date'],
        'catatan'      => ['nullable', 'string', 'max:500'],

        // kelas_ids: opsional untuk ketertiban (bisa kosong), wajib untuk degur
        'kelas_ids'    => ['nullable', 'array'],
        'kelas_ids.*'  => ['integer', 'exists:kelas,id'],

        // presensi: array [santri_id => status]
        'presensi'     => ['nullable', 'array'],
        'presensi.*'   => ['nullable', 'string', Rule::in(\App\Models\Presensi::STATUS)],
    ];
}
```

---

### 11. Buat Service/Helper: Kalkulasi Rekap Bulanan

Buat file baru `app/Services/PresensiRekapService.php`:

```php
<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Santri;
use App\Models\Sesi;

class PresensiRekapService
{
    /**
     * Rekap kehadiran santri dalam 1 bulan.
     * total_sesi dihitung dari Sesi yang kelas-nya mencakup kelas santri.
     */
    public function rekapBulanan(Santri $santri, int $bulan, int $tahun): array
    {
        $baseQuery = Sesi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan);

        if ($santri->kelas_id) {
            $baseQuery->whereHas('kelas', fn($q) => $q->where('kelas.id', $santri->kelas_id));
        }

        $totalSesi = (clone $baseQuery)->count();

        $presensiBulan = Presensi::where('santri_id', $santri->id)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan);

        $hadir = (clone $presensiBulan)->where('status', 'hadir')->count();
        $izin  = (clone $presensiBulan)->where('status', 'izin')->count();
        $sakit = (clone $presensiBulan)->where('status', 'sakit')->count();
        $alpha = (clone $presensiBulan)->where('status', 'alpha')->count();

        return [
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'total_sesi'  => $totalSesi,
            'hadir'       => $hadir,
            'izin'        => $izin,
            'sakit'       => $sakit,
            'alpha'       => $alpha,
            'persentase'  => $totalSesi > 0 ? round($hadir / $totalSesi * 100) : 0,
        ];
    }

    /**
     * Rekap per kegiatan spesifik (misal: sambung subuh bulan ini).
     */
    public function rekapPerKegiatan(Santri $santri, string $kategori, string $waktu, int $bulan, int $tahun): array
    {
        $baseQuery = Sesi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereHas('kegiatan', fn($q) => $q->where('kategori', $kategori)->where('waktu', $waktu));

        if ($santri->kelas_id) {
            $baseQuery->whereHas('kelas', fn($q) => $q->where('kelas.id', $santri->kelas_id));
        }

        $totalSesi = (clone $baseQuery)->count();

        $hadir = Presensi::where('santri_id', $santri->id)
            ->whereHas('sesi', fn($q) => $q
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->whereHas('kegiatan', fn($kq) => $kq->where('kategori', $kategori)->where('waktu', $waktu))
            )
            ->where('status', 'hadir')
            ->count();

        return [
            'kegiatan'   => "$kategori ($waktu)",
            'total_sesi' => $totalSesi,
            'hadir'      => $hadir,
            'persentase' => $totalSesi > 0 ? round($hadir / $totalSesi * 100) : 0,
        ];
    }
}
```

---

## Catatan Penting untuk Code Agent

1. **JANGAN ubah** logika Policy (`PresensiPolicy.php`) — hak akses tidak berubah
2. **JANGAN hapus** kolom `kegiatan_id` di tabel `presensis` — tetap ada sebagai denormalisasi
3. **Semua migration** harus bisa di-rollback (implementasi `down()` yang benar)
4. **Backward compatible**: data presensi lama yang `sesi_id = null` tidak boleh error, cukup di-handle dengan kondisi nullable
5. Untuk **Ketertiban** (yang input berdasarkan gender, bukan kelas): buat Sesi tetap, tapi `kelas_ids` bisa kosong array. Santri yang diloop adalah semua santri gender terpilih
6. Untuk **Dewan Guru**: `kelas_ids` default dari kelas yang diampu, tapi bisa dipilih multi bila ingin gabung
7. Pastikan **form `create.blade.php`** memiliki UI multi-select kelas (bisa sederhana: checkbox list kelas)
8. **Jangan lupa** tambahkan `Sesi` ke `AppServiceProvider` atau `AuthServiceProvider` jika perlu policy

---

## Urutan Pengerjaan yang Disarankan

```
1. Migration: sesi → sesi_kelas → alter presensis (add sesi_id)
2. Model Sesi.php (dengan semua relasi)
3. Update Model Presensi.php (tambah relasi sesi)
4. Update Model Kegiatan.php (tambah relasi sesis)
5. Update StorePresensiRequest.php (tambah rules kelas_ids)
6. Update PresensiController@store (logika baru dengan Sesi)
7. Update PresensiController@create (form multi-select kelas)
8. Update PresensiController@index (kalkulasi total_sesi yang benar)
9. Buat PresensiRekapService.php
10. Update view create.blade.php (tambah UI pilih kelas)
11. Update view index.blade.php (tampilkan total_sesi, bukan total_pertemuan dari record)
```