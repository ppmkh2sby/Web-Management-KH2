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
    $grouped = $logs->groupBy(fn ($log) => strtolower($log->status));
    $recent = $logs->take(5);
@endphp

<div class="space-y-6">
  @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 text-sm space-y-1">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm text-gray-500">Log Keluar/Masuk</p>
        <h2 class="text-2xl font-semibold text-gray-900">{{ $santri->nama_lengkap ?? 'Santri' }}</h2>
        <p class="mt-1 text-sm text-gray-500">Catat izin keluar/masuk dan pantau statusnya.</p>
      </div>
      <div class="flex gap-2">
        @php
          $tabs = [
            ['key' => 'input', 'label' => 'Input Pengajuan', 'icon' => 'plus-circle'],
            ['key' => 'mine',  'label' => 'Log Saya', 'icon' => 'list-checks'],
          ];
        @endphp
        @foreach($tabs as $tab)
          @php $active = $mode === $tab['key']; @endphp
          <a href="{{ route('santri.data.log', ['mode' => $tab['key']]) }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold {{ $active ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4"></i>
            {{ $tab['label'] }}
          </a>
        @endforeach
      </div>
    </div>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach($statusStyles as $status => $style)
      @php $count = optional($grouped->get($status))->count() ?? 0; @endphp
      <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <p class="text-sm text-gray-500 capitalize">{{ $status }}</p>
        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $count }}</p>
      </div>
    @endforeach
  </div>

  @if($mode === 'input')
    <div class="grid gap-5 lg:grid-cols-3">
      <div class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Ajukan izin keluar/masuk</h3>
            <p class="text-sm text-gray-500">Isi tanggal, tujuan, dan rentang waktu keluar/masuk.</p>
          </div>
          <span class="text-xs text-gray-500">Status awal: proses</span>
        </div>
        <form method="POST" action="{{ route('santri.data.log.store') }}" class="space-y-4">
          @csrf
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm text-gray-600">Tanggal</label>
              <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>
            <div>
              <label class="text-sm text-gray-600">Tujuan</label>
              <input type="text" name="tujuan" value="{{ old('tujuan') }}" placeholder="Misal: Kontrol kesehatan" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="150" required>
            </div>
            <div>
              <label class="text-sm text-gray-600">Waktu keluar</label>
              <input type="time" name="waktu_keluar" value="{{ old('waktu_keluar') }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>
            <div>
              <label class="text-sm text-gray-600">Waktu masuk</label>
              <input type="time" name="waktu_masuk" value="{{ old('waktu_masuk') }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>
          </div>
          <div>
            <label class="text-sm text-gray-600">Catatan (opsional)</label>
            <textarea name="catatan" rows="3" placeholder="Tambahkan detail penting" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('catatan') }}</textarea>
          </div>
          <div class="flex items-center justify-between">
            <p class="text-xs text-gray-500">Pengajuan baru akan ditandai sebagai proses hingga disetujui/dicatat petugas.</p>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
              <i data-lucide="send" class="w-4 h-4"></i> Simpan Pengajuan
            </button>
          </div>
        </form>
      </div>
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Log terbaru</h3>
          <span class="text-xs text-gray-500">{{ $recent->count() }} catatan</span>
        </div>
        @if($recent->isEmpty())
          <p class="mt-3 text-sm text-gray-500">Belum ada log yang tercatat.</p>
        @else
          <ul class="mt-4 space-y-3">
            @foreach($recent as $log)
              <li class="rounded-2xl border border-gray-100 p-3">
                <div class="flex items-center justify-between text-sm">
                  <p class="font-semibold text-gray-900">{{ $log->jenis }}</p>
                  <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[strtolower($log->status)] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($log->status) }}</span>
                </div>
                <p class="text-xs text-gray-500">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</p>
                <p class="mt-2 text-xs text-gray-600">{{ $log->rentang }}</p>
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
    @if($logs->isEmpty())
      <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
        Belum ada pengajuan keluar/masuk.
      </div>
    @else
      <div class="space-y-4">
        @foreach($logs as $log)
          @php
            $editable = strtolower($log->status) === 'proses';
            $parts = array_map('trim', explode('-', $log->rentang ?? '', 2));
            [$startTime, $endTime] = [$parts[0] ?? '', $parts[1] ?? ''];
          @endphp
          <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div>
                <p class="text-sm text-gray-500">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</p>
                <h4 class="text-lg font-semibold text-gray-900">{{ $log->jenis }}</h4>
              </div>
              <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[strtolower($log->status)] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($log->status) }}</span>
            </div>
            <p class="text-sm text-gray-600">Rentang: <span class="font-semibold text-gray-900">{{ $log->rentang }}</span></p>
            <p class="text-sm text-gray-500">Catatan: {{ $log->catatan ?: '-' }}</p>
            @if($editable)
              <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-800">Masih proses - bisa diubah atau dibatalkan.</p>
                <form method="POST" action="{{ route('santri.data.log.update', $log) }}" class="mt-3 space-y-3">
                  @csrf
                  @method('PATCH')
                  <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                      <label class="text-xs text-gray-600">Tanggal</label>
                      <input type="date" name="tanggal" value="{{ optional($log->tanggal_pengajuan)->toDateString() }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    </div>
                    <div>
                      <label class="text-xs text-gray-600">Tujuan</label>
                      <input type="text" name="tujuan" value="{{ $log->jenis }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    </div>
                    <div>
                      <label class="text-xs text-gray-600">Waktu keluar</label>
                      <input type="time" name="waktu_keluar" value="{{ $startTime }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    </div>
                    <div>
                      <label class="text-xs text-gray-600">Waktu masuk</label>
                      <input type="time" name="waktu_masuk" value="{{ $endTime }}" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-gray-600">Catatan</label>
                    <textarea name="catatan" rows="2" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ $log->catatan }}</textarea>
                  </div>
                  <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-gray-500">Jika sudah disetujui, status akan diperbarui oleh petugas.</p>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                      <i data-lucide="save" class="w-4 h-4"></i> Simpan perubahan
                    </button>
                  </div>
                </form>
                <form method="POST" action="{{ route('santri.data.log.destroy', $log) }}" onsubmit="return confirm('Hapus pengajuan ini?')" class="mt-2 flex justify-end">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                    <i data-lucide="trash" class="w-4 h-4"></i> Batalkan
                  </button>
                </form>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  @endif
</div>
@endsection
