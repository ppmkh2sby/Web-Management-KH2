<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\UserVerificationMail;

class UserRegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'id_role' => 'required|exists:roles,id',
            'secret_code' => 'nullable|string', // tambahkan validasi opsional
        ]);

        // Validasi khusus jika role = Degur atau Pengurus
        if (in_array($request->id_role, [6, 7])) {
            $validCodes = [
                6 => env('DEGUR_CODE', 'DEGUR-2025'),
                7 => env('PENGURUS_CODE', 'PENGURUS-2025'),
            ];

            if (!isset($validCodes[$request->id_role]) || $request->secret_code !== $validCodes[$request->id_role]) {
                return back()->withErrors(['secret_code' => 'Kode rahasia tidak valid untuk role yang dipilih.'])->withInput();
            }
        }

        // Simpan user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->id_role = $request->id_role;
        $user->verification_token = Str::random(64);
        $user->save();

        Mail::to($user->email)->send(new UserVerificationMail($user));

        return redirect()->route('login')->with('success', 'Check your email to verify your account.');
    }


    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid verification token.');
        }

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        return redirect()->route('login')->with('success', 'Email verified successfully!');
    }
}
