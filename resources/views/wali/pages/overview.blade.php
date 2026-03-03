@extends('layouts.santri-modern')
@section('title','Monitoring Anak')

@section('content')
@include('wali.pages.partials.child-navigation')

@php
    $waliName = auth()->user()->name;
    $displayName = $santri->nama_lengkap ?? $santri->user?->name ?? 'Santri';
    $featureCards = [
        [
            'label' => 'Dashboard Anak',
            'desc' => 'Ringkasan performa anak',
            'icon' => 'layout-dashboard',
            'route' => route('wali.anak.overview', $santri->code),
            'accent' => 'border-emerald-100 bg-emerald-50/70',
        ],
        [
            'label' => 'Kehadiran',
            'desc' => 'Lihat presensi harian',
            'icon' => 'fingerprint',
            'route' => route('wali.anak.presensi', $santri->code),
            'accent' => 'border-sky-100 bg-sky-50/70',
        ],
        [
            'label' => 'Progress Keilmuan',
            'desc' => 'Pantau progres belajar',
            'icon' => 'book-open',
            'route' => route('wali.anak.progres', $santri->code),
            'accent' => 'border-violet-100 bg-violet-50/70',
        ],
        [
            'label' => 'Log Keluar/Masuk',
            'desc' => 'Riwayat aktivitas keluar',
            'icon' => 'door-open',
            'route' => route('wali.anak.log', $santri->code),
            'accent' => 'border-amber-100 bg-amber-50/70',
        ],
    ];

    $statusStyles = [
        'hadir' => 'bg-emerald-100 text-emerald-700',
        'izin' => 'bg-amber-100 text-amber-700',
        'alpa' => 'bg-rose-100 text-rose-700',
        'telat' => 'bg-yellow-100 text-yellow-700',
    ];
@endphp

<div class="mt-6 space-y-6">
    <div class="rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-white p-6">
        <p class="text-sm text-emerald-600">Assalamualaikum, {{ $waliName }}</p>
        <h2 class="mt-1 text-2xl font-semibold">Dashboard Anak: {{ $displayName }}</h2>
        <p class="mt-3 text-sm text-gray-600">
            Ringkasan ini menyesuaikan fitur yang aktif: Kehadiran, Progress Keilmuan, dan Log Keluar/Masuk.
        </p>
        <div class="mt-5 flex flex-wrap gap-3 text-sm">
            <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-white px-3 py-2">
                <i data-lucide="badge-check" class="w-4 h-4 text-emerald-500"></i>
                {{ $emailVerified ? 'Email wali terverifikasi' : 'Mohon verifikasi email wali' }}
            </span>
            <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-white px-3 py-2">
                <i data-lucide="id-card" class="w-4 h-4 text-emerald-500"></i>
                Kode santri: <span class="font-mono text-gray-900">{{ $santri->code ?? '-' }}</span>
            </span>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($featureCards as $card)
            <a href="{{ $card['route'] }}" class="rounded-2xl border {{ $card['accent'] }} p-4 shadow-sm hover:shadow transition">
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-white p-2 text-gray-700">
                        <i data-lucide="{{ $card['icon'] }}" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $card['label'] }}</p>
                        <p class="text-xs text-gray-500">{{ $card['desc'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Kehadiran Tercatat</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $kehadiranTotal }}</p>
            <p class="mt-1 text-xs text-emerald-700">Hadir {{ $hadir }} | Izin {{ $izin }} | Alpa {{ $alpa }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Persentase Kehadiran</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $kehadiranPercent }}%</p>
            <p class="mt-1 text-xs text-gray-500">Dari seluruh data presensi anak</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Progress Keilmuan</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $progressAverage }}%</p>
            <p class="mt-1 text-xs text-gray-500">{{ $progressCompleted }} selesai • {{ $progressInProgress }} berjalan</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Log Keluar/Masuk</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $logTotal }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $logThisMonth }} data bulan ini</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Kehadiran Terbaru</h3>
                    <p class="text-sm text-gray-500">5 data kehadiran terakhir</p>
                </div>
                <a href="{{ route('wali.anak.presensi', $santri->code) }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
            </div>
            @if($kehadiranRecent->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-gray-200 p-5 text-sm text-gray-500 text-center">
                    Belum ada data kehadiran.
                </div>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach($kehadiranRecent as $row)
                        <li class="rounded-2xl border border-gray-100 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900">{{ ucfirst($row->status) }}</p>
                                <span class="rounded-lg px-2 py-1 text-xs {{ $statusStyles[$row->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($row->status) }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</p>
                            <p class="mt-1 text-xs text-gray-600">{{ $row->keterangan ?? '-' }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Progress Keilmuan</h3>
                    <p class="text-sm text-gray-500">5 update progres terakhir</p>
                </div>
                <a href="{{ route('wali.anak.progres', $santri->code) }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
            </div>
            @if($progressRecent->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500 text-center">
                    Belum ada data progress.
                </div>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach($progressRecent as $row)
                        <li class="rounded-2xl border border-gray-100 p-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $row->judul ?? '-' }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ (int) ($row->capaian ?? 0) }} / {{ (int) ($row->target ?? 0) }} {{ $row->satuan ?? 'halaman' }}
                            </p>
                            <div class="mt-2 h-2 rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ min((int) ($row->persentase ?? 0), 100) }}%"></div>
                            </div>
                            <p class="mt-1 text-right text-xs font-semibold text-emerald-700">{{ (int) ($row->persentase ?? 0) }}%</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Log Keluar/Masuk</h3>
                    <p class="text-sm text-gray-500">5 data log terakhir</p>
                </div>
                <a href="{{ route('wali.anak.log', $santri->code) }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
            </div>
            @if($logRecent->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500 text-center">
                    Belum ada data log keluar/masuk.
                </div>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach($logRecent as $row)
                        @php
                            $parts = preg_split('/\s*-\s*/', (string) ($row->rentang ?? ''), 2) ?: [];
                            $keluar = trim((string) ($parts[0] ?? ''));
                            $masuk = trim((string) ($parts[1] ?? ''));
                        @endphp
                        <li class="rounded-2xl border border-gray-100 p-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $row->jenis ?? '-' }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($row->tanggal_pengajuan)->translatedFormat('d M Y') }}</p>
                            <p class="mt-1 text-xs text-gray-600">Keluar {{ $keluar !== '' ? $keluar : '-' }} | Masuk {{ $masuk !== '' ? $masuk : '-' }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $row->catatan ?? '-' }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection

