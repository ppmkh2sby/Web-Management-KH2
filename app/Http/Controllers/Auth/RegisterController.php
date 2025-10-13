<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserVerificationMail;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        // kalau mau dropdown diisi dari DB:
        // $roles = Role::whereIn('nama_role', ['Santri','Wali','Degur','Pengurus'])->get();
        return view('auth.register'); // sudah oke
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'phone'                 => 'required|string|max:20',      // WAJIB karena kolom NOT NULL
            'password'              => 'required|confirmed|min:6',     // butuh field password_confirmation
            'id_role'               => 'required|integer|exists:roles,id',
            'secret_code'           => 'nullable|string',
        ]);

        // Validasi kode rahasia untuk Degur/Pengurus
        if (in_array((int)$request->id_role, [/* Degur */ 3, /* Pengurus */ 4])) {
            $request->validate([
                'secret_code' => 'required|string',
            ]);

            $expected = (int)$request->id_role === 3
                ? config('auth.secret_codes.degur')
                : config('auth.secret_codes.pengurus');

            if (! hash_equals($expected, (string) $request->secret_code)) {
                return back()->withErrors(['secret_code' => 'Kode rahasia tidak valid.'])->withInput();
            }
        }

        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'phone'               => $request->phone,       // <- inilah yang sebelumnya bikin error
            'password'            => bcrypt($request->password),
            'id_role'             => (int)$request->id_role,
            'verification_token'  => Str::random(64),
            'status'              => 0,
        ]);

        // Kirim email verifikasi
        Mail::to($user->email)->send(new UserVerificationMail($user));

        return redirect()->route('login')
            ->with('success', 'Akun dibuat. Cek email untuk verifikasi.');
    }

    public function verifyEmail(string $token)
    {
        $user = User::where('verification_token', $token)->first();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Token verifikasi tidak valid.');
        }

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->status = 1;
        $user->save();

        return redirect()->route('login')->with('success', 'Email terverifikasi. Silakan login.');
    }
}
