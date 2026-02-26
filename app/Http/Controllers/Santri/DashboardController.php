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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PROGRESS_PERCENT_SQL = "CASE WHEN target > 0 THEN CASE WHEN ROUND((capaian * 100.0) / target) > 100 THEN 100 ELSE ROUND((capaian * 100.0) / target) END ELSE 0 END";

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

        $attendanceStats = $this->emptyAttendanceStats();
        $attendanceRecent = collect();

        $kafarahStats = $this->emptyKafarahStats();
        $kafarahRecent = collect();

        $progressStats = $this->emptyProgressStats();
        $progressRecent = collect();

        $logStats = $this->emptyLogStats();
        $logRecent = collect();

        $staffAttendanceStats = $this->emptyStaffAttendanceStats();
        $staffProgressStats = $this->emptyStaffProgressStats();
        $staffLogStats = $this->emptyStaffLogStats();
        $staffRecentLogs = collect();
        $staffProgressLeaders = collect();

        if ($santriId) {
            $attendanceData = $this->resolveSantriAttendance($santriId);
            $attendanceStats = $attendanceData['stats'];
            $attendanceRecent = $attendanceData['recent'];

            $kafarahData = $this->resolveSantriKafarah($santriId);
            $kafarahStats = $kafarahData['stats'];
            $kafarahRecent = $kafarahData['recent'];

            $progressData = $this->resolveSantriProgress($santriId);
            $progressStats = $progressData['stats'];
            $progressRecent = $progressData['recent'];

            $logData = $this->resolveSantriLogs($santriId);
            $logStats = $logData['stats'];
            $logRecent = $logData['recent'];
        }

        if ($isStaffDashboard) {
            $today = Carbon::today();

            $staffAttendanceStats = $this->resolveStaffAttendance($today);

            $staffProgressData = $this->resolveStaffProgress();
            $staffProgressStats = $staffProgressData['stats'];
            $staffProgressLeaders = $staffProgressData['leaders'];

            $staffLogData = $this->resolveStaffLogs($today);
            $staffLogStats = $staffLogData['stats'];
            $staffRecentLogs = $staffLogData['recent'];
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

    private function emptyAttendanceStats(): array
    {
        return [
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'persentase' => 0,
        ];
    }

    private function emptyKafarahStats(): array
    {
        return [
            'total' => 0,
            'total_kafarah' => 0,
            'jumlah_setor' => 0,
            'sisa_tanggungan' => 0,
        ];
    }

    private function emptyProgressStats(): array
    {
        return [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'average' => 0,
            'quran' => 0,
            'hadits' => 0,
        ];
    }

    private function emptyLogStats(): array
    {
        $stats = collect(LogKeluarMasuk::STATUSES)
            ->mapWithKeys(fn (string $status) => [strtolower($status) => 0])
            ->all();
        $stats['total'] = 0;

        return $stats;
    }

    private function emptyStaffAttendanceStats(): array
    {
        return [
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
    }

    private function emptyStaffProgressStats(): array
    {
        return [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'average' => 0,
            'quran' => 0,
            'hadits' => 0,
            'activeSantri' => 0,
        ];
    }

    private function emptyStaffLogStats(): array
    {
        return [
            'total' => 0,
            'today' => 0,
            'putra' => 0,
            'putri' => 0,
        ];
    }

    private function resolveSantriAttendance(int $santriId): array
    {
        $stats = $this->emptyAttendanceStats();
        $attendanceBase = Presensi::query()->where('santri_id', $santriId);
        $attendanceCounts = (clone $attendanceBase)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats['hadir'] = (int) ($attendanceCounts['hadir'] ?? 0);
        $stats['izin'] = (int) ($attendanceCounts['izin'] ?? 0);
        $stats['sakit'] = (int) ($attendanceCounts['sakit'] ?? 0);
        $stats['alpha'] = (int) ($attendanceCounts['alpha'] ?? 0);
        $stats['total'] = $stats['hadir'] + $stats['izin'] + $stats['sakit'] + $stats['alpha'];
        $stats['persentase'] = $stats['total'] > 0
            ? (int) round(($stats['hadir'] / $stats['total']) * 100)
            : 0;

        return [
            'stats' => $stats,
            'recent' => (clone $attendanceBase)->with('kegiatan')->latest('created_at')->take(5)->get(),
        ];
    }

    private function resolveSantriKafarah(int $santriId): array
    {
        $stats = $this->emptyKafarahStats();
        $kafarahBase = Kafarah::query()->where('santri_id', $santriId);
        $aggregate = (clone $kafarahBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(tanggungan), 0) as total_kafarah')
            ->selectRaw('COALESCE(SUM(jumlah_setor), 0) as jumlah_setor')
            ->first();

        $stats['total'] = (int) ($aggregate?->total ?? 0);
        $stats['total_kafarah'] = (int) ($aggregate?->total_kafarah ?? 0);
        $stats['jumlah_setor'] = (int) ($aggregate?->jumlah_setor ?? 0);
        $stats['sisa_tanggungan'] = max(0, $stats['total_kafarah'] - $stats['jumlah_setor']);

        return [
            'stats' => $stats,
            'recent' => (clone $kafarahBase)->latest('tanggal')->take(5)->get(),
        ];
    }

    private function resolveSantriProgress(int $santriId): array
    {
        $stats = $this->emptyProgressStats();
        $progressBase = ProgressKeilmuan::query()->where('santri_id', $santriId);
        $aggregate = (clone $progressBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN " . self::PROGRESS_PERCENT_SQL . " >= 100 THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN " . self::PROGRESS_PERCENT_SQL . " > 0 AND " . self::PROGRESS_PERCENT_SQL . " < 100 THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("COALESCE(AVG(" . self::PROGRESS_PERCENT_SQL . "), 0) as average")
            ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as quran', [ProgressKeilmuan::LEVEL_QURAN])
            ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as hadits', [ProgressKeilmuan::LEVEL_HADITS])
            ->first();

        $stats['total'] = (int) ($aggregate?->total ?? 0);
        $stats['completed'] = (int) ($aggregate?->completed ?? 0);
        $stats['in_progress'] = (int) ($aggregate?->in_progress ?? 0);
        $stats['average'] = (int) round((float) ($aggregate?->average ?? 0));
        $stats['quran'] = (int) ($aggregate?->quran ?? 0);
        $stats['hadits'] = (int) ($aggregate?->hadits ?? 0);

        return [
            'stats' => $stats,
            'recent' => (clone $progressBase)->latest('updated_at')->take(5)->get(),
        ];
    }

    private function resolveSantriLogs(int $santriId): array
    {
        $stats = $this->emptyLogStats();
        $logBase = LogKeluarMasuk::query()->where('santri_id', $santriId);
        $logCounts = (clone $logBase)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats['total'] = (int) $logCounts->sum();
        foreach (LogKeluarMasuk::STATUSES as $status) {
            $stats[strtolower($status)] = (int) ($logCounts[$status] ?? 0);
        }

        return [
            'stats' => $stats,
            'recent' => (clone $logBase)->latest('tanggal_pengajuan')->take(5)->get(),
        ];
    }

    private function resolveStaffAttendance(Carbon $today): array
    {
        $stats = $this->emptyStaffAttendanceStats();
        $stats['santriTotal'] = Santri::query()->count();
        $aggregate = Presensi::query()
            ->leftJoin('santris', 'santris.id', '=', 'presensis.santri_id')
            ->selectRaw("SUM(CASE WHEN presensis.status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'izin' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'sakit' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN presensis.status = 'alpha' THEN 1 ELSE 0 END) as alpha")
            ->selectRaw('SUM(CASE WHEN DATE(presensis.created_at) = ? THEN 1 ELSE 0 END) as today', [$today->toDateString()])
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putra' THEN 1 ELSE 0 END) as putra")
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putri' THEN 1 ELSE 0 END) as putri")
            ->first();

        $stats['hadir'] = (int) ($aggregate?->hadir ?? 0);
        $stats['izin'] = (int) ($aggregate?->izin ?? 0);
        $stats['sakit'] = (int) ($aggregate?->sakit ?? 0);
        $stats['alpha'] = (int) ($aggregate?->alpha ?? 0);
        $stats['total'] = $stats['hadir'] + $stats['izin'] + $stats['sakit'] + $stats['alpha'];
        $stats['persentase'] = $stats['total'] > 0
            ? (int) round(($stats['hadir'] / $stats['total']) * 100)
            : 0;
        $stats['today'] = (int) ($aggregate?->today ?? 0);
        $stats['putra'] = (int) ($aggregate?->putra ?? 0);
        $stats['putri'] = (int) ($aggregate?->putri ?? 0);

        return $stats;
    }

    private function resolveStaffProgress(): array
    {
        $stats = $this->emptyStaffProgressStats();
        $aggregate = ProgressKeilmuan::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN " . self::PROGRESS_PERCENT_SQL . " >= 100 THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN " . self::PROGRESS_PERCENT_SQL . " > 0 AND " . self::PROGRESS_PERCENT_SQL . " < 100 THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("COALESCE(AVG(" . self::PROGRESS_PERCENT_SQL . "), 0) as average")
            ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as quran', [ProgressKeilmuan::LEVEL_QURAN])
            ->selectRaw('SUM(CASE WHEN level = ? THEN 1 ELSE 0 END) as hadits', [ProgressKeilmuan::LEVEL_HADITS])
            ->selectRaw('COUNT(DISTINCT CASE WHEN COALESCE(capaian, 0) > 0 THEN santri_id END) as active_santri')
            ->first();

        $stats['total'] = (int) ($aggregate?->total ?? 0);
        $stats['completed'] = (int) ($aggregate?->completed ?? 0);
        $stats['in_progress'] = (int) ($aggregate?->in_progress ?? 0);
        $stats['average'] = (int) round((float) ($aggregate?->average ?? 0));
        $stats['quran'] = (int) ($aggregate?->quran ?? 0);
        $stats['hadits'] = (int) ($aggregate?->hadits ?? 0);
        $stats['activeSantri'] = (int) ($aggregate?->active_santri ?? 0);

        $leaders = ProgressKeilmuan::query()
            ->selectRaw('santri_id')
            ->selectRaw("ROUND(AVG(" . self::PROGRESS_PERCENT_SQL . ")) as average")
            ->selectRaw("SUM(CASE WHEN " . self::PROGRESS_PERCENT_SQL . " >= 100 THEN 1 ELSE 0 END) as completed")
            ->selectRaw('MAX(COALESCE(terakhir_setor, updated_at)) as updated_at')
            ->whereNotNull('santri_id')
            ->groupBy('santri_id')
            ->with('santri:id,nama_lengkap,tim,code')
            ->orderByDesc('average')
            ->limit(8)
            ->get()
            ->map(fn (ProgressKeilmuan $row) => $this->mapStaffProgressLeader($row))
            ->values();

        return ['stats' => $stats, 'leaders' => $leaders];
    }

    private function mapStaffProgressLeader(ProgressKeilmuan $row): array
    {
        $santri = $row->santri;

        return [
            'nama' => $santri?->nama_lengkap ?? '-',
            'tim' => $santri?->tim_resolved ?? $santri?->tim ?? '-',
            'average' => (int) ($row->average ?? 0),
            'completed' => (int) ($row->completed ?? 0),
            'updated_at' => $row->updated_at ? Carbon::parse((string) $row->updated_at) : null,
        ];
    }

    private function resolveStaffLogs(Carbon $today): array
    {
        $stats = $this->emptyStaffLogStats();
        $aggregate = LogKeluarMasuk::query()
            ->leftJoin('santris', 'santris.id', '=', 'log_keluar_masuks.santri_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN DATE(log_keluar_masuks.tanggal_pengajuan) = ? THEN 1 ELSE 0 END) as today', [$today->toDateString()])
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putra' THEN 1 ELSE 0 END) as putra")
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(santris.gender, '')) = 'putri' THEN 1 ELSE 0 END) as putri")
            ->first();

        $stats['total'] = (int) ($aggregate?->total ?? 0);
        $stats['today'] = (int) ($aggregate?->today ?? 0);
        $stats['putra'] = (int) ($aggregate?->putra ?? 0);
        $stats['putri'] = (int) ($aggregate?->putri ?? 0);

        $recent = LogKeluarMasuk::query()
            ->with('santri:id,nama_lengkap,gender,tim,code')
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->limit(10)
            ->get();

        return ['stats' => $stats, 'recent' => $recent];
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
