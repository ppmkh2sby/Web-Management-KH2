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

<body class="h-screen w-screen flex justify-center items-center bg-gray-100 font-[Poppins]">
    <div class="container flex w-5/6 h-4/5 shadow-2xl rounded-3xl bg-white overflow-hidden">
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

            <div class="relative bg-white/90 backdrop-blur-md border border-gray-200 shadow-2xl shadow-indigo-100 rounded-2xl w-[85%] max-w-md p-10 z-10">
                <h1 class="pt-2 pb-6 font-bold text-gray-700 text-4xl text-center select-none">Register</h1>

                @if($errors->any())
                    <div class="mb-5 text-sm bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded-lg shadow-sm">
                        <ul class="list-disc list-inside text-left">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.submit') }}" method="POST" class="space-y-4" x-data="{ selectedRole: '' }">
                    @csrf

                    <input type="text" name="name" placeholder="Name" required class="border p-3 rounded w-full">

                    <input type="email" name="email" placeholder="Email" required class="border p-3 rounded w-full">

                    <!-- 🔹 Tambahan Nomor Telepon -->
                    <input type="text" name="phone" placeholder="Nomor Telepon" required class="border p-3 rounded w-full">

                    <input type="password" name="password" placeholder="Password" required class="border p-3 rounded w-full">

                    <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="border p-3 rounded w-full">

                    <!-- 🔽 Dropdown Role -->
                    <select name="id_role" required x-model="selectedRole" class="border p-3 rounded w-full bg-white text-gray-700">
                        <option value="" disabled selected>Pilih Role</option>
                        <option value="4">Santri</option>
                        <option value="5">Wali</option>
                        <option value="6">Degur</option>
                        <option value="7">Pengurus</option>
                    </select>

                    <!-- 🔒 Kode Rahasia -->
                    <div x-show="selectedRole == 6 || selectedRole == 7" x-transition>
                        <div class="flex gap-2 mt-2">
                            <input type="text" name="secret_code" placeholder="Kode Rahasia" class="border p-3 rounded w-1/2">
                            <p class="text-xs text-gray-500 flex items-center">
                                Hanya untuk <span class="ml-1 font-semibold" x-text="selectedRole == 6 ? 'Degur' : 'Pengurus'"></span>.
                            </p>
                        </div>
                    </div>

                    <button type="submit"
                        class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-purple-500 hover:to-blue-500 text-white py-2 w-full rounded transition duration-300 ease-in-out">
                        Register
                    </button>
                </form>




                <div class="text-center mt-5 text-gray-500 text-sm">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-blue-400 hover:underline font-medium">Log In</a>
                </div>

                <p class="text-gray-400 mt-4 text-xs text-center">
                    By registering, you agree to our
                    <a href="#" class="text-blue-400 hover:underline">Terms</a> and
                    <a href="#" class="text-blue-400 hover:underline">Privacy Policy</a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
