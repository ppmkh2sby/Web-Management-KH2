@extends('layouts.santri-modern')
@section('title', 'Input Kehadiran')

@section('content')
<div class="space-y-3.5">
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

  <form method="POST" action="{{ route('santri.presensi.store') }}">
    @csrf
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
      {{-- Main Content: Santri List --}}
      <div class="space-y-5">
        {{-- Header --}}
        <div class="space-y-2">
          <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('santri.dashboard') }}" class="hover:text-gray-800">Dashboard</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
            <span>Kehadiran Santri</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
            <span class="text-emerald-700 font-medium">Input Kehadiran Santri</span>
          </nav>

          <div class="flex items-start justify-between gap-4">
            <div>
              <h1 class="text-[22px] font-semibold text-gray-900">Input Kehadiran Santri</h1>
              <p class="text-sm text-gray-600 max-w-3xl mt-1">Lorem ipsum dolor sit amet consectetur. Volutpat tellus facilisis nulla commodo non tellus quis.</p>
            </div>
            <div class="flex items-center gap-2">
              @foreach(['putra'=>'Putra','putri'=>'Putri'] as $val => $label)
                <a href="{{ route('santri.presensi.create', ['gender' => $val]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium {{ $gender === $val ? 'bg-emerald-600 text-white border-emerald-600' : 'border-gray-200 text-gray-700 hover:border-emerald-300' }}">
                  {{ $label }}
                </a>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Santri Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Santri</h2>
            <div class="relative w-72">
              <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
              <input type="text"
                     placeholder="Cari santri"
                     class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-500 focus:ring-1 focus:ring-emerald-600/20 focus:border-emerald-600" />
            </div>
          </div>

          @php
            $statusLabels = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'];
          @endphp

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-5 py-3 text-left min-w-[360px]">
                    <div class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                      <input type="checkbox"
                             class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 accent-emerald-600"
                             id="select-all" />
                      <label for="select-all" class="cursor-pointer">Nama</label>
                    </div>
                  </th>
                  @foreach($statusLabels as $key => $label)
                    <th class="px-3 py-3 text-center w-24">
                      <div class="flex items-center justify-center gap-1.5">
                        <input type="checkbox"
                               class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 accent-emerald-600 status-select-all"
                               data-status="{{ $key }}" />
                        <span class="text-[11px] font-semibold text-gray-700">{{ $label }}</span>
                      </div>
                    </th>
                  @endforeach
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @forelse($santriList as $santri)
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3">
                      <div class="flex items-center gap-3">
                        <input type="checkbox"
                               class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 accent-emerald-600 santri-checkbox"
                               value="{{ $santri->id }}" />
                        <div class="flex flex-col">
                          <span class="text-xs font-semibold text-gray-900 leading-5">{{ $santri->nama_lengkap }}</span>
                          <span class="text-[11px] text-gray-500 mt-0.5">Tim: {{ $santri->tim_resolved ?? '-' }}</span>
                        </div>
                      </div>
                    </td>
                    @foreach(array_keys($statusLabels) as $status)
                      <td class="px-3 py-3 text-center">
                        <input type="radio"
                               name="presensi[{{ $santri->id }}]"
                               value="{{ $status }}"
                               class="h-4 w-4 cursor-pointer rounded-full border-2 border-gray-300 text-emerald-600 focus:ring-emerald-500 transition status-radio"
                               data-status="{{ $status }}" />
                      </td>
                    @endforeach
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                      Tidak ada data santri.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if($santriList->hasPages())
            <div class="border-t border-gray-200 px-3 py-2.5 flex items-center justify-between">
              @if($santriList->onFirstPage())
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-medium text-gray-400 shadow-sm cursor-not-allowed" disabled>
                  <i data-lucide="arrow-left" class="w-3 h-3"></i>
                  Previous
                </button>
              @else
                <a href="{{ $santriList->previousPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                  <i data-lucide="arrow-left" class="w-3 h-3"></i>
                  Previous
                </a>
              @endif

              <div class="flex gap-1">
                @php
                  $current = $santriList->currentPage();
                  $last = $santriList->lastPage();
                  $start = max(1, $current - 2);
                  $end = min($last, $current + 2);
                @endphp

                @if($start > 1)
                  <a href="{{ $santriList->url(1) }}" class="rounded-lg px-2.5 py-1.5 text-[10px] font-medium text-gray-600 hover:bg-gray-50">
                    1
                  </a>
                  @if($start > 2)
                    <span class="px-2 py-1.5 text-[10px] text-gray-400">...</span>
                  @endif
                @endif

                @foreach(range($start, $end) as $page)
                  <a href="{{ $santriList->url($page) }}" 
                     class="rounded-lg px-2.5 py-1.5 text-[10px] font-medium {{ $current === $page ? 'bg-emerald-100 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    {{ $page }}
                  </a>
                @endforeach

                @if($end < $last)
                  @if($end < $last - 1)
                    <span class="px-2 py-1.5 text-[10px] text-gray-400">...</span>
                  @endif
                  <a href="{{ $santriList->url($last) }}" class="rounded-lg px-2.5 py-1.5 text-[10px] font-medium text-gray-600 hover:bg-gray-50">
                    {{ $last }}
                  </a>
                @endif
              </div>

              @if($santriList->hasMorePages())
                <a href="{{ $santriList->nextPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                  Next
                  <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
              @else
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-[10px] font-medium text-gray-400 shadow-sm cursor-not-allowed" disabled>
                  Next
                  <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </button>
              @endif
            </div>
          @endif
        </div>
      </div>

      {{-- Right Side: Input Form --}}
      <div class="mt-6 xl:mt-0 xl:sticky xl:top-4 xl:self-start">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-4 xl:max-h-[calc(100vh-6rem)] xl:overflow-y-auto">
          {{-- Stats Cards --}}
          <div class="grid grid-cols-4 gap-2.5">
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 text-center">
              <p class="text-xs font-medium text-gray-600 mb-1.5">Hadir</p>
              <p class="text-2xl leading-[30px] font-semibold text-gray-900" id="stat-hadir">{{ $stats['hadir'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 text-center">
              <p class="text-xs font-medium text-gray-600 mb-1.5">Izin</p>
              <p class="text-2xl leading-[30px] font-semibold text-gray-900" id="stat-izin">{{ $stats['izin'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 text-center">
              <p class="text-xs font-medium text-gray-600 mb-1.5">Sakit</p>
              <p class="text-2xl leading-[30px] font-semibold text-gray-900" id="stat-sakit">{{ $stats['sakit'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 text-center">
              <p class="text-xs font-medium text-gray-600 mb-1.5">Alpa</p>
              <p class="text-2xl leading-[30px] font-semibold text-gray-900" id="stat-alpha">{{ $stats['alpha'] }}</p>
            </div>
          </div>

          {{-- Tanggal --}}
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
            <div class="relative">
              <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" 
                     class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-500 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600" 
                     placeholder="Pilih Tanggal"
                     required />
              <i data-lucide="calendar" class="w-5 h-5 text-gray-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
          </div>

          {{-- Kategori Kegiatan --}}
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700">Kategori Kegiatan</label>
            <div class="relative">
              <select name="kategori" 
                      class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 appearance-none" 
                      required>
                <option value="" disabled selected>Pilih Kategori</option>
                @foreach($kategoriOptions as $kategori)
                  <option value="{{ $kategori }}" {{ old('kategori') === $kategori ? 'selected' : '' }}>
                    {{ ucfirst($kategori) }}
                  </option>
                @endforeach
              </select>
              <i data-lucide="chevron-down" class="w-5 h-5 text-gray-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
          </div>

          {{-- Waktu --}}
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700">Waktu</label>
            <div class="relative">
              <select name="waktu" 
                      class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 appearance-none" 
                      required>
                <option value="" disabled selected>Pilih Waktu</option>
                @foreach($waktuOptions as $waktu)
                  <option value="{{ $waktu }}" {{ old('waktu') === $waktu ? 'selected' : '' }}>
                    {{ ucfirst($waktu) }}
                  </option>
                @endforeach
              </select>
              <i data-lucide="chevron-down" class="w-5 h-5 text-gray-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
          </div>

          {{-- Catatan --}}
          <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
            <textarea name="catatan" rows="6" 
                      placeholder="Masukkan catatan" 
                      class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-500 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 resize-none h-[120px]">{{ old('catatan') }}</textarea>
          </div>

          {{-- Submit Button --}}
          <button type="submit" 
                  class="w-full rounded-lg bg-emerald-600 border-2 border-white/10 px-3 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition-colors">
            Submit Kehadiran
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    const rowCheckboxes = Array.from(document.querySelectorAll('.santri-checkbox'));
    const statusHeaderCheckboxes = Array.from(document.querySelectorAll('.status-select-all'));
    const statusRadios = Array.from(document.querySelectorAll('.status-radio'));
    const statEls = {
      hadir: document.getElementById('stat-hadir'),
      izin: document.getElementById('stat-izin'),
      sakit: document.getElementById('stat-sakit'),
      alpha: document.getElementById('stat-alpha'),
    };

    if (!selectAll || rowCheckboxes.length === 0) return;

    const updateSummary = () => {
      const counts = { hadir: 0, izin: 0, sakit: 0, alpha: 0 };
      statusRadios.forEach(r => {
        if (r.checked && counts.hasOwnProperty(r.dataset.status)) {
          counts[r.dataset.status]++;
        }
      });
      Object.entries(statEls).forEach(([key, el]) => {
        if (el) el.textContent = counts[key] ?? 0;
      });
    };

    const syncHeaderCheckbox = () => {
      const checkedCount = rowCheckboxes.filter(cb => cb.checked).length;
      selectAll.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
      selectAll.checked = checkedCount === rowCheckboxes.length;
    };

    selectAll.addEventListener('change', () => {
      rowCheckboxes.forEach(cb => {
        cb.checked = selectAll.checked;
      });
      syncHeaderCheckbox();
    });

    rowCheckboxes.forEach(cb => cb.addEventListener('change', syncHeaderCheckbox));

    statusHeaderCheckboxes.forEach(headerCb => {
      headerCb.addEventListener('change', () => {
        const status = headerCb.dataset.status;
        if (headerCb.checked) {
          // Apply this status to all, uncheck the other header toggles
          statusRadios.forEach(radio => {
            if (radio.dataset.status === status) {
              radio.checked = true;
            }
          });
          statusHeaderCheckboxes.forEach(cb => {
            if (cb !== headerCb) cb.checked = false;
          });
        } else {
          // Clear this status for all rows
          statusRadios.forEach(radio => {
            if (radio.dataset.status === status) {
              radio.checked = false;
            }
          });
        }
        updateSummary();
      });
    });

    statusRadios.forEach(r => r.addEventListener('change', updateSummary));

    syncHeaderCheckbox();
    updateSummary();
  });
</script>
@endsection
