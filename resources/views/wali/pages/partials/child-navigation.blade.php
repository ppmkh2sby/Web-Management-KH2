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

<div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase text-emerald-500 tracking-[0.2em]">Sedang dipantau</p>
            <h2 class="text-2xl font-semibold text-gray-900">{{ $santri->nama_lengkap ?? $santri->user?->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                Kode: <span class="font-mono text-gray-900">{{ $code ?: 'Belum tersedia' }}</span>
                @if($santri->kelas?->nama)
                    • Kelas {{ $santri->kelas->nama }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            @if(isset($santriList) && $santriList->isNotEmpty())
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i data-lucide="shuffle" class="w-4 h-4"></i>
                    <label class="sr-only" for="child-switcher">Ganti Anak</label>
                    <select id="child-switcher" class="rounded-xl border border-gray-200 px-3 py-2 bg-white">
                        @foreach($santriList as $item)
                            @php $itemCode = $item->code; @endphp
                            <option value="{{ $itemCode ? route($switchRoute, $itemCode) : '' }}"
                                {{ $itemCode === $code ? 'selected' : '' }}
                                {{ $itemCode ? '' : 'disabled' }}>
                                {{ $item->nama_lengkap ?? $item->user?->name }}
                                {{ $itemCode ? '(' . $itemCode . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">
                <i data-lucide="user-cog" class="w-4 h-4"></i> Perbarui Profil Wali
            </a>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-2 text-sm">
        @foreach($tabs as $tab)
            @php $isActive = request()->routeIs($tab['route']); @endphp
            <a href="{{ $code ? route($tab['route'], $code) : '#' }}"
               class="inline-flex items-center gap-2 rounded-full px-4 py-2 border {{ $isActive ? 'border-emerald-500 bg-emerald-50 text-emerald-700 font-semibold' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}
                    {{ $code ? '' : 'opacity-50 cursor-not-allowed pointer-events-none' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('child-switcher');
            if (!select) return;
            select.addEventListener('change', () => {
                if (!select.value) { return; }
                window.location.href = select.value;
            });
        });
    </script>
@endonce
