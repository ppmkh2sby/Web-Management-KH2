<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Akun Anda</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Halo, {{ $user->name }}!</h2>
    <p>Terima kasih sudah mendaftar di sistem kami.</p>
    <p>Silakan klik link di bawah ini untuk memverifikasi akun Anda:</p>

    <p>
        <a href="{{ url('/verify-email/'.$user->verification_token) }}" 
           style="display:inline-block;padding:10px 20px;background:#4f46e5;color:white;text-decoration:none;border-radius:8px;">
            Verifikasi Sekarang
        </a>
    </p>

    <p>Jika Anda tidak mendaftar akun ini, abaikan saja email ini.</p>
    <br>
    <p>Terima kasih,<br>Tim PPM KH2</p>
</body>
</html>
