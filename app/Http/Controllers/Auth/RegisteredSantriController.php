<?php

namespace App\Http\Controllers\Auth;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\User;
use App\Support\LoginCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredSantriController extends Controller
{
    public function create()
    {
        return view('auth.register-santri');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','lowercase','email','max:255','unique:users,email'],
            'password' => ['required','confirmed', Rules\Password::defaults()],
            'nama_lengkap' => ['nullable','string','max:255'],
        ]);

        $code = LoginCodeGenerator::generate(Role::SANTRI);

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'login_code' => $code,
            'password' => Hash::make($data['password']),
            'role' => Role::SANTRI,
        ]);

        Santri::create([
            'user_id' => $user->id,
            'code' => $code,
            'nama_lengkap' => $data['nama_lengkap'] ?? $data['name'],
        ]);

        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('status', "Pendaftaran santri berhasil. Kode verifikasi untuk Wali: {$code}");
    }
    // Kode login santri sekarang dibangkitkan melalui LoginCodeGenerator agar konsisten di semua flow.
}
