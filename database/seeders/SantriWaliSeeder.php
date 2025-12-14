<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SantriWaliSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'kh2kh2kh2';

    /**
     * Jalankan seed untuk santri + wali dengan kode induk tetap.
     */
    public function run(): void
    {
        $timByName = [
            'Alwida Rahmat' => 'Ketertiban',
            'Maestro Rafa Agniya' => 'Ketertiban',
            'Syahdinda Sherlyta Laura' => 'Ketertiban',
            'Zaky Afifi Arif' => 'Keilmuan',
            'Cherfine An-Nisaul Auliya Ulla' => 'Keilmuan',
            'Muhammad Farizky Alfath Muhardian Putra' => 'Keilmuan',
            'Renata Keysha Azalia' => 'Acara',
            'Ayesha Nayyara Putri Wuryadi' => 'Acara',
            'Keisha Zafif Fahrezi' => 'Acara',
            'Maritza Dara Athifa' => 'Sekben',
            'Zahra Suciana Tri Amma Maretha' => 'Sekben',
            "Fahmi Rosyidin Al'Ulya" => 'Sekben',
            'Deven Kartika Wijaya' => 'Kebersihan',
            'Muhammad Setyo Arfan Ibrahim' => 'ukppt',
            'Muhammad Farrel Al-Aqso' => 'ukppt',
            'Azzahra Jamalullaily Mafaza' => 'ukppt',
            'Rara Arimbi Gita Atmodjo' => 'ph',
        ];

        $entries = [
            [
                'santri' => ['code' => '022424001', 'name' => 'Alwida Rahmat'],
                'wali'   => ['code' => '0231324001', 'name' => 'Wali Alwida Rahmat'],
            ],
            [
                'santri' => ['code' => '022424002', 'name' => 'Ayesha Nayyara Putri Wuryadi'],
                'wali'   => ['code' => '0231324002', 'name' => 'Wali Ayesha Nayyara Putri Wuryadi'],
            ],
            [
                'santri' => ['code' => '022424003', 'name' => 'Azzahra Jamalullaily Mafaza'],
                'wali'   => ['code' => '0231324003', 'name' => 'Wali Azzahra Jamalullaily Mafaza'],
            ],
            [
                'santri' => ['code' => '022424004', 'name' => 'Cherfine An-Nisaul Auliya Ulla'],
                'wali'   => ['code' => '0231324004', 'name' => 'Wali Cherfine An-Nisaul Auliya Ulla'],
            ],
            [
                'santri' => ['code' => '022424005', 'name' => 'Deven Kartika Wijaya'],
                'wali'   => ['code' => '0231324005', 'name' => 'Wali Deven Kartika Wijaya'],
            ],
            [
                'santri' => ['code' => '022424006', 'name' => "Fahmi Rosyidin Al'Ulya"],
                'wali'   => ['code' => '0231324006', 'name' => "Wali Fahmi Rosyidin Al'Ulya"],
            ],
            [
                'santri' => ['code' => '022424007', 'name' => 'Keisha Zafif Fahrezi'],
                'wali'   => ['code' => '0231324007', 'name' => 'Wali Keisha Zafif Fahrezi'],
            ],
            [
                'santri' => ['code' => '022424008', 'name' => 'Maestro Rafa Agniya'],
                'wali'   => ['code' => '0231324008', 'name' => 'Wali Maestro Rafa Agniya'],
            ],
            [
                'santri' => ['code' => '022424009', 'name' => 'Maritza Dara Athifa'],
                'wali'   => ['code' => '0231324009', 'name' => 'Wali Maritza Dara Athifa'],
            ],
            [
                'santri' => ['code' => '022424010', 'name' => 'Muhammad Farizky Alfath Muhardian Putra'],
                'wali'   => ['code' => '0231324010', 'name' => 'Wali Muhammad Farizky Alfath Muhardian Putra'],
            ],
            [
                'santri' => ['code' => '022424011', 'name' => 'Muhammad Farrel Al-Aqso'],
                'wali'   => ['code' => '0231324011', 'name' => 'Wali Muhammad Farrel Al-Aqso'],
            ],
            [
                'santri' => ['code' => '022424012', 'name' => 'Muhammad Setyo Arfan Ibrahim'],
                'wali'   => ['code' => '0231324012', 'name' => 'Wali Muhammad Setyo Arfan Ibrahim'],
            ],
            [
                'santri' => ['code' => '022424013', 'name' => 'Rara Arimbi Gita Atmodjo'],
                'wali'   => ['code' => '0231324013', 'name' => 'Wali Rara Arimbi Gita Atmodjo'],
            ],
            [
                'santri' => ['code' => '022424014', 'name' => 'Renata Keysha Azalia'],
                'wali'   => ['code' => '0231324014', 'name' => 'Wali Renata Keysha Azalia'],
            ],
            [
                'santri' => ['code' => '022424015', 'name' => 'Syahdinda Sherlyta Laura'],
                'wali'   => ['code' => '0231324015', 'name' => 'Wali Syahdinda Sherlyta Laura'],
            ],
            [
                'santri' => ['code' => '022424016', 'name' => 'Zahra Suciana Tri Amma Maretha'],
                'wali'   => ['code' => '0231324016', 'name' => 'Wali Zahra Suciana Tri Amma Maretha'],
            ],
            [
                'santri' => ['code' => '022424017', 'name' => 'Zaky Afifi Arif'],
                'wali'   => ['code' => '0231324017', 'name' => 'Wali Zaky Afifi Arif'],
            ],
        ];

        $passwordHash = Hash::make(self::DEFAULT_PASSWORD);

        foreach ($entries as $entry) {
            $santriUser = $this->upsertUser($entry['santri'], Role::SANTRI, $passwordHash, 'santri');
            $waliUser   = $this->upsertUser($entry['wali'], Role::WALI, $passwordHash, 'wali');
            $tim        = $timByName[$entry['santri']['name']] ?? null;

            $santri = Santri::updateOrCreate(
                ['code' => $entry['santri']['code']],
                [
                    'user_id' => $santriUser->id,
                    'nama_lengkap' => $entry['santri']['name'],
                    'tim' => $tim,
                ]
            );

            DB::table('santri_wali')->updateOrInsert(
                [
                    'santri_id' => $santri->id,
                    'wali_user_id' => $waliUser->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $staffGroups = [
            [
                'role' => Role::DEWAN_GURU,
                'type' => 'degur',
                'members' => [
                    ['code' => '0235499001', 'name' => 'Amir'],
                    ['code' => '0235499002', 'name' => 'Anton'],
                    ['code' => '0235499003', 'name' => 'Ridho'],
                ],
            ],
            [
                'role' => Role::PENGURUS,
                'type' => 'pengurus',
                'members' => [
                    ['code' => '0218354001', 'name' => 'Saiful'],
                    ['code' => '0218354002', 'name' => 'Hiru'],
                    ['code' => '0218354003', 'name' => 'Angga'],
                    ['code' => '0218354004', 'name' => 'Avan'],
                    ['code' => '0218354005', 'name' => 'Abdurrahman'],
                ],
            ],
        ];

        foreach ($staffGroups as $group) {
            foreach ($group['members'] as $member) {
                $this->upsertUser($member, $group['role'], $passwordHash, $group['type']);
            }
        }
    }

    private function upsertUser(array $profile, Role $role, string $passwordHash, string $type): User
    {
        return User::updateOrCreate(
            ['login_code' => $profile['code']],
            [
                'name' => $profile['name'],
                'email' => sprintf('%s@%s.kh2.local', $profile['code'], $type),
                'password' => $passwordHash,
                'role' => $role,
                'email_verified_at' => now(),
            ]
        );
    }
}
