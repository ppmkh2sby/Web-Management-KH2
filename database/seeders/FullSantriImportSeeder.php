<?php

namespace Database\Seeders;

use App\Enum\Role;
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
                    'kampus' => $entry['kampus'] ?? null,
                    'jurusan' => $entry['jurusan'] ?? null,
                    'gender' => $entry['gender'] ?? null,
                ]
            );
        }
    }

    private function putra(): array
    {
        return [
            ['name' => 'ABDULLAH JUWAN DAWIRA', 'nis' => '022121013', 'kampus' => 'UNAIR', 'jurusan' => 'Keselamatan dan Kesehatan Kerja', 'gender' => 'putra', 'tim' => null],
            ['name' => 'MUHAMMAD FARID FATCHTUR', 'nis' => '022222001', 'kampus' => 'ITS', 'jurusan' => 'Sistem Perkapalan', 'gender' => 'putra', 'tim' => 'PH'],
            ['name' => 'MUHAMMAD FATH RAJIHAN NAFIE', 'nis' => '022222006', 'kampus' => 'UNAIR', 'jurusan' => 'Matematika', 'gender' => 'putra', 'tim' => 'Kebersihan'],
            ['name' => 'ARDIAS AJI SAPUTRO', 'nis' => '022323002', 'kampus' => 'ITS', 'jurusan' => 'Teknik Infrastruktur Sipil', 'gender' => 'putra', 'tim' => 'Sekben'],
            ['name' => 'MUHAMMAD IRSYAD IBRAHIMOVIC', 'nis' => '022323004', 'kampus' => 'ITS', 'jurusan' => 'Teknik Material dan Metalurgi', 'gender' => 'putra', 'tim' => 'KBM'],
            ['name' => 'SYAIFUDIN AKBARI ABILUDIN', 'nis' => '022323006', 'kampus' => 'PPNS', 'jurusan' => 'Manajemen Bisnis', 'gender' => 'putra', 'tim' => 'PH'],
            ['name' => 'ALWIDA RAHMAT', 'nis' => '022424001', 'kampus' => 'ITS', 'jurusan' => 'Sistem Informasi', 'gender' => 'putra', 'tim' => 'Acara'],
            ["name" => "FAHMI ROSYIDIN AL'ULYA", 'nis' => '022424006', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putra', 'tim' => 'KTB'],
            ['name' => 'KEISHA ZAFIF FAHREZI', 'nis' => '022424007', 'kampus' => 'PENS', 'jurusan' => 'Multimedia Broadcast', 'gender' => 'putra', 'tim' => 'Sekben'],
            ['name' => 'MAESTRO RAFA AGNIYA', 'nis' => '022424008', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putra', 'tim' => 'Acara'],
            ['name' => 'MUHAMAD FARREL AL-AQSA', 'nis' => '022424010', 'kampus' => 'UNAIR', 'jurusan' => 'Fisioterapi', 'gender' => 'putra', 'tim' => 'Ukppt'],
            ['name' => 'MUHAMMAD FARIZKY ALFATH MAHARDIAN PUTRA', 'nis' => '022424011', 'kampus' => 'ITS', 'jurusan' => 'Teknik Sipil', 'gender' => 'putra', 'tim' => 'KBM'],
            ['name' => 'MUHAMMAD SETYO ARFAN IBRAHIM', 'nis' => '022424012', 'kampus' => 'ITS', 'jurusan' => 'Desain Produk', 'gender' => 'putra', 'tim' => 'Ukppt'],
            ['name' => 'VIKY KARUNIA PUTRA PRATAMA', 'nis' => '022423017', 'kampus' => 'PPNS', 'jurusan' => 'Teknik Desain dan Manufaktur', 'gender' => 'putra', 'tim' => 'Kebersihan'],
            ['name' => 'ZAKI AFIFI ARIF', 'nis' => '022424019', 'kampus' => 'ITS', 'jurusan' => 'Teknik Lingkungan', 'gender' => 'putra', 'tim' => 'KBM'],
            ['name' => 'BRILIANT ACHMAD RAMADHAN', 'nis' => '022525004', 'kampus' => 'ITS', 'jurusan' => 'Teknik Lepas Pantai', 'gender' => 'putra', 'tim' => 'Kebersihan'],
            ['name' => 'DIMAS ADI SANJAYA', 'nis' => '022525005', 'kampus' => 'Universitas Dr. Soetomo', 'jurusan' => 'Manajemen', 'gender' => 'putra', 'tim' => 'Ukppt'],
            ['name' => 'FARIS JULDAN', 'nis' => '022525006', 'kampus' => 'PPNS', 'jurusan' => 'Keselamatan dan Kesehatan Kerja', 'gender' => 'putra', 'tim' => 'Sekben'],
            ['name' => 'HANAFI SATRIYO UTOMO SETIAWAN', 'nis' => '022525007', 'kampus' => 'ITS', 'jurusan' => 'S2 - Teknik Informatika', 'gender' => 'putra', 'tim' => 'Acara'],
            ['name' => 'SOFWAN MIFTAKHUDDIN MAARIF', 'nis' => '022525013', 'kampus' => 'UNAIR', 'jurusan' => 'Farmasi', 'gender' => 'putra', 'tim' => 'KTB'],
            ['name' => 'MUHAMAD BAEHAQI AL MUJAHIDIN', 'nis' => '022524015', 'kampus' => 'UNAIR', 'jurusan' => 'Teknologi Hasil Perikanan', 'gender' => 'putra', 'tim' => 'Acara'],
        ];
    }

    private function putri(): array
    {
        return [
            ['name' => "KAFITA LU'LU' JANNIAH", 'nis' => '022121007', 'kampus' => 'UNAIR', 'jurusan' => 'Keperawatan', 'gender' => 'putri', 'tim' => 'Ukppt'],
            ['name' => 'TARISA ADELYA SAFIERA', 'nis' => '022222004', 'kampus' => 'ITS', 'jurusan' => 'Perencanaan Wilayah dan Kota', 'gender' => 'putri', 'tim' => 'Acara'],
            ['name' => 'AISYA WIDYA PRATIWI', 'nis' => '022323001', 'kampus' => 'UNAIR', 'jurusan' => 'Matematika', 'gender' => 'putri', 'tim' => 'PH'],
            ['name' => 'CASEY PALLAS TALITHA HARJANTO', 'nis' => '022323003', 'kampus' => 'ITS', 'jurusan' => 'Desain Komunikasi Visual', 'gender' => 'putri', 'tim' => 'Kebersihan'],
            ['name' => 'RIZKY KHOIRUNNISA', 'nis' => '022323005', 'kampus' => 'PENS', 'jurusan' => 'Teknik Telekomunikasi', 'gender' => 'putri', 'tim' => 'KBM'],
            ['name' => 'AYESHA NAYYARA PUTRI WURYADI', 'nis' => '022424002', 'kampus' => 'PPNS', 'jurusan' => 'Teknik Perancangan dan Kontruksi Kapal', 'gender' => 'putri', 'tim' => 'Acara'],
            ['name' => 'AZZAHRA JAMALULLAILY MAFAZA', 'nis' => '022424003', 'kampus' => 'UNAIR', 'jurusan' => 'Bahasa dan Sastra Inggris', 'gender' => 'putri', 'tim' => 'Ukppt'],
            ['name' => 'CHERFINE AN-NISAUL AULIYA ULLA', 'nis' => '022424004', 'kampus' => 'ITS', 'jurusan' => 'Teknik Sipil', 'gender' => 'putri', 'tim' => 'Kebersihan'],
            ['name' => 'DEVEN KARTIKA WIJAYA', 'nis' => '022424005', 'kampus' => 'ITS', 'jurusan' => 'Arsitektur', 'gender' => 'putri', 'tim' => 'KBM'],
            ['name' => 'MARITZA DARA ATHIFA', 'nis' => '022424009', 'kampus' => 'ITS', 'jurusan' => 'Sistem Informasi', 'gender' => 'putri', 'tim' => 'Sekben'],
            ['name' => 'NABILA KAYSA ADRISTI', 'nis' => '022424013', 'kampus' => 'ITS', 'jurusan' => 'Studi Pembangunan', 'gender' => 'putri', 'tim' => 'Kebersihan'],
            ['name' => 'RARA ARIMBI GITA ATMODJO', 'nis' => '022424014', 'kampus' => 'ITS', 'jurusan' => 'Desain Komunikasi Visual', 'gender' => 'putri', 'tim' => 'PH'],
            ['name' => 'RENATA KEYSHA AZALIA KHOIRUNNISA', 'nis' => '022424015', 'kampus' => 'ITS', 'jurusan' => 'Teknik Geofisika', 'gender' => 'putri', 'tim' => 'Acara'],
            ['name' => 'SYAHDINDA SHERLYTA LAURA', 'nis' => '022424016', 'kampus' => 'UNAIR', 'jurusan' => 'Bahasa dan Sastra Inggris', 'gender' => 'putri', 'tim' => 'KTB'],
            ['name' => 'ZAHRA SUCIANA TRI AMMA MARETHA', 'nis' => '022424018', 'kampus' => 'UNAIR', 'jurusan' => 'Akuntansi', 'gender' => 'putri', 'tim' => 'Sekben'],
            ['name' => 'AMANDA RAMADHANI PUTRI PANGESTI', 'nis' => '022525001', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putri', 'tim' => 'Ukppt'],
            ['name' => 'AURA RENATA ANASYIYA AZKA', 'nis' => '022525002', 'kampus' => 'PENS', 'jurusan' => 'Sains Data Terapan', 'gender' => 'putri', 'tim' => 'Acara'],
            ['name' => 'BALQIS SALWA AURELIA AZZAHRA', 'nis' => '022525003', 'kampus' => 'ITS', 'jurusan' => 'Teknologi Kedokteran', 'gender' => 'putri', 'tim' => 'KTB'],
            ['name' => 'IMELYA URIVARTOUSI', 'nis' => '022525008', 'kampus' => 'ITS', 'jurusan' => 'Sistem Informasi', 'gender' => 'putri', 'tim' => 'KTB'],
            ['name' => 'MAYLAVASA ADIVA BILQIS', 'nis' => '022525009', 'kampus' => 'PENS', 'jurusan' => 'Teknik Elektro Industri', 'gender' => 'putri', 'tim' => 'Kebersihan'],
            ['name' => 'QISTHI KHIROFATI MADINA SENOAJI', 'nis' => '022525010', 'kampus' => 'PENS', 'jurusan' => 'Teknik Informatika', 'gender' => 'putri', 'tim' => 'Acara'],
            ['name' => 'RASHIDA ZARA FAUZIAH', 'nis' => '022525013', 'kampus' => 'ITS', 'jurusan' => 'Studi Pembangunan', 'gender' => 'putri', 'tim' => 'KBM'],
            ['name' => 'SAFA KARINDAH KAHAYA AISHA', 'nis' => '022525012', 'kampus' => 'UMS', 'jurusan' => 'Farmasi', 'gender' => 'putri', 'tim' => 'Ukppt'],
            ['name' => 'SYARIFAH HUURI FILJANNAH', 'nis' => '022525014', 'kampus' => 'ITS', 'jurusan' => 'Teknik Kimia', 'gender' => 'putri', 'tim' => 'KBM'],
        ];
    }
}
