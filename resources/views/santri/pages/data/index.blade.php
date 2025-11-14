@extends('layouts.santri-modern')
@section('title','Data Santri')

@section('content')
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <a href="{{ route('santri.data.presensi') }}" class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 hover:shadow-md transition">
    <p class="font-semibold">Presensi</p>
    <p class="text-sm text-gray-500">Riwayat kehadiran terbaru.</p>
  </a>
  <a href="{{ route('santri.data.progres') }}" class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 hover:shadow-md transition">
    <p class="font-semibold">Progres Keilmuan</p>
    <p class="text-sm text-gray-500">Capaian hafalan/pelajaran.</p>
  </a>
  <a href="{{ route('santri.data.log') }}" class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 hover:shadow-md transition">
    <p class="font-semibold">Log Keluar/Masuk</p>
    <p class="text-sm text-gray-500">Riwayat izin pondok.</p>
  </a>
</div>
@endsection
