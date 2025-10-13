<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    // Menampilkan form untuk input email user
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    // Mengirim email berisi link reset password
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Please check your email for the password reset link.')
            : back()->withErrors(['email' => __($status)]);
    }

    // Menampilkan form untuk reset password (setelah klik link dari email)
    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    // Menyimpan password baru user
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password has been reset successfully.')
            : back()->withErrors(['email' => [__($status)]]);
    }
}
