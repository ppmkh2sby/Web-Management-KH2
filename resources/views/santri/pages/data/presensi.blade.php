@extends('layouts.santri-modern')
@section('title','Presensi')

@section('content')
@php
    $statusStyles = [
        'hadir' => 'bg-emerald-100 text-emerald-700',
        'izin'  => 'bg-amber-100 text-amber-700',
        'alpa'  => 'bg-rose-100 text-rose-700',
    ];
    $statusOptions = \App\Models\Kehadiran::STATUSES ?? ['hadir','izin','alpa'];
    $totalPertemuan = $data->count();
    $hadir = $data->where('status','hadir')->count();
    $izin = $data->where('status','izin')->count();
    $alpa = $data->where('status','alpa')->count();
    $presentase = $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100) : 0;
    $recent = $data->take(5);
    $issues = $data->filter(fn ($row) => in_array($row->status, ['izin','alpa']))->take(4);
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

  @if($isKetertiban)
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-gray-500">Panel Tim Ketertiban</p>
        <h3 class="text-lg font-semibold text-gray-900">Absen & kelola presensi santri</h3>
      </div>
      <span class="text-xs text-gray-500">Akses khusus anggota tim Ketertiban</span>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-semibold text-gray-800">Absen cepat</h4>
          <span class="text-xs text-gray-500">Tanggal: {{ now()->translatedFormat('d M Y') }}</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500">
                <th class="py-2">Santri</th>
                <th class="py-2">Tim</th>
                <th class="py-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($santriList as $row)
                <tr class="border-t border-gray-100">
                  <td class="py-2">
                    <div class="font-semibold text-gray-900">{{ $row->nama_lengkap }}</div>
                    <div class="text-xs text-gray-500">Kode: {{ $row->code }}</div>
                  </td>
                  <td class="py-2 text-xs">
                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-gray-700 border border-gray-200">
                      <i data-lucide="shield-check" class="w-3 h-3"></i> {{ $row->tim ?? '-' }}
                    </span>
                  </td>
                  <td class="py-2">
                    <div class="flex justify-end gap-2">
                      @foreach($statusOptions as $option)
                        <form method="POST" action="{{ route('santri.ketertiban.presensi.store') }}" class="inline">
                          @csrf
                          <input type="hidden" name="santri_id" value="{{ $row->id }}">
                          <input type="hidden" name="tanggal" value="{{ now()->toDateString() }}">
                          <input type="hidden" name="kegiatan" value="Presensi harian">
                          <button type="submit" name="status" value="{{ $option }}" class="rounded-xl px-3 py-1.5 text-xs font-semibold border border-gray-200 bg-white hover:border-emerald-300 hover:text-emerald-700">
                            {{ ucfirst($option) }}
                          </button>
                        </form>
                      @endforeach
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="py-3 text-center text-gray-500">Belum ada data santri.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-semibold text-gray-800">Edit / hapus catatan</h4>
          <span class="text-xs text-gray-500">60 catatan terbaru</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-xs sm:text-sm">
            <thead>
              <tr class="text-left text-gray-500">
                <th class="py-2">Tanggal</th>
                <th class="py-2">Santri</th>
                <th class="py-2">Status</th>
                <th class="py-2">Keterangan</th>
                <th class="py-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="align-top">
              @forelse($managedKehadiran as $row)
                @php
                  $tanggalValue = \Illuminate\Support\Carbon::parse($row->tanggal)->toDateString();
                  $formId = 'update-kehadiran-'.$row->id;
                @endphp
                <tr class="border-t border-gray-100">
                  <td class="py-2 pr-2">
                      <input form="{{ $formId }}" type="date" name="tanggal" value="{{ $tanggalValue }}" class="rounded-lg border-gray-200 text-xs sm:text-sm">
                  </td>
                  <td class="py-2 pr-2 text-gray-800 font-semibold">{{ $row->santri->nama_lengkap ?? '-' }}</td>
                  <td class="py-2 pr-2">
                      <select form="{{ $formId }}" name="status" class="rounded-lg border-gray-200 text-xs sm:text-sm">
                        @foreach($statusOptions as $option)
                          <option value="{{ $option }}" @selected($row->status === $option)> {{ ucfirst($option) }} </option>
                        @endforeach
                      </select>
                  </td>
                  <td class="py-2 pr-2">
                      <input form="{{ $formId }}" type="text" name="keterangan" value="{{ $row->keterangan ?? '' }}" placeholder="Keterangan" class="w-full rounded-lg border-gray-200 text-xs sm:text-sm" />
                  </td>
                  <td class="py-2 text-right">
                      <div class="flex justify-end gap-2">
                        <form id="{{ $formId }}" method="POST" action="{{ route('santri.ketertiban.presensi.update', $row) }}">
                          @csrf
                          @method('PATCH')
                        </form>
                        <button type="submit" form="{{ $formId }}" class="rounded-lg bg-emerald-600 px-3 py-1 text-white text-xs sm:text-sm hover:bg-emerald-700">Simpan</button>
                        <form method="POST" action="{{ route('santri.ketertiban.presensi.destroy', $row) }}">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1 text-rose-700 text-xs sm:text-sm hover:bg-rose-50" onclick="return confirm('Hapus catatan presensi ini?')">Hapus</button>
                        </form>
                      </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="py-3 text-center text-gray-500">Belum ada catatan kehadiran.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif

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
