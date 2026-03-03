<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\User;
use App\Support\LoginCodeGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InitialSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@kh2.local'], 
            [
                'name' => 'Super Admin',
                'password' => Hash::make('kh2kh2kh2'),
                'role' => Role::ADMIN,
                'email_verified_at' => now(),
                'login_code' => LoginCodeGenerator::generate(Role::ADMIN),
            ]
        );

        if (! $admin->login_code) {
            $admin->forceFill([
                'login_code' => LoginCodeGenerator::generate(Role::ADMIN),
            ])->save();
        }

        $this->issueCode(Role::PENGURUS, 'PENGURUS-KH2');
        $this->issueCode(Role::DEWAN_GURU, 'DEGUR-313');
    }

    private function issueCode(Role $role, string $plainCode, ?int $maxUses = null): void
    {
        DB::table('role_verification_codes')->updateOrInsert(
            ['role' => $role->value], 
            [
                'code_hash'  => Hash::make($plainCode), 
                'max_uses'   => $maxUses,
                'uses'       => 0,
                'expires_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
