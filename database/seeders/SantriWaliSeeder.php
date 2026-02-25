<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SantriWaliSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'kh2kh2kh2';

    public function run(): void
    {
        $passwordHash = Hash::make(self::DEFAULT_PASSWORD);

        $santris = Santri::all();

        foreach ($santris as $santri) {
            // Pastikan user santri tetap ada
            $this->upsertUser([
                'code' => $santri->code,
                'name' => $santri->nama_lengkap ?? $santri->code,
            ], Role::SANTRI, $passwordHash, 'santri');

            $waliProfile = [
                'code' => $this->buildWaliCode($santri->code),
                'name' => $this->buildWaliName($santri->nama_lengkap ?? $santri->code),
            ];

            $waliUser = $this->upsertUser($waliProfile, Role::WALI, $passwordHash, 'wali');

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
                    ['code' => '0235499001', 'name' => 'Amir', 'kelas' => ['Bacaan', 'Pegon']],
                    ['code' => '0235499002', 'name' => 'Anton', 'kelas' => ['Cepatan']],
                    ['code' => '0235499003', 'name' => 'Ridho', 'kelas' => ['Lambatan']],
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
                $staffUser = $this->upsertUser($member, $group['role'], $passwordHash, $group['type']);

                if ($group['role'] === Role::DEWAN_GURU) {
                    $this->syncDegurKelas($staffUser, (array) ($member['kelas'] ?? []));
                }
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

    /**
     * Ubah kode santri: ganti kemunculan pertama "24" menjadi "313".
     * Contoh: 022424001 -> 0231324001
     */
    private function buildWaliCode(string $santriCode): string
    {
        $pos = strpos($santriCode, '24');

        if ($pos === false) {
            return '313' . $santriCode;
        }

        return substr($santriCode, 0, $pos) . '313' . substr($santriCode, $pos + 2);
    }

    /**
     * Bentuk nama wali: "Wali_{nama_santri}" (spasi diganti underscore).
     */
    private function buildWaliName(string $santriName): string
    {
        $cleanName = trim($santriName) === '' ? 'Santri' : trim($santriName);

        return 'Wali_' . str_replace(' ', '_', $cleanName);
    }

    /**
     * @param array<int, string> $kelasNames
     */
    private function syncDegurKelas(User $degur, array $kelasNames): void
    {
        $kelasNames = collect($kelasNames)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $kelasIds = Kelas::query()
            ->whereIn('nama', $kelasNames)
            ->pluck('id')
            ->all();

        $degur->kelasAjar()->sync($kelasIds);
    }
}
