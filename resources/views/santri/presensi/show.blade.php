@extends('layouts.santri-modern')
@section('title','Detail Presensi')

@section('content')
<div class="space-y-4">
  <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-sm text-emerald-700"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali</a>
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm space-y-3">
    <h3 class="text-lg font-semibold text-gray-900">Detail Presensi</h3>
    <dl class="text-sm text-gray-700 space-y-2">
      <div><span class="font-medium">Santri:</span> {{ $presensi->nama }} (Tim: {{ $presensi->santri->tim ?? '-' }})</div>
      <div><span class="font-medium">Kategori:</span> {{ ucfirst($presensi->kegiatan->kategori ?? '-') }}</div>
      <div><span class="font-medium">Waktu:</span> {{ ucfirst($presensi->waktu) }}</div>
      <div><span class="font-medium">Status:</span> {{ ucfirst($presensi->status) }}</div>
      <div><span class="font-medium">Catatan:</span> {{ $presensi->catatan ?? '-' }}</div>
      <div><span class="font-medium">Diperbarui:</span> {{ $presensi->updated_at?->translatedFormat('d M Y H:i') }}</div>
    </dl>
  </div>
</div>
@endsection
