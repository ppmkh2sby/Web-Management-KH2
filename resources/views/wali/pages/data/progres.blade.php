@extends('layouts.santri-modern')
@section('title','Progress Keilmuan Anak')

@section('content')
@include('wali.pages.partials.child-navigation')

@php
    $moduleCount = $items->count();
    $completed = $items->filter(fn ($item) => $item->capaian >= $item->target)->count();
    $inProgress = max($moduleCount - $completed, 0);
    $avgPercent = $moduleCount > 0 ? round($items->avg('persentase')) : 0;
    $recentUpdates = $items->sortByDesc(fn ($item) => $item->terakhir)->take(3);
@endphp

<div class="mt-6 space-y-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Progress Keilmuan</p>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $santri->nama_lengkap ?? 'Santri' }}</h2>
                <p class="text-sm text-gray-500">Pantau tiap modul yang sedang ditempuh anak Anda.</p>
            </div>
            <a href="{{ route('wali.anak.overview', $santri->code) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-sm text-gray-500">Total Modul</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $moduleCount }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-sm text-gray-500">Selesai</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $completed }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-sm text-gray-500">Sedang Dikerjakan</p>
                <p class="mt-2 text-3xl font-semibold text-orange-500">{{ $inProgress }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-sm text-gray-500">Rata-rata Pencapaian</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $avgPercent }}%</p>
            </div>
        </div>
    </div>

    @if($items->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
            Belum ada data progres keilmuan. Silakan hubungi pengurus atau musyrif untuk memperbarui capaian belajar anak.
        </div>
    @else
        <div class="grid gap-5 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                @foreach($items as $module)
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase text-gray-400">{{ $module->level }}</p>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $module->judul }}</h3>
                                <p class="text-sm text-gray-500">Pembimbing: {{ $module->pembimbing }}</p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">
                                Target {{ $module->target }} {{ $module->satuan }}
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span>Capaian</span>
                                <span>{{ $module->capaian }} / {{ $module->target }} {{ $module->satuan }}</span>
                            </div>
                            <div class="mt-2 h-3 rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ min($module->persentase, 100) }}%;"></div>
                            </div>
                            <div class="mt-2 text-right text-xs text-emerald-600 font-semibold">{{ $module->persentase }}%</div>
                        </div>
                        <p class="mt-4 text-sm text-gray-600">{{ $module->catatan }}</p>
                        <p class="mt-2 text-xs text-gray-400">Pembaruan terakhir: {{ optional($module->terakhir)->translatedFormat('d M Y') ?? '-' }}</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Update Terbaru</h3>
                    <span class="text-xs text-gray-500">{{ $recentUpdates->count() }} catatan</span>
                </div>
                <ul class="mt-4 space-y-3">
                    @foreach($recentUpdates as $module)
                        <li class="rounded-2xl border border-gray-100 p-3">
                            <p class="text-sm font-semibold text-gray-900">{{ $module->judul }}</p>
                            <p class="text-xs text-gray-500">Pemutakhiran: {{ optional($module->terakhir)->diffForHumans() ?? '-' }}</p>
                            <p class="mt-2 text-xs text-gray-600">
                                {{ $module->capaian }} / {{ $module->target }} {{ $module->satuan }} · {{ $module->persentase }}%
                            </p>
                            <span class="mt-2 inline-flex items-center gap-2 text-xs text-emerald-600">
                                <i data-lucide="sparkles" class="w-3 h-3"></i> {{ $module->catatan }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection
