@extends('layouts.santri-modern')
@section('title','Profil')

@section('content')
<div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 shadow-sm">
  <h2 class="text-lg font-semibold mb-4">Profil Santri</h2>
  <form method="POST" action="{{ route('santri.profile') }}">
    @csrf
    {{-- contoh tampilan saja; ganti action ke controller update profil jika sudah ada --}}
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm text-gray-500">Nama</label>
        <input class="mt-1 w-full rounded-lg border bg-white/50 dark:bg-gray-900 dark:border-gray-700" value="{{ $santri->nama ?? auth()->user()->name }}" />
      </div>
      <div>
        <label class="text-sm text-gray-500">NIS</label>
        <input class="mt-1 w-full rounded-lg border bg-white/50 dark:bg-gray-900 dark:border-gray-700" value="{{ $santri->nis ?? '' }}" />
      </div>
      <div>
        <label class="text-sm text-gray-500">Kelas</label>
        <input disabled class="mt-1 w-full rounded-lg border bg-gray-50 dark:bg-gray-800 dark:border-gray-700" value="{{ optional($santri->kelas)->nama ?? '-' }}" />
      </div>
      <div>
        <label class="text-sm text-gray-500">Kontak Wali</label>
        <input class="mt-1 w-full rounded-lg border bg-white/50 dark:bg-gray-900 dark:border-gray-700" value="{{ optional($santri->wali)->telepon ?? '' }}" />
      </div>
    </div>
    <div class="mt-4">
      <button class="px-4 py-2 rounded-lg bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900">Simpan</button>
    </div>
  </form>
</div>
@endsection
