<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserVerificationMail;
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
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'phone'       => 'required|string|max:20',
            'password'    => 'required|confirmed|min:6',
            'id_role'     => 'required|integer|exists:roles,id',
            'secret_code' => 'nullable|string',
            'kode_anak'   => 'nullable|string',
        ]);

            // ✅ Validasi kode rahasia untuk Degur / Pengurus
            if (in_array((int)$request->id_role, [6, 7])) {
                $request->validate(['secret_code' => 'required|string']);

                $expected = (int)$request->id_role === 6
                    ? config('auth.secret_codes.degur')
                    : config('auth.secret_codes.pengurus');

                if (! hash_equals($expected, (string) $request->secret_code)) {
                    return back()->withErrors(['secret_code' => 'Kode rahasia tidak valid.'])->withInput();
                }
            }

            // ✅ Validasi dan ambil data santri berdasarkan kode unik (untuk Wali)
            $santri = null;
            if ((int)$request->id_role === 5) {
                $request->validate(['kode_anak' => 'required|string']);

                $santri = User::where('kode_unik', $request->kode_anak)
                            ->where('id_role', 4)
                            ->first();

                if (! $santri) {
                    return back()->withErrors(['kode_anak' => 'Kode anak tidak ditemukan atau sudah digunakan.'])->withInput();
                }
            }

            // ✅ Generate token unik tanpa duplikasi
            do {
                $token = Str::random(60);
            } while (User::where('token', $token)->exists());

            // ✅ Buat user baru
            $user = User::create([
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => bcrypt($request->password),
                'id_role'     => $request->id_role,
                'verification_token' => Str::random(64),
                'status'      => 0,
                'token'       => $token, // disimpan langsung yang sudah dicek unik
            ]);

            // ✅ Jika role SANTRI, buat kode unik KH2-XXXXXX
            if ((int)$request->id_role === 4) {
                do {
                    $kode_unik = 'KH2-' . strtoupper(Str::random(6));
                } while (User::where('kode_unik', $kode_unik)->exists());

                $user->kode_unik = $kode_unik;
                $user->save();
            }

            // ✅ Jika role WALI, buat relasi otomatis ke santri
            if ((int)$request->id_role === 5 && $santri) {
                WaliSantriRelasi::create([
                    'id_wali'   => $user->id,
                    'id_santri' => $santri->id,
                    'hubungan'  => 'Orang Tua',
                ]);
            }

            // ✅ Kirim email verifikasi
            Mail::to($user->email)->send(new UserVerificationMail($user));

            return redirect()->route('login')
                ->with('success', 'Akun berhasil dibuat. Silakan cek email untuk verifikasi.');
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

            return redirect()->route('login')->with('success', 'Email berhasil diverifikasi. Silakan login.');
        }
    }
