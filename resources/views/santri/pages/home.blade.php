@extends('layouts.santri-modern')
@section('title','Dashboard Santri')

@section('content')
@php
    $santri = auth()->user();
    $displayName = $santri?->name ?? 'Santri';

    $metrics = [
        ['label' => 'Kehadiran Bulan Ini', 'value' => '92%', 'trend' => '+3% dari bulan lalu', 'icon' => 'check-circle'],
        ['label' => 'Setoran Hafalan', 'value' => '18 lembar', 'trend' => 'Target mingguan tercapai', 'icon' => 'book-open'],
        ['label' => 'Tugas Belum Selesai', 'value' => '2 tugas', 'trend' => 'Segera selesaikan', 'icon' => 'list-checks'],
        ['label' => 'Pelanggaran', 'value' => '0 catatan', 'trend' => 'Terima kasih sudah tertib', 'icon' => 'shield-check'],
    ];

    $classSchedule = [
        ['kelas' => 'Tafsir Jalalain', 'ustadz' => 'Ust. Abdullah', 'waktu' => '08.00 - 09.30', 'lokasi' => 'Serambi A'],
        ['kelas' => 'Fiqih Ibadah', 'ustadz' => 'Ust. Farhan', 'waktu' => '10.00 - 11.30', 'lokasi' => 'Ruang Multaqa'],
        ['kelas' => 'Nahwu Shorof', 'ustadz' => 'Ust. Hafidz', 'waktu' => '13.30 - 15.00', 'lokasi' => 'Kelas Quba'],
        ['kelas' => 'Tahfidz Murajaah', 'ustadz' => 'Ustadzah Alia', 'waktu' => '20.00 - 21.00', 'lokasi' => 'Mushola Utama'],
    ];

    $agenda = [
        'Harian' => [
            ['kegiatan' => 'Tahajud Berjamaah', 'waktu' => '04:00', 'status' => 'Wajib'],
            ['kegiatan' => 'Setoran Hafalan', 'waktu' => '06:30', 'status' => 'Disiplin'],
            ['kegiatan' => 'Pengajian Kitab', 'waktu' => 'Ba\'da Maghrib', 'status' => 'Prioritas'],
        ],
        'Mingguan' => [
            ['kegiatan' => 'Mabit & Muhasabah', 'waktu' => 'Jumat Malam', 'status' => 'Spesial'],
            ['kegiatan' => 'Kajian Tematik', 'waktu' => 'Sabtu Sore', 'status' => 'Terbuka'],
            ['kegiatan' => 'Rapat Divisi', 'waktu' => 'Minggu Pagi', 'status' => 'Opsional'],
        ],
        'Bulanan' => [
            ['kegiatan' => 'Tasmi\' Akbar', 'waktu' => 'Awal Bulan', 'status' => 'Utama'],
            ['kegiatan' => 'Khidmah Sosial', 'waktu' => 'Pekan ke-2', 'status' => 'Lapangan'],
            ['kegiatan' => 'Evaluasi Santri', 'waktu' => 'Akhir Bulan', 'status' => 'Pendampingan'],
        ],
    ];

    $news = [
        ['judul' => 'Kelas Tahfidz ditutup sementara 25 Desember untuk persiapan Tasmi\' Akbar.', 'icon' => 'megaphone'],
        ['judul' => 'Update fasilitas internet: akses wifi utama tetap aktif hingga pukul 23.00.', 'icon' => 'wifi'],
        ['judul' => 'Submit jurnal harian maksimal pukul 21.00 pada aplikasi ini.', 'icon' => 'notebook-pen'],
    ];

    $summaries = [
        [
            'title' => 'Log Presensi Sambung',
            'value' => '34 pertemuan',
            'detail' => '3 catatan terlambat',
            'icon' => 'fingerprint',
            'link' => route('santri.data.presensi'),
            'cta' => 'Detail presensi',
        ],
        [
            'title' => 'Log Keluar/Masuk',
            'value' => '5 izin aktif',
            'detail' => '2 pengajuan menunggu persetujuan',
            'icon' => 'door-open',
            'link' => route('santri.data.log'),
            'cta' => 'Lihat riwayat',
        ],
        [
            'title' => 'Progress Keilmuan',
            'value' => '72% target',
            'detail' => 'Butuh 4 setoran lagi untuk level berikutnya',
            'icon' => 'graduation-cap',
            'link' => route('santri.data.progres'),
            'cta' => 'Cek progres',
        ],
    ];
@endphp

<div class="space-y-6">
  <div class="grid gap-4 lg:grid-cols-3">
    <div class="lg:col-span-2 rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-white p-6">
      <p class="text-sm text-emerald-600">Assalamualaikum,</p>
      <h2 class="mt-1 text-2xl font-semibold">Selamat datang kembali, {{ $displayName }}!</h2>
      <p class="mt-3 text-sm text-gray-600 max-w-2xl">
        Berikut ringkasan aktivitas dan agenda pondok hari ini. Tetap jaga semangat belajar
        dan catat progresmu agar mudah terpantau oleh musyrif.
      </p> 
      <div class="mt-6 flex flex-wrap gap-3 text-sm">
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm border border-emerald-100">
          <i data-lucide="sunrise" class="w-4 h-4 text-emerald-500"></i>
          16 Rabiul Akhir 1447 H
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm border border-emerald-100">
          <i data-lucide="map-pin" class="w-4 h-4 text-emerald-500"></i>
          Kompleks Putra - Zona Ilmu
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm border border-emerald-100">
          <i data-lucide="target" class="w-4 h-4 text-emerald-500"></i>
          Fokus pekan ini: Konsisten murajaah
        </span>
      </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Agenda Terdekat</p>
          <h3 class="text-lg font-semibold mt-1">Tasmi' Akbar</h3>
        </div>
        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">4 Hari Lagi</span>
      </div>
      <p class="mt-4 text-sm text-gray-600">
        Siapkan hafalan terbaikmu. Pastikan setoran pekan ini selesai agar jadwal gladi bersih lancar.
      </p>
      <div class="mt-4 flex gap-2 text-xs text-gray-500">
        <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2 py-1">
          <i data-lucide="calendar" class="w-3 h-3"></i> 24 Desember
        </span>
        <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2 py-1">
          <i data-lucide="map" class="w-3 h-3"></i> Aula Al-Mubarok
        </span>
      </div>
    </div>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($metrics as $metric)
      <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between text-sm text-gray-500">
          <span>{{ $metric['label'] }}</span>
          <i data-lucide="{{ $metric['icon'] }}" class="w-4 h-4 text-emerald-500"></i>
        </div>
        <div class="mt-3 flex items-end justify-between">
          <span class="text-2xl font-semibold text-gray-900">{{ $metric['value'] }}</span>
          <span class="text-xs text-emerald-600">{{ $metric['trend'] }}</span>
        </div>
      </div>
    @endforeach
  </div>

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold">Ringkasan Data</h3>
        <p class="text-sm text-gray-500">Rekap cepat fitur presensi, log keluar/masuk, dan progres keilmuan.</p>
      </div>
      <a href="{{ route('santri.data.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">Lihat semua</a>
    </div>
    <div class="mt-5 grid gap-4 md:grid-cols-3">
      @foreach($summaries as $summary)
        <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4">
          <div class="flex items-center justify-between text-sm text-gray-500">
            <span>{{ $summary['title'] }}</span>
            <i data-lucide="{{ $summary['icon'] }}" class="w-4 h-4 text-emerald-500"></i>
          </div>
          <p class="mt-3 text-2xl font-semibold text-gray-900">{{ $summary['value'] }}</p>
          <p class="mt-1 text-xs text-gray-500">{{ $summary['detail'] }}</p>
          <a href="{{ $summary['link'] }}" class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700">
            {{ $summary['cta'] }}
            <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
          </a>
        </div>
      @endforeach
    </div>
  </div>
  <div class="grid gap-5 xl:grid-cols-3">
    <div class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold">Kelas Hari Ini</h3>
          <p class="text-sm text-gray-500">Pastikan hadir tepat waktu dan konfirmasi presensi.</p>
        </div>
        <button class="text-sm text-emerald-600 hover:text-emerald-700">Lihat Kalender</button>
      </div>
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500">
              <th class="py-2 pr-4 font-medium">Kelas</th>
              <th class="py-2 pr-4 font-medium">Pengampu</th>
              <th class="py-2 pr-4 font-medium">Waktu</th>
              <th class="py-2 font-medium">Lokasi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($classSchedule as $kelas)
              <tr class="hover:bg-gray-50">
                <td class="py-3 pr-4 font-medium text-gray-900">{{ $kelas['kelas'] }}</td>
                <td class="py-3 pr-4 text-gray-600">{{ $kelas['ustadz'] }}</td>
                <td class="py-3 pr-4 text-gray-600">{{ $kelas['waktu'] }}</td>
                <td class="py-3 text-gray-600">{{ $kelas['lokasi'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Agenda Pondok</h3>
        <span class="text-xs text-gray-500">Checklist rutin</span>
      </div>
      <p class="mt-1 text-sm text-gray-500">Kelola to-do list harian, mingguan, dan bulanan pondok.</p>
      <div class="mt-4 grid gap-3">
        @foreach($agenda as $kategori => $items)
          <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
            <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
              <span>{{ $kategori }}</span>
              <i data-lucide="chevrons-right" class="w-4 h-4 text-emerald-500"></i>
            </div>
            <ul class="mt-2 space-y-2">
              @foreach($items as $item)
                <li class="flex items-start gap-2 rounded-lg bg-white px-3 py-2 shadow-sm">
                  <i data-lucide="circle" class="mt-0.5 w-3 h-3 text-gray-300"></i>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">{{ $item['kegiatan'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['waktu'] }} • {{ $item['status'] }}</p>
                  </div>
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </div>
    </div>
  </div>
  <div class="grid gap-5 lg:grid-cols-3">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm lg:col-span-2">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Aktivitas & Catatan</h3>
        <button class="text-sm text-emerald-600 hover:text-emerald-700">Tambah Catatan</button>
      </div>
      <ul class="mt-4 space-y-3">
        <li class="flex items-start gap-3 rounded-xl border border-gray-100 p-3">
          <span class="rounded-full bg-emerald-50 p-2 text-emerald-600">
            <i data-lucide="pen-square" class="w-4 h-4"></i>
          </span>
          <div>
            <p class="text-sm font-semibold text-gray-800">Jurnal harian</p>
            <p class="text-xs text-gray-500">Tuliskan satu pelajaran penting dari majelis hari ini.</p>
          </div>
        </li>
        <li class="flex items-start gap-3 rounded-xl border border-gray-100 p-3">
          <span class="rounded-full bg-blue-50 p-2 text-blue-600">
            <i data-lucide="book" class="w-4 h-4"></i>
          </span>
          <div>
            <p class="text-sm font-semibold text-gray-800">Murajaah</p>
            <p class="text-xs text-gray-500">Minimal ulangi 2 lembar malam ini sebelum tidur.</p>
          </div>
        </li>
        <li class="flex items-start gap-3 rounded-xl border border-gray-100 p-3">
          <span class="rounded-full bg-orange-50 p-2 text-orange-600">
            <i data-lucide="heart-handshake" class="w-4 h-4"></i>
          </span>
          <div>
            <p class="text-sm font-semibold text-gray-800">Khidmah Asrama</p>
            <p class="text-xs text-gray-500">Tugas piket kamar Zayd malam ini (21.30).</p>
          </div>
        </li>
      </ul>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <h3 class="text-lg font-semibold">Info Pondok</h3>
      <p class="mt-1 text-sm text-gray-500">Pastikan membaca setiap pembaruan.</p>
      <div class="mt-4 space-y-3 text-sm text-gray-700">
        @foreach($news as $item)
          <div class="flex gap-3 rounded-xl border border-gray-100 p-3">
            <span class="rounded-full bg-emerald-50 p-2 text-emerald-600">
              <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
            </span>
            <p>{{ $item['judul'] }}</p>
          </div>
        @endforeach
      </div>
      <button class="mt-4 w-full rounded-xl bg-emerald-600 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        Konfirmasi Sudah Dibaca
      </button>
    </div>
  </div>
</div>

@endsection
