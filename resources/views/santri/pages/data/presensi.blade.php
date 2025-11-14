@extends('layouts.santri-modern')
@section('title','Presensi')

@section('content')
<div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 shadow-sm">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Presensi Terakhir</h2>
    <a href="{{ route('santri.data.index') }}" class="text-sm underline">Kembali</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-gray-500">
          <th class="py-2">Tanggal</th>
          <th class="py-2">Status</th>
          <th class="py-2">Keterangan</th>
        </tr>
      </thead>
      <tbody>
                @php
        $statusStyles = [
            'hadir' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
            'izin'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            'alpa'  => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
        ];
        @endphp
        @forelse($data as $row)
          <tr class="border-t dark:border-gray-800">
            <td class="py-2">{{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
            <td class="py-2">
                <span class="px-2 py-1 rounded-lg text-xs {{ $statusStyles[$row->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ ucfirst($row->status) }}
                </span>
            </td>
            <td class="py-2">{{ $row->keterangan ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="py-3 text-center text-gray-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
