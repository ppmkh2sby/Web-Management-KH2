<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Pastikan akun sudah diverifikasi dan aktif
            if ($user->status == 0) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun belum diverifikasi, cek email Anda.']);
            }

            // 🔹 Arahkan ke dashboard berdasarkan role
            switch ($user->id_role) {
                case 4: // Santri
                    return redirect()->route('santri.dashboard');

                case 5: // Wali
                    return redirect()->route('wali.dashboard');

                case 6: // Degur
                    return redirect()->route('degur.dashboard');

                case 7: // Pengurus
                    return redirect()->route('pengurus.dashboard');

                default: // Admin atau lainnya
                    return redirect()->route('admin_dashboard');
            }
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }
}
