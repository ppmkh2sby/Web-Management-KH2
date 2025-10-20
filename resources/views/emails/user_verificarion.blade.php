<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Akun</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Halo, {{ $user->name }} 👋</h2>
    <p>Terima kasih telah mendaftar di <strong>Management KH2</strong>.</p>
    <p>Untuk mengaktifkan akun Anda, klik tautan verifikasi di bawah ini:</p>

    <p style="margin: 20px 0;">
        <a href="{{ $verificationUrl }}" 
           style="background-color:#4f46e5;color:white;padding:10px 20px;
                  border-radius:8px;text-decoration:none;">
           Verifikasi Akun
        </a>
    </p>

    <p>Jika Anda tidak merasa membuat akun, abaikan email ini.</p>
    <hr>
    <p style="font-size:12px;color:#999;">
        © {{ date('Y') }} Management KH2. Semua hak dilindungi.
    </p>
</body>
</html>
