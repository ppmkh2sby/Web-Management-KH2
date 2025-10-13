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
    <title>Forgot Password</title>
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

            <!-- Background Slideshow -->
            <template x-for="(img, index) in images" :key="index">
                <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="active === index ? 'opacity-100' : 'opacity-0'">
                    <img :src="img" alt="" class="object-cover w-full h-full" />
                </div>
            </template>

            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-tr from-black/60 via-transparent to-indigo-700/50 mix-blend-multiply"></div>

            <!-- Info Text -->
            <div class="absolute bottom-6 left-6 text-white transition-all duration-500 ease-in-out" x-transition>
                <h1 class="text-3xl font-bold drop-shadow-lg tracking-wide">OUR MEMORIES</h1>
                <p class="text-sm text-gray-100 mt-1">Kebersamaan, perjuangan, dan tawa yang tak terlupakan.</p>
            </div>

            <!-- Indicator Bullets -->
            <div class="absolute bottom-6 right-6 flex space-x-2">
                <template x-for="(img, i) in images" :key="i">
                    <div class="w-3 h-3 rounded-full transition-all duration-500"
                        :class="active === i ? 'bg-white scale-110' : 'bg-gray-400 opacity-50'"></div>
                </template>
            </div>
        </div>

        <!-- RIGHT PAGE -->
        <div class="right_page w-1/2 flex justify-center items-center bg-gradient-to-tr from-gray-50 to-white relative">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-100/30 via-purple-100/20 to-transparent"></div>

            <div
                class="relative bg-white/90 backdrop-blur-xl border border-gray-200 rounded-2xl shadow-xl p-10 w-[85%] max-w-md z-10 text-center transform hover:-translate-y-1 transition-all duration-300 ease-out">

                <!-- Icon -->
                <div class="flex justify-center mb-6">
                    <div
                        class="w-16 h-16 flex items-center justify-center bg-gradient-to-r from-blue-500 to-purple-500 rounded-full shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 12H8m8 0l-4 4m4-4l-4-4m12 8a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-3xl font-bold text-gray-700 mb-2">Forgot Password?</h1>
                <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                    Don’t worry! Just enter your email below and we’ll send you a link to reset your password.
                </p>

                <!-- Error / Success Messages -->
                @if($errors->any())
                    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- FORM -->
                <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-left text-gray-600 text-sm font-medium mb-1">
                            Email Address
                        </label>
                        <input id="email" name="email"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            type="email" placeholder="Enter your registered email" required />
                    </div>

                    <button type="submit"
                        class="w-full py-2 mt-3 rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold shadow-lg hover:scale-105 hover:from-purple-500 hover:to-blue-500 transition duration-300">
                        SEND RESET LINK
                    </button>
                </form>

                <!-- Back to Login -->
                <p class="text-gray-500 text-sm mt-6">
                    Remember your password?
                    <a href="{{ route('login') }}" class="text-blue-500 hover:underline font-medium">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
