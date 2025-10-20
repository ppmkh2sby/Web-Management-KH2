@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 flex justify-center items-center font-[Poppins]">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-[500px] text-center">
        <h1 class="text-3xl font-bold text-gray-700 mb-4">Dashboard Santri</h1>

        <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold mb-2">Kode Unik Anda</h2>
            <p class="text-2xl font-bold tracking-wide select-all">
                {{ $user->kode_unik ?? 'Belum ada kode' }}
            </p>
        </div>

        <p class="mt-4 text-gray-500 text-sm">
            Berikan kode ini kepada wali santri Anda untuk menghubungkan akun.
        </p>

        <a href="{{ route('logout') }}"
            class="inline-block mt-6 bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg transition duration-300">
            Logout
        </a>
    </div>
</div>
@endsection
