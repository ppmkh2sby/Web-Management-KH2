<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js.navbar.js'])
    <title>Register</title>
</head>

<body class="h-screen w-screen flex justify-center items-center bg-gray-100 font-[Poppins] overflow-hidden">
    <div class="container flex w-5/6 h-[90vh] shadow-2xl rounded-3xl bg-white overflow-hidden">
        <!-- LEFT PAGE -->
        <div class="left_page w-1/2 relative overflow-hidden rounded-l-3xl"
            x-data="{ active: 0, images: [
                '{{ asset('photo/DSC09821.JPG') }}',
                '{{ asset('photo/DSC09905.JPG') }}',
                '{{ asset('photo/DSC09936.JPG') }}',
                '{{ asset('photo/HWP_6626.JPG') }}',
                '{{ asset('photo/HWP_6653.JPG') }}'
            ] }"
            x-init="setInterval(() => active = (active + 1) % images.length, 4000)">
            
            <template x-for="(img, index) in images" :key="index">
                <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="active === index ? 'opacity-100' : 'opacity-0'">
                    <img :src="img" alt="" class="object-cover w-full h-full" />
                </div>
            </template>

            <div class="absolute inset-0 bg-gradient-to-tr from-black/60 via-transparent to-indigo-700/50 mix-blend-multiply"></div>

            <div class="absolute bottom-6 left-6 text-white">
                <h1 class="text-3xl font-bold drop-shadow-lg tracking-wide">OUR MEMORIES</h1>
                <p class="text-sm text-gray-100 mt-1">Kebersamaan, perjuangan, dan tawa yang tak terlupakan.</p>
            </div>

            <div class="absolute bottom-6 right-6 flex space-x-2">
                <template x-for="(img, i) in images" :key="i">
                    <div class="w-3 h-3 rounded-full transition-all duration-500"
                        :class="active === i ? 'bg-white scale-110' : 'bg-gray-400 opacity-50'"></div>
                </template>
            </div>
        </div>

        <!-- RIGHT PAGE -->
        <div class="right_page w-1/2 flex justify-center items-center relative bg-gradient-to-br from-gray-50 to-gray-100 rounded-r-3xl">
            <div class="absolute w-72 h-72 bg-blue-400 opacity-20 blur-3xl rounded-full -top-10 -right-16"></div>

            <!-- 🔹 Card Container -->
            <div class="relative bg-white/95 backdrop-blur-md border border-gray-200 shadow-2xl shadow-indigo-100 rounded-2xl w-[85%] max-w-md px-10 py-8 flex flex-col justify-between h-[90%] z-10">
                
                <!-- 🧭 Bagian Atas -->
                <div>
                    <h1 class="font-bold text-gray-800 text-4xl text-center select-none tracking-wide mb-8">Register</h1>

                    @if($errors->any())
                        <div class="mb-6 text-sm bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded-lg shadow-sm">
                            <ul class="list-disc list-inside text-left">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- 📝 Form -->
                <div class="flex-grow overflow-y-auto px-1">
                    <form action="{{ route('register.submit') }}" method="POST" class="space-y-4" x-data="{ selectedRole: '' }">
                        @csrf

                        <input type="text" name="name" placeholder="Nama Lengkap" required class="border p-3 rounded w-full">
                        <input type="email" name="email" placeholder="Email" required class="border p-3 rounded w-full">
                        <input type="text" name="phone" placeholder="Nomor Telepon" required class="border p-3 rounded w-full">
                        <input type="password" name="password" placeholder="Password" required class="border p-3 rounded w-full">
                        <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required class="border p-3 rounded w-full">

                        <!-- 🔸 Role + Kode -->
                        <div class="grid grid-cols-2 gap-3 items-start">
                            <div>
                                <select name="id_role" required x-model="selectedRole" class="border p-3 rounded w-full bg-white text-gray-700">
                                    <option value="" disabled selected>Pilih Role</option>
                                    <option value="4">Santri</option>
                                    <option value="5">Wali Santri</option>
                                    <option value="6">Degur</option>
                                    <option value="7">Pengurus</option>
                                </select>
                            </div>
                            <div>
                                <div x-show="selectedRole == 5" x-transition>
                                    <input type="text" name="kode_anak" placeholder="Kode Anak" class="border p-3 rounded w-full" />
                                </div>
                                <div x-show="selectedRole == 6 || selectedRole == 7" x-transition>
                                    <input type="text" name="secret_code" placeholder="Kode Rahasia" class="border p-3 rounded w-full" />
                                </div>
                            </div>
                        </div>

                        <!-- 🔘 Tombol -->
                        <button type="submit"
                            class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-purple-500 hover:to-blue-500 text-white py-2.5 w-full rounded-lg text-[16px] font-medium shadow-md transition duration-300 ease-in-out mt-4">
                            Register
                        </button>
                    </form>
                </div>

                <!-- 📄 Footer -->
                <div class="pt-4">
                    <div class="text-center text-gray-600 text-sm mb-2">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-blue-500 hover:underline font-medium">Log In</a>
                    </div>

                    <p class="text-gray-400 text-xs text-center leading-relaxed px-4">
                        Dengan mendaftar, Anda menyetujui 
                        <a href="#" class="text-blue-400 hover:underline">Syarat</a> dan 
                        <a href="#" class="text-blue-400 hover:underline">Kebijakan Privasi</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
