<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>KH2 Boarding School</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-[#f5f4ef] text-[#1b1b18] antialiased">
        <header class="fixed inset-x-0 top-0 z-20 bg-[#f5f4ef]/95 backdrop-blur border-b border-black/5">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="text-lg font-semibold tracking-wide">
                    KH2<span class="text-emerald-600">Boarding</span>
                </div>
                <nav class="hidden gap-6 text-sm font-medium md:flex">
                    <a href="#home" class="hover:text-emerald-700 transition">Home</a>
                    <a href="#about" class="hover:text-emerald-700 transition">About Us</a>
                    <a href="#gallery" class="hover:text-emerald-700 transition">Gallery</a>
                </nav>
                <div class="flex items-center gap-3">
                    @if(Route::has('login'))
                        <a href="{{ route('login') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                            Login
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="pt-24">
            <section id="home" class="mx-auto flex max-w-6xl flex-col gap-10 px-6 py-16 md:flex-row md:items-center">
                <div class="md:w-1/2">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-600">
                        Pesantren Modern KH2
                    </p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight text-[#151513] md:text-5xl">
                        Menemani Santri Menjadi Generasi Berakhlaq dan Berilmu
                    </h1>
                    <p class="mt-6 text-base text-[#4b4a44]">
                        Platform manajemen terpadu untuk santri, wali, pengurus, dan dewan guru.
                        Pantau kegiatan harian, perkembangan akademik, dan informasi pesantren hanya dalam satu dashboard.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="#about" class="rounded-lg bg-[#151513] px-5 py-3 text-sm font-semibold text-white hover:bg-[#1f1f1c] transition">
                            Pelajari Lebih Lanjut
                        </a>
                        @if(Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-lg border border-[#151513] px-5 py-3 text-sm font-semibold text-[#151513] hover:bg-[#151513] hover:text-white transition">
                                Login
                            </a>
                        @endif
                    </div>
                </div>
                <div class="md:w-1/2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-white p-6 shadow">
                            <p class="text-3xl font-bold text-emerald-600">500+</p>
                            <p class="mt-2 text-sm text-[#4b4a44]">Santri aktif mengikuti program</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-600/10 p-6 shadow">
                            <p class="text-3xl font-bold text-emerald-700">24/7</p>
                            <p class="mt-2 text-sm text-[#4b4a44]">Monitoring perkembangan santri</p>
                        </div>
                        <div class="col-span-2 rounded-2xl bg-white p-6 shadow">
                            <h3 class="text-lg font-semibold">Integrasi Orang Tua</h3>
                            <p class="mt-2 text-sm text-[#4b4a44]">
                                Orang tua dapat memantau absensi, progres hafalan, dan jadwal kegiatan secara waktu nyata.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="about" class="bg-white py-16">
                <div class="mx-auto max-w-5xl px-6">
                    <div class="flex flex-col gap-8 md:flex-row md:items-center">
                        <div class="md:w-1/2">
                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-600">About Us</p>
                            <h2 class="mt-3 text-3xl font-semibold text-[#151513]">Misi Digitalisasi Pesantren KH2</h2>
                            <p class="mt-5 text-[#4b4a44]">
                                Aplikasi KH2 membantu pengelolaan data santri, wali, pengurus, serta dewan guru dalam satu sistem.
                                Mulai dari administrasi pendaftaran, pemantauan kegiatan, hingga laporan perkembangan harian.
                            </p>
                        </div>
                        <div class="md:w-1/2 rounded-2xl border border-black/5 bg-[#f5f4ef] p-6">
                            <ul class="space-y-4 text-[#34332d]">
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-600"></span>
                                    Manajemen data santri dan wali yang rapi berdasarkan nomor induk.
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-600"></span>
                                    Dashboard khusus untuk masing-masing role agar fokus pada kebutuhan mereka.
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-600"></span>
                                    Informasi kegiatan, galeri pesantren, dan pengumuman terpusat.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section id="gallery" class="py-16">
                <div class="mx-auto max-w-6xl px-6">
                    <div class="mb-10 text-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-600">Gallery</p>
                        <h2 class="mt-3 text-3xl font-semibold text-[#151513]">Kegiatan Terbaru Santri KH2</h2>
                        <p class="mt-3 text-[#4b4a44]">Cuplikan aktivitas santri dalam belajar, beribadah, dan berkreasi.</p>
                    </div>
                    @php
                        $gallery = [
                            ['title' => 'Halaqah Pagi', 'desc' => 'Pembinaan tahfidz Al-Qur\'an setiap pagi.'],
                            ['title' => 'Kegiatan Pramuka', 'desc' => 'Melatih kemandirian dan kepemimpinan.'],
                            ['title' => 'Kuliah Umum', 'desc' => 'Sesi bersama pemateri tamu dan alumni.'],
                            ['title' => 'Pelatihan IT', 'desc' => 'Mengenal teknologi sebagai bekal masa depan.'],
                            ['title' => 'Malam Bina Iman', 'desc' => 'Qiyamul lail berjamaah setiap pekan.'],
                            ['title' => 'Olahraga Sore', 'desc' => 'Sesi kebugaran untuk menjaga kesehatan santri.'],
                        ];
                    @endphp
                    <div class="grid gap-6 md:grid-cols-3">
                        @foreach($gallery as $item)
                            <div class="rounded-2xl bg-white shadow hover:-translate-y-1 transition transform p-5">
                                <div class="h-40 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-200"></div>
                                <h3 class="mt-4 text-lg font-semibold text-[#151513]">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm text-[#4b4a44]">{{ $item['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-black/5 bg-white py-6">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-2 px-6 text-sm text-[#4b4a44] md:flex-row md:justify-between">
                <p>© {{ date('Y') }} KH2 Boarding School. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#home" class="hover:text-emerald-700 transition">Home</a>
                    <a href="#about" class="hover:text-emerald-700 transition">About</a>
                    <a href="#gallery" class="hover:text-emerald-700 transition">Gallery</a>
                </div>
            </div>
        </footer>
    </body>
</html>
