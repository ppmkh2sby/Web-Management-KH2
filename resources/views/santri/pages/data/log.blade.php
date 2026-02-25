@extends('layouts.santri-modern')
@section('title', $isStaffViewer ? 'Log Keluar/Masuk Santri' : 'Log Keluar/Masuk')

@section('content')
@php
    $isPaginated = $logs instanceof \Illuminate\Pagination\LengthAwarePaginator || $logs instanceof \Illuminate\Pagination\Paginator;
    $logRows = $isPaginated ? collect($logs->items()) : $logs;
    $recent = $logRows->take(5);
@endphp

<div class="space-y-6">
  @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 space-y-1">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  @if($isStaffViewer)
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-sm text-gray-500">Monitoring Log Keluar/Masuk</p>
          <h2 class="text-2xl font-semibold text-gray-900">Semua Santri</h2>
          <p class="mt-1 text-sm text-gray-500">Pengurus dan dewan guru dapat melihat seluruh log santri.</p>
        </div>
        <form method="GET" action="{{ route('santri.data.log') }}" class="flex items-center gap-2">
          <input type="hidden" name="gender_filter" value="{{ $genderFilter }}">
          <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama/tujuan"
                 class="w-56 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
          <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Cari
          </button>
        </form>
      </div>

      <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri'] as $key => $label)
          <a href="{{ route('santri.data.log', ['gender_filter' => $key, 'search' => $search ?: null]) }}"
             class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ $genderFilter === $key ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ $label }}
          </a>
        @endforeach
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      @if($logRows->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
          Belum ada data log keluar/masuk.
        </div>
      @else
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 text-left text-gray-500">
                <th class="py-2 pr-4">Tanggal</th>
                <th class="py-2 pr-4">Santri</th>
                <th class="py-2 pr-4">Gender</th>
                <th class="py-2 pr-4">Tujuan</th>
                <th class="py-2 pr-4">Keluar</th>
                <th class="py-2 pr-4">Masuk</th>
                <th class="py-2">Catatan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($logRows as $log)
                @php
                  $gender = strtolower((string) ($log->santri->gender ?? ''));
                  $genderLabel = $gender === 'putra' ? 'Putra' : ($gender === 'putri' ? 'Putri' : '-');
                  $genderClass = $gender === 'putra'
                    ? 'bg-blue-100 text-blue-700'
                    : ($gender === 'putri' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600');
                @endphp
                <tr class="border-b border-gray-50">
                  <td class="py-2 pr-4">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</td>
                  <td class="py-2 pr-4 font-medium text-gray-900">{{ $log->santri->nama_lengkap ?? '-' }}</td>
                  <td class="py-2 pr-4">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $genderClass }}">{{ $genderLabel }}</span>
                  </td>
                  <td class="py-2 pr-4">{{ $log->jenis }}</td>
                  <td class="py-2 pr-4">{{ $log->waktu_keluar ?: '-' }}</td>
                  <td class="py-2 pr-4">{{ $log->waktu_masuk ?: '-' }}</td>
                  <td class="py-2">{{ $log->catatan ?: '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      @if($isPaginated && method_exists($logs, 'hasPages') && $logs->hasPages())
        <div class="mt-4 border-t border-gray-100 pt-3">
          {{ $logs->links() }}
        </div>
      @endif
    </div>
  @else
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-sm text-gray-500">Log Keluar/Masuk</p>
          <h2 class="text-2xl font-semibold text-gray-900">{{ $santri->nama_lengkap ?? 'Santri' }}</h2>
          <p class="mt-1 text-sm text-gray-500">Isi data keluar/masuk, lalu catatan langsung masuk ke log Anda.</p>
        </div>
        <span class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
          {{ $mode === 'mine' ? 'Subfitur: Log Saya' : 'Subfitur: Input Keluar/Masuk' }}
        </span>
      </div>
    </div>

    @if($mode === 'input')
      <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Form Log Keluar/Masuk</h3>
            <p class="text-sm text-gray-500">Tanggal, tujuan, waktu keluar, waktu masuk, dan catatan.</p>
          </div>
          <form method="POST" action="{{ route('santri.data.log.store') }}" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="text-sm text-gray-600">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}"
                       class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
              </div>
              <div>
                <label class="text-sm text-gray-600">Tujuan</label>
                <input type="text" name="tujuan" value="{{ old('tujuan') }}" placeholder="Misal: Kontrol kesehatan"
                       class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="150" required>
              </div>
              <div>
                <label class="text-sm text-gray-600">Waktu keluar</label>
                <input type="time" name="waktu_keluar" value="{{ old('waktu_keluar') }}"
                       class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
              </div>
              <div>
                <label class="text-sm text-gray-600">Waktu masuk</label>
                <input type="time" name="waktu_masuk" value="{{ old('waktu_masuk') }}"
                       class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
              </div>
            </div>
            <div>
              <label class="text-sm text-gray-600">Catatan</label>
              <textarea name="catatan" rows="3" placeholder="Opsional"
                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('catatan') }}</textarea>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Log
              </button>
            </div>
          </form>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Log terbaru</h3>
            <span class="text-xs text-gray-500">{{ $recent->count() }} data</span>
          </div>
          @if($recent->isEmpty())
            <p class="mt-3 text-sm text-gray-500">Belum ada data log.</p>
          @else
            <ul class="mt-4 space-y-3">
              @foreach($recent as $log)
                <li class="rounded-2xl border border-gray-100 p-3">
                  <p class="text-sm font-semibold text-gray-900">{{ $log->jenis }}</p>
                  <p class="text-xs text-gray-500">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</p>
                  <p class="mt-2 text-xs text-gray-600">Keluar {{ $log->waktu_keluar ?: '-' }} | Masuk {{ $log->waktu_masuk ?: '-' }}</p>
                  @if($log->catatan)
                    <p class="mt-1 text-xs text-gray-500">{{ $log->catatan }}</p>
                  @endif
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    @else
      @if($logRows->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
          Belum ada data log keluar/masuk.
        </div>
      @else
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-100 text-left text-gray-500">
                  <th class="py-2 pr-4">Tanggal</th>
                  <th class="py-2 pr-4">Tujuan</th>
                  <th class="py-2 pr-4">Waktu Keluar</th>
                  <th class="py-2 pr-4">Waktu Masuk</th>
                  <th class="py-2">Catatan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($logRows as $log)
                  <tr class="border-b border-gray-50">
                    <td class="py-2 pr-4">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</td>
                    <td class="py-2 pr-4 font-medium text-gray-900">{{ $log->jenis }}</td>
                    <td class="py-2 pr-4">{{ $log->waktu_keluar ?: '-' }}</td>
                    <td class="py-2 pr-4">{{ $log->waktu_masuk ?: '-' }}</td>
                    <td class="py-2">{{ $log->catatan ?: '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    @endif
  @endif
</div>
@endsection
