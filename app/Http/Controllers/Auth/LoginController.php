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
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Regenerate session ID for security
        $request->session()->regenerate();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Pastikan email sudah diverifikasi
            if (is_null($user->email_verified_at)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Email Anda belum terverifikasi. Silakan cek email untuk verifikasi.'
                ])->onlyInput('email');
            }

            $role = strtolower(optional($user->role)->nama_role ?? '');

            return match ($role) {
                'santri'   => redirect()->route('santri.dashboard'),
                'wali'     => redirect()->route('wali.dashboard'),
                'pengurus' => redirect()->route('pengurus.dashboard'),
                'degur', 'dewan_guru' => redirect()->route('degur.dashboard'),
                'admin'    => redirect('/admin/dashboard'),
                default    => redirect('/'),
            };
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }
}
