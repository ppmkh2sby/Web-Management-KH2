<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard PPM KH2')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-[Poppins] text-gray-800">

    <!-- SIDEBAR -->
    <div class="flex min-h-screen">
        <aside class="w-64 bg-indigo-700 text-white flex flex-col shadow-xl">
            <div class="p-6 border-b border-indigo-500">
                <h1 class="text-2xl font-bold">PPM KH2</h1>
                <p class="text-sm text-indigo-200">Manajemen Santri</p>
            </div>

            <nav class="flex-1 p-4 space-y-2 text-sm">
                <a href="{{ route('wali.dashboard') }}" class="block px-4 py-2 rounded-md hover:bg-indigo-600 transition">
                    🏠 Dashboard
                </a>
                <a href="#" class="block px-4 py-2 rounded-md hover:bg-indigo-600 transition">
                    👦 Data Santri
                </a>
                <a href="#" class="block px-4 py-2 rounded-md hover:bg-indigo-600 transition">
                    📘 Laporan Presensi
                </a>
            </nav>

            <div class="p-4 border-t border-indigo-500 text-sm">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-2 rounded-lg transition">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>

</body>
</html>
