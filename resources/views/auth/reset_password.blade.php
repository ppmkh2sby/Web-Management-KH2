<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 w-96">
        <h2 class="text-2xl font-semibold text-center mb-4">Reset Password</h2>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <label class="block text-gray-600 mb-2">Email</label>
            <input type="email" name="email" required class="border border-gray-300 p-2 w-full rounded mb-4" placeholder="Enter your email">

            <label class="block text-gray-600 mb-2">New Password</label>
            <input type="password" name="password" required class="border border-gray-300 p-2 w-full rounded mb-4" placeholder="New password">

            <label class="block text-gray-600 mb-2">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="border border-gray-300 p-2 w-full rounded mb-4" placeholder="Confirm password">

            <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-500 text-white p-2 rounded hover:opacity-90 transition">
                Reset Password
            </button>
        </form>
    </div>
</body>
</html>
