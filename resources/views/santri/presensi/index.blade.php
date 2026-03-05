@extends('layouts.santri-modern')
@section('title', $mode === 'team' ? 'Kehadiran Santri' : ($canManage ? 'Manajemen Kehadiran' : 'Kehadiran Saya'))

@section('content')
<div class="space-y-3.5">
  @if(session('success'))
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4">
      <div class="toast-banner relative w-full max-w-3xl" data-autohide="true">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 text-sm shadow-md shadow-emerald-100/70">
          {{ session('success') }}
        </div>
      </div>
    </div>
  @endif
  @if ($errors->any())
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4">
      <div class="toast-banner relative w-full max-w-3xl" data-autohide="true">
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-rose-800 text-sm shadow-md shadow-rose-100/70 space-y-1">
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      </div>
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
            <a href="{{ route('santri.presensi.index', ['mode' => 'input', 'gender' => $val]) }}" wire:navigate
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

  @if($mode === 'team')
  {{-- Kehadiran Santri View - Matching Figma Design --}}
  <div class="space-y-3.5">
    {{-- Page Header --}}
    <div class="space-y-2">
      <div class="text-xs text-gray-500">
        <span>Dashboard</span>
        <span class="mx-2">›</span>
        <span class="text-gray-900">Kehadiran Santri</span>
      </div>
      @php
        $genderSwitchQuery = request()->query();
        unset($genderSwitchQuery['page']);
      @endphp
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-[20px] font-semibold text-gray-900">Kehadiran Santri</h1>
          <p class="text-[10px] text-gray-600 mt-0.5">Lorem ipsum dolor sit amet, consectetur. Volutpat tellus facilisi nulla commodo non libero quis.</p>
          <div class="mt-2 inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-1">
            @foreach(['all' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri'] as $genderKey => $genderLabel)
              <a href="{{ route('santri.presensi.index', array_merge($genderSwitchQuery, ['mode' => 'team', 'gender_filter' => $genderKey])) }}" wire:navigate
                 class="inline-flex min-w-[66px] items-center justify-center rounded-md px-3 py-1.5 text-xs font-semibold transition {{ ($genderFilter ?? 'all') === $genderKey ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }}">
                {{ $genderLabel }}
              </a>
            @endforeach
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <form id="team-search-form" method="GET" action="{{ route('santri.presensi.index') }}">
              <input type="hidden" name="mode" value="{{ $mode }}" />
              <input type="hidden" name="gender_filter" value="{{ $genderFilter ?? 'all' }}" />
              <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
              <input id="search-input" name="search" value="{{ $search }}" placeholder="Cari santri" 
                     class="rounded-lg border border-gray-200 bg-white pl-8 pr-3 py-2 text-sm placeholder:text-gray-400 focus:ring-1 focus:ring-emerald-600/20 focus:border-emerald-600 transition-all w-72" />
            </form>
          </div>
          @if($canTeamFilter ?? false)
          <button type="button" id="filter-button-team"
                  class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm relative">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Filter
            @php
              $activeFiltersCount = count($statusFilter) + count($kategoriFilter) + count($waktuFilter) + count($timFilter) + ($tanggalFilter ? 1 : 0) + (($genderFilter ?? 'all') !== 'all' ? 1 : 0);
            @endphp
            @if($activeFiltersCount > 0)
              <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-semibold text-white">{{ $activeFiltersCount }}</span>
            @endif
          </button>
          @endif
          @if(($canInput ?? false))
            <a href="{{ route('santri.presensi.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Input Kehadiran
            </a>
          @endif
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Nama</span>
                  <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Tim</span>
                  <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Tanggal</span>
                  <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Kategori</span>
                  <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Waktu</span>
                  <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Status</span>
                  <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Catatan</span>
                  <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
                </div>
              </th>
              @if($canManage)
                <th class="px-3 py-1.5 w-10 text-center"></th>
              @endif
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            @forelse($presensis as $row)
              <tr class="hover:bg-gray-50 border-b border-gray-200">
                <td class="px-3 py-1.5">
                  <span class="font-medium text-[10px] leading-3 text-gray-900">{{ $row->nama }}</span>
                </td>
                <td class="px-3 py-1.5">
                  @php $team = $row->santri?->tim_resolved ?? $row->santri?->tim ?? '-'; @endphp
                  <span class="text-gray-600 text-[10px] leading-3">{{ $team }}</span>
                </td>
                <td class="px-3 py-1.5">
                  <span class="text-gray-600 text-[10px] leading-3">{{ optional($row->sesi?->tanggal)->format('d M Y') ?? $row->created_at->format('d M Y') }}</span>
                </td>
                <td class="px-3 py-1.5">
                  <span class="text-gray-600 text-[10px] leading-3">{{ ucfirst($row->kegiatan->kategori ?? 'Sambung') }}</span>
                </td>
                <td class="px-3 py-1.5">
                  <span class="text-gray-600 text-[10px] leading-3">{{ ucfirst($row->waktu) }}</span>
                </td>
                <td class="px-3 py-1.5">
                  @php
                    $statusColors = [
                      'hadir' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700'],
                      'izin' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-700'],
                      'sakit' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700'],
                      'alpha' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700'],
                    ];
                    $colors = $statusColors[strtolower($row->status)] ?? $statusColors['hadir'];
                  @endphp
                  <span class="inline-flex items-center rounded-full border px-1.5 py-0.5 text-[9px] font-medium {{ $colors['bg'] }} {{ $colors['border'] }} {{ $colors['text'] }}">
                    {{ ucfirst($row->status) }}
                  </span>
                </td>
                <td class="px-3 py-1.5">
                  <span class="text-gray-600 text-[10px] leading-3">{{ $row->catatan ?? '-' }}</span>
                </td>
                @if($canManage)
                  <td class="px-3 py-1.5 text-center">
                    <button type="button"
                            class="text-gray-400 hover:text-gray-600 p-1 action-menu-button"
                            data-id="{{ $row->id }}"
                            data-tanggal="{{ optional($row->sesi?->tanggal)->format('Y-m-d') ?? $row->created_at->format('Y-m-d') }}"
                            data-kategori="{{ $row->kegiatan->kategori ?? '' }}"
                            data-waktu="{{ $row->waktu }}"
                            data-status="{{ $row->status }}"
                            data-catatan="{{ $row->catatan }}">
                      <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                  </td>
                @endif
              </tr>
            @empty
              <tr>
                <td colspan="{{ $canManage ? 8 : 7 }}" class="px-3 py-8 text-center text-gray-500 text-xs">
                  Belum ada data kehadiran.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($presensis->hasPages())
        <div class="border-t border-gray-200 px-3 py-2.5 flex items-center justify-between">
          @if($presensis->onFirstPage())
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-medium text-gray-400 shadow-sm cursor-not-allowed" disabled>
              <i data-lucide="arrow-left" class="w-3 h-3"></i>
              Previous
            </button>
          @else
            <a href="{{ $presensis->previousPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-medium text-gray-700 shadow-sm hover:bg-gray-50">
              <i data-lucide="arrow-left" class="w-3 h-3"></i>
              Previous
            </a>
          @endif

          <div class="flex gap-1">
            @if(method_exists($presensis, 'lastPage'))
              @php
                $current = $presensis->currentPage();
                $last = $presensis->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
              @endphp

              @if($start > 1)
                <a href="{{ $presensis->url(1) }}" class="rounded-lg px-2.5 py-1.5 text-[10px] font-medium text-gray-600 hover:bg-gray-50">
                  1
                </a>
                @if($start > 2)
                  <span class="px-2 py-1.5 text-[10px] text-gray-400">...</span>
                @endif
              @endif

              @foreach(range($start, $end) as $page)
                <a href="{{ $presensis->url($page) }}" 
                  class="rounded-lg px-2.5 py-1.5 text-[10px] font-medium {{ $current === $page ? 'bg-emerald-100 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' }}">
                  {{ $page }}
                </a>
              @endforeach

              @if($end < $last)
                @if($end < $last - 1)
                  <span class="px-2 py-1.5 text-[10px] text-gray-400">...</span>
                @endif
                <a href="{{ $presensis->url($last) }}" class="rounded-lg px-2.5 py-1.5 text-[10px] font-medium text-gray-600 hover:bg-gray-50">
                  {{ $last }}
                </a>
              @endif
            @else
              <span class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[10px] font-medium text-gray-600">
                Halaman {{ $presensis->currentPage() }}
              </span>
            @endif
          </div>

          @if($presensis->hasMorePages())
            <a href="{{ $presensis->nextPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
              Next
              <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
          @else
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-medium text-gray-400 shadow-sm cursor-not-allowed" disabled>
              Next
              <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </button>
          @endif
        </div>
      @endif
    </div>
  </div>
  @elseif($mode === 'mine' || ($canManage && $mode !== 'input'))

  {{-- Kehadiran Saya View - Matching Figma Design --}}
  <div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="text-sm text-gray-500">
      <span>Dashboard</span>
      <span class="mx-2">›</span>
      <span class="text-gray-900">Kehadiran Saya</span>
    </div>

    {{-- Page Header --}}
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 leading-8">Kehadiran Saya</h1>
      <p class="text-xs text-gray-600 mt-1 leading-relaxed">Lorem ipsum dolor sit amet consectetur. Volutpat tellus facilisi nulla commodo non libero quis.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-6 gap-3">
      {{-- Total Sesi --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Total Sesi</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['total_pertemuan'] }}</p>
      </div>

      {{-- Hadir --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Hadir</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['hadir'] }}</p>
      </div>

      {{-- Izin --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Izin</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['izin'] }}</p>
      </div>

      {{-- Sakit --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Sakit</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['sakit'] }}</p>
      </div>

      {{-- Alpa --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Alpa</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['alpa'] }}</p>
      </div>

      {{-- Persentase Kehadiran --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Persentase Kehadiran</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['persentase'] }}%</p>
      </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-[1fr_375px] gap-4">
      {{-- Table Section --}}
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        {{-- Table Header with Filters --}}
        <div class="border-b border-gray-200 px-4 py-3.5">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900 leading-6">Riwayat Keseluruhan Kehadiran</h2>
            </div>
            <div class="flex items-center gap-2">
              <div class="relative">
                <form method="GET" action="{{ route('santri.presensi.index') }}">
                  <input type="hidden" name="mode" value="mine" />
                  <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                  <input id="search-input-mine" name="search" value="{{ $search }}" placeholder="Cari..."
                         class="rounded-lg border border-gray-200 bg-white pl-8 pr-2.5 py-1.5 text-xs placeholder:text-gray-400 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 w-56" />
                </form>
              </div>
              <button type="button" id="filter-button-mine"
                      class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 shadow-sm relative">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                Filter
                @php
                  $activeFiltersMine = (request('status_filter_mine') ? count((array)request('status_filter_mine')) : 0) + 
                                       (request('kategori_filter_mine') ? count((array)request('kategori_filter_mine')) : 0) + 
                                       (request('waktu_filter_mine') ? count((array)request('waktu_filter_mine')) : 0) + 
                                       (request('tanggal_mine') ? 1 : 0) + 
                                       (request('bulan_mine') ? 1 : 0);
                @endphp
                @if($activeFiltersMine > 0)
                  <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-semibold text-white">{{ $activeFiltersMine }}</span>
                @endif
              </button>
            </div>
          </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  <div class="flex items-center gap-0.5">
                    Tanggal
                    <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-400"></i>
                  </div>
                </th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  <div class="flex items-center gap-0.5">
                    Kategori
                    <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-400"></i>
                  </div>
                </th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  <div class="flex items-center gap-0.5">
                    Waktu
                    <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-400"></i>
                  </div>
                </th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  <div class="flex items-center gap-0.5">
                    Status Kehadiran
                    <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-400"></i>
                  </div>
                </th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  <div class="flex items-center gap-0.5">
                    Catatan
                    <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-400"></i>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($presensis as $row)
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 text-xs font-medium text-gray-900">
                    {{ optional($row->sesi?->tanggal)->translatedFormat('d M Y') ?? $row->created_at->translatedFormat('d M Y') }}
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-600">
                    {{ ucfirst($row->kegiatan->kategori ?? 'Sambung') }}
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-600">
                    {{ ucfirst($row->waktu) }}
                  </td>
                  <td class="px-4 py-3">
                    @php
                      $statusColors = [
                        'hadir' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700'],
                        'izin' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-700'],
                        'sakit' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700'],
                        'alpha' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700'],
                      ];
                      $colors = $statusColors[strtolower($row->status)] ?? $statusColors['hadir'];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs font-medium {{ $colors['bg'] }} {{ $colors['border'] }} {{ $colors['text'] }}">
                      <span class="w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', $colors['text']) }}"></span>
                      {{ ucfirst($row->status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $row->catatan ?? '-' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center gap-1.5">
                      <i data-lucide="inbox" class="w-10 h-10 text-gray-300"></i>
                      <p class="text-xs font-medium text-gray-500">Belum ada data kehadiran.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if($presensis->hasPages())
          <div class="border-t border-gray-200 px-4 py-3 flex items-center justify-between bg-gray-50">
            @if($presensis->onFirstPage())
              <button class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-400 shadow-sm cursor-not-allowed" disabled>
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Previous
              </button>
            @else
              <a href="{{ $presensis->previousPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Previous
              </a>
            @endif

            <div class="flex gap-1.5">
              @php
                $current = $presensis->currentPage();
                $last = $presensis->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
              @endphp

              @if($start > 1)
                <a href="{{ $presensis->url(1) }}" class="min-w-[36px] h-9 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-white hover:border hover:border-gray-200 transition-all flex items-center justify-center">1</a>
                @if($start > 2)
                  <span class="px-1.5 py-1.5 text-xs text-gray-400">...</span>
                @endif
              @endif

              @foreach(range($start, $end) as $page)
                <a href="{{ $presensis->url($page) }}" 
                   class="min-w-[36px] h-9 rounded-lg px-2.5 py-1.5 text-xs font-medium transition-all flex items-center justify-center {{ $current === $page ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-gray-700 hover:bg-white hover:border hover:border-gray-200' }}">
                  {{ $page }}
                </a>
              @endforeach

              @if($end < $last)
                @if($end < $last - 1)
                  <span class="px-1.5 py-1.5 text-xs text-gray-400">...</span>
                @endif
                <a href="{{ $presensis->url($last) }}" class="min-w-[36px] h-9 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-white hover:border hover:border-gray-200 transition-all flex items-center justify-center">{{ $last }}</a>
              @endif
            </div>

            @if($presensis->hasMorePages())
              <a href="{{ $presensis->nextPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                Next
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
              </a>
            @else
              <button class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-400 shadow-sm cursor-not-allowed" disabled>
                Next
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
              </button>
            @endif
          </div>
        @endif
      </div>

      {{-- Latest Updates Sidebar --}}
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden h-fit">
        {{-- Header --}}
        <div class="border-b border-gray-200 px-3.5 py-3">
          <h3 class="text-base font-semibold text-gray-900 leading-6">Update Terbaru</h3>
        </div>

        {{-- Updates List --}}
        <div class="divide-y divide-gray-200">
          @forelse($latestUpdates as $update)
            <div class="px-3.5 py-3">
              <div class="flex items-start justify-between gap-2 mb-1">
                <p class="text-sm font-semibold text-gray-900 flex-1 leading-5">
                  {{ ucfirst($update->kegiatan->kategori ?? 'Sambung') }} {{ ucfirst($update->waktu) }}
                </p>
                @php
                  $statusBadgeColors = [
                    'hadir' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-600'],
                    'izin' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-700', 'dot' => 'bg-orange-600'],
                    'sakit' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700', 'dot' => 'bg-gray-600'],
                    'alpha' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'dot' => 'bg-red-600'],
                  ];
                  $badgeColors = $statusBadgeColors[strtolower($update->status)] ?? $statusBadgeColors['hadir'];
                @endphp
                <span class="inline-flex items-center gap-1 rounded-full border px-1.5 py-0.5 text-[10px] font-medium {{ $badgeColors['bg'] }} {{ $badgeColors['border'] }} {{ $badgeColors['text'] }} whitespace-nowrap">
                  <span class="w-1 h-1 rounded-full {{ $badgeColors['dot'] }}"></span>
                  {{ ucfirst($update->status) }}
                </span>
              </div>
              <p class="text-xs font-medium text-gray-500 leading-tight">
                {{ optional($update->sesi?->tanggal)->translatedFormat('l, d F Y') ?? $update->created_at->translatedFormat('l, d F Y') }}
              </p>
            </div>
          @empty
            <div class="px-3.5 py-10 text-center">
              <div class="flex flex-col items-center gap-1.5">
                <i data-lucide="clock" class="w-8 h-8 text-gray-300"></i>
                <p class="text-xs font-medium text-gray-500">Belum ada update terbaru.</p>
              </div>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  @endif
</div>

{{-- Filter Slide-out Panel for Mine Mode --}}
@if($mode === 'mine')
<div id="filter-panel-mine" class="fixed inset-0 z-50 hidden">
  {{-- Backdrop --}}
  <div id="filter-backdrop-mine" class="absolute inset-0 bg-gray-900/50"></div>
  
  {{-- Slide Panel --}}
  <div id="filter-slide-mine" class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-xl transition-transform duration-300 ease-in-out transform translate-x-full flex flex-col">
    {{-- Header --}}
    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
      <button type="button" id="close-filter-mine" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
      {{-- Tanggal --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Tanggal</h3>
        <div class="relative">
          <input type="date" id="filter-tanggal-mine" value="{{ request('tanggal_mine', '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
        </div>
      </div>

      {{-- Bulan --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Bulan</h3>
        <div class="relative">
          <input type="month" id="filter-bulan-mine" value="{{ request('bulan_mine', '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
        </div>
      </div>

      {{-- Status Kehadiran --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Status Kehadiran</h3>
        <div class="space-y-2.5 pl-2">
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_mine[]" value="hadir" {{ in_array('hadir', (array)request('status_filter_mine', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-xs font-medium border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
              Hadir
            </span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_mine[]" value="izin" {{ in_array('izin', (array)request('status_filter_mine', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-orange-50 text-orange-700 text-xs font-medium border border-orange-200">
              <span class="w-1.5 h-1.5 rounded-full bg-orange-600"></span>
              Izin
            </span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_mine[]" value="sakit" {{ in_array('sakit', (array)request('status_filter_mine', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-gray-50 text-gray-700 text-xs font-medium border border-gray-200">
              <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
              Sakit
            </span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_mine[]" value="alpha" {{ in_array('alpha', (array)request('status_filter_mine', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-red-50 text-red-700 text-xs font-medium border border-red-200">
              <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
              Alpa
            </span>
          </label>
        </div>
      </div>

      {{-- Kategori --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Kategori</h3>
        <div class="space-y-2.5 pl-2">
          @foreach(['sambung' => 'Sambung', 'asrama' => 'Asrama'] as $val => $label)
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="filter_kategori_mine[]" value="{{ $val }}" {{ in_array($val, (array)request('kategori_filter_mine', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
              <span class="text-sm text-gray-700">{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Waktu --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Waktu</h3>
        <div class="space-y-2.5 pl-2">
          @foreach(['subuh' => 'Subuh', 'pagi' => 'Pagi', 'siang' => 'Siang', 'sore' => 'Sore', 'malam' => 'Malam'] as $val => $label)
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="filter_waktu_mine[]" value="{{ $val }}" {{ in_array($val, (array)request('waktu_filter_mine', [])) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
              <span class="text-sm text-gray-700">{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Footer --}}
    <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between bg-gray-50">
      <button type="button" id="clear-filters-mine" class="text-sm font-medium text-gray-700 hover:text-gray-900">
        Clear all
      </button>
      <div class="flex gap-3">
        <button type="button" id="cancel-filter-mine" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">
          Cancel
        </button>
        <button type="button" id="apply-filter-mine" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
          Apply
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Filter Panel Logic for Mine Mode
    const filterPanelMine = document.getElementById('filter-panel-mine');
    const filterSlideMine = document.getElementById('filter-slide-mine');
    const filterButtonMine = document.getElementById('filter-button-mine');
    const closeFilterMine = document.getElementById('close-filter-mine');
    const cancelFilterMine = document.getElementById('cancel-filter-mine');
    const applyFilterMine = document.getElementById('apply-filter-mine');
    const clearFiltersMine = document.getElementById('clear-filters-mine');
    const filterBackdropMine = document.getElementById('filter-backdrop-mine');
    const searchInputMine = document.getElementById('search-input-mine');

    if (!filterPanelMine) return;

    const openFilterPanelMine = () => {
      filterPanelMine.classList.remove('hidden');
      setTimeout(() => {
        filterSlideMine.classList.remove('translate-x-full');
      }, 10);
      document.body.style.overflow = 'hidden';
    };

    const closeFilterPanelMine = () => {
      filterSlideMine.classList.add('translate-x-full');
      setTimeout(() => {
        filterPanelMine.classList.add('hidden');
        document.body.style.overflow = '';
      }, 300);
    };

    if (filterButtonMine) {
      filterButtonMine.addEventListener('click', openFilterPanelMine);
    }

    if (closeFilterMine) {
      closeFilterMine.addEventListener('click', closeFilterPanelMine);
    }

    if (cancelFilterMine) {
      cancelFilterMine.addEventListener('click', closeFilterPanelMine);
    }

    if (filterBackdropMine) {
      filterBackdropMine.addEventListener('click', closeFilterPanelMine);
    }

    if (clearFiltersMine) {
      clearFiltersMine.addEventListener('click', () => {
        const checkboxes = filterPanelMine.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = false);
        document.getElementById('filter-tanggal-mine').value = '';
        document.getElementById('filter-bulan-mine').value = '';
      });
    }

    if (applyFilterMine) {
      applyFilterMine.addEventListener('click', () => {
        const params = new URLSearchParams();
        params.append('mode', 'mine');

        // Get search value
        if (searchInputMine && searchInputMine.value.trim()) {
          params.append('search_mine', searchInputMine.value.trim());
        }

        // Get all checked filters
        const statusFilters = Array.from(filterPanelMine.querySelectorAll('input[name="filter_status_mine[]"]:checked')).map(cb => cb.value);
        const kategoriFilters = Array.from(filterPanelMine.querySelectorAll('input[name="filter_kategori_mine[]"]:checked')).map(cb => cb.value);
        const waktuFilters = Array.from(filterPanelMine.querySelectorAll('input[name="filter_waktu_mine[]"]:checked')).map(cb => cb.value);
        const tanggalFilter = document.getElementById('filter-tanggal-mine')?.value || '';
        const bulanFilter = document.getElementById('filter-bulan-mine')?.value || '';

        statusFilters.forEach(s => params.append('status_filter_mine[]', s));
        kategoriFilters.forEach(k => params.append('kategori_filter_mine[]', k));
        waktuFilters.forEach(w => params.append('waktu_filter_mine[]', w));
        if (tanggalFilter) {
          params.append('tanggal_mine', tanggalFilter);
        }
        if (bulanFilter) {
          params.append('bulan_mine', bulanFilter);
        }

        window.location.href = `{{ route('santri.presensi.index') }}?${params.toString()}`;
      });
    }

    // Handle search input for mine mode
    if (searchInputMine) {
      searchInputMine.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          const params = new URLSearchParams(window.location.search);
          params.set('mode', 'mine');
          if (searchInputMine.value.trim()) {
            params.set('search_mine', searchInputMine.value.trim());
          } else {
            params.delete('search_mine');
          }
          window.location.href = `{{ route('santri.presensi.index') }}?${params.toString()}`;
        }
      });
    }
  });
</script>
@endif

{{-- Filter Slide-out Panel for Team Mode --}}
@if($mode === 'team')
<div id="filter-panel-team" class="fixed inset-0 z-50 hidden">
  {{-- Backdrop --}}
  <div id="filter-backdrop-team" class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
  
  {{-- Panel --}}
  <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col transform transition-transform duration-300 translate-x-full" id="filter-slide-team">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5">
      <div>
        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
        <p class="text-sm text-gray-500 mt-0.5">Apply filters to table data.</p>
      </div>
      <button type="button" id="close-filter-team" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
      {{-- Tanggal --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Tanggal</h3>
        <div class="relative">
          <input type="date" id="filter-tanggal" value="{{ $tanggalFilter }}" class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
        </div>
      </div>

      {{-- Status Kehadiran --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Status Kehadiran</h3>
        <div class="space-y-2.5 pl-2">
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_team[]" value="hadir" {{ in_array('hadir', $statusFilter) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-xs font-medium border border-emerald-200">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
              Hadir
            </span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_team[]" value="izin" {{ in_array('izin', $statusFilter) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200">
              <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
              Izin
            </span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_team[]" value="sakit" {{ in_array('sakit', $statusFilter) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-xs font-medium border border-amber-200">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
              Sakit
            </span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="filter_status_team[]" value="alpha" {{ in_array('alpha', $statusFilter) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-red-50 text-red-700 text-xs font-medium border border-red-200">
              <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
              Alpa
            </span>
          </label>
        </div>
      </div>

      {{-- Kategori --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Kategori</h3>
        <div class="space-y-2.5 pl-2">
          @foreach($kategoriOptions as $kategori)
            <label class="flex items-center gap-2.5 cursor-pointer">
              <input type="checkbox" name="filter_kategori_team[]" value="{{ $kategori }}" {{ in_array($kategori, $kategoriFilter) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
              <span class="text-sm text-gray-700">{{ ucfirst($kategori) }}</span>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Waktu --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Waktu</h3>
        <div class="space-y-2.5 pl-2">
          @foreach($waktuOptions as $waktu)
            <label class="flex items-center gap-2.5 cursor-pointer">
              <input type="checkbox" name="filter_waktu_team[]" value="{{ $waktu }}" {{ in_array($waktu, $waktuFilter) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
              <span class="text-sm text-gray-700">{{ ucfirst($waktu) }}</span>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Tim --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Tim</h3>
        <div class="space-y-2.5 pl-2">
          @foreach(['PH', 'Sekben', 'Acara', 'KTB'] as $tim)
            <label class="flex items-center gap-2.5 cursor-pointer">
              <input type="checkbox" name="filter_tim_team[]" value="{{ $tim }}" {{ in_array($tim, $timFilter) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
              <span class="text-sm text-gray-700">{{ $tim }}</span>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Gender (Putra/Putri) --}}
      <div class="space-y-3">
        <h3 class="text-sm font-medium text-gray-900">Gender</h3>
        <div class="space-y-2.5 pl-2">
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="radio" name="filter_gender_team" value="all" {{ $genderFilter === 'all' ? 'checked' : '' }} class="w-4 h-4 border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-gray-700">Semua</span>
          </label>
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="radio" name="filter_gender_team" value="putra" {{ $genderFilter === 'putra' ? 'checked' : '' }} class="w-4 h-4 border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-gray-700">Putra</span>
          </label>
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="radio" name="filter_gender_team" value="putri" {{ $genderFilter === 'putri' ? 'checked' : '' }} class="w-4 h-4 border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-gray-700">Putri</span>
          </label>
        </div>
      </div>
    </div>

    {{-- Footer --}}
    <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between bg-gray-50">
      <button type="button" id="clear-filters-team" class="text-sm font-medium text-gray-700 hover:text-gray-900">
        Clear all
      </button>
      <div class="flex gap-3">
        <button type="button" id="cancel-filter-team" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">
          Cancel
        </button>
        <button type="button" id="apply-filter-team" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
          Apply
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Filter Panel Logic for Team Mode
    const filterPanel = document.getElementById('filter-panel-team');
    const filterSlide = document.getElementById('filter-slide-team');
    const filterButton = document.getElementById('filter-button-team');
    const closeFilter = document.getElementById('close-filter-team');
    const cancelFilter = document.getElementById('cancel-filter-team');
    const applyFilter = document.getElementById('apply-filter-team');
    const clearFilters = document.getElementById('clear-filters-team');
    const filterBackdrop = document.getElementById('filter-backdrop-team');
    const teamSearchForm = document.getElementById('team-search-form');
    const searchInput = document.getElementById('search-input');
    const searchInputMine = document.getElementById('search-input-mine');

    if (!filterPanel) return;

    const openFilterPanel = () => {
      filterPanel.classList.remove('hidden');
      setTimeout(() => {
        filterSlide.classList.remove('translate-x-full');
      }, 10);
      document.body.style.overflow = 'hidden';
    };

    const closeFilterPanel = () => {
      filterSlide.classList.add('translate-x-full');
      setTimeout(() => {
        filterPanel.classList.add('hidden');
        document.body.style.overflow = '';
      }, 300);
    };

    if (filterButton) {
      filterButton.addEventListener('click', openFilterPanel);
    }

    if (closeFilter) {
      closeFilter.addEventListener('click', closeFilterPanel);
    }

    if (cancelFilter) {
      cancelFilter.addEventListener('click', closeFilterPanel);
    }

    if (filterBackdrop) {
      filterBackdrop.addEventListener('click', closeFilterPanel);
    }

    if (clearFilters) {
      clearFilters.addEventListener('click', () => {
        const checkboxes = filterPanel.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = false);
        const radios = filterPanel.querySelectorAll('input[name="filter_gender_team"]');
        radios.forEach(r => r.checked = r.value === 'all');
        document.getElementById('filter-tanggal').value = '';
      });
    }

    if (applyFilter) {
      applyFilter.addEventListener('click', () => {
        const params = new URLSearchParams();
        params.append('mode', 'team');

        // Get search value
        if (searchInput && searchInput.value.trim()) {
          params.append('search', searchInput.value.trim());
        }

        // Get all checked filters
        const statusFilters = Array.from(filterPanel.querySelectorAll('input[name="filter_status_team[]"]:checked')).map(cb => cb.value);
        const kategoriFilters = Array.from(filterPanel.querySelectorAll('input[name="filter_kategori_team[]"]:checked')).map(cb => cb.value);
        const waktuFilters = Array.from(filterPanel.querySelectorAll('input[name="filter_waktu_team[]"]:checked')).map(cb => cb.value);
        const timFilters = Array.from(filterPanel.querySelectorAll('input[name="filter_tim_team[]"]:checked')).map(cb => cb.value);
        const genderFilter = filterPanel.querySelector('input[name="filter_gender_team"]:checked')?.value || 'all';
        const tanggalFilter = document.getElementById('filter-tanggal')?.value || '';

        statusFilters.forEach(s => params.append('status_filter[]', s));
        kategoriFilters.forEach(k => params.append('kategori_filter[]', k));
        waktuFilters.forEach(w => params.append('waktu_filter[]', w));
        timFilters.forEach(t => params.append('tim_filter[]', t));
        params.append('gender_filter', genderFilter);
        if (tanggalFilter) {
          params.append('tanggal', tanggalFilter);
        }

        window.location.href = `{{ route('santri.presensi.index') }}?${params.toString()}`;
      });
    }

    // Handle search input (team mode)
    const applyTeamSearch = () => {
      const params = new URLSearchParams(window.location.search);
      params.set('mode', 'team');
      if (searchInput && searchInput.value.trim()) {
        params.set('search', searchInput.value.trim());
      } else {
        params.delete('search');
      }
      window.location.href = `{{ route('santri.presensi.index') }}?${params.toString()}`;
    };

    if (teamSearchForm) {
      teamSearchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        applyTeamSearch();
      });
    }

    // Handle search in Kehadiran Saya (mine)
    if (searchInputMine) {
      searchInputMine.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          const params = new URLSearchParams(window.location.search);
          params.set('mode', 'mine');
          if (searchInputMine.value.trim()) {
            params.set('search', searchInputMine.value.trim());
          } else {
            params.delete('search');
          }
          window.location.href = `{{ route('santri.presensi.index') }}?${params.toString()}`;
        }
      });
    }

    // Auto-hide toast banners (5s)
    document.querySelectorAll('.toast-banner[data-autohide]').forEach(toast => {
      const hide = () => {
        toast.classList.add('opacity-0', 'translate-y-2', 'transition', 'duration-500', 'ease-out');
        setTimeout(() => toast.remove(), 500);
      };
      setTimeout(hide, 2000);
    });
  });
</script>
@endif

@if($mode === 'team' && $canManage)
{{-- Modal edit/hapus presensi --}}
<div id="edit-modal" class="fixed inset-0 bg-black/40 z-40 hidden items-center justify-center">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Edit Kehadiran</h3>
      <button type="button" id="close-edit-modal" class="text-gray-500 hover:text-gray-700">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <form id="edit-form" method="POST">
      @csrf
      @method('PATCH')
      <div class="space-y-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Tanggal</label>
          <input type="date" name="tanggal" id="edit-tanggal" class="w-full rounded-lg border-gray-300 text-sm" required>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Kategori</label>
          <select name="kategori" id="edit-kategori" class="w-full rounded-lg border-gray-300 text-sm" required>
            @foreach($kategoriOptions as $kategori)
              <option value="{{ $kategori }}">{{ ucfirst($kategori) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Waktu</label>
          <select name="waktu" id="edit-waktu" class="w-full rounded-lg border-gray-300 text-sm" required>
            @foreach($waktuOptions as $waktu)
              <option value="{{ $waktu }}">{{ ucfirst($waktu) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Status</label>
          <select name="status" id="edit-status" class="w-full rounded-lg border-gray-300 text-sm" required>
            @foreach($statuses as $status)
              <option value="{{ $status }}">{{ ucfirst($status) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Catatan</label>
          <input type="text" name="catatan" id="edit-catatan" class="w-full rounded-lg border-gray-300 text-sm">
        </div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" id="cancel-edit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
      </div>
    </form>
    <form id="delete-form" method="POST" class="hidden">
      @csrf
      @method('DELETE')
    </form>
    <div class="mt-2">
      <button type="button" id="delete-button" class="text-sm text-red-600 hover:text-red-700">Hapus Kehadiran</button>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('edit-modal');
    const closeEditModal = document.getElementById('close-edit-modal');
    const cancelEdit = document.getElementById('cancel-edit');
    const editForm = document.getElementById('edit-form');
    const deleteForm = document.getElementById('delete-form');
    const deleteButton = document.getElementById('delete-button');
    const fieldTanggal = document.getElementById('edit-tanggal');
    const fieldKategori = document.getElementById('edit-kategori');
    const fieldWaktu = document.getElementById('edit-waktu');
    const fieldStatus = document.getElementById('edit-status');
    const fieldCatatan = document.getElementById('edit-catatan');

    const openEditModal = (data) => {
      editForm.action = `{{ url('/santri/presensi') }}/${data.id}`;
      deleteForm.action = `{{ url('/santri/presensi') }}/${data.id}`;
      fieldTanggal.value = data.tanggal;
      fieldKategori.value = data.kategori || '';
      fieldWaktu.value = data.waktu || '';
      fieldStatus.value = data.status || '';
      fieldCatatan.value = data.catatan || '';
      editModal.classList.remove('hidden');
      editModal.classList.add('flex');
    };

    const closeEdit = () => {
      editModal.classList.add('hidden');
      editModal.classList.remove('flex');
    };

    document.querySelectorAll('.action-menu-button').forEach(btn => {
      btn.addEventListener('click', () => {
        openEditModal({
          id: btn.dataset.id,
          tanggal: btn.dataset.tanggal,
          kategori: btn.dataset.kategori,
          waktu: btn.dataset.waktu,
          status: btn.dataset.status,
          catatan: btn.dataset.catatan || ''
        });
      });
    });

    [closeEditModal, cancelEdit].forEach(el => el?.addEventListener('click', closeEdit));
    editModal?.addEventListener('click', (e) => { if (e.target === editModal) closeEdit(); });

    deleteButton?.addEventListener('click', () => {
      if (confirm('Hapus kehadiran ini?')) {
        deleteForm.submit();
      }
    });
  });
</script>
@endif

@endsection

