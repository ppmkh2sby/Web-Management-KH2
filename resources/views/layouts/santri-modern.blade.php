<!DOCTYPE html>
<html lang="id">
<head>
  @php($livewireReady = \Illuminate\Support\Facades\Route::has('livewire.update'))
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title','Santri')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  @if($livewireReady)
    @livewireStyles
  @endif
  <style>
    [x-cloak] {
      display: none !important;
    }

    [wire\:loading-bar] {
      position: fixed;
      top: 0;
      left: 0;
      height: 3px;
      background: linear-gradient(90deg, #10b981, #059669);
      z-index: 9999;
      transition: width 0.2s ease;
    }

    /* Critical layout styles to prevent FOUC/shift before Vite CSS hydrates */
    .layout-shell { min-height: 100vh; padding: 1.25rem; }
    .layout-grid { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 1.25rem; align-items: start; }
    .sidebar-shell { width: 280px; min-width: 280px; }
    .content-shell { min-width: 0; }
    .content-panel { border-radius: 1.5rem; padding: 1.25rem; }
    .mobile-topbar { display: none; }
    @media (max-width: 1023.98px) {
      .layout-shell { padding: 0.75rem; }
      .layout-grid { display: block; }
      .mobile-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        padding: 0.75rem 0.9rem;
        border: 1px solid rgba(229, 231, 235, 1);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 10px 24px -18px rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
      }
      .sidebar-shell {
        width: min(88vw, 320px);
        min-width: 0;
        max-height: calc(100dvh - 1rem);
        height: auto;
        position: fixed;
        top: 0.5rem;
        left: 0.5rem;
        bottom: 0.5rem;
        z-index: 70;
        border-radius: 1.25rem;
        transform: translateX(-120%);
        transition: transform 0.28s ease, box-shadow 0.28s ease;
      }
      .sidebar-shell.mobile-open {
        transform: translateX(0);
        box-shadow: 0 30px 60px -30px rgba(15, 23, 42, 0.65);
      }
      .content-shell { min-width: 0; }
      .content-panel {
        border-radius: 1.1rem;
        padding: 1rem;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
      }
    }

    /* Prevent layout shift when icons/fonts load */
    i[data-lucide] { width: 1.25rem; height: 1.25rem; display: inline-block; vertical-align: middle; }
    body { scrollbar-gutter: stable; }

    /* Reusable elegant scrollbar */
    .elegant-scroll {
      scrollbar-width: thin;
      scrollbar-color: #10b981 #ecfdf5;
    }
    .elegant-scroll::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }
    .elegant-scroll::-webkit-scrollbar-track {
      background: #ecfdf5;
      border-radius: 9999px;
    }
    .elegant-scroll::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #34d399 0%, #059669 100%);
      border: 2px solid #ecfdf5;
      border-radius: 9999px;
    }
    .elegant-scroll::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(180deg, #278C46 0%, #278C46 100%);
    }
    .elegant-scroll::-webkit-scrollbar-corner {
      background: transparent;
    }
  </style>
</head>
<body
  class="bg-gray-100 text-gray-800 overflow-y-scroll antialiased"
  x-data="{ mobileSidebarOpen: false }"
  x-on:kh2-close-sidebar.window="mobileSidebarOpen = false"
  x-on:keydown.escape.window="mobileSidebarOpen = false"
>
  @if($livewireReady)
    <div wire:loading.delay wire:target="navigate"
         class="fixed top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-emerald-400 to-emerald-600 z-[9999]"
         style="display:none;">
    </div>
  @endif
  <div class="layout-shell min-h-screen p-5">
    <div class="mobile-topbar lg:hidden">
      <button
        type="button"
        @click="mobileSidebarOpen = true"
        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm"
        aria-label="Buka menu"
      >
        <i data-lucide="menu" class="h-5 w-5"></i>
      </button>
      <div class="min-w-0 flex-1">
        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-600">PPM KH2</p>
        <p class="truncate text-sm font-semibold text-gray-900">@yield('title', 'Dashboard')</p>
      </div>
      <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-sm font-semibold text-emerald-700">
        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
      </div>
    </div>

    <div
      x-cloak
      x-show="mobileSidebarOpen"
      x-transition.opacity
      @click="mobileSidebarOpen = false"
      class="fixed inset-0 z-[60] bg-slate-900/40 lg:hidden"
    ></div>

    <div class="layout-grid grid grid-cols-[280px_1fr] gap-5">
      <aside
             data-mobile-sidebar
             :class="{ 'mobile-open': mobileSidebarOpen }"
             class="sidebar-shell bg-white rounded-3xl shadow-lg border border-gray-100 h-[calc(100vh-40px)] sticky top-5 overflow-hidden flex flex-col"
             x-data="{ 
               presensiOpen: {{ request()->routeIs('santri.presensi.*') ? 'true' : 'false' }},
               logMenuOpen: {{ request()->routeIs('santri.data.log') ? 'true' : 'false' }},
               profileMenuOpen: false
             }">
        @include('layouts.partials.santri.sidebar-brand')
        @include('layouts.partials.santri.sidebar-navigation')
        @include('layouts.partials.santri.sidebar-footer')
      </aside>

      <section class="content-shell">
        <div class="content-panel bg-white rounded-3xl shadow-lg border border-gray-100 p-5 @yield('content_panel_class')">
          @yield('content')
        </div>
      </section>
    </div>
  </div>
  @if($livewireReady)
    @livewireScripts
  @endif
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.querySelector('[data-mobile-sidebar]');

      if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (link) {
          link.addEventListener('click', function () {
            if (window.innerWidth < 1024) {
              window.dispatchEvent(new CustomEvent('kh2-close-sidebar'));
            }
          });
        });
      }

      document.addEventListener('livewire:navigated', function () {
        if (window.innerWidth < 1024) {
          window.dispatchEvent(new CustomEvent('kh2-close-sidebar'));
        }
      });
    });
  </script>
</body>
</html>
