<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nama_role' => 'Admin',    'deskripsi' => 'Akses penuh ke seluruh fitur'],
            ['nama_role' => 'Pengurus', 'deskripsi' => 'Dapat mengelola presensi dan data santri'],
            ['nama_role' => 'Degur',    'deskripsi' => 'Dapat CRUD dan melihat semua santri'],
            ['nama_role' => 'Santri',   'deskripsi' => 'Hanya dapat melihat data dan presensi dirinya sendiri'],
            ['nama_role' => 'Wali',     'deskripsi' => 'Hanya dapat melihat data anaknya dan laporan presensi'],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['nama_role' => $roleData['nama_role']], // cek berdasarkan nama_role
                ['deskripsi' => $roleData['deskripsi']]  // update deskripsi jika sudah ada
            );
        }

        $this->command->info('✅ Roles seeded successfully (no duplicates).');
    }
}
