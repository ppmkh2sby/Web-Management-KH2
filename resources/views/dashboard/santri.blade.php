@extends('layouts.app')

@section('title', 'Dashboard Santri')
@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-2xl shadow p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Dashboard Santri</h1>
        <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold mb-2">Kode Unik Anda</h2>
            <p class="text-2xl font-bold tracking-wide select-all">{{ $user->kode_unik ?? 'Belum ada kode' }}</p>
        </div>
        <p class="mt-4 text-gray-500 text-sm">Berikan kode ini kepada wali santri untuk menghubungkan akun.</p>
    </div>
</div>
@endsection
