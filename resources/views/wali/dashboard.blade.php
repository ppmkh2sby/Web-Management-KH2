<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php($user = auth()->user())

            @if (session('status'))
                <div class="p-3 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <p>{{ __("You're logged in!") }}</p>
                    <p>Role:
                        <span class="font-semibold">{{ $user->role->value }}</span>
                    </p>

                    {{-- Info khusus per-role --}}
                    @if ($user->role->value === 'santri')
                        @php($kode = optional($user->santri)->code)
                        <div class="p-4 border rounded bg-slate-50">
                            <p class="mb-2">Kode Anak Anda:</p>

                            <div class="flex items-center gap-2">
                                <code id="kode-anak" class="px-2 py-1 bg-white border rounded">
                                    {{ $kode }}
                                </code>
                                <button type="button" id="btn-copy"
                                        class="text-sm border rounded px-2 py-1 hover:bg-slate-100">
                                    Salin
                                </button>
                            </div>

                            <p class="text-xs text-slate-500 mt-1">
                                Berikan kode ini kepada Wali agar dapat menghubungkan akunnya saat mendaftar.
                            </p>
                        </div>
                    @elseif ($user->role->value === 'wali')
                        <a class="underline" href="{{ route('wali.anak') }}">
                            Lihat data anak saya
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->role->value === 'admin')
        <a href="{{ route('admin.users.index') }}" class="underline">Kelola Users</a>
    @endif

    {{-- Copy to clipboard untuk kode anak --}}
    <script>
        const copyBtn = document.getElementById('btn-copy');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const text = document.getElementById('kode-anak').innerText.trim();
                navigator.clipboard.writeText(text);
                copyBtn.textContent = 'Tersalin ✓';
                setTimeout(() => copyBtn.textContent = 'Salin', 1500);
            });
        }
    </script>
</x-app-layout>
