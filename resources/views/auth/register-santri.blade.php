<x-guest-layout>
    <h1 class="text-xl font-semibold mb-4">Registrasi Santri</h1>

    {{-- Notifikasi error global (opsional, untuk debug) --}}
    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
            Terjadi kesalahan. Periksa isian Anda.
        </div>
    @endif

    <form method="POST" action="{{ url('/register/santri') }}" class="space-y-4">
        @csrf

        {{-- Nama --}}
        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" name="name" :value="old('name')" required placeholder="Nama" class="block w-full mt-1"/>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required placeholder="Email" class="block w-full mt-1"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- (Opsional) Nama Lengkap --}}
        <div>
            <x-input-label for="nama_lengkap" :value="__('Nama Lengkap (opsional)')" />
            <x-text-input id="nama_lengkap" name="nama_lengkap" :value="old('nama_lengkap')" placeholder="Nama Lengkap" class="block w-full mt-1"/>
            <x-input-error :messages="$errors->get('nama_lengkap')" class="mt-2" />
        </div>

        {{-- (Opsional) No. HP, tambahkan hanya jika controller menyimpan phone --}}
        {{-- 
        <div>
            <x-input-label for="phone" :value="__('No. Handphone')" />
            <x-text-input id="phone" name="phone" :value="old('phone')" placeholder="08xxxxx / +62xxxxx" class="block w-full mt-1"/>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>
        --}}

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required placeholder="Password" class="block w-full mt-1"/>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Konfirmasi Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Konfirmasi Password" class="block w-full mt-1"/>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Daftar</x-primary-button>

        <p class="text-center text-sm text-slate-600 mt-2">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="underline">Masuk</a>
        </p>
    </form>
</x-guest-layout>
