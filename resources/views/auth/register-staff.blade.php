<x-guest-layout>
    <h1 class="text-xl font-semibold mb-4">Registrasi Pengurus / Dewan Guru</h1>
    <form method="POST" action="{{ url('/register/staff') }}" class="space-y-4">
        @csrf
        <x-text-input name="name" :value="old('name')" required placeholder="Nama"/>
        <x-text-input type="email" name="email" :value="old('email')" required placeholder="Email"/>

        <select name="role" class="border rounded w-full p-2" required>
            <option value="">Pilih Role</option>
            <option value="pengurus" @selected(old('role')==='pengurus')>Pengurus</option>
            <option value="degur" @selected(old('role')==='degur')>Dewan Guru</option>
        </select>

        <x-text-input name="verification_code" :value="old('verification_code')" required placeholder="Kode Verifikasi dari Admin"/>
        @error('verification_code') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

        <x-text-input type="password" name="password" required placeholder="Password"/>
        <x-text-input type="password" name="password_confirmation" required placeholder="Konfirmasi Password"/>
        <x-primary-button class="w-full">Daftar</x-primary-button>
    </form>
</x-guest-layout>
