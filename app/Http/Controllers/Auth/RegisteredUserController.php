<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Enum\Role;
use App\Models\Santri;
use App\Models\Wali;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan form register gabungan (semua role, kecuali admin).
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Simpan pendaftaran user baru + alur khusus per-role.
     * Setelah sukses, login langsung tanpa verifikasi email.
     */
    public function store(Request $request): RedirectResponse
    {
        // --- Validasi dasar + khusus role ---
        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','string','lowercase','email','max:255','unique:users,email'],
            'phone' => ['required','string','max:32','regex:/^[0-9+\s\-().]{8,20}$/'],

            // password minimal 8, wajib sama dengan password_confirmation
            'password' => ['required','confirmed', PasswordRule::min(8)->letters()->numbers()],

            // role harus salah satu dari enum
            'role' => ['required','string', Rule::in(array_map(fn($r)=>$r->value, Role::cases()))],

            // khusus Wali: wajib isi kode santri
            'santri_code'       => ['required_if:role,'.Role::WALI->value, 'nullable','string','exists:santris,code'],

            // khusus Pengurus/Degur: wajib isi kode verifikasi
            'verification_code' => ['required_if:role,'.Role::PENGURUS->value, 'required_if:role,'.Role::DEWAN_GURU->value, 'nullable','string'],
        ], [
            'santri_code.required_if'       => 'Kode Anak wajib diisi untuk pendaftaran Wali.',
            'santri_code.exists'            => 'Kode Anak tidak ditemukan.',
            'verification_code.required_if' => 'Kode Rahasia wajib diisi untuk Pengurus/Dewan Guru.',
            'phone.regex'                   => 'Format nomor handphone tidak valid.',
        ]);

        // --- Buat user ---
        $user = new User();
        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->phone    = $data['phone'];
        $user->password = Hash::make($data['password']);
        $user->role     = Role::from($data['role']); // cast ke enum (atau simpan string jika model belum cast)

        // --- Aksi khusus per-role ---
        switch ($data['role']) {
            case Role::SANTRI->value:
                // simpan user dulu agar dapat ID
                $user->save();

                // generate kode santri unik: S-XXXXXXXX
                do {
                    $code = 'S-'.strtoupper(Str::random(8));
                } while (Santri::where('code', $code)->exists());

                Santri::create([
                    'user_id'       => $user->id,
                    'code'          => $code,
                    'nama_lengkap'  => $user->name,
                ]);
                break;

            case Role::WALI->value:
                // pastikan kode anak valid (sudah divalidasi exists, ini jaga-jaga)
                $santri = Santri::where('code', $data['santri_code'] ?? '')->first();
                if (!$santri) {
                    return back()->withErrors(['santri_code' => 'Kode Anak tidak valid.'])->withInput();
                }
                $user->save();
                // relasi wali → santri (assume pivot: santri_wali dengan user_id wali)
                $santri->walis()->syncWithoutDetaching([$user->id]);
                break;

            case Role::PENGURUS->value:
            case Role::DEWAN_GURU->value:
                // verifikasi kode rahasia dari admin
                $plain = trim((string)($data['verification_code'] ?? ''));

                $row = DB::table('role_verification_codes')
                    ->where('role', $data['role']) // harus 'pengurus' atau 'degur' sesuai enum
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('max_uses')->orWhereColumn('uses','<','max_uses');
                    })
                    ->orderByDesc('id')
                    ->get()
                    ->first(fn($r) => Hash::check($plain, $r->code_hash));

                if (!$row) {
                    return back()->withErrors([
                        'verification_code' => 'Kode Rahasia tidak valid / kadaluarsa / sudah terpakai.'
                    ])->withInput();
                }

                // simpan user & increment penggunaan kode
                $user->save();
                DB::table('role_verification_codes')->where('id', $row->id)->increment('uses');
                break;
        }

        // --- Lewati verifikasi email; login dan langsung masuk dashboard ---
        Auth::login($user);
        return redirect()->route('dashboard')->with('status', 'Pendaftaran berhasil. Selamat datang!');
    }
}
