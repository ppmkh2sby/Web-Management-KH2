<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Masuk ke sistem manajemen KH2 Boarding School.">
    <title>Login | KH2 Boarding School</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #122228;
            --ink-soft: #5b7178;
            --accent: #0f9a8d;
            --accent-strong: #0c7a70;
            --line: rgba(18, 34, 40, 0.13);
            --bg: #eef3f3;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: "Plus Jakarta Sans", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 0% 0%, rgba(15, 154, 141, 0.2), transparent 35%),
                radial-gradient(circle at 100% 100%, rgba(255, 196, 95, 0.14), transparent 32%),
                var(--bg);
        }

        .display-font {
            font-family: "Fraunces", serif;
        }

        .auth-stage {
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.74);
            backdrop-filter: blur(10px);
            box-shadow: 0 34px 70px -45px rgba(8, 21, 25, 0.7);
        }

        .brand-panel {
            background:
                linear-gradient(165deg, #114e5a 0%, #0d343d 55%, #0d2a33 100%);
        }

        .auth-input {
            width: 100%;
            border-radius: 0.85rem;
            border: 1px solid var(--line);
            background: #fff;
            padding: 0.72rem 0.95rem;
            font-size: 0.95rem;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .auth-input:focus {
            border-color: rgba(18, 150, 138, 0.8);
            box-shadow: 0 0 0 3px rgba(18, 150, 138, 0.17);
            outline: none;
            transform: translateY(-1px);
        }

        .chip {
            border: 1px solid rgba(255, 255, 255, 0.26);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(2px);
        }

        a[data-direct-nav] {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .fade-in {
            animation: fade-in 0.7s ease-out both;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="h-[100dvh] overflow-hidden antialiased">
    <main class="relative mx-auto flex h-[100dvh] w-full max-w-6xl items-center px-4 py-3 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute -left-14 top-14 h-36 w-36 rounded-full bg-emerald-300/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-14 bottom-8 h-40 w-40 rounded-full bg-amber-200/40 blur-3xl"></div>

        <div class="auth-stage fade-in relative z-10 grid h-[min(740px,94dvh)] w-full overflow-hidden rounded-[28px] lg:grid-cols-[1.05fr_0.95fr]">
            <section class="brand-panel relative hidden h-full flex-col justify-between p-10 text-white lg:flex">
                <div>
                    <a data-direct-nav href="{{ route('landing') }}" class="inline-flex items-center text-sm font-semibold text-white/80 transition hover:text-white">
                        <span aria-hidden="true">&larr;</span>
                        <span class="ml-2">Kembali ke landing page</span>
                    </a>

                    <p class="mt-10 text-xs font-semibold uppercase tracking-[0.28em] text-emerald-200">PPM Khoirul Huda 2</p>
                    <h1 class="display-font mt-3 text-4xl leading-tight">
                            Sistem Manajemen
                        <br>
                        PPM Khoirul Huda 2
                    </h1>
                    <p class="mt-5 max-w-md text-sm leading-relaxed text-white/80">
                            Sistem manajemen terintegrasi untuk mendukung operasional  <br> dan pengelolaan data di PPM Khoirul Huda 2. 
                    </p>
                </div>
            </section>

            <section class="flex h-full items-center bg-white/86 px-5 py-6 sm:px-8 lg:px-10">
                <div class="mx-auto w-full max-w-md">
                    <a data-direct-nav href="{{ route('landing') }}" class="inline-flex items-center text-xs font-semibold uppercase tracking-[0.18em] text-[var(--ink-soft)] transition hover:text-[var(--accent)] lg:hidden">
                        <span aria-hidden="true">&larr;</span>
                        <span class="ml-2">Kembali</span>
                    </a>

                    <div class="mt-4 lg:mt-0">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-[var(--accent)]">Login Sistem</p>
                        <h2 class="display-font mt-2 text-3xl font-semibold text-[var(--ink)]">Selamat Datang</h2>
                        <p class="mt-1.5 text-sm text-[var(--ink-soft)]">Masukkan nomor induk santri dan password untuk melanjutkan.</p>
                    </div>

                    @if (session('status'))
                        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label for="login_code" class="mb-2 block text-sm font-semibold text-[var(--ink)]">Nomor Induk</label>
                            <input id="login_code" type="text" name="login_code" value="{{ old('login_code') }}" required autofocus autocomplete="username" class="auth-input" placeholder="Contoh: 0229210223">
                            @if ($errors->get('login_code'))
                                <ul class="mt-2 space-y-1 text-sm text-red-600">
                                    @foreach ((array) $errors->get('login_code') as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-[var(--ink)]">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password" class="auth-input" placeholder="Masukkan password">
                            @if ($errors->get('password'))
                                <ul class="mt-2 space-y-1 text-sm text-red-600">
                                    @foreach ((array) $errors->get('password') as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center text-sm text-[var(--ink-soft)]">
                                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                                <span class="ml-2">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[var(--ink-soft)] transition hover:text-[var(--accent)]">
                                    Lupa password?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[var(--ink)] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#0d181c] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:ring-offset-2">
                            Log In
                        </button>
                    </form>

                    <p class="mt-5 text-center text-xs text-[var(--ink-soft)]">
                        WebDev KH2 &copy; {{ date('Y') }}
                    </p>
                </div>
            </section>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let touchNavigationTime = 0;

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
        });
    </script>
</body>
</html>
