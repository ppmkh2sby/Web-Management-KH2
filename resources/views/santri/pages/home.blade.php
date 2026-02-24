@extends('layouts.santri-modern')
@section('title', 'Dashboard Santri')

@section('content')
@php
  $attendanceStatusStyles = [
    'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'izin' => 'bg-amber-50 text-amber-700 border-amber-200',
    'sakit' => 'bg-sky-50 text-sky-700 border-sky-200',
    'alpha' => 'bg-rose-50 text-rose-700 border-rose-200',
  ];

  $logStatusStyles = [
    'tercatat' => 'bg-blue-50 text-blue-700 border-blue-200',
  ];

  $kafarahProgress = $kafarahStats['total_kafarah'] > 0
    ? (int) min(100, round(($kafarahStats['jumlah_setor'] / $kafarahStats['total_kafarah']) * 100))
    : 0;
@endphp

<div class="space-y-6">
  <div class="relative overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-slate-50 p-6 shadow-sm">
    <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-emerald-200/40 blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-10 -left-10 h-36 w-36 rounded-full bg-sky-200/30 blur-2xl"></div>

    <div class="relative flex flex-wrap items-start justify-between gap-5">
      <div class="max-w-2xl space-y-3">
        <p class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
          <i data-lucide="layout-dashboard" class="h-3.5 w-3.5"></i>
          Ringkasan Santri
        </p>
        <h1 class="text-2xl font-semibold text-gray-900 sm:text-3xl">Assalamualaikum, {{ $displayName }}</h1>
        <p class="text-sm leading-relaxed text-gray-600">
          Dashboard ini merangkum data dari fitur Presensi, Kafarah, Progress Keilmuan, dan Log Keluar/Masuk dalam satu tampilan.
        </p>
        <div class="flex flex-wrap gap-2 text-xs">
          <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-700">
            <i data-lucide="calendar-days" class="h-3.5 w-3.5 text-emerald-600"></i>
            {{ $todayLabel }}
          </span>
          <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-700">
            <i data-lucide="users" class="h-3.5 w-3.5 text-emerald-600"></i>
            Tim: {{ $santri?->tim_resolved ?? $santri?->tim ?? '-' }}
          </span>
          <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-700">
            <i data-lucide="{{ $emailVerified ? 'badge-check' : 'badge-alert' }}" class="h-3.5 w-3.5 {{ $emailVerified ? 'text-emerald-600' : 'text-amber-500' }}"></i>
            {{ $emailVerified ? 'Email terverifikasi' : 'Email belum terverifikasi' }}
          </span>
        </div>
      </div>

      <div class="w-full max-w-sm rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Highlight</p>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
          <div class="rounded-xl border border-gray-100 bg-white p-3">
            <p class="text-xs text-gray-500">Kehadiran</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $attendanceStats['persentase'] }}%</p>
          </div>
          <div class="rounded-xl border border-gray-100 bg-white p-3">
            <p class="text-xs text-gray-500">Sisa Kafarah</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $kafarahStats['sisa_tanggungan'] }}</p>
          </div>
          <div class="rounded-xl border border-gray-100 bg-white p-3">
            <p class="text-xs text-gray-500">Avg Progress</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $progressStats['average'] }}%</p>
          </div>
          <div class="rounded-xl border border-gray-100 bg-white p-3">
            <p class="text-xs text-gray-500">Log Tercatat</p>
            <p class="mt-1 text-xl font-semibold text-gray-900">{{ $logStats['tercatat'] ?? ($logStats['total'] ?? 0) }}</p>
          </div>
        </div>
      </div>
    </div>

    @if($isSantriContext)
      <div class="relative mt-5 flex flex-wrap gap-2">
        <a href="{{ route('santri.presensi.index', ['mode' => 'mine']) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:text-emerald-700">
          <i data-lucide="clipboard-check" class="h-4 w-4"></i>
          Kehadiran Saya
        </a>
        <a href="{{ route('santri.kafarah.index', ['mode' => 'mine']) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:text-emerald-700">
          <i data-lucide="shield-alert" class="h-4 w-4"></i>
          Kafarah Saya
        </a>
        <a href="{{ route('santri.data.progres') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:text-emerald-700">
          <i data-lucide="book-marked" class="h-4 w-4"></i>
          Progress Keilmuan
        </a>
        <a href="{{ route('santri.data.log', ['mode' => 'mine']) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:text-emerald-700">
          <i data-lucide="door-open" class="h-4 w-4"></i>
          Log Keluar/Masuk
        </a>
      </div>
    @endif
  </div>

  @unless($isSantriContext)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
      Akun ini belum terhubung dengan data santri, sehingga dashboard menampilkan ringkasan kosong.
    </div>
  @endunless

  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Total Presensi</p>
      <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $attendanceStats['total'] }}</p>
      <p class="mt-1 text-xs text-emerald-700">Hadir {{ $attendanceStats['hadir'] }} kali</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Total Kafarah</p>
      <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $kafarahStats['total'] }}</p>
      <p class="mt-1 text-xs text-rose-700">Sisa {{ $kafarahStats['sisa_tanggungan'] }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Catatan Progress</p>
      <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $progressStats['total'] }}</p>
      <p class="mt-1 text-xs text-emerald-700">{{ $progressStats['completed'] }} materi selesai</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Total Log Keluar/Masuk</p>
      <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $logStats['total'] ?? 0 }}</p>
      <p class="mt-1 text-xs text-blue-700">{{ $logStats['tercatat'] ?? ($logStats['total'] ?? 0) }} data tercatat</p>
    </div>
  </div>

  <div class="grid gap-5 xl:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Presensi Kehadiran Saya</h2>
          <p class="text-sm text-gray-500">Ringkasan kehadiran dan catatan terbaru.</p>
        </div>
        @if($isSantriContext)
          <a href="{{ route('santri.presensi.index', ['mode' => 'mine']) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
        @else
          <span class="text-xs font-semibold text-gray-400">Hanya akun santri</span>
        @endif
      </div>
      <div class="mt-4 grid grid-cols-5 gap-2 text-center text-xs">
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Hadir</p><p class="font-semibold text-gray-900">{{ $attendanceStats['hadir'] }}</p></div>
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Izin</p><p class="font-semibold text-gray-900">{{ $attendanceStats['izin'] }}</p></div>
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Sakit</p><p class="font-semibold text-gray-900">{{ $attendanceStats['sakit'] }}</p></div>
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Alpa</p><p class="font-semibold text-gray-900">{{ $attendanceStats['alpha'] }}</p></div>
        <div class="rounded-lg bg-emerald-50 p-2"><p class="text-emerald-700">Persen</p><p class="font-semibold text-emerald-700">{{ $attendanceStats['persentase'] }}%</p></div>
      </div>
      <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $attendanceStats['persentase'] }}%"></div>
      </div>
      <div class="mt-4 space-y-2">
        @forelse($attendanceRecent as $row)
          @php
            $status = strtolower((string) $row->status);
            $statusLabel = $status === 'alpha' ? 'Alpa' : ucfirst($status);
            $statusClass = $attendanceStatusStyles[$status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
          @endphp
          <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3 py-2">
            <div class="min-w-0">
              <p class="truncate text-sm font-medium text-gray-900">{{ ucfirst($row->kegiatan->kategori ?? 'Presensi') }} - {{ ucfirst($row->waktu ?? '-') }}</p>
              <p class="text-xs text-gray-500">{{ optional($row->created_at)->translatedFormat('d M Y') }}</p>
            </div>
            <span class="whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
          </div>
        @empty
          <div class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500">Belum ada data presensi.</div>
        @endforelse
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Kafarah Saya</h2>
          <p class="text-sm text-gray-500">Pantau setoran dan tanggungan terkini.</p>
        </div>
        @if($isSantriContext)
          <a href="{{ route('santri.kafarah.index', ['mode' => 'mine']) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
        @else
          <span class="text-xs font-semibold text-gray-400">Hanya akun santri</span>
        @endif
      </div>
      <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Total</p><p class="font-semibold text-gray-900">{{ $kafarahStats['total'] }}</p></div>
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Setor</p><p class="font-semibold text-gray-900">{{ $kafarahStats['jumlah_setor'] }}</p></div>
        <div class="rounded-lg bg-rose-50 p-2"><p class="text-rose-700">Sisa</p><p class="font-semibold text-rose-700">{{ $kafarahStats['sisa_tanggungan'] }}</p></div>
      </div>
      <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $kafarahProgress }}%"></div>
      </div>
      <p class="mt-2 text-xs text-gray-500">Progress penyelesaian kafarah: {{ $kafarahProgress }}%</p>
      <div class="mt-4 space-y-2">
        @forelse($kafarahRecent as $row)
          <div class="rounded-xl border border-gray-100 px-3 py-2">
            <div class="flex items-start justify-between gap-2">
              <p class="text-sm font-medium text-gray-900">{{ $row->jenisPelanggaranLabel ?? '-' }}</p>
              <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700">{{ optional($row->tanggal)->translatedFormat('d M Y') }}</span>
            </div>
            <p class="mt-1 text-xs text-gray-600">{{ $row->kafarah ?? '-' }}</p>
            <p class="mt-1 text-xs text-gray-500">Setor: {{ $row->jumlah_setor }} | Tanggungan: {{ $row->tanggungan }}</p>
          </div>
        @empty
          <div class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500">Belum ada data kafarah.</div>
        @endforelse
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Log Progress Keilmuan</h2>
          <p class="text-sm text-gray-500">Update materi Al-Quran dan Al-Hadits terbaru.</p>
        </div>
        @if($isSantriContext)
          <a href="{{ route('santri.data.progres') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
        @else
          <span class="text-xs font-semibold text-gray-400">Hanya akun santri</span>
        @endif
      </div>
      <div class="mt-4 grid grid-cols-5 gap-2 text-center text-xs">
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Total</p><p class="font-semibold text-gray-900">{{ $progressStats['total'] }}</p></div>
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Selesai</p><p class="font-semibold text-gray-900">{{ $progressStats['completed'] }}</p></div>
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Proses</p><p class="font-semibold text-gray-900">{{ $progressStats['in_progress'] }}</p></div>
        <div class="rounded-lg bg-gray-50 p-2"><p class="text-gray-500">Quran</p><p class="font-semibold text-gray-900">{{ $progressStats['quran'] }}</p></div>
        <div class="rounded-lg bg-emerald-50 p-2"><p class="text-emerald-700">Avg</p><p class="font-semibold text-emerald-700">{{ $progressStats['average'] }}%</p></div>
      </div>
      <div class="mt-4 space-y-2">
        @forelse($progressRecent as $row)
          @php
            $levelLabel = $row->level === \App\Models\ProgressKeilmuan::LEVEL_HADITS ? 'Al-Hadits' : 'Al-Quran';
            $percent = (int) ($row->persentase ?? 0);
          @endphp
          <div class="rounded-xl border border-gray-100 px-3 py-2">
            <div class="flex items-start justify-between gap-2">
              <p class="text-sm font-medium text-gray-900">{{ $row->judul }}</p>
              <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">{{ $levelLabel }}</span>
            </div>
            <p class="mt-1 text-xs text-gray-500">
              {{ $row->capaian }}/{{ $row->target }} {{ $row->satuan ?? 'halaman' }} - {{ optional($row->updated_at)->translatedFormat('d M Y H:i') }}
            </p>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
              <div class="h-full rounded-full bg-emerald-500" style="width: {{ $percent }}%"></div>
            </div>
          </div>
        @empty
          <div class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500">Belum ada log progress keilmuan.</div>
        @endforelse
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Log Keluar/Masuk</h2>
          <p class="text-sm text-gray-500">Status izin keluar/masuk terbaru Anda.</p>
        </div>
        @if($isSantriContext)
          <a href="{{ route('santri.data.log', ['mode' => 'mine']) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
        @else
          <span class="text-xs font-semibold text-gray-400">Hanya akun santri</span>
        @endif
      </div>
      <div class="mt-4 flex flex-wrap gap-2">
        @foreach(['tercatat'] as $status)
          <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $logStatusStyles[$status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
            {{ ucfirst($status) }}: {{ $logStats[$status] ?? 0 }}
          </span>
        @endforeach
      </div>
      <div class="mt-4 space-y-2">
        @forelse($logRecent as $row)
          @php
            $status = strtolower((string) $row->status);
            $badgeClass = $logStatusStyles[$status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
          @endphp
          <div class="rounded-xl border border-gray-100 px-3 py-2">
            <div class="flex items-start justify-between gap-2">
              <p class="text-sm font-medium text-gray-900">{{ $row->jenis }}</p>
              <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $badgeClass }}">{{ ucfirst($status) }}</span>
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ optional($row->tanggal_pengajuan)->translatedFormat('d M Y') }} - {{ $row->rentang }}</p>
            @if($row->catatan)
              <p class="mt-1 text-xs text-gray-600">{{ $row->catatan }}</p>
            @endif
          </div>
        @empty
          <div class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500">Belum ada log keluar/masuk.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
