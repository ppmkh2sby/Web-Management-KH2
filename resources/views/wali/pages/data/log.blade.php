@extends('layouts.santri-modern')
@section('title','Log Keluar/Masuk Anak')
@section('content')
@include('wali.pages.partials.child-navigation')
<div class="mt-6 space-y-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Log Keluar/Masuk</p>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $santri->nama_lengkap ?? 'Santri' }}</h2>
                <p class="text-sm text-gray-500">Pantau seluruh data keluar/masuk anak Anda.</p>
            </div>
            <a href="{{ route('wali.anak.overview', $santri->code) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
        </div>
        <p class="mt-2 text-sm text-gray-500">Data ini menampilkan riwayat log yang diinput langsung oleh akun santri.</p>
    </div>

    @if($logs->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
            Belum ada data log keluar/masuk.
        </div>
    @else
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold mb-3">Riwayat Lengkap</h3>
                <span class="text-xs text-gray-500">{{ $logs->count() }} data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2 pr-4">Tanggal</th>
                            <th class="py-2 pr-4">Tujuan</th>
                            <th class="py-2 pr-4">Waktu Keluar</th>
                            <th class="py-2 pr-4">Waktu Masuk</th>
                            <th class="py-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $parts = preg_split('/\s*-\s*/', (string) ($log->rentang ?? ''), 2) ?: [];
                                $waktuKeluar = trim((string) ($parts[0] ?? ''));
                                $waktuMasuk = trim((string) ($parts[1] ?? ''));
                            @endphp
                            <tr class="border-t border-gray-100">
                                <td class="py-2 pr-4">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</td>
                                <td class="py-2 pr-4">{{ $log->jenis }}</td>
                                <td class="py-2 pr-4">{{ $waktuKeluar !== '' ? $waktuKeluar : '-' }}</td>
                                <td class="py-2 pr-4">{{ $waktuMasuk !== '' ? $waktuMasuk : '-' }}</td>
                                <td class="py-2">{{ $log->catatan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

