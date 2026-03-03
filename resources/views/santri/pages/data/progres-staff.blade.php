@extends('layouts.santri-modern')
@section('title', 'Progress Keilmuan Santri' . ($category === 'al-hadits' ? ' - Hadits' : ' - Quran'))
@section('content_panel_class', 'h-[calc(100vh-40px)] overflow-hidden')

@section('content')
@php
  $tabs = [
    ['key' => 'al-quran', 'label' => 'Al-Quran'],
    ['key' => 'al-hadits', 'label' => 'Al-Hadits'],
  ];
@endphp

<div class="flex h-full min-h-0 flex-col gap-1.5 overflow-hidden" data-progress-staff-root>
  <div class="shrink-0 space-y-0.5">
    <h1 class="text-lg font-semibold text-gray-900">Progress Keilmuan Seluruh Santri</h1>
    <p class="text-xs text-gray-500">Akses monitoring untuk dewan guru dan pengurus pada seluruh data santri.</p>
  </div>

  <div class="shrink-0 mt-6 mb-3 flex items-center gap-6 border-b border-gray-200">
    @foreach($tabs as $tab)
      @php $active = $category === $tab['key']; @endphp
      <a href="{{ route('santri.data.progres', ['category' => $tab['key'], 'gender' => $genderFilter, 'q' => $searchQuery]) }}" wire:navigate
         class="pb-2.5 text-sm font-medium {{ $active ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-gray-500 hover:text-gray-700' }}">
        {{ $tab['label'] }}
      </a>
    @endforeach
  </div>

  <div class="shrink-0">
    <div class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1">
    @foreach(['all' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri'] as $genderKey => $genderLabel)
      <a href="{{ route('santri.data.progres', ['category' => $category, 'gender' => $genderKey, 'q' => $searchQuery]) }}" wire:navigate
         class="inline-flex min-w-[72px] items-center justify-center rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $genderFilter === $genderKey ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }}">
        {{ $genderLabel }}
      </a>
    @endforeach
    </div>
  </div>

  <div class="flex shrink-0 flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 shadow-sm">
    <p class="text-sm font-semibold text-gray-900">Daftar Progres Santri</p>
    <form method="GET" action="{{ route('santri.data.progres') }}" class="relative w-full sm:w-72" id="progress-staff-search-form">
      <input type="hidden" name="category" value="{{ $category }}">
      <input type="hidden" name="gender" value="{{ $genderFilter }}">
      <input type="hidden" name="page" value="{{ $rows->currentPage() }}" id="progress-staff-page-input">
      <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
      <input
        type="search"
        name="q"
        value="{{ $searchQuery }}"
        placeholder="Cari nama/kelas/tim/kode"
        class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600/20"
        id="progress-staff-search-input"
      />
    </form>
  </div>

  <div class="space-y-2" id="progress-staff-panel">
    @include('santri.pages.data.partials.progres-staff-table-panel', [
      'rows' => $rows,
      'stats' => $stats,
    ])
  </div>
</div>

@once
<script>
  (function () {
    const initProgressStaffAjax = () => {
      const root = document.querySelector('[data-progress-staff-root]');
      if (!root || root.dataset.ajaxBound === '1') {
        return;
      }
      root.dataset.ajaxBound = '1';

      const form = root.querySelector('#progress-staff-search-form');
      const searchInput = root.querySelector('#progress-staff-search-input');
      const pageInput = root.querySelector('#progress-staff-page-input');
      const panel = root.querySelector('#progress-staff-panel');
      if (!form || !panel || !pageInput) {
        return;
      }

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
              throw new Error('Gagal memuat data progres.');
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

      const submitForm = (page = 1) => {
        pageInput.value = String(page);
        const params = new URLSearchParams(new FormData(form));
        const url = `${form.action}?${params.toString()}`;
        fetchPanel(url);
      };

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        submitForm(1);
      });

      if (searchInput) {
        searchInput.addEventListener('input', () => {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(() => submitForm(1), 120);
        });
      }

      root.addEventListener('click', (event) => {
        const link = event.target.closest('[data-progress-pagination] a[href]');
        if (!link) {
          return;
        }

        event.preventDefault();
        const linkUrl = new URL(link.href, window.location.origin);
        const nextPage = linkUrl.searchParams.get('page');
        submitForm(nextPage ? Number(nextPage) : 1);
      });
    };

    document.addEventListener('DOMContentLoaded', initProgressStaffAjax);
    document.addEventListener('livewire:navigated', initProgressStaffAjax);
  })();
</script>
@endonce
@endsection
