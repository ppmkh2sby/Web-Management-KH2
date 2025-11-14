@extends('layouts.santri-modern')
@section('title','Log Keluar/Masuk')

@section('content')
<div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 shadow-sm">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Log Keluar/Masuk</h2>
  </div>
  @if($logs->isEmpty())
    <p class="text-sm text-gray-500">Belum ada log izin.</p>
  @else
    {{-- table logs --}}
  @endif
</div>
@endsection
