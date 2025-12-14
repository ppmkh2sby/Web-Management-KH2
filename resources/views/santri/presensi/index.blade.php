@extends('layouts.santri-modern')
@section('title', $canManage ? 'Manajemen Presensi' : 'Presensi Saya')

@section('content')
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

  @if($canManage && $mode === 'input')
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <div>
        <p class="text-sm text-gray-500">Panel Tim Ketertiban</p>
        <h3 class="text-lg font-semibold text-gray-900">Tambah Presensi</h3>
      </div>
      <span class="text-xs text-gray-500">Ketertiban bisa mengelola semua santri</span>
    </div>
    <form method="POST" action="{{ route('santri.presensi.store') }}" class="grid gap-3 md:grid-cols-2">
      @csrf
      <div>
        <label class="text-sm font-medium text-gray-700">Santri</label>
        <select name="santri_id" class="mt-1 w-full rounded-xl border-gray-200 text-sm" required>
          <option value="">Pilih santri</option>
          @foreach($santriList as $santri)
            <option value="{{ $santri->id }}">{{ $santri->nama_lengkap }} ({{ $santri->tim ?? '-' }})</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-xl border-gray-200 text-sm" required>
          @foreach($statuses as $status)
            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Kategori Kegiatan</label>
        <select name="kategori" class="mt-1 w-full rounded-xl border-gray-200 text-sm" required>
          @foreach($kategoriOptions as $kategori)
            <option value="{{ $kategori }}">{{ ucfirst($kategori) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Waktu</label>
        <select name="waktu" class="mt-1 w-full rounded-xl border-gray-200 text-sm" required>
          @foreach($waktuOptions as $waktu)
            <option value="{{ $waktu }}">{{ ucfirst($waktu) }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="text-sm font-medium text-gray-700">Catatan</label>
        <textarea name="catatan" rows="2" class="mt-1 w-full rounded-xl border-gray-200 text-sm" placeholder="Opsional"></textarea>
      </div>
      <div class="md:col-span-2 flex justify-end">
        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-white text-sm font-semibold hover:bg-emerald-700">Simpan</button>
      </div>
    </form>
  </div>
  @endif

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <div>
        <p class="text-sm text-gray-500">{{ $canManage ? ($mode === 'input' ? 'Semua Presensi' : 'Presensi Saya') : 'Presensi Saya' }}</p>
        <h3 class="text-lg font-semibold text-gray-900">Riwayat Presensi</h3>
      </div>
      <div class="flex items-center gap-3">
        @if($canManage)
          <div class="relative">
            <select class="rounded-xl border-gray-200 text-xs py-2 px-3" onchange="location.href='{{ route('santri.presensi.index') }}?mode='+this.value">
              <option value="input" @selected($mode==='input')>Input presensi</option>
              <option value="mine" @selected($mode==='mine')>Presensi saya</option>
            </select>
          </div>
        @endif
        <span class="text-xs text-gray-500">{{ $presensis->total() }} catatan</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2">Santri</th>
            <th class="py-2">Kategori</th>
            <th class="py-2">Waktu</th>
            <th class="py-2">Status</th>
            <th class="py-2">Catatan</th>
            @if($canManage)
              <th class="py-2 text-right">Aksi</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @forelse($presensis as $row)
            @php
              $formId = 'presensi-'.$row->id;
            @endphp
            <tr class="border-t border-gray-100 align-top">
              <td class="py-2 pr-2">
                <div class="font-semibold text-gray-900">{{ $row->nama }}</div>
                <div class="text-xs text-gray-500">Tim: {{ $row->santri->tim ?? '-' }}</div>
              </td>
              <td class="py-2 pr-2">{{ ucfirst($row->kegiatan->kategori ?? '-') }}</td>
              <td class="py-2 pr-2">{{ ucfirst($row->waktu) }}</td>
              <td class="py-2 pr-2">
                @if($canEdit)
                  <select form="{{ $formId }}" name="status" class="rounded-lg border-gray-200 text-xs">
                    @foreach($statuses as $status)
                      <option value="{{ $status }}" @selected($row->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                  </select>
                @else
                  <span class="px-2 py-1 rounded-lg text-xs bg-gray-100 text-gray-700">{{ ucfirst($row->status) }}</span>
                @endif
              </td>
              <td class="py-2 pr-2">
                @if($canEdit)
                  <input form="{{ $formId }}" type="text" name="catatan" value="{{ $row->catatan }}" class="w-full rounded-lg border-gray-200 text-xs" />
                @else
                  <span class="text-sm text-gray-700">{{ $row->catatan ?? '-' }}</span>
                @endif
              </td>
              @if($canEdit)
              <td class="py-2 text-right">
                <form id="{{ $formId }}" method="POST" action="{{ route('santri.presensi.update', $row) }}" class="inline">
                  @csrf
                  @method('PATCH')
                  <input type="hidden" name="kategori" value="{{ $row->kegiatan->kategori ?? '' }}">
                  <input type="hidden" name="waktu" value="{{ $row->waktu }}">
                </form>
                <div class="flex justify-end gap-2">
                  <button type="submit" form="{{ $formId }}" class="rounded-lg bg-emerald-600 px-3 py-1 text-white text-xs hover:bg-emerald-700">Simpan</button>
                  <form method="POST" action="{{ route('santri.presensi.destroy', $row) }}" onsubmit="return confirm('Hapus presensi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1 text-rose-700 text-xs hover:bg-rose-50">Hapus</button>
                  </form>
                </div>
              </td>
              @endif
            </tr>
          @empty
            <tr>
              <td colspan="{{ $canEdit ? 6 : 5 }}" class="py-3 text-center text-gray-500">Belum ada data.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $presensis->links() }}
    </div>
  </div>
</div>
@endsection
