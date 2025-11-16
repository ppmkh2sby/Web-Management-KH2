@extends('layouts.santri-modern')
@section('title','Ringkasan Data Santri')

@section('content')
@php
    $presensiStats = [
        'hadir' => 34,
        'terlambat' => 3,
        'alpa' => 1,
        'persentase' => '92%',
    ];

    $presensiTimeline = [
        ['tanggal' => 'Sen, 11 Des', 'kegiatan' => 'Pengajian Kitab', 'status' => 'Hadir Tepat Waktu', 'label' => 'Hijau'],
        ['tanggal' => 'Sel, 12 Des', 'kegiatan' => 'Tahfidz Sore', 'status' => 'Terlambat 7 menit', 'label' => 'Kuning'],
        ['tanggal' => 'Rab, 13 Des', 'kegiatan' => 'Fiqih Ibadah', 'status' => 'Hadir Tepat Waktu', 'label' => 'Hijau'],
        ['tanggal' => 'Kam, 14 Des', 'kegiatan' => 'Murajaah Malam', 'status' => 'Izin Sakit', 'label' => 'Biru'],
    ];

    $logEntries = [
        ['tanggal' => '10 Des 2025', 'jenis' => 'Keluar Pondok', 'durasi' => '13.00 - 16.00', 'status' => 'Disetujui', 'keterangan' => 'Kontrol kesehatan Puskesmas'],
        ['tanggal' => '05 Des 2025', 'jenis' => 'Cuti Akhir Pekan', 'durasi' => 'Sabtu - Ahad', 'status' => 'Proses', 'keterangan' => 'Undangan keluarga'],
        ['tanggal' => '28 Nov 2025', 'jenis' => 'Kembali', 'durasi' => '21.15', 'status' => 'Tercatat', 'keterangan' => 'Sesudah kegiatan lapangan'],
    ];

    $progresModules = [
        ['nama' => 'Hafalan Juz 30', 'target' => '20 surah', 'selesai' => '15 surah', 'status' => 'On Track'],
        ['nama' => 'Fiqih Ibadah', 'target' => '8 bab', 'selesai' => '5 bab', 'status' => 'Butuh Percepatan'],
        ['nama' => 'Bahasa Arab', 'target' => '12 modul', 'selesai' => '9 modul', 'status' => 'Menunggu evaluasi'],
    ];
@endphp

<div class="space-y-6">
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <p class="text-sm text-gray-500">Ringkasan Data</p>
    <h2 class="mt-1 text-2xl font-semibold text-gray-900">Rangkuman keseluruhan aktivitas santri</h2>
    <p class="mt-2 text-sm text-gray-600">Pantau progres dari seluruh fitur: presensi sambung, log keluar/masuk, dan perkembangan keilmuan.</p>
    <div class="mt-4 flex flex-wrap gap-3 text-xs sm:text-sm">
      <a href="{{ route('santri.data.presensi') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-emerald-700">
        <i data-lucide="fingerprint" class="w-4 h-4"></i> Detail Presensi
      </a>
      <a href="{{ route('santri.data.log') }}" class="inline-flex items-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-blue-700">
        <i data-lucide="door-open" class="w-4 h-4"></i> Riwayat Keluar/Masuk
      </a>
      <a href="{{ route('santri.data.progres') }}" class="inline-flex items-center gap-2 rounded-xl border border-orange-100 bg-orange-50 px-3 py-2 text-orange-700">
        <i data-lucide="graduation-cap" class="w-4 h-4"></i> Progress Keilmuan
      </a>
    </div>
  </div>

  <div class="grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Hadir</p>
      <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $presensiStats['hadir'] }}</p>
      <p class="text-xs text-gray-400">Pertemuan bulan ini</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Terlambat</p>
      <p class="mt-2 text-3xl font-semibold text-orange-500">{{ $presensiStats['terlambat'] }}</p>
      <p class="text-xs text-gray-400">Butuh konfirmasi musyrif</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <p class="text-sm text-gray-500">Kehadiran</p>
      <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $presensiStats['persentase'] }}</p>
      <p class="text-xs text-gray-400">{{ $presensiStats['alpa'] }} kali alpa bulan ini</p>
    </div>
  </div>

  <div class="grid gap-5 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold">Log Presensi Sambung</h3>
          <p class="text-sm text-gray-500">Riwayat empat majelis terakhir dan statusnya.</p>
        </div>
        <a href="{{ route('santri.data.presensi') }}" class="text-sm text-emerald-600 hover:text-emerald-700">Lihat semua</a>
      </div>
      <ul class="mt-4 space-y-3">
        @foreach($presensiTimeline as $item)
          <li class="rounded-2xl border border-gray-100 p-3">
            <div class="flex items-center justify-between text-sm">
              <div>
                <p class="font-semibold text-gray-900">{{ $item['kegiatan'] }}</p>
                <p class="text-xs text-gray-500">{{ $item['tanggal'] }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-xs font-medium
                @if($item['label']==='Hijau') bg-emerald-50 text-emerald-700
                @elseif($item['label']==='Kuning') bg-yellow-50 text-yellow-700
                @else bg-blue-50 text-blue-700 @endif">
                {{ $item['status'] }}
              </span>
            </div>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold">Log Keluar/Masuk</h3>
          <p class="text-sm text-gray-500">Monitoring izin dan waktu kembali.</p>
        </div>
        <a href="{{ route('santri.data.log') }}" class="text-sm text-blue-600 hover:text-blue-700">Detail log</a>
      </div>
      <div class="mt-4 space-y-4">
        @foreach($logEntries as $log)
          <div class="rounded-2xl border border-gray-100 p-4">
            <div class="flex items-center justify-between text-sm text-gray-500">
              <span>{{ $log['tanggal'] }}</span>
              <span class="rounded-full bg-gray-100 px-2 py-1 text-xs">{{ $log['status'] }}</span>
            </div>
            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $log['jenis'] }}</p>
            <p class="text-xs text-gray-500">{{ $log['durasi'] }}</p>
            <p class="mt-1 text-xs text-gray-600">{{ $log['keterangan'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold">Progress Keilmuan</h3>
        <p class="text-sm text-gray-500">Perkembangan modul yang sedang ditempuh.</p>
      </div>
      <a href="{{ route('santri.data.progres') }}" class="text-sm text-orange-600 hover:text-orange-700">Lihat progres</a>
    </div>
    <div class="mt-4 grid gap-4 md:grid-cols-3">
      @foreach($progresModules as $module)
        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
          <p class="text-sm font-semibold text-gray-900">{{ $module['nama'] }}</p>
          <p class="mt-2 text-xs text-gray-500">Target: {{ $module['target'] }}</p>
          <p class="text-xs text-gray-500">Selesai: {{ $module['selesai'] }}</p>
          <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs text-gray-700">
            <i data-lucide="sparkles" class="w-3 h-3 text-orange-500"></i>
            {{ $module['status'] }}
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
