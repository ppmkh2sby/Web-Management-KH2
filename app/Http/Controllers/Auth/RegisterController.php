<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserVerificationMail;
use App\Models\User;
use App\Models\Role;
use App\Models\WaliSantriRelasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register'); 
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'phone'                 => 'required|string|max:20',      
            'password'              => 'required|confirmed|min:6',    
            'id_role'               => 'required|integer|exists:roles,id',
            'secret_code'           => 'nullable|string',
        ]);

        if (in_array((int)$request->id_role, [6,7])) {
            $request->validate([
                'secret_code' => 'required|string',
            ]);

            $expected = (int)$request->id_role === 6
                ? config('auth.secret_codes.degur')
                : config('auth.secret_codes.pengurus');

            if (! hash_equals($expected, (string) $request->secret_code)) {
                return back()->withErrors(['secret_code' => 'Kode rahasia tidak valid.'])->withInput();
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'id_role' => $request->id_role,
            'verification_token' => Str::random(64),
            'status' => 0,
            'token' => Str::random(60),
        ]);


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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'phone' => 'required|string|max:20',
            'kode_anak' => 'required|string', 
        ]);

        // Cari santri dengan kode unik
        $santri = User::where('kode_unik', $request->kode_anak)
                    ->where('id_role', 4) // role santri
                    ->first();

        if (!$santri) {
            return back()->withErrors(['kode_anak' => 'Kode anak tidak ditemukan atau sudah digunakan.']);
        }

        $wali = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'id_role' => 5, 
            'status' => 0,
            'token' => Str::random(64),
        ]);

        WaliSantriRelasi::create([
            'id_wali' => $wali->id,
            'id_santri' => $santri->id,
            'hubungan' => 'Orang Tua',
        ]);

        return redirect()->route('login')->with('success', 'Akun wali berhasil dibuat. Silakan login.');
    }
}
