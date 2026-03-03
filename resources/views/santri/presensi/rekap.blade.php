@extends('layouts.santri-modern')
@section('title', 'Rekap Presensi KTB')
@section('content_panel_class', 'h-[calc(100vh-40px)] overflow-y-auto')

@section('content')
<style>
  @media (min-width: 768px) {
    .rekap-page {
      height: 100%;
      min-height: 0;
      display: flex;
      flex-direction: column;
    }

    .rekap-header {
      flex: 0 0 auto;
    }

    .rekap-layout {
      display: grid;
      grid-template-columns: 360px minmax(0, 1fr);
      gap: 1rem;
      flex: 1 1 auto;
      height: 100%;
      min-height: 0;
      overflow: hidden;
    }

    .rekap-left {
      height: 100%;
      min-height: 0;
      overflow: visible;
      padding-right: 0.25rem;
    }

    .rekap-right {
      height: 100%;
      min-height: 0;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .rekap-table-panel {
      flex: 1 1 auto;
      min-height: 0;
    }

    .rekap-table-scroll {
      flex: 1 1 auto;
      height: 100%;
      max-height: 100%;
      min-height: 0;
      overflow: auto;
    }
  }

  .rekap-card {
    border-radius: 1rem;
    border: 1px solid rgb(229 231 235);
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
  }

  .rekap-summary-chip {
    border-radius: 999px;
    border-width: 1px;
    padding: 0.12rem 0.45rem;
    font-size: 0.68rem;
    line-height: 0.95rem;
    font-weight: 500;
  }
</style>

<div class="rekap-page h-full min-h-0 flex flex-col gap-4" data-rekap-root>
  <div class="rekap-header space-y-2 flex-none">
    <nav class="flex items-center gap-2 text-sm text-gray-500">
      <a href="{{ route('santri.dashboard') }}" wire:navigate class="hover:text-gray-800">Dashboard</a>
      <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
      <a href="{{ route('santri.presensi.index', ['mode' => 'team']) }}" wire:navigate class="hover:text-gray-800">Kehadiran Santri</a>
      <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
      <span class="text-emerald-700 font-medium">Rekap Presensi KTB</span>
    </nav>

    <div>
      <h1 class="text-[22px] font-semibold text-gray-900">Rekap Presensi Bulanan</h1>
      <p class="text-sm text-gray-600 mt-1">Optimized mode: tab, filter, dan pagination dimuat dengan AJAX tanpa reload penuh.</p>
    </div>
  </div>

  <div id="rekap-layout-container">
    @include('santri.presensi.partials.rekap-layout', [
      'bulanInput' => $bulanInput,
      'activeTab' => $activeTab,
      'selectedKategori' => $selectedKategori,
      'selectedWaktu' => $selectedWaktu,
      'kategoriOptions' => $kategoriOptions,
      'waktuOptions' => $waktuOptions,
      'putraSummary' => $putraSummary,
      'putriSummary' => $putriSummary,
      'activeRows' => $activeRows,
    ])
  </div>
</div>

@once
<script>
  (function () {
    const initRekapAjax = () => {
      const root = document.querySelector('[data-rekap-root]');
      if (!root || root.dataset.ajaxBound === '1') {
        return;
      }
      root.dataset.ajaxBound = '1';

      const layoutContainer = root.querySelector('#rekap-layout-container');
      if (!layoutContainer) {
        return;
      }

      let currentController = null;

      const fetchLayout = (url, pushState = true) => {
        if (currentController) {
          currentController.abort();
        }
        currentController = new AbortController();

        const targetUrl = new URL(url, window.location.origin);
        targetUrl.searchParams.set('ajax', '1');

        layoutContainer.classList.add('opacity-60');

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
              throw new Error('Gagal memuat rekap presensi.');
            }
            return response.json();
          })
          .then((payload) => {
            if (!payload || typeof payload.html !== 'string') {
              throw new Error('Respons AJAX rekap tidak valid.');
            }

            layoutContainer.innerHTML = payload.html;

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
            layoutContainer.classList.remove('opacity-60');
          });
      };

      const submitFilter = (formElement, page = 1) => {
        const pageInput = formElement.querySelector('#rekap-page-input');
        if (pageInput) {
          pageInput.value = String(page);
        }

        const params = new URLSearchParams(new FormData(formElement));
        const url = `${formElement.action}?${params.toString()}`;
        fetchLayout(url);
      };

      root.addEventListener('submit', (event) => {
        const formElement = event.target.closest('#rekap-filter-form');
        if (!formElement) {
          return;
        }

        event.preventDefault();
        submitFilter(formElement, 1);
      });

      root.addEventListener('click', (event) => {
        const tabButton = event.target.closest('[data-rekap-tab]');
        if (tabButton) {
          const formElement = root.querySelector('#rekap-filter-form');
          const tabInput = root.querySelector('#rekap-tab-input');
          if (!formElement || !tabInput) {
            return;
          }

          tabInput.value = tabButton.dataset.rekapTab || 'putra';
          submitFilter(formElement, 1);
          return;
        }

        const paginationLink = event.target.closest('[data-rekap-pagination] a[href]');
        if (!paginationLink) {
          return;
        }

        event.preventDefault();
        const formElement = root.querySelector('#rekap-filter-form');
        if (!formElement) {
          fetchLayout(paginationLink.href);
          return;
        }

        const linkUrl = new URL(paginationLink.href, window.location.origin);
        const nextPage = linkUrl.searchParams.get('page');
        submitFilter(formElement, nextPage ? Number(nextPage) : 1);
      });
    };

    document.addEventListener('DOMContentLoaded', initRekapAjax);
    document.addEventListener('livewire:navigated', initRekapAjax);
  })();
</script>
@endonce
@endsection
