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

        $isStaffDashboard = (bool) ($user && in_array($user->role, [Role::DEWAN_GURU, Role::PENGURUS], true));
        $santriOwned = $user?->santri;
        $isSantriContext = (bool) ($user && $user->role === Role::SANTRI && $santriOwned);
        $santri = $isSantriContext ? $santriOwned->loadMissing(['kelas', 'walis', 'user']) : $this->loadSantri();
        $santriId = $isSantriContext ? $santriOwned->id : null;

        $baseDisplayName = trim((string) ($santriOwned?->nama_lengkap ?? $user?->name ?? 'Santri'));
        $displayName = $isStaffDashboard ? 'Pak ' . $baseDisplayName : $baseDisplayName;
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

        $staffAttendanceStats = [
            'santriTotal' => 0,
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'persentase' => 0,
            'today' => 0,
            'putra' => 0,
            'putri' => 0,
        ];
        $staffProgressStats = [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'average' => 0,
            'quran' => 0,
            'hadits' => 0,
            'activeSantri' => 0,
        ];
        $staffLogStats = [
            'total' => 0,
            'today' => 0,
            'putra' => 0,
            'putri' => 0,
        ];
        $staffRecentLogs = collect();
        $staffProgressLeaders = collect();
        $progressPercentSql = "CASE WHEN target > 0 THEN CASE WHEN ROUND((capaian * 100.0) / target) > 100 THEN 100 ELSE ROUND((capaian * 100.0) / target) END ELSE 0 END";

        if ($santriId) {
            $attendanceBase = Presensi::query()->where('santri_id', $santriId);
            $attendanceCounts = (clone $attendanceBase)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
            $attendanceStats['hadir'] = (int) ($attendanceCounts['hadir'] ?? 0);
            $attendanceStats['izin'] = (int) ($attendanceCounts['izin'] ?? 0);
            $attendanceStats['sakit'] = (int) ($attendanceCounts['sakit'] ?? 0);
            $attendanceStats['alpha'] = (int) ($attendanceCounts['alpha'] ?? 0);
            $attendanceStats['total'] = $attendanceStats['hadir']
                + $attendanceStats['izin']
                + $attendanceStats['sakit']
                + $attendanceStats['alpha'];
            $attendanceStats['persentase'] = $attendanceStats['total'] > 0
                ? (int) round(($attendanceStats['hadir'] / $attendanceStats['total']) * 100)
                : 0;
            $attendanceRecent = (clone $attendanceBase)->with('kegiatan')->latest('created_at')->take(5)->get();

            $kafarahBase = Kafarah::query()->where('santri_id', $santriId);
            $kafarahAggregate = (clone $kafarahBase)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COALESCE(SUM(tanggungan), 0) as total_kafarah')
                ->selectRaw('COALESCE(SUM(jumlah_setor), 0) as jumlah_setor')
                ->first();
            $kafarahStats['total'] = (int) ($kafarahAggregate?->total ?? 0);
            $kafarahStats['total_kafarah'] = (int) ($kafarahAggregate?->total_kafarah ?? 0);
            $kafarahStats['jumlah_setor'] = (int) ($kafarahAggregate?->jumlah_setor ?? 0);
            $kafarahStats['sisa_tanggungan'] = max(
                0,
                $kafarahStats['total_kafarah'] - $kafarahStats['jumlah_setor']
            );
            $kafarahRecent = (clone $kafarahBase)->latest('tanggal')->take(5)->get();

            $progressBase = ProgressKeilmuan::query()->where('santri_id', $santriId);
            $progressAggregate = (clone $progressBase)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN {$progressPercentSql} >= 100 THEN 1 ELSE 0 END) as completed")
                ->selectRaw("SUM(CASE WHEN {$progressPercentSql} > 0 AND {$progressPercentSql} < 100 THEN 1 ELSE 0 END) as in_progress")
                ->selectRaw("COALESCE(AVG({$progressPercentSql}), 0) as average")
                ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as quran', [ProgressKeilmuan::LEVEL_QURAN])
                ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as hadits', [ProgressKeilmuan::LEVEL_HADITS])
                ->first();
            $progressStats['total'] = (int) ($progressAggregate?->total ?? 0);
            $progressStats['completed'] = (int) ($progressAggregate?->completed ?? 0);
            $progressStats['in_progress'] = (int) ($progressAggregate?->in_progress ?? 0);
            $progressStats['average'] = (int) round((float) ($progressAggregate?->average ?? 0));
            $progressStats['quran'] = (int) ($progressAggregate?->quran ?? 0);
            $progressStats['hadits'] = (int) ($progressAggregate?->hadits ?? 0);
            $progressRecent = (clone $progressBase)->latest('updated_at')->take(5)->get();

            $logBase = LogKeluarMasuk::query()->where('santri_id', $santriId);
            $logCounts = (clone $logBase)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
            $logStats['total'] = (int) $logCounts->sum();
            foreach (LogKeluarMasuk::STATUSES as $status) {
                $logStats[strtolower($status)] = (int) ($logCounts[$status] ?? 0);
            }
            $logRecent = (clone $logBase)->latest('tanggal_pengajuan')->take(5)->get();
        }

        if ($isStaffDashboard) {
            $today = Carbon::today();
            $staffAttendanceStats['santriTotal'] = Santri::query()->count();

            $staffAttendanceAggregate = Presensi::query()
                ->leftJoin('santris', 'santris.id', '=', 'presensis.santri_id')
                ->selectRaw("SUM(CASE WHEN presensis.status = 'hadir' THEN 1 ELSE 0 END) as hadir")
                ->selectRaw("SUM(CASE WHEN presensis.status = 'izin' THEN 1 ELSE 0 END) as izin")
                ->selectRaw("SUM(CASE WHEN presensis.status = 'sakit' THEN 1 ELSE 0 END) as sakit")
                ->selectRaw("SUM(CASE WHEN presensis.status = 'alpha' THEN 1 ELSE 0 END) as alpha")
                ->selectRaw('SUM(CASE WHEN DATE(presensis.created_at) = ? THEN 1 ELSE 0 END) as today', [$today->toDateString()])
                ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putra' THEN 1 ELSE 0 END) as putra")
                ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putri' THEN 1 ELSE 0 END) as putri")
                ->first();

            $staffAttendanceStats['hadir'] = (int) ($staffAttendanceAggregate?->hadir ?? 0);
            $staffAttendanceStats['izin'] = (int) ($staffAttendanceAggregate?->izin ?? 0);
            $staffAttendanceStats['sakit'] = (int) ($staffAttendanceAggregate?->sakit ?? 0);
            $staffAttendanceStats['alpha'] = (int) ($staffAttendanceAggregate?->alpha ?? 0);
            $staffAttendanceStats['total'] = $staffAttendanceStats['hadir']
                + $staffAttendanceStats['izin']
                + $staffAttendanceStats['sakit']
                + $staffAttendanceStats['alpha'];
            $staffAttendanceStats['persentase'] = $staffAttendanceStats['total'] > 0
                ? (int) round(($staffAttendanceStats['hadir'] / $staffAttendanceStats['total']) * 100)
                : 0;
            $staffAttendanceStats['today'] = (int) ($staffAttendanceAggregate?->today ?? 0);
            $staffAttendanceStats['putra'] = (int) ($staffAttendanceAggregate?->putra ?? 0);
            $staffAttendanceStats['putri'] = (int) ($staffAttendanceAggregate?->putri ?? 0);

            $staffProgressAggregate = ProgressKeilmuan::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN {$progressPercentSql} >= 100 THEN 1 ELSE 0 END) as completed")
                ->selectRaw("SUM(CASE WHEN {$progressPercentSql} > 0 AND {$progressPercentSql} < 100 THEN 1 ELSE 0 END) as in_progress")
                ->selectRaw("COALESCE(AVG({$progressPercentSql}), 0) as average")
                ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as quran', [ProgressKeilmuan::LEVEL_QURAN])
                ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as hadits', [ProgressKeilmuan::LEVEL_HADITS])
                ->selectRaw('COUNT(DISTINCT CASE WHEN COALESCE(capaian, 0) > 0 THEN santri_id END) as active_santri')
                ->first();

            $staffProgressStats['total'] = (int) ($staffProgressAggregate?->total ?? 0);
            $staffProgressStats['completed'] = (int) ($staffProgressAggregate?->completed ?? 0);
            $staffProgressStats['in_progress'] = (int) ($staffProgressAggregate?->in_progress ?? 0);
            $staffProgressStats['average'] = (int) round((float) ($staffProgressAggregate?->average ?? 0));
            $staffProgressStats['quran'] = (int) ($staffProgressAggregate?->quran ?? 0);
            $staffProgressStats['hadits'] = (int) ($staffProgressAggregate?->hadits ?? 0);
            $staffProgressStats['activeSantri'] = (int) ($staffProgressAggregate?->active_santri ?? 0);

            $staffProgressLeaders = ProgressKeilmuan::query()
                ->selectRaw('santri_id')
                ->selectRaw("ROUND(AVG({$progressPercentSql})) as average")
                ->selectRaw("SUM(CASE WHEN {$progressPercentSql} >= 100 THEN 1 ELSE 0 END) as completed")
                ->selectRaw('MAX(COALESCE(terakhir_setor, updated_at)) as updated_at')
                ->whereNotNull('santri_id')
                ->groupBy('santri_id')
                ->with('santri:id,nama_lengkap,tim,code')
                ->orderByDesc('average')
                ->limit(8)
                ->get()
                ->map(function (ProgressKeilmuan $row) {
                    $santri = $row->santri;

                    return [
                        'nama' => $santri?->nama_lengkap ?? '-',
                        'tim' => $santri?->tim_resolved ?? $santri?->tim ?? '-',
                        'average' => (int) ($row->average ?? 0),
                        'completed' => (int) ($row->completed ?? 0),
                        'updated_at' => $row->updated_at ? Carbon::parse((string) $row->updated_at) : null,
                    ];
                })
                ->values();

            $staffLogAggregate = LogKeluarMasuk::query()
                ->leftJoin('santris', 'santris.id', '=', 'log_keluar_masuks.santri_id')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN DATE(log_keluar_masuks.tanggal_pengajuan) = ? THEN 1 ELSE 0 END) as today', [$today->toDateString()])
                ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putra' THEN 1 ELSE 0 END) as putra")
                ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putri' THEN 1 ELSE 0 END) as putri")
                ->first();
            $staffLogStats['total'] = (int) ($staffLogAggregate?->total ?? 0);
            $staffLogStats['today'] = (int) ($staffLogAggregate?->today ?? 0);
            $staffLogStats['putra'] = (int) ($staffLogAggregate?->putra ?? 0);
            $staffLogStats['putri'] = (int) ($staffLogAggregate?->putri ?? 0);

            $staffRecentLogs = LogKeluarMasuk::query()
                ->with('santri:id,nama_lengkap,gender,tim,code')
                ->latest('tanggal_pengajuan')
                ->latest('id')
                ->limit(10)
                ->get();
        }

        $emailVerified = !is_null(Auth::user()->email_verified_at);

        return view('santri.pages.home', compact(
            'santri',
            'emailVerified',
            'isStaffDashboard',
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
            'logRecent',
            'staffAttendanceStats',
            'staffProgressStats',
            'staffLogStats',
            'staffRecentLogs',
            'staffProgressLeaders'
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
