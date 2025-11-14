<?php

namespace App\Http\Controllers\Auth;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LoginCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class RegisteredStaffController extends Controller
{
    public function create()
    {
        return view('auth.register-staff');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','lowercase','email','max:255','unique:users,email'],
            'password' => ['required','confirmed', Rules\Password::defaults()],
            'role'     => ['required', Rule::in([Role::PENGURUS->value, Role::DEWAN_GURU->value])],
            'verification_code' => ['required','string'],
        ]);

        $role = Role::from($data['role']);

        // Cek kode di DB (hash)
        $row = DB::table('role_verification_codes')
            ->where('role', $role->value)
            ->orderByDesc('id')
            ->first();

        if (!$row || ($row->expires_at && now()->greaterThan($row->expires_at)) || !Hash::check($data['verification_code'], $row->code_hash)) {
            return back()->withErrors(['verification_code' => 'Kode verifikasi tidak valid atau kadaluarsa.'])->withInput();
        }

        // optional: batasi pemakaian
        if (!is_null($row->max_uses) && $row->uses >= $row->max_uses) {
            return back()->withErrors(['verification_code' => 'Kode verifikasi sudah mencapai batas penggunaan.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'login_code' => LoginCodeGenerator::generate($role),
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        // increment uses
        DB::table('role_verification_codes')->where('id', $row->id)->increment('uses');

        auth()->login($user);

        return redirect()->route('dashboard')->with('status', 'Pendaftaran staf berhasil.');
    }
}
