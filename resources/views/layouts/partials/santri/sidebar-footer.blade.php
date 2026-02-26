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
        <div class="text-xs text-gray-500">{{ $sidebarRoleCaption }}</div>
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
