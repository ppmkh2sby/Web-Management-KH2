@extends('layouts.santri-modern')
@section('title', $isStaffViewer ? 'Log Keluar/Masuk Santri' : 'Log Keluar/Masuk')

@section('content')
@php
    $isPaginated = $logs instanceof \Illuminate\Pagination\LengthAwarePaginator || $logs instanceof \Illuminate\Pagination\Paginator;
    $logRows = $isPaginated ? collect($logs->items()) : $logs;
    $recent = $logRows->take(5);
@endphp

<div class="space-y-6" data-log-ajax-root data-log-mode="{{ $mode ?? 'input' }}" data-log-staff="{{ $isStaffViewer ? '1' : '0' }}">
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
        <form method="GET" action="{{ route('santri.data.log') }}" class="flex items-center gap-2" id="log-filter-form">
          <input type="hidden" name="gender_filter" value="{{ $genderFilter }}">
          <input type="hidden" name="page" value="{{ method_exists($logs, 'currentPage') ? $logs->currentPage() : 1 }}" id="log-page-input">
          <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama/tujuan" id="log-search-input"
                 class="w-56 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
          <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Cari
          </button>
        </form>
      </div>

      <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri'] as $key => $label)
          <a href="{{ route('santri.data.log', ['gender_filter' => $key, 'search' => $search ?: null]) }}" data-log-filter-link
             class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ $genderFilter === $key ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ $label }}
          </a>
        @endforeach
      </div>
    </div>

    <div id="log-table-panel">
      @include('santri.pages.data.partials.log-table-panel', [
        'logs' => $logs,
        'logRows' => $logRows,
        'isPaginated' => $isPaginated,
        'isStaffViewer' => true,
      ])
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
      <div id="log-table-panel">
        @include('santri.pages.data.partials.log-table-panel', [
          'logs' => $logs,
          'logRows' => $logRows,
          'isPaginated' => $isPaginated,
          'isStaffViewer' => false,
        ])
      </div>
    @endif
  @endif
</div>

@once
<script>
  (function () {
    const initLogAjax = () => {
      const root = document.querySelector('[data-log-ajax-root]');
      if (!root || root.dataset.ajaxBound === '1') {
        return;
      }
      root.dataset.ajaxBound = '1';

      const isStaff = root.dataset.logStaff === '1';
      const mode = root.dataset.logMode || 'input';
      if (!isStaff && mode !== 'mine') {
        return;
      }

      const panel = root.querySelector('#log-table-panel');
      if (!panel) {
        return;
      }

      const filterForm = root.querySelector('#log-filter-form');
      const searchInput = root.querySelector('#log-search-input');
      const pageInput = root.querySelector('#log-page-input');
      const genderInput = filterForm ? filterForm.querySelector('input[name=\"gender_filter\"]') : null;

      let debounceTimer = null;
      let currentController = null;

      const fetchPanel = (url, pushState = true) => {
        if (currentController) {
          currentController.abort();
        }
        currentController = new AbortController();

        const targetUrl = new URL(url, window.location.origin);
        targetUrl.searchParams.set('ajax', '1');

        panel.classList.add('opacity-60');

        fetch(targetUrl.toString(), {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          signal: currentController.signal,
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error('Gagal memuat data log.');
            }
            return response.json();
          })
          .then((payload) => {
            if (!payload || typeof payload.html !== 'string') {
              throw new Error('Respons AJAX tidak valid.');
            }

            panel.innerHTML = payload.html;

            if (pushState) {
              targetUrl.searchParams.delete('ajax');
              window.history.replaceState({}, '', targetUrl.toString());
            }

            if (typeof window.refreshLucideIcons === 'function') {
              window.refreshLucideIcons();
            }
          })
          .catch((error) => {
            if (error.name !== 'AbortError') {
              window.location.href = url;
            }
          })
          .finally(() => {
            panel.classList.remove('opacity-60');
          });
      };

      const submitFilterForm = (page = 1) => {
        if (!filterForm) {
          return;
        }

        if (pageInput) {
          pageInput.value = String(page);
        }

        const params = new URLSearchParams(new FormData(filterForm));
        const url = `${filterForm.action}?${params.toString()}`;
        fetchPanel(url);
      };

      const setActiveGenderChip = (value) => {
        root.querySelectorAll('[data-log-filter-link]').forEach((chip) => {
          const chipUrl = new URL(chip.href, window.location.origin);
          const chipGender = chipUrl.searchParams.get('gender_filter') || 'all';
          const isActive = chipGender === value;

          chip.classList.toggle('bg-emerald-600', isActive);
          chip.classList.toggle('text-white', isActive);
          chip.classList.toggle('bg-gray-100', !isActive);
          chip.classList.toggle('text-gray-700', !isActive);
          chip.classList.toggle('hover:bg-gray-200', !isActive);
        });
      };

      if (filterForm) {
        filterForm.addEventListener('submit', (event) => {
          event.preventDefault();
          submitFilterForm(1);
        });
      }

      if (searchInput && filterForm) {
        searchInput.addEventListener('input', () => {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(() => submitFilterForm(1), 120);
        });
      }

      root.addEventListener('click', (event) => {
        const genderLink = event.target.closest('[data-log-filter-link]');
        if (genderLink) {
          event.preventDefault();
          const genderUrl = new URL(genderLink.href, window.location.origin);
          const selectedGender = genderUrl.searchParams.get('gender_filter') || 'all';
          if (genderInput) {
            genderInput.value = selectedGender;
          }
          if (pageInput) {
            pageInput.value = '1';
          }
          setActiveGenderChip(selectedGender);
          fetchPanel(genderLink.href);
          return;
        }

        const paginationLink = event.target.closest('[data-log-pagination] a[href]');
        if (!paginationLink) {
          return;
        }

        event.preventDefault();

        if (filterForm) {
          const linkUrl = new URL(paginationLink.href, window.location.origin);
          const nextPage = linkUrl.searchParams.get('page');
          submitFilterForm(nextPage ? Number(nextPage) : 1);
          return;
        }

        fetchPanel(paginationLink.href);
      });
    };

    document.addEventListener('DOMContentLoaded', initLogAjax);
    document.addEventListener('livewire:navigated', initLogAjax);
  })();
</script>
@endonce
@endsection
