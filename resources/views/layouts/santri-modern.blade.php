<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title','Santri')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  @livewireStyles
  <style>
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
    @media (max-width: 1023.98px) {
      .layout-grid { grid-template-columns: 1fr; }
      .sidebar-shell { width: 100%; min-width: 0; height: auto; position: static; }
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
<body class="bg-gray-100 text-gray-800 overflow-y-scroll antialiased">
  <div wire:loading.delay wire:target="navigate"
       class="fixed top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-emerald-400 to-emerald-600 z-[9999]"
       style="display:none;">
  </div>
  <div class="layout-shell min-h-screen p-5">
    <div class="layout-grid grid grid-cols-[280px_1fr] gap-5">
      <aside class="sidebar-shell bg-white rounded-3xl shadow-lg border border-gray-100 h-[calc(100vh-40px)] sticky top-5 overflow-hidden flex flex-col"
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
        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-5 @yield('content_panel_class')">
          @yield('content')
        </div>
      </section>
    </div>
  </div>
  @livewireScripts
</body>
</html>
