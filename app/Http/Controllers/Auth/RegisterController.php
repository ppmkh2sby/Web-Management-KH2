<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserVerificationMail;
use App\Models\Role;
use App\Models\User;
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
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'role'             => ['required', 'in:santri,wali'],
            'kode_unik_santri' => ['nullable', 'string'],
            'hubungan'         => ['nullable', 'string', 'max:50'],
        ]);

        $roleName = strtolower($request->input('role'));
        $role = Role::whereRaw('LOWER(nama_role) = ?', [$roleName])->first();
        if (! $role) {
            return back()->withErrors(['role' => 'Role tidak ditemukan. Jalankan seeder roles.'])->withInput();
        }

        $user = new User();
        $user->name  = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->id_role = $role->id;
        $user->verification_token = Str::random(64);

        if ($roleName === 'santri') {
            // generate kode unik santri
            $user->kode_unik = User::generateSantriCode();
        }

        if ($roleName === 'wali') {
            $kode = trim((string) $request->kode_unik_santri);
            if ($kode === '') {
                return back()->withErrors(['kode_unik_santri' => 'Kode unik santri wajib diisi untuk role wali.'])->withInput();
            }

            $santri = User::where('kode_unik', $kode)->first();
            if (! $santri) {
                return back()->withErrors(['kode_unik_santri' => 'Kode unik tidak ditemukan.'])->withInput();
            }

            // simpan user wali
            $user->save();

            // buat relasi wali -> santri
            WaliSantriRelasi::create([
                'id_wali'   => $user->id,
                'id_santri' => $santri->id,
                'hubungan'  => $request->hubungan ?: null,
            ]);

            // kirim email verifikasi
            Mail::to($user->email)->send(new UserVerificationMail($user));

            return redirect()->route('login')->with('success', 'Akun wali berhasil dibuat. Silakan verifikasi email Anda.');
        }

        // untuk santri
        $user->save();
        Mail::to($user->email)->send(new UserVerificationMail($user));

        return redirect()->route('login')->with('success', 'Akun santri berhasil dibuat. Silakan verifikasi email Anda.');
    }

    public function verifyEmail(string $token)
    {
        $user = User::where('verification_token', $token)->first();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Token verifikasi tidak valid.');
        }

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        return redirect()->route('login')->with('success', 'Email berhasil diverifikasi. Silakan login.');
    }
}
