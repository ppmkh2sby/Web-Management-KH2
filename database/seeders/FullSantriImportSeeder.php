<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FullSantriImportSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'kh2kh2kh2';

    public function run(): void
    {
        $entries = array_merge($this->putra(), $this->putri());
        $password = Hash::make(self::DEFAULT_PASSWORD);
        $kelasIdByName = $this->syncKelas($entries);

        foreach ($entries as $entry) {
            $user = User::updateOrCreate(
                ['login_code' => $entry['nis']],
                [
                    'name' => $entry['name'],
                    'email' => sprintf('%s@santri.kh2.local', $entry['nis']),
                    'password' => $password,
                    'role' => Role::SANTRI,
                    'email_verified_at' => now(),
                ]
            );

            Santri::updateOrCreate(
                ['code' => $entry['nis']],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $entry['name'],
                    'tim' => $entry['tim'] ?? null,
                    'kelas_id' => $this->resolveKelasId($entry['kelas'] ?? null, $kelasIdByName),
                    'kampus' => $entry['kampus'] ?? null,
                    'jurusan' => $entry['jurusan'] ?? null,
                    'gender' => $entry['gender'] ?? null,
                ]
            );
        }
    }

    /**
     * Pastikan semua kelas yang dipakai data import tersedia.
     *
     * @param array<int, array<string, string|null>> $entries
     * @return array<string, int>
     */
    private function syncKelas(array $entries): array
    {
        $kelasNames = collect($entries)
            ->pluck('kelas')
            ->filter(fn ($name) => filled($name))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        $map = [];

        foreach ($kelasNames as $kelasName) {
            $kelas = Kelas::firstOrCreate(['nama' => $kelasName]);
            $map[$kelasName] = $kelas->id;
        }

        return $map;
    }

    /**
     * @param array<string, int> $kelasIdByName
     */
    private function resolveKelasId(?string $kelasName, array $kelasIdByName): ?int
    {
        $key = trim((string) $kelasName);
        if ($key === '') {
            return null;
        }

        return $kelasIdByName[$key] ?? null;
    }

    private function putra(): array
    {
        return [
            ['name' => 'ABDULLAH JUWAN DAWIRA', 'nis' => '022121013', 'kampus' => 'UNAIR', 'jurusan' => 'Keselamatan dan Kesehatan Kerja', 'gender' => 'putra', 'tim' => 'PH', 'kelas' => 'Cepatan'],
            ['name' => 'MUHAMMAD FARID FATCHTUR', 'nis' => '022222001', 'kampus' => 'ITS', 'jurusan' => 'Sistem Perkapalan', 'gender' => 'putra', 'tim' => 'Kebersihan', 'kelas' => 'Cepatan'],
            ['name' => 'MUHAMMAD FATH RAJIHAN NAFIE', 'nis' => '022222006', 'kampus' => 'UNAIR', 'jurusan' => 'Matematika', 'gender' => 'putra', 'tim' => 'Sekben', 'kelas' => 'Lambatan'],
            ['name' => 'ARDIAS AJI SAPUTRO', 'nis' => '022323002', 'kampus' => 'ITS', 'jurusan' => 'Teknik Infrastruktur Sipil', 'gender' => 'putra', 'tim' => 'KBM', 'kelas' => 'Lambatan'],
            ['name' => 'MUHAMMAD IRSYAD IBRAHIMOVIC', 'nis' => '022323004', 'kampus' => 'ITS', 'jurusan' => 'Teknik Material dan Metalurgi', 'gender' => 'putra', 'tim' => 'PH', 'kelas' => 'Lambatan'],
            ['name' => 'SYAIFUDIN AKBARI ABILUDIN', 'nis' => '022323006', 'kampus' => 'PPNS', 'jurusan' => 'Manajemen Bisnis', 'gender' => 'putra', 'tim' => 'Acara', 'kelas' => 'Pegon'],
            ['name' => 'ALWIDA RAHMAT', 'nis' => '022424001', 'kampus' => 'ITS', 'jurusan' => 'Sistem Informasi', 'gender' => 'putra', 'tim' => 'KTB', 'kelas' => 'Lambatan'],
            ["name" => "FAHMI ROSYIDIN AL'ULYA", 'nis' => '022424006', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putra', 'tim' => 'Sekben', 'kelas' => 'Lambatan'],
            ['name' => 'KEISHA ZAFIF FAHREZI', 'nis' => '022424007', 'kampus' => 'PENS', 'jurusan' => 'Multimedia Broadcast', 'gender' => 'putra', 'tim' => 'Acara', 'kelas' => 'Lambatan'],
            ['name' => 'MAESTRO RAFA AGNIYA', 'nis' => '022424008', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putra', 'tim' => 'KTB', 'kelas' => 'Lambatan'],
            ['name' => 'MUHAMAD FARREL AL-AQSA', 'nis' => '022424010', 'kampus' => 'UNAIR', 'jurusan' => 'Fisioterapi', 'gender' => 'putra', 'tim' => 'Ukppt', 'kelas' => 'Bacaan'],
            ['name' => 'MUHAMMAD FARIZKY ALFATH MAHARDIAN PUTRA', 'nis' => '022424011', 'kampus' => 'ITS', 'jurusan' => 'Teknik Sipil', 'gender' => 'putra', 'tim' => 'KBM', 'kelas' => 'Pegon'],
            ['name' => 'MUHAMMAD SETYO ARFAN IBRAHIM', 'nis' => '022424012', 'kampus' => 'ITS', 'jurusan' => 'Desain Produk', 'gender' => 'putra', 'tim' => 'Ukppt', 'kelas' => 'Bacaan'],
            ['name' => 'VIKY KARUNIA PUTRA PRATAMA', 'nis' => '022423017', 'kampus' => 'PPNS', 'jurusan' => 'Teknik Desain dan Manufaktur', 'gender' => 'putra', 'tim' => 'Kebersihan', 'kelas' => 'Lambatan'],
            ['name' => 'ZAKI AFIFI ARIF', 'nis' => '022424019', 'kampus' => 'ITS', 'jurusan' => 'Teknik Lingkungan', 'gender' => 'putra', 'tim' => 'KBM', 'kelas' => 'Bacaan'],
            ['name' => 'BRILIANT ACHMAD RAMADHAN', 'nis' => '022525004', 'kampus' => 'ITS', 'jurusan' => 'Teknik Lepas Pantai', 'gender' => 'putra', 'tim' => 'Kebersihan', 'kelas' => 'Lambatan'],
            ['name' => 'DIMAS ADI SANJAYA', 'nis' => '022525005', 'kampus' => 'Universitas Dr. Soetomo', 'jurusan' => 'Manajemen', 'gender' => 'putra', 'tim' => 'Ukppt', 'kelas' => 'Bacaan'],
            ['name' => 'FARIS JULDAN', 'nis' => '022525006', 'kampus' => 'PPNS', 'jurusan' => 'Keselamatan dan Kesehatan Kerja', 'gender' => 'putra', 'tim' => 'Sekben', 'kelas' => 'Bacaan'],
            ['name' => 'HANAFI SATRIYO UTOMO SETIAWAN', 'nis' => '022525007', 'kampus' => 'ITS', 'jurusan' => 'S2 - Teknik Informatika', 'gender' => 'putra', 'tim' => 'Acara', 'kelas' => 'Bacaan'],
            ['name' => 'SOFWAN MIFTAKHUDDIN MAARIF', 'nis' => '022525013', 'kampus' => 'UNAIR', 'jurusan' => 'Farmasi', 'gender' => 'putra', 'tim' => 'KTB', 'kelas' => 'Bacaan'],
            ['name' => 'MUHAMAD BAEHAQI AL MUJAHIDIN', 'nis' => '022524015', 'kampus' => 'UNAIR', 'jurusan' => 'Teknologi Hasil Perikanan', 'gender' => 'putra', 'tim' => 'Acara', 'kelas' => 'Pegon'],
        ];
    }

    private function putri(): array
    {
        return [
            ['name' => 'TARISA ADELYA SAFIERA', 'nis' => '022222004', 'kampus' => 'ITS', 'jurusan' => 'Perencanaan Wilayah dan Kota', 'gender' => 'putri', 'tim' => 'Acara', 'kelas' => 'Cepatan'],
            ['name' => 'AISYA WIDYA PRATIWI', 'nis' => '022323001', 'kampus' => 'UNAIR', 'jurusan' => 'Matematika', 'gender' => 'putri', 'tim' => 'PH', 'kelas' => 'Cepatan'],
            ['name' => 'CASEY PALLAS TALITHA HARJANTO', 'nis' => '022323003', 'kampus' => 'ITS', 'jurusan' => 'Desain Komunikasi Visual', 'gender' => 'putri', 'tim' => 'Kebersihan', 'kelas' => 'Lambatan'],
            ['name' => 'RIZKY KHOIRUNNISA', 'nis' => '022323005', 'kampus' => 'PENS', 'jurusan' => 'Teknik Telekomunikasi', 'gender' => 'putri', 'tim' => 'KBM', 'kelas' => 'Pegon'],
            ['name' => 'AYESHA NAYYARA PUTRI WURYADI', 'nis' => '022424002', 'kampus' => 'PPNS', 'jurusan' => 'Teknik Perancangan dan Kontruksi Kapal', 'gender' => 'putri', 'tim' => 'Acara', 'kelas' => 'Lambatan'],
            ['name' => 'AZZAHRA JAMALULLAILY MAFAZA', 'nis' => '022424003', 'kampus' => 'UNAIR', 'jurusan' => 'Bahasa dan Sastra Inggris', 'gender' => 'putri', 'tim' => 'Ukppt', 'kelas' => 'Lambatan'],
            ['name' => 'CHERFINE AN-NISAUL AULIYA ULLA', 'nis' => '022424004', 'kampus' => 'ITS', 'jurusan' => 'Teknik Sipil', 'gender' => 'putri', 'tim' => 'KBM', 'kelas' => 'Lambatan'],
            ['name' => 'DEVEN KARTIKA WIJAYA', 'nis' => '022424005', 'kampus' => 'ITS', 'jurusan' => 'Arsitektur', 'gender' => 'putri', 'tim' => 'Kebersihan', 'kelas' => 'Lambatan'],
            ['name' => 'MARITZA DARA ATHIFA', 'nis' => '022424009', 'kampus' => 'ITS', 'jurusan' => 'Sistem Informasi', 'gender' => 'putri', 'tim' => 'Sekben', 'kelas' => 'Pegon'],
            ['name' => 'NABILA KAYSA ADRISTI', 'nis' => '022424013', 'kampus' => 'ITS', 'jurusan' => 'Studi Pembangunan', 'gender' => 'putri', 'tim' => 'Kebersihan', 'kelas' => 'Cepatan'],
            ['name' => 'RARA ARIMBI GITA ATMODJO', 'nis' => '022424014', 'kampus' => 'ITS', 'jurusan' => 'Desain Komunikasi Visual', 'gender' => 'putri', 'tim' => 'PH', 'kelas' => 'Pegon'],
            ['name' => 'RENATA KEYSHA AZALIA KHOIRUNNISA', 'nis' => '022424015', 'kampus' => 'ITS', 'jurusan' => 'Teknik Geofisika', 'gender' => 'putri', 'tim' => 'Acara', 'kelas' => 'Lambatan'],
            ['name' => 'SYAHDINDA SHERLYTA LAURA', 'nis' => '022424016', 'kampus' => 'UNAIR', 'jurusan' => 'Bahasa dan Sastra Inggris', 'gender' => 'putri', 'tim' => 'KTB', 'kelas' => 'Lambatan'],
            ['name' => 'ZAHRA SUCIANA TRI AMMA MARETHA', 'nis' => '022424018', 'kampus' => 'UNAIR', 'jurusan' => 'Akuntansi', 'gender' => 'putri', 'tim' => 'Sekben', 'kelas' => 'Lambatan'],
            ['name' => 'AMANDA RAMADHANI PUTRI PANGESTI', 'nis' => '022525001', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putri', 'tim' => 'Ukppt', 'kelas' => 'Bacaan'],
            ['name' => 'AURA RENATA ANASYIYA AZKA', 'nis' => '022525002', 'kampus' => 'PENS', 'jurusan' => 'Sains Data Terapan', 'gender' => 'putri', 'tim' => 'Acara', 'kelas' => 'Bacaan'],
            ['name' => 'BALQIS SALWA AURELIA AZZAHRA', 'nis' => '022525003', 'kampus' => 'ITS', 'jurusan' => 'Teknologi Kedokteran', 'gender' => 'putri', 'tim' => 'KTB', 'kelas' => 'Bacaan'],
            ['name' => 'IMELYA URIVARTOUSI', 'nis' => '..', 'kampus' => 'ITS', 'jurusan' => 'Sistem Informasi', 'gender' => 'putri', 'tim' => 'KTB', 'kelas' => 'Lambatan'],
            ['name' => 'MAYLAVASA ADIVA BILQIS', 'nis' => '022525009', 'kampus' => 'PENS', 'jurusan' => 'Teknik Elektro Industri', 'gender' => 'putri', 'tim' => 'Kebersihan', 'kelas' => 'Bacaan'],
            ['name' => 'QISTHI KHIROFATI MADINA SENOAJI', 'nis' => '022525010', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putri', 'tim' => 'Acara', 'kelas' => 'Bacaan'],
            ['name' => 'RASHIDA ZARA FAUZIAH', 'nis' => '022525013', 'kampus' => 'ITS', 'jurusan' => 'Studi Pembangunan', 'gender' => 'putri', 'tim' => 'Sekben', 'kelas' => 'Lambatan'],
            ['name' => 'SAFA KARINDAH KAHAYA AISHA', 'nis' => '022525012', 'kampus' => 'UMS', 'jurusan' => 'Farmasi', 'gender' => 'putri', 'tim' => 'Ukppt', 'kelas' => 'Bacaan'],
            ['name' => 'SYARIFAH HUURI FILJANNAH', 'nis' => '022525014', 'kampus' => 'ITS', 'jurusan' => 'Teknik Kimia', 'gender' => 'putri', 'tim' => 'KBM', 'kelas' => 'Bacaan'],
        ];
    }
}
