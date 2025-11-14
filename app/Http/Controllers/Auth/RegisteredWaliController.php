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

class RegisteredWaliController extends Controller
{
    public function create()
    {
        return view('auth.register-wali');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','lowercase','email','max:255','unique:users,email'],
            'password' => ['required','confirmed', Rules\Password::defaults()],
            'santri_code' => ['required','string','exists:santris,code'],
        ], [
            'santri_code.exists' => 'Kode santri tidak ditemukan.',
        ]);

        $santri = Santri::where('code', $data['santri_code'])->first();

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'login_code' => LoginCodeGenerator::generate(Role::WALI),
            'password' => Hash::make($data['password']),
            'role' => Role::WALI,
        ]);

        // Link wali -> santri
        $santri->walis()->syncWithoutDetaching([$user->id]);

        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('status', 'Pendaftaran wali berhasil. Akun terhubung ke data anak.');
    }
}
