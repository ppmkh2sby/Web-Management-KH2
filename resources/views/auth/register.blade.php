<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <div class="text-center">
            <h1 class="text-xl font-semibold">Buat Akun</h1>
            <p class="text-sm text-slate-500">Isi data di bawah sesuai peran Anda</p>
        </div>

        {{-- Nama --}}
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Phone --}}
        <div>
            <x-input-label for="phone" :value="__('No. Handphone')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                          :value="old('phone')" required placeholder="08xxxx / +62xxx" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        {{-- Role --}}
        <div>
            <x-input-label for="role" :value="__('Role')" />
            <select id="role" name="role" class="mt-1 block w-full border-slate-300 rounded-md" required>
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- Pilih Role --</option>
                <option value="santri"   {{ old('role')==='santri' ? 'selected' : '' }}>Santri</option>
                <option value="wali"     {{ old('role')==='wali' ? 'selected' : '' }}>Wali Santri</option>
                <option value="pengurus" {{ old('role')==='pengurus' ? 'selected' : '' }}>Pengurus</option>
                <option value="degur"    {{ old('role')==='degur' ? 'selected' : '' }}>Dewan Guru</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        {{-- Kode Anak (khusus Wali) --}}
        <div id="field-santri-code" class="hidden">
            <x-input-label for="santri_code" :value="__('Kode Anak (dari Santri)')" />
            <x-text-input id="santri_code" name="santri_code" type="text" class="mt-1 block w-full"
                          :value="old('santri_code')" placeholder="Misal: S-AB12CD34" />
            <x-input-error :messages="$errors->get('santri_code')" class="mt-2" />
        </div>

        {{-- Kode Rahasia (khusus Pengurus/Degur) --}}
        <div id="field-verification-code" class="hidden">
            <x-input-label for="verification_code" :value="__('Kode Rahasia (dari Admin)')" />
            <x-text-input id="verification_code" name="verification_code" type="text" class="mt-1 block w-full"
                          :value="old('verification_code')" placeholder="Masukkan kode rahasia" />
            <x-input-error :messages="$errors->get('verification_code')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Konfirmasi --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                          class="mt-1 block w-full" required autocomplete="new-password" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Daftar') }}
        </x-primary-button>

        <p class="text-center text-sm text-slate-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="underline">Masuk</a>
        </p>
    </form>

    {{-- Script show/hide field berdasar role --}}
    <script>
        const roleSel = document.getElementById('role');
        const fSantri  = document.getElementById('field-santri-code');
        const fVerif   = document.getElementById('field-verification-code');

        function applyRoleVisibility() {
            const r = roleSel.value;
            fSantri.classList.toggle('hidden', !(r === 'wali'));
            fVerif.classList.toggle('hidden', !(r === 'pengurus' || r === 'degur'));
        }
        roleSel.addEventListener('change', applyRoleVisibility);
        // panggil saat load agar old() ikut terbaca
        window.addEventListener('DOMContentLoaded', applyRoleVisibility);
    </script>
</x-guest-layout>
