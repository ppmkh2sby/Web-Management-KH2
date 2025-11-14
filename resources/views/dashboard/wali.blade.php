@extends('layouts.app')

@section('title', 'Dashboard Wali Santri')
@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow p-6">
        <h1 class="text-2xl font-bold text-gray-800">Data Santri Anda</h1>
        <p class="text-gray-600 text-sm mt-1">Tertaut melalui kode unik.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        @forelse ($anak as $s)
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800">{{ $s->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">Kode: <span class="font-mono">{{ $s->kode_unik ?? '-' }}</span></p>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-gray-500">Belum ada santri yang terhubung.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
