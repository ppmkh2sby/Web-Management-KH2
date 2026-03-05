<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem manajemen terpadu KH2 Boarding School untuk santri, wali, pengurus, dan dewan guru.">
    <title>KH2 Boarding School</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #132328;
            --muted: #4f666c;
            --accent: #0f9f8f;
            --accent-strong: #0b7f73;
            --surface: #f4f7f6;
            --card: #ffffff;
            --ring: rgba(19, 35, 40, 0.1);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 20%, rgba(15, 159, 143, 0.12), transparent 26%),
                radial-gradient(circle at 90% 0%, rgba(255, 188, 93, 0.12), transparent 25%),
                var(--surface);
            overflow-x: hidden;
        }

        .display-font {
            font-family: 'Sora', sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 45px -30px rgba(20, 38, 44, 0.45);
        }

        .hero-visual::after {
            content: "";
            position: absolute;
            inset: 1rem;
            border: 1px solid var(--ring);
            border-radius: 1.25rem;
            pointer-events: none;
        }

        .float-slow {
            animation: float-slow 5s ease-in-out infinite;
        }

        .float-delay {
            animation: float-slow 6s ease-in-out 1.2s infinite;
        }

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: transform 0.8s ease, opacity 0.8s ease;
        }

        .reveal.show {
            opacity: 1;
            transform: translateY(0);
        }

        .mobile-menu {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.35s ease, opacity 0.25s ease;
        }

        .mobile-menu[data-open="true"] {
            max-height: 280px;
            opacity: 1;
        }

        a[data-direct-nav] {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        @keyframes float-slow {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen antialiased">
    <header class="fixed inset-x-0 top-0 z-30 border-b border-black/5 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="#home" class="display-font text-xl font-extrabold tracking-tight text-[var(--ink)]">
                PPM<span class="text-[var(--accent)]"> Khoirul Huda 2</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-semibold text-[var(--muted)] md:flex">
                <a href="#home" class="transition hover:text-[var(--accent)]">Home</a>
                <a href="#about" class="transition hover:text-[var(--accent)]">Tentang</a>
                <a href="#features" class="transition hover:text-[var(--accent)]">Fitur</a>
                <a href="#gallery" class="transition hover:text-[var(--accent)]">Galeri</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @if(Route::has('login'))
                    <a data-direct-nav href="{{ route('login') }}" class="rounded-xl bg-[var(--accent)] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[var(--accent-strong)]">
                        Login
                    </a>
                @endif
            </div>

            <button type="button" data-menu-button aria-label="Toggle menu" aria-expanded="false" class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white p-2 text-[var(--ink)] md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 menu-open-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>

        <div data-mobile-menu data-open="false" class="mobile-menu border-t border-black/5 bg-white md:hidden">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-4 text-sm font-semibold text-[var(--muted)] sm:px-8">
                <a data-close-menu href="#home" class="rounded-lg px-3 py-2 transition hover:bg-[var(--surface)] hover:text-[var(--accent)]">Home</a>
                <a data-close-menu href="#about" class="rounded-lg px-3 py-2 transition hover:bg-[var(--surface)] hover:text-[var(--accent)]">Tentang</a>
                <a data-close-menu href="#features" class="rounded-lg px-3 py-2 transition hover:bg-[var(--surface)] hover:text-[var(--accent)]">Fitur</a>
                <a data-close-menu href="#gallery" class="rounded-lg px-3 py-2 transition hover:bg-[var(--surface)] hover:text-[var(--accent)]">Galeri</a>
                @if(Route::has('login'))
                    <a data-direct-nav href="{{ route('login') }}" class="mt-2 rounded-lg bg-[var(--accent)] px-3 py-2 text-center font-bold text-white transition hover:bg-[var(--accent-strong)]">Login</a>
                @endif
            </div>
        </div>
    </header>

    <main class="pt-20" id="home">
        <section class="mx-auto grid w-full max-w-7xl gap-12 px-5 py-14 sm:px-8 md:grid-cols-2 md:items-center md:py-20">
            <div class="reveal">
                <span class="inline-flex items-center rounded-full border border-[var(--ring)] bg-white px-4 py-1.5 text-xs font-bold uppercase tracking-[0.25em] text-[var(--accent)]">
                    Platform Digitalisasi KH2
                </span>
                <h1 class="display-font mt-6 text-4xl font-extrabold leading-tight text-[var(--ink)] sm:text-5xl lg:text-6xl">
                    Pusat Manajemen Data PPM Khoirul Huda 2
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-[var(--muted)] sm:text-lg">
                    Menghadirkan sistem manajemen terpadu untuk memudahkan civitas KH2 dalam mengelola data dan aktivitas pesantren secara efisien.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="#features" class="rounded-xl bg-[var(--ink)] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#0e191d]">
                        Lihat Fitur
                    </a>
                    @if(Route::has('login'))
                        <a data-direct-nav href="{{ route('login') }}" class="rounded-xl border border-[var(--ring)] bg-white px-6 py-3 text-sm font-bold text-[var(--ink)] transition hover:border-[var(--accent)] hover:text-[var(--accent)]">
                            Masuk Dashboard
                        </a>
                    @endif
                </div>

                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    <div class="glass-card reveal rounded-2xl px-4 py-4">
                        <p class="display-font text-2xl font-extrabold text-[var(--accent)]">43</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">Santri aktif</p>
                    </div>
                    <div class="glass-card reveal rounded-2xl px-4 py-4">
                        <p class="display-font text-2xl font-extrabold text-[var(--accent)]">4 Role</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">Akses terpisah</p>
                    </div>
                    <div class="glass-card reveal rounded-2xl px-4 py-4">
                        <p class="display-font text-2xl font-extrabold text-[var(--accent)]">24/7</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">Monitoring data</p>
                    </div>
                </div>
            </div>

            <div class="absolute left-0 bottom-[30px] h-full w-full md:relative">
                <div class="hero-visual relative overflow-hidden rounded-3xl border border-[var(--ring)] bg-white p-3 shadow-xl shadow-black/10">
                    <img src="{{ asset('assets/images/foto_bersama.JPG') }}" alt="Foto kegiatan KH2" class="h-[430px] w-full rounded-2xl object-cover sm:h-[500px]">
                    <div class="pointer-events-none absolute inset-3 rounded-2xl bg-gradient-to-t from-[#132328]/40 via-transparent to-transparent"></div>
                </div>

                <div class="float-slow glass-card absolute -left-2 top-3 w-40 rounded-2xl p-4 sm:-left-8 sm:w-44">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--accent)]">Presensi</p>
                    <p class="mt-2 text-sm font-bold text-[var(--ink)]">Rekap harian realtime</p>
                </div>

                <div class="float-delay glass-card absolute bottom-[50px] right-0 w-44 rounded-2xl p-4 sm:-right-6 sm:w-52">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--accent)]">Progres</p>
                    <p class="mt-2 text-sm font-bold text-[var(--ink)]">Pantau perkembangan setiap santri</p>
                </div>
            </div>
        </section>

        <section id="about" class="py-10 sm:py-14">
            <div class="mx-auto grid w-full max-w-7xl gap-6 px-5 sm:px-8 md:grid-cols-12">
                <div class="reveal rounded-3xl border border-[var(--ring)] bg-white p-7 shadow-lg shadow-black/5 md:col-span-7">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[var(--accent)]">Tentang KH2</p>
                    <h2 class="display-font mt-4 text-3xl font-extrabold text-[var(--ink)] sm:text-4xl">
                        Satu Platform untuk Operasional Pesantren yang Lebih Terkelola
                    </h2>
                    <p class="mt-5 text-[var(--muted)]">
                        KH2 mengintegrasikan administrasi, pembinaan, dan pelaporan dalam satu sistem terpadu untuk mendukung pengambilan keputusan, pemantauan wali, serta evaluasi santri secara terstruktur.
                    </p>
                </div>
                <div class="reveal grid gap-6 md:col-span-5">
                    <div class="rounded-3xl border border-[var(--ring)] bg-[#eaf7f5] p-6">
                        <p class="display-font text-3xl font-extrabold text-[var(--accent-strong)]">1 Dashboard</p>
                        <p class="mt-2 text-sm text-[var(--muted)]">Integrasi data dan aktivitas seluruh peran dalam satu sistem terpadu.</p>
                    </div>
                    <div class="rounded-3xl border border-[var(--ring)] bg-white p-6">
                        <p class="display-font text-3xl font-extrabold text-[var(--accent-strong)]">Role-based</p>
                        <p class="mt-2 text-sm text-[var(--muted)]">Tampilan disesuaikan dengan kebutuhan pengguna.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-12 sm:py-16">
            <div class="mx-auto w-full max-w-7xl px-5 sm:px-8">
                <div class="reveal mb-8 max-w-2xl">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[var(--accent)]">Fitur Utama</p>
                    <h2 class="display-font mt-3 text-3xl font-extrabold text-[var(--ink)] sm:text-4xl">
                        Dirancang untuk Efisiensi, Kejelasan, dan Kecepatan Kerja
                    </h2>
                </div>
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <article class="reveal rounded-2xl border border-[var(--ring)] bg-white p-6 shadow-lg shadow-black/5 transition hover:-translate-y-1">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf7f5] text-lg font-extrabold text-[var(--accent)]">1</div>
                        <h3 class="text-lg font-extrabold text-[var(--ink)]">Monitoring Presensi</h3>
                        <p class="mt-3 text-sm text-[var(--muted)]">Lihat status hadir, izin, dan alfa setiap sesi dengan rekap yang mudah dibaca.</p>
                    </article>
                    <article class="reveal rounded-2xl border border-[var(--ring)] bg-white p-6 shadow-lg shadow-black/5 transition hover:-translate-y-1">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff4e7] text-lg font-extrabold text-[#c27a2f]">2</div>
                        <h3 class="text-lg font-extrabold text-[var(--ink)]">Progress Keilmuan</h3>
                        <p class="mt-3 text-sm text-[var(--muted)]">Catat dan evaluasi perkembangan hafalan, materi, serta capaian akademik santri.</p>
                    </article>
                    <article class="reveal rounded-2xl border border-[var(--ring)] bg-white p-6 shadow-lg shadow-black/5 transition hover:-translate-y-1">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#ecf2ff] text-lg font-extrabold text-[#2a63c9]">3</div>
                        <h3 class="text-lg font-extrabold text-[var(--ink)]">Akses Wali Terarah</h3>
                        <p class="mt-3 text-sm text-[var(--muted)]">Wali santri mendapat informasi aktivitas dan progres tanpa harus menunggu laporan manual.</p>
                    </article>
                    <article class="reveal rounded-2xl border border-[var(--ring)] bg-white p-6 shadow-lg shadow-black/5 transition hover:-translate-y-1">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#e9f9ef] text-lg font-extrabold text-[#2d9a5d]">4</div>
                        <h3 class="text-lg font-extrabold text-[var(--ink)]">Riwayat Terdokumentasi</h3>
                        <p class="mt-3 text-sm text-[var(--muted)]">Semua perubahan data tersimpan rapi agar audit dan evaluasi lebih akurat.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="gallery" class="py-12 sm:py-16">
            <div class="mx-auto w-full max-w-7xl px-5 sm:px-8">
                <div class="reveal mb-8 text-center">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[var(--accent)]">Galeri Kegiatan</p>
                    <h2 class="display-font mt-3 text-3xl font-extrabold text-[var(--ink)] sm:text-4xl">Potret Aktivitas Santri KH2</h2>
                </div>

                @php
                    $galleryBasePath = public_path('assets/images/kegiatan');
                    $descriptions = [
                        'belajar' => 'Suasana belajar dengan pembinaan yang terstruktur.',
                        'keakraban' => 'Momen kebersamaan untuk menjaga keakraban antar santri.',
                        'orumawa' => 'Kegiatan olahraga rutin untuk menjaga kebugaran dan kekompakan.',
                    ];

                    $titleOverrides = [
                        'orumawa' => 'ORUMAWA',
                    ];

                    $gallery = [];
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                    if (is_dir($galleryBasePath)) {
                        $activityFolders = collect(\Illuminate\Support\Facades\File::directories($galleryBasePath))
                            ->sort()
                            ->values();

                        foreach ($activityFolders as $folderPath) {
                            $folderName = basename($folderPath);

                            $images = collect(\Illuminate\Support\Facades\File::files($folderPath))
                                ->filter(function ($file) use ($allowedExtensions) {
                                    return in_array(strtolower($file->getExtension()), $allowedExtensions, true);
                                })
                                ->sortBy(function ($file) {
                                    return $file->getFilename();
                                })
                                ->map(function ($file) use ($folderName) {
                                    return asset('assets/images/kegiatan/' . $folderName . '/' . $file->getFilename());
                                })
                                ->values()
                                ->all();

                            if (empty($images)) {
                                continue;
                            }

                            $readableName = \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', $folderName));
                            $gallery[] = [
                                'title' => $titleOverrides[$folderName] ?? $readableName,
                                'desc' => $descriptions[$folderName] ?? ('Dokumentasi kegiatan ' . strtolower($readableName) . '.'),
                                'images' => $images,
                            ];
                        }
                    }
                @endphp

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($gallery as $item)
                        <article class="reveal overflow-hidden rounded-2xl border border-[var(--ring)] bg-white shadow-lg shadow-black/5 transition hover:-translate-y-1">
                            <img
                                src="{{ $item['images'][0] }}"
                                alt="{{ $item['title'] }}"
                                class="h-52 w-full object-cover opacity-100 transition-opacity duration-700 ease-in-out"
                                data-gallery-rotator
                                data-images='@json($item['images'])'
                            >
                            <div class="p-5">
                                <h3 class="text-lg font-extrabold text-[var(--ink)]">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm text-[var(--muted)]">{{ $item['desc'] }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-[var(--ring)] bg-white p-8 text-center text-sm text-[var(--muted)]">
                            Belum ada foto kegiatan yang dapat ditampilkan.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="pb-14 pt-6 sm:pb-20">
            <div class="mx-auto w-full max-w-7xl px-5 sm:px-8">
                <div class="reveal rounded-3xl bg-[var(--ink)] px-6 py-10 text-center sm:px-10">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[#75d6cb]">Mulai Sekarang</p>
                    <h2 class="display-font mt-3 text-3xl font-extrabold text-white sm:text-4xl">
                        Optimalkan Manajemen Pesantren dari Satu Tempat
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-sm text-slate-200 sm:text-base">
                        Platform KH2 membantu civitas KH2 bekerja dengan alur yang lebih cepat dan data yang lebih akurat.
                    </p>
                    @if(Route::has('login'))
                        <a data-direct-nav href="{{ route('login') }}" class="mt-7 inline-flex rounded-xl bg-white px-6 py-3 text-sm font-extrabold text-[var(--ink)] transition hover:bg-slate-100">
                            Login ke Sistem
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-black/5 bg-white py-7">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-3 px-5 text-sm text-[var(--muted)] sm:px-8 md:flex-row md:items-center md:justify-between">
            <p>(c) {{ date('Y') }} WebDev KH2. All rights reserved.</p>
            <div class="flex items-center gap-5">
                <a href="#home" class="transition hover:text-[var(--accent)]">Home</a>
                <a href="#about" class="transition hover:text-[var(--accent)]">Tentang</a>
                <a href="#gallery" class="transition hover:text-[var(--accent)]">Galeri</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.querySelector('[data-menu-button]');
            const mobileMenu = document.querySelector('[data-mobile-menu]');
            let touchNavigationTime = 0;

            if (menuButton && mobileMenu) {
                menuButton.addEventListener('click', function () {
                    const isOpen = mobileMenu.dataset.open === 'true';
                    const nextState = (!isOpen).toString();
                    mobileMenu.dataset.open = nextState;
                    menuButton.setAttribute('aria-expanded', nextState);
                });

                document.querySelectorAll('[data-close-menu]').forEach(function (item) {
                    item.addEventListener('click', function () {
                        mobileMenu.dataset.open = 'false';
                        menuButton.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            document.querySelectorAll('a[data-direct-nav]').forEach(function (anchor) {
                anchor.addEventListener('pointerup', function (event) {
                    if (event.pointerType !== 'touch') {
                        return;
                    }

                    touchNavigationTime = Date.now();
                    event.preventDefault();
                    window.location.assign(anchor.href);
                }, { passive: false });

                anchor.addEventListener('click', function (event) {
                    if (event.defaultPrevented) {
                        return;
                    }

                    if (Date.now() - touchNavigationTime < 700) {
                        event.preventDefault();
                        return;
                    }

                    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                        return;
                    }

                    event.preventDefault();
                    window.location.assign(anchor.href);
                }, true);
            });

            const items = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function (entries, obs) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('show');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.14, rootMargin: '0px 0px -8% 0px' });

                items.forEach(function (item) {
                    observer.observe(item);
                });
            } else {
                items.forEach(function (item) {
                    item.classList.add('show');
                });
            }

            document.querySelectorAll('[data-gallery-rotator]').forEach(function (img) {
                let images = [];

                try {
                    images = JSON.parse(img.dataset.images || '[]');
                } catch (error) {
                    images = [];
                }

                if (!Array.isArray(images) || images.length < 2) {
                    return;
                }

                images.slice(1).forEach(function (src) {
                    const preloaded = new Image();
                    preloaded.src = src;
                });

                let currentIndex = 0;

                setInterval(function () {
                    currentIndex = (currentIndex + 1) % images.length;
                    img.classList.add('opacity-0');

                    setTimeout(function () {
                        img.src = images[currentIndex];
                        requestAnimationFrame(function () {
                            img.classList.remove('opacity-0');
                        });
                    }, 300);
                }, 2000);
            });
        });
    </script>
</body>
</html>
