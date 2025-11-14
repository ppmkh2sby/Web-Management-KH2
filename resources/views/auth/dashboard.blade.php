<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl tracking-tight">Dashboard</h2>
    </x-slot>

    @php
        $user = auth()->user();
        $roleStr = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        $firstCard = collect($cards ?? [])->firstWhere('url');
        $firstUrl  = is_array($firstCard) ? ($firstCard['url'] ?? null) : null;
    @endphp

    <div class="max-w-7xl mx-auto p-6 space-y-10">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-teal-600 to-sky-600 text-white">
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" viewBox="0 0 800 400">
                    <defs>
                        <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
                            <stop offset="0%" stop-color="#ffffff"/>
                            <stop offset="100%" stop-color="#a7f3d0"/>
                        </linearGradient>
                    </defs>
                    <g fill="url(#g)" opacity=".35">
                        <circle cx="120" cy="80" r="70"/><circle cx="340" cy="60" r="40"/>
                        <circle cx="520" cy="120" r="60"/><circle cx="720" cy="90" r="30"/>
                        <circle cx="240" cy="200" r="90"/><circle cx="600" cy="240" r="110"/>
                    </g>
                </svg>
            </div>
            <div class="relative px-8 py-12 lg:px-16 grid lg:grid-cols-2 gap-10">
                <div class="space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-white/20 grid place-items-center ring-2 ring-white/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
                            </svg>
                        </div>
                        <p class="text-emerald-100 text-sm">Selamat datang kembali, <span class="font-semibold">{{ $user->name }}</span></p>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Pondok Pesantren KH2</h1>
                    <p class="text-emerald-50 leading-relaxed max-w-2xl">
                        Rumah ilmu, adab, dan pengabdian. Di KH2, setiap santri dididik untuk berakhlak mulia,
                        tekun menuntut ilmu, dan berperan aktif membangun umat.
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        @if($firstUrl)
                            <a href="{{ $firstUrl }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/90 text-emerald-700 font-semibold rounded-xl shadow hover:bg-white transition">
                                Mulai Akses Sistem
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-5 py-2.5 border border-white/40 rounded-xl hover:bg-white/10 transition">
                            Kelola Profil
                        </a>
                        <span class="px-2.5 py-1 rounded-lg bg-white/10 border border-white/20 text-sm">Role: {{ $roleStr }}</span>
                        @if($user->email_verified_at)
                            <span class="px-2.5 py-1 rounded-lg bg-white/10 border border-white/20 text-sm">Email terverifikasi</span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg bg-white/10 border border-white/20 text-sm">Email belum terverifikasi</span>
                        @endif
                    </div>
                </div>
                <div class="lg:pl-8">
                    <div class="bg-white/10 rounded-2xl p-6 backdrop-blur border border-white/20 h-full">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-center">
                            <div class="p-4 rounded-xl bg-white/10">
                                <div class="text-2xl font-extrabold">Adab</div>
                                <div class="text-xs opacity-80">Landasan</div>
                            </div>
                            <div class="p-4 rounded-xl bg-white/10">
                                <div class="text-2xl font-extrabold">Ilmu</div>
                                <div class="text-xs opacity-80">Talaqqi</div>
                            </div>
                            <div class="p-4 rounded-xl bg-white/10">
                                <div class="text-2xl font-extrabold">Amal</div>
                                <div class="text-xs opacity-80">Pengabdian</div>
                            </div>
                            <div class="p-4 rounded-xl bg-white/10">
                                <div class="text-2xl font-extrabold">Khidmah</div>
                                <div class="text-xs opacity-80">Untuk Umat</div>
                            </div>
                        </div>
                        <p class="mt-5 text-sm text-emerald-50 leading-relaxed">
                            “Barangsiapa menempuh jalan untuk mencari ilmu, maka Allah memudahkan baginya jalan menuju surga.”
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <section class="grid md:grid-cols-3 gap-4">
            <a href="{{ route('profile.edit') }}" class="group p-5 rounded-2xl bg-white border hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-700 grid place-items-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold">Lengkapi Profil</div>
                        <div class="text-sm text-slate-500">Foto, biodata, kontak</div>
                    </div>
                </div>
            </a>
            <div class="p-5 rounded-2xl bg-white border">
                <div class="font-semibold">Pengumuman</div>
                <p class="text-sm text-slate-500 mt-1">Tidak ada pengumuman baru.</p>
            </div>
            <div class="p-5 rounded-2xl bg-white border">
                <div class="font-semibold">Agenda Hari Ini</div>
                <p class="text-sm text-slate-500 mt-1">Belum ada jadwal.</p>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Sistem Informasi</h3>
                <p class="text-sm text-slate-500">Akses menu sesuai peran Anda</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse(($cards ?? []) as $card)
                    @php
                        $disabled = empty($card['url'] ?? null);
                    @endphp
                    <div class="p-5 border rounded-2xl {{ $disabled ? 'bg-slate-50 opacity-70' : 'bg-white hover:bg-slate-50' }} transition">
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ $card['label'] ?? '-' }}</div>
                            @unless($disabled)
                                <a href="{{ $card['url'] }}" class="text-sm text-blue-600 underline">Buka</a>
                            @endunless
                        </div>
                        @if($disabled)
                            <div class="text-xs text-slate-500 mt-2">Segera hadir</div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full text-sm text-slate-500">Belum ada menu untuk peran Anda.</div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Foto Individu</h3>
                <p class="text-sm text-slate-500">Daftar foto bergulir otomatis.</p>
            </div>

            @php
                $baseDir = public_path('assets/images/individu');
                $individuImages = [];
                if (is_dir($baseDir)) {
                    foreach (\Illuminate\Support\Facades\File::files($baseDir) as $file) {
                        $fname = $file->getFilename();
                        if (preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $fname)) {
                            $name = pathinfo($fname, PATHINFO_FILENAME);
                            $name = ucwords(str_replace(['-', '_'], ' ', $name));
                            $individuImages[] = [
                                'src'  => asset('assets/images/individu/' . $fname),
                                'name' => $name,
                            ];
                        }
                    }
                }
            @endphp

            @if(!empty($individuImages))
                <style>
                    .marquee { overflow: hidden; }
                    .marquee__track {
                        display: flex;
                        gap: 2rem;
                        align-items: center;
                        animation: marquee var(--marquee-duration, 40s) linear infinite;
                    }
                    /* Jangan berhenti saat hover */
                    @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
                    @media (prefers-reduced-motion: reduce) { .marquee__track { animation: none; } }
                    .float-item { width: 12rem; text-align: center; padding-inline: .5rem; }
                    .float-photo { width: 10rem; height: 10rem; object-fit: contain; }
                </style>

                <div class="marquee">
                    <div class="marquee__track py-6" style="--marquee-duration: {{ max(30, (count($individuImages) * 3)) }}s;">
                        @foreach($individuImages as $img)
                            <div class="shrink-0 float-item">
                                <img src="{{ $img['src'] }}" alt="{{ $img['name'] }}" class="float-photo mx-auto select-none" style="background-color: transparent; filter: drop-shadow(0 8px 14px rgba(0,0,0,.18));">
                                <div class="mt-2 text-sm font-medium truncate" title="{{ $img['name'] }}">{{ $img['name'] }}</div>
                            </div>
                        @endforeach
                        @foreach($individuImages as $img)
                            <div class="shrink-0 float-item">
                                <img src="{{ $img['src'] }}" alt="{{ $img['name'] }}" class="float-photo mx-auto select-none" style="background-color: transparent; filter: drop-shadow(0 8px 14px rgba(0,0,0,.18));">
                                <div class="mt-2 text-sm font-medium truncate" title="{{ $img['name'] }}">{{ $img['name'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-6 rounded-2xl border bg-white text-center text-slate-500">
                    Tidak ada foto di folder assets/images/individu.
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
