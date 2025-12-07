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
  $activeChildCode = request()->route('santriCode') ?? request()->route('santri') ?? request()->route('code');
  $hasChildSelected = filled($activeChildCode);
@endphp
  <div class="min-h-screen p-5">
    <div class="grid grid-cols-[260px_1fr] gap-5">
      <aside class="bg-white rounded-3xl shadow-lg border border-gray-100 h-[calc(100vh-40px)] sticky top-5 overflow-hidden">
        <div class="px-5 pt-6 pb-4 border-b">
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
          <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            @if($logoRel)
              <img class="h-9 w-9 object-contain" src="{{ asset($logoRel) }}" alt="PPM KH2">
            @else
              <i data-lucide="shield" class="w-7 h-7 text-emerald-600"></i>
            @endif
            <span class="font-semibold group-hover:text-emerald-700">PPM KH2</span>
          </a>
        </div>
        <nav class="p-4 space-y-6 text-sm">
          <div>
            <div class="px-2 text-xs uppercase text-gray-500 mb-2">Menu</div>
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
              <ul class="space-y-1">
                <li>
                  <a href="{{ route('santri.home') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ request()->routeIs('santri.home') ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                  </a>
                </li>
                <li>
                  <a href="{{ route('santri.data.presensi') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ request()->routeIs('santri.data.presensi') ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Presensi</span>
                  </a>
                </li>
                <li>
                  <a href="{{ route('santri.data.progres') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ request()->routeIs('santri.data.progres') ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    <span>Progres Keilmuan</span>
                  </a>
                </li>
                <li>
                  <a href="{{ route('santri.data.log') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ request()->routeIs('santri.data.log') ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                    <span>Log Keluar/Masuk</span>
                  </a>
                </li>
              </ul>
            @endif
          </div>
          <div>
            <div class="px-2 text-xs uppercase text-gray-500 mb-2">General</div>
            <ul class="space-y-1">
              @if($roleValue === \App\Enum\Role::WALI->value)
                <li>
                  <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ request()->routeIs('profile.edit') ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span>Pengaturan Akun</span>
                  </a>
                </li>
              @else
                <li>
                  <a href="{{ route('santri.profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ request()->routeIs('santri.profile') ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span>Profil</span>
                  </a>
                </li>
                <li>
                  <a href="{{ route('santri.setting') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 {{ request()->routeIs('santri.setting') ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span>Settings</span>
                  </a>
                </li>
              @endif
              <li>
                <form method="POST" action="{{ route('logout') }}">@csrf
                  <button class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 text-left">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span>Logout</span>
                  </button>
                </form>
              </li>
            </ul>
          </div>
          <div class="mt-4 p-3 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-700 text-white">
            <div class="text-xs opacity-80">Download App</div>
            <div class="mt-1 font-semibold">Dapatkan kemudahan presensi</div>
            <a href="#" class="mt-3 inline-flex items-center gap-2 text-xs bg-white/10 hover:bg-white/20 rounded-lg px-3 py-1">
              <i data-lucide="download" class="w-4 h-4"></i> Download
            </a>
          </div>
        </nav>
      </aside>

      <section class="space-y-4">
        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-3 flex items-center justify-between">
          <div class="flex items-center gap-3 flex-1">
            <div class="relative flex-1 max-w-xl">
              <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input type="text" placeholder="Cari tugas/fitur..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button class="p-2 rounded-xl hover:bg-gray-100"><i data-lucide="bell" class="w-5 h-5"></i></button>
            <button class="p-2 rounded-xl hover:bg-gray-100"><i data-lucide="inbox" class="w-5 h-5"></i></button>
            <div class="h-8 w-8 rounded-full bg-gray-200 grid place-items-center text-xs font-medium">{{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}</div>
          </div>
        </div>

        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-4">
          @yield('content')
        </div>
      </section>
    </div>
  </div>
</body>
</html>
