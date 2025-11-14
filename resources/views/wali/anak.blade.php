<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Anak Saya</h2></x-slot>
    <div class="p-6 space-y-2">
        @forelse($santriList as $s)
            <div class="p-3 rounded border">
                <div>Nama Santri: <strong>{{ $s->nama_lengkap ?? $s->user->name }}</strong></div>
                <div>Kode: <code>{{ $s->code }}</code></div>
            </div>
        @empty
            <p>Belum ada santri terhubung.</p>
        @endforelse
    </div>
</x-app-layout>
