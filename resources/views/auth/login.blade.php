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
    <title>Login</title>
</head>

<body class="h-screen w-screen flex justify-center items-center bg-gray-100 font-[Poppins]">

    <div class="container flex w-5/6 h-4/5 shadow-2xl rounded-3xl bg-white overflow-hidden">
        <!-- LEFT PAGE: Slideshow -->
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

        <!-- RIGHT PAGE: Login Card -->
        <div class="right_page w-1/2 flex justify-center items-center relative bg-gradient-to-br from-gray-50 to-gray-100 rounded-r-3xl">
            <div class="absolute w-72 h-72 bg-blue-400 opacity-20 blur-3xl rounded-full -top-10 -right-16"></div>

            <div class="relative bg-white/90 backdrop-blur-md border border-gray-200 shadow-2xl shadow-indigo-100 rounded-2xl w-[85%] max-w-md p-10 z-10 transform hover:-translate-y-1 transition-all duration-300 ease-out">

                <h1 class="pt-2 pb-6 font-bold text-gray-700 text-4xl text-center select-none">Log In</h1>

                <!-- Error message -->
                @if($errors->any())
                    <div class="mb-5 text-sm text-center bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded-lg shadow-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-5 text-sm text-center bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded-lg shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- FORM LOGIN -->
                <form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-gray-600 text-sm font-medium">Email</label>
                        <input id="email" name='email'
                            class="border border-gray-300 bg-white/80 p-3 rounded-lg w-full shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none focus:scale-105 transition duration-300"
                            type="email" placeholder="Email" required />
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-gray-600 text-sm font-medium">Password</label>
                        <input id="password" name="password"
                            class="border border-gray-300 bg-white/80 p-3 rounded-lg w-full shadow-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none focus:scale-105 transition duration-300"
                            type="password" placeholder="Password" required />
                    </div>

                    <a href="{{ route('password.request') }}"
                        class="text-sm text-blue-500 hover:underline block text-right">Forgot your password?</a>

                    <button type="submit"
                        class="w-full py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold shadow-md hover:shadow-lg hover:scale-105 transition duration-300 ease-in-out">
                        LOG IN
                    </button>
                </form>

                <!-- Register Link -->
                <div class="text-center mt-5 text-gray-500 text-sm">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-blue-400 hover:underline font-medium">Sign Up</a>
                </div>

                <!-- Social Media Icons -->
                <div class="flex justify-center flex-wrap mt-5">
                    @foreach ([
                        'https://ucarecdn.com/8f25a2ba-bdcf-4ff1-b596-088f330416ef/' => 'Google',
                        'https://ucarecdn.com/95eebb9c-85cf-4d12-942f-3c40d7044dc6/' => 'Linkedin',
                        'https://ucarecdn.com/be5b0ffd-85e8-4639-83a6-5162dfa15a16/' => 'Github',
                        'https://ucarecdn.com/6f56c0f1-c9c0-4d72-b44d-51a79ff38ea9/' => 'Facebook',
                        'https://ucarecdn.com/82d7ca0a-c380-44c4-ba24-658723e2ab07/' => 'Twitter',
                        'https://ucarecdn.com/3277d952-8e21-4aad-a2b7-d484dad531fb/' => 'Apple'
                    ] as $src => $alt)
                        <button class="hover:scale-105 ease-in-out duration-300 shadow-lg p-2 rounded-lg m-1 bg-white">
                            <img class="max-w-[25px]" src="{{ $src }}" alt="{{ $alt }}">
                        </button>
                    @endforeach
                </div>

                <!-- Terms -->
                <p class="text-gray-400 mt-4 text-xs text-center">
                    By signing in, you agree to our
                    <a href="#" class="text-blue-400 hover:underline">Terms</a> and
                    <a href="#" class="text-blue-400 hover:underline">Privacy Policy</a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
