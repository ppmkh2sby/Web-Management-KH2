@php
    $tabs = [
        ['label' => 'Overview', 'route' => 'wali.anak.overview'],
        ['label' => 'Presensi', 'route' => 'wali.anak.presensi'],
        ['label' => 'Progress', 'route' => 'wali.anak.progres'],
        ['label' => 'Log Keluar/Masuk', 'route' => 'wali.anak.log'],
    ];
    $code = $santri->code ?? '';
    $currentRoute = request()->route()->getName();
    $validRoutes = collect($tabs)->pluck('route')->toArray();
    $switchRoute = in_array($currentRoute, $validRoutes, true) ? $currentRoute : 'wali.anak.overview';
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.16em] text-emerald-600">Sedang dipantau</p>
            <h2 class="text-2xl font-semibold text-gray-900">{{ $santri->nama_lengkap ?? $santri->user?->name }}</h2>
            @if($santri->kelas?->nama)
                <p class="mt-1 text-sm text-gray-500">Kelas {{ $santri->kelas->nama }}</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                <i data-lucide="user-cog" class="w-4 h-4"></i> Profil Wali
            </a>
        </div>
    </div>

    <div class="mt-4 flex gap-2 overflow-x-auto pb-1 text-sm whitespace-nowrap">
        @foreach($tabs as $tab)
            @php $isActive = request()->routeIs($tab['route']); @endphp
            <a href="{{ $code ? route($tab['route'], $code) : '#' }}"
               class="shrink-0 inline-flex items-center gap-2 rounded-full border px-4 py-2 {{ $isActive ? 'border-emerald-500 bg-emerald-50 text-emerald-700 font-semibold' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }} {{ $code ? '' : 'opacity-50 cursor-not-allowed pointer-events-none' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
