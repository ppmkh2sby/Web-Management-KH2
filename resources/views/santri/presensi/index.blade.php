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
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-gray-500">Panel Tim Ketertiban</p>
        <h3 class="text-lg font-semibold text-gray-900">Input Presensi Massal</h3>
        <p class="text-xs text-gray-500">Pilih status per santri, lalu simpan sekali klik.</p>
      </div>
      <span class="text-xs text-gray-500">Ketertiban bisa mengelola semua santri</span>
    </div>
      <div class="flex flex-wrap items-center gap-3">
        <div class="text-sm text-gray-700">Kelompok:</div>
        <div class="flex gap-2">
          @foreach(['putra'=>'Putra','putri'=>'Putri'] as $val => $label)
            <a href="{{ route('santri.presensi.index', ['mode' => 'input', 'gender' => $val]) }}"
               class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs {{ $gender === $val ? 'bg-emerald-600 text-white border-emerald-600' : 'border-gray-200 text-gray-700 hover:border-emerald-300' }}">
              {{ $label }}
            </a>
          @endforeach
        </div>
      </div>
    <form method="POST" action="{{ route('santri.presensi.store') }}" class="space-y-4">
      @csrf
      <div class="grid gap-3 md:grid-cols-3">
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
        <div>
          <label class="text-sm font-medium text-gray-700">Catatan (opsional, berlaku untuk semua)</label>
          <input name="catatan" class="mt-1 w-full rounded-xl border-gray-200 text-sm" placeholder="Misal: Kegiatan pagi" />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500">
              <th class="py-2">Santri</th>
              <th class="py-2 text-center">Hadir</th>
              <th class="py-2 text-center">Izin</th>
              <th class="py-2 text-center">Sakit</th>
              <th class="py-2 text-center">Alpha</th>
            </tr>
          </thead>
          <tbody>
            @foreach($santriList as $santri)
              <tr class="border-t border-gray-100">
                <td class="py-2 pr-2">
                  <div class="font-semibold text-gray-900">{{ $santri->nama_lengkap }}</div>
                  <div class="text-xs text-gray-500">Tim: {{ $santri->tim ?? '-' }}</div>
                </td>
                @foreach(['hadir','izin','sakit','alpha'] as $opt)
                  <td class="py-2 text-center">
                    <input type="radio" name="presensi[{{ $santri->id }}]" value="{{ $opt }}" class="h-4 w-4 text-emerald-600 border-gray-300" />
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-white text-sm font-semibold hover:bg-emerald-700">Simpan Presensi</button>
      </div>
    </form>
  </div>
  @endif

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <div>
        @php
          $subtitle = 'Presensi Saya';
          if($canManage && $mode === 'input') { $subtitle = 'Semua Presensi'; }
          if($isStaffViewer) { $subtitle = 'Rekap Presensi'; }
        @endphp
        <p class="text-sm text-gray-500">{{ $subtitle }}</p>
        <h3 class="text-lg font-semibold text-gray-900">Riwayat Presensi</h3>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        @if($canManage && $mode==='input')
          <div class="flex gap-2">
            @foreach(['putra'=>'Putra','putri'=>'Putri'] as $val => $label)
              <a href="{{ route('santri.presensi.index', ['mode' => 'input', 'gender' => $val]) }}"
                 class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs {{ $gender === $val ? 'bg-emerald-600 text-white border-emerald-600' : 'border-gray-200 text-gray-700 hover:border-emerald-300' }}">
                {{ $label }}
              </a>
            @endforeach
          </div>
        @endif
        @if($canManage)
          <div class="relative">
            <select class="rounded-xl border-gray-200 text-xs py-2 px-3" onchange="location.href='{{ route('santri.presensi.index') }}?mode='+this.value+'&gender={{ $gender }}'">
              <option value="input" @selected($mode==='input')>Input presensi</option>
              <option value="mine" @selected($mode==='mine')>Presensi saya</option>
            </select>
          </div>
        @endif
        @if($isStaffViewer || $canManage)
          <form method="GET" action="{{ route('santri.presensi.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="mode" value="{{ $mode }}" />
            @if($mode==='input')
              <input type="hidden" name="gender" value="{{ $gender }}" />
            @endif
            <input name="search" value="{{ $search }}" placeholder="Cari santri" class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm" />
            <select name="gender_filter" class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm">
              <option value="all" @selected(($genderFilter ?? 'all')==='all')>Semua</option>
              <option value="putra" @selected(($genderFilter ?? 'all')==='putra')>Putra</option>
              <option value="putri" @selected(($genderFilter ?? 'all')==='putri')>Putri</option>
            </select>
            <select name="kategori_filter" class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm">
              <option value="">Semua kegiatan</option>
              @foreach($kategoriOptions as $kategori)
                <option value="{{ $kategori }}" @selected(($kategoriFilter ?? '')===$kategori)>{{ ucfirst($kategori) }}</option>
              @endforeach
            </select>
            <input type="date" name="tanggal" value="{{ $tanggalFilter ?? '' }}" class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm" />
            <button class="rounded-xl bg-gray-800 px-3 py-1.5 text-white text-xs" type="submit">Cari</button>
          </form>
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
