<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = 'maestrorafa05@gmail.com';

        Admin::updateOrCreate(
            ['email' => $adminEmail], // Cek email unik
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'), // kamu bisa ubah password default di sini
                'token' => '',
            ]
        );

        $this->command->info('✅ Admin seeded successfully (no duplicates).');
    }
}
