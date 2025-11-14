<x-guest-layout>
    <h1 class="text-xl font-semibold mb-4">Registrasi Wali Santri</h1>
    <form method="POST" action="{{ url('/register/wali') }}" class="space-y-4">
        @csrf
        <x-text-input name="name" :value="old('name')" required placeholder="Nama"/>
        <x-text-input type="email" name="email" :value="old('email')" required placeholder="Email"/>

        <x-text-input name="santri_code" :value="old('santri_code')" required placeholder="Kode Santri (dari anak)"/>
        @error('santri_code') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

        <x-text-input type="password" name="password" required placeholder="Password"/>
        <x-text-input type="password" name="password_confirmation" required placeholder="Konfirmasi Password"/>
        <x-primary-button class="w-full">Daftar</x-primary-button>
    </form>
</x-guest-layout>
