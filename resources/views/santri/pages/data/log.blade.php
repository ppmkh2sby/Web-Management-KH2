@extends('layouts.santri-modern')
@section('title','Log Keluar/Masuk')

@section('content')
@php
    $statusStyles = [
        'disetujui' => 'bg-emerald-100 text-emerald-700',
        'proses'    => 'bg-amber-100 text-amber-700',
        'tercatat'  => 'bg-blue-100 text-blue-700',
        'ditolak'   => 'bg-rose-100 text-rose-700',
    ];
@endphp

<div class="space-y-6">
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm text-gray-500">Log Keluar/Masuk</p>
        <h2 class="text-2xl font-semibold text-gray-900">Pantau perizinan santri</h2>
      </div>
      <a href="{{ route('santri.home') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
      </a>
    </div>
    <p class="mt-2 text-sm text-gray-500">Gunakan catatan ini untuk memastikan izin keluar/masuk tercatat dan dikonfirmasi petugas.</p>
  </div>

  @if($logs->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
      Belum ada log izin yang tercatat.
    </div>
  @else
    <div class="grid gap-5 lg:grid-cols-2">
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Ringkasan Status</h3>
          <span class="text-xs text-gray-500">{{ $logs->count() }} log</span>
        </div>
        @php
            $grouped = $logs->groupBy(fn ($log) => strtolower($log->status));
        @endphp
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          @foreach($statusStyles as $status => $style)
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
              <p class="text-sm text-gray-500 capitalize">{{ $status }}</p>
              <p class="mt-2 text-2xl font-semibold text-gray-900">{{ optional($grouped->get($status))->count() ?? 0 }}</p>
            </div>
          @endforeach
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Timeline Pengajuan</h3>
          <span class="text-xs text-gray-500">5 catatan terbaru</span>
        </div>
        <ul class="mt-4 space-y-3">
          @foreach($logs->take(5) as $log)
            <li class="rounded-2xl border border-gray-100 p-3">
              <div class="flex items-center justify-between text-sm">
                <p class="font-semibold text-gray-900">{{ $log->jenis }}</p>
                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[strtolower($log->status)] ?? 'bg-gray-100 text-gray-700' }}">
                  {{ ucfirst($log->status) }}
                </span>
              </div>
              <p class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</p>
              <p class="mt-2 text-xs text-gray-600">{{ $log->rentang }}</p>
              <p class="mt-1 text-xs text-gray-500">{{ $log->catatan }}</p>
            </li>
          @endforeach
        </ul>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <h3 class="text-lg font-semibold mb-3">Riwayat Lengkap</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500">
              <th class="py-2 pr-4">Tanggal Pengajuan</th>
              <th class="py-2 pr-4">Jenis</th>
              <th class="py-2 pr-4">Durasi/Rentang</th>
              <th class="py-2 pr-4">Petugas/Pengampu</th>
              <th class="py-2 pr-4">Status</th>
              <th class="py-2">Catatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logs as $log)
              <tr class="border-t border-gray-100">
                <td class="py-2 pr-4">{{ \Illuminate\Support\Carbon::parse($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</td>
                <td class="py-2 pr-4">{{ $log->jenis }}</td>
                <td class="py-2 pr-4">{{ $log->rentang }}</td>
                <td class="py-2 pr-4">{{ $log->petugas ?? '-' }}</td>
                <td class="py-2 pr-4">
                  <span class="rounded-lg px-2 py-1 text-xs {{ $statusStyles[strtolower($log->status)] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst($log->status) }}
                  </span>
                </td>
                <td class="py-2">{{ $log->catatan ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection
