@extends('layouts.santri-modern')
@section('title','Monitoring Anak')

@section('content')
@include('wali.pages.partials.child-navigation')

@php
    $waliName = auth()->user()->name;
    $displayName = $santri->nama_lengkap ?? $santri->user?->name ?? 'Santri';
    $infoCards = [
        [
            'label' => 'Hadir Bulan Ini',
            'value' => $hadir,
            'desc'  => 'Kehadiran tercatat status hadir',
            'icon'  => 'check-circle',
            'accent'=> 'bg-emerald-50 text-emerald-600',
        ],
        [
            'label' => 'Izin Tercatat',
            'value' => $izin,
            'desc'  => 'Butuh tindak lanjut musyrif',
            'icon'  => 'shield',
            'accent'=> 'bg-amber-50 text-amber-600',
        ],
        [
            'label' => 'Alpa',
            'value' => $alpa,
            'desc'  => 'Segera konfirmasi ke pengurus',
            'icon'  => 'alert-triangle',
            'accent'=> 'bg-rose-50 text-rose-600',
        ],
    ];
@endphp

<div class="mt-6 space-y-6">
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-white p-6">
            <p class="text-sm text-emerald-600">Assalamualaikum, {{ $waliName }}</p>
            <h2 class="mt-1 text-2xl font-semibold">Pantau progres {{ $displayName }}</h2>
            <p class="mt-3 text-sm text-gray-600 max-w-3xl">
                Ringkasan ini memperlihatkan aktivitas terbaru anak Anda mulai dari kehadiran, jadwal majelis hari ini,
                hingga pengumuman penting dari pesantren. Data otomatis disinkronkan ketika santri melakukan presensi atau pembaruan lainnya.
            </p>
            <div class="mt-6 flex flex-wrap gap-3 text-sm">
                <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm border border-emerald-100">
                    <i data-lucide="badge-check" class="w-4 h-4 text-emerald-500"></i>
                    {{ $emailVerified ? 'Email wali sudah terverifikasi' : 'Mohon verifikasi email untuk notifikasi' }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm border border-emerald-100">
                    <i data-lucide="id-card" class="w-4 h-4 text-emerald-500"></i>
                    Kode santri: <span class="font-mono text-gray-900">{{ $santri->code ?? '-' }}</span>
                </span>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
            <p class="text-sm text-gray-500">Akses Cepat</p>
            <div class="space-y-2 text-sm">
                <a href="{{ route('wali.anak.presensi', $santri->code) }}" class="flex items-center gap-2 rounded-xl border border-emerald-100 px-3 py-2 hover:bg-emerald-50">
                    <i data-lucide="fingerprint" class="w-4 h-4 text-emerald-600"></i> Lihat presensi lengkap
                </a>
                <a href="{{ route('wali.anak.progres', $santri->code) }}" class="flex items-center gap-2 rounded-xl border border-blue-100 px-3 py-2 hover:bg-blue-50">
                    <i data-lucide="sparkles" class="w-4 h-4 text-blue-600"></i> Pantau progres hafalan/kelas
                </a>
                <a href="{{ route('wali.anak.log', $santri->code) }}" class="flex items-center gap-2 rounded-xl border border-amber-100 px-3 py-2 hover:bg-amber-50">
                    <i data-lucide="door-open" class="w-4 h-4 text-amber-600"></i> Lihat izin keluar/masuk
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach($infoCards as $card)
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 rounded-2xl {{ $card['accent'] }}">
                        <i data-lucide="{{ $card['icon'] }}" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <p class="text-xs uppercase text-gray-500">{{ $card['label'] }}</p>
                        <div class="text-2xl font-semibold text-gray-900">{{ $card['value'] }}</div>
                        <p class="text-xs text-gray-400">{{ $card['desc'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Jadwal Hari Ini</h3>
                    <p class="text-sm text-gray-500">Menampilkan jadwal kelas {{ $santri->kelas->nama ?? 'santri' }}.</p>
                </div>
                <span class="text-xs text-gray-500">{{ $jadwalHariIni->count() }} agenda</span>
            </div>
            @if($jadwalHariIni->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-gray-200 p-5 text-sm text-gray-500 text-center">
                    Jadwal belum tersedia. Silakan hubungi pengurus kelas untuk pembaruan.
                </div>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach($jadwalHariIni as $jadwal)
                        <li class="rounded-2xl border border-gray-100 p-4">
                            <div class="flex items-center justify-between text-sm font-semibold text-gray-900">
                                <span>{{ $jadwal->mapel->nama ?? $jadwal->judul ?? 'Majelis' }}</span>
                                <span class="text-xs text-gray-500">{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</span>
                            </div>
                            <p class="text-xs text-gray-500">Pembimbing: {{ $jadwal->guru->nama ?? '-' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ \Illuminate\Support\Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Pengumuman Terbaru</h3>
                <span class="text-xs text-gray-500">{{ $pengumuman->count() }} info</span>
            </div>
            @forelse($pengumuman as $item)
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-900">{{ $item->judul ?? $item->title ?? 'Pengumuman' }}</p>
                    <p class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($item->created_at ?? now())->diffForHumans() }}</p>
                    <p class="mt-2 text-sm text-gray-600">{{ $item->isi ?? $item->content ?? 'Detail pengumuman belum tersedia.' }}</p>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500 text-center">
                    Belum ada pengumuman terbaru.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
