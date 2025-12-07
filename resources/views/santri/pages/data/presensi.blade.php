@extends('layouts.santri-modern')
@section('title','Presensi')

@section('content')
@php
    $statusStyles = [
        'hadir' => 'bg-emerald-100 text-emerald-700',
        'izin'  => 'bg-amber-100 text-amber-700',
        'alpa'  => 'bg-rose-100 text-rose-700',
    ];
    $totalPertemuan = $data->count();
    $hadir = $data->where('status','hadir')->count();
    $izin = $data->where('status','izin')->count();
    $alpa = $data->where('status','alpa')->count();
    $presentase = $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100) : 0;
    $recent = $data->take(5);
    $issues = $data->filter(fn ($row) => in_array($row->status, ['izin','alpa']))->take(4);
@endphp

<div class="space-y-6">
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm text-gray-500">Ringkasan Presensi</p>
        <h2 class="text-2xl font-semibold text-gray-900">30 catatan terakhir</h2>
      </div>
      <div class="flex gap-2 text-sm">
        <a href="{{ route('santri.home') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-gray-600 hover:text-gray-800">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <button class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-700">
          <i data-lucide="download" class="w-4 h-4"></i> Unduh Rekap
        </button>
      </div>
    </div>
    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <p class="text-sm text-gray-500">Total Pertemuan</p>
        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalPertemuan }}</p>
      </div>
      <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <p class="text-sm text-gray-500">Hadir</p>
        <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $hadir }}</p>
      </div>
      <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <p class="text-sm text-gray-500">Izin</p>
        <p class="mt-2 text-3xl font-semibold text-amber-500">{{ $izin }}</p>
      </div>
      <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <p class="text-sm text-gray-500">Persentase Kehadiran</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $presentase }}%</p>
      </div>
    </div>
  </div>

  <div class="grid gap-5 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Timeline Presensi</h3>
        <span class="text-xs text-gray-500">5 catatan terbaru</span>
      </div>
      <ul class="mt-4 space-y-3">
        @forelse($recent as $row)
          <li class="flex items-center gap-3 rounded-2xl border border-gray-100 p-3">
            <div class="rounded-xl bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700">
              {{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M') }}
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-gray-900">{{ $row->keterangan ?? 'Kegiatan harian' }}</p>
              <p class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('l, d F Y') }}</p>
            </div>
            <span class="rounded-lg px-3 py-1 text-xs font-medium {{ $statusStyles[$row->status] ?? 'bg-gray-100 text-gray-700' }}">
              {{ ucfirst($row->status) }}
            </span>
          </li>
        @empty
          <li class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500">Belum ada catatan presensi.</li>
        @endforelse
      </ul>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold">Catatan Perlu Tindak Lanjut</h3>
          <p class="text-sm text-gray-500">Ambil tindakan pada izin/alpa terbaru.</p>
        </div>
        <span class="text-xs text-gray-500">{{ $izin + $alpa }} catatan</span>
      </div>
      <ul class="mt-4 space-y-3">
        @forelse($issues as $row)
          <li class="rounded-2xl border border-gray-100 p-3">
            <div class="flex items-center justify-between text-sm">
              <p class="font-semibold text-gray-900">{{ ucfirst($row->status) }}</p>
              <span class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</span>
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ $row->keterangan ?? '-' }}</p>
          </li>
        @empty
          <li class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500">Tidak ada izin/alpa terbaru.</li>
        @endforelse
      </ul>
    </div>
  </div>

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="text-lg font-semibold mb-3">Riwayat Presensi Lengkap</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2">Tanggal</th>
            <th class="py-2">Status</th>
            <th class="py-2">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $row)
            <tr class="border-t border-gray-100">
              <td class="py-2">{{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
              <td class="py-2">
                <span class="px-2 py-1 rounded-lg text-xs {{ $statusStyles[$row->status] ?? 'bg-gray-100 text-gray-700' }}">
                  {{ ucfirst($row->status) }}
                </span>
              </td>
              <td class="py-2">{{ $row->keterangan ?? '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="py-3 text-center text-gray-500">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
