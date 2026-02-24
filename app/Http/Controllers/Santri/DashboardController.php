<?php

namespace App\Http\Controllers\Santri;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\Kafarah;
use App\Models\Kehadiran;
use App\Models\LogKeluarMasuk;
use App\Models\Presensi;
use App\Models\ProgressKeilmuan;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
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

    private function firstWaliChildCode(): ?string
    {
        return Auth::user()
            ?->waliOf()
            ->orderBy('santris.nama_lengkap')
            ->value('santris.code');
    }

    public function home(): View|RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->role === Role::WALI) {
            return redirect()->route('wali.main');
        }

        $santriOwned = $user?->santri;
        $isSantriContext = (bool) ($user && $user->role === Role::SANTRI && $santriOwned);
        $santri = $isSantriContext ? $santriOwned->loadMissing(['kelas', 'walis', 'user']) : $this->loadSantri();
        $santriId = $isSantriContext ? $santriOwned->id : null;

        $displayName = trim((string) ($santriOwned?->nama_lengkap ?? $user?->name ?? 'Santri'));
        $todayLabel = Carbon::now()->translatedFormat('l, d F Y');

        $attendanceStats = [
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'persentase' => 0,
        ];
        $attendanceRecent = collect();

        $kafarahStats = [
            'total' => 0,
            'total_kafarah' => 0,
            'jumlah_setor' => 0,
            'sisa_tanggungan' => 0,
        ];
        $kafarahRecent = collect();

        $progressStats = [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'average' => 0,
            'quran' => 0,
            'hadits' => 0,
        ];
        $progressRecent = collect();

        $logStats = collect(LogKeluarMasuk::STATUSES)->mapWithKeys(
            fn (string $status) => [strtolower($status) => 0]
        )->all();
        $logStats['total'] = 0;
        $logRecent = collect();

        if ($santriId) {
            $attendanceBase = Presensi::query()->where('santri_id', $santriId);
            $attendanceStats['total'] = (clone $attendanceBase)->count();
            $attendanceStats['hadir'] = (clone $attendanceBase)->where('status', 'hadir')->count();
            $attendanceStats['izin'] = (clone $attendanceBase)->where('status', 'izin')->count();
            $attendanceStats['sakit'] = (clone $attendanceBase)->where('status', 'sakit')->count();
            $attendanceStats['alpha'] = (clone $attendanceBase)->where('status', 'alpha')->count();
            $attendanceStats['persentase'] = $attendanceStats['total'] > 0
                ? (int) round(($attendanceStats['hadir'] / $attendanceStats['total']) * 100)
                : 0;
            $attendanceRecent = (clone $attendanceBase)->with('kegiatan')->latest('created_at')->take(5)->get();

            $kafarahRows = Kafarah::query()
                ->where('santri_id', $santriId)
                ->latest('tanggal')
                ->get();
            $kafarahStats['total'] = $kafarahRows->count();
            $kafarahStats['total_kafarah'] = (int) $kafarahRows->sum('tanggungan');
            $kafarahStats['jumlah_setor'] = (int) $kafarahRows->sum('jumlah_setor');
            $kafarahStats['sisa_tanggungan'] = max(
                0,
                $kafarahStats['total_kafarah'] - $kafarahStats['jumlah_setor']
            );
            $kafarahRecent = $kafarahRows->take(5);

            $progressRows = ProgressKeilmuan::query()
                ->where('santri_id', $santriId)
                ->latest('updated_at')
                ->get();
            $progressStats['total'] = $progressRows->count();
            $progressStats['completed'] = $progressRows->filter(
                fn (ProgressKeilmuan $item) => ($item->persentase ?? 0) >= 100
            )->count();
            $progressStats['in_progress'] = $progressRows->filter(
                fn (ProgressKeilmuan $item) => ($item->persentase ?? 0) > 0 && ($item->persentase ?? 0) < 100
            )->count();
            $progressStats['average'] = $progressStats['total'] > 0
                ? (int) round($progressRows->avg(fn (ProgressKeilmuan $item) => $item->persentase ?? 0))
                : 0;
            $progressStats['quran'] = $progressRows->where('level', ProgressKeilmuan::LEVEL_QURAN)->count();
            $progressStats['hadits'] = $progressRows->where('level', ProgressKeilmuan::LEVEL_HADITS)->count();
            $progressRecent = $progressRows->take(5);

            $logRows = LogKeluarMasuk::query()
                ->where('santri_id', $santriId)
                ->latest('tanggal_pengajuan')
                ->get();
            $logStats['total'] = $logRows->count();
            foreach (LogKeluarMasuk::STATUSES as $status) {
                $logStats[strtolower($status)] = $logRows->where('status', $status)->count();
            }
            $logRecent = $logRows->take(5);
        }

        $emailVerified = !is_null(Auth::user()->email_verified_at);

        return view('santri.pages.home', compact(
            'santri',
            'emailVerified',
            'isSantriContext',
            'displayName',
            'todayLabel',
            'attendanceStats',
            'attendanceRecent',
            'kafarahStats',
            'kafarahRecent',
            'progressStats',
            'progressRecent',
            'logStats',
            'logRecent'
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

    public function presensi(): View|RedirectResponse
    {
        if (Auth::user()?->role === Role::WALI) {
            $firstChildCode = $this->firstWaliChildCode();
            if (filled($firstChildCode)) {
                return redirect()->route('wali.anak.presensi', ['santriCode' => $firstChildCode]);
            }
            return redirect()->route('profile.edit')->with('status', 'Akun wali belum terhubung ke data anak.');
        }

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
                    'terakhir_setor' => Carbon::today()->subDays(1),
                ],
                [
                    'judul' => 'Fiqih Ibadah',
                    'target' => 8,
                    'capaian' => 5,
                    'satuan' => 'bab',
                    'level' => 'Fundamental',
                    'catatan' => 'Bab thaharah perlu diulang sebelum ujian lisan.',
                    'pembimbing' => 'Ust. Farhan',
                    'terakhir_setor' => Carbon::today()->subDays(2),
                ],
                [
                    'judul' => 'Bahasa Arab',
                    'target' => 12,
                    'capaian' => 9,
                    'satuan' => 'modul',
                    'level' => 'Advance',
                    'catatan' => 'Pertahankan setoran harian mufrodat baru.',
                    'pembimbing' => 'Ustadzah Alia',
                    'terakhir_setor' => Carbon::today()->subDays(3),
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
