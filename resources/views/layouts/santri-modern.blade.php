<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title','Santri')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <script defer src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => { window.lucide?.createIcons?.(); });
  </script>
  <style>
    /* Prevent layout shift when icons/fonts load */
    i[data-lucide] { width: 1.25rem; height: 1.25rem; display: inline-block; vertical-align: middle; }
    body { scrollbar-gutter: stable; }
  </style>
</head>
<body class="bg-gray-100 text-gray-800 overflow-y-scroll antialiased">
@php
  $currentUser = auth()->user();
  $roleValue = $currentUser?->role?->value;
  $isKetertiban = $currentUser?->isKetertiban();
  $activeChildCode = request()->route('santriCode') ?? request()->route('santri') ?? request()->route('code');
  $hasChildSelected = filled($activeChildCode);
  $santriTeam = null;

  if ($roleValue === \App\Enum\Role::SANTRI->value) {
    // Ambil tim dari relasi santri atau fallback by user_id
    $santriTeam = trim((string) ($currentUser?->santri?->tim ?? ''));
    if ($santriTeam === '') {
      $santriTeam = trim((string) optional(\App\Models\Santri::where('user_id', $currentUser?->id)->first())->tim ?? '');
    }
  } elseif ($roleValue === \App\Enum\Role::WALI->value && $hasChildSelected) {
    // Jika wali memilih santri tertentu, ambil tim santri tersebut via code
    $santriTeam = trim((string) optional(\App\Models\Santri::where('code', $activeChildCode)->first())->tim ?? '');
  }
@endphp
  <div class="min-h-screen p-5">
    <div class="grid grid-cols-[280px_1fr] gap-5">
      <aside class="bg-white rounded-3xl shadow-lg border border-gray-100 h-[calc(100vh-40px)] sticky top-5 overflow-hidden flex flex-col"
             x-data="{ 
               presensiOpen: {{ request()->routeIs('santri.presensi.*') ? 'true' : 'false' }},
               profileMenuOpen: false
             }">
        <div class="px-6 py-5 border-b border-gray-100">
          @php
            $logoCandidates = [
              'assets/images/logo-ppm.png',
              'assets/images/logo_ppm.png',
              'assets/images/logo.png',
            ];
            $logoRel = null;
            foreach ($logoCandidates as $c) {
              if (file_exists(public_path($c))) { $logoRel = $c; break; }
            }
          @endphp
          <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
            @if($logoRel)
              <img class="h-8 w-8 object-contain" src="{{ asset($logoRel) }}" alt="PPM KH2">
            @else
              <i data-lucide="shield" class="w-8 h-8 text-emerald-600"></i>
            @endif
            <span class="text-base font-semibold text-gray-900 group-hover:text-emerald-700">PPM Khoirul Huda 2</span>
          </a>
        </div>
        <nav class="flex-1 px-5 py-5 space-y-1 text-sm overflow-y-auto">
          <div class="mb-4">
            <div class="relative">
              <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
              <input type="search" placeholder="Search"
                     class="w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 py-2 text-sm placeholder:text-gray-400 focus:ring-1 focus:ring-emerald-600/20 focus:border-emerald-600 transition-all" />
            </div>
          </div>
          @if($roleValue === \App\Enum\Role::WALI->value)
              <ul class="space-y-1">
                @php
                  $waliMenu = [
                    ['label' => 'Dashboard Anak', 'icon' => 'layout-dashboard', 'route' => 'wali.anak.overview'],
                    ['label' => 'Presensi', 'icon' => 'fingerprint', 'route' => 'wali.anak.presensi'],
                    ['label' => 'Progress Keilmuan', 'icon' => 'calendar', 'route' => 'wali.anak.progres'],
                    ['label' => 'Log Keluar/Masuk', 'icon' => 'clock', 'route' => 'wali.anak.log'],
                  ];
                @endphp
                @foreach($waliMenu as $item)
                  @php
                    $isActive = request()->routeIs($item['route']);
                    $stateClasses = $isActive ? 'bg-emerald-50 text-emerald-700 font-medium' : '';
                    $disabledClasses = $hasChildSelected ? '' : 'opacity-50 cursor-not-allowed pointer-events-none';
                  @endphp
                  <li>
                    <a href="{{ $hasChildSelected ? route($item['route'], $activeChildCode) : '#' }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ $stateClasses }} {{ $disabledClasses }}">
                      <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                      <span>{{ $item['label'] }}</span>
                    </a>
                  </li>
                @endforeach
              </ul>
            @else
              <ul class="space-y-1 text-gray-700">
                <li>
                  <a href="{{ route('santri.home') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.home') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                  </a>
                </li>
                <li>
                  <button type="button"
                          @click="presensiOpen = !presensiOpen"
                          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.presensi.*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                    <span class="flex-1 text-left">Presensi</span>
                    <i :class="presensiOpen ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform"></i>
                  </button>
                  <div x-show="presensiOpen" x-transition class="pl-12 pr-4 pt-1 pb-1 space-y-0.5">
                    <a href="{{ route('santri.presensi.index', ['mode' => 'mine']) }}"
                       class="block rounded-lg px-3 py-2 text-sm {{ request()->fullUrlIs(route('santri.presensi.index', ['mode' => 'mine'])) ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                      Presensi Saya
                    </a>
                    <a href="{{ route('santri.presensi.index', ['mode' => 'team']) }}"
                       class="block rounded-lg px-3 py-2 text-sm {{ request()->fullUrlIs(route('santri.presensi.index', ['mode' => 'team'])) ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                      Presensi Tim
                    </a>
                  </div>
                </li>
                <li>
                  <a href="{{ route('santri.data.progres') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.data.progres') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                    <span>Progress Keilmuan</span>
                  </a>
                </li>
                <li>
                  <a href="{{ route('santri.data.log') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.data.log') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                    <span>Log Keluar/Masuk</span>
                  </a>
                </li>
              </ul>
            @endif
        </nav>
        
        <div class="mt-auto border-t border-gray-100 px-5 py-4 space-y-3">
          <a href="#" class="flex items-center justify-between px-4 py-2.5 rounded-lg hover:bg-gray-50 text-gray-700">
            <div class="flex items-center gap-3">
              <i data-lucide="bell" class="w-5 h-5"></i>
              <span class="text-sm">Notifikasi</span>
            </div>
            <span class="text-xs font-semibold bg-gray-200 text-gray-700 rounded-full px-2 py-0.5">10</span>
          </a>
          
          <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <div class="px-3 py-3 rounded-xl bg-gray-50 flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold text-sm">
                {{ strtoupper(substr($currentUser?->name ?? 'U', 0, 1)) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold leading-tight text-gray-900 truncate">{{ $currentUser?->name ?? 'User' }}</div>
                <div class="text-xs text-gray-500">
                  Tim: {{ $santriTeam !== null && $santriTeam !== '' ? $santriTeam : '-' }}
                </div>
              </div>
              <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
              </button>
            </div>
            
            {{-- Dropdown Menu --}}
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50"
                 style="display: none;">
              <a href="{{ route('santri.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Profil</span>
              </a>
              <a href="{{ route('santri.setting') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Pengaturan</span>
              </a>
              <div class="h-px bg-gray-200 my-1"></div>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                  <i data-lucide="log-out" class="w-4 h-4"></i>
                  <span>Sign out</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </aside>

      <section>
        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-5">
          @yield('content')
        </div>
      </section>
    </div>
  </div>
</body>
</html>
