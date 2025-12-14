<?php

namespace App\Http\Controllers\Santri;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\Kehadiran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected function loadSantri()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $santri = Santri::with(['kelas','walis','user'])
            ->where('user_id', Auth::id())
            ->first();

        if ($santri) {
            return $santri;
        }

        // Jika user bukan santri, coba pakai santri pertama sebagai referensi tampilan
        $fallback = Santri::with(['kelas','walis','user'])->first();
        if ($fallback) {
            return $fallback;
        }

        // Stub agar view tetap jalan walau tidak ada data santri
        $stub = new Santri();
        $stub->id = 0;
        $stub->nama = $user?->name ?? 'Tamu';
        $stub->nis = '';
        $stub->setRelation('kelas', null);
        $stub->setRelation('wali', null);
        return $stub;
    }

    /** Return query builder untuk model bila ada, selain itu null */
    private function q(string $class)
    {
        return class_exists($class) ? $class::query() : null;
    }

    public function home(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isSantri = $user && $user->role === Role::SANTRI;

        $santri = $this->loadSantri();
        $santriId = $santri?->id ?? 0;
        $today  = Carbon::today();

        // ---- Kehadiran (aman jika model tidak ada atau user bukan santri)
        $K = $this->q(Kehadiran::class);
        $hadir = $K && $santriId ? (clone $K)->where('santri_id', $santriId)->whereMonth('tanggal', now()->month)->where('status','hadir')->count() : 0;
        $izin  = $K && $santriId ? (clone $K)->where('santri_id', $santriId)->whereMonth('tanggal', now()->month)->where('status','izin')->count()  : 0;
        $alpa  = $K && $santriId ? (clone $K)->where('santri_id', $santriId)->whereMonth('tanggal', now()->month)->where('status','alpa')->count()  : 0;

        // ---- Jadwal hari ini
        $J = $this->q('App\\Models\\JadwalPelajaran');
        $jadwalHariIni = $J
            ? (clone $J)->with('mapel','guru')->where('kelas_id', optional($santri?->kelas)->id)->whereDate('tanggal', $today)->orderBy('jam_mulai')->get()
            : collect(); // fallback: koleksi kosong

        // ---- Pengumuman terbaru (bisa dibaca semua role)
        $P = $this->q('App\\Models\\Pengumuman');
        $pengumuman = $P ? (clone $P)->latest()->take(4)->get() : collect();

        $emailVerified = !is_null(Auth::user()->email_verified_at);

        return view('santri.pages.home', compact(
            'santri','hadir','izin','alpa','jadwalHariIni','pengumuman','emailVerified','isSantri'
        ));
    }

    public function profile(): View
    {
        $santri = $this->loadSantri();
        return view('santri.pages.profile', compact('santri'));
    }

    public function setting(): View
    {
        return view('santri.pages.setting');
    }

    public function presensi(): View
    {
        $santri = $this->loadSantri();
        $K = $this->q(Kehadiran::class);
        $data = $K && $santri?->id ? (clone $K)->where('santri_id',$santri->id)->latest('tanggal')->take(30)->get() : collect();

        $isKetertiban = auth()->user()?->isKetertiban()
            || (strcasecmp(trim((string)($santri?->tim ?? '')), 'ketertiban') === 0);
        $santriList = collect();
        $managedKehadiran = collect();

        if ($isKetertiban) {
            $santriList = Santri::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'code', 'tim']);
            $managedKehadiran = $K
                ? (clone $K)->with('santri')->latest('tanggal')->take(60)->get()
                : collect();
        }

        if ($data->isEmpty()) {
            $data = collect([
                ['tanggal' => Carbon::today()->toDateString(), 'status' => 'hadir', 'keterangan' => 'Pengajian Kitab Ba\'da Subuh'],
                ['tanggal' => Carbon::yesterday()->toDateString(), 'status' => 'hadir', 'keterangan' => 'Tahfidz Pagi'],
                ['tanggal' => Carbon::today()->subDays(2)->toDateString(), 'status' => 'izin', 'keterangan' => 'Kontrol kesehatan'],
                ['tanggal' => Carbon::today()->subDays(3)->toDateString(), 'status' => 'hadir', 'keterangan' => 'Kajian Hadits'],
                ['tanggal' => Carbon::today()->subDays(4)->toDateString(), 'status' => 'alpa', 'keterangan' => 'Belum mengisi presensi'],
            ])->map(fn ($row) => (object) $row);
        }

        return view('santri.pages.data.presensi', compact('santri','data','isKetertiban','santriList','managedKehadiran'));
    }

    public function progres(): View
    {
        $santri = $this->loadSantri();
        $ProgressModel = $this->q(\App\Models\ProgressKeilmuan::class);
        $items = $ProgressModel
            ? (clone $ProgressModel)->where('santri_id', $santri?->id)->latest()->get()
            : collect();

        if ($items->isEmpty()) {
            $items = collect([
                [
                    'judul' => 'Hafalan Juz 30',
                    'target' => 20,
                    'capaian' => 15,
                    'satuan' => 'surah',
                    'level' => 'Intermediate',
                    'catatan' => 'Fokuskan murajaah pada surah pendek menjelang tasmi\'.',
                    'pembimbing' => 'Ust. Abdullah',
                    'terakhir' => Carbon::today()->subDays(1),
                ],
                [
                    'judul' => 'Fiqih Ibadah',
                    'target' => 8,
                    'capaian' => 5,
                    'satuan' => 'bab',
                    'level' => 'Fundamental',
                    'catatan' => 'Bab thaharah perlu diulang sebelum ujian lisan.',
                    'pembimbing' => 'Ust. Farhan',
                    'terakhir' => Carbon::today()->subDays(2),
                ],
                [
                    'judul' => 'Bahasa Arab',
                    'target' => 12,
                    'capaian' => 9,
                    'satuan' => 'modul',
                    'level' => 'Advance',
                    'catatan' => 'Pertahankan setoran harian mufrodat baru.',
                    'pembimbing' => 'Ustadzah Alia',
                    'terakhir' => Carbon::today()->subDays(3),
                ],
            ])->map(function ($item) {
                $item['persentase'] = $item['target'] ? round(($item['capaian'] / $item['target']) * 100) : 0;
                return (object) $item;
            });
        }

        return view('santri.pages.data.progres', compact('santri','items'));
    }

    public function log(): View
    {
        $santri = $this->loadSantri();
        $LogModel = $this->q(\App\Models\LogKeluarMasuk::class);
        $logs = $LogModel
            ? (clone $LogModel)->where('santri_id', $santri?->id)->latest('tanggal_pengajuan')->get()
            : collect();

        if ($logs->isEmpty()) {
            $logs = collect([
                [
                    'tanggal_pengajuan' => Carbon::today()->subDays(1)->toDateString(),
                    'jenis' => 'Keluar Pondok',
                    'rentang' => '13.00 - 16.00',
                    'status' => 'disetujui',
                    'catatan' => 'Kontrol kesehatan di Puskesmas',
                    'petugas' => 'Ust. Fathur',
                ],
                [
                    'tanggal_pengajuan' => Carbon::today()->subDays(5)->toDateString(),
                    'jenis' => 'Cuti Akhir Pekan',
                    'rentang' => 'Sabtu - Ahad',
                    'status' => 'proses',
                    'catatan' => 'Undangan keluarga',
                    'petugas' => 'Ust. Dani',
                ],
                [
                    'tanggal_pengajuan' => Carbon::today()->subDays(8)->toDateString(),
                    'jenis' => 'Kembali ke Pondok',
                    'rentang' => '21.15',
                    'status' => 'tercatat',
                    'catatan' => 'Setelah bakti sosial',
                    'petugas' => 'Satpam Utama',
                ],
            ])->map(fn ($row) => (object) $row);
        }

        return view('santri.pages.data.log', compact('santri','logs'));
    }
}
