@extends('layouts.santri-modern')
@section('title', ($isStaffView ?? false) ? 'Detail Progress Keilmuan Santri' : 'Progress Keilmuan Anak')
@section('content_panel_class', ($isStaffView ?? false) ? 'h-[calc(100vh-40px)] overflow-hidden' : 'h-[calc(100vh-40px)] overflow-y-auto')

@section('content')
@php
    $isStaffCompact = (bool) ($isStaffView ?? false);
@endphp
<div class="{{ $isStaffCompact ? 'flex h-full min-h-0 flex-col' : '' }}">
@if(!($isStaffView ?? false))
    @include('wali.pages.partials.child-navigation')
@else
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.16em] text-emerald-600">Detail progress santri</p>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $santri->nama_lengkap ?? $santri->user?->name }}</h2>
                @if($santri->kelas?->nama)
                    <p class="mt-0.5 text-xs text-gray-500">Kelas {{ $santri->kelas->nama }}</p>
                @endif
            </div>
            <a href="{{ $backUrl ?? route('santri.data.progres') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali ke daftar
            </a>
        </div>
    </div>
@endif

@php
    $normalize = function ($value) {
        return strtolower(trim((string) preg_replace('/\s+/', ' ', $value)));
    };

    $quranModules = array_map(fn ($i) => ['judul' => 'Juz '.$i, 'target' => 20], range(1, 30));
    $haditsModules = [
        ["judul" => "Mukhtaru Da'awat", 'target' => 171],
        ['judul' => 'Buku Saku Tata Krama', 'target' => 60],
        ['judul' => 'Al Khulasoh Fii Adabith Tholib', 'target' => 20],
        ['judul' => 'Materi Kelas Bacaan', 'target' => 37],
        ['judul' => 'K. Sholah', 'target' => 151],
        ["judul" => "Al-Khulashoh Fil Imla'", 'target' => 20],
        ["judul" => "Luzumul Jama'ah", 'target' => 40],
        ['judul' => 'Materi Kelas Pegon', 'target' => 30],
        ['judul' => 'K. Sholatin Nawafil', 'target' => 98],
        ['judul' => 'K. Shoum', 'target' => 98],
        ['judul' => 'Materi Kelas Lambatan', 'target' => 47],
        ["judul" => "K. Da'awat", 'target' => 65],
        ['judul' => 'K. Adab', 'target' => 95],
        ['judul' => 'K. Shifatil Jannati Wannar', 'target' => 85],
        ['judul' => 'K. Janaiz', 'target' => 79],
        ['judul' => 'K. Adillah', 'target' => 96],
        ['judul' => 'K. Manasik wal Jihad', 'target' => 51],
        ['judul' => 'Materi Kelas Cepatan', 'target' => 70],
        ['judul' => 'K. Haji', 'target' => 111],
        ['judul' => 'K. Manaskil Haji', 'target' => 116],
        ['judul' => 'K. Ahkam', 'target' => 124],
        ['judul' => 'K. Jihad', 'target' => 63],
        ['judul' => 'K. Imaroh', 'target' => 104],
        ['judul' => 'K. Imaroh min Kanzil ummal', 'target' => 122],
        ['judul' => 'Khutbah', 'target' => 152],
        ['judul' => 'Materi Kelas Saringan', 'target' => 63],
        ['judul' => 'Hidayatul Mustafidz Fit-Tajwid', 'target' => 98],
        ['judul' => 'K. Nikah', 'target' => 101],
        ['judul' => 'K. Faroidh', 'target' => 134],
        ['judul' => 'Syarah Asmaullohul Husna', 'target' => 39],
        ["judul" => "Syarah Do'a ASAD", 'target' => 35],
    ];

    $quranRecordMap = [];
    $haditsRecordMap = [];
    $fallbackHaditsMap = [];

    foreach ($items as $item) {
        $titleRaw = (string) ($item->judul ?? '');
        $titleKey = $normalize($titleRaw);
        if ($titleKey === '') {
            continue;
        }

        $level = strtolower((string) ($item->level ?? ''));
        if ($level === 'al-quran') {
            $quranRecordMap[$titleKey] = $item;
        } elseif ($level === 'al-hadits') {
            $haditsRecordMap[$titleKey] = $item;
        } else {
            $fallbackHaditsMap[$titleKey] = $item;
        }

        if (preg_match('/juz\s*([0-9]{1,2})/i', $titleRaw, $match)) {
            $juzNumber = (int) ($match[1] ?? 0);
            if ($juzNumber >= 1 && $juzNumber <= 30) {
                $quranRecordMap[$normalize('Juz '.$juzNumber)] = $item;
            }
        }
    }

    if (empty($haditsRecordMap)) {
        $haditsRecordMap = $fallbackHaditsMap;
    }

    $buildEntries = function (array $modules, array $lookup, string $type) use ($normalize) {
        return collect($modules)->map(function (array $module) use ($lookup, $normalize, $type) {
            $title = (string) ($module['judul'] ?? '');
            $target = (int) ($module['target'] ?? 0);
            $record = $lookup[$normalize($title)] ?? null;
            $recordTarget = (int) ($record->target ?? 0);
            $effectiveTarget = $recordTarget > 0 ? $recordTarget : $target;
            $capaian = max((int) ($record->capaian ?? 0), 0);
            $persentase = $effectiveTarget > 0
                ? (int) min(100, round(($capaian / $effectiveTarget) * 100))
                : (int) ($record->persentase ?? 0);

            return [
                'type' => $type,
                'judul' => $title,
                'target' => $effectiveTarget,
                'capaian' => min($capaian, max($effectiveTarget, 0)),
                'persentase' => max(min($persentase, 100), 0),
                'updated_at' => $record->terakhir_setor ?? $record->updated_at ?? null,
            ];
        })->values();
    };

    $buildStats = function ($entries) {
        $total = $entries->count();

        return [
            'total' => $total,
            'completed' => $entries->filter(fn ($entry) => ($entry['persentase'] ?? 0) >= 100)->count(),
            'inProgress' => $entries->filter(fn ($entry) => ($entry['persentase'] ?? 0) > 0 && ($entry['persentase'] ?? 0) < 100)->count(),
            'average' => $total > 0 ? (int) round($entries->avg('persentase')) : 0,
        ];
    };

    $quranEntries = $buildEntries($quranModules, $quranRecordMap, 'al-quran');
    $haditsEntries = $buildEntries($haditsModules, $haditsRecordMap, 'al-hadits');
    $quranPages = $quranEntries->chunk(5)->values();
    $haditsPages = $haditsEntries->chunk(5)->values();
    $quranStats = $buildStats($quranEntries);
    $haditsStats = $buildStats($haditsEntries);
    $quranUpdates = $quranEntries->filter(fn ($entry) => ! empty($entry['updated_at']))->sortByDesc('updated_at')->take(8)->values();
    $haditsUpdates = $haditsEntries->filter(fn ($entry) => ! empty($entry['updated_at']))->sortByDesc('updated_at')->take(8)->values();
@endphp

<div
    class="{{ $isStaffCompact ? 'mt-3 flex min-h-0 flex-1 flex-col gap-3' : 'mt-6 flex min-h-0 flex-1 flex-col gap-5' }}"
    x-data="waliProgressSwitcher({ quranPages: {{ $quranPages->count() }}, haditsPages: {{ $haditsPages->count() }} })"
>
    <div class="rounded-2xl border border-gray-100 bg-white {{ $isStaffCompact ? 'p-4' : 'p-5' }} shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Progress Keilmuan</p>
                <p class="text-sm text-gray-500">{{ ($isStaffView ?? false) ? 'Ringkasan capaian Al-Quran dan Hadits santri.' : 'Ringkasan capaian Al-Quran dan Hadits anak Anda.' }}</p>
            </div>
        </div>
        <div class="{{ $isStaffCompact ? 'mt-3 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-4' : 'mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4' }}">
            <div class="rounded-2xl border border-gray-100 bg-gray-50 {{ $isStaffCompact ? 'p-3' : 'p-4' }}">
                <p class="{{ $isStaffCompact ? 'text-xs text-gray-500' : 'text-sm text-gray-500' }}">Total Modul</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-gray-900" x-show="activeCategory === 'al-quran'">{{ $quranStats['total'] }}</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-gray-900" x-show="activeCategory === 'al-hadits'">{{ $haditsStats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 {{ $isStaffCompact ? 'p-3' : 'p-4' }}">
                <p class="{{ $isStaffCompact ? 'text-xs text-gray-500' : 'text-sm text-gray-500' }}">Selesai</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-emerald-600" x-show="activeCategory === 'al-quran'">{{ $quranStats['completed'] }}</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-emerald-600" x-show="activeCategory === 'al-hadits'">{{ $haditsStats['completed'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 {{ $isStaffCompact ? 'p-3' : 'p-4' }}">
                <p class="{{ $isStaffCompact ? 'text-xs text-gray-500' : 'text-sm text-gray-500' }}">Sedang Dikerjakan</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-orange-500" x-show="activeCategory === 'al-quran'">{{ $quranStats['inProgress'] }}</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-orange-500" x-show="activeCategory === 'al-hadits'">{{ $haditsStats['inProgress'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 {{ $isStaffCompact ? 'p-3' : 'p-4' }}">
                <p class="{{ $isStaffCompact ? 'text-xs text-gray-500' : 'text-sm text-gray-500' }}">Rata-rata Pencapaian</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-slate-900" x-show="activeCategory === 'al-quran'">{{ $quranStats['average'] }}%</p>
                <p class="{{ $isStaffCompact ? 'mt-1 text-2xl' : 'mt-2 text-3xl' }} font-semibold text-slate-900" x-show="activeCategory === 'al-hadits'">{{ $haditsStats['average'] }}%</p>
            </div>
        </div>
    </div>

    <div class="{{ $isStaffCompact ? 'grid min-h-0 flex-1 gap-3 xl:grid-cols-[minmax(0,1fr)_280px]' : 'grid min-h-0 flex-1 gap-5 xl:grid-cols-[1fr_320px]' }}">
        <div class="flex h-full {{ $isStaffCompact ? 'min-h-0' : 'min-h-[460px]' }} flex-col rounded-2xl border border-gray-100 bg-white {{ $isStaffCompact ? 'p-4' : 'p-5' }} shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="{{ $isStaffCompact ? 'text-xs text-gray-500' : 'text-sm text-gray-500' }}">Ringkasan Progress</p>
                    <h3 class="{{ $isStaffCompact ? 'text-lg' : 'text-xl' }} font-semibold text-gray-900" x-show="activeCategory === 'al-quran'">Materi Al-Quran</h3>
                    <h3 class="{{ $isStaffCompact ? 'text-lg' : 'text-xl' }} font-semibold text-gray-900" x-show="activeCategory === 'al-hadits'">Materi Al-Hadits</h3>
                    <p class="{{ $isStaffCompact ? 'text-xs text-gray-500' : 'text-sm text-gray-500' }}">Progress halaman dan jumlah juz ditampilkan per modul.</p>
                </div>
                <div class="inline-flex rounded-xl border border-gray-200 p-2 pr-2 pl-2">
                    <button
                        type="button"
                        @click="setCategory('al-quran')"
                        :class="activeCategory === 'al-quran' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                        class="rounded-lg {{ $isStaffCompact ? 'px-3 py-2 text-[12px]' : 'px-3 py-1.5 text-sm' }} font-semibold transition"
                    >
                        Quran
                    </button>
                    <button
                        type="button"
                        @click="setCategory('al-hadits')"
                        :class="activeCategory === 'al-hadits' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                        class="rounded-lg {{ $isStaffCompact ? 'px-3 py-2 text-[12px]' : 'px-3 py-1.5 text-sm' }} font-semibold transition"
                    >
                        Hadits
                    </button>
                </div>
            </div>

            <div class="{{ $isStaffCompact ? 'mt-5 overflow-x-auto' : 'mt-5 flex-1 overflow-x-auto' }} rounded-xl border border-gray-200 bg-white">
                <div class="min-w-[560px] grid grid-cols-[minmax(120px,165px)_minmax(145px,170px)_1fr] items-center border-b border-gray-200 bg-gray-50 px-4 {{ $isStaffCompact ? 'py-1.5' : 'py-2' }} text-[10px] font-semibold uppercase text-gray-600">
                    <div class="flex items-center gap-1">
                        <span x-text="activeCategory === 'al-hadits' ? 'Hadits' : 'Juz'"></span>
                        <i data-lucide="chevrons-up-down" class="h-3 w-3 text-gray-500"></i>
                    </div>
                    <div class="flex items-center gap-1">
                        Progress Halaman
                        <i data-lucide="info" class="h-3 w-3 text-gray-500"></i>
                    </div>
                    <div class="flex items-center justify-center gap-1 text-center">
                        Persentase
                        <i data-lucide="info" class="h-3 w-3 text-gray-500"></i>
                    </div>
                </div>

                <div>
                    @foreach($quranPages as $pageIndex => $pageItems)
                        <div
                            x-show="activeCategory === 'al-quran' && currentPage === {{ $pageIndex + 1 }}"
                            x-cloak
                            class="divide-y divide-gray-200"
                        >
                            @foreach($pageItems as $entry)
                                <div class="min-w-[560px] grid grid-cols-[minmax(120px,165px)_minmax(145px,170px)_1fr] items-center gap-3 px-4 {{ $isStaffCompact ? 'py-1.5' : 'py-3' }} hover:bg-gray-50/80">
                                    <p class="truncate {{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-medium text-gray-900">{{ $entry['judul'] }}</p>
                                    <p class="inline-flex items-baseline gap-1 {{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-semibold text-gray-800 tabular-nums">
                                        <span class="inline-block w-[3ch] text-right">{{ $entry['capaian'] }}</span>
                                        <span class="text-gray-500">/</span>
                                        <span class="inline-block w-[3ch] text-left">{{ $entry['target'] }}</span>
                                    </p>
                                    <div class="flex items-center gap-3">
                                        <div class="h-1.5 flex-1 rounded-full bg-gray-200">
                                            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $entry['persentase'] }}%"></div>
                                        </div>
                                        <span class="inline-block w-[5ch] text-right {{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-semibold text-gray-800 tabular-nums">{{ $entry['persentase'] }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    @foreach($haditsPages as $pageIndex => $pageItems)
                        <div
                            x-show="activeCategory === 'al-hadits' && currentPage === {{ $pageIndex + 1 }}"
                            x-cloak
                            class="divide-y divide-gray-200"
                        >
                            @foreach($pageItems as $entry)
                                <div class="min-w-[560px] grid grid-cols-[minmax(120px,165px)_minmax(145px,170px)_1fr] items-center gap-3 px-4 {{ $isStaffCompact ? 'py-1.5' : 'py-3' }} hover:bg-gray-50/80">
                                    <p class="truncate {{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-medium text-gray-900">{{ $entry['judul'] }}</p>
                                    <p class="inline-flex items-baseline gap-1 {{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-semibold text-gray-800 tabular-nums">
                                        <span class="inline-block w-[3ch] text-right">{{ $entry['capaian'] }}</span>
                                        <span class="text-gray-500">/</span>
                                        <span class="inline-block w-[3ch] text-left">{{ $entry['target'] }}</span>
                                    </p>
                                    <div class="flex items-center gap-3">
                                        <div class="h-1.5 flex-1 rounded-full bg-gray-200">
                                            <div class="h-full rounded-full bg-sky-500" style="width: {{ $entry['persentase'] }}%"></div>
                                        </div>
                                        <span class="inline-block w-[5ch] text-right {{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-semibold text-gray-800 tabular-nums">{{ $entry['persentase'] }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="{{ $isStaffCompact ? 'mt-2.5' : 'mt-4' }} flex items-center justify-between gap-3">
                <button
                    type="button"
                    @click="prevPage()"
                    :disabled="currentPage <= 1"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 {{ $isStaffCompact ? 'px-2.5 py-1.5 text-xs' : 'px-3 py-2 text-sm' }} text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Previous
                </button>

                <div class="flex items-center gap-1">
                    <div class="flex items-center gap-1" x-show="activeCategory === 'al-quran'">
                        @foreach($quranPages as $pageIndex => $pageItems)
                            <button
                                type="button"
                                @click="goToPage({{ $pageIndex + 1 }})"
                                :class="currentPage === {{ $pageIndex + 1 }} ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'"
                                class="{{ $isStaffCompact ? 'h-7 w-7 text-xs' : 'h-8 w-8 text-sm' }} rounded-lg font-medium transition"
                            >
                                {{ $pageIndex + 1 }}
                            </button>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-1" x-show="activeCategory === 'al-hadits'">
                        @foreach($haditsPages as $pageIndex => $pageItems)
                            <button
                                type="button"
                                @click="goToPage({{ $pageIndex + 1 }})"
                                :class="currentPage === {{ $pageIndex + 1 }} ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'"
                                class="{{ $isStaffCompact ? 'h-7 w-7 text-xs' : 'h-8 w-8 text-sm' }} rounded-lg font-medium transition"
                            >
                                {{ $pageIndex + 1 }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <button
                    type="button"
                    @click="nextPage()"
                    :disabled="currentPage >= totalPages"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 {{ $isStaffCompact ? 'px-2.5 py-1.5 text-xs' : 'px-3 py-2 text-sm' }} text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Next
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </button>
            </div>
        </div>

        <div class="flex h-full {{ $isStaffCompact ? 'min-h-0' : 'min-h-[460px]' }} flex-col rounded-2xl border border-gray-100 bg-white {{ $isStaffCompact ? 'p-4' : 'p-5' }} shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center justify-between">
                    <h3 class="{{ $isStaffCompact ? 'text-base' : 'text-lg' }} font-semibold text-gray-900">Update Terbaru</h3>
                </div>
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600" x-show="activeCategory === 'al-quran'">{{ $quranUpdates->count() }} catatan</span>
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600" x-show="activeCategory === 'al-hadits'">{{ $haditsUpdates->count() }} catatan</span>
            </div>

            <div class="{{ $isStaffCompact ? 'mt-3' : 'mt-4' }} flex-1 overflow-y-auto pr-1">
                <ul class="{{ $isStaffCompact ? 'space-y-2.5' : 'space-y-3' }}" x-show="activeCategory === 'al-quran'">
                    @forelse($quranUpdates as $entry)
                        <li class="rounded-xl border border-gray-100 {{ $isStaffCompact ? 'p-2.5' : 'p-3' }}">
                            <p class="{{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-semibold text-gray-900">{{ $entry['judul'] }}</p>
                            <p class="mt-1 text-xs text-gray-500">Pembaruan: {{ optional($entry['updated_at'])->diffForHumans() ?? '-' }}</p>
                            <p class="mt-2 text-xs text-gray-600">{{ $entry['capaian'] }} / {{ $entry['target'] }} halaman - {{ $entry['persentase'] }}%</p>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                            Belum ada update Al-Quran.
                        </li>
                    @endforelse
                </ul>

                <ul class="{{ $isStaffCompact ? 'space-y-2.5' : 'space-y-3' }}" x-show="activeCategory === 'al-hadits'">
                    @forelse($haditsUpdates as $entry)
                        <li class="rounded-xl border border-gray-100 {{ $isStaffCompact ? 'p-2.5' : 'p-3' }}">
                            <p class="{{ $isStaffCompact ? 'text-[13px]' : 'text-sm' }} font-semibold text-gray-900">{{ $entry['judul'] }}</p>
                            <p class="mt-1 text-xs text-gray-500">Pembaruan: {{ optional($entry['updated_at'])->diffForHumans() ?? '-' }}</p>
                            <p class="mt-2 text-xs text-gray-600">{{ $entry['capaian'] }} / {{ $entry['target'] }} halaman - {{ $entry['persentase'] }}%</p>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                            Belum ada update Hadits.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
</div>

@once
    <script>
        function waliProgressSwitcher(config) {
            return {
                activeCategory: 'al-quran',
                currentPage: 1,
                quranPages: Number(config.quranPages || 1),
                haditsPages: Number(config.haditsPages || 1),
                get totalPages() {
                    return this.activeCategory === 'al-hadits' ? this.haditsPages : this.quranPages;
                },
                setCategory(category) {
                    this.activeCategory = category;
                    this.currentPage = 1;
                },
                goToPage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                    }
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage += 1;
                    }
                },
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage -= 1;
                    }
                },
            };
        }
    </script>
@endonce
@endsection
