@extends('layouts.santri-modern')
@section('title', 'Input Kehadiran')
@section('content_panel_class', 'xl:h-[calc(100vh-40px)] xl:overflow-hidden')

@section('content')
@php
  $isDegur = $isDegur ?? false;
@endphp
<div class="space-y-3.5 h-full min-h-0 xl:flex xl:flex-col">
  @if(session('success'))
    <div id="success-toast" class="pointer-events-none fixed inset-x-0 top-4 z-[70] px-4">
      <div class="mx-auto w-full max-w-4xl rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-md shadow-emerald-100/70">
        {{ session('success') }}
      </div>
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 text-sm space-y-1">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif
  <div id="missing-warning" class="pointer-events-none fixed inset-x-0 top-4 z-50 hidden px-4">
    <div class="mx-auto w-full max-w-4xl rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 shadow-md shadow-amber-100/70"></div>
  </div>

  <form method="POST" action="{{ route('santri.presensi.store') }}" class="flex-1 min-h-0">
    @csrf
    @if(! $isDegur)
      <input type="hidden" name="gender_scope" value="{{ $gender }}">
    @endif
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6 h-full min-h-0">
      {{-- Main Content: Santri List --}}
      <div class="space-y-5 min-h-0 xl:flex xl:flex-col">
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
              <h1 class="text-[22px] font-semibold text-gray-900">{{ $isDegur ? 'Input Kehadiran Kelas' : 'Input Kehadiran Santri' }}</h1>
              <p class="text-sm text-gray-600 max-w-3xl mt-1">
                {{ $isDegur ? 'Input presensi santri berdasarkan kelas yang Anda ampu.' : 'Input presensi santri secara massal untuk tim Ketertiban.' }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              @if(! $isDegur)
                @foreach(['putra'=>'Putra','putri'=>'Putri'] as $val => $label)
                  <a href="{{ route('santri.presensi.create', ['gender' => $val]) }}"
                     class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium {{ $gender === $val ? 'bg-emerald-600 text-white border-emerald-600' : 'border-gray-200 text-gray-700 hover:border-emerald-300' }}">
                    {{ $label }}
                  </a>
                @endforeach
              @endif
            </div>
          </div>

          @if($isDegur)
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
              <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-600">Kelas Sesi</span>
                <div class="flex flex-wrap items-center gap-2">
                  @foreach(($managedKelas ?? collect()) as $kelas)
                    <label class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs text-gray-700">
                      <input type="checkbox"
                             class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 degur-kelas-filter"
                             name="kelas_ids[]"
                             value="{{ $kelas->id }}"
                             @checked(in_array((int) $kelas->id, array_map('intval', (array) ($selectedKelasIds ?? [])), true)) />
                      <span>{{ $kelas->nama }}</span>
                    </label>
                  @endforeach
                </div>
                <button type="button"
                        id="apply-kelas-filter"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                  Tampilkan Santri
                </button>
              </div>
              <p class="mt-2 text-[11px] text-gray-500">Centang satu atau lebih kelas untuk membuat sesi gabungan.</p>
            </div>
          @endif
        </div>

        {{-- Santri Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden xl:flex-1 xl:min-h-0 xl:flex xl:flex-col">
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

          <div class="elegant-scroll overflow-auto xl:flex-1 xl:min-h-0">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                  <th class="px-5 py-3 text-left min-w-[360px]">
                    <span class="text-sm font-semibold text-gray-700">Nama</span>
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
                  <tr class="hover:bg-gray-50 transition-colors" data-santri-id="{{ $santri->id }}">
                    <td class="px-5 py-3">
                      <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-900 leading-5">{{ $santri->nama_lengkap }}</span>
                        @if($isDegur)
                          <span class="text-[11px] text-gray-500 mt-0.5">Kelas: {{ $kelasNameMap[$santri->kelas_id] ?? '-' }}</span>
                        @else
                          <span class="text-[11px] text-gray-500 mt-0.5">Tim: {{ $santri->tim_resolved ?? '-' }}</span>
                        @endif
                        <span class="row-missing-indicator hidden text-[11px] mt-1 font-medium text-rose-600">Pilih status kehadiran</span>
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
      <div class="mt-6 xl:mt-0 xl:h-full xl:min-h-0 xl:self-stretch">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm xl:h-full xl:overflow-hidden p-4 space-y-4 flex flex-col">
          {{-- Stats Cards --}}
          <div class="grid grid-cols-4 {{ $isDegur ? 'gap-2' : 'gap-2.5' }}">
            <div class="rounded-xl border border-gray-200 bg-white text-center {{ $isDegur ? 'px-2 py-2' : 'px-3 py-3' }}">
              <p class="font-medium text-gray-600 {{ $isDegur ? 'text-[11px] mb-1' : 'text-xs mb-1.5' }}">Hadir</p>
              <p class="font-semibold text-gray-900 {{ $isDegur ? 'text-[22px] leading-7' : 'text-2xl leading-[30px]' }}" id="stat-hadir">{{ $stats['hadir'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white text-center {{ $isDegur ? 'px-2 py-2' : 'px-3 py-3' }}">
              <p class="font-medium text-gray-600 {{ $isDegur ? 'text-[11px] mb-1' : 'text-xs mb-1.5' }}">Izin</p>
              <p class="font-semibold text-gray-900 {{ $isDegur ? 'text-[22px] leading-7' : 'text-2xl leading-[30px]' }}" id="stat-izin">{{ $stats['izin'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white text-center {{ $isDegur ? 'px-2 py-2' : 'px-3 py-3' }}">
              <p class="font-medium text-gray-600 {{ $isDegur ? 'text-[11px] mb-1' : 'text-xs mb-1.5' }}">Sakit</p>
              <p class="font-semibold text-gray-900 {{ $isDegur ? 'text-[22px] leading-7' : 'text-2xl leading-[30px]' }}" id="stat-sakit">{{ $stats['sakit'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white text-center {{ $isDegur ? 'px-2 py-2' : 'px-3 py-3' }}">
              <p class="font-medium text-gray-600 {{ $isDegur ? 'text-[11px] mb-1' : 'text-xs mb-1.5' }}">Alpa</p>
              <p class="font-semibold text-gray-900 {{ $isDegur ? 'text-[22px] leading-7' : 'text-2xl leading-[30px]' }}" id="stat-alpha">{{ $stats['alpha'] }}</p>
            </div>
          </div>

          {{-- Tanggal --}}
          <div class="{{ $isDegur ? 'space-y-1' : 'space-y-1.5' }}">
            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
            <div class="relative">
              <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" 
                     class="w-full rounded-lg border border-gray-300 bg-white px-3 {{ $isDegur ? 'py-1.5' : 'py-2' }} text-sm text-gray-900 placeholder:text-gray-500 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600" 
                     placeholder="Pilih Tanggal"
                     required />
              <i data-lucide="calendar" class="w-5 h-5 text-gray-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
          </div>

          {{-- Kategori Kegiatan --}}
          <div class="{{ $isDegur ? 'space-y-1' : 'space-y-1.5' }}">
            <label class="block text-sm font-medium text-gray-700">Kategori Kegiatan</label>
            <div class="relative">
              <select name="kategori" 
                      class="w-full rounded-lg border border-gray-300 bg-white px-3 {{ $isDegur ? 'py-1.5' : 'py-2' }} text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 appearance-none" 
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
          <div class="{{ $isDegur ? 'space-y-1' : 'space-y-1.5' }}">
            <label class="block text-sm font-medium text-gray-700">Waktu</label>
            <div class="relative">
              <select name="waktu" 
                      class="w-full rounded-lg border border-gray-300 bg-white px-3 {{ $isDegur ? 'py-1.5' : 'py-2' }} text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 appearance-none" 
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
          <div class="{{ $isDegur ? 'space-y-1 flex-1 min-h-0' : 'space-y-1.5' }}">
            <label class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
            <textarea name="catatan" rows="{{ $isDegur ? 4 : 6 }}" 
                      placeholder="Masukkan catatan" 
                      class="w-full rounded-lg border border-gray-300 bg-white px-3 {{ $isDegur ? 'py-2 h-[92px]' : 'py-2.5 h-[120px]' }} text-sm text-gray-900 placeholder:text-gray-500 shadow-sm focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 resize-none">{{ old('catatan') }}</textarea>
          </div>

          {{-- Submit Button --}}
          <button type="submit" 
                  class="w-full rounded-lg bg-emerald-600 border-2 border-white/10 px-3 {{ $isDegur ? 'py-2' : 'py-2.5' }} text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition-colors">
            Submit Kehadiran
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const successToast = document.getElementById('success-toast');
    if (successToast) {
      setTimeout(() => {
        successToast.classList.add('hidden');
      }, 1500);
    }

    const presensiForm = document.querySelector('form[action="{{ route('santri.presensi.store') }}"]');
    const missingWarning = document.getElementById('missing-warning');
    const missingWarningText = missingWarning?.querySelector('div');
    const santriRows = Array.from(document.querySelectorAll('tr[data-santri-id]'));
    const statusHeaderCheckboxes = Array.from(document.querySelectorAll('.status-select-all'));
    const statusRadios = Array.from(document.querySelectorAll('.status-radio'));
    const applyKelasFilterBtn = document.getElementById('apply-kelas-filter');
    const statEls = {
      hadir: document.getElementById('stat-hadir'),
      izin: document.getElementById('stat-izin'),
      sakit: document.getElementById('stat-sakit'),
      alpha: document.getElementById('stat-alpha'),
    };

    if (applyKelasFilterBtn) {
      applyKelasFilterBtn.addEventListener('click', () => {
        const selectedKelasIds = Array.from(document.querySelectorAll('.degur-kelas-filter:checked'))
          .map(el => el.value)
          .filter(Boolean);

        if (selectedKelasIds.length === 0) {
          alert('Pilih minimal satu kelas.');
          return;
        }

        const params = new URLSearchParams();
        selectedKelasIds.forEach(id => params.append('kelas_ids[]', id));
        window.location.href = `{{ route('santri.presensi.create') }}?${params.toString()}`;
      });
    }

    let submitAttempted = false;
    let warningTimer = null;

    const hideMissingWarning = () => {
      if (!missingWarning) {
        return;
      }
      missingWarning.classList.add('hidden');
      if (missingWarningText) {
        missingWarningText.textContent = '';
      }
      if (warningTimer) {
        clearTimeout(warningTimer);
        warningTimer = null;
      }
    };

    const showMissingWarning = (missingCount) => {
      if (!missingWarning) {
        return;
      }
      if (missingWarningText) {
        missingWarningText.textContent = `Masih ada ${missingCount} santri yang belum dipilih status kehadirannya.`;
      }
      missingWarning.classList.remove('hidden');
      if (warningTimer) {
        clearTimeout(warningTimer);
      }
      warningTimer = setTimeout(() => {
        missingWarning.classList.add('hidden');
        warningTimer = null;
      }, 3000);
    };

    const updateMissingState = ({ reveal = false } = {}) => {
      const missingRowIds = [];
      const shouldReveal = reveal || submitAttempted;

      santriRows.forEach((row) => {
        const santriId = row.dataset.santriId;
        const checked = document.querySelector(`input[name="presensi[${santriId}]"]:checked`);
        const missing = !checked;

        if (missing) {
          missingRowIds.push(santriId);
        }

        row.classList.toggle('bg-rose-50', shouldReveal && missing);
        const indicator = row.querySelector('.row-missing-indicator');
        if (indicator) {
          indicator.classList.toggle('hidden', !(shouldReveal && missing));
        }
      });

      if (missingRowIds.length === 0) {
        hideMissingWarning();
      }

      return missingRowIds;
    };

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

      updateMissingState();
    };

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

    if (presensiForm) {
      presensiForm.addEventListener('submit', (event) => {
        submitAttempted = true;
        const missingRows = updateMissingState({ reveal: true });
        if (missingRows.length > 0) {
          event.preventDefault();
          showMissingWarning(missingRows.length);
          const firstMissing = document.querySelector(`tr[data-santri-id="${missingRows[0]}"]`);
          firstMissing?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    }

    updateSummary();
  });
</script>
@endsection
