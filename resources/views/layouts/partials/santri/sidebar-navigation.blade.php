<nav class="elegant-scroll flex-1 px-5 py-5 space-y-1 text-sm overflow-y-auto">
  <div class="mb-4">
    <div class="relative">
      <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
      <input type="search" placeholder="Search"
             class="w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 py-2 text-sm placeholder:text-gray-400 focus:ring-1 focus:ring-emerald-600/20 focus:border-emerald-600 transition-all" />
    </div>
  </div>
  @if($isWali)
      <ul class="space-y-1">
        @foreach($waliMenu as $item)
          @php
            $isActive = request()->routeIs($item['route']);
            $stateClasses = $isActive ? 'bg-emerald-50 text-emerald-700 font-medium' : '';
            $disabledClasses = $hasChildSelected ? '' : 'opacity-50 cursor-not-allowed pointer-events-none';
          @endphp
          <li>
            <a href="{{ $hasChildSelected ? route($item['route'], $activeChildCode) : '#' }}"
               @if($hasChildSelected) wire:navigate @endif
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
          <a href="{{ route('santri.home') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.home') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Dashboard</span>
          </a>
        </li>
        @php
          $isKetertibanUser = $isKetertiban;
        @endphp
        <li>
          <button type="button"
                  @click="presensiOpen = !presensiOpen"
                  class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.presensi.*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <i data-lucide="user-check" class="w-5 h-5"></i>
            <span class="flex-1 text-left">Data Santri</span>
            <i :class="presensiOpen ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform"></i>
          </button>
          <div x-show="presensiOpen" x-transition class="pl-12 pr-4 pt-1 pb-1 space-y-0.5">
            <a href="{{ route('santri.presensi.index', ['mode' => $defaultPresensiMode]) }}" wire:navigate
               class="block rounded-lg px-3 py-2 text-sm {{ $isPresensiPrimaryActive ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
              {{ $isStaffRole ? 'Kehadiran Santri' : 'Kehadiran Saya' }}
            </a>
            @if(!$isStaffRole)
              <a href="{{ route('santri.kafarah.index', ['mode' => 'mine']) }}" wire:navigate
                 class="block rounded-lg px-3 py-2 text-sm {{ request()->fullUrlIs(route('santri.kafarah.index', ['mode' => 'mine'])) ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                Kafarah Saya
              </a>
            @endif
            @if($isKetertibanUser)
              <a href="{{ route('santri.presensi.index', ['mode' => 'team']) }}" wire:navigate
                 class="flex items-center justify-between rounded-lg px-3 py-2 text-sm {{ request()->fullUrlIs(route('santri.presensi.index', ['mode' => 'team'])) ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                <span>Kehadiran Santri</span>
                <span class="ml-2 inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-emerald-700">{{ $teamFeatureBadge }}</span>
              </a>
              <a href="{{ route('santri.presensi.rekap') }}" wire:navigate
                 class="flex items-center justify-between rounded-lg px-3 py-2 text-sm {{ request()->routeIs('santri.presensi.rekap') ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                <span>Rekap Presensi</span>
                <span class="ml-2 inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-emerald-700">{{ $teamFeatureBadge }}</span>
              </a>
              <a href="{{ route('santri.kafarah.index', ['mode' => 'team']) }}" wire:navigate
                 class="flex items-center justify-between rounded-lg px-3 py-2 text-sm {{ request()->fullUrlIs(route('santri.kafarah.index', ['mode' => 'team'])) ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                <span>Kafarah Santri</span>
                <span class="ml-2 inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-emerald-700">{{ $teamFeatureBadge }}</span>
              </a>
            @endif
          </div>
        </li>
        <li>
          <a href="{{ route('santri.data.progres') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.data.progres*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            <i data-lucide="book-open" class="w-5 h-5"></i>
            <span>Progress Keilmuan</span>
          </a>
        </li>
        <li>
          @if($isSantri)
            <button type="button"
                    @click="logMenuOpen = !logMenuOpen"
                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ $isLogRoute ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
              <i data-lucide="clock" class="w-5 h-5"></i>
              <span class="flex-1 text-left">Log Keluar/Masuk</span>
              <i :class="logMenuOpen ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform"></i>
            </button>
            <div x-show="logMenuOpen" x-transition class="pl-12 pr-4 pt-1 pb-1 space-y-0.5">
              <a href="{{ route('santri.data.log', ['mode' => 'input']) }}" wire:navigate
                 class="block rounded-lg px-3 py-2 text-sm {{ $isLogInputActive ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                Input Keluar/Masuk
              </a>
              <a href="{{ route('santri.data.log', ['mode' => 'mine']) }}" wire:navigate
                 class="block rounded-lg px-3 py-2 text-sm {{ $isLogMineActive ? 'text-emerald-700 font-medium bg-emerald-50' : 'text-gray-600 hover:bg-gray-50' }}">
                Log Saya
              </a>
            </div>
          @else
            <a href="{{ route('santri.data.log') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('santri.data.log') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
              <i data-lucide="clock" class="w-5 h-5"></i>
              <span>Log Keluar/Masuk</span>
            </a>
          @endif
        </li>
      </ul>
    @endif
</nav>
