@extends('layouts.app')

@section('title', 'Dashboard Wali Santri')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-indigo-100 py-10 px-5 font-[Poppins]">
    <!-- HEADER -->
    <div class="max-w-5xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row justify-between items-center bg-white shadow-md p-6 rounded-2xl border border-gray-200">
            <div>
                <h1 class="text-3xl font-bold text-gray-700">Dashboard Wali Santri</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Selamat datang, <span class="font-semibold text-indigo-500">{{ Auth::user()->name }}</span> 👋
                </p>
            </div>
            <div class="mt-4 md:mt-0 text-sm text-gray-500">
                <p>📧 {{ Auth::user()->email }}</p>
                <p>📱 {{ Auth::user()->phone ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- RINGKASAN -->
    <div class="max-w-5xl mx-auto mb-6">
        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl flex items-center justify-between px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-700">Data Santri Anda</h2>
            <span class="text-sm bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full shadow">
                Total: {{ $anak->count() }} Santri
            </span>
        </div>
    </div>

    <!-- DAFTAR SANTRI -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($anak as $santri)
            <div class="bg-white p-5 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-xl shadow-inner">
                        {{ strtoupper(substr($santri->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $santri->name }}</h3>
                        <p class="text-gray-500 text-sm">Email: {{ $santri->email }}</p>
                        <p class="text-gray-500 text-sm">Telepon: {{ $santri->phone ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4 text-right">
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        Lihat Detail →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-yellow-50 border border-yellow-300 text-yellow-700 rounded-xl p-6 text-center shadow-sm">
                    <p class="font-medium text-lg mb-1">Belum ada santri yang terhubung</p>
                    <p class="text-sm text-gray-600">
                        Hubungi pengurus untuk menautkan akun santri ke akun wali Anda.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- FOOTER -->
    <div class="max-w-5xl mx-auto mt-10 text-center text-gray-500 text-sm">
        <p>© {{ date('Y') }} Pondok Mahasiswa Khoirul Huda 2 | Sistem Manajemen Santri</p>
    </div>
</div>
@endsection
