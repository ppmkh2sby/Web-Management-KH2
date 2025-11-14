@extends('layouts.santri-modern')
@section('title','Progres Keilmuan')

@section('content')
<div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 shadow-sm">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Progres Keilmuan</h2>
    <a href="{{ route('santri.data.index') }}" class="text-sm underline">Kembali</a>
  </div>
  @if($items->isEmpty())
    <p class="text-sm text-gray-500">Belum ada data progres. (Hub. pengurus/guru untuk pembaruan)</p>
  @else
    {{-- render list progres di sini --}}
  @endif
</div>
@endsection
