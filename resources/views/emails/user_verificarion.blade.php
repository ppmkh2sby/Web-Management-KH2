<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9fafb; color: #111827; }
        .container { background: #fff; border-radius: 10px; padding: 20px; margin: 30px auto; max-width: 500px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn { background: linear-gradient(to right, #6366f1, #8b5cf6); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello, {{ $user->name }} 👋</h2>
        <p>Thank you for registering! Please verify your email by clicking the button below:</p>
        <a href="{{ $verificationUrl }}" class="btn">Verify Email</a>
        <p>If you didn’t register, please ignore this email.</p>
    </div>
</body>
</html>
